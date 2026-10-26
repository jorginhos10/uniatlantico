<?php
// modelo/Modulo144Model.php
require_once 'config/config.php';

class Modulo144Model {
    private $db;
    
    // Definición de módulos disponibles - AHORA AMBOS USAN LA MISMA TABLA
    private $modulos = [
        'formulacion' => [
            'tabla' => 'formulacion_144',
            'nombre' => 'FORMULACIÓN DEL PLAN DE ACCIÓN FOR-DE-144',
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
            'nombre' => 'SEGUIMIENTO DEL PLAN DE ACCIÓN FOR-DE-144',
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
                                   'semestre1_seguimiento', 'semestre2_seguimiento', 'descripcion_sem1', 'descripcion_sem2',
                                   'logros', 'limites', 'observacion_estado', 'acciones_fortalecimiento',
                                   'logros_sem2', 'limites_sem2', 'observacion_estado_sem2', 'acciones_fortalecimiento_sem2'],
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
        $this->asegurarColumnasBloqueoSemestres();
        $this->asegurarColumnasDescripcionSemestres();
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

    // Todos los proyectos activos con su línea y motor, para el buscador del modal de Formulación
    public function getCatalogoProyectos() {
        try {
            $stmt = $this->db->prepare("SELECT p.id, p.codigo, p.nombre, p.linea_id, p.motor_id,
                                        l.codigo AS linea_codigo, l.nombre AS linea_nombre,
                                        m.codigo AS motor_codigo, m.nombre AS motor_nombre
                                        FROM proyectos p
                                        INNER JOIN lineas_estrategicas l ON p.linea_id = l.id AND l.activo = 1
                                        INNER JOIN motores m ON p.motor_id = m.id AND m.activo = 1
                                        WHERE p.activo = 1
                                        ORDER BY l.codigo, m.codigo, p.codigo");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getCatalogoProyectos: " . $e->getMessage());
            return [];
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

    public function getCargoNombre($cargo_id) {
        try {
            $stmt = $this->db->prepare("SELECT nombre FROM cargos WHERE id = :id");
            $stmt->execute([':id' => (int)$cargo_id]);
            return (string)($stmt->fetchColumn() ?: '');
        } catch (PDOException $e) {
            error_log("Error getCargoNombre: " . $e->getMessage());
            return '';
        }
    }

    // Nombres de las dependencias del usuario: la principal (usuarios.cargo_id) y las asociadas en
    // "Responsable de" (usuario_dependencias). Con ellas se decide de qué indicadores es responsable.
    public function getNombresDependenciasUsuario($usuarioId, $cargoId) {
        $nombres = [];
        $principal = $this->getCargoNombre($cargoId);
        if ($principal !== '') $nombres[] = $principal;
        try {
            $stmt = $this->db->prepare(
                "SELECT c.nombre FROM usuario_dependencias ud JOIN cargos c ON c.id = ud.cargo_id WHERE ud.usuario_id = :u"
            );
            $stmt->execute([':u' => (int)$usuarioId]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $n) $nombres[] = $n;
        } catch (PDOException $e) {
            // Sin la migración todavía: solo la dependencia principal
        }
        return array_values(array_unique(array_filter(array_map('trim', $nombres), 'strlen')));
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

    // Opciones del select "ID INDICADOR". Si el catálogo aún no existe o está vacío,
    // se devuelven las opciones base para que el formulario no quede sin opciones.
    public function getIdIndicadores() {
        $porDefecto = ['PR', 'PDI', 'PA', 'PR-PDI', 'PR-PA', 'PDI-PA'];
        try {
            $stmt = $this->db->prepare("SELECT nombre FROM id_indicadores WHERE activo = 1 ORDER BY id");
            $stmt->execute();
            $nombres = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $nombres ?: $porDefecto;
        } catch (PDOException $e) {
            error_log("Error getIdIndicadores: " . $e->getMessage());
            return $porDefecto;
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
                                        " . ($modulo === 'seguimiento' ? "AND f.estado_formulacion = 2" : "") . "
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
    // Todos los registros no cancelados del formulario, para la plantilla de importación/exportación
    public function getRegistrosParaPlantilla($formulario_id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT f.*, le.codigo AS linea_codigo, m.codigo AS motor_codigo, p.codigo AS proyecto_codigo
                 FROM formulacion_144 f
                 LEFT JOIN lineas_estrategicas le ON f.linea_estrategica = le.nombre AND le.activo = 1
                 LEFT JOIN motores m ON f.motor_desarrollo = m.nombre AND m.linea_id = le.id AND m.activo = 1
                 LEFT JOIN proyectos p ON f.proyecto = p.nombre AND p.motor_id = m.id AND p.activo = 1
                 WHERE f.formulario_id = :fid AND f.estado_formulacion != 1
                 ORDER BY le.codigo ASC, m.codigo ASC, p.codigo ASC, f.id ASC"
            );
            $stmt->execute([':fid' => $formulario_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getRegistrosParaPlantilla: " . $e->getMessage());
            return [];
        }
    }

    public function ultimoIdInsertado() {
        return (int)$this->db->lastInsertId();
    }

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
        $this->asegurarColumnasDescripcionSemestres();
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

            // Columnas numéricas: un campo vacío se guarda como NULL (si no, MySQL lo convierte en 0.00
            // y el formulario vuelve a abrir mostrando "0,00" en vez de vacío)
            $camposNumericos = [
                'semestre1_seguimiento', 'semestre2_seguimiento', 'porcentaje_avance',
                'ponderacion_proyectos', 'ponderacion_actividades', 'anio_base_meta',
            ];

            foreach ($campos as $campo) {
                if (isset($data[$campo])) {
                    $valor = $data[$campo];
                    if (in_array($campo, $camposNumericos, true) && trim((string)$valor) === '') {
                        $valor = null;
                    }
                    $sets[] = "{$campo} = :{$campo}";
                    $params[":{$campo}"] = $valor;
                }
            }
            
            // El objetivo depende de la línea estratégica: si se guarda una línea sin objetivo, se toma del catálogo
            if ($modulo === 'formulacion' && !empty($data['linea_estrategica']) && trim((string)($data['objetivo'] ?? '')) === '') {
                $q = $this->db->prepare("SELECT objetivo FROM lineas_estrategicas WHERE nombre = :n AND activo = 1 LIMIT 1");
                $q->execute([':n' => $data['linea_estrategica']]);
                $obj = $q->fetchColumn();
                if ($obj !== false && trim((string)$obj) !== '') {
                    $sets = array_values(array_filter($sets, function ($x) { return strpos($x, 'objetivo = ') !== 0; }));
                    unset($params[':objetivo']);
                    $sets[] = "objetivo = :objetivo";
                    $params[':objetivo'] = $obj;
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
            } elseif ($estado == 0) {
                // Devolver a borrador: se reinicia el flujo de aprobación (semáforo y solicitud)
                // para que el creador pueda editarlo y volver a solicitar aprobación.
                $stmt = $this->db->prepare("UPDATE {$tabla}
                                            SET {$campo_estado} = 0,
                                                {$modulo_config['campo_solicitud']} = 0,
                                                {$modulo_config['campo_semaforo']} = 0,
                                                {$modulo_config['campo_semaforo_rechazo']} = 0,
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

    // Devuelve a borrador TODOS los publicados del módulo en este formulario, reiniciando su
    // flujo de aprobación igual que cambiarEstado(..., 0). Devuelve cuántos se movieron (o false).
    public function devolverTodosABorrador($modulo, $formulario_id) {
        try {
            $c = $this->modulos[$modulo];
            $stmt = $this->db->prepare("UPDATE {$c['tabla']}
                                        SET {$c['campo_estado']} = 0,
                                            {$c['campo_solicitud']} = 0,
                                            {$c['campo_semaforo']} = 0,
                                            {$c['campo_semaforo_rechazo']} = 0,
                                            fecha_actualizacion = NOW()
                                        WHERE formulario_id = :fid AND {$c['campo_estado']} = 2");
            $stmt->execute([':fid' => $formulario_id]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error devolverTodosABorrador [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }

    // Publica en bloque los borradores de FORMULACIÓN del formulario (o solo los $ids dados), sin pasar por las
    // 4 etapas del semáforo: lo hace el administrador al cargar el plan por plantilla. Un borrador incompleto
    // (sin línea, motor, proyecto, indicador, tipo de medición o responsable) no se publica y se devuelve en
    // "omitidos" con el motivo. Devuelve ['publicados' => [ids], 'omitidos' => [[id, nombre, motivo]]] o false.
    public function publicarBorradores($formulario_id, array $ids = null, $usuarioId = 0, $usuarioNombre = '', $rol = null) {
        try {
            $c = $this->modulos['formulacion'];
            $sql = "SELECT id, nombre_borrador, linea_estrategica, motor_desarrollo, proyecto, nombre_indicador, formula_medicion,
                           tipo_medicion, responsable_formulacion
                    FROM {$c['tabla']}
                    WHERE formulario_id = :fid AND {$c['campo_estado']} = 0";
            $params = [':fid' => $formulario_id];
            if ($ids !== null) {
                $ids = array_values(array_filter(array_map('intval', $ids)));
                if (!$ids) return ['publicados' => [], 'omitidos' => []];
                $marcas = [];
                foreach ($ids as $i => $id) { $marcas[] = ":id{$i}"; $params[":id{$i}"] = $id; }
                $sql .= " AND id IN (" . implode(',', $marcas) . ")";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $borradores = $stmt->fetchAll();

            $publicar = [];
            $omitidos = [];
            foreach ($borradores as $b) {
                $faltan = [];
                if (trim((string)$b['linea_estrategica']) === '') $faltan[] = 'línea estratégica';
                if (trim((string)$b['motor_desarrollo']) === '') $faltan[] = 'motor de desarrollo';
                if (trim((string)$b['proyecto']) === '') $faltan[] = 'proyecto';
                if (trim((string)$b['nombre_indicador']) === '' && trim((string)$b['formula_medicion']) === '') $faltan[] = 'nombre o fórmula del indicador';
                if (trim((string)$b['tipo_medicion']) === '') $faltan[] = 'tipo de medición';
                if (trim((string)$b['responsable_formulacion']) === '') $faltan[] = 'responsable';
                if ($faltan) {
                    $nombre = trim((string)$b['nombre_indicador']) !== '' ? $b['nombre_indicador'] : $b['nombre_borrador'];
                    $omitidos[] = ['id' => (int)$b['id'], 'nombre' => $nombre, 'motivo' => 'Falta: ' . implode(', ', $faltan)];
                } else {
                    $publicar[] = (int)$b['id'];
                }
            }

            $this->db->beginTransaction();
            $upd = $this->db->prepare(
                "UPDATE {$c['tabla']}
                 SET {$c['campo_estado']} = 2,
                     {$c['fecha_publicacion']} = NOW(),
                     {$c['campo_solicitud']} = 1,
                     {$c['campo_semaforo']} = 4,
                     {$c['campo_semaforo_rechazo']} = 0,
                     fecha_actualizacion = NOW()
                 WHERE id = :id AND {$c['campo_estado']} = 0"
            );
            $publicados = [];
            foreach ($publicar as $id) {
                $upd->execute([':id' => $id]);
                if ($upd->rowCount() > 0) $publicados[] = $id;
            }
            $this->db->commit();

            foreach ($publicados as $id) {
                $this->registrarHistorialSemaforo('formulacion', $id, 4, 'aprobado', $usuarioId, $usuarioNombre, $rol,
                    'Publicado por el administrador (carga del plan)');
            }
            return ['publicados' => $publicados, 'omitidos' => $omitidos];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Error publicarBorradores: " . $e->getMessage());
            return false;
        }
    }

    // Publica el seguimiento de un indicador sin pasar por las etapas del semáforo que le faltan (superadministrador).
    // Equivale a que Planeación lo apruebe: queda como reportado.
    public function publicarSeguimientoIndicador($id, $usuarioId = 0, $usuarioNombre = '', $rol = null) {
        try {
            $c = $this->modulos['seguimiento'];
            $stmt = $this->db->prepare(
                "UPDATE {$c['tabla']}
                 SET {$c['campo_estado']} = 2,
                     {$c['fecha_publicacion']} = NOW(),
                     {$c['campo_solicitud']} = 1,
                     {$c['campo_semaforo']} = 4,
                     {$c['campo_semaforo_rechazo']} = 0,
                     fecha_actualizacion = NOW()
                 WHERE id = :id AND estado_formulacion = 2 AND {$c['campo_estado']} != 2"
            );
            $stmt->execute([':id' => (int)$id]);
            if ($stmt->rowCount() === 0) return false;
            $this->registrarHistorialSemaforo('seguimiento', (int)$id, 4, 'aprobado', $usuarioId, $usuarioNombre, $rol,
                'Publicado por el superadministrador');
            return true;
        } catch (PDOException $e) {
            error_log("Error publicarSeguimientoIndicador: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarSolicitudEstado($modulo, $id, $solicitud_estado, $nivelCreador = null) {
        try {
            $tabla = $this->modulos[$modulo]['tabla'];
            $campo_solicitud = $this->modulos[$modulo]['campo_solicitud'];
            $campo_rechazo = $this->modulos[$modulo]['campo_semaforo_rechazo'];
            $campo_semaforo = $this->modulos[$modulo]['campo_semaforo'];
            // Al reenviar (solicitud_estado = 1) se limpia la marca roja de rechazo pendiente
            // y el semáforo avanza al menos al nivel del creador, para que el siguiente rol lo vea.
            if ($solicitud_estado == 1) {
                if ($nivelCreador !== null && $nivelCreador > 0) {
                    $stmt = $this->db->prepare(
                        "UPDATE {$tabla} SET {$campo_solicitud} = :se, {$campo_rechazo} = 0,
                                {$campo_semaforo} = GREATEST({$campo_semaforo}, :nivel), fecha_actualizacion = NOW()
                         WHERE id = :id"
                    );
                    return $stmt->execute([':se' => $solicitud_estado, ':id' => $id, ':nivel' => $nivelCreador]);
                }
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
            // Tolerante: avanza si la etapa guardada es MENOR a la nueva (no exige que coincida
            // exacto con lo que mostraba la pantalla, así se autocorrige cualquier desfase previo).
            // Si alguien más ya avanzó más allá mientras tanto, no se pisa ese progreso.
            $stmt = $this->db->prepare(
                "UPDATE {$tabla} SET {$campo_semaforo} = :nueva, fecha_actualizacion = NOW()
                 WHERE id = :id AND {$campo_semaforo} < :nueva2"
            );
            $stmt->execute([':nueva' => $etapaNueva, ':id' => $id, ':nueva2' => $etapaNueva]);
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
            // Tolerante: rechaza mientras la etapa guardada no haya llegado/pasado ya la etapa
            // que está rechazando (evita pisar una aprobación real que ya avanzó más).
            $stmt = $this->db->prepare(
                "UPDATE {$tabla} SET {$campo_semaforo} = 0, {$campo_solicitud} = 2, {$campo_rechazo} = :rechazo, fecha_actualizacion = NOW()
                 WHERE id = :id AND {$campo_semaforo} < :rechazo2"
            );
            $stmt->execute([':id' => $id, ':rechazo' => $etapaQueRechaza, ':rechazo2' => $etapaQueRechaza]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error rechazarSemaforo [{$modulo}]: " . $e->getMessage());
            return false;
        }
    }

    public function registrarHistorialSemaforo($modulo, $formulacionId, $etapa, $accion, $usuarioId, $usuarioNombre, $rol = null, $motivo = null) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO semaforo_historial_144 (modulo, formulacion_id, etapa, accion, usuario_id, usuario_nombre, rol, motivo)
                 VALUES (:modulo, :formulacion_id, :etapa, :accion, :usuario_id, :usuario_nombre, :rol, :motivo)"
            );
            return $stmt->execute([
                ':modulo' => $modulo,
                ':formulacion_id' => $formulacionId,
                ':etapa' => $etapa,
                ':accion' => $accion,
                ':usuario_id' => $usuarioId,
                ':usuario_nombre' => $usuarioNombre,
                ':rol' => $rol,
                ':motivo' => $motivo
            ]);
        } catch (PDOException $e) {
            error_log("Error registrarHistorialSemaforo: " . $e->getMessage());
            return false;
        }
    }

    public function getHistorialSemaforo($modulo, $formulacionId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM semaforo_historial_144
                 WHERE modulo = :modulo AND formulacion_id = :formulacion_id
                 ORDER BY fecha_creacion ASC, id ASC"
            );
            $stmt->execute([':modulo' => $modulo, ':formulacion_id' => $formulacionId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getHistorialSemaforo: " . $e->getMessage());
            return [];
        }
    }

    public function getDistribucionFacultades($formulacionId) {
        // Se prueba de la versión más completa a la más simple, por si aún no se ejecutó toda la
        // migración de seguimiento por facultad (así lo que ya existe se sigue cargando)
        $this->asegurarColumnasDescripcionSemestres();
        $columnas = [
            'facultad_id, observacion, distribucion, seguimiento_sem1, seguimiento_sem2, seguimiento_descripcion, seguimiento_descripcion2',
            'facultad_id, observacion, distribucion, seguimiento_sem1, seguimiento_sem2, seguimiento_descripcion',
            'facultad_id, observacion, distribucion, seguimiento_sem1, seguimiento_sem2',
            'facultad_id, observacion, distribucion',
        ];
        foreach ($columnas as $i => $cols) {
            try {
                $stmt = $this->db->prepare(
                    "SELECT {$cols} FROM formulacion_distribucion_facultad_144 WHERE formulacion_id = :fid"
                );
                $stmt->execute([':fid' => $formulacionId]);
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                if ($i === count($columnas) - 1) {
                    error_log("Error getDistribucionFacultades: " . $e->getMessage());
                }
            }
        }
        return [];
    }

    // Facultades con valor en la distribución de cada indicador del formulario: [formulacion_id => [facultad_id, ...]]
    public function getFacultadesConDistribucion($formularioId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT d.formulacion_id, d.facultad_id
                 FROM formulacion_distribucion_facultad_144 d
                 JOIN formulacion_144 f ON f.id = d.formulacion_id
                 WHERE f.formulario_id = :fid AND d.distribucion > 0"
            );
            $stmt->execute([':fid' => $formularioId]);
            $mapa = [];
            foreach ($stmt->fetchAll() as $r) $mapa[(int)$r['formulacion_id']][] = (int)$r['facultad_id'];
            return $mapa;
        } catch (PDOException $e) {
            error_log("Error getFacultadesConDistribucion: " . $e->getMessage());
            return [];
        }
    }

    // Facultades que pueden ver y registrar el seguimiento de cada indicador: las que tienen meta en la distribución y,
    // en los indicadores gestionados desde facultades, todas (una facultad sin meta también puede reportar lo que hace)
    public function getFacultadesConAcceso($formularioId) {
        $mapa = $this->getFacultadesConDistribucion($formularioId);
        try {
            $stmt = $this->db->prepare(
                "SELECT f.id AS formulacion_id, fa.id AS facultad_id
                 FROM formulacion_144 f JOIN facultades fa ON fa.estado = 1
                 WHERE f.formulario_id = :fid AND f.estado_formulacion = 2 AND f.gestionado_facultades = '1'"
            );
            $stmt->execute([':fid' => $formularioId]);
            foreach ($stmt->fetchAll() as $r) {
                $mapa[(int)$r['formulacion_id']][] = (int)$r['facultad_id'];
            }
            foreach ($mapa as $k => $ids) $mapa[$k] = array_values(array_unique($ids));
        } catch (PDOException $e) {
            error_log('Error getFacultadesConAcceso: ' . $e->getMessage());
        }
        return $mapa;
    }

    // La facultad del usuario es la que tiene el mismo nombre que su dependencia (sin acentos ni mayúsculas)
    // Ids de las facultades del usuario: las que tienen el mismo nombre que cualquiera de sus dependencias
    // (la principal y las asociadas en "Responsable de"), sin importar mayúsculas ni tildes.
    public function getFacultadesDelUsuario($usuarioId, $cargoId) {
        $ids = [];
        foreach ($this->getNombresDependenciasUsuario($usuarioId, $cargoId) as $nombre) {
            $id = $this->getFacultadIdPorNombre($nombre);
            if ($id > 0) $ids[] = $id;
        }
        return array_values(array_unique($ids));
    }

    public function getFacultadIdPorNombre($nombre) {
        $norm = function ($s) {
            $s = mb_strtolower(trim((string)$s), 'UTF-8');
            return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
        };
        $buscado = $norm($nombre);
        if ($buscado === '') return 0;
        foreach ($this->getFacultades() as $fac) {
            if ($norm($fac['nombre']) === $buscado) return (int)$fac['id'];
        }
        return 0;
    }

    // $descripcion = descripción del semestre 1; $descripcion2 = descripción del semestre 2
    public function guardarSeguimientoFacultad($formulacionId, $facultadId, $sem1, $sem2, $descripcion = null, $descripcion2 = null) {
        $this->asegurarColumnasDescripcionSemestres();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO formulacion_distribucion_facultad_144 (formulacion_id, facultad_id, distribucion, seguimiento_sem1, seguimiento_sem2, seguimiento_descripcion, seguimiento_descripcion2)
                 VALUES (:fid, :facid, 0, :s1, :s2, :descr, :descr2)
                 ON DUPLICATE KEY UPDATE seguimiento_sem1 = VALUES(seguimiento_sem1), seguimiento_sem2 = VALUES(seguimiento_sem2),
                                         seguimiento_descripcion = VALUES(seguimiento_descripcion),
                                         seguimiento_descripcion2 = VALUES(seguimiento_descripcion2)"
            );
            return $stmt->execute([':fid' => $formulacionId, ':facid' => $facultadId, ':s1' => $sem1, ':s2' => $sem2, ':descr' => $descripcion, ':descr2' => $descripcion2]);
        } catch (PDOException $e) {
            error_log("Error guardarSeguimientoFacultad: " . $e->getMessage());
            return false;
        }
    }

    public function guardarDistribucionFacultad($formulacionId, $facultadId, $observacion, $distribucion) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO formulacion_distribucion_facultad_144 (formulacion_id, facultad_id, observacion, distribucion)
                 VALUES (:fid, :facid, :obs, :dist)
                 ON DUPLICATE KEY UPDATE observacion = VALUES(observacion), distribucion = VALUES(distribucion)"
            );
            return $stmt->execute([
                ':fid' => $formulacionId,
                ':facid' => $facultadId,
                ':obs' => $observacion,
                ':dist' => $distribucion,
            ]);
        } catch (PDOException $e) {
            error_log("Error guardarDistribucionFacultad: " . $e->getMessage());
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

    // ───────────────────────── Evidencias (archivos del seguimiento) ─────────────────────────
    // facultad_id = 0 → evidencias del indicador; > 0 → evidencias de esa facultad.
    private function asegurarTablaEvidencias() {
        static $listo = false;
        if ($listo) return;
        $this->db->exec("CREATE TABLE IF NOT EXISTS evidencias_144 (
            id INT AUTO_INCREMENT PRIMARY KEY,
            formulacion_id INT NOT NULL,
            facultad_id INT NOT NULL DEFAULT 0,
            nombre_original VARCHAR(255) NOT NULL,
            ruta VARCHAR(500) NOT NULL,
            tamano INT NOT NULL DEFAULT 0,
            usuario_id INT NULL,
            fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_evidencia_item (formulacion_id, facultad_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Cada semestre tiene sus propias evidencias (las ya cargadas quedan en el semestre 1)
        if ($this->db->query("SHOW COLUMNS FROM evidencias_144 LIKE 'semestre'")->rowCount() === 0) {
            $this->db->exec("ALTER TABLE evidencias_144 ADD COLUMN semestre TINYINT NOT NULL DEFAULT 1 AFTER facultad_id");
        }
        $listo = true;
    }

    public function getEvidencias($formulacionId, $facultadId = 0, $semestre = 1) {
        try {
            $this->asegurarTablaEvidencias();
            $stmt = $this->db->prepare("SELECT id, nombre_original, ruta, tamano, fecha_creacion, semestre
                                        FROM evidencias_144 WHERE formulacion_id = :f AND facultad_id = :fac AND semestre = :sem ORDER BY id");
            $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':sem' => (int)$semestre]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error getEvidencias: ' . $e->getMessage());
            return [];
        }
    }

    // ['general' => [semestre => n], 'facultades' => [facultad_id => [semestre => n]]]
    public function getResumenEvidencias($formulacionId) {
        $res = ['general' => [1 => 0, 2 => 0], 'facultades' => []];
        try {
            $this->asegurarTablaEvidencias();
            $stmt = $this->db->prepare("SELECT facultad_id, semestre, COUNT(*) AS n FROM evidencias_144 WHERE formulacion_id = :f GROUP BY facultad_id, semestre");
            $stmt->execute([':f' => (int)$formulacionId]);
            foreach ($stmt->fetchAll() as $r) {
                $sem = (int)$r['semestre'] === 2 ? 2 : 1;
                if ((int)$r['facultad_id'] === 0) $res['general'][$sem] = (int)$r['n'];
                else $res['facultades'][(int)$r['facultad_id']][$sem] = (int)$r['n'];
            }
        } catch (PDOException $e) {
            error_log('Error getResumenEvidencias: ' . $e->getMessage());
        }
        return $res;
    }

    public function contarEvidencias($formulacionId, $facultadId = 0, $semestre = 1) {
        return count($this->getEvidencias($formulacionId, $facultadId, $semestre));
    }

    public function guardarEvidencia($formulacionId, $facultadId, $nombreOriginal, $ruta, $tamano, $usuarioId, $semestre = 1) {
        try {
            $this->asegurarTablaEvidencias();
            $stmt = $this->db->prepare("INSERT INTO evidencias_144 (formulacion_id, facultad_id, semestre, nombre_original, ruta, tamano, usuario_id)
                                        VALUES (:f, :fac, :sem, :n, :r, :t, :u)");
            $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':sem' => (int)$semestre, ':n' => $nombreOriginal,
                            ':r' => $ruta, ':t' => (int)$tamano, ':u' => $usuarioId ?: null]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error guardarEvidencia: ' . $e->getMessage());
            return 0;
        }
    }

    public function getEvidencia($id) {
        try {
            $this->asegurarTablaEvidencias();
            $stmt = $this->db->prepare("SELECT * FROM evidencias_144 WHERE id = :id");
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('Error getEvidencia: ' . $e->getMessage());
            return null;
        }
    }

    public function eliminarEvidencia($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM evidencias_144 WHERE id = :id");
            return $stmt->execute([':id' => (int)$id]);
        } catch (PDOException $e) {
            error_log('Error eliminarEvidencia: ' . $e->getMessage());
            return false;
        }
    }

    public function getNombreFacultad($facultadId) {
        try {
            $stmt = $this->db->prepare("SELECT nombre FROM facultades WHERE id = :id");
            $stmt->execute([':id' => (int)$facultadId]);
            $n = $stmt->fetchColumn();
            return $n === false ? null : $n;
        } catch (PDOException $e) {
            return null;
        }
    }

    // Bloqueo de casillas de seguimiento por semestre (se configura en Editar formulario → Campos)
    public function asegurarColumnasBloqueoSemestres() {
        static $listo = false;
        if ($listo) return;
        try {
            foreach (['bloqueo_sem1', 'bloqueo_sem2', 'bloqueo_excepto_admin'] as $col) {
                if ($this->db->query("SHOW COLUMNS FROM formularios LIKE '" . $col . "'")->rowCount() === 0) {
                    $this->db->exec("ALTER TABLE formularios ADD COLUMN " . $col . " TINYINT(1) NOT NULL DEFAULT 0");
                }
            }
            $listo = true;
        } catch (PDOException $e) {
            error_log('Error asegurarColumnasBloqueoSemestres: ' . $e->getMessage());
        }
    }

    public function getBloqueosSemestres($formularioId) {
        $this->asegurarColumnasBloqueoSemestres();
        try {
            $stmt = $this->db->prepare("SELECT bloqueo_sem1, bloqueo_sem2, bloqueo_excepto_admin FROM formularios WHERE id = :id");
            $stmt->execute([':id' => (int)$formularioId]);
            $r = $stmt->fetch();
            return ['sem1' => (int)($r['bloqueo_sem1'] ?? 0) === 1, 'sem2' => (int)($r['bloqueo_sem2'] ?? 0) === 1,
                    'excepto_admin' => (int)($r['bloqueo_excepto_admin'] ?? 0) === 1];
        } catch (PDOException $e) {
            return ['sem1' => false, 'sem2' => false, 'excepto_admin' => false];
        }
    }

    // Descripción por semestre: del indicador (formulacion_144) y de cada facultad (distribución). Las columnas se crean solas.
    public function asegurarColumnasDescripcionSemestres() {
        static $listo = false;
        if ($listo) return;
        try {
            $cols = [
                ['formulacion_144', 'descripcion_sem1', 'TEXT NULL'],
                ['formulacion_144', 'descripcion_sem2', 'TEXT NULL'],
                // Estado de proyectos del semestre 2 (logros, limites, observacion_estado y acciones_fortalecimiento son los del semestre 1)
                ['formulacion_144', 'logros_sem2', 'TEXT NULL'],
                ['formulacion_144', 'limites_sem2', 'TEXT NULL'],
                ['formulacion_144', 'observacion_estado_sem2', 'TEXT NULL'],
                ['formulacion_144', 'acciones_fortalecimiento_sem2', 'TEXT NULL'],
                ['formulacion_distribucion_facultad_144', 'seguimiento_descripcion2', 'TEXT NULL'],
            ];
            foreach ($cols as [$tabla, $col, $def]) {
                if ($this->db->query("SHOW COLUMNS FROM {$tabla} LIKE '{$col}'")->rowCount() === 0) {
                    $this->db->exec("ALTER TABLE {$tabla} ADD COLUMN {$col} {$def}");
                }
            }
            $listo = true;
        } catch (PDOException $e) {
            error_log('Error asegurarColumnasDescripcionSemestres: ' . $e->getMessage());
        }
    }

    // ───────── Concertaciones: lo que reporta cada facultad se concierta contra su meta propuesta ─────────
    // Una fila por indicador, facultad y semestre. concertado = 1 cuando la meta hecha quedó concertada.
    private function asegurarTablaConcertaciones() {
        static $listo = false;
        if ($listo) return;
        $this->db->exec("CREATE TABLE IF NOT EXISTS concertaciones_144 (
            id INT AUTO_INCREMENT PRIMARY KEY,
            formulacion_id INT NOT NULL,
            facultad_id INT NOT NULL,
            semestre TINYINT NOT NULL,
            concertado TINYINT(1) NOT NULL DEFAULT 0,
            valor_concertado DECIMAL(12,2) NULL,
            observacion TEXT NULL,
            usuario_id INT NULL,
            fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_concertacion (formulacion_id, facultad_id, semestre)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        foreach ([
            'aprobado' => 'TINYINT(1) NOT NULL DEFAULT 0', 'aprobado_por' => 'INT NULL', 'fecha_aprobado' => 'DATETIME NULL',
            // Debate de la meta real: cada lado propone su valor; el superadministrador aprueba el valor final
            'valor_facultad' => 'DECIMAL(12,2) NULL', 'valor_vicerrectoria' => 'DECIMAL(12,2) NULL', 'valor_final' => 'DECIMAL(12,2) NULL',
            'usuario_facultad' => 'INT NULL', 'fecha_facultad' => 'DATETIME NULL', 'usuario_vice' => 'INT NULL', 'fecha_vice' => 'DATETIME NULL',
            // Cada declarante explica su valor
            'descripcion_facultad' => 'TEXT NULL', 'descripcion_vicerrectoria' => 'TEXT NULL',
            // Flujo: al concertar se envía al superadministrador, que aprueba (y el valor pasa al seguimiento) o rechaza
            'enviado' => 'TINYINT(1) NOT NULL DEFAULT 0', 'fecha_envio' => 'DATETIME NULL', 'usuario_envio' => 'INT NULL',
            'rechazado' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'valor_previo' => 'DECIMAL(12,2) NULL', 'previo_existia' => 'TINYINT(1) NOT NULL DEFAULT 0',
            // Descripción del valor concertado (es la que queda en el seguimiento por facultades) y la que había antes de aprobar
            'descripcion_concertada' => 'TEXT NULL', 'descripcion_previa' => 'TEXT NULL',
        ] as $col => $def) {
            if ($this->db->query("SHOW COLUMNS FROM concertaciones_144 LIKE '{$col}'")->rowCount() === 0) {
                $this->db->exec("ALTER TABLE concertaciones_144 ADD COLUMN {$col} {$def}");
            }
        }
        $listo = true;
    }

    // [formulacion_id => ['facultades' => n, 's1' => concertadas, 's2' => concertadas]] de los indicadores con distribución
    public function getResumenConcertaciones($formularioId) {
        $res = [];
        try {
            $this->asegurarTablaConcertaciones();
            // Cuentan las facultades con meta y las que no la tienen pero reportaron algo
            try {
                $stmt = $this->db->prepare(
                    "SELECT d.formulacion_id, COUNT(*) AS n
                     FROM formulacion_distribucion_facultad_144 d JOIN formulacion_144 f ON f.id = d.formulacion_id
                     WHERE f.formulario_id = :fid AND (d.distribucion > 0 OR d.seguimiento_sem1 IS NOT NULL OR d.seguimiento_sem2 IS NOT NULL)
                     GROUP BY d.formulacion_id"
                );
                $stmt->execute([':fid' => (int)$formularioId]);
            } catch (PDOException $e) {
                $stmt = $this->db->prepare(
                    "SELECT d.formulacion_id, COUNT(*) AS n
                     FROM formulacion_distribucion_facultad_144 d JOIN formulacion_144 f ON f.id = d.formulacion_id
                     WHERE f.formulario_id = :fid AND d.distribucion > 0 GROUP BY d.formulacion_id"
                );
                $stmt->execute([':fid' => (int)$formularioId]);
            }
            foreach ($stmt->fetchAll() as $r) $res[(int)$r['formulacion_id']] = ['facultades' => (int)$r['n'], 's1' => 0, 's2' => 0];

            $q = $this->db->prepare(
                "SELECT c.formulacion_id, c.semestre, COUNT(*) AS n
                 FROM concertaciones_144 c JOIN formulacion_144 f ON f.id = c.formulacion_id
                 JOIN formulacion_distribucion_facultad_144 d ON d.formulacion_id = c.formulacion_id AND d.facultad_id = c.facultad_id
                 WHERE f.formulario_id = :fid AND c.concertado = 1 GROUP BY c.formulacion_id, c.semestre"
            );
            $q->execute([':fid' => (int)$formularioId]);
            foreach ($q->fetchAll() as $r) {
                $id = (int)$r['formulacion_id'];
                if (isset($res[$id])) $res[$id][(int)$r['semestre'] === 2 ? 's2' : 's1'] = (int)$r['n'];
            }
        } catch (PDOException $e) {
            error_log('Error getResumenConcertaciones: ' . $e->getMessage());
        }
        return $res;
    }

    // [facultad_id => [semestre => fila]] con las propuestas de cada lado, quién las puso y la aprobación
    public function getConcertaciones($formulacionId) {
        $res = [];
        try {
            $this->asegurarTablaConcertaciones();
            $stmt = $this->db->prepare(
                "SELECT c.facultad_id, c.semestre, c.valor_facultad, c.valor_vicerrectoria, c.valor_final, c.aprobado, c.fecha_aprobado, c.fecha_facultad, c.fecha_vice,
                        c.descripcion_facultad, c.descripcion_vicerrectoria, c.descripcion_concertada, c.enviado, c.rechazado, c.fecha_envio, ue.nombre AS usuario_envio_nombre,
                        uf.nombre AS usuario_facultad, uv.nombre AS usuario_vice, ua.nombre AS aprobador
                 FROM concertaciones_144 c
                 LEFT JOIN usuarios uf ON uf.id = c.usuario_facultad
                 LEFT JOIN usuarios uv ON uv.id = c.usuario_vice
                 LEFT JOIN usuarios ua ON ua.id = c.aprobado_por
                 LEFT JOIN usuarios ue ON ue.id = c.usuario_envio
                 WHERE c.formulacion_id = :f AND c.facultad_id > 0"
            );
            $stmt->execute([':f' => (int)$formulacionId]);
            foreach ($stmt->fetchAll() as $r) $res[(int)$r['facultad_id']][(int)$r['semestre']] = $r;
        } catch (PDOException $e) {
            error_log('Error getConcertaciones: ' . $e->getMessage());
        }
        return $res;
    }

    // Todas las propuestas del formulario: [formulacion_id => [facultad_id => [semestre => ['vf' =>, 'vv' =>, 'aprobado' =>]]]]
    public function getTodasConcertaciones($formularioId) {
        $res = [];
        try {
            $this->asegurarTablaConcertaciones();
            $stmt = $this->db->prepare(
                "SELECT c.formulacion_id, c.facultad_id, c.semestre, c.valor_facultad, c.valor_vicerrectoria, c.aprobado, c.enviado
                 FROM concertaciones_144 c JOIN formulacion_144 f ON f.id = c.formulacion_id
                 WHERE f.formulario_id = :fid AND c.facultad_id > 0"
            );
            $stmt->execute([':fid' => (int)$formularioId]);
            foreach ($stmt->fetchAll() as $r) {
                $res[(int)$r['formulacion_id']][(int)$r['facultad_id']][(int)$r['semestre']] = [
                    'vf' => $r['valor_facultad'] !== null ? (float)$r['valor_facultad'] : null,
                    'vv' => $r['valor_vicerrectoria'] !== null ? (float)$r['valor_vicerrectoria'] : null,
                    'aprobado' => (int)$r['aprobado'] === 1,
                    'enviado' => (int)$r['enviado'] === 1,
                ];
            }
        } catch (PDOException $e) {
            error_log('Error getTodasConcertaciones: ' . $e->getMessage());
        }
        return $res;
    }

    // Propuesta de un lado ('facultad' o 'vicerrectoria') para un semestre de una facultad. $valor null = borrar la propuesta
    public function guardarPropuesta($formulacionId, $facultadId, $semestre, $lado, $valor, $usuarioId) {
        $col = $lado === 'facultad' ? 'valor_facultad' : 'valor_vicerrectoria';
        $ucol = $lado === 'facultad' ? 'usuario_facultad' : 'usuario_vice';
        $fcol = $lado === 'facultad' ? 'fecha_facultad' : 'fecha_vice';
        try {
            $this->asegurarTablaConcertaciones();
            // Si el valor cambia, lo enviado deja de valer: hay que volver a concertar
            $stmt = $this->db->prepare(
                "INSERT INTO concertaciones_144 (formulacion_id, facultad_id, semestre, concertado, {$col}, {$ucol}, {$fcol}, enviado, rechazado)
                 VALUES (:f, :fac, :s, 0, :v, :u, NOW(), 0, 0)
                 ON DUPLICATE KEY UPDATE {$col} = VALUES({$col}), {$ucol} = VALUES({$ucol}), {$fcol} = VALUES({$fcol}), enviado = 0, rechazado = 0"
            );
            return $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':s' => (int)$semestre, ':v' => $valor, ':u' => $usuarioId ?: null]);
        } catch (PDOException $e) {
            error_log('Error guardarPropuesta: ' . $e->getMessage());
            return false;
        }
    }

    // Descripción con la que un lado explica su valor ($texto vacío = borrarla)
    public function guardarDescripcion($formulacionId, $facultadId, $semestre, $lado, $texto, $usuarioId) {
        $col = $lado === 'facultad' ? 'descripcion_facultad' : 'descripcion_vicerrectoria';
        $ucol = $lado === 'facultad' ? 'usuario_facultad' : 'usuario_vice';
        $fcol = $lado === 'facultad' ? 'fecha_facultad' : 'fecha_vice';
        try {
            $this->asegurarTablaConcertaciones();
            $stmt = $this->db->prepare(
                "INSERT INTO concertaciones_144 (formulacion_id, facultad_id, semestre, concertado, {$col}, {$ucol}, {$fcol})
                 VALUES (:f, :fac, :s, 0, :t, :u, NOW())
                 ON DUPLICATE KEY UPDATE {$col} = VALUES({$col}), {$ucol} = VALUES({$ucol}), {$fcol} = VALUES({$fcol})"
            );
            return $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':s' => (int)$semestre, ':t' => $texto === '' ? null : $texto, ':u' => $usuarioId ?: null]);
        } catch (PDOException $e) {
            error_log('Error guardarDescripcion: ' . $e->getMessage());
            return false;
        }
    }

    // Aprobación de la meta real de una facultad en un semestre (solo superadministrador)
    // Valor del seguimiento de una facultad en un semestre (null si no hay)
    private function leerSeguimientoFacultad($formulacionId, $facultadId, $semestre) {
        $col = (int)$semestre === 2 ? 'seguimiento_sem2' : 'seguimiento_sem1';
        $stmt = $this->db->prepare("SELECT {$col} FROM formulacion_distribucion_facultad_144 WHERE formulacion_id = :f AND facultad_id = :fac");
        $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId]);
        $v = $stmt->fetchColumn();
        return ($v === false || $v === null || $v === '') ? null : (float)$v;
    }

    // Descripción del seguimiento de una facultad en un semestre (null si no hay)
    private function leerDescripcionFacultad($formulacionId, $facultadId, $semestre) {
        $this->asegurarColumnasDescripcionSemestres();
        $col = (int)$semestre === 2 ? 'seguimiento_descripcion2' : 'seguimiento_descripcion';
        $stmt = $this->db->prepare("SELECT {$col} FROM formulacion_distribucion_facultad_144 WHERE formulacion_id = :f AND facultad_id = :fac");
        $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId]);
        $v = $stmt->fetchColumn();
        return ($v === false || $v === null || trim((string)$v) === '') ? null : $v;
    }

    private function escribirDescripcionFacultad($formulacionId, $facultadId, $semestre, $texto) {
        $this->asegurarColumnasDescripcionSemestres();
        $col = (int)$semestre === 2 ? 'seguimiento_descripcion2' : 'seguimiento_descripcion';
        $stmt = $this->db->prepare(
            "INSERT INTO formulacion_distribucion_facultad_144 (formulacion_id, facultad_id, distribucion, {$col}) VALUES (:f, :fac, 0, :t)
             ON DUPLICATE KEY UPDATE {$col} = VALUES({$col})"
        );
        $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':t' => $texto]);
    }

    // Descripción del valor concertado: solo existe tras concertar y es la que queda en el seguimiento por facultades
    public function guardarDescripcionConcertada($formulacionId, $facultadId, $semestre, $texto) {
        try {
            $this->asegurarTablaConcertaciones();
            $this->db->beginTransaction();
            $q = $this->db->prepare("SELECT aprobado FROM concertaciones_144 WHERE formulacion_id = :f AND facultad_id = :fac AND semestre = :s");
            $q->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':s' => (int)$semestre]);
            if ((int)$q->fetchColumn() !== 1) { $this->db->rollBack(); return false; }
            $u = $this->db->prepare("UPDATE concertaciones_144 SET descripcion_concertada = :t WHERE formulacion_id = :f AND facultad_id = :fac AND semestre = :s");
            $u->execute([':t' => $texto === '' ? null : $texto, ':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':s' => (int)$semestre]);
            $this->escribirDescripcionFacultad($formulacionId, $facultadId, $semestre, $texto === '' ? null : $texto);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('Error guardarDescripcionConcertada: ' . $e->getMessage());
            return false;
        }
    }

    private function escribirSeguimientoFacultad($formulacionId, $facultadId, $semestre, $valor) {
        $col = (int)$semestre === 2 ? 'seguimiento_sem2' : 'seguimiento_sem1';
        $stmt = $this->db->prepare(
            "INSERT INTO formulacion_distribucion_facultad_144 (formulacion_id, facultad_id, distribucion, {$col}) VALUES (:f, :fac, 0, :v)
             ON DUPLICATE KEY UPDATE {$col} = VALUES({$col})"
        );
        $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':v' => $valor]);
    }

