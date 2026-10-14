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
        } catch (PDOException $e) {
            error_log("Error conexión SnapshotModel: " . $e->getMessage());
        }
    }

    public function getAll() {
        try {
            $stmt = $this->db->query(
                "SELECT s.id, s.nombre, s.creado_en, u.nombre AS creado_por_nombre,
                        COUNT(sd.id) AS total_tablas,
                        COALESCE(SUM(sd.filas), 0) AS total_filas,
                        COALESCE(SUM(LENGTH(sd.datos)), 0) AS bytes
                 FROM snapshots s
                 LEFT JOIN usuarios u ON u.id = s.creado_por
                 LEFT JOIN snapshots_datos sd ON sd.snapshot_id = s.id
                 GROUP BY s.id, s.nombre, s.creado_en, u.nombre
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
            $stmt = $this->db->prepare("SELECT id, nombre, creado_por, creado_en FROM snapshots WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Error getById snapshot: " . $e->getMessage());
            return null;
        }
    }

    // Copia todas las tablas en una sola transacción: si algo falla, no queda un snapshot a medias.
    // Devuelve el id del snapshot creado; lanza la excepción si falla.
    public function crear($nombre, $usuarioId) {
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
