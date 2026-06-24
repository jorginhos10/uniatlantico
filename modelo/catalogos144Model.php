<?php
// modelo/catalogos144Model.php
// CRUD de los catalogos del plan estrategico FOR-DE-144: lineas_estrategicas, motores, proyectos
require_once 'config/config.php';

class Catalogos144Model {
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
            error_log("Error de conexion en Catalogos144Model: " . $e->getMessage());
        }
    }

    // ============= LINEAS ESTRATEGICAS =============

    public function getAllLineas() {
        try {
            $stmt = $this->db->query(
                "SELECT id, codigo, nombre, objetivo, activo, fecha_creacion
                 FROM lineas_estrategicas ORDER BY codigo ASC"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAllLineas: " . $e->getMessage());
            return [];
        }
    }

    public function getLineaById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM lineas_estrategicas WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getLineaById: " . $e->getMessage());
            return null;
        }
    }

    public function crearLinea($codigo, $nombre, $objetivo, $activo = 1) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO lineas_estrategicas (codigo, nombre, objetivo, activo)
                 VALUES (:codigo, :nombre, :objetivo, :activo)"
            );
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':objetivo' => $objetivo,
                ':activo' => $activo,
            ]);
            return ['success' => true, 'message' => 'Linea estrategica creada exitosamente', 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error crearLinea: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear la linea estrategica'];
        }
    }

    public function actualizarLinea($id, $codigo, $nombre, $objetivo, $activo) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE lineas_estrategicas
                 SET codigo = :codigo, nombre = :nombre, objetivo = :objetivo, activo = :activo
                 WHERE id = :id"
            );
            $stmt->execute([
                ':id' => $id,
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':objetivo' => $objetivo,
                ':activo' => $activo,
            ]);
            return ['success' => true, 'message' => 'Linea estrategica actualizada exitosamente'];
        } catch (PDOException $e) {
            error_log("Error actualizarLinea: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar la linea estrategica'];
        }
    }

    public function cambiarEstadoLinea($id, $activo) {
        try {
            $stmt = $this->db->prepare("UPDATE lineas_estrategicas SET activo = :activo WHERE id = :id");
            $stmt->execute([':id' => $id, ':activo' => $activo]);
            return ['success' => true, 'message' => $activo ? 'Linea activada' : 'Linea desactivada'];
        } catch (PDOException $e) {
            error_log("Error cambiarEstadoLinea: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar el estado'];
        }
    }

    public function eliminarLinea($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM lineas_estrategicas WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return ['success' => true, 'message' => 'Linea estrategica eliminada exitosamente'];
        } catch (PDOException $e) {
            error_log("Error eliminarLinea: " . $e->getMessage());
            return ['success' => false, 'message' => 'No se puede eliminar: tiene motores o proyectos asociados'];
        }
    }

    // ============= ESTRATEGIAS =============

    public function getAllEstrategias() {
        try {
            $stmt = $this->db->query(
                "SELECT e.id, e.linea_id, e.descripcion, e.activo, e.fecha_creacion,
                        l.codigo AS linea_codigo, l.nombre AS linea_nombre
                 FROM estrategias e
                 INNER JOIN lineas_estrategicas l ON e.linea_id = l.id
                 ORDER BY l.codigo ASC, e.id ASC"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAllEstrategias: " . $e->getMessage());
            return [];
        }
    }

    public function getEstrategiaById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM estrategias WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getEstrategiaById: " . $e->getMessage());
            return null;
        }
    }

    public function crearEstrategia($linea_id, $descripcion, $activo = 1) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO estrategias (linea_id, descripcion, activo)
                 VALUES (:linea_id, :descripcion, :activo)"
            );
            $stmt->execute([
                ':linea_id' => $linea_id,
                ':descripcion' => $descripcion,
                ':activo' => $activo,
            ]);
            return ['success' => true, 'message' => 'Estrategia creada exitosamente', 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error crearEstrategia: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear la estrategia'];
        }
    }

    public function actualizarEstrategia($id, $linea_id, $descripcion, $activo) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE estrategias
                 SET linea_id = :linea_id, descripcion = :descripcion, activo = :activo
                 WHERE id = :id"
            );
            $stmt->execute([
                ':id' => $id,
                ':linea_id' => $linea_id,
                ':descripcion' => $descripcion,
                ':activo' => $activo,
            ]);
            return ['success' => true, 'message' => 'Estrategia actualizada exitosamente'];
        } catch (PDOException $e) {
            error_log("Error actualizarEstrategia: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar la estrategia'];
        }
    }

    public function cambiarEstadoEstrategia($id, $activo) {
        try {
            $stmt = $this->db->prepare("UPDATE estrategias SET activo = :activo WHERE id = :id");
            $stmt->execute([':id' => $id, ':activo' => $activo]);
            return ['success' => true, 'message' => $activo ? 'Estrategia activada' : 'Estrategia desactivada'];
        } catch (PDOException $e) {
            error_log("Error cambiarEstadoEstrategia: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar el estado'];
        }
    }

    public function eliminarEstrategia($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM estrategias WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return ['success' => true, 'message' => 'Estrategia eliminada exitosamente'];
        } catch (PDOException $e) {
            error_log("Error eliminarEstrategia: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar la estrategia'];
        }
    }

    // ============= MOTORES =============

    public function getAllMotores() {
        try {
            $stmt = $this->db->query(
                "SELECT m.id, m.linea_id, m.nombre, m.ponderacion, m.activo,
                        l.codigo AS linea_codigo, l.nombre AS linea_nombre
                 FROM motores m
                 INNER JOIN lineas_estrategicas l ON m.linea_id = l.id
                 ORDER BY l.codigo ASC, m.nombre ASC"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAllMotores: " . $e->getMessage());
            return [];
        }
    }

    public function getMotorById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM motores WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getMotorById: " . $e->getMessage());
            return null;
        }
    }

    public function crearMotor($linea_id, $nombre, $ponderacion, $activo = 1) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO motores (linea_id, nombre, ponderacion, activo)
                 VALUES (:linea_id, :nombre, :ponderacion, :activo)"
            );
            $stmt->execute([
                ':linea_id' => $linea_id,
                ':nombre' => $nombre,
                ':ponderacion' => $ponderacion,
                ':activo' => $activo,
            ]);
            return ['success' => true, 'message' => 'Motor creado exitosamente', 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error crearMotor: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear el motor'];
        }
    }

    public function actualizarMotor($id, $linea_id, $nombre, $ponderacion, $activo) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE motores
                 SET linea_id = :linea_id, nombre = :nombre, ponderacion = :ponderacion, activo = :activo
                 WHERE id = :id"
            );
            $stmt->execute([
                ':id' => $id,
                ':linea_id' => $linea_id,
                ':nombre' => $nombre,
                ':ponderacion' => $ponderacion,
                ':activo' => $activo,
            ]);
            return ['success' => true, 'message' => 'Motor actualizado exitosamente'];
        } catch (PDOException $e) {
            error_log("Error actualizarMotor: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el motor'];
        }
    }

    public function cambiarEstadoMotor($id, $activo) {
        try {
            $stmt = $this->db->prepare("UPDATE motores SET activo = :activo WHERE id = :id");
            $stmt->execute([':id' => $id, ':activo' => $activo]);
            return ['success' => true, 'message' => $activo ? 'Motor activado' : 'Motor desactivado'];
        } catch (PDOException $e) {
            error_log("Error cambiarEstadoMotor: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar el estado'];
        }
    }

    public function eliminarMotor($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM motores WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return ['success' => true, 'message' => 'Motor eliminado exitosamente'];
        } catch (PDOException $e) {
            error_log("Error eliminarMotor: " . $e->getMessage());
            return ['success' => false, 'message' => 'No se puede eliminar: tiene proyectos asociados'];
        }
    }

    // ============= PROYECTOS =============

    public function getAllProyectos() {
        try {
            $stmt = $this->db->query(
                "SELECT p.id, p.linea_id, p.motor_id, p.codigo, p.nombre, p.activo,
                        l.codigo AS linea_codigo, l.nombre AS linea_nombre,
                        m.nombre AS motor_nombre
                 FROM proyectos p
                 INNER JOIN lineas_estrategicas l ON p.linea_id = l.id
                 INNER JOIN motores m ON p.motor_id = m.id
                 ORDER BY l.codigo ASC, m.nombre ASC, p.codigo ASC"
            );
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAllProyectos: " . $e->getMessage());
            return [];
        }
    }

    public function getProyectoById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM proyectos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getProyectoById: " . $e->getMessage());
            return null;
        }
    }

    public function crearProyecto($linea_id, $motor_id, $codigo, $nombre, $activo = 1) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO proyectos (linea_id, motor_id, codigo, nombre, activo)
                 VALUES (:linea_id, :motor_id, :codigo, :nombre, :activo)"
            );
            $stmt->execute([
                ':linea_id' => $linea_id,
                ':motor_id' => $motor_id,
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':activo' => $activo,
            ]);
            return ['success' => true, 'message' => 'Proyecto creado exitosamente', 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error crearProyecto: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear el proyecto'];
        }
    }

    public function actualizarProyecto($id, $linea_id, $motor_id, $codigo, $nombre, $activo) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE proyectos
                 SET linea_id = :linea_id, motor_id = :motor_id, codigo = :codigo, nombre = :nombre, activo = :activo
                 WHERE id = :id"
            );
            $stmt->execute([
                ':id' => $id,
                ':linea_id' => $linea_id,
                ':motor_id' => $motor_id,
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':activo' => $activo,
            ]);
            return ['success' => true, 'message' => 'Proyecto actualizado exitosamente'];
        } catch (PDOException $e) {
            error_log("Error actualizarProyecto: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el proyecto'];
        }
    }

    public function cambiarEstadoProyecto($id, $activo) {
        try {
            $stmt = $this->db->prepare("UPDATE proyectos SET activo = :activo WHERE id = :id");
            $stmt->execute([':id' => $id, ':activo' => $activo]);
            return ['success' => true, 'message' => $activo ? 'Proyecto activado' : 'Proyecto desactivado'];
        } catch (PDOException $e) {
            error_log("Error cambiarEstadoProyecto: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar el estado'];
        }
    }

    public function eliminarProyecto($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM proyectos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return ['success' => true, 'message' => 'Proyecto eliminado exitosamente'];
        } catch (PDOException $e) {
            error_log("Error eliminarProyecto: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar el proyecto'];
        }
    }

    // ============= AUXILIARES PARA LOS SELECTS DE LOS MODALES =============

    public function getLineasActivas() {
        try {
            $stmt = $this->db->query("SELECT id, codigo, nombre FROM lineas_estrategicas WHERE activo = 1 ORDER BY codigo ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getMotoresPorLinea($linea_id) {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre FROM motores WHERE linea_id = :linea_id AND activo = 1 ORDER BY nombre ASC");
            $stmt->execute([':linea_id' => $linea_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
