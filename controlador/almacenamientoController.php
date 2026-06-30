<?php
// controlador/almacenamientoController.php
require_once 'config/config.php';

class AlmacenamientoController {
    private $db;

    private $tablas = [
        'lineas_estrategicas',
        'estrategias',
        'motores',
        'proyectos',
        'planes_institucionales',
        'facultades',
        'ano-for-de-144',
        'data_linea_estrategica',
        'data_motores',
        'data_proyectos',
        'formulacion_144',
        'formularios',
        'novedades',
        'proveedores',
        'usuarios',
        'cargos',
        'lista_permisos',
        'detalle_permiso',
        'mensajes',
        'mensajes_leidos',
        'user_filter_preferences',
    ];

    public function __construct() {
        try {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
            $this->db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AlmacenamientoController DB error: " . $e->getMessage());
        }
    }

    public function index() {
        require_once 'vista/almacenamiento/index.php';
    }

    public function backup() {
        $payload = [
            'version' => '1.0',
            'app'     => 'uniatlantico',
            'fecha'   => date('Y-m-d H:i:s'),
            'db'      => Config::DB_NAME,
            'tablas'  => [],
        ];

        foreach ($this->tablas as $tabla) {
            try {
                $stmt = $this->db->query("SELECT * FROM `$tabla`");
                $payload['tablas'][$tabla] = $stmt->fetchAll();
            } catch (PDOException $e) {
                // tabla no existe en esta instalacion, omitir
                $payload['tablas'][$tabla] = [];
            }
        }

        $json     = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'backup_' . date('Ymd_His') . '.javb';

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        echo $json;
        exit;
    }

    public function sincronizar() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo o hubo un error de subida']);
            return;
        }

        $contenido = file_get_contents($_FILES['archivo']['tmp_name']);
        if ($contenido === false) {
            echo json_encode(['success' => false, 'message' => 'No se pudo leer el archivo']);
            return;
        }

        $data = json_decode($contenido, true);

        if (!$data || !isset($data['app']) || $data['app'] !== 'uniatlantico' || !isset($data['tablas'])) {
            echo json_encode(['success' => false, 'message' => 'Archivo .javb inválido o no pertenece a esta aplicación']);
            return;
        }

        try {
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");

            foreach ($data['tablas'] as $tabla => $filas) {
                // Solo restaurar tablas conocidas por seguridad
                if (!in_array($tabla, $this->tablas)) continue;

                $this->db->exec("TRUNCATE TABLE `$tabla`");

                if (empty($filas)) continue;

                $columnas     = array_keys($filas[0]);
                $colStr       = implode(', ', array_map(fn($c) => "`$c`", $columnas));
                $placeholders = '(' . implode(', ', array_fill(0, count($columnas), '?')) . ')';
                $stmt         = $this->db->prepare("INSERT INTO `$tabla` ($colStr) VALUES $placeholders");

                foreach ($filas as $fila) {
                    $stmt->execute(array_values($fila));
                }
            }

            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");

            $fecha = $data['fecha'] ?? 'desconocida';
            echo json_encode([
                'success' => true,
                'message' => "Base de datos restaurada exitosamente desde el backup del $fecha",
            ]);
        } catch (PDOException $e) {
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
            error_log("Error sincronizar backup: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al restaurar: ' . $e->getMessage()]);
        }
    }
}
?>
