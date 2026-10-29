<?php
// modelo/dependenciasModel.php
require_once 'config/config.php';

class DependenciasModel {
    private $db;

    public function __construct() {
        $this->conectarDB();
    }

    private function conectarDB() {
        try {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
            $this->db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error de conexión en DependenciasModel: " . $e->getMessage());
        }
    }

    // Organigrama: cada dependencia reporta a otra (cargos.reporta_a_id), por ejemplo una dirección a su vicerrectoría
    private function asegurarColumnaReportaA() {
        if ($this->db->query("SHOW COLUMNS FROM cargos LIKE 'reporta_a_id'")->rowCount() === 0) {
            $this->db->exec("ALTER TABLE cargos ADD COLUMN reporta_a_id INT NULL DEFAULT NULL");
        }
    }

    // El superior debe existir y no puede ser la misma dependencia ni una que ya le reporta (evita ciclos)
    public function reportaAValido($id, $reportaA) {
        if (!$reportaA) return true;
        $id = (int)$id; $cur = (int)$reportaA;
        if ($cur === $id || !$this->getById($cur)) return false;
        for ($i = 0; $cur && $i < 50; $i++) {
            if ($cur === $id) return false;
            $cur = (int)($this->getById($cur)['reporta_a_id'] ?? 0);
        }
        return true;
    }

    public function getAll() {
        try {
            $this->asegurarColumnaReportaA();
            $stmt = $this->db->prepare(
                "SELECT c.id, c.nombre, c.activo, c.fecha_creacion, c.reporta_a_id, p.nombre AS reporta_a_nombre
                 FROM cargos c LEFT JOIN cargos p ON p.id = c.reporta_a_id
                 ORDER BY c.nombre ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAll cargos: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $this->asegurarColumnaReportaA();
            $stmt = $this->db->prepare(
                "SELECT id, nombre, activo, fecha_creacion, reporta_a_id
                 FROM cargos WHERE id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getById cargo: " . $e->getMessage());
            return null;
        }
    }

    public function crear($nombre, $activo, $reportaA = null) {
        try {
            $this->asegurarColumnaReportaA();
            if ($reportaA && !$this->reportaAValido(0, $reportaA)) return ['success' => false, 'message' => 'La dependencia superior no es válida'];
            $stmt = $this->db->prepare(
                "INSERT INTO cargos (nombre, activo, reporta_a_id, fecha_creacion)
                 VALUES (?, ?, ?, NOW())"
            );
            $stmt->execute([$nombre, $activo, $reportaA ?: null]);
            return ['success' => true, 'message' => 'Cargo creado exitosamente', 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error crear cargo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear el cargo'];
        }
    }

    public function actualizar($id, $nombre, $activo, $reportaA = null) {
        try {
            $this->asegurarColumnaReportaA();
            if (!$this->reportaAValido($id, $reportaA)) return ['success' => false, 'message' => 'La dependencia superior no es válida: no puede reportarse a sí misma ni a una que depende de ella'];
            $stmt = $this->db->prepare(
                "UPDATE cargos SET nombre = ?, activo = ?, reporta_a_id = ?, fecha_actualizacion = NOW()
                 WHERE id = ?"
            );
            $stmt->execute([$nombre, $activo, $reportaA ?: null, $id]);
            return ['success' => true, 'message' => 'Cargo actualizado exitosamente'];
        } catch (PDOException $e) {
            error_log("Error actualizar cargo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el cargo'];
        }
    }

    public function eliminar($id) {
        try {
            $this->asegurarColumnaReportaA();
            $this->db->prepare("UPDATE cargos SET reporta_a_id = NULL WHERE reporta_a_id = ?")->execute([$id]);
            $stmt = $this->db->prepare("DELETE FROM cargos WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Cargo eliminado exitosamente'];
        } catch (PDOException $e) {
            error_log("Error eliminar cargo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar el cargo'];
        }
    }

    public function cambiarEstado($id, $activo) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE cargos SET activo = ?, fecha_actualizacion = NOW() WHERE id = ?"
            );
            $stmt->execute([$activo, $id]);
            $msg = $activo ? 'Cargo activado' : 'Cargo desactivado';
            return ['success' => true, 'message' => $msg];
        } catch (PDOException $e) {
            error_log("Error cambiarEstado cargo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar el estado'];
        }
    }
}
