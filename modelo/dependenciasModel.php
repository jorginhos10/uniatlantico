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
            $this->eliminarFlujosDeDependencia($id);
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

    // ── FLUJO DE INFORMACIÓN: la dependencia origen envía a la destino, que la aprueba ──

    public function getFlujos() {
        try {
            $stmt = $this->db->prepare(
                "SELECT f.id, f.origen_id, f.destino_id, f.aplica_a, f.observacion,
                        o.nombre AS origen_nombre, d.nombre AS destino_nombre
                 FROM flujo_dependencias f
                 JOIN cargos o ON o.id = f.origen_id
                 JOIN cargos d ON d.id = f.destino_id
                 ORDER BY o.nombre ASC, d.nombre ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getFlujos: " . $e->getMessage());
            return [];
        }
    }

    // ¿Ya existe una relación entre las mismas dependencias que se cruce con "aplica_a"?
    // (ambos se cruza con formulación y con seguimiento)
    public function existeFlujo($origenId, $destinoId, $aplicaA, $exceptoId = 0) {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM flujo_dependencias
                 WHERE origen_id = ? AND destino_id = ? AND id <> ?
                   AND (aplica_a = ? OR aplica_a = 'ambos' OR ? = 'ambos')"
            );
            $stmt->execute([$origenId, $destinoId, $exceptoId, $aplicaA, $aplicaA]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error existeFlujo: " . $e->getMessage());
            return false;
        }
    }

    public function crearFlujo($origenId, $destinoId, $aplicaA, $observacion) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO flujo_dependencias (origen_id, destino_id, aplica_a, observacion) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$origenId, $destinoId, $aplicaA, $observacion]);
            return ['success' => true, 'message' => 'Relación agregada correctamente'];
        } catch (PDOException $e) {
            error_log("Error crearFlujo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al agregar la relación'];
        }
    }

    public function actualizarFlujo($id, $origenId, $destinoId, $aplicaA, $observacion) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE flujo_dependencias SET origen_id = ?, destino_id = ?, aplica_a = ?, observacion = ? WHERE id = ?"
            );
            $stmt->execute([$origenId, $destinoId, $aplicaA, $observacion, $id]);
            return ['success' => true, 'message' => 'Relación actualizada correctamente'];
        } catch (PDOException $e) {
            error_log("Error actualizarFlujo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar la relación'];
        }
    }

    public function eliminarFlujo($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM flujo_dependencias WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Relación eliminada correctamente'];
        } catch (PDOException $e) {
            error_log("Error eliminarFlujo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar la relación'];
        }
    }

    private function eliminarFlujosDeDependencia($cargoId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM flujo_dependencias WHERE origen_id = ? OR destino_id = ?");
            $stmt->execute([$cargoId, $cargoId]);
        } catch (PDOException $e) {
            // La tabla puede no existir todavía si no se ejecutó la migración
            error_log("Error eliminarFlujosDeDependencia: " . $e->getMessage());
        }
    }
}
