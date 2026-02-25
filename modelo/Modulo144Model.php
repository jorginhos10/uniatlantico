<?php
// modelo/Modulo144Model.php
require_once 'config/config.php';

class Modulo144Model {
    private $db;
    
    // Definición de módulos disponibles - AHORA AMBOS USAN LA MISMA TABLA
    private $modulos = [
        'formulacion' => [
            'tabla' => 'formulacion_144',
            'nombre' => 'FORMULACIÓN 144',
            'icono' => 'fa-clipboard-list',
            'color' => '#2C3E50',
            'color_header' => 'linear-gradient(135deg, #2C3E50 0%, #34495E 100%)',
            'descripcion' => 'Planificación y formulación estratégica',
            'campo_estado' => 'estado_formulacion',
            'fecha_publicacion' => 'fecha_publicacion_formulacion',
            'campos_editables' => [
                'anio', 'linea_estrategica', 'objetivo', 'estrategia', 'motor_desarrollo', 
                'proyecto', 'meta_resultado', 'ponderacion_proyectos', 'actividad_proyecto', 
                'ponderacion_actividades', 'responsable_formulacion', 'id_indicador', 
                'gestionado_facultades',
                'nombre_indicador', 'formula_medicion', 'frecuencia_medicion', 
                'unidad_medida', 'tipo_medicion', 'descripcion_indicador'
            ],
            'campos_vista' => [
                'AÑO' => 'anio',
                'LÍNEA ESTRATÉGICA' => 'linea_estrategica',
                'OBJETIVO' => 'objetivo',
                'ESTRATEGIA' => 'estrategia',
                'MOTOR DE DESARROLLO' => 'motor_desarrollo',
                'PROYECTO' => 'proyecto',
                'META DE RESULTADO' => 'meta_resultado',
                'PONDERACIÓN PROYECTOS' => 'ponderacion_proyectos',
                'ACTIVIDAD DEL PROYECTO' => 'actividad_proyecto',
                'PONDERACIÓN ACTIVIDADES' => 'ponderacion_actividades',
                'RESPONSABLE' => 'responsable_formulacion',
                'ID INDICADOR' => 'id_indicador',
                'GESTIONADO EN FACULTADES' => 'gestionado_facultades',
                'NOMBRE DEL INDICADOR' => 'nombre_indicador',
                'FÓRMULA DE MEDICIÓN' => 'formula_medicion',
                'FRECUENCIA DE MEDICIÓN' => 'frecuencia_medicion',
                'UNIDAD DE MEDIDA' => 'unidad_medida',
                'TIPO DE MEDICIÓN' => 'tipo_medicion',
                'DESCRIPCIÓN DEL INDICADOR' => 'descripcion_indicador'
            ]
        ],
        'seguimiento' => [
            'tabla' => 'formulacion_144',
            'nombre' => 'SEGUIMIENTO 144',
            'icono' => 'fa-chart-line',
            'color' => '#27AE60',
            'color_header' => 'linear-gradient(135deg, #27AE60 0%, #2ECC71 100%)',
            'descripcion' => 'Seguimiento y monitoreo de avances',
            'campo_estado' => 'estado_seguimiento',
            'fecha_publicacion' => 'fecha_publicacion_seguimiento',
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

    /**
     * Obtener líneas estratégicas de la tabla lineas_estrategicas
     */
    public function getLineasEstrategicas() {
        try {
            $stmt = $this->db->prepare("SELECT id, codigo, nombre, objetivo FROM lineas_estrategicas WHERE activo = 1 ORDER BY codigo");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getLineasEstrategicas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estrategias de la tabla estrategias
     */
    public function getEstrategias() {
        try {
            $stmt = $this->db->prepare("SELECT e.id, e.linea_id, e.descripcion, l.codigo as linea_codigo, l.nombre as linea_nombre 
                                        FROM estrategias e 
                                        INNER JOIN lineas_estrategicas l ON e.linea_id = l.id 
                                        WHERE e.activo = 1 
                                        ORDER BY l.codigo, e.id");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getEstrategias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estrategias por línea estratégica
     */
    public function getEstrategiasPorLinea($linea_id) {
        try {
            $stmt = $this->db->prepare("SELECT id, descripcion FROM estrategias WHERE linea_id = :linea_id AND activo = 1 ORDER BY id");
            $stmt->execute([':linea_id' => $linea_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getEstrategiasPorLinea: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener motores por línea estratégica
     */
    public function getMotoresPorLinea($linea_id) {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre FROM motores WHERE linea_id = :linea_id AND activo = 1 ORDER BY nombre");
            $stmt->execute([':linea_id' => $linea_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getMotoresPorLinea: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener proyectos por línea y motor
     */
    public function getProyectosPorLineaYMotor($linea_id, $motor_id) {
        try {
            $stmt = $this->db->prepare("SELECT id, codigo, nombre FROM proyectos WHERE linea_id = :linea_id AND motor_id = :motor_id AND activo = 1 ORDER BY codigo");
            $stmt->execute([
                ':linea_id' => $linea_id,
                ':motor_id' => $motor_id
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getProyectosPorLineaYMotor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los proyectos
     */
    public function getProyectos() {
        try {
            $stmt = $this->db->prepare("SELECT p.id, p.linea_id, p.motor_id, p.codigo, p.nombre,
                                        l.codigo as linea_codigo, l.nombre as linea_nombre,
                                        m.nombre as motor_nombre
                                        FROM proyectos p
                                        INNER JOIN lineas_estrategicas l ON p.linea_id = l.id
                                        INNER JOIN motores m ON p.motor_id = m.id
                                        WHERE p.activo = 1 
                                        ORDER BY l.codigo, m.id, p.codigo");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getProyectos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener cargos de la tabla cargos
     */
    public function getCargos() {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre FROM cargos WHERE activo = 1 ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getCargos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene registros por estado (usando el campo de estado específico del módulo)
     */
    public function getByEstado($modulo, $formulario_id, $estado) {
        try {
            $modulo_config = $this->modulos[$modulo];
            $tabla = $modulo_config['tabla'];
            $campo_estado = $modulo_config['campo_estado'];
            
            $stmt = $this->db->prepare("SELECT * FROM {$tabla} 
                                        WHERE formulario_id = :formulario_id AND {$campo_estado} = :estado 
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

    /**
     * Crear un nuevo borrador (solo desde formulación)
     */
    public function crearBorrador($modulo, $formulario_id, $nombre_borrador, $creado_por = 1) {
        try {
            // Solo permitir crear desde formulación
            if ($modulo !== 'formulacion') {
                error_log("Error: Solo se pueden crear borradores desde formulación");
                return false;
            }
            
            $tabla = $this->modulos['formulacion']['tabla'];
            
            $checkTable = $this->db->prepare("SHOW TABLES LIKE '{$tabla}'");
            $checkTable->execute();
            if ($checkTable->rowCount() == 0) {
                error_log("Error: La tabla '{$tabla}' no existe");
                return false;
            }
            
            // Insertar con ambos estados en 0 (borrador)
            $stmt = $this->db->prepare("INSERT INTO {$tabla} 
                                        (formulario_id, nombre_borrador, estado_formulacion, estado_seguimiento, creado_por) 
                                        VALUES (:formulario_id, :nombre, 0, 0, :creado_por)");
            
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

    /**
     * Obtener un registro por ID
     */
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

    /**
     * Actualizar campos específicos del módulo
     */
    public function actualizar($modulo, $id, $data) {
        try {
            $modulo_config = $this->modulos[$modulo];
            $tabla = $modulo_config['tabla'];
            $campos = $modulo_config['campos_editables'];
            
            $sets = [];
            $params = [':id' => $id];
            
            foreach ($campos as $campo) {
                if (isset($data[$campo])) {
                    $sets[] = "{$campo} = :{$campo}";
                    $params[":{$campo}"] = $data[$campo];
                }
            }
            
            // También permitir actualizar el nombre del borrador
            if (isset($data['nombre_borrador'])) {
                $sets[] = "nombre_borrador = :nombre_borrador";
                $params[':nombre_borrador'] = $data['nombre_borrador'];
            }
            
            if (empty($sets)) {
                return true; // Nada que actualizar
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

    /**
     * Cambiar estado de un módulo específico
     */
    public function cambiarEstado($modulo, $id, $estado) {
        try {
            $modulo_config = $this->modulos[$modulo];
            $tabla = $modulo_config['tabla'];
            $campo_estado = $modulo_config['campo_estado'];
            $campo_fecha = $modulo_config['fecha_publicacion'];
            
            // Si es cancelado desde formulación, cancelar también seguimiento
            if ($modulo === 'formulacion' && $estado == 1) {
                $stmt = $this->db->prepare("UPDATE {$tabla} 
                                            SET estado_formulacion = 1, 
                                                estado_seguimiento = 1,
                                                fecha_actualizacion = NOW() 
                                            WHERE id = :id");
                return $stmt->execute([':id' => $id]);
            } else {
                // Cambiar estado normal (publicar o mover a borrador)
                $stmt = $this->db->prepare("UPDATE {$tabla} 
                                            SET {$campo_estado} = :estado, 
                                                {$campo_fecha} = CASE WHEN :estado = 2 THEN NOW() ELSE {$campo_fecha} END,
                                                fecha_actualizacion = NOW() 
                                            WHERE id = :id");
                return $stmt->execute([
                    ':id' => $id,
                    ':estado' => $estado
                ]);
            }
        } catch (PDOException $e) {
            error_log("Error cambiarEstado [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un registro
     */
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

    /**
     * Duplicar un registro completo
     */
    public function duplicar($modulo, $id, $nuevo_nombre, $creado_por = 1) {
        try {
            $original = $this->getById($modulo, $id);
            if (!$original) return false;
            
            $tabla = $this->modulos[$modulo]['tabla'];
            
            $stmt = $this->db->prepare("INSERT INTO {$tabla} 
                                        (formulario_id, nombre_borrador, anio, linea_estrategica, objetivo, 
                                         estrategia, motor_desarrollo, proyecto, meta_resultado, 
                                         ponderacion_proyectos, actividad_proyecto, ponderacion_actividades, 
                                         responsable_formulacion, id_indicador, gestionado_facultades,
                                         nombre_indicador, formula_medicion, frecuencia_medicion,
                                         unidad_medida, tipo_medicion, descripcion_indicador,
                                         indicador, meta_programada, meta_ejecutada, 
                                         porcentaje_avance, fecha_seguimiento, observaciones, responsable_seguimiento,
                                         estado_formulacion, estado_seguimiento, creado_por) 
                                        VALUES 
                                        (:formulario_id, :nombre, :anio, :linea_estrategica, :objetivo,
                                         :estrategia, :motor_desarrollo, :proyecto, :meta_resultado,
                                         :ponderacion_proyectos, :actividad_proyecto, :ponderacion_actividades,
                                         :responsable_formulacion, :id_indicador, :gestionado_facultades,
                                         :nombre_indicador, :formula_medicion, :frecuencia_medicion,
                                         :unidad_medida, :tipo_medicion, :descripcion_indicador,
                                         :indicador, :meta_programada, :meta_ejecutada,
                                         :porcentaje_avance, :fecha_seguimiento, :observaciones, :responsable_seguimiento,
                                         0, 0, :creado_por)");
            
            return $stmt->execute([
                ':formulario_id' => $original['formulario_id'],
                ':nombre' => $nuevo_nombre,
                ':anio' => $original['anio'],
                ':linea_estrategica' => $original['linea_estrategica'],
                ':objetivo' => $original['objetivo'],
                ':estrategia' => $original['estrategia'],
                ':motor_desarrollo' => $original['motor_desarrollo'],
                ':proyecto' => $original['proyecto'],
                ':meta_resultado' => $original['meta_resultado'],
                ':ponderacion_proyectos' => $original['ponderacion_proyectos'],
                ':actividad_proyecto' => $original['actividad_proyecto'],
                ':ponderacion_actividades' => $original['ponderacion_actividades'],
                ':responsable_formulacion' => $original['responsable_formulacion'],
                ':id_indicador' => $original['id_indicador'],
                ':gestionado_facultades' => $original['gestionado_facultades'],
                ':nombre_indicador' => $original['nombre_indicador'],
                ':formula_medicion' => $original['formula_medicion'],
                ':frecuencia_medicion' => $original['frecuencia_medicion'],
                ':unidad_medida' => $original['unidad_medida'],
                ':tipo_medicion' => $original['tipo_medicion'],
                ':descripcion_indicador' => $original['descripcion_indicador'],
                ':indicador' => $original['indicador'],
                ':meta_programada' => $original['meta_programada'],
                ':meta_ejecutada' => $original['meta_ejecutada'],
                ':porcentaje_avance' => $original['porcentaje_avance'],
                ':fecha_seguimiento' => $original['fecha_seguimiento'],
                ':observaciones' => $original['observaciones'],
                ':responsable_seguimiento' => $original['responsable_seguimiento'],
                ':creado_por' => $creado_por
            ]);
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