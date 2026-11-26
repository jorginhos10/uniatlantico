<?php
// modelo/ProyeccionModel.php
// "Proyección de metas": toma lo formulado y lo logrado en Planes de Acción de años anteriores y propone
// la meta de cada indicador para un año nuevo. Guarda cada proyección (nombre, planes base, año, método).
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/FORDE144Model.php';

class ProyeccionModel {
    private $db;

    const METODOS = [
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
    }

    // Decisiones sobre las metas propuestas: [clave => ['e' => 'aprobada'|'rechazada', 'v' => meta final|null]]
    public function decisiones($proyeccionId) {
        $s = $this->db->prepare("SELECT clave, estado, valor FROM proyeccion_decisiones_144 WHERE proyeccion_id = ?");
        $s->execute([(int)$proyeccionId]);
        $out = [];
        foreach ($s->fetchAll() as $r) $out[$r['clave']] = ['e' => $r['estado'], 'v' => $r['valor'] === null ? null : (float)$r['valor']];
        return $out;
    }

    // estado: 'aprobada', 'rechazada' o '' (vuelve a pendiente)
    public function decidir($proyeccionId, $clave, $estado, $valor, $uid) {
        if ($estado === '') {
            $this->db->prepare("DELETE FROM proyeccion_decisiones_144 WHERE proyeccion_id = ? AND clave = ?")->execute([(int)$proyeccionId, $clave]);
            return;
        }
        if (!in_array($estado, ['aprobada', 'rechazada'], true)) return;
        $this->db->prepare("INSERT INTO proyeccion_decisiones_144 (proyeccion_id, clave, estado, valor, decidido_por) VALUES (?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE estado = VALUES(estado), valor = VALUES(valor), decidido_por = VALUES(decidido_por), fecha = NOW()")
                 ->execute([(int)$proyeccionId, $clave, $estado, $valor, $uid]);
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
    public static function proponer(array $hist, $metodo, $ajuste, $tipo, $unidad) {
        $logrados = []; $metas = [];
        foreach ($hist as $h) {
            if ($h['logrado'] !== null) $logrados[$h['anio']] = $h['logrado'];
            if ($h['meta'] !== null) $metas[$h['anio']] = $h['meta'];
        }
        $ultMeta = $metas ? end($metas) : null;
        $txt = '';
        $v = null;
        if ($tipo === 'Límite') { // en un límite menos es mejor: no se "sube", se mantiene el último límite
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

    // $planes: filas de formularios (id, titulo, anio) en el orden de las columnas. Una fila por indicador (mismo nombre en distintos años = mismo indicador).
    public function construir(array $planes, $anioObjetivo, $metodo, $ajuste) {
        $modelo = new FORDE144Model();
        $anios = [];
        $filas = [];
        $n = count($planes);
        foreach ($planes as $idx => $pl) {
            $anios[$idx] = (int)$pl['anio'];
            $inf = $modelo->getInformeCompleto((int)$pl['id'], ['indicadores' => true]);
            $vistos = [];
            foreach (($inf['indicadores'] ?? []) as $r) {
                $nk = self::norm($r['nombre']);
                if ($nk === '') continue;
                $vistos[$nk] = ($vistos[$nk] ?? 0) + 1;
                $k = $nk . '#' . $vistos[$nk];
                if (!isset($filas[$k])) $filas[$k] = ['hist' => []];
                // Los datos descriptivos son los del plan más reciente en que aparece
                $filas[$k] = array_merge($filas[$k], [
                    'nombre' => $r['nombre'], 'codigo' => $r['codigo'], 'responsable' => $r['responsable'], 'formula' => $r['formula'],
                    'unidad' => $r['unidad'], 'tipo' => $r['tipo'],
                    'linea' => ($r['linea_codigo'] ? $r['linea_codigo'] . ' - ' : '') . $r['linea'],
                    'motor' => ($r['motor_codigo'] ? $r['motor_codigo'] . ' - ' : '') . $r['motor'],
                    'proyecto' => ($r['proyecto_codigo'] ? $r['proyecto_codigo'] . ' - ' : '') . $r['proyecto'],
                ]);
                $filas[$k]['hist'][$idx] = ['anio' => (int)$pl['anio'], 'meta' => $r['meta'], 'logrado' => $r['logrado']];
            }
        }
        $res = [];
        foreach ($filas as $clave => $f) {
            ksort($f['hist']);
            [$prop, $txt] = self::proponer($f['hist'], $metodo, $ajuste, $f['tipo'], $f['unidad']);
            // Estado según el cumplimiento del último año con meta y logrado
            $pct = null;
            foreach (array_reverse($f['hist'], true) as $h) {
                if ($h['meta'] !== null && $h['logrado'] !== null) {
                    $pct = $f['tipo'] === 'Límite' ? ($h['logrado'] <= $h['meta'] ? 100 : 0) : ($h['meta'] > 0 ? $h['logrado'] / $h['meta'] * 100 : null);
                    break;
                }
            }
            $f['pct'] = $pct;
            $f['estado'] = $pct === null ? 'Sin dato' : ($pct >= 100 ? 'Cumplido' : ($pct >= 60 ? 'Parcial' : 'No cumplido'));
            $f['propuesta'] = $prop;
            $f['descripcion'] = $txt;
            $f['presentes'] = count($f['hist']);
            $f['clave'] = $clave;
            $res[] = $f;
        }
        usort($res, function ($a, $b) {
            return strnatcasecmp($a['linea'], $b['linea']) ?: strnatcasecmp($a['motor'], $b['motor']) ?: strnatcasecmp($a['proyecto'], $b['proyecto']) ?: strcasecmp($a['nombre'], $b['nombre']);
        });
        return ['anios' => $anios, 'filas' => $res, 'anio_objetivo' => (int)$anioObjetivo];
    }
}
