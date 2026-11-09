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
    //   'trimestres'    => true      la fila del callback 'avance' incluye meta_programada y trimestre1..4_seguimiento
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
            // La fila trae siempre la meta anual, los valores por trimestre y el estado de aprobación de cada uno: tN_estado (2 = aprobado)
            // y tN_sol (1 = en revisión). Las columnas y la tabla de trimestres las crea el Módulo 144.
            require_once __DIR__ . '/Modulo144Model.php';
            $mod144 = new Modulo144Model();
            $mod144->asegurarColumnasDescripcionSemestres();
            $extraCols = '';
            if (true) {
                $extraCols = ', f.meta_programada, f.responsable_formulacion, fa.nombre AS fac_nombre';
                foreach ([1, 2, 3, 4] as $n) {
                    $extraCols .= ", f.trimestre{$n}_seguimiento"
                        . ", (SELECT x.estado FROM seguimiento_trimestres_144 x WHERE x.formulacion_id = f.id AND x.trimestre = {$n}) AS t{$n}_estado"
                        . ", (SELECT x.solicitud FROM seguimiento_trimestres_144 x WHERE x.formulacion_id = f.id AND x.trimestre = {$n}) AS t{$n}_sol";
                }
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
                        f.estado_seguimiento, f.solicitud_estado_seguimiento$extraCols
                 FROM formulacion_144 f
                 LEFT JOIN lineas_estrategicas le ON f.linea_estrategica = le.nombre AND le.activo = 1
                 LEFT JOIN motores m ON f.motor_desarrollo = m.nombre AND m.linea_id = le.id AND m.activo = 1
                 LEFT JOIN proyectos p ON f.proyecto = p.nombre AND p.motor_id = m.id AND p.activo = 1
                 LEFT JOIN facultades fa ON f.facultad_id = fa.id
                 WHERE f.formulario_id = :fid AND f.estado_formulacion " . (!empty($opts['soloAprobadas']) ? '= 2' : '!= 1') . $sqlIds
            );
            $stmt->execute([':fid' => $formulario_id]);
            $rows = $stmt->fetchAll();
            // Avance de cada indicador: el que pida quien llama o, por defecto, lo APROBADO acumulado hasta el trimestre vigente
            // (el cumplimiento actual; no hace falta que el indicador tenga los cuatro trimestres aprobados para aportar)
            $tAvance = $mod144->getTrimestreVigente($formulario_id, $anio) ?: 1;
            $avanceFn = (!empty($opts['avance']) && is_callable($opts['avance']))
                ? $opts['avance']
                : function ($r) use ($tAvance) { return self::pctAcumTrim($r, $tAvance, false); };
            foreach ($rows as &$rowAv) $rowAv['avance'] = max(0, min(100, (float)call_user_func($avanceFn, $rowAv)));
            unset($rowAv);

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

            // Reportado = el trimestre vigente aprobado por la Oficina de Planeación (T1 si aún no vence ninguno)
            $tv = $mod144->getTrimestreVigente($formulario_id, $anio) ?: 1;
            $aplicaTv = function ($r) use ($tv) { return !(($r['tipo_medicion'] ?? '') === 'Límite' && $tv <= 2); };
            $reportado = function ($r) use ($tv, $aplicaTv) { return $aplicaTv($r) && (int)($r['t' . $tv . '_estado'] ?? 0) === 2; };

            $porDep = [];
            foreach ($rows as $r) {
                $dep = trim((string)($r['responsable_formulacion'] ?? ''));
                if ($dep === '') $dep = trim((string)($r['fac_nombre'] ?? '')) ?: 'Sin dependencia';
                if (!isset($porDep[$dep])) $porDep[$dep] = ['dependencia' => $dep, 'total_indicadores' => 0, 'reportados' => 0, 'indicadores_80' => 0, 'suma' => 0.0];
                $porDep[$dep]['total_indicadores']++;
                if ($reportado($r)) $porDep[$dep]['reportados']++;
                if ((float)$r['avance'] >= 80) $porDep[$dep]['indicadores_80']++;
                $porDep[$dep]['suma'] += (float)$r['avance'];
            }
            $dependencias = [];
            foreach ($porDep as $d) {
                $d['cumplimiento'] = round($d['suma'] / max(1, $d['total_indicadores']), 2);
                unset($d['suma']);
                $dependencias[] = $d;
            }
            usort($dependencias, function ($a, $b) { return $b['cumplimiento'] <=> $a['cumplimiento']; });

            return [
                'facultades'          => $this->getInformeFacultades($formulario_id),
                'global_cumplimiento' => round($global, 2),
                'metas'               => count(array_filter($rows, $aplicaTv)),
                'metas_total'         => count($rows),
                'trimestre_reporte'   => $tv,
                'reportados'          => count(array_filter($rows, $reportado)),
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
    // Cumplimiento (0 a 100) de un indicador con lo acumulado hasta el trimestre $k frente a su meta anual, según el tipo de medición:
    // Acumulado → línea base + suma de los trimestres; Nuevo gestionado → suma; Promedio → promedio de los reportados;
    // Último valor reportado → el del último trimestre con dato; Límite (solo T3 y T4) → 100 si lo declarado no pasa el límite.
    // Solo cuentan los trimestres aprobados (tN_estado = 2) y, con $conRevision, también los que están en revisión (tN_sol = 1).
    public static function pctAcumTrim($r, $k, $conRevision = false) {
        $num = function ($v) {
            if ($v === null) return null;
            $v = str_replace(['%', ' '], '', (string)$v);
            $v = str_replace(',', '.', $v);
            return is_numeric($v) ? (float)$v : null;
        };
        $tipo = (string)($r['tipo_medicion'] ?? '');
        if ($tipo === 'Límite' && $k < 3) return 0.0; // T1 y T2 no se registran: cuenta 0 hasta T3, para medir siempre los mismos indicadores
        $meta = $num($r['meta_programada'] ?? null);
        $vals = [];
        for ($i = ($tipo === 'Límite' ? 3 : 1); $i <= $k; $i++) {
            $e = (int)($r['t' . $i . '_estado'] ?? 0);
            $aprobado = $e === 2;
            $enRevision = $e === 0 && (int)($r['t' . $i . '_sol'] ?? 0) === 1;
            if (!$aprobado && !($conRevision && $enRevision)) continue;
            $v = $num($r['trimestre' . $i . '_seguimiento'] ?? null);
            if ($v !== null) $vals[] = $v;
        }
        if (!$vals) return 0.0;
        if ($tipo === 'Límite') return ($meta !== null && end($vals) <= $meta) ? 100.0 : 0.0;
        if ($tipo === 'Promedio') $logrado = array_sum($vals) / count($vals);
        elseif ($tipo === 'Último valor reportado') $logrado = end($vals);
        elseif ($tipo === 'Acumulado') $logrado = ($num($r['linea_base_meta'] ?? null) ?? 0) + array_sum($vals);
        else $logrado = array_sum($vals);
        return ($meta !== null && $meta > 0) ? max(0.0, min(100.0, $logrado / $meta * 100)) : 0.0;
    }

    // Cumplimiento ponderado (global y por línea) de VARIAS formas de medir el avance, leyendo los indicadores y los pesos UNA sola vez.
    // $variantes: [clave => callable(fila) => avance 0-100]; la fila trae meta_programada, trimestreN_seguimiento y tN_estado / tN_sol.
    // $opts: 'ids' => [int] solo esos indicadores (lo que el usuario puede ver); en ese caso las líneas, motores y proyectos con
    // peso pero sin indicadores visibles no entran (no diluyen el resultado).
    // Devuelve [clave => ['global' => float, 'lineas' => [código de línea => float]]].
    public function getCumplimientosPorVariante($formulario_id, array $variantes, array $opts = []) {
        $out = [];
        foreach ($variantes as $k => $_) $out[$k] = ['global' => null, 'lineas' => []];
        try {
            require_once __DIR__ . '/Modulo144Model.php';
            (new Modulo144Model())->asegurarColumnasDescripcionSemestres(); // columnas y tabla de trimestres

            $formulario = $this->getById($formulario_id);
            $anio = (int)($formulario['anio'] ?? 0);
            $sqlIds = '';
            $conIds = isset($opts['ids']) && is_array($opts['ids']);
            if ($conIds) {
                $ids = array_values(array_filter(array_map('intval', $opts['ids'])));
                $sqlIds = $ids ? ' AND f.id IN (' . implode(',', $ids) . ')' : ' AND 1 = 0';
            }
            $extra = ', f.meta_programada';
            foreach ([1, 2, 3, 4] as $n) {
                $extra .= ", f.trimestre{$n}_seguimiento"
                    . ", (SELECT x.estado FROM seguimiento_trimestres_144 x WHERE x.formulacion_id = f.id AND x.trimestre = {$n}) AS t{$n}_estado"
                    . ", (SELECT x.solicitud FROM seguimiento_trimestres_144 x WHERE x.formulacion_id = f.id AND x.trimestre = {$n}) AS t{$n}_sol";
            }
            $stmt = $this->db->prepare(
                "SELECT f.id,
                        COALESCE(f.linea_estrategica,'Sin línea') AS linea, le.id AS linea_id, le.codigo AS linea_codigo,
                        COALESCE(f.motor_desarrollo,'Sin motor') AS motor, m.id AS motor_id, m.codigo AS motor_codigo,
                        COALESCE(f.proyecto,'Sin proyecto') AS proyecto, p.id AS proyecto_id, p.codigo AS proyecto_codigo,
                        COALESCE(f.ponderacion_actividades, 0) AS ponderacion,
                        f.tipo_medicion, f.linea_base_meta, f.estado_seguimiento, f.solicitud_estado_seguimiento{$extra}
                 FROM formulacion_144 f
                 LEFT JOIN lineas_estrategicas le ON f.linea_estrategica = le.nombre AND le.activo = 1
                 LEFT JOIN motores m ON f.motor_desarrollo = m.nombre AND m.linea_id = le.id AND m.activo = 1
                 LEFT JOIN proyectos p ON f.proyecto = p.nombre AND p.motor_id = m.id AND p.activo = 1
                 WHERE f.formulario_id = :fid AND f.estado_formulacion = 2" . $sqlIds
            );
            $stmt->execute([':fid' => $formulario_id]);
            $rows = $stmt->fetchAll();

            // Pesos configurados para el año (solo los mayores a 0)
            $pesos = ['linea' => [], 'motor' => [], 'proyecto' => []];
            if ($anio > 0) {
                $consultas = [
                    'linea'    => "SELECT d.linea_id AS id, d.porcentaje, le.nombre, le.codigo FROM data_linea_estrategica d JOIN lineas_estrategicas le ON le.id = d.linea_id WHERE d.anio = :anio AND d.porcentaje > 0",
                    'motor'    => "SELECT d.motor_id AS id, d.linea_id AS padre_id, d.porcentaje, m.nombre, m.codigo FROM data_motores d JOIN motores m ON m.id = d.motor_id WHERE d.anio = :anio AND d.porcentaje > 0",
                    'proyecto' => "SELECT d.proyecto_id AS id, d.motor_id AS padre_id, d.porcentaje, p.nombre, p.codigo FROM data_proyectos d JOIN proyectos p ON p.id = d.proyecto_id WHERE d.anio = :anio AND d.porcentaje > 0",
                ];
                foreach ($consultas as $nivel => $sql) {
                    $s = $this->db->prepare($sql);
                    $s->execute([':anio' => $anio]);
                    foreach ($s->fetchAll() as $r) $pesos[$nivel][(int)$r['id']] = $r;
                }
            }

            foreach ($variantes as $clave => $avanceFn) {
                // Si el callable devuelve null el indicador no aplica a esa medición y no entra en el promedio
                $filas = [];
                foreach ($rows as $f) {
                    $av = call_user_func($avanceFn, $f);
                    if ($av === null) continue;
                    $f['avance'] = max(0, min(100, (float)$av));
                    $filas[] = $f;
                }
                $out[$clave] = $this->ponderarArbol($filas, $pesos, !$conIds);
            }
        } catch (PDOException $e) {
            error_log('Error getCumplimientosPorVariante: ' . $e->getMessage());
        }
        return $out;
    }

    // Árbol línea → motor → proyecto → indicadores, ponderado con los pesos configurados. Devuelve ['global', 'lineas' => [código => cumplimiento]]
    private function ponderarArbol(array $filas, array $pesos, $conVacios) {
        $arbol = [];
        foreach ($filas as $r) {
            $lk = $r['linea_id'] ? 'i' . $r['linea_id'] : 'n' . $r['linea'];
            $mk = $r['motor_id'] ? 'i' . $r['motor_id'] : 'n' . $r['motor'];
            $pk = $r['proyecto_id'] ? 'i' . $r['proyecto_id'] : 'n' . $r['proyecto'];
            $arbol[$lk] = $arbol[$lk] ?? ['id' => (int)$r['linea_id'], 'codigo' => $r['linea_codigo'], 'hijos' => []];
            $arbol[$lk]['hijos'][$mk] = $arbol[$lk]['hijos'][$mk] ?? ['id' => (int)$r['motor_id'], 'hijos' => []];
            $arbol[$lk]['hijos'][$mk]['hijos'][$pk] = $arbol[$lk]['hijos'][$mk]['hijos'][$pk] ?? ['id' => (int)$r['proyecto_id'], 'indicadores' => []];
            $arbol[$lk]['hijos'][$mk]['hijos'][$pk]['indicadores'][] = ['avance' => (float)$r['avance'], 'ponderacion' => (float)$r['ponderacion']];
        }

        // Los elementos con peso configurado que no tienen indicadores aportan 0 (salvo cuando se mira solo lo visible para un usuario)
        $agregar = function (array &$hijos, array $pw, $padreId, $conHijos) {
            foreach ($pw as $id => $w) {
                if ($padreId !== null && (int)$w['padre_id'] !== $padreId) continue;
                if (!isset($hijos['i' . $id])) $hijos['i' . $id] = ['id' => $id, 'codigo' => $w['codigo'] ?? null] + ($conHijos ? ['hijos' => []] : ['indicadores' => []]);
            }
        };
        // Promedio ponderado por el peso configurado de cada hijo; sin pesos, promedio simple
        $ponderar = function (array $items, array $pw) {
            $suma = 0.0;
            foreach ($items as $it) $suma += isset($pw[$it['id']]) ? (float)$pw[$it['id']]['porcentaje'] : 0;
            if ($suma > 0) {
                $total = 0.0;
                foreach ($items as $it) $total += (isset($pw[$it['id']]) ? (float)$pw[$it['id']]['porcentaje'] : 0) * $it['cumplimiento'];
                return $total / $suma;
            }
            return $items ? array_sum(array_column($items, 'cumplimiento')) / count($items) : 0;
        };

        if ($conVacios) $agregar($arbol, $pesos['linea'], null, true);
        $lineas = [];
        foreach ($arbol as $linea) {
            $lineaId = $linea['id'] ?: null;
            if ($conVacios && $lineaId !== null) $agregar($linea['hijos'], $pesos['motor'], $lineaId, true);
            $motores = [];
            foreach ($linea['hijos'] as $motor) {
                $motorId = $motor['id'] ?: null;
                if ($conVacios && $motorId !== null) $agregar($motor['hijos'], $pesos['proyecto'], $motorId, false);
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
                    $proyectos[] = ['id' => $proy['id'], 'cumplimiento' => round($cp, 2)];
                }
                $motores[] = ['id' => $motor['id'], 'cumplimiento' => round($ponderar($proyectos, $pesos['proyecto']), 2)];
            }
            $lineas[] = ['id' => $linea['id'], 'codigo' => $linea['codigo'] ?: '—', 'cumplimiento' => round($ponderar($motores, $pesos['motor']), 2)];
        }
        // 'pesos': el peso configurado de cada línea (null si no tiene): con él, lo que aporta la línea al plan = cumplimiento x peso
        $res = ['global' => round($ponderar($lineas, $pesos['linea']), 2), 'lineas' => [], 'pesos' => []];
        foreach ($lineas as $l) {
            $res['lineas'][(string)$l['codigo']] = (float)$l['cumplimiento'];
            $res['pesos'][(string)$l['codigo']] = isset($pesos['linea'][$l['id']]) ? round((float)$pesos['linea'][$l['id']]['porcentaje'], 2) : null;
        }
        return $res;
    }

    // Trimestre vigente del plan (0 a 4): el del selector del Dashboard o, si no, el del calendario. Una sola implementación (Modulo144Model).
    public function getTrimestreVigente($formulario_id, $anio) {
        require_once __DIR__ . '/Modulo144Model.php';
        return (new Modulo144Model())->getTrimestreVigente($formulario_id, $anio);
    }

    // El seguimiento se aprueba por trimestre: cada indicador trae, por trimestre, su valor (trimestreN_seguimiento), su estado de
    // aprobación (tN_estado: 2 = aprobado), si está en revisión o devuelto (tN_sol: 1 / 2) y la etapa del semáforo (tN_etapa).
    public function getReporteSeguimiento($formulario_id) {
        $res = ['indicadores' => [], 'evidencias' => [], 'facultades' => []];
        try {
            // Crea las columnas y la tabla de trimestres si todavía no existen (las consultas siguientes las necesitan)
            require_once __DIR__ . '/Modulo144Model.php';
            (new Modulo144Model())->asegurarColumnasDescripcionSemestres();
            $subTrim = '';
            foreach ([1, 2, 3, 4] as $n) {
                $sub = "(SELECT x.%s FROM seguimiento_trimestres_144 x WHERE x.formulacion_id = f.id AND x.trimestre = {$n})";
                $subTrim .= ", f.trimestre{$n}_seguimiento, " . sprintf($sub, 'estado') . " AS t{$n}_estado, " . sprintf($sub, 'solicitud') . " AS t{$n}_sol, "
                          . sprintf($sub, 'semaforo_etapa') . " AS t{$n}_etapa";
            }
            $stmt = $this->db->prepare(
                "SELECT f.id, f.nombre_borrador, f.nombre_indicador, f.formula_medicion, f.linea_estrategica, f.responsable_formulacion,
                        f.tipo_medicion, f.unidad_medida, f.meta_programada, f.meta_ejecutada, f.porcentaje_avance,
                        f.semestre1_seguimiento, f.semestre2_seguimiento, f.estado_seguimiento,
                        f.solicitud_estado_seguimiento, f.semaforo_etapa_seguimiento, f.gestionado_facultades{$subTrim},
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