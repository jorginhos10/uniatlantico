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

    public function getAll() {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, nombre, activo, fecha_creacion
                 FROM cargos
                 ORDER BY nombre ASC"
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
            $stmt = $this->db->prepare(
                "SELECT id, nombre, activo, fecha_creacion
                 FROM cargos WHERE id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getById cargo: " . $e->getMessage());
            return null;
        }
    }

    public function crear($nombre, $activo) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO cargos (nombre, activo, fecha_creacion)
                 VALUES (?, ?, NOW())"
            );
            $stmt->execute([$nombre, $activo]);
            return ['success' => true, 'message' => 'Cargo creado exitosamente', 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error crear cargo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear el cargo'];
        }
    }

    public function actualizar($id, $nombre, $activo) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE cargos SET nombre = ?, activo = ?, fecha_actualizacion = NOW()
                 WHERE id = ?"
            );
            $stmt->execute([$nombre, $activo, $id]);
            return ['success' => true, 'message' => 'Cargo actualizado exitosamente'];
        } catch (PDOException $e) {
            error_log("Error actualizar cargo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el cargo'];
        }
    }

    public function eliminar($id) {
        try {
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
