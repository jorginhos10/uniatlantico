<?php
// modelo/snapshotModel.php
// Un "snapshot" es una copia completa del estado de la base de datos (todas las tablas, fila por
// fila, como JSON) tomada en un momento dado. Sirve como respaldo y como fuente de datos congelada.
require_once 'config/config.php';

class SnapshotModel {
    private $db;

    // Tablas del propio mecanismo de snapshots: nunca se incluyen dentro de un snapshot
    private const TABLAS_EXCLUIDAS = ['snapshots', 'snapshots_datos'];

    public function __construct() {
        try {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
            $this->db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->asegurarTablas();
        } catch (PDOException $e) {
            error_log("Error conexión SnapshotModel: " . $e->getMessage());
        }
    }

    // Las tablas del mecanismo se crean solas si la base todavía no las tiene (antes dependían de un script SQL aparte)
    private function asegurarTablas() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS `snapshots` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nombre` varchar(150) NOT NULL,
            `creado_por` int(11) DEFAULT NULL,
            `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Trimestre al que representa el snapshot (para el Seguimiento trimestral): formulario y trimestre 1 a 4
        foreach (['formulario_id' => 'INT NULL', 'trimestre' => 'TINYINT NULL'] as $col => $def) {
            if ($this->db->query("SHOW COLUMNS FROM snapshots LIKE '{$col}'")->rowCount() === 0) {
                $this->db->exec("ALTER TABLE snapshots ADD COLUMN {$col} {$def}");
            }
        }
        $this->db->exec("CREATE TABLE IF NOT EXISTS `snapshots_datos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `snapshot_id` int(11) NOT NULL,
            `tabla` varchar(100) NOT NULL,
            `filas` int(11) NOT NULL DEFAULT 0,
            `datos` longtext NOT NULL,
            PRIMARY KEY (`id`),
            KEY `snapshot_id` (`snapshot_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function getAll() {
        try {
            $stmt = $this->db->query(
                "SELECT s.id, s.nombre, s.creado_en, s.formulario_id, s.trimestre, frm.titulo AS formulario_titulo, frm.anio AS formulario_anio,
                        u.nombre AS creado_por_nombre,
                        COUNT(sd.id) AS total_tablas,
                        COALESCE(SUM(sd.filas), 0) AS total_filas,
                        COALESCE(SUM(LENGTH(sd.datos)), 0) AS bytes
                 FROM snapshots s
                 LEFT JOIN usuarios u ON u.id = s.creado_por
                 LEFT JOIN formularios frm ON frm.id = s.formulario_id
                 LEFT JOIN snapshots_datos sd ON sd.snapshot_id = s.id
                 GROUP BY s.id, s.nombre, s.creado_en, s.formulario_id, s.trimestre, frm.titulo, frm.anio, u.nombre
                 ORDER BY s.creado_en DESC, s.id DESC"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAll snapshots: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre, creado_por, creado_en, formulario_id, trimestre FROM snapshots WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Error getById snapshot: " . $e->getMessage());
            return null;
        }
    }

    // Copia todas las tablas en una sola transacción: si algo falla, no queda un snapshot a medias.
    // Devuelve el id del snapshot creado; lanza la excepción si falla.
    // Formularios (planes) a los que se puede asignar un trimestre
    public function getFormularios() {
        try {
            return $this->db->query("SELECT id, titulo, anio FROM formularios WHERE estado = 1 ORDER BY anio DESC, id DESC")->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getFormularios snapshots: " . $e->getMessage());
            return [];
        }
    }

    // Asigna el snapshot a un trimestre (1 a 4) de un formulario; cada trimestre tiene un solo snapshot, así que
    // reemplaza al que tuviera. Trimestre 0 quita la asignación.
    public function asignarTrimestre($id, $formularioId, $trimestre) {
        try {
            $this->db->beginTransaction();
            if ((int)$trimestre >= 1 && (int)$trimestre <= 4 && (int)$formularioId > 0) {
                $this->db->prepare("UPDATE snapshots SET formulario_id = NULL, trimestre = NULL WHERE formulario_id = :f AND trimestre = :t AND id != :id")
                    ->execute([':f' => (int)$formularioId, ':t' => (int)$trimestre, ':id' => (int)$id]);
                $this->db->prepare("UPDATE snapshots SET formulario_id = :f, trimestre = :t WHERE id = :id")
                    ->execute([':f' => (int)$formularioId, ':t' => (int)$trimestre, ':id' => (int)$id]);
            } else {
                $this->db->prepare("UPDATE snapshots SET formulario_id = NULL, trimestre = NULL WHERE id = :id")->execute([':id' => (int)$id]);
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // Sin esto, un fallo a medias dejaba al snapshot anterior sin trimestre y al nuevo sin asignar
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Error asignarTrimestre: " . $e->getMessage());
            return false;
        }
    }

    public function crear($nombre, $usuarioId, $formularioId = null, $trimestre = null) {
        $tablas = $this->getNombresTablas();

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO snapshots (nombre, creado_por) VALUES (:nombre, :creado_por)");
            $stmt->execute([':nombre' => $nombre, ':creado_por' => $usuarioId ?: null]);
            $snapshotId = (int)$this->db->lastInsertId();

            $insertar = $this->db->prepare(
                "INSERT INTO snapshots_datos (snapshot_id, tabla, filas, datos) VALUES (:snapshot_id, :tabla, :filas, :datos)"
            );
            foreach ($tablas as $tabla) {
                $filas = $this->db->query("SELECT * FROM `" . str_replace('`', '``', $tabla) . "`")->fetchAll();
                $insertar->execute([
                    ':snapshot_id' => $snapshotId,
                    ':tabla'       => $tabla,
                    ':filas'       => count($filas),
                    ':datos'       => json_encode($filas, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
                ]);
            }

            $this->db->commit();
            if ($formularioId && $trimestre) $this->asignarTrimestre($snapshotId, $formularioId, $trimestre);
            return $snapshotId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function eliminar($id) {
        try {
            $this->db->beginTransaction();
            $this->db->prepare("DELETE FROM snapshots_datos WHERE snapshot_id = :id")->execute([':id' => $id]);
            $this->db->prepare("DELETE FROM snapshots WHERE id = :id")->execute([':id' => $id]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error eliminar snapshot: " . $e->getMessage());
            return false;
        }
    }

    // Todas las tablas del snapshot como [tabla => filas], para descargarlo
    public function getDatos($id) {
        $stmt = $this->db->prepare("SELECT tabla, datos FROM snapshots_datos WHERE snapshot_id = :id ORDER BY tabla");
        $stmt->execute([':id' => $id]);
        $tablas = [];
        foreach ($stmt->fetchAll() as $fila) {
            $tablas[$fila['tabla']] = json_decode($fila['datos'], true) ?? [];
        }
        return $tablas;
    }

    // Tablas base de la base de datos (no vistas), leídas de INFORMATION_SCHEMA para que un
    // snapshot incluya siempre cualquier tabla nueva que se agregue después
    private function getNombresTablas() {
        $stmt = $this->db->query(
            "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'
             ORDER BY TABLE_NAME"
        );
        return array_values(array_diff(array_column($stmt->fetchAll(), 'TABLE_NAME'), self::TABLAS_EXCLUIDAS));
    }
}
