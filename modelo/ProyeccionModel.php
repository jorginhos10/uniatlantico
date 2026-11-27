<?php
// modelo/ProyeccionModel.php
// "Proyección de metas": toma lo formulado y lo logrado en Planes de Acción de años anteriores y propone
// la meta de cada indicador para un año nuevo. Guarda cada proyección (nombre, planes base, año, método).
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/FORDE144Model.php';

class ProyeccionModel {
    private $db;

    const METODOS = [
        'form'   => 'Lo formulado en el año a proyectar',
        'prom'   => 'Promedio de lo logrado',
        'ultimo' => 'Último logrado',
        'mejor'  => 'Mejor logrado',
        'tend'   => 'Último logrado + tendencia',
        'meta'   => 'Repetir la última meta',
    ];

    public function __construct() {
        $this->db = new PDO("mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET, Config::DB_USER, Config::DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->db->exec("CREATE TABLE IF NOT EXISTS proyecciones_144 (
            id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(150) NOT NULL, formularios TEXT NOT NULL,
            anio_objetivo INT NOT NULL, metodo VARCHAR(10) NOT NULL DEFAULT 'prom', ajuste DECIMAL(6,2) NOT NULL DEFAULT 0,
            creado_por INT NULL, fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $this->db->exec("CREATE TABLE IF NOT EXISTS proyeccion_decisiones_144 (
            id INT AUTO_INCREMENT PRIMARY KEY, proyeccion_id INT NOT NULL, clave VARCHAR(220) NOT NULL,
            estado VARCHAR(10) NOT NULL, valor DECIMAL(18,4) NULL, decidido_por INT NULL, fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_proy_clave (proyeccion_id, clave)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        if ($this->db->query("SHOW COLUMNS FROM proyeccion_decisiones_144 LIKE 'comentario'")->rowCount() === 0) {
            $this->db->exec("ALTER TABLE proyeccion_decisiones_144 ADD COLUMN comentario TEXT NULL");
        }
    }

    // Decisiones sobre las metas propuestas: [clave => ['e' => 'aprobada'|'rechazada'|'', 'v' => meta final|null, 'c' => comentario]]
    public function decisiones($proyeccionId) {
        $s = $this->db->prepare("SELECT clave, estado, valor, comentario FROM proyeccion_decisiones_144 WHERE proyeccion_id = ?");
        $s->execute([(int)$proyeccionId]);
        $out = [];
        foreach ($s->fetchAll() as $r) $out[$r['clave']] = ['e' => $r['estado'], 'v' => $r['valor'] === null ? null : (float)$r['valor'], 'c' => (string)$r['comentario']];
        return $out;
    }

    // estado: 'aprobada', 'rechazada' o '' (vuelve a pendiente)
    public function decidir($proyeccionId, $clave, $estado, $valor, $uid) {
        if ($estado === '') {
            // No se borra la fila: el comentario del indicador se conserva
            $this->db->prepare("UPDATE proyeccion_decisiones_144 SET estado = '', valor = NULL, decidido_por = ?, fecha = NOW() WHERE proyeccion_id = ? AND clave = ?")
                     ->execute([$uid, (int)$proyeccionId, $clave]);
            return;
        }
        if (!in_array($estado, ['aprobada', 'rechazada'], true)) return;
        $this->db->prepare("INSERT INTO proyeccion_decisiones_144 (proyeccion_id, clave, estado, valor, decidido_por) VALUES (?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE estado = VALUES(estado), valor = VALUES(valor), decidido_por = VALUES(decidido_por), fecha = NOW()")
                 ->execute([(int)$proyeccionId, $clave, $estado, $valor, $uid]);
    }

    // Comentario sobre la meta de un indicador; se guarda esté aprobada o pendiente
    public function comentar($proyeccionId, $clave, $texto) {
        $this->db->prepare("INSERT INTO proyeccion_decisiones_144 (proyeccion_id, clave, estado, comentario) VALUES (?, ?, '', ?)
                            ON DUPLICATE KEY UPDATE comentario = VALUES(comentario)")
                 ->execute([(int)$proyeccionId, $clave, mb_substr(trim((string)$texto), 0, 1000)]);
    }

    public function listar() {
        return $this->db->query("SELECT * FROM proyecciones_144 ORDER BY fecha_creacion DESC")->fetchAll();
    }

    public function obtener($id) {
        $s = $this->db->prepare("SELECT * FROM proyecciones_144 WHERE id = ?");
        $s->execute([(int)$id]);
        $r = $s->fetch();
        if ($r) $r['ids'] = array_map('intval', (array)json_decode($r['formularios'], true));
        return $r ?: null;
    }

    public function crear($nombre, array $ids, $anio, $metodo, $ajuste, $uid) {
        if (!isset(self::METODOS[$metodo])) $metodo = 'prom';
        $this->db->prepare("INSERT INTO proyecciones_144 (nombre, formularios, anio_objetivo, metodo, ajuste, creado_por) VALUES (?, ?, ?, ?, ?, ?)")
                 ->execute([mb_substr($nombre, 0, 150), json_encode(array_values($ids)), (int)$anio, $metodo, (float)$ajuste, $uid]);
        return (int)$this->db->lastInsertId();
    }

    public function actualizar($id, $metodo, $ajuste, $anio, array $ids = null) {
        if (!isset(self::METODOS[$metodo])) $metodo = 'prom';
        $anio = ($anio >= 2000 && $anio <= 2100) ? (int)$anio : (int)date('Y');
        $this->db->prepare("UPDATE proyecciones_144 SET metodo = ?, ajuste = ?, anio_objetivo = ? WHERE id = ?")->execute([$metodo, (float)$ajuste, $anio, (int)$id]);
        if ($ids) $this->db->prepare("UPDATE proyecciones_144 SET formularios = ? WHERE id = ?")->execute([json_encode(array_values($ids)), (int)$id]);
    }

    // Cambia todos los datos de una proyección; las metas ya aprobadas se conservan
    public function editar($id, $nombre, array $ids, $anio, $metodo, $ajuste) {
        if (!isset(self::METODOS[$metodo])) $metodo = 'prom';
        $this->db->prepare("UPDATE proyecciones_144 SET nombre = ?, formularios = ?, anio_objetivo = ?, metodo = ?, ajuste = ? WHERE id = ?")
                 ->execute([mb_substr($nombre, 0, 150), json_encode(array_values($ids)), (int)$anio, $metodo, (float)$ajuste, (int)$id]);
    }

    public function eliminar($id) {
        $this->db->prepare("DELETE FROM proyeccion_decisiones_144 WHERE proyeccion_id = ?")->execute([(int)$id]);
        $this->db->prepare("DELETE FROM proyecciones_144 WHERE id = ?")->execute([(int)$id]);
    }

    // Clave comparable entre años: minúsculas, sin tildes ni signos
    public static function norm($s) {
        $s = mb_strtolower(trim((string)$s), 'UTF-8');
        $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n']);
        $s = preg_replace('/[^a-z0-9]+/', ' ', $s);
        return mb_substr(trim($s), 0, 200);
    }

    // Meta propuesta a partir del histórico. $hist: [idx => ['meta' => ?float, 'logrado' => ?float, 'anio' => int]] en orden de año.
    // Devuelve [valor|null, texto que explica de dónde sale].
    public static function proponer(array $hist, $metodo, $ajuste, $tipo, $unidad, $metaForm = null) {
        $logrados = []; $metas = [];
        foreach ($hist as $h) {
            if ($h['logrado'] !== null) $logrados[$h['anio']] = $h['logrado'];
            if ($h['meta'] !== null) $metas[$h['anio']] = $h['meta'];
        }
        $ultMeta = $metas ? end($metas) : null;
        $txt = '';
        $v = null;
        if ($metodo === 'form' && $metaForm !== null) { // lo ya formulado en el año a proyectar
            $v = $metaForm; $txt = 'Meta formulada en el año a proyectar: ' . self::f($v) . '.';
        } elseif ($tipo === 'Límite') { // en un límite menos es mejor: no se "sube", se mantiene el último límite
            $v = $ultMeta; $txt = $v === null ? '' : 'Se mantiene el último límite (' . self::f($v) . ').';
        } elseif ($metodo === 'meta') {
            $v = $ultMeta; $txt = $v === null ? '' : 'Repite la última meta formulada (' . self::f($v) . ').';
        } elseif ($logrados) {
            $anios = array_keys($logrados);
            $rango = count($anios) > 1 ? reset($anios) . '-' . end($anios) : (string)reset($anios);
            if ($metodo === 'ultimo') { $v = end($logrados); $txt = 'Último logrado (' . end($anios) . '): ' . self::f($v) . '.'; }
            elseif ($metodo === 'mejor') { $v = max($logrados); $txt = 'Mejor logrado (' . $rango . '): ' . self::f($v) . '.'; }
            elseif ($metodo === 'tend') {
                $vals = array_values($logrados);
                $inc = count($vals) > 1 ? (end($vals) - reset($vals)) / (count($vals) - 1) : 0;
                $v = end($vals) + $inc;
                $txt = 'Último logrado ' . self::f(end($vals)) . ' + incremento anual promedio ' . self::f($inc) . '.';
            } else { $v = array_sum($logrados) / count($logrados); $txt = 'Promedio de lo logrado ' . $rango . ': ' . self::f($v) . '.'; }
        } elseif ($ultMeta !== null) {
            $v = $ultMeta; $txt = 'Sin logrado aprobado: se toma la última meta formulada (' . self::f($ultMeta) . ').';
        }
        if ($v === null) return [null, 'Sin histórico para proponer.'];
        if ($ajuste != 0 && $tipo !== 'Límite') { $v *= (1 + $ajuste / 100); $txt .= ' Ajuste ' . ($ajuste > 0 ? '+' : '') . self::f($ajuste) . ' %.'; }
        $esPct = mb_stripos((string)$unidad, 'porcentaje') !== false;
        if ($esPct) $v = min(100, $v);
        $v = $esPct ? round($v, 1) : (abs($v) >= 10 ? round($v) : round($v, 1));
        return [max(0, $v), $txt];
    }

    private static function f($n) {
        return rtrim(rtrim(number_format((float)$n, 2, ',', '.'), '0'), ',');
    }

    // Palabras significativas de un nombre normalizado (para reconocer el mismo indicador aunque haya cambiado de redacción)
    private static function palabras($norm) {
        static $vacias = ['de','la','el','en','los','las','por','con','para','del','que','una','uno','al','y','a','o','un','se','su','sus','como','entre'];
        $p = [];
        foreach (explode(' ', $norm) as $w) if (mb_strlen($w) >= 3 && !in_array($w, $vacias, true)) $p[$w] = true;
        return array_keys($p);
    }
    private static function similitud(array $a, array $b) {
        if (!$a || !$b) return 0.0;
        $i = count(array_intersect($a, $b));
        $u = count($a) + count($b) - $i;
        return $u ? $i / $u : 0.0;
    }

    // Plan del año a proyectar con más indicadores formulados (puede haber varios formularios del mismo año)
    private function planFormulado($anio) {
        $s = $this->db->prepare("SELECT fo.id, fo.titulo, fo.anio, COUNT(f.id) c FROM formularios fo JOIN formulacion_144 f ON f.formulario_id = fo.id
                                 WHERE fo.anio = ? AND f.estado_formulacion != 1 GROUP BY fo.id, fo.titulo, fo.anio ORDER BY c DESC, fo.id DESC LIMIT 1");
        $s->execute([(int)$anio]);
        return $s->fetch() ?: null;
    }

    // [id de indicador => [nombres de facultad con distribución mayor que cero]]
    private function facultadesPorIndicador(array $ids) {
        $out = [];
        try {
            foreach (array_chunk(array_values(array_unique($ids)), 1000) as $grupo) {
                $s = $this->db->query("SELECT d.formulacion_id AS id, fa.nombre FROM formulacion_distribucion_facultad_144 d
                                       JOIN facultades fa ON fa.id = d.facultad_id
                                       WHERE d.formulacion_id IN (" . implode(',', array_map('intval', $grupo)) . ") AND d.distribucion > 0");
                foreach ($s->fetchAll() as $r) $out[(int)$r['id']][] = $r['nombre'];
            }
        } catch (PDOException $e) {
            error_log('Proyección, facultades: ' . $e->getMessage());
        }
        return $out;
    }

    // $planes: filas de formularios (id, titulo, anio) históricos, en el orden de las columnas.
    // Una fila por indicador: se reconoce el mismo indicador entre años por su nombre y, si cambió de redacción,
    // por su parecido dentro del mismo proyecto/línea (se marca en cada año donde el nombre es distinto).
    // Si el año a proyectar ya tiene formulación, esas son las filas base y su meta se muestra como "formulada".
    public function construir(array $planes, $anioObjetivo, $metodo, $ajuste) {
        $modelo = new FORDE144Model();
        $anios = [];
        $series = [];      // id => ['pal' => palabras, 'ub' => ubicación, idx => entrada, 'obj' => entrada del año a proyectar]
        $claves = [];      // clave de nombre (nk#n) => id de serie (también las de nombres anteriores)

        $ubic = function ($r) {
            return ['l' => ($r['linea_codigo'] ?: $r['linea']), 'm' => ($r['motor_codigo'] ?: $r['motor']), 'p' => ($r['proyecto_codigo'] ?: $r['proyecto'])];
        };
        // Casa un indicador con una serie: exacto por nombre o, si no, el más parecido que aún no tenga ese año
        $casar = function ($r, $nk, $occ, $slot) use (&$series, &$claves, $ubic) {
            $gk = $nk . '#' . $occ;
            if (isset($claves[$gk]) && !isset($series[$claves[$gk]][$slot])) return [$claves[$gk], false];
            $pal = self::palabras($nk);
            $u = $ubic($r);
            $mejor = null; $mejorS = 0;
            foreach ($series as $sid => $s) {
                if (isset($s[$slot])) continue;
                $mismoProy = $s['ub']['p'] === $u['p'] && $s['ub']['m'] === $u['m'] && $s['ub']['l'] === $u['l'];
                if (!$mismoProy && $s['ub']['l'] !== $u['l']) continue;
                $sc = self::similitud($pal, $s['pal']);
                if ($sc < ($mismoProy ? 0.5 : 0.75)) continue;
                if ($mismoProy) $sc += 0.1;
                if ($sc > $mejorS) { $mejorS = $sc; $mejor = $sid; }
            }
            return $mejor !== null ? [$mejor, true] : [null, false];
        };

        $recorrer = function ($inf, $slot, $pl) use (&$series, &$claves, $casar, $ubic) {
            $vistos = [];
            foreach (($inf['indicadores'] ?? []) as $r) {
                $nk = self::norm($r['nombre']);
                if ($nk === '') continue;
                $vistos[$nk] = ($vistos[$nk] ?? 0) + 1;
                $gk = $nk . '#' . $vistos[$nk];
                [$sid, $aprox] = $casar($r, $nk, $vistos[$nk], $slot);
                if ($sid === null) {
                    $sid = $gk;
                    $series[$sid] = ['pal' => self::palabras($nk), 'ub' => $ubic($r)];
                }
                $claves[$gk] = $claves[$gk] ?? $sid;
                $series[$sid]['pal'] = self::palabras($nk); // la redacción más reciente es la de referencia
                $series[$sid]['ub'] = $ubic($r);
                $series[$sid][$slot] = [
                    'id' => $r['id'], 'facultad' => $r['facultad'], 'anio' => (int)$pl['anio'], 'nombre' => $r['nombre'], 'nk' => $nk, 'formula' => $r['formula'], 'meta' => $r['meta'], 'logrado' => $r['logrado'], 'aprox' => $aprox,
                    'codigo' => $r['codigo'], 'resp' => $r['responsable'], 'unidad' => $r['unidad'], 'tipo' => $r['tipo'],
                    'linea' => ($r['linea_codigo'] ? $r['linea_codigo'] . ' - ' : '') . $r['linea'],
                    'motor' => ($r['motor_codigo'] ? $r['motor_codigo'] . ' - ' : '') . $r['motor'],
                    'proyecto' => ($r['proyecto_codigo'] ? $r['proyecto_codigo'] . ' - ' : '') . $r['proyecto'],
                ];
            }
        };

        foreach ($planes as $idx => $pl) {
            if ((int)$pl['anio'] >= (int)$anioObjetivo) continue; // el año a proyectar no entra como histórico
            $anios[$idx] = (int)$pl['anio'];
            $recorrer($modelo->getInformeCompleto((int)$pl['id'], ['indicadores' => true]), $idx, $pl);
        }

        // Formulación del año a proyectar (si existe): sus indicadores son las filas base
        $planObj = $this->planFormulado($anioObjetivo);
        if ($planObj) {
            $recorrer($modelo->getInformeCompleto((int)$planObj['id'], ['indicadores' => true, 'soloAprobadas' => false]), 'obj', ['anio' => $anioObjetivo]);
        }

        // Facultades de cada indicador (para el filtro): las que tienen distribución y la facultad del registro
        $ids = [];
        foreach ($series as $s) foreach ($s as $k => $e) if (is_int($k) || $k === 'obj') $ids[] = (int)$e['id'];
        $facPorId = $this->facultadesPorIndicador($ids);

        $res = [];
        foreach ($series as $sid => $s) {
            $hist = [];
            foreach ($s as $k => $e) if (is_int($k)) $hist[$k] = $e;
            ksort($hist);
            $form = $s['obj'] ?? null;
            $ref = $form ?: ($hist ? end($hist) : null);
            if (!$ref) continue;
            // Estado del indicador frente a la formulación del año a proyectar
            $st = '';
            if ($planObj) $st = $form ? ($hist ? '' : 'nuevo') : 'sinformular';
            // Nombre o fórmula distintos al de referencia en cada año histórico
            $h2 = [];
            foreach ($hist as $e) {
                $h2[] = ['anio' => $e['anio'], 'meta' => $e['meta'], 'logrado' => $e['logrado'],
                         'nm' => $e['nk'] !== $ref['nk'] ? $e['nombre'] : null,
                         'ff' => (trim($e['formula']) !== '' && self::norm($e['formula']) !== self::norm($ref['formula'])) ? $e['formula'] : null];
            }
            $f = ['hist' => $h2, 'nombre' => $ref['nombre'], 'codigo' => $ref['codigo'], 'responsable' => $ref['resp'], 'formula' => $ref['formula'],
                  'unidad' => $ref['unidad'], 'tipo' => $ref['tipo'], 'linea' => $ref['linea'], 'motor' => $ref['motor'], 'proyecto' => $ref['proyecto'],
                  'formulada' => $form ? $form['meta'] : null, 'st' => $st];
            [$prop, $txt] = self::proponer($h2, $metodo, $ajuste, $f['tipo'], $f['unidad'], $f['formulada']);
            // Estado según el cumplimiento del último año histórico con meta y logrado
            $pct = null;
            foreach (array_reverse($h2) as $h) {
                if ($h['meta'] !== null && $h['logrado'] !== null) {
                    $pct = $f['tipo'] === 'Límite' ? ($h['logrado'] <= $h['meta'] ? 100 : 0) : ($h['meta'] > 0 ? $h['logrado'] / $h['meta'] * 100 : null);
                    break;
                }
            }
            $f['pct'] = $pct;
            $f['estado'] = $pct === null ? 'Sin dato' : ($pct >= 100 ? 'Cumplido' : ($pct >= 60 ? 'Parcial' : 'No cumplido'));
            $f['propuesta'] = $prop;
            $f['descripcion'] = $txt;
            $f['presentes'] = count($h2);
            $f['clave'] = (string)$sid;
            $facs = [];
            foreach ($s as $k => $e) {
                if (!is_int($k) && $k !== 'obj') continue;
                foreach ($facPorId[(int)$e['id']] ?? [] as $n) $facs[$n] = true;
                if ($e['facultad'] !== '') $facs[$e['facultad']] = true;
            }
            $f['facultades'] = array_keys($facs);
            $res[] = $f;
        }
        usort($res, function ($a, $b) {
            return strnatcasecmp($a['linea'], $b['linea']) ?: strnatcasecmp($a['motor'], $b['motor']) ?: strnatcasecmp($a['proyecto'], $b['proyecto']) ?: strcasecmp($a['nombre'], $b['nombre']);
        });
        return ['anios' => array_values(array_unique($anios)), 'filas' => $res, 'anio_objetivo' => (int)$anioObjetivo, 'plan_objetivo' => $planObj ? $planObj['titulo'] : null];
    }
}
