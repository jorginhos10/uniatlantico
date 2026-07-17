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
     * Obtiene todos los formularios activos (filtrados por tiempo)
     */
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
                    (titulo, descripcion, tipo_tiempo, fecha_inicio, fecha_fin, estado, anio) 
                    VALUES (:titulo, :descripcion, :tipo_tiempo, :fecha_inicio, :fecha_fin, :estado, :anio)";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':tipo_tiempo' => $data['tipo_tiempo'],
                ':fecha_inicio' => $data['fecha_inicio'] ?? null,
                ':fecha_fin' => $data['fecha_fin'] ?? null,
                ':estado' => $data['estado'],
                ':anio' => $data['anio'] ?? null
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
                        anio = :anio,
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
                ':estado' => $data['estado'],
                ':anio' => $data['anio'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    public function getAdministradores($formulario_id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT fa.id, u.id AS usuario_id, u.nombre, u.email, u.rol
                 FROM formulario_administradores fa
                 INNER JOIN usuarios u ON fa.usuario_id = u.id
                 WHERE fa.formulario_id = :fid
                 ORDER BY u.nombre"
            );
            $stmt->execute([':fid' => $formulario_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAdministradores: " . $e->getMessage());
            return [];
        }
    }

    public function agregarAdministrador($formulario_id, $usuario_id) {
        try {
            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO formulario_administradores (formulario_id, usuario_id) VALUES (:fid, :uid)"
            );
            return $stmt->execute([':fid' => $formulario_id, ':uid' => $usuario_id]);
        } catch (PDOException $e) {
            error_log("Error agregarAdministrador: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarAdministrador($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM formulario_administradores WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error eliminarAdministrador: " . $e->getMessage());
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

    /**
     * Obtiene los años disponibles desde la tabla ano-for-de-144
     */
    public function getAnios() {
        try {
            $stmt = $this->db->prepare("SELECT id, anio FROM `ano-for-de-144` WHERE activo = 1 ORDER BY orden ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en getAnios: " . $e->getMessage());
            return [];
        }
    }

    public function getInformeCompleto($formulario_id) {
        // porcentaje_avance (0-100) is the user-entered progress field in formulacion_144
        $calc = "LEAST(COALESCE(f.porcentaje_avance, 0), 100)";

        try {
            $stmt = $this->db->prepare(
                "SELECT
                    COALESCE(f.linea_estrategica,'Sin línea') as linea,
                    COALESCE(le.codigo,'—') as linea_codigo,
                    COALESCE(f.motor_desarrollo,'Sin motor') as motor,
                    COALESCE(f.proyecto,'Sin proyecto') as proyecto,
                    COUNT(f.id) as total,
                    ROUND(AVG($calc),2) as cumplimiento_proyecto
                 FROM formulacion_144 f
                 LEFT JOIN lineas_estrategicas le
                        ON f.linea_estrategica = le.nombre AND le.activo = 1
                 WHERE f.formulario_id = :fid
                 GROUP BY f.linea_estrategica, le.codigo, f.motor_desarrollo, f.proyecto
                 ORDER BY le.codigo ASC, f.linea_estrategica ASC, f.motor_desarrollo ASC, f.proyecto ASC"
            );
            $stmt->execute([':fid' => $formulario_id]);
            $rows = $stmt->fetchAll();

            $lineasMap = [];
            foreach ($rows as $row) {
                $lKey = $row['linea'];
                $mKey = $row['motor'];
                if (!isset($lineasMap[$lKey])) {
                    $lineasMap[$lKey] = ['codigo' => $row['linea_codigo'], 'titulo' => $lKey, 'motores' => [], '_vals' => []];
                }
                if (!isset($lineasMap[$lKey]['motores'][$mKey])) {
                    $lineasMap[$lKey]['motores'][$mKey] = ['nombre' => $mKey, 'proyectos' => [], '_vals' => []];
                }
                $pct = (float)$row['cumplimiento_proyecto'];
                $lineasMap[$lKey]['motores'][$mKey]['proyectos'][] = [
                    'nombre' => $row['proyecto'], 'total' => (int)$row['total'], 'cumplimiento' => $pct
                ];
                $lineasMap[$lKey]['motores'][$mKey]['_vals'][] = $pct;
                $lineasMap[$lKey]['_vals'][] = $pct;
            }

            $lineas = [];
            foreach ($lineasMap as $lKey => $linea) {
                $motores = [];
                foreach ($linea['motores'] as $motor) {
                    $mc = count($motor['_vals']) > 0 ? array_sum($motor['_vals']) / count($motor['_vals']) : 0;
                    $motores[] = ['nombre' => $motor['nombre'], 'cumplimiento' => round($mc, 2), 'proyectos' => $motor['proyectos']];
                }
                $lc = count($linea['_vals']) > 0 ? array_sum($linea['_vals']) / count($linea['_vals']) : 0;
                $titulo = ($linea['codigo'] !== '—') ? $linea['codigo'] . ' - ' . $lKey : $lKey;
                $lineas[] = ['codigo' => $linea['codigo'], 'titulo' => $titulo, 'cumplimiento' => round($lc, 2), 'motores' => $motores];
            }

            $global = count($lineas) > 0 ? array_sum(array_column($lineas, 'cumplimiento')) / count($lineas) : 0;

            $stmtD = $this->db->prepare(
                "SELECT
                    COALESCE(fa.nombre, CONCAT('Dependencia #', f.facultad_id)) as dependencia,
                    COUNT(f.id) as total_indicadores,
                    SUM(CASE WHEN ($calc) >= 80 THEN 1 ELSE 0 END) as indicadores_80,
                    ROUND(AVG($calc),2) as cumplimiento
                 FROM formulacion_144 f
                 LEFT JOIN facultades fa ON f.facultad_id = fa.id
                 WHERE f.formulario_id = :fid
                 GROUP BY f.facultad_id, fa.nombre
                 ORDER BY cumplimiento DESC"
            );
            $stmtD->execute([':fid' => $formulario_id]);
            $dependencias = $stmtD->fetchAll();

            return [
                'global_cumplimiento' => round($global, 2),
                'lineas'              => $lineas,
                'dependencias'        => $dependencias,
            ];
        } catch (PDOException $e) {
            error_log("Error getInformeCompleto: " . $e->getMessage());
            return ['global_cumplimiento' => 0, 'lineas' => [], 'dependencias' => [], 'error' => $e->getMessage()];
        }
    }

    public function getInforme($formulario_id) {
        $calc = "LEAST(COALESCE(f.porcentaje_avance, 0), 100)";

        try {
            $stmtG = $this->db->prepare(
                "SELECT COUNT(f.id) as total, ROUND(AVG($calc),2) as cumplimiento_global
                 FROM formulacion_144 f WHERE f.formulario_id = :fid"
            );
            $stmtG->execute([':fid' => $formulario_id]);
            $global = $stmtG->fetch();

            $stmtL = $this->db->prepare(
                "SELECT COALESCE(f.linea_estrategica,'Sin línea') as linea,
                        COALESCE(le.codigo,'—') as codigo,
                        COUNT(f.id) as total,
                        ROUND(AVG($calc),2) as cumplimiento
                 FROM formulacion_144 f
                 LEFT JOIN lineas_estrategicas le
                        ON f.linea_estrategica = le.nombre AND le.activo = 1
                 WHERE f.formulario_id = :fid
                 GROUP BY f.linea_estrategica, le.codigo
                 ORDER BY le.codigo ASC, f.linea_estrategica"
            );
            $stmtL->execute([':fid' => $formulario_id]);
            $lineas = $stmtL->fetchAll();

            $stmtD = $this->db->prepare(
                "SELECT COALESCE(fa.nombre, CONCAT('Dependencia #', f.facultad_id)) as dependencia,
                        COUNT(f.id) as total_indicadores,
                        SUM(CASE WHEN ($calc) >= 80 THEN 1 ELSE 0 END) as indicadores_alto,
                        ROUND(AVG($calc),2) as cumplimiento
                 FROM formulacion_144 f
                 LEFT JOIN facultades fa ON f.facultad_id = fa.id
                 WHERE f.formulario_id = :fid
                 GROUP BY f.facultad_id, fa.nombre
                 ORDER BY cumplimiento DESC"
            );
            $stmtD->execute([':fid' => $formulario_id]);
            $dependencias = $stmtD->fetchAll();

            return [
                'global'       => $global,
                'lineas'       => $lineas,
                'dependencias' => $dependencias,
            ];
        } catch (PDOException $e) {
            error_log("Error getInforme: " . $e->getMessage());
            return ['global' => ['total' => 0, 'cumplimiento_global' => 0], 'lineas' => [], 'dependencias' => []];
        }
    }

    /**
     * Verifica si un formulario está disponible según su configuración de tiempo
     */
    public function isDisponible($id) {
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