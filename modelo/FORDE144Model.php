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

    // Cumplimiento ponderado según Configuraciones (data_linea_estrategica / data_motores / data_proyectos
    // del año del formulario): cada nivel suma sus hijos por su peso. Un hijo con peso configurado pero
    // sin indicadores aporta 0. Si un nivel no tiene pesos configurados, se usa el promedio simple.
    // Dentro de cada proyecto, los indicadores pesan según su "ponderación de actividades".
    // Criterio: se miden las METAS APROBADAS (estado_formulacion = 2, sin borradores) y solo cuenta el avance de los
    // seguimientos REPORTADOS, es decir aprobados por la Oficina de Planeación (estado_seguimiento = 2); el resto aporta 0.
    // Seguimiento por facultades según lo CONCERTADO: por cada facultad con meta en las metas aprobadas, su logrado sale de los
    // valores concertados y aprobados (Concertaciones) y se compara con la meta que le corresponde (distribución). Lo que
    // todavía no está concertado y aprobado aporta 0 %. Devuelve [['facultad', 'metas', 'concertados', 'indicadores_80', 'cumplimiento']].
    public function getInformeFacultades($formulario_id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT d.formulacion_id, d.facultad_id, fa.nombre AS facultad, d.distribucion, f.tipo_medicion,
                        c1.valor_final AS v1, c2.valor_final AS v2
                 FROM formulacion_distribucion_facultad_144 d
                 JOIN formulacion_144 f ON f.id = d.formulacion_id AND f.formulario_id = :fid AND f.estado_formulacion = 2
                 JOIN facultades fa ON fa.id = d.facultad_id
                 LEFT JOIN concertaciones_144 c1 ON c1.formulacion_id = d.formulacion_id AND c1.facultad_id = d.facultad_id AND c1.semestre = 1 AND c1.aprobado = 1
                 LEFT JOIN concertaciones_144 c2 ON c2.formulacion_id = d.formulacion_id AND c2.facultad_id = d.facultad_id AND c2.semestre = 2 AND c2.aprobado = 1
                 WHERE d.distribucion > 0"
            );
            $stmt->execute([':fid' => (int)$formulario_id]);
            $por = [];
            foreach ($stmt->fetchAll() as $r) {
                $v1 = $r['v1'] === null ? null : (float)$r['v1'];
                $v2 = $r['v2'] === null ? null : (float)$r['v2'];
                $meta = (float)$r['distribucion'];
                $logrado = null;
                if ($v1 !== null || $v2 !== null) {
                    switch ($r['tipo_medicion']) {
                        case 'Promedio':
                            $vals = array_filter([$v1, $v2], function ($v) { return $v !== null; });
                            $logrado = array_sum($vals) / count($vals);
                            break;
                        case 'Último valor reportado':
                        case 'Límite':
                            $logrado = $v2 !== null ? $v2 : $v1;
                            break;
                        default: // Acumulado y nuevo gestionado durante la vigencia: se suman los semestres
                            $logrado = ($v1 ?? 0) + ($v2 ?? 0);
                    }
                }
                if ($logrado === null) $pct = 0;
                elseif ($r['tipo_medicion'] === 'Límite') $pct = $logrado <= $meta ? 100 : 0;
                else $pct = min(100, $logrado / $meta * 100);
                $k = (int)$r['facultad_id'];
                if (!isset($por[$k])) $por[$k] = ['facultad' => $r['facultad'], 'metas' => 0, 'concertados' => 0, 'indicadores_80' => 0, 'suma' => 0.0];
                $por[$k]['metas']++;
                if ($logrado !== null) $por[$k]['concertados']++;
                if ($pct >= 80) $por[$k]['indicadores_80']++;
                $por[$k]['suma'] += $pct;
            }
            $res = [];
            foreach ($por as $p) {
                $res[] = ['facultad' => $p['facultad'], 'metas' => $p['metas'], 'concertados' => $p['concertados'],
                          'indicadores_80' => $p['indicadores_80'], 'cumplimiento' => round($p['suma'] / $p['metas'], 2)];
            }
            usort($res, function ($a, $b) { return $b['cumplimiento'] <=> $a['cumplimiento']; });
            return $res;
        } catch (PDOException $e) {
            error_log('Error getInformeFacultades: ' . $e->getMessage()); // p. ej. todavía no existe concertaciones_144
            return [];
        }
    }

    // $opts (opcional):
    //   'soloAprobadas' => false     (por defecto true) incluir también lo que no está aprobado
    //   'ids'           => [int]     solo esos indicadores (lo que el usuario puede ver)
    //   'avance'        => callable  recibe la fila del indicador y devuelve su avance (0-100) en lugar del criterio por defecto
    public function getInformeCompleto($formulario_id, array $opts = []) {
        $opts += ['soloAprobadas' => true];
        // porcentaje_avance (0-100) lo guarda el seguimiento; solo cuenta cuando el seguimiento está aprobado
        $calc = "CASE WHEN f.estado_seguimiento = 2 THEN LEAST(COALESCE(f.porcentaje_avance, 0), 100) ELSE 0 END";

        try {
            $formulario = $this->getById($formulario_id);
            $anio = (int)($formulario['anio'] ?? 0);

            // Indicadores del formulario (sin cancelados)
            $sqlIds = '';
            if (isset($opts['ids']) && is_array($opts['ids'])) {
                $ids = array_values(array_filter(array_map('intval', $opts['ids'])));
                $sqlIds = $ids ? ' AND f.id IN (' . implode(',', $ids) . ')' : ' AND 1 = 0';
            }
            $stmt = $this->db->prepare(
                "SELECT f.id,
                        COALESCE(f.linea_estrategica,'Sin línea') AS linea,
                        le.id AS linea_id, le.codigo AS linea_codigo,
                        COALESCE(f.motor_desarrollo,'Sin motor') AS motor,
                        m.id AS motor_id, m.codigo AS motor_codigo,
                        COALESCE(f.proyecto,'Sin proyecto') AS proyecto,
                        p.id AS proyecto_id, p.codigo AS proyecto_codigo,
                        $calc AS avance,
                        COALESCE(f.ponderacion_actividades, 0) AS ponderacion,
                        f.tipo_medicion, f.meta_s1, f.meta_s2, f.linea_base_meta, f.porcentaje_avance,
                        f.estado_seguimiento, f.solicitud_estado_seguimiento
                 FROM formulacion_144 f
                 LEFT JOIN lineas_estrategicas le ON f.linea_estrategica = le.nombre AND le.activo = 1
                 LEFT JOIN motores m ON f.motor_desarrollo = m.nombre AND m.linea_id = le.id AND m.activo = 1
                 LEFT JOIN proyectos p ON f.proyecto = p.nombre AND p.motor_id = m.id AND p.activo = 1
                 WHERE f.formulario_id = :fid AND f.estado_formulacion " . (!empty($opts['soloAprobadas']) ? '= 2' : '!= 1') . $sqlIds
            );
            $stmt->execute([':fid' => $formulario_id]);
            $rows = $stmt->fetchAll();
            if (!empty($opts['avance']) && is_callable($opts['avance'])) {
                foreach ($rows as &$rowAv) $rowAv['avance'] = max(0, min(100, (float)call_user_func($opts['avance'], $rowAv)));
                unset($rowAv);
            }

            // Pesos configurados para el año (solo los mayores a 0), con nombres para los que no tengan datos
            $pesosLinea = $pesosMotor = $pesosProyecto = [];
            if ($anio > 0) {
                $s = $this->db->prepare(
                    "SELECT d.linea_id AS id, d.porcentaje, le.nombre, le.codigo
                     FROM data_linea_estrategica d JOIN lineas_estrategicas le ON le.id = d.linea_id
                     WHERE d.anio = :anio AND d.porcentaje > 0"
                );
                $s->execute([':anio' => $anio]);
                foreach ($s->fetchAll() as $r) $pesosLinea[(int)$r['id']] = $r;

                $s = $this->db->prepare(
                    "SELECT d.motor_id AS id, d.linea_id AS padre_id, d.porcentaje, m.nombre, m.codigo
                     FROM data_motores d JOIN motores m ON m.id = d.motor_id
                     WHERE d.anio = :anio AND d.porcentaje > 0"
                );
                $s->execute([':anio' => $anio]);
                foreach ($s->fetchAll() as $r) $pesosMotor[(int)$r['id']] = $r;

                $s = $this->db->prepare(
                    "SELECT d.proyecto_id AS id, d.motor_id AS padre_id, d.porcentaje, p.nombre, p.codigo
                     FROM data_proyectos d JOIN proyectos p ON p.id = d.proyecto_id
                     WHERE d.anio = :anio AND d.porcentaje > 0"
                );
                $s->execute([':anio' => $anio]);
                foreach ($s->fetchAll() as $r) $pesosProyecto[(int)$r['id']] = $r;
            }

            // Árbol línea → motor → proyecto → indicadores (clave por id; por nombre si no está en el catálogo)
            $arbol = [];
            foreach ($rows as $r) {
                $lk = $r['linea_id'] ? 'i' . $r['linea_id'] : 'n' . $r['linea'];
                $mk = $r['motor_id'] ? 'i' . $r['motor_id'] : 'n' . $r['motor'];
                $pk = $r['proyecto_id'] ? 'i' . $r['proyecto_id'] : 'n' . $r['proyecto'];
                $arbol[$lk] = $arbol[$lk] ?? ['id' => (int)$r['linea_id'], 'nombre' => $r['linea'], 'codigo' => $r['linea_codigo'], 'hijos' => []];
                $motores = &$arbol[$lk]['hijos'];
                $motores[$mk] = $motores[$mk] ?? ['id' => (int)$r['motor_id'], 'nombre' => $r['motor'], 'codigo' => $r['motor_codigo'], 'hijos' => []];
                $proyectos = &$motores[$mk]['hijos'];
                $proyectos[$pk] = $proyectos[$pk] ?? ['id' => (int)$r['proyecto_id'], 'nombre' => $r['proyecto'], 'codigo' => $r['proyecto_codigo'], 'indicadores' => []];
                $proyectos[$pk]['indicadores'][] = ['avance' => (float)$r['avance'], 'ponderacion' => (float)$r['ponderacion']];
                unset($motores, $proyectos);
            }

            // Agrega los elementos con peso configurado que no tienen indicadores (aportan 0)
            $agregarConfigurados = function (array &$hijos, array $pesos, $padreId, $conHijos) {
                foreach ($pesos as $id => $w) {
                    if ($padreId !== null && (int)$w['padre_id'] !== $padreId) continue;
                    if (!isset($hijos['i' . $id])) {
                        $hijos['i' . $id] = ['id' => $id, 'nombre' => $w['nombre'], 'codigo' => $w['codigo']]
                                          + ($conHijos ? ['hijos' => []] : ['indicadores' => []]);
                    }
                }
            };

            // Promedio ponderado por el peso configurado de cada hijo; sin pesos, promedio simple
            $ponderar = function (array $items, array $pesos) {
                $sumaPesos = 0.0;
                foreach ($items as $it) $sumaPesos += isset($pesos[$it['id']]) ? (float)$pesos[$it['id']]['porcentaje'] : 0;
                if ($sumaPesos > 0) {
                    $total = 0.0;
                    foreach ($items as $it) $total += (isset($pesos[$it['id']]) ? (float)$pesos[$it['id']]['porcentaje'] : 0) * $it['cumplimiento'];
                    return $total / $sumaPesos;
                }
                return $items ? array_sum(array_column($items, 'cumplimiento')) / count($items) : 0;
            };

            $peso = function (array $pesos, $id) {
                return isset($pesos[$id]) ? round((float)$pesos[$id]['porcentaje'], 2) : null;
            };
            $titulo = function ($codigo, $nombre) {
                return $codigo ? $codigo . ' - ' . $nombre : $nombre;
            };
            $ordenar = function (array &$lista) {
                usort($lista, function ($a, $b) {
                    return strnatcasecmp($a['codigo'] ?: '~' . $a['nombre'], $b['codigo'] ?: '~' . $b['nombre']);
                });
            };

            $agregarConfigurados($arbol, $pesosLinea, null, true);

            $lineas = [];
            foreach ($arbol as $linea) {
                $lineaId = $linea['id'] ?: null;
                if ($lineaId !== null) $agregarConfigurados($linea['hijos'], $pesosMotor, $lineaId, true);

                $motores = [];
                foreach ($linea['hijos'] as $motor) {
                    $motorId = $motor['id'] ?: null;
                    if ($motorId !== null) $agregarConfigurados($motor['hijos'], $pesosProyecto, $motorId, false);

                    $proyectos = [];
                    foreach ($motor['hijos'] as $proy) {
                        $inds = $proy['indicadores'];
                        $sumaPond = array_sum(array_column($inds, 'ponderacion'));
                        if ($sumaPond > 0) {
                            $cp = 0.0;
                            foreach ($inds as $ind) $cp += $ind['ponderacion'] * $ind['avance'];
                            $cp /= $sumaPond;
                        } else {
                            $cp = $inds ? array_sum(array_column($inds, 'avance')) / count($inds) : 0;
                        }
                        $proyectos[] = [
                            'id' => $proy['id'], 'codigo' => $proy['codigo'],
                            'nombre' => $proy['nombre'], 'total' => count($inds),
                            'cumplimiento' => round($cp, 2), 'peso' => $peso($pesosProyecto, $proy['id']),
                        ];
                    }
                    $ordenar($proyectos);
                    $motores[] = [
                        'id' => $motor['id'], 'codigo' => $motor['codigo'], 'nombre' => $motor['nombre'],
                        'cumplimiento' => round($ponderar($proyectos, $pesosProyecto), 2),
                        'peso' => $peso($pesosMotor, $motor['id']), 'proyectos' => $proyectos,
                    ];
                }
                $ordenar($motores);
                $lineas[] = [
                    'id' => $linea['id'], 'codigo' => $linea['codigo'] ?: '—', 'nombre' => $linea['nombre'],
                    'titulo' => $titulo($linea['codigo'], $linea['nombre']),
                    'cumplimiento' => round($ponderar($motores, $pesosMotor), 2),
                    'peso' => $peso($pesosLinea, $linea['id']), 'motores' => $motores,
                ];
            }
            $ordenar($lineas);

            $global = $ponderar($lineas, $pesosLinea);

            $stmtD = $this->db->prepare(
                "SELECT
                    COALESCE(NULLIF(TRIM(f.responsable_formulacion), ''), fa.nombre, 'Sin dependencia') as dependencia,
                    COUNT(f.id) as total_indicadores,
                    SUM(CASE WHEN f.estado_seguimiento = 2 THEN 1 ELSE 0 END) as reportados,
                    SUM(CASE WHEN ($calc) >= 80 THEN 1 ELSE 0 END) as indicadores_80,
                    ROUND(AVG($calc),2) as cumplimiento
                 FROM formulacion_144 f
                 LEFT JOIN facultades fa ON f.facultad_id = fa.id
                 WHERE f.formulario_id = :fid AND f.estado_formulacion = 2
                 GROUP BY dependencia
                 ORDER BY cumplimiento DESC"
            );
            $stmtD->execute([':fid' => $formulario_id]);
            $dependencias = $stmtD->fetchAll();

            return [
                'facultades'          => $this->getInformeFacultades($formulario_id),
                'global_cumplimiento' => round($global, 2),
                'metas'               => count($rows),
                'reportados'          => count(array_filter($rows, function ($r) { return (int)($r['estado_seguimiento'] ?? 0) === 2; })),
                'lineas'              => $lineas,
                'dependencias'        => $dependencias,
            ];
        } catch (PDOException $e) {
            error_log("Error getInformeCompleto: " . $e->getMessage());
            return ['global_cumplimiento' => 0, 'lineas' => [], 'dependencias' => [], 'error' => $e->getMessage()];
        }
    }

    public function getInforme($formulario_id) {
        // Mismo criterio que getInformeCompleto: solo indicadores con formulación publicada, y el avance solo cuenta
        // cuando el seguimiento está aprobado (si no, este informe daba cifras distintas al de la página)
        $calc = "CASE WHEN f.estado_seguimiento = 2 THEN LEAST(COALESCE(f.porcentaje_avance, 0), 100) ELSE 0 END";

        try {
            $stmtG = $this->db->prepare(
                "SELECT COUNT(f.id) as total, ROUND(AVG($calc),2) as cumplimiento_global
                 FROM formulacion_144 f WHERE f.formulario_id = :fid AND f.estado_formulacion = 2"
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
                 WHERE f.formulario_id = :fid AND f.estado_formulacion = 2
                 GROUP BY f.linea_estrategica, le.codigo
                 ORDER BY le.codigo ASC, f.linea_estrategica"
            );
            $stmtL->execute([':fid' => $formulario_id]);
            $lineas = $stmtL->fetchAll();

            $stmtD = $this->db->prepare(
                "SELECT COALESCE(NULLIF(TRIM(f.responsable_formulacion), ''), fa.nombre, 'Sin dependencia') as dependencia,
                        COUNT(f.id) as total_indicadores,
                        SUM(CASE WHEN ($calc) >= 80 THEN 1 ELSE 0 END) as indicadores_alto,
                        ROUND(AVG($calc),2) as cumplimiento
                 FROM formulacion_144 f
                 LEFT JOIN facultades fa ON f.facultad_id = fa.id
                 WHERE f.formulario_id = :fid AND f.estado_formulacion = 2
                 GROUP BY dependencia
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

    // ───────── Bloqueo de las casillas de seguimiento por semestre (pestaña "Campos") ─────────
    // bloqueo_sem1 / bloqueo_sem2: 1 = casillas de ese semestre bloqueadas; 0 = desbloqueadas. Las columnas se crean solas.
    public function asegurarColumnasBloqueo() {
        static $listo = false;
        if ($listo) return;
        try {
            foreach (['bloqueo_sem1', 'bloqueo_sem2', 'bloqueo_excepto_admin'] as $col) {
                $q = $this->db->query("SHOW COLUMNS FROM " . $this->table . " LIKE '" . $col . "'");
                if ($q->rowCount() === 0) {
                    $this->db->exec("ALTER TABLE " . $this->table . " ADD COLUMN " . $col . " TINYINT(1) NOT NULL DEFAULT 0");
                }
            }
            $listo = true;
        } catch (PDOException $e) {
            error_log('Error asegurarColumnasBloqueo: ' . $e->getMessage());
        }
    }

    public function getBloqueosSemestres($formulario_id) {
        $this->asegurarColumnasBloqueo();
        try {
            $stmt = $this->db->prepare("SELECT bloqueo_sem1, bloqueo_sem2, bloqueo_excepto_admin FROM " . $this->table . " WHERE id = :id");
            $stmt->execute([':id' => (int)$formulario_id]);
            $r = $stmt->fetch();
            return ['sem1' => (int)($r['bloqueo_sem1'] ?? 0) === 1, 'sem2' => (int)($r['bloqueo_sem2'] ?? 0) === 1,
                    'excepto_admin' => (int)($r['bloqueo_excepto_admin'] ?? 0) === 1];
        } catch (PDOException $e) {
            error_log('Error getBloqueosSemestres: ' . $e->getMessage());
            return ['sem1' => false, 'sem2' => false, 'excepto_admin' => false];
        }
    }

    public function setBloqueoSemestre($formulario_id, $semestre, $bloqueado) {
        $this->asegurarColumnasBloqueo();
        // semestre 0 = opción "Excepto administradores"
        $col = (int)$semestre === 0 ? 'bloqueo_excepto_admin' : ((int)$semestre === 2 ? 'bloqueo_sem2' : 'bloqueo_sem1');
        try {
            $stmt = $this->db->prepare("UPDATE " . $this->table . " SET {$col} = :v WHERE id = :id");
            return $stmt->execute([':v' => $bloqueado ? 1 : 0, ':id' => (int)$formulario_id]);
        } catch (PDOException $e) {
            error_log('Error setBloqueoSemestre: ' . $e->getMessage());
            return false;
        }
    }

    // Estado actual de los indicadores publicados de un formulario (landing "Reporte de seguimiento")
    public function getReporteSeguimiento($formulario_id) {
        $res = ['indicadores' => [], 'evidencias' => [], 'facultades' => []];
        try {
            $stmt = $this->db->prepare(
                "SELECT f.id, f.nombre_borrador, f.nombre_indicador, f.formula_medicion, f.linea_estrategica, f.responsable_formulacion,
                        f.tipo_medicion, f.unidad_medida, f.meta_programada, f.meta_ejecutada, f.porcentaje_avance,
                        f.semestre1_seguimiento, f.semestre2_seguimiento, f.estado_seguimiento,
                        f.solicitud_estado_seguimiento, f.semaforo_etapa_seguimiento, f.gestionado_facultades,
                        le.codigo AS linea_codigo, m.codigo AS motor_codigo, p.codigo AS proyecto_codigo
                 FROM formulacion_144 f
                 LEFT JOIN lineas_estrategicas le ON f.linea_estrategica = le.nombre AND le.activo = 1
                 LEFT JOIN motores m ON f.motor_desarrollo = m.nombre AND m.linea_id = le.id AND m.activo = 1
                 LEFT JOIN proyectos p ON f.proyecto = p.nombre AND p.motor_id = m.id AND p.activo = 1
                 WHERE f.formulario_id = :fid AND f.estado_formulacion = 2
                 ORDER BY le.codigo ASC, m.codigo ASC, p.codigo ASC, f.id ASC"
            );
            $stmt->execute([':fid' => (int)$formulario_id]);
            $res['indicadores'] = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error getReporteSeguimiento: ' . $e->getMessage());
            return $res;
        }
        // Facultades con meta en cada indicador: [formulacion_id => [[id, nombre], ...]]
        try {
            $q = $this->db->prepare(
                "SELECT d.formulacion_id, fa.id AS facultad_id, fa.nombre
                 FROM formulacion_distribucion_facultad_144 d
                 JOIN formulacion_144 f ON f.id = d.formulacion_id
                 JOIN facultades fa ON fa.id = d.facultad_id
                 WHERE f.formulario_id = :fid AND d.distribucion > 0"
            );
            $q->execute([':fid' => (int)$formulario_id]);
            foreach ($q->fetchAll() as $r) {
                $res['facultades'][(int)$r['formulacion_id']][] = ['id' => (int)$r['facultad_id'], 'nombre' => $r['nombre']];
            }
        } catch (PDOException $e) {
            error_log('Error facultades del reporte: ' . $e->getMessage());
        }
        // Evidencias por indicador y semestre (la tabla se crea al cargar la primera evidencia)
        try {
            if ($this->db->query("SHOW TABLES LIKE 'evidencias_144'")->rowCount() > 0) {
                $tieneSem = $this->db->query("SHOW COLUMNS FROM evidencias_144 LIKE 'semestre'")->rowCount() > 0;
                $sql = $tieneSem
                    ? "SELECT e.formulacion_id, e.semestre, COUNT(*) AS n FROM evidencias_144 e JOIN formulacion_144 f ON f.id = e.formulacion_id
                       WHERE f.formulario_id = :fid GROUP BY e.formulacion_id, e.semestre"
                    : "SELECT e.formulacion_id, 1 AS semestre, COUNT(*) AS n FROM evidencias_144 e JOIN formulacion_144 f ON f.id = e.formulacion_id
                       WHERE f.formulario_id = :fid GROUP BY e.formulacion_id";
                $q = $this->db->prepare($sql);
                $q->execute([':fid' => (int)$formulario_id]);
                foreach ($q->fetchAll() as $r) {
                    $sem = (int)$r['semestre'] === 2 ? 2 : 1;
                    $res['evidencias'][(int)$r['formulacion_id']][$sem] = ($res['evidencias'][(int)$r['formulacion_id']][$sem] ?? 0) + (int)$r['n'];
                }
            }
        } catch (PDOException $e) {
            error_log('Error evidencias del reporte: ' . $e->getMessage());
        }
        return $res;
    }
}
?>