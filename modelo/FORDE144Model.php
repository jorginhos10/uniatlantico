<?php 
// modelo/FORDE144Model.php - VERSIÓN PRODUCCIÓN
require_once 'config/config.php';

class FORDE144Model {
    private $db;
    private $table = 'formularios';

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
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            die("Error en el sistema. Por favor, intente más tarde.");
        }
    }

    /**
     * Obtiene todos los formularios activos
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE estado = 1 ORDER BY fecha_creacion DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un formulario por ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo formulario
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO " . $this->table . " (titulo, descripcion, estado) 
                    VALUES (:titulo, :descripcion, :estado)";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':estado' => $data['estado']
            ]);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el último ID insertado
     */
    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }

    /**
     * Actualiza un formulario existente
     */
    public function update($id, $data) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET titulo = :titulo, 
                        descripcion = :descripcion, 
                        estado = :estado,
                        updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':id' => $id,
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':estado' => $data['estado']
            ]);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina (cambia estado a 0) un formulario
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("UPDATE " . $this->table . " SET estado = 0 WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }
}
?>