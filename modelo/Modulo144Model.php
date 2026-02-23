<?php
// modelo/Modulo144Model.php
require_once 'config/config.php';

class Modulo144Model {
    private $db;
    
    // Definición de módulos disponibles
    private $modulos = [
        'formulacion' => [
            'tabla' => 'formulacion_144',
            'nombre' => 'FORMULACIÓN 144',
            'icono' => 'fa-clipboard-list',
            'color' => '#2C3E50',
            'color_header' => 'linear-gradient(135deg, #2C3E50 0%, #34495E 100%)',
            'descripcion' => 'Planificación y formulación estratégica',
            'campos_editables' => ['anio', 'linea_estrategica', 'objetivo', 'estrategia', 'motor_desarrollo', 
                                   'meta_resultado', 'proyecto', 'ponderacion_proyectos', 'actividad_proyecto', 
                                   'ponderacion_actividades', 'responsable'],
            'campos_vista' => [
                'AÑO' => 'anio',
                'LÍNEA ESTRATÉGICA' => 'linea_estrategica',
                'OBJETIVO' => 'objetivo',
                'ESTRATEGIA' => 'estrategia',
                'MOTOR DE DESARROLLO' => 'motor_desarrollo',
                'META DE RESULTADO' => 'meta_resultado',
                'PROYECTO' => 'proyecto',
                'PONDERACIÓN PROYECTOS' => 'ponderacion_proyectos',
                'ACTIVIDAD DEL PROYECTO' => 'actividad_proyecto',
                'PONDERACIÓN ACTIVIDADES' => 'ponderacion_actividades',
                'RESPONSABLE' => 'responsable'
            ]
        ],
        'seguimiento' => [
            'tabla' => 'seguimiento_144',
            'nombre' => 'SEGUIMIENTO 144',
            'icono' => 'fa-chart-line',
            'color' => '#27AE60',
            'color_header' => 'linear-gradient(135deg, #27AE60 0%, #2ECC71 100%)',
            'descripcion' => 'Seguimiento y monitoreo de avances',
            'campos_editables' => ['indicador', 'meta_programada', 'meta_ejecutada', 'porcentaje_avance', 
                                   'fecha_seguimiento', 'observaciones', 'responsable_seguimiento'],
            'campos_vista' => [
                'INDICADOR' => 'indicador',
                'META PROGRAMADA' => 'meta_programada',
                'META EJECUTADA' => 'meta_ejecutada',
                '% AVANCE' => 'porcentaje_avance',
                'FECHA SEGUIMIENTO' => 'fecha_seguimiento',
                'OBSERVACIONES' => 'observaciones',
                'RESPONSABLE' => 'responsable_seguimiento'
            ]
        ]
    ];

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
            error_log("Error de conexión: " . $e->getMessage());
            return false;
        }
    }

    public function getModulos() {
        return $this->modulos;
    }

    public function verificarFormulario($id) {
        try {
            $stmt = $this->db->prepare("SELECT *, 
                                        fecha_fin as fecha_cierre,
                                        NOW() as fecha_actual,
                                        TIMESTAMPDIFF(SECOND, NOW(), fecha_fin) as segundos_restantes,
                                        CASE 
                                            WHEN fecha_inicio IS NOT NULL AND NOW() < fecha_inicio THEN 'no_iniciado'
                                            WHEN fecha_fin IS NOT NULL AND NOW() > fecha_fin THEN 'expirado'
                                            WHEN fecha_inicio IS NOT NULL AND fecha_fin IS NOT NULL AND NOW() BETWEEN fecha_inicio AND fecha_fin THEN 'vigente'
                                            WHEN fecha_inicio IS NULL AND fecha_fin IS NULL THEN 'sin_fechas'
                                            ELSE 'vigente'
                                        END as estado_fecha
                                        FROM formularios 
                                        WHERE id = :id AND estado = 1");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error verificarFormulario: " . $e->getMessage());
            return null;
        }
    }

    public function verificarFechaHabil($formulario) {
        if (!$formulario) {
            return [
                'valido' => false,
                'mensaje' => 'Formulario no encontrado',
                'clase' => 'error'
            ];
        }

        $fecha_actual = date('Y-m-d H:i:s');
        
        if (empty($formulario['fecha_inicio']) && empty($formulario['fecha_fin'])) {
            return [
                'valido' => true,
                'mensaje' => 'Sin restricción de fechas',
                'clase' => 'sin-fechas'
            ];
        }

        if (!empty($formulario['fecha_inicio']) && !empty($formulario['fecha_fin'])) {
            if ($fecha_actual < $formulario['fecha_inicio']) {
                return [
                    'valido' => false,
                    'mensaje' => 'Disponible desde: ' . date('d/m/Y H:i', strtotime($formulario['fecha_inicio'])),
                    'clase' => 'no-iniciado'
                ];
            } elseif ($fecha_actual > $formulario['fecha_fin']) {
                return [
                    'valido' => false,
                    'mensaje' => '⚠️ EXPIRADO: ' . date('d/m/Y H:i', strtotime($formulario['fecha_fin'])),
                    'clase' => 'expirado'
                ];
            } else {
                return [
                    'valido' => true,
                    'mensaje' => 'Vigente hasta: ' . date('d/m/Y H:i', strtotime($formulario['fecha_fin'])),
                    'clase' => 'vigente'
                ];
            }
        }
        return ['valido' => true, 'mensaje' => 'Vigente', 'clase' => 'vigente'];
    }

    public function getByEstado($modulo, $formulario_id, $estado) {
        try {
            $tabla = $this->modulos[$modulo]['tabla'];
            $stmt = $this->db->prepare("SELECT * FROM {$tabla} 
                                        WHERE formulario_id = :formulario_id AND estado = :estado 
                                        ORDER BY fecha_creacion DESC");
            $stmt->execute([
                ':formulario_id' => $formulario_id,
                ':estado' => $estado
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getByEstado [{$modulo}]: " . $e->getMessage());
            return [];
        }
    }

    public function getBorradores($modulo, $formulario_id) {
        return $this->getByEstado($modulo, $formulario_id, 0);
    }

    public function getPublicados($modulo, $formulario_id) {
        return $this->getByEstado($modulo, $formulario_id, 2);
    }

    public function getCancelados($modulo, $formulario_id) {
        return $this->getByEstado($modulo, $formulario_id, 1);
    }

    public function crearBorrador($modulo, $formulario_id, $nombre_borrador, $creado_por = 1) {
        try {
            if (!isset($this->modulos[$modulo])) {
                error_log("Error: Módulo '$modulo' no existe");
                return false;
            }
            
            $tabla = $this->modulos[$modulo]['tabla'];
            
            $checkTable = $this->db->prepare("SHOW TABLES LIKE '{$tabla}'");
            $checkTable->execute();
            if ($checkTable->rowCount() == 0) {
                error_log("Error: La tabla '{$tabla}' no existe");
                return false;
            }
            
            $stmt = $this->db->prepare("INSERT INTO {$tabla} 
                                        (formulario_id, nombre_borrador, estado, creado_por) 
                                        VALUES (:formulario_id, :nombre, 0, :creado_por)");
            
            return $stmt->execute([
                ':formulario_id' => $formulario_id,
                ':nombre' => $nombre_borrador,
                ':creado_por' => $creado_por
            ]);
            
        } catch (PDOException $e) {
            error_log("Error crearBorrador [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }

    public function getById($modulo, $id) {
        try {
            $tabla = $this->modulos[$modulo]['tabla'];
            $stmt = $this->db->prepare("SELECT * FROM {$tabla} WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getById [{$modulo}]: " . $e->getMessage());
            return null;
        }
    }

    public function actualizar($modulo, $id, $data) {
        try {
            $tabla = $this->modulos[$modulo]['tabla'];
            $campos = $this->modulos[$modulo]['campos_editables'];
            
            $sets = [];
            $params = [':id' => $id];
            
            foreach ($campos as $campo) {
                if (isset($data[$campo])) {
                    $sets[] = "{$campo} = :{$campo}";
                    $params[":{$campo}"] = $data[$campo];
                }
            }
            
            $sets[] = "fecha_actualizacion = NOW()";
            $sql = "UPDATE {$tabla} SET " . implode(', ', $sets) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error actualizar [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }

    public function cambiarEstado($modulo, $id, $estado) {
        try {
            $tabla = $this->modulos[$modulo]['tabla'];
            $stmt = $this->db->prepare("UPDATE {$tabla} SET estado = :estado, fecha_actualizacion = NOW() WHERE id = :id");
            return $stmt->execute([
                ':id' => $id,
                ':estado' => $estado
            ]);
        } catch (PDOException $e) {
            error_log("Error cambiarEstado [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($modulo, $id) {
        try {
            $tabla = $this->modulos[$modulo]['tabla'];
            $stmt = $this->db->prepare("DELETE FROM {$tabla} WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error eliminar [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }

    public function duplicar($modulo, $id, $nuevo_nombre, $creado_por = 1) {
        try {
            $original = $this->getById($modulo, $id);
            if (!$original) return false;
            
            $tabla = $this->modulos[$modulo]['tabla'];
            $campos = $this->modulos[$modulo]['campos_editables'];
            
            $insert_campos = ['formulario_id', 'nombre_borrador', 'estado', 'creado_por'];
            $insert_values = [':formulario_id', ':nombre', '0', ':creado_por'];
            $params = [
                ':formulario_id' => $original['formulario_id'],
                ':nombre' => $nuevo_nombre,
                ':creado_por' => $creado_por
            ];
            
            foreach ($campos as $campo) {
                if (isset($original[$campo]) && $original[$campo] !== null) {
                    $insert_campos[] = $campo;
                    $insert_values[] = ":{$campo}";
                    $params[":{$campo}"] = $original[$campo];
                }
            }
            
            $sql = "INSERT INTO {$tabla} (" . implode(', ', $insert_campos) . ") 
                    VALUES (" . implode(', ', $insert_values) . ")";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error duplicar [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerTodosLosFormularios() {
        try {
            $stmt = $this->db->prepare("SELECT id, titulo, descripcion, fecha_inicio, fecha_fin FROM formularios WHERE estado = 1 ORDER BY id DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error obtenerTodosLosFormularios: " . $e->getMessage());
            return [];
        }
    }

    public function verificarTabla($modulo) {
        try {
            if (!isset($this->modulos[$modulo])) {
                return false;
            }
            $tabla = $this->modulos[$modulo]['tabla'];
            $stmt = $this->db->prepare("SHOW TABLES LIKE '{$tabla}'");
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error verificarTabla [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }
}
?>