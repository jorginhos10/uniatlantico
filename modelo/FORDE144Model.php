<?php 
<<<<<<< Updated upstream
// modelo/FORDE144Model.php - VERSIÓN PRODUCCIÓN
=======
// modelo/FORDE144Model.php - VERSIÓN COMPLETA
>>>>>>> Stashed changes
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

<<<<<<< Updated upstream
    /**
     * Obtiene todos los formularios activos (filtrados por tiempo)
     */
=======
>>>>>>> Stashed changes
    public function getAll() {
        try {
            $sql = "SELECT *, 
                    CASE 
                        WHEN tipo_tiempo = 'libre' THEN 1
                        WHEN tipo_tiempo = 'rango' AND NOW() BETWEEN fecha_inicio AND fecha_fin THEN 1
                        ELSE 0
                    END as disponible
                    FROM " . $this->table . " 
                    WHERE estado = 1 
                    ORDER BY fecha_creacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAll: " . $e->getMessage());
            return [];
        }
    }

<<<<<<< Updated upstream
    /**
     * Obtiene todos los formularios (para administración)
     */
    public function getAllAdmin() {
        try {
            $sql = "SELECT *, 
                    CASE 
                        WHEN tipo_tiempo = 'libre' THEN 'Siempre disponible'
                        WHEN tipo_tiempo = 'rango' AND NOW() BETWEEN fecha_inicio AND fecha_fin THEN 'Disponible'
                        WHEN tipo_tiempo = 'rango' AND NOW() < fecha_inicio THEN 'Próximamente'
                        WHEN tipo_tiempo = 'rango' AND NOW() > fecha_fin THEN 'Finalizado'
                        ELSE 'Estado especial'
                    END as estado_tiempo
                    FROM " . $this->table . " 
                    ORDER BY fecha_creacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAllAdmin: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un formulario por ID
     */
=======
>>>>>>> Stashed changes
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

    public function create($data) {
        try {
            $sql = "INSERT INTO " . $this->table . " 
                    (titulo, descripcion, tipo_tiempo, fecha_inicio, fecha_fin, estado) 
                    VALUES (:titulo, :descripcion, :tipo_tiempo, :fecha_inicio, :fecha_fin, :estado)";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':tipo_tiempo' => $data['tipo_tiempo'],
                ':fecha_inicio' => $data['fecha_inicio'] ?? null,
                ':fecha_fin' => $data['fecha_fin'] ?? null,
                ':estado' => $data['estado']
            ]);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }

    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        try {
            $sql = "UPDATE " . $this->table . " 
                    SET titulo = :titulo, 
                        descripcion = :descripcion,
                        tipo_tiempo = :tipo_tiempo,
                        fecha_inicio = :fecha_inicio,
                        fecha_fin = :fecha_fin,
                        estado = :estado,
                        updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':id' => $id,
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':tipo_tiempo' => $data['tipo_tiempo'],
                ':fecha_inicio' => $data['fecha_inicio'] ?? null,
                ':fecha_fin' => $data['fecha_fin'] ?? null,
                ':estado' => $data['estado']
            ]);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

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

<<<<<<< Updated upstream
    /**
     * Verifica si un formulario está disponible según su configuración de tiempo
     */
    public function isDisponible($id) {
=======
    public function verificarDisponibilidad($id) {
>>>>>>> Stashed changes
        try {
            $stmt = $this->db->prepare("SELECT 
                    CASE 
                        WHEN tipo_tiempo = 'libre' THEN 1
                        WHEN tipo_tiempo = 'rango' AND NOW() BETWEEN fecha_inicio AND fecha_fin THEN 1
                        ELSE 0
                    END as disponible
                    FROM " . $this->table . " 
                    WHERE id = :id AND estado = 1");
            
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch();
            
            return $result ? $result['disponible'] : false;
        } catch (PDOException $e) {
            error_log("Error en isDisponible: " . $e->getMessage());
            return false;
        }
    }
}
?>