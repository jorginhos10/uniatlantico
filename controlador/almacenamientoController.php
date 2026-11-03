<?php
// controlador/almacenamientoController.php
require_once 'config/config.php';
require_once __DIR__ . '/../config/roles_helper.php';

class AlmacenamientoController {
    private $db;

    // Tablas que no entran en el respaldo: los snapshots ya son copias y pesan mucho
    private $excluidas = ['snapshots'];

    const MAX_BYTES = 52428800; // 50 MB

    public function __construct() {
        // Copias de seguridad y restauración: solo el administrador del sistema (además del filtro de la ruta)
        if (!Roles::esAdminSistema()) {
            http_response_code(403);
            exit('No tienes permisos para esta sección');
        }
        try {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
            $this->db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AlmacenamientoController DB error: " . $e->getMessage());
        }
    }

    // Todas las tablas de la base (no una lista fija: así el respaldo no se queda sin las tablas nuevas, como los trimestres)
    private function tablas() {
        $tablas = [];
        foreach ($this->db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM) as $f) {
            if (!in_array($f[0], $this->excluidas, true)) $tablas[] = $f[0];
        }
        return $tablas;
    }

    private function esc($nombre) {
        return '`' . str_replace('`', '``', $nombre) . '`';
    }

    private function volcar() {
        $payload = [
            'version' => '2.0',
            'app'     => 'uniatlantico',
            'fecha'   => date('Y-m-d H:i:s'),
            'db'      => Config::DB_NAME,
            'tablas'  => [],
        ];
        foreach ($this->tablas() as $tabla) {
            try {
                $payload['tablas'][$tabla] = $this->db->query("SELECT * FROM " . $this->esc($tabla))->fetchAll();
            } catch (PDOException $e) {
                error_log("Backup: no se pudo leer $tabla: " . $e->getMessage());
                $payload['tablas'][$tabla] = [];
            }
        }
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function index() {
        require_once 'vista/almacenamiento/index.php';
    }

    public function backup() {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');
        $json = $this->volcar();
        if ($json === false) {
            http_response_code(500);
            exit('No se pudo generar el respaldo');
        }
        $filename = 'backup_' . date('Ymd_His') . '.javb';

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        echo $json;
        exit;
    }

    // Antes de restaurar se guarda una copia de lo que hay: si el archivo trae datos equivocados se puede volver atrás
    private function guardarCopiaPrevia() {
        $dir = dirname(__DIR__) . '/evidencias/_respaldos';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) return null;
        // La carpeta evidencias/ no se sirve; se asegura igual
        $ht = dirname(__DIR__) . '/evidencias/.htaccess';
        if (!is_file($ht)) {
            @file_put_contents($ht, "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
        }
        $json = $this->volcar();
        $ruta = $dir . '/prerestauracion_' . date('Ymd_His') . '.javb';
        return ($json !== false && @file_put_contents($ruta, $json) !== false) ? basename($ruta) : null;
    }

    public function sincronizar() {
        header('Content-Type: application/json');
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        if (($_POST['confirmacion'] ?? '') !== 'RESTAURAR') {
            echo json_encode(['success' => false, 'message' => 'Falta la confirmación: escribe RESTAURAR']);
            return;
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo o hubo un error de subida']);
            return;
        }
        if ($_FILES['archivo']['size'] > self::MAX_BYTES) {
            echo json_encode(['success' => false, 'message' => 'El archivo supera los 50 MB permitidos']);
            return;
        }

        $contenido = file_get_contents($_FILES['archivo']['tmp_name']);
        if ($contenido === false) {
            echo json_encode(['success' => false, 'message' => 'No se pudo leer el archivo']);
            return;
        }

        $data = json_decode($contenido, true);

        if (!$data || !isset($data['app']) || $data['app'] !== 'uniatlantico' || !isset($data['tablas']) || !is_array($data['tablas'])) {
            echo json_encode(['success' => false, 'message' => 'Archivo .javb inválido o no pertenece a esta aplicación']);
            return;
        }

        $copia = $this->guardarCopiaPrevia();
        if ($copia === null) {
            echo json_encode(['success' => false, 'message' => 'No se pudo guardar la copia previa; no se restauró nada']);
            return;
        }

        $permitidas = $this->tablas();
        try {
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
            $this->db->beginTransaction();

            foreach ($data['tablas'] as $tabla => $filas) {
                // Solo se restauran tablas que existen en esta base (el nombre del archivo nunca entra al SQL sin comprobarse)
                if (!in_array($tabla, $permitidas, true) || !is_array($filas)) continue;

                // DELETE y no TRUNCATE: TRUNCATE confirma la transacción y un fallo a medias dejaría la tabla vacía
                $this->db->exec("DELETE FROM " . $this->esc($tabla));
                if (empty($filas)) continue;

                $reales = array_column($this->db->query("SHOW COLUMNS FROM " . $this->esc($tabla))->fetchAll(), 'Field');
                $columnas = array_values(array_intersect(array_keys($filas[0]), $reales));
                if (!$columnas) continue;
                $colStr = implode(', ', array_map([$this, 'esc'], $columnas));
                $stmt   = $this->db->prepare("INSERT INTO " . $this->esc($tabla) . " ($colStr) VALUES (" . implode(', ', array_fill(0, count($columnas), '?')) . ")");

                foreach ($filas as $fila) {
                    $stmt->execute(array_map(function ($c) use ($fila) { return $fila[$c] ?? null; }, $columnas));
                }
            }

            $this->db->commit();
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");

            $fecha = $data['fecha'] ?? 'desconocida';
            echo json_encode([
                'success' => true,
                'message' => "Base de datos restaurada desde el backup del $fecha. Se guardó una copia de lo anterior ($copia).",
            ]);
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
            error_log("Error sincronizar backup: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'No se restauró nada: ocurrió un error y se revirtieron los cambios.']);
        }
    }
}