    // Aprueba (o retira la aprobación de) la meta real de una facultad en un semestre. Solo superadministrador.
    // Al aprobar, el valor final se escribe en el seguimiento por facultades; al retirarla, se restaura el que había.
    public function guardarAprobacion($formulacionId, $facultadId, $semestre, $aprobado, $valorFinal, $usuarioId) {
        try {
            $this->asegurarTablaConcertaciones();
            $this->db->beginTransaction();
            if ($aprobado) {
                $previo = $this->leerSeguimientoFacultad($formulacionId, $facultadId, $semestre);
                $descPrevia = $this->leerDescripcionFacultad($formulacionId, $facultadId, $semestre);
                $this->escribirSeguimientoFacultad($formulacionId, $facultadId, $semestre, $valorFinal);
                $stmt = $this->db->prepare(
                    "INSERT INTO concertaciones_144 (formulacion_id, facultad_id, semestre, concertado, aprobado, valor_final, aprobado_por, fecha_aprobado, enviado, rechazado, valor_previo, previo_existia, descripcion_previa, descripcion_concertada)
                     VALUES (:f, :fac, :s, 0, 1, :v, :u, NOW(), 0, 0, :pv, :pe, :dp, NULL)
                     ON DUPLICATE KEY UPDATE aprobado = 1, valor_final = VALUES(valor_final), aprobado_por = VALUES(aprobado_por), fecha_aprobado = NOW(),
                                             enviado = 0, rechazado = 0, valor_previo = VALUES(valor_previo), previo_existia = VALUES(previo_existia),
                                             descripcion_previa = VALUES(descripcion_previa), descripcion_concertada = NULL"
                );
                $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':s' => (int)$semestre, ':v' => $valorFinal, ':u' => $usuarioId ?: null,
                                ':pv' => $previo, ':pe' => $previo === null ? 0 : 1, ':dp' => $descPrevia]);
            } else {
                $q = $this->db->prepare("SELECT valor_previo, previo_existia, descripcion_previa FROM concertaciones_144 WHERE formulacion_id = :f AND facultad_id = :fac AND semestre = :s");
                $q->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':s' => (int)$semestre]);
                $r = $q->fetch();
                if ($r) $this->escribirDescripcionFacultad($formulacionId, $facultadId, $semestre, $r['descripcion_previa']);
                if ($r) $this->escribirSeguimientoFacultad($formulacionId, $facultadId, $semestre, ((int)$r['previo_existia'] === 1 && $r['valor_previo'] !== null) ? (float)$r['valor_previo'] : null);
                $stmt = $this->db->prepare(
                    "UPDATE concertaciones_144 SET aprobado = 0, valor_final = NULL, aprobado_por = NULL, fecha_aprobado = NULL, enviado = 0, valor_previo = NULL, previo_existia = 0,
                            descripcion_concertada = NULL, descripcion_previa = NULL
                     WHERE formulacion_id = :f AND facultad_id = :fac AND semestre = :s"
                );
                $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':s' => (int)$semestre]);
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('Error guardarAprobacion: ' . $e->getMessage());
            return false;
        }
    }

    // Envía al superadministrador lo concertado entre la facultad y la vicerrectoría
    public function enviarConcertacion($formulacionId, $facultadId, $semestre, $usuarioId) {
        try {
            $this->asegurarTablaConcertaciones();
            $stmt = $this->db->prepare(
                "UPDATE concertaciones_144 SET enviado = 1, rechazado = 0, fecha_envio = NOW(), usuario_envio = :u
                 WHERE formulacion_id = :f AND facultad_id = :fac AND semestre = :s AND aprobado = 0"
            );
            return $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':s' => (int)$semestre, ':u' => $usuarioId ?: null]);
        } catch (PDOException $e) {
            error_log('Error enviarConcertacion: ' . $e->getMessage());
            return false;
        }
    }

    // El superadministrador rechaza: vuelve al debate y se debe seguir concertando
    public function rechazarConcertacion($formulacionId, $facultadId, $semestre) {
        try {
            $this->asegurarTablaConcertaciones();
            $stmt = $this->db->prepare(
                "UPDATE concertaciones_144 SET enviado = 0, rechazado = 1 WHERE formulacion_id = :f AND facultad_id = :fac AND semestre = :s AND aprobado = 0"
            );
            return $stmt->execute([':f' => (int)$formulacionId, ':fac' => (int)$facultadId, ':s' => (int)$semestre]);
        } catch (PDOException $e) {
            error_log('Error rechazarConcertacion: ' . $e->getMessage());
            return false;
        }
    }

    // [formulacion_id => [facultad_id => fila]] con meta propuesta y seguimiento de cada facultad (matriz de Concertaciones)
    public function getMatrizConcertacion($formularioId) {
        $this->asegurarColumnasDescripcionSemestres();
        $variantes = [
            'd.facultad_id, d.distribucion, d.seguimiento_sem1, d.seguimiento_sem2, d.seguimiento_descripcion, d.seguimiento_descripcion2',
            'd.facultad_id, d.distribucion, d.seguimiento_sem1, d.seguimiento_sem2, d.seguimiento_descripcion',
            'd.facultad_id, d.distribucion',
        ];
        foreach ($variantes as $i => $cols) {
            try {
                $stmt = $this->db->prepare(
                    "SELECT d.formulacion_id, {$cols} FROM formulacion_distribucion_facultad_144 d
                     JOIN formulacion_144 f ON f.id = d.formulacion_id WHERE f.formulario_id = :fid"
                );
                $stmt->execute([':fid' => (int)$formularioId]);
                $res = [];
                foreach ($stmt->fetchAll() as $r) $res[(int)$r['formulacion_id']][(int)$r['facultad_id']] = $r;
                return $res;
            } catch (PDOException $e) {
                if ($i === count($variantes) - 1) error_log('Error getMatrizConcertacion: ' . $e->getMessage());
            }
        }
        return [];
    }
}
?>