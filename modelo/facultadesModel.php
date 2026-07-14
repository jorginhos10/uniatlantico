<?php
// modelo/facultadesModel.php
require_once 'config/config.php';

class FacultadesModel {
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
            error_log("Error de conexión en FacultadesModel: " . $e->getMessage());
        }
    }

    public function getAll() {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, codigo, nombre, estado
                 FROM facultades
                 ORDER BY codigo ASC, nombre ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAll facultades: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, codigo, nombre, estado
                 FROM facultades WHERE id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getById facultad: " . $e->getMessage());
            return null;
        }
    }

    public function crear($codigo, $nombre, $estado) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO facultades (codigo, nombre, estado)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$codigo, $nombre, $estado]);
            return ['success' => true, 'message' => 'Facultad creada exitosamente', 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error crear facultad: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear la facultad'];
        }
    }

    public function actualizar($id, $codigo, $nombre, $estado) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE facultades SET codigo = ?, nombre = ?, estado = ?
                 WHERE id = ?"
            );
            $stmt->execute([$codigo, $nombre, $estado, $id]);
            return ['success' => true, 'message' => 'Facultad actualizada exitosamente'];
        } catch (PDOException $e) {
            error_log("Error actualizar facultad: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar la facultad'];
        }
    }

    public function eliminar($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM facultades WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Facultad eliminada exitosamente'];
        } catch (PDOException $e) {
            error_log("Error eliminar facultad: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar la facultad'];
        }
    }

    public function cambiarEstado($id, $estado) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE facultades SET estado = ? WHERE id = ?"
            );
            $stmt->execute([$estado, $id]);
            $msg = $estado ? 'Facultad activada' : 'Facultad desactivada';
            return ['success' => true, 'message' => $msg];
        } catch (PDOException $e) {
            error_log("Error cambiarEstado facultad: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar el estado'];
        }
    }
}
