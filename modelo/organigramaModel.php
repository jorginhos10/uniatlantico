<?php
// modelo/organigramaModel.php
require_once 'config/config.php';

class OrganigramaModel {
    private $db;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
            $this->db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error conexión OrganigramaModel: " . $e->getMessage());
        }
    }

    public function getAll() {
        try {
            $stmt = $this->db->query(
                "SELECT n.id, n.nombre, n.padre_id, n.rol, n.orden, n.activo, p.nombre AS nombre_padre
                 FROM organigrama_nodos n
                 LEFT JOIN organigrama_nodos p ON p.id = n.padre_id
                 ORDER BY n.orden ASC, n.nombre ASC"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAll organigrama: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM organigrama_nodos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Error getById organigrama: " . $e->getMessage());
            return null;
        }
    }

    public function crear($nombre, $padreId, $rol, $orden) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO organigrama_nodos (nombre, padre_id, rol, orden) VALUES (:nombre, :padre_id, :rol, :orden)"
            );
            $stmt->execute([':nombre' => $nombre, ':padre_id' => $padreId, ':rol' => $rol, ':orden' => $orden]);
            return true;
        } catch (PDOException $e) {
            error_log("Error crear organigrama: " . $e->getMessage());
            return false;
        }
    }

    public function actualizar($id, $nombre, $padreId, $rol, $orden) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE organigrama_nodos SET nombre = :nombre, padre_id = :padre_id, rol = :rol, orden = :orden WHERE id = :id"
            );
            $stmt->execute([':nombre' => $nombre, ':padre_id' => $padreId, ':rol' => $rol, ':orden' => $orden, ':id' => $id]);
            return true;
        } catch (PDOException $e) {
            error_log("Error actualizar organigrama: " . $e->getMessage());
            return false;
        }
    }

    public function cambiarEstado($id) {
        try {
            $stmt = $this->db->prepare("UPDATE organigrama_nodos SET activo = IF(activo = 1, 0, 1) WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            error_log("Error cambiarEstado organigrama: " . $e->getMessage());
            return false;
        }
    }

    public function tieneHijos($id) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM organigrama_nodos WHERE padre_id = :id");
            $stmt->execute([':id' => $id]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error tieneHijos organigrama: " . $e->getMessage());
            return true;
        }
    }

    public function eliminar($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM organigrama_nodos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            error_log("Error eliminar organigrama: " . $e->getMessage());
            return false;
        }
    }
}
