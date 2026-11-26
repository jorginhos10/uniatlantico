<?php
// modelo/ComparativoModel.php
// Comparativos guardados entre Planes de Acción ("Así vamos") y su construcción:
// árbol línea → motor → proyecto → indicador con el avance de cada plan, y la "trascendencia" de cada indicador
// (en cuántos de los planes comparados aparece el mismo indicador).
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/FORDE144Model.php';

class ComparativoModel {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET, Config::DB_USER, Config::DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->db->exec("CREATE TABLE IF NOT EXISTS comparativos_144 (
            id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(150) NOT NULL, formularios TEXT NOT NULL,
            creado_por INT NULL, fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function listar() {
        return $this->db->query("SELECT * FROM comparativos_144 ORDER BY fecha_creacion DESC")->fetchAll();
    }

    public function obtener($id) {
        $s = $this->db->prepare("SELECT * FROM comparativos_144 WHERE id = ?");
        $s->execute([(int)$id]);
        $r = $s->fetch();
        if ($r) $r['ids'] = array_map('intval', (array)json_decode($r['formularios'], true));
        return $r ?: null;
    }

    public function crear($nombre, array $ids, $uid) {
        $this->db->prepare("INSERT INTO comparativos_144 (nombre, formularios, creado_por) VALUES (?, ?, ?)")
                 ->execute([mb_substr($nombre, 0, 150), json_encode(array_values($ids)), $uid]);
        return (int)$this->db->lastInsertId();
    }

    public function eliminar($id) {
        $this->db->prepare("DELETE FROM comparativos_144 WHERE id = ?")->execute([(int)$id]);
    }

    // Clave comparable entre años: minúsculas, sin tildes ni signos
    public static function norm($s) {
        $s = mb_strtolower(trim((string)$s), 'UTF-8');
        $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n']);
        $s = preg_replace('/[^a-z0-9]+/', ' ', $s);
        return mb_substr(trim($s), 0, 200);
    }

    // $planes: filas de formularios (id, titulo, anio) en el orden en que se muestran como columnas
    public function construir(array $planes) {
        $n = count($planes);
        $cabeceras = [];
        $arbol = [];     // lk => ['nombre','codigo','vals','motores' => [mk => [... 'proyectos' => [pk => [... 'inds' => []]]]]]
        $global = [];    // clave de indicador => ['nombre','codigo','resp','vals'=>[idx=>avance],'loc'=>[lk,mk,pk],'locIdx'=>idx]

        $modelo = new FORDE144Model();
        $clave = function ($codigo, $nombre) { return ($codigo && $codigo !== '—') ? 'c' . $codigo : 'n' . self::norm($nombre); };

        foreach ($planes as $idx => $pl) {
            $inf = $modelo->getInformeCompleto((int)$pl['id'], ['indicadores' => true]);
            $cabeceras[$idx] = [
                'id' => (int)$pl['id'], 'titulo' => $pl['titulo'], 'anio' => (int)$pl['anio'],
                'global' => (float)($inf['global_cumplimiento'] ?? 0), 'metas' => (int)($inf['metas_total'] ?? 0),
                'reportados' => (int)($inf['reportados'] ?? 0), 'trim' => (int)($inf['trimestre_reporte'] ?: 1),
            ];
            foreach (($inf['lineas'] ?? []) as $l) {
                $lk = $clave($l['codigo'], $l['nombre']);
                $arbol[$lk]['nombre'] = $arbol[$lk]['nombre'] ?? $l['titulo'];
                $arbol[$lk]['codigo'] = $arbol[$lk]['codigo'] ?? ($l['codigo'] !== '—' ? $l['codigo'] : '');
                $arbol[$lk]['vals'][$idx] = (float)$l['cumplimiento'];
                foreach ($l['motores'] as $m) {
                    $mk = $clave($m['codigo'], $m['nombre']);
                    $arbol[$lk]['motores'][$mk]['nombre'] = $arbol[$lk]['motores'][$mk]['nombre'] ?? ($m['codigo'] ? $m['codigo'] . ' - ' : '') . $m['nombre'];
                    $arbol[$lk]['motores'][$mk]['codigo'] = $arbol[$lk]['motores'][$mk]['codigo'] ?? (string)$m['codigo'];
                    $arbol[$lk]['motores'][$mk]['vals'][$idx] = (float)$m['cumplimiento'];
                    foreach ($m['proyectos'] as $p) {
                        $pk = $clave($p['codigo'], $p['nombre']);
                        $arbol[$lk]['motores'][$mk]['proyectos'][$pk]['nombre'] = $arbol[$lk]['motores'][$mk]['proyectos'][$pk]['nombre'] ?? ($p['codigo'] ? $p['codigo'] . ' - ' : '') . $p['nombre'];
                        $arbol[$lk]['motores'][$mk]['proyectos'][$pk]['codigo'] = $arbol[$lk]['motores'][$mk]['proyectos'][$pk]['codigo'] ?? (string)$p['codigo'];
                        $arbol[$lk]['motores'][$mk]['proyectos'][$pk]['vals'][$idx] = (float)$p['cumplimiento'];
                    }
                }
            }
            // Indicadores: el mismo nombre repetido dentro de un plan se numera para no mezclarlos
            $vistos = [];
            foreach (($inf['indicadores'] ?? []) as $r) {
                $nk = self::norm($r['nombre']);
                if ($nk === '') continue;
                $vistos[$nk] = ($vistos[$nk] ?? 0) + 1;
                $gk = $nk . '#' . $vistos[$nk];
                if (!isset($global[$gk])) $global[$gk] = ['nombre' => $r['nombre'], 'codigo' => $r['codigo'], 'resp' => $r['responsable'], 'vals' => []];
                $global[$gk]['vals'][$idx] = $r['avance'];
                // Se ubica donde aparece en el plan más reciente
                $global[$gk]['loc'] = [$clave($r['linea_codigo'], $r['linea']), $clave($r['motor_codigo'], $r['motor']), $clave($r['proyecto_codigo'], $r['proyecto']),
                                       $r['linea'], $r['motor'], $r['proyecto'], $r['linea_codigo'], $r['motor_codigo'], $r['proyecto_codigo']];
            }
        }

        // Cuelga cada indicador de su proyecto (creando los nodos que falten)
        foreach ($global as $gk => $g) {
            [$lk, $mk, $pk, $ln, $mn, $pn, $lc, $mc, $pc] = $g['loc'];
            $arbol[$lk]['nombre'] = $arbol[$lk]['nombre'] ?? (($lc ? $lc . ' - ' : '') . $ln);
            $arbol[$lk]['codigo'] = $arbol[$lk]['codigo'] ?? (string)$lc;
            $arbol[$lk]['motores'][$mk]['nombre'] = $arbol[$lk]['motores'][$mk]['nombre'] ?? (($mc ? $mc . ' - ' : '') . $mn);
            $arbol[$lk]['motores'][$mk]['codigo'] = $arbol[$lk]['motores'][$mk]['codigo'] ?? (string)$mc;
            $arbol[$lk]['motores'][$mk]['proyectos'][$pk]['nombre'] = $arbol[$lk]['motores'][$mk]['proyectos'][$pk]['nombre'] ?? (($pc ? $pc . ' - ' : '') . $pn);
            $arbol[$lk]['motores'][$mk]['proyectos'][$pk]['codigo'] = $arbol[$lk]['motores'][$mk]['proyectos'][$pk]['codigo'] ?? (string)$pc;
            $presentes = count($g['vals']);
            $vals = array_values(array_filter($g['vals'], function ($v) { return $v !== null; }));
            $arbol[$lk]['motores'][$mk]['proyectos'][$pk]['inds'][] = [
                'nombre' => $g['nombre'], 'codigo' => $g['codigo'], 'resp' => $g['resp'], 'vals' => $g['vals'],
                'presentes' => $presentes, 'todos' => $presentes === $n,
                'cambio' => $presentes >= 2 ? round(end($vals) - reset($vals), 1) : null,
            ];
        }

        // Estructura final ordenada, con el conteo de indicadores que trascienden (aparecen en 2 o más planes)
        $orden = function (array &$l) { uasort($l, function ($a, $b) { return strnatcasecmp(($a['codigo'] ?? '') ?: '~' . $a['nombre'], ($b['codigo'] ?? '') ?: '~' . $b['nombre']); }); };
        $orden($arbol);
        foreach ($arbol as $lk => &$L) {
            $L['vals'] = $L['vals'] ?? [];
            $L['total'] = $L['trasc'] = 0;
            $L['motores'] = $L['motores'] ?? [];
            $orden($L['motores']);
            foreach ($L['motores'] as &$M) {
                $M['vals'] = $M['vals'] ?? [];
                $M['total'] = $M['trasc'] = 0;
                $M['proyectos'] = $M['proyectos'] ?? [];
                $orden($M['proyectos']);
                foreach ($M['proyectos'] as &$P) {
                    $P['vals'] = $P['vals'] ?? [];
                    $P['inds'] = $P['inds'] ?? [];
                    usort($P['inds'], function ($a, $b) { return $b['presentes'] <=> $a['presentes'] ?: strcasecmp($a['nombre'], $b['nombre']); });
                    $P['total'] = count($P['inds']);
                    $P['trasc'] = count(array_filter($P['inds'], function ($i) { return $i['presentes'] >= 2; }));
                    $M['total'] += $P['total']; $M['trasc'] += $P['trasc'];
                }
                unset($P);
                $L['total'] += $M['total']; $L['trasc'] += $M['trasc'];
            }
            unset($M);
        }
        unset($L);

        $totalInd = count($global);
        $trasc = count(array_filter($global, function ($g) { return count($g['vals']) >= 2; }));
        $todos = count(array_filter($global, function ($g) use ($n) { return count($g['vals']) === $n; }));
        return ['planes' => $cabeceras, 'arbol' => $arbol, 'indicadores' => $totalInd, 'trascienden' => $trasc, 'en_todos' => $todos];
    }
}
