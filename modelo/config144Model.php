<?php
// modelo/config144Model.php
require_once 'config/config.php';

class config144Model {
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
            return $this->db;
        } catch (PDOException $e) {
            error_log("Error de conexión en config144Model: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los años
     */
    public function getAnos() {
        try {
            $stmt = $this->db->prepare("SELECT id, anio, activo, orden, created_at FROM `ano-for-de-144` ORDER BY orden ASC, anio ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAnos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un año por ID
     */
    public function getAnoById($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, anio, activo, orden, created_at FROM `ano-for-de-144` WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getAnoById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crear un nuevo año
     */
    public function crearAno($anio, $activo = 1, $orden = null) {
        try {
            // Verificar si el año ya existe
            $checkStmt = $this->db->prepare("SELECT id FROM `ano-for-de-144` WHERE anio = :anio");
            $checkStmt->execute([':anio' => $anio]);
            if ($checkStmt->fetch()) {
                return ['success' => false, 'message' => 'El año ' . $anio . ' ya existe en la base de datos'];
            }

            // Si no se proporciona orden, obtener el máximo orden + 1
            if ($orden === null || $orden === '') {
                $maxOrdenStmt = $this->db->query("SELECT MAX(orden) as max_orden FROM `ano-for-de-144`");
                $maxOrden = $maxOrdenStmt->fetch()['max_orden'];
                $orden = ($maxOrden !== null) ? $maxOrden + 1 : 1;
            }

            $stmt = $this->db->prepare("INSERT INTO `ano-for-de-144` (anio, activo, orden) VALUES (:anio, :activo, :orden)");
            $resultado = $stmt->execute([
                ':anio' => $anio,
                ':activo' => $activo,
                ':orden' => $orden
            ]);

            if ($resultado) {
                return ['success' => true, 'message' => 'Año creado exitosamente', 'id' => $this->db->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Error al crear el año'];
            }
        } catch (PDOException $e) {
            error_log("Error crearAno: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar un año existente
     */
    public function actualizarAno($id, $anio, $activo = null, $orden = null) {
        try {
            // Verificar si el año ya existe en otro registro
            $checkStmt = $this->db->prepare("SELECT id FROM `ano-for-de-144` WHERE anio = :anio AND id != :id");
            $checkStmt->execute([':anio' => $anio, ':id' => $id]);
            if ($checkStmt->fetch()) {
                return ['success' => false, 'message' => 'El año ' . $anio . ' ya existe en otro registro'];
            }

            $sets = [];
            $params = [':id' => $id];

            $sets[] = "anio = :anio";
            $params[':anio'] = $anio;

            if ($activo !== null) {
                $sets[] = "activo = :activo";
                $params[':activo'] = $activo;
            }

            if ($orden !== null && $orden !== '') {
                $sets[] = "orden = :orden";
                $params[':orden'] = $orden;
            }

            $sql = "UPDATE `ano-for-de-144` SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute($params);

            return [
                'success' => $resultado,
                'message' => $resultado ? 'Año actualizado exitosamente' : 'Error al actualizar el año'
            ];
        } catch (PDOException $e) {
            error_log("Error actualizarAno: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function cambiarEstado($id, $activo) {
        try {
            $stmt = $this->db->prepare("UPDATE `ano-for-de-144` SET activo = :activo WHERE id = :id");
            $resultado = $stmt->execute([
                ':id' => $id,
                ':activo' => $activo
            ]);

            $estadoTexto = $activo ? 'activado' : 'desactivado';
            return [
                'success' => $resultado,
                'message' => $resultado ? "Año $estadoTexto exitosamente" : 'Error al cambiar el estado'
            ];
        } catch (PDOException $e) {
            error_log("Error cambiarEstado: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos'];
        }
    }

    /**
     * Eliminar un año
     */
    public function eliminarAno($id) {
        try {
            // Verificar si el año está siendo usado en formulacion_144
            $checkStmt = $this->db->prepare("SELECT id FROM formulacion_144 WHERE anio = (SELECT anio FROM `ano-for-de-144` WHERE id = :id) LIMIT 1");
            $checkStmt->execute([':id' => $id]);
            if ($checkStmt->fetch()) {
                return ['success' => false, 'message' => 'No se puede eliminar el año porque está siendo usado en formulaciones'];
            }

            $stmt = $this->db->prepare("DELETE FROM `ano-for-de-144` WHERE id = :id");
            $resultado = $stmt->execute([':id' => $id]);

            return [
                'success' => $resultado,
                'message' => $resultado ? 'Año eliminado exitosamente' : 'Error al eliminar el año'
            ];
        } catch (PDOException $e) {
            error_log("Error eliminarAno: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener años activos para selects
     */
    public function getAnosActivos() {
        try {
            $stmt = $this->db->prepare("SELECT id, anio FROM `ano-for-de-144` WHERE activo = 1 ORDER BY orden ASC, anio ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAnosActivos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar el orden de un año específico
     */
    public function actualizarOrden($id, $orden) {
        try {
            $stmt = $this->db->prepare("UPDATE `ano-for-de-144` SET orden = :orden WHERE id = :id");
            $resultado = $stmt->execute([
                ':id' => $id,
                ':orden' => $orden
            ]);

            return [
                'success' => $resultado,
                'message' => $resultado ? 'Orden actualizado' : 'Error al actualizar orden'
            ];
        } catch (PDOException $e) {
            error_log("Error actualizarOrden: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Reordenar todos los años (para cuando se hace drag & drop)
     */
    public function reordenarAnos($ordenes) {
        try {
            $this->db->beginTransaction();
            
            foreach ($ordenes as $item) {
                $stmt = $this->db->prepare("UPDATE `ano-for-de-144` SET orden = :orden WHERE id = :id");
                $stmt->execute([
                    ':id' => $item['id'],
                    ':orden' => $item['orden']
                ]);
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Orden actualizado correctamente'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error reordenarAnos: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al reordenar: ' . $e->getMessage()];
        }
    }
}
?>