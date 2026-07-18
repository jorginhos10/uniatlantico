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
            'campo_solicitud' => 'solicitud_estado_formulacion',
            'campo_semaforo' => 'semaforo_etapa_formulacion',
            'campo_semaforo_rechazo' => 'semaforo_rechazo_etapa_formulacion',
            'fecha_publicacion' => 'fecha_publicacion_formulacion',
            'campos_editables' => [
                'anio', 'linea_estrategica', 'objetivo', 'estrategia', 'motor_desarrollo', 
                'proyecto', 'meta_resultado', 'ponderacion_proyectos', 'actividad_proyecto', 
                'ponderacion_actividades', 'responsable_formulacion', 'id_indicador', 
                'gestionado_facultades',
                'nombre_indicador', 'formula_medicion', 'frecuencia_medicion', 
                'unidad_medida', 'tipo_medicion', 'descripcion_indicador',
                'planes_institucionales',
                'linea_base_meta', 'anio_base_meta', 'meta_s1', 'meta_s2',
                'facultad_id',
                'gestion_sem1', 'gestion_sem2', 'vigencia', 'descripcion_gestion',
                'tabla_fila1_sem1', 'tabla_fila1_sem2',
                'tabla_fila2_sem1', 'tabla_fila2_sem2',
                'tabla_fila3_sem1', 'tabla_fila3_sem2'
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
                'DESCRIPCIÓN DEL INDICADOR' => 'descripcion_indicador',
                'PLANES INSTITUCIONALES' => 'planes_institucionales',
                'LÍNEA BASE META' => 'linea_base_meta',
                'AÑO BASE META' => 'anio_base_meta',
                'META SEMESTRE 1' => 'meta_s1',
                'META SEMESTRE 2' => 'meta_s2',
                'FACULTAD' => 'facultad_id',
                'GESTIÓN SEMESTRE 1' => 'gestion_sem1',
                'GESTIÓN SEMESTRE 2' => 'gestion_sem2',
                'VIGENCIA' => 'vigencia',
                'DESCRIPCIÓN GESTIÓN' => 'descripcion_gestion'
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
            'campo_solicitud' => 'solicitud_estado_seguimiento',
            'campo_semaforo' => 'semaforo_etapa_seguimiento',
            'campo_semaforo_rechazo' => 'semaforo_rechazo_etapa_seguimiento',
            'fecha_publicacion' => 'fecha_publicacion_seguimiento',
            'campos_editables' => ['indicador', 'meta_programada', 'meta_ejecutada', 'porcentaje_avance',
                                   'fecha_seguimiento', 'observaciones', 'responsable_seguimiento',
                                   'semestre1_seguimiento', 'semestre2_seguimiento',
                                   'logros', 'limites', 'observacion_estado', 'acciones_fortalecimiento'],
            'campos_vista' => [
                'INDICADOR' => 'indicador',
                'META PROGRAMADA' => 'meta_programada',
                'META EJECUTADA' => 'meta_ejecutada',
                '% AVANCE' => 'porcentaje_avance',
                'FECHA SEGUIMIENTO' => 'fecha_seguimiento',
                'OBSERVACIONES' => 'observaciones',
                'RESPONSABLE' => 'responsable_seguimiento',
                'SEGUIMIENTO SEMESTRE 1' => 'semestre1_seguimiento',
                'SEGUIMIENTO SEMESTRE 2' => 'semestre2_seguimiento',
                'LOGROS' => 'logros',
                'LÍMITES' => 'limites',
                'OBSERVACIÓN' => 'observacion_estado',
                'ACCIONES DE FORTALECIMIENTO' => 'acciones_fortalecimiento'
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

    public function getAnos() {
        try {
            $stmt = $this->db->prepare("SELECT id, anio FROM `ano-for-de-144` WHERE activo = 1 ORDER BY anio DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getAnos: " . $e->getMessage());
            return [];
        }
    }

    public function verificarFormulario($id) {
        try {
            $stmt = $this->db->prepare("SELECT *, 
                                        CASE WHEN tipo_tiempo = 'libre' THEN NULL ELSE fecha_fin END as fecha_cierre,
                                        NOW() as fecha_actual,
                                        CASE WHEN tipo_tiempo = 'rango' AND fecha_fin IS NOT NULL 
                                             THEN TIMESTAMPDIFF(SECOND, NOW(), fecha_fin) 
                                             ELSE NULL END as segundos_restantes,
                                        CASE 
                                            WHEN tipo_tiempo = 'libre' THEN 'sin_fechas'
                                            WHEN tipo_tiempo = 'rango' AND fecha_inicio IS NOT NULL AND NOW() < fecha_inicio THEN 'no_iniciado'
                                            WHEN tipo_tiempo = 'rango' AND fecha_fin IS NOT NULL AND NOW() > fecha_fin THEN 'expirado'
                                            WHEN tipo_tiempo = 'rango' AND fecha_inicio IS NOT NULL AND fecha_fin IS NOT NULL AND NOW() BETWEEN fecha_inicio AND fecha_fin THEN 'vigente'
                                            ELSE 'sin_fechas'
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

        // Formulario con tiempo libre: siempre disponible, sin fechas
        if (($formulario['tipo_tiempo'] ?? '') === 'libre') {
            return [
                'valido' => true,
                'mensaje' => 'Siempre disponible',
                'clase' => 'sin-fechas'
            ];
        }

        $fecha_actual = date('Y-m-d H:i:s');

        // Formulario con rango: verificar fechas
        $fi = $formulario['fecha_inicio'] ?? null;
        $ff = $formulario['fecha_fin']    ?? null;

        if (empty($fi) && empty($ff)) {
            // Rango pero sin fechas cargadas → tratar como libre
            return [
                'valido' => true,
                'mensaje' => 'Sin restricción de fechas',
                'clase' => 'sin-fechas'
            ];
        }

        if (!empty($fi) && !empty($ff)) {
            if ($fecha_actual < $fi) {
                return [
                    'valido' => false,
                    'mensaje' => 'Disponible desde: ' . date('d/m/Y H:i', strtotime($fi)),
                    'clase' => 'no-iniciado'
                ];
            } elseif ($fecha_actual > $ff) {
                return [
                    'valido' => false,
                    'mensaje' => '⚠️ EXPIRADO: ' . date('d/m/Y H:i', strtotime($ff)),
                    'clase' => 'expirado'
                ];
            } else {
                return [
                    'valido' => true,
                    'mensaje' => 'Vigente hasta: ' . date('d/m/Y H:i', strtotime($ff)),
                    'clase' => 'vigente'
                ];
            }
        }

        return ['valido' => true, 'mensaje' => 'Vigente', 'clase' => 'vigente'];
    }

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

    public function getMotoresPorLinea($linea_id) {
        try {
            $stmt = $this->db->prepare("SELECT id, codigo, nombre FROM motores WHERE linea_id = :linea_id AND activo = 1 ORDER BY codigo ASC");
            $stmt->execute([':linea_id' => $linea_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getMotoresPorLinea: " . $e->getMessage());
            return [];
        }
    }

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

    public function getPonderacionProyecto($proyecto_id, $anio) {
        try {
            // La llave real del dato guardado es proyecto_id + anio (ver config144Model::guardarDataProyecto).
            // No se filtra por motor_id porque no forma parte de esa llave y puede no coincidir.
            $stmt = $this->db->prepare("SELECT porcentaje FROM data_proyectos WHERE proyecto_id = :proyecto_id AND anio = :anio LIMIT 1");
            $stmt->execute([
                ':proyecto_id' => $proyecto_id,
                ':anio'        => $anio
            ]);
            $row = $stmt->fetch();
            return $row ? $row['porcentaje'] : null;
        } catch (PDOException $e) {
            error_log("Error getPonderacionProyecto: " . $e->getMessage());
            return null;
        }
    }

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

    public function getPlanesInstitucionales() {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre FROM planes_institucionales WHERE activo = 1 ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getPlanesInstitucionales: " . $e->getMessage());
            return [];
        }
    }

    public function getFacultades() {
        try {
            $stmt = $this->db->prepare("SELECT id, codigo, nombre, estado FROM facultades WHERE estado = 1 ORDER BY codigo, nombre");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getFacultades: " . $e->getMessage());
            return [];
        }
    }

    public function getBorradoresPorFacultad($facultad_id, $formulario_id) {
        try {
            $tabla = $this->modulos['formulacion']['tabla'];
            $stmt = $this->db->prepare("SELECT * FROM {$tabla} 
                                        WHERE formulario_id = :formulario_id 
                                        AND facultad_id = :facultad_id 
                                        ORDER BY fecha_creacion DESC");
            $stmt->execute([
                ':formulario_id' => $formulario_id,
                ':facultad_id' => $facultad_id
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getBorradoresPorFacultad: " . $e->getMessage());
            return [];
        }
    }

    public function getByEstado($modulo, $formulario_id, $estado) {
        try {
            $modulo_config = $this->modulos[$modulo];
            $tabla = $modulo_config['tabla'];
            $campo_estado = $modulo_config['campo_estado'];

            $stmt = $this->db->prepare("SELECT f.*,
                                        le.codigo  AS linea_codigo,
                                        m.id       AS motor_id_num,
                                        m.codigo   AS motor_codigo,
                                        p.codigo   AS proyecto_codigo,
                                        u.nombre   AS creado_por_nombre,
                                        u.cargo_id AS creado_por_cargo_id,
                                        u.rol      AS creado_por_rol,
                                        c.nombre   AS creado_por_cargo_nombre
                                        FROM {$tabla} f
                                        LEFT JOIN lineas_estrategicas le ON f.linea_estrategica = le.nombre AND le.activo = 1
                                        LEFT JOIN motores m ON f.motor_desarrollo = m.nombre AND m.linea_id = le.id AND m.activo = 1
                                        LEFT JOIN proyectos p ON f.proyecto = p.nombre AND p.motor_id = m.id AND p.activo = 1
                                        LEFT JOIN usuarios u ON f.creado_por = u.id
                                        LEFT JOIN cargos c ON u.cargo_id = c.id
                                        WHERE f.formulario_id = :formulario_id AND f.{$campo_estado} = :estado
                                        ORDER BY le.codigo ASC, m.id ASC, p.codigo ASC, f.fecha_creacion DESC");
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

    // Devuelve id, proyecto y ponderacion_actividades de TODAS las formulaciones del formulario
    public function getPonderacionesPorFormulario($formulario_id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, proyecto, ponderacion_actividades
                 FROM formulacion_144
                 WHERE formulario_id = :fid
                   AND estado_formulacion != 1"
            );
            $stmt->execute([':fid' => $formulario_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getPonderacionesPorFormulario: " . $e->getMessage());
            return [];
        }
    }

    // Suma la ponderacion_actividades de un proyecto excluyendo un id dado
    public function getAcumuladoProyecto($formulario_id, $proyecto, $excluir_id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT COALESCE(SUM(ponderacion_actividades), 0) as total
                 FROM formulacion_144
                 WHERE formulario_id = :fid
                   AND proyecto = :proyecto
                   AND id != :excluir
                   AND estado_formulacion != 1"
            );
            $stmt->execute([':fid' => $formulario_id, ':proyecto' => $proyecto, ':excluir' => $excluir_id]);
            $row = $stmt->fetch();
            return (float)($row['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getAcumuladoProyecto: " . $e->getMessage());
            return 0;
        }
    }

    // Cuenta el total de registros del formulario (para polling de cambios)
    public function contarRegistros($formulario_id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) as total FROM formulacion_144 WHERE formulario_id = :fid"
            );
            $stmt->execute([':fid' => $formulario_id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getPublicados($modulo, $formulario_id) {
        return $this->getByEstado($modulo, $formulario_id, 2);
    }

    public function getCancelados($modulo, $formulario_id) {
        return $this->getByEstado($modulo, $formulario_id, 1);
    }

    public function esAdministradorFormulario($usuario_id, $formulario_id) {
        if ((int)$usuario_id === 1) return true;
        try {
            $stmt = $this->db->prepare(
                "SELECT 1 FROM formulario_administradores WHERE formulario_id = :fid AND usuario_id = :uid LIMIT 1"
            );
            $stmt->execute([':fid' => $formulario_id, ':uid' => $usuario_id]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error esAdministradorFormulario: " . $e->getMessage());
            return false;
        }
    }

    public function crearBorrador($modulo, $formulario_id, $nombre_borrador, $creado_por = 1, $facultad_id = null) {
        try {
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

            // Obtener el año del formulario padre automáticamente
            $stmtAnio = $this->db->prepare("SELECT anio FROM formularios WHERE id = :formulario_id LIMIT 1");
            $stmtAnio->execute([':formulario_id' => $formulario_id]);
            $rowAnio = $stmtAnio->fetch();
            $anio = $rowAnio ? $rowAnio['anio'] : null;
            
            if ($facultad_id) {
                $stmt = $this->db->prepare("INSERT INTO {$tabla} 
                                            (formulario_id, nombre_borrador, facultad_id, anio, estado_formulacion, estado_seguimiento, creado_por) 
                                            VALUES (:formulario_id, :nombre, :facultad_id, :anio, 0, 0, :creado_por)");
                return $stmt->execute([
                    ':formulario_id' => $formulario_id,
                    ':nombre' => $nombre_borrador,
                    ':facultad_id' => $facultad_id,
                    ':anio' => $anio,
                    ':creado_por' => $creado_por
                ]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO {$tabla} 
                                            (formulario_id, nombre_borrador, anio, estado_formulacion, estado_seguimiento, creado_por) 
                                            VALUES (:formulario_id, :nombre, :anio, 0, 0, :creado_por)");
                return $stmt->execute([
                    ':formulario_id' => $formulario_id,
                    ':nombre' => $nombre_borrador,
                    ':anio' => $anio,
                    ':creado_por' => $creado_por
                ]);
            }
            
        } catch (PDOException $e) {
            error_log("Error crearBorrador [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }

    public function getById($modulo, $id) {
        try {
            $tabla = $this->modulos[$modulo]['tabla'];
            $stmt = $this->db->prepare("SELECT f.*,
                                        le.codigo  AS linea_codigo,
                                        m.codigo   AS motor_codigo,
                                        p.codigo   AS proyecto_codigo
                                        FROM {$tabla} f
                                        LEFT JOIN lineas_estrategicas le ON f.linea_estrategica = le.nombre AND le.activo = 1
                                        LEFT JOIN motores m ON f.motor_desarrollo = m.nombre AND m.linea_id = le.id AND m.activo = 1
                                        LEFT JOIN proyectos p ON f.proyecto = p.nombre AND p.motor_id = m.id AND p.activo = 1
                                        WHERE f.id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getById [{$modulo}]: " . $e->getMessage());
            return null;
        }
    }

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
            
            if (isset($data['nombre_borrador'])) {
                $sets[] = "nombre_borrador = :nombre_borrador";
                $params[':nombre_borrador'] = $data['nombre_borrador'];
            }
            
            if (empty($sets)) {
                return true;
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
            $modulo_config = $this->modulos[$modulo];
            $tabla = $modulo_config['tabla'];
            $campo_estado = $modulo_config['campo_estado'];
            $campo_fecha = $modulo_config['fecha_publicacion'];
            
            if ($modulo === 'formulacion' && $estado == 1) {
                $stmt = $this->db->prepare("UPDATE {$tabla} 
                                            SET estado_formulacion = 1, 
                                                estado_seguimiento = 1,
                                                fecha_actualizacion = NOW() 
                                            WHERE id = :id");
                return $stmt->execute([':id' => $id]);
            } else {
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

    public function actualizarSolicitudEstado($modulo, $id, $solicitud_estado) {
        try {
            $tabla = $this->modulos[$modulo]['tabla'];
            $campo_solicitud = $this->modulos[$modulo]['campo_solicitud'];
            $campo_rechazo = $this->modulos[$modulo]['campo_semaforo_rechazo'];
            // Al reenviar (solicitud_estado = 1) se limpia la marca roja de rechazo pendiente
            if ($solicitud_estado == 1) {
                $stmt = $this->db->prepare(
                    "UPDATE {$tabla} SET {$campo_solicitud} = :se, {$campo_rechazo} = 0, fecha_actualizacion = NOW() WHERE id = :id"
                );
            } else {
                $stmt = $this->db->prepare("UPDATE {$tabla} SET {$campo_solicitud} = :se, fecha_actualizacion = NOW() WHERE id = :id");
            }
            return $stmt->execute([':se' => $solicitud_estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error actualizarSolicitudEstado [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }

    public function avanzarSemaforo($modulo, $id, $etapaNueva, $etapaActual) {
        try {
            $tabla = $this->modulos[$modulo]['tabla'];
            $campo_semaforo = $this->modulos[$modulo]['campo_semaforo'];
            $stmt = $this->db->prepare(
                "UPDATE {$tabla} SET {$campo_semaforo} = :nueva, fecha_actualizacion = NOW()
                 WHERE id = :id AND {$campo_semaforo} = :actual"
            );
            $stmt->execute([':nueva' => $etapaNueva, ':id' => $id, ':actual' => $etapaActual]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error avanzarSemaforo [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }

    public function rechazarSemaforo($modulo, $id, $etapaActual, $etapaQueRechaza) {
        try {
            $tabla = $this->modulos[$modulo]['tabla'];
            $campo_semaforo = $this->modulos[$modulo]['campo_semaforo'];
            $campo_solicitud = $this->modulos[$modulo]['campo_solicitud'];
            $campo_rechazo = $this->modulos[$modulo]['campo_semaforo_rechazo'];
            $stmt = $this->db->prepare(
                "UPDATE {$tabla} SET {$campo_semaforo} = 0, {$campo_solicitud} = 2, {$campo_rechazo} = :rechazo, fecha_actualizacion = NOW()
                 WHERE id = :id AND {$campo_semaforo} = :actual"
            );
            $stmt->execute([':id' => $id, ':actual' => $etapaActual, ':rechazo' => $etapaQueRechaza]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error rechazarSemaforo [{$modulo}]: " . $e->getMessage());
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
            
            $stmt = $this->db->prepare("INSERT INTO {$tabla} 
                                        (formulario_id, nombre_borrador, facultad_id, anio, linea_estrategica, objetivo, 
                                         estrategia, motor_desarrollo, proyecto, meta_resultado, 
                                         ponderacion_proyectos, actividad_proyecto, ponderacion_actividades, 
                                         responsable_formulacion, id_indicador, gestionado_facultades,
                                         nombre_indicador, formula_medicion, frecuencia_medicion,
                                         unidad_medida, tipo_medicion, descripcion_indicador,
                                         planes_institucionales,
                                         linea_base_meta, anio_base_meta, meta_s1, meta_s2,
                                         indicador, meta_programada, meta_ejecutada, 
                                         porcentaje_avance, fecha_seguimiento, observaciones, responsable_seguimiento,
                                         gestion_sem1, gestion_sem2, vigencia, descripcion_gestion,
                                         tabla_fila1_sem1, tabla_fila1_sem2, tabla_fila2_sem1, tabla_fila2_sem2, tabla_fila3_sem1, tabla_fila3_sem2,
                                         estado_formulacion, estado_seguimiento, creado_por) 
                                        VALUES 
                                        (:formulario_id, :nombre, :facultad_id, :anio, :linea_estrategica, :objetivo,
                                         :estrategia, :motor_desarrollo, :proyecto, :meta_resultado,
                                         :ponderacion_proyectos, :actividad_proyecto, :ponderacion_actividades,
                                         :responsable_formulacion, :id_indicador, :gestionado_facultades,
                                         :nombre_indicador, :formula_medicion, :frecuencia_medicion,
                                         :unidad_medida, :tipo_medicion, :descripcion_indicador,
                                         :planes_institucionales,
                                         :linea_base_meta, :anio_base_meta, :meta_s1, :meta_s2,
                                         :indicador, :meta_programada, :meta_ejecutada,
                                         :porcentaje_avance, :fecha_seguimiento, :observaciones, :responsable_seguimiento,
                                         :gestion_sem1, :gestion_sem2, :vigencia, :descripcion_gestion,
                                         :tabla_fila1_sem1, :tabla_fila1_sem2, :tabla_fila2_sem1, :tabla_fila2_sem2, :tabla_fila3_sem1, :tabla_fila3_sem2,
                                         0, 0, :creado_por)");
            
            return $stmt->execute([
                ':formulario_id' => $original['formulario_id'],
                ':nombre' => $nuevo_nombre,
                ':facultad_id' => $original['facultad_id'],
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
                ':planes_institucionales' => $original['planes_institucionales'],
                ':linea_base_meta' => $original['linea_base_meta'],
                ':anio_base_meta' => $original['anio_base_meta'],
                ':meta_s1' => $original['meta_s1'],
                ':meta_s2' => $original['meta_s2'],
                ':indicador' => $original['indicador'],
                ':meta_programada' => $original['meta_programada'],
                ':meta_ejecutada' => $original['meta_ejecutada'],
                ':porcentaje_avance' => $original['porcentaje_avance'],
                ':fecha_seguimiento' => $original['fecha_seguimiento'],
                ':observaciones' => $original['observaciones'],
                ':responsable_seguimiento' => $original['responsable_seguimiento'],
                ':gestion_sem1' => $original['gestion_sem1'],
                ':gestion_sem2' => $original['gestion_sem2'],
                ':vigencia' => $original['vigencia'],
                ':descripcion_gestion' => $original['descripcion_gestion'],
                ':tabla_fila1_sem1' => $original['tabla_fila1_sem1'],
                ':tabla_fila1_sem2' => $original['tabla_fila1_sem2'],
                ':tabla_fila2_sem1' => $original['tabla_fila2_sem1'],
                ':tabla_fila2_sem2' => $original['tabla_fila2_sem2'],
                ':tabla_fila3_sem1' => $original['tabla_fila3_sem1'],
                ':tabla_fila3_sem2' => $original['tabla_fila3_sem2'],
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
    
    public function actualizarGestionSemestral($id, $data) {
        try {
            $tabla = $this->modulos['formulacion']['tabla'];
            
            $sets = [];
            $params = [':id' => $id];
            
            $camposPermitidos = [
                'gestion_sem1', 'gestion_sem2', 'vigencia', 'descripcion_gestion',
                'tabla_fila1_sem1', 'tabla_fila1_sem2',
                'tabla_fila2_sem1', 'tabla_fila2_sem2',
                'tabla_fila3_sem1', 'tabla_fila3_sem2'
            ];
            
            foreach ($camposPermitidos as $campo) {
                if (isset($data[$campo])) {
                    $sets[] = "{$campo} = :{$campo}";
                    $params[":{$campo}"] = $data[$campo];
                }
            }
            
            if (empty($sets)) {
                return true;
            }
            
            $sets[] = "fecha_actualizacion = NOW()";
            $sql = "UPDATE {$tabla} SET " . implode(', ', $sets) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error actualizarGestionSemestral: " . $e->getMessage());
            return false;
        }
    }

    public function getGestionFacultad($formulacion_id, $facultad_id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM gestion_facultad_144 WHERE formulacion_id = :fid AND facultad_id = :facid LIMIT 1"
            );
            $stmt->execute([':fid' => $formulacion_id, ':facid' => $facultad_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getGestionFacultad: " . $e->getMessage());
            return null;
        }
    }

    public function guardarGestionFacultad($formulacion_id, $facultad_id, $data) {
        try {
            $campos = ['sem1', 'sem2', 'vigencia', 'seguimiento_sem1', 'seguimiento_sem2', 'descripcion_gestion'];
            $params = [':fid' => $formulacion_id, ':facid' => $facultad_id];
            foreach ($campos as $campo) {
                $params[":{$campo}"] = $data[$campo] ?? null;
            }

            $sql = "INSERT INTO gestion_facultad_144 (formulacion_id, facultad_id, sem1, sem2, vigencia, seguimiento_sem1, seguimiento_sem2, descripcion_gestion)
                    VALUES (:fid, :facid, :sem1, :sem2, :vigencia, :seguimiento_sem1, :seguimiento_sem2, :descripcion_gestion)
                    ON DUPLICATE KEY UPDATE
                        sem1 = VALUES(sem1),
                        sem2 = VALUES(sem2),
                        vigencia = VALUES(vigencia),
                        seguimiento_sem1 = VALUES(seguimiento_sem1),
                        seguimiento_sem2 = VALUES(seguimiento_sem2),
                        descripcion_gestion = VALUES(descripcion_gestion),
                        fecha_actualizacion = NOW()";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error guardarGestionFacultad: " . $e->getMessage());
            return false;
        }
    }

    public function getFilterPreference($usuario_id, $formulario_id, $modulo) {
        try {
            $stmt = $this->db->prepare(
                "SELECT tipo_filtro, valor_filtro FROM user_filter_preferences
                 WHERE usuario_id = :uid AND formulario_id = :fid AND modulo = :mod LIMIT 1"
            );
            $stmt->execute([':uid' => $usuario_id, ':fid' => $formulario_id, ':mod' => $modulo]);
            $row = $stmt->fetch();
            return $row ?: ['tipo_filtro' => 'todos', 'valor_filtro' => null];
        } catch (PDOException $e) {
            error_log("Error getFilterPreference: " . $e->getMessage());
            return ['tipo_filtro' => 'todos', 'valor_filtro' => null];
        }
    }

    public function saveFilterPreference($usuario_id, $formulario_id, $modulo, $tipo_filtro, $valor_filtro) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO user_filter_preferences (usuario_id, formulario_id, modulo, tipo_filtro, valor_filtro)
                 VALUES (:uid, :fid, :mod, :tipo, :valor)
                 ON DUPLICATE KEY UPDATE tipo_filtro = VALUES(tipo_filtro), valor_filtro = VALUES(valor_filtro)"
            );
            return $stmt->execute([
                ':uid'   => $usuario_id,
                ':fid'   => $formulario_id,
                ':mod'   => $modulo,
                ':tipo'  => $tipo_filtro,
                ':valor' => $valor_filtro ?: null,
            ]);
        } catch (PDOException $e) {
            error_log("Error saveFilterPreference: " . $e->getMessage());
            return false;
        }
    }
}
?>