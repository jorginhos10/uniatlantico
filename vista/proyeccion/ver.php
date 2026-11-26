<?php
// vista/proyeccion/ver.php
// Detalle de una proyección de metas, con el formato del seguimiento del FOR-DE-144:
// línea, motor, proyecto, responsable, indicador, fórmula, unidad, tipo, metas y cumplimiento de los años base,
// estado y META PROPUESTA para el año a proyectar.
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../modelo/FORDE144Model.php';
require_once __DIR__ . '/../../modelo/ProyeccionModel.php';

$titulo       = 'Proyección de metas';
$paginaActual = 'proyeccion';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

$uid     = (int)($_SESSION['usuario_id'] ?? 0);
$rolNorm = strtr(mb_strtolower(trim((string)($_SESSION['usuario_rol'] ?? '')), 'UTF-8'), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
$veTodo  = $uid === 1 || in_array($rolNorm, ['admin', 'administrador', 'sub administrador'], true);
if (!$veTodo) { header('Location: ' . $basePath . '/proyeccion'); exit; }

$pry = new ProyeccionModel();
$abierto = $pry->obtener((int)($_GET['id'] ?? $_POST['id'] ?? 0));
if (!$abierto) { header('Location: ' . $basePath . '/proyeccion'); exit; }

// Guardar el método y el ajuste elegidos
$forde = new FORDE144Model();
$todosPlanes = $forde->getAllAdmin();
usort($todosPlanes, function ($a, $b) { return ((int)$a['anio'] <=> (int)$b['anio']) ?: ((int)$a['id'] <=> (int)$b['id']); });
$idsValidos = array_map('intval', array_column($todosPlanes, 'id'));
$pedidosH = isset($_REQUEST['h']) && is_array($_REQUEST['h']) ? array_slice(array_values(array_intersect($idsValidos, array_map('intval', $_REQUEST['h']))), 0, 8) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'actualizar') {
    $pry->actualizar($abierto['id'], (string)($_POST['m'] ?? 'prom'), (float)str_replace(',', '.', (string)($_POST['a'] ?? 0)), (int)($_POST['y'] ?? 0), $pedidosH);
    header('Location: ' . $basePath . '/proyeccion/ver?id=' . (int)$abierto['id']);
    exit;
}

// Método y ajuste: lo guardado, o lo que se está probando por la URL (sin guardar)
$metodo = isset($_GET['m'], ProyeccionModel::METODOS[$_GET['m']]) ? $_GET['m'] : $abierto['metodo'];
$ajuste = isset($_GET['a']) ? (float)str_replace(',', '.', (string)$_GET['a']) : (float)$abierto['ajuste'];

// Años históricos: los elegidos en pantalla (sin guardar) o los de la proyección
$idsBase = $pedidosH ?: $abierto['ids'];
$planes = [];
foreach ($idsBase as $fid) { $f = $forde->getById($fid); if ($f) $planes[] = $f; }
usort($planes, function ($a, $b) { return ((int)$a['anio'] <=> (int)$b['anio']) ?: ((int)$a['id'] <=> (int)$b['id']); });
$anioDecidir = isset($_GET['y']) && (int)$_GET['y'] >= 2000 && (int)$_GET['y'] <= 2100 ? (int)$_GET['y'] : (int)$abierto['anio_objetivo'];
$datos = $pry->construir($planes, $anioDecidir, $metodo, $ajuste);

// Aprobar / rechazar metas propuestas (una o varias); responde JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'decidir') {
    header('Content-Type: application/json; charset=UTF-8');
    $claves = array_column($datos['filas'], 'clave');
    $estado = (string)($_POST['estado'] ?? '');
    $items = json_decode((string)($_POST['items'] ?? '[]'), true);
    $hechos = 0;
    if (is_array($items) && in_array($estado, ['aprobada', 'rechazada', ''], true)) {
        foreach ($items as $it) {
            $k = (string)($it['k'] ?? '');
            if (!in_array($k, $claves, true)) continue;
            $v = isset($it['v']) && is_numeric($it['v']) ? (float)$it['v'] : null;
            $pry->decidir($abierto['id'], $k, $estado, $estado === 'aprobada' ? $v : null, $uid);
            $hechos++;
        }
    }
    echo json_encode(['ok' => true, 'n' => $hechos]);
    exit;
}
$decisiones = $pry->decisiones($abierto['id']);
$anios = array_values(array_unique($datos['anios']));
$obj = $datos['anio_objetivo'];
$rango = $anios ? (count($anios) > 1 ? reset($anios) . '-' . end($anios) : (string)reset($anios)) : '';

// Formato de números: sin decimales si es entero; las unidades en porcentaje llevan %
$num = function ($v, $unidad) {
    if ($v === null) return null;
    $s = rtrim(rtrim(number_format((float)$v, 2, ',', '.'), '0'), ',');
    return $s . (mb_stripos((string)$unidad, 'porcentaje') !== false ? '%' : '');
};
$metasTxt = function ($f) use ($num) {
    $l = [];
    foreach ($f['hist'] as $hh) $l[] = $hh['anio'] . ': ' . ($hh['meta'] === null ? 'No formulado' : $num($hh['meta'], $f['unidad']));
    return $l;
};
$cumplTxt = function ($f) use ($num) {
    $l = [];
    foreach ($f['hist'] as $hh) {
        if ($hh['meta'] === null) { $l[] = $hh['anio'] . ': No formulado'; continue; }
        if ($hh['logrado'] === null) { $l[] = $hh['anio'] . ': sin dato'; continue; }
        $p = $f['tipo'] === 'Límite' ? ($hh['logrado'] <= $hh['meta'] ? 100 : 0) : ($hh['meta'] > 0 ? $hh['logrado'] / $hh['meta'] * 100 : null);
        $l[] = $hh['anio'] . ': ' . $num($hh['logrado'], $f['unidad']) . ($p === null ? '' : ' (' . number_format($p, 1, ',', '.') . '%)');
    }
    return $l;
};

// Exportar a CSV (se abre en Excel)
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="proyeccion-metas-' . $obj . '.csv"');
    echo "\xEF\xBB\xBF";
    $o = fopen('php://output', 'w');
    fputcsv($o, ['Línea estratégica', 'Motor de desarrollo', 'Proyecto', 'Responsable', 'Nombre del indicador', 'Fórmula del indicador', 'Unidad', 'Tipo de medición',
                 'Metas ' . $rango, 'Cumplimiento ' . $rango, 'Estado', 'Meta propuesta ' . $obj, 'Descripción ' . $obj, 'ID indicador', 'Decisión', 'Meta final ' . $obj], ';');
    foreach ($datos['filas'] as $f) {
        fputcsv($o, [$f['linea'], $f['motor'], $f['proyecto'], $f['responsable'], $f['nombre'], $f['formula'], $f['unidad'], $f['tipo'],
                     implode("\n", $metasTxt($f)), implode("\n", $cumplTxt($f)), $f['estado'],
                     $f['propuesta'] === null ? '' : $num($f['propuesta'], $f['unidad']), $f['descripcion'], $f['codigo'],
                     isset($decisiones[$f['clave']]) ? ucfirst($decisiones[$f['clave']]['e']) : 'Pendiente',
                     (isset($decisiones[$f['clave']]) && $decisiones[$f['clave']]['e'] === 'aprobada' && $decisiones[$f['clave']]['v'] !== null) ? $num($decisiones[$f['clave']]['v'], $f['unidad']) : ''], ';');
    }
    fclose($o);
    exit;
}

$cuenta = ['Cumplido' => 0, 'Parcial' => 0, 'No cumplido' => 0, 'Sin dato' => 0];
foreach ($datos['filas'] as $f) $cuenta[$f['estado']]++;

// Datos para el tablero (el navegador dibuja los gráficos, navega líneas → motores → proyectos y filtra sin recargar)
$js = [];
foreach ($datos['filas'] as $i => $f) {
    $hist = [];
    foreach ($f['hist'] as $hh) {
        $p = null;
        if ($hh['meta'] !== null && $hh['logrado'] !== null) {
            $p = $f['tipo'] === 'Límite' ? ($hh['logrado'] <= $hh['meta'] ? 100 : 0) : ($hh['meta'] > 0 ? $hh['logrado'] / $hh['meta'] * 100 : null);
        }
        $hist[] = ['a' => $hh['anio'], 'p' => $p === null ? null : round($p, 1),
                   'mf' => $hh['meta'] === null ? null : $num($hh['meta'], $f['unidad']), 'lf' => $hh['logrado'] === null ? null : $num($hh['logrado'], $f['unidad'])];
    }
    $ultMeta = null;
    foreach ($f['hist'] as $hh) if ($hh['meta'] !== null) $ultMeta = $hh['meta'];
    $js[] = ['i' => $i, 'k' => $f['clave'], 'n' => $f['nombre'], 'l' => $f['linea'], 'm' => $f['motor'], 'p' => $f['proyecto'], 'r' => $f['responsable'],
             'u' => $f['unidad'], 't' => $f['tipo'], 'f' => $f['formula'], 'c' => $f['codigo'], 'e' => $f['estado'],
             'pr' => $f['propuesta'], 'um' => $ultMeta, 'd' => $f['descripcion'], 'h' => $hist];
}
$jsonDatos = json_encode(['filas' => $js, 'anios' => $anios, 'obj' => $obj, 'dec' => (object)$decisiones, 'url' => $basePath . '/proyeccion/ver?id=' . (int)$abierto['id']],
                         JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);

ob_start();
?>
<style>
    /* Cabe en la pantalla sin desplazamiento: encabezado 64 px + 28 px de margen arriba y abajo */
    .pv { height: calc(100vh - 120px); min-height: 560px; display: flex; flex-direction: column; gap: .6rem; color: #1d1d1f; overflow: hidden; }
    .pv * { box-sizing: border-box; }
    .pv .barra { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .pv a.volver { color: #0071e3; text-decoration: none; font-size: .78rem; }
    .pv h1 { font-size: 1.3rem; font-weight: 800; margin: 0; letter-spacing: -.02em; line-height: 1.15; }
    .pv .sub { color: #86868b; font-size: .76rem; margin: 0; }
    .pv .ctl { display: flex; gap: .4rem; align-items: flex-end; flex-wrap: wrap; }
    .pv .ctl label { font-size: .65rem; font-weight: 600; color: #6e6e73; display: block; }
    .pv .ctl select, .pv .ctl input { padding: .32rem .5rem; border: 1px solid #d2d2d7; border-radius: 8px; font-size: .78rem; background: #fff; }
    .pv .hist-sel { position: relative; }
    .pv details.hist > summary { list-style: none; cursor: pointer; padding: .32rem .65rem; }
    .pv details.hist > summary::-webkit-details-marker { display: none; }
    .pv details.hist .menu { position: absolute; top: 100%; left: 0; z-index: 20; background: #fff; border: 1px solid #d2d2d7; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.15); padding: .4rem; min-width: 260px; margin-top: 4px; }
    .pv details.hist .menu label { display: flex; gap: .45rem; align-items: center; font-size: .78rem; font-weight: 500; color: #1d1d1f; padding: .3rem .4rem; border-radius: 6px; cursor: pointer; }
    .pv details.hist .menu label:hover { background: #f0f6ff; }
    .pv details.hist .menu b { min-width: 2.4rem; }
    .pv .btn { border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .34rem .65rem; cursor: pointer; font-size: .78rem; color: inherit; text-decoration: none; display: inline-block; }
    .pv .btn:hover { border-color: #0071e3; color: #0071e3; }
    .pv .btn.primario { background: #0071e3; border-color: #0071e3; color: #fff; font-weight: 600; }
    .pv .kpis { display: grid; grid-template-columns: repeat(5, 1fr); gap: .55rem; }
    .pv .kpi { background: #fff; border: 1px solid #e0e0e5; border-radius: 12px; padding: .45rem .8rem; position: relative; overflow: hidden; }
    .pv .kpi::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--c, #0071e3); }
    .pv .kpi b { font-size: 1.4rem; line-height: 1.1; display: block; color: var(--c, #0071e3); font-variant-numeric: tabular-nums; }
    .pv .kpi span { font-size: .7rem; color: #6e6e73; }
    .pv .cuerpo { flex: 1; min-height: 0; display: grid; grid-template-columns: 225px minmax(0, 1fr) minmax(0, 1.05fr); gap: .6rem; }
    @media (max-width: 1150px) { .pv .cuerpo { grid-template-columns: 1fr 1fr; } .pv .col-dona { display: none; } }
    .pv .panel { background: #fff; border: 1px solid #e0e0e5; border-radius: 12px; padding: .65rem .8rem; display: flex; flex-direction: column; min-height: 0; }
    .pv .panel h2 { font-size: .74rem; margin: 0 0 .45rem; text-transform: uppercase; letter-spacing: .05em; color: #6e6e73; display: flex; justify-content: space-between; align-items: center; gap: .5rem; }
    .pv .panel h2 em { font-style: normal; text-transform: none; letter-spacing: 0; font-weight: 500; font-size: .68rem; }
    .pv .limpiar { color: #0071e3; cursor: pointer; font-size: .7rem; display: none; text-transform: none; letter-spacing: 0; font-weight: 500; }
    /* Dona */
    .pv .dona { position: relative; width: 128px; height: 128px; margin: 0 auto .5rem; flex: none; }
    .pv .dona svg { width: 100%; height: 100%; transform: rotate(-90deg); }
    .pv .dona circle { fill: none; stroke-width: 20; cursor: pointer; transition: stroke-width .15s, opacity .15s; }
    .pv .dona circle:hover { stroke-width: 24; }
    .pv .dona .centro { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none; }
    .pv .dona .centro b { font-size: 1.4rem; line-height: 1; }
    .pv .dona .centro span { font-size: .64rem; color: #6e6e73; }
    .pv .leyenda { display: flex; flex-direction: column; gap: .15rem; }
    .pv .leyenda button { display: flex; align-items: center; gap: .45rem; border: 1px solid transparent; background: none; padding: .2rem .35rem; border-radius: 8px; font-size: .74rem; cursor: pointer; text-align: left; }
    .pv .leyenda button:hover, .pv .leyenda button.act { background: #f5f5f7; border-color: #d2d2d7; }
    .pv .leyenda i { width: 9px; height: 9px; border-radius: 50%; flex: none; }
    .pv .leyenda strong { margin-left: auto; font-variant-numeric: tabular-nums; }
    .pv .decide { margin-top: .6rem; padding-top: .55rem; border-top: 1px solid #ececf0; }
    .pv .decide .tit { font-size: .7rem; font-weight: 700; color: #6e6e73; text-transform: uppercase; letter-spacing: .05em; margin-bottom: .35rem; }
    .pv .progreso { height: 8px; border-radius: 4px; background: #ececf0; display: flex; overflow: hidden; margin-bottom: .4rem; }
    .pv .progreso i { display: block; height: 100%; }
    .pv .nota { margin-top: auto; font-size: .64rem; color: #86868b; line-height: 1.3; padding-top: .4rem; }
    /* Migas de pan y filas del desglose */
    .pv .migas { display: flex; gap: .25rem; align-items: center; flex-wrap: wrap; font-size: .76rem; margin-bottom: .45rem; }
    .pv .migas button { border: 0; background: #f0f0f3; border-radius: 999px; padding: .15rem .6rem; cursor: pointer; font-size: .74rem; }
    .pv .migas button:hover { background: #e0ecff; color: #0058b0; }
    .pv .migas button.ult { background: #0071e3; color: #fff; cursor: default; }
    .pv .migas span { color: #aeaeb2; }
    .pv .lista { flex: 1; min-height: 0; display: flex; flex-direction: column; gap: .3rem; overflow: hidden; }
    .pv .nodo { flex: none; height: 66px; border: 1px solid #ececf0; border-radius: 10px; padding: .3rem .6rem; display: flex; flex-direction: column; justify-content: space-between; cursor: pointer; transition: border-color .15s, background .15s; }
    .pv .nodo:hover { border-color: #0071e3; background: #f7fbff; }
    .pv .nodo.act { border-color: #0071e3; background: #eaf3ff; }
    .pv .nodo .t1 { display: flex; justify-content: space-between; align-items: baseline; gap: .5rem; }
    .pv .nodo .nom { font-size: .76rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0; }
    .pv .nodo .nom small { color: #86868b; font-weight: 500; margin-left: .3rem; font-size: .68rem; }
    .pv .nodo .ir { font-size: .68rem; color: #0071e3; white-space: nowrap; }
    .pv .nodo .t2 { display: flex; align-items: center; gap: .6rem; }
    .pv .cumple { flex: 1; min-width: 70px; }
    .pv .cumple .txt { display: flex; justify-content: space-between; font-size: .66rem; color: #6e6e73; margin-bottom: 2px; }
    .pv .cumple .txt b { font-size: .8rem; }
    .pv .cumple .bar { height: 7px; background: #ececf0; border-radius: 4px; overflow: hidden; }
    .pv .cumple .bar i { display: block; height: 100%; border-radius: 4px; }
    /* Años en orden, fáciles de leer */
    .pv .anios { display: flex; gap: 3px; flex: none; }
    .pv .yr { width: 38px; border-radius: 6px; text-align: center; padding: 1px 0 2px; line-height: 1.1; }
    .pv .yr small { display: block; font-size: .58rem; opacity: .75; font-weight: 600; }
    .pv .yr b { font-size: .78rem; font-variant-numeric: tabular-nums; }
    .pv .yr.v-ok { background: #e3f8e9; color: #1f6f3b; } .pv .yr.v-md { background: #fff1c9; color: #7a5b00; }
    .pv .yr.v-no { background: #ffe0dd; color: #b3001b; } .pv .yr.v-na { background: #f0f0f3; color: #a0a0a6; }
    .pv .sube { color: #1b5e20; } .pv .baja { color: #d70015; } .pv .igual { color: #86868b; }
    /* Indicadores con decisión */
    .pv .ind { flex: none; height: 76px; border: 1px solid #ececf0; border-radius: 10px; padding: .3rem .55rem; display: grid; grid-template-columns: 6px minmax(0, 1fr) 96px auto; gap: .5rem; align-items: center; transition: border-color .15s; }
    .pv .ind:hover { border-color: #0071e3; }
    .pv .ind.ap { background: #f3fbf5; border-color: #bfe3c5; } .pv .ind.re { background: #fff5f4; border-color: #f2c4bf; }
    .pv .ind .pt { width: 6px; height: 54px; border-radius: 3px; }
    .pv .ind .cen { min-width: 0; cursor: pointer; display: flex; flex-direction: column; gap: 4px; }
    .pv .ind .nom { font-size: .74rem; font-weight: 600; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pv .ind .fil2 { display: flex; align-items: center; gap: .5rem; }
    .pv .ind .cum { text-align: center; border-left: 1px solid #ececf0; border-right: 1px solid #ececf0; padding: 0 .3rem; line-height: 1.15; }
    .pv .ind .cum .lab { font-size: .62rem; color: #86868b; }
    .pv .ind .cum b { display: block; font-size: 1.05rem; font-variant-numeric: tabular-nums; }
    .pv .ind .cum small { display: block; font-size: .62rem; color: #6e6e73; }
    .pv .ind .der { text-align: right; min-width: 128px; }
    .pv .ind .der .lab { font-size: .62rem; color: #86868b; }
    .pv .ind .der b { display: block; font-size: 1.05rem; line-height: 1.1; color: #1b5e20; font-variant-numeric: tabular-nums; }
    .pv .ind .der b.tach { color: #b3001b; text-decoration: line-through; font-size: .85rem; }
    .pv .acc { display: flex; gap: .3rem; justify-content: flex-end; margin-top: 3px; }
    .pv .acc button { border: 1px solid #d2d2d7; background: #fff; border-radius: 7px; padding: .12rem .5rem; font-size: .68rem; font-weight: 600; cursor: pointer; }
    .pv .acc .si { color: #1f8a3d; border-color: #b9e0c3; } .pv .acc .si:hover { background: #e3f8e9; }
    .pv .acc .no { color: #d70015; border-color: #f2c4bf; } .pv .acc .no:hover { background: #ffe5e3; }
    .pv .acc .un { color: #6e6e73; }
    .pv .pager { display: flex; justify-content: space-between; align-items: center; padding-top: .4rem; font-size: .72rem; color: #6e6e73; }
    .pv .pager button { border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .2rem .6rem; cursor: pointer; }
    .pv .pager button[disabled] { opacity: .4; cursor: default; }
    .pv .vacio { color: #86868b; font-size: .85rem; text-align: center; padding: 1.5rem 0; }
    .pv .masivo { display: flex; gap: .3rem; }
    .pv .masivo button { border: 1px solid #d2d2d7; background: #fff; border-radius: 7px; padding: .15rem .5rem; font-size: .66rem; font-weight: 600; cursor: pointer; text-transform: none; letter-spacing: 0; }
    .pv .masivo .si { color: #1f8a3d; border-color: #b9e0c3; } .pv .masivo .no { color: #d70015; border-color: #f2c4bf; }
    /* Ventanas */
    .pv-modal { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: none; align-items: center; justify-content: center; z-index: 3000; padding: 1.5rem; }
    .pv-modal.abierto { display: flex; }
    .pv-modal .caja { background: #fff; border-radius: 14px; width: min(780px, 100%); max-height: 90vh; overflow: auto; padding: 1.2rem 1.4rem; box-shadow: 0 20px 60px rgba(0,0,0,.3); color: #1d1d1f; }
    .pv-modal .caja.ancha { width: min(1500px, 100%); }
    .pv-modal h3 { margin: 0 0 .2rem; font-size: 1.05rem; }
    .pv-modal .cerrar { float: right; border: 0; background: #f0f0f3; border-radius: 50%; width: 30px; height: 30px; cursor: pointer; font-size: 1rem; }
    .pv-modal .meta-linea { color: #6e6e73; font-size: .78rem; margin-bottom: .8rem; }
    .pv-modal table { border-collapse: collapse; width: 100%; font-size: .82rem; margin: .6rem 0; }
    .pv-modal th { background: #1f3864; color: #fff; padding: .4rem .6rem; text-align: center; }
    .pv-modal td { border-bottom: 1px solid #ececf0; padding: .4rem .6rem; text-align: center; }
    .pv-modal .barra-pct { height: 6px; background: #ececf0; border-radius: 3px; overflow: hidden; margin-top: 3px; }
    .pv-modal .barra-pct i { display: block; height: 100%; }
    .pv-modal .propuesta { background: #f1faf2; border: 1px solid #bfe3c5; border-radius: 12px; padding: .8rem 1rem; margin-top: .6rem; }
    .pv-modal .propuesta .lab { font-size: .78rem; color: #424245; }
    .pv-modal .propuesta b { font-size: 1.8rem; color: #1b5e20; display: block; line-height: 1.1; }
    .pv-modal .decidir { display: flex; gap: .6rem; align-items: flex-end; flex-wrap: wrap; margin-top: .8rem; }
    .pv-modal .decidir label { font-size: .72rem; font-weight: 600; color: #424245; display: block; }
    .pv-modal .decidir input { padding: .45rem .6rem; border: 1px solid #d2d2d7; border-radius: 8px; width: 140px; font-size: .95rem; }
    .pv-modal .decidir button { border: 0; border-radius: 8px; padding: .55rem 1.1rem; font-weight: 700; cursor: pointer; font-size: .85rem; }
    .pv-modal .b-si { background: #1f8a3d; color: #fff; } .pv-modal .b-no { background: #d70015; color: #fff; } .pv-modal .b-un { background: #f0f0f3; color: #424245; }
    .pv-modal dl { display: grid; grid-template-columns: 120px 1fr; gap: .25rem .8rem; font-size: .8rem; margin: 0; }
    .pv-modal dt { color: #86868b; } .pv-modal dd { margin: 0; }
    .pv-tabla { border-collapse: collapse; font-size: .72rem; width: max-content; min-width: 100%; }
    .pv-tabla th { background: #1f3864; color: #fff; padding: .4rem .5rem; border: 1px solid #fff; position: sticky; top: 0; }
    .pv-tabla th.prop { background: #1b5e20; }
    .pv-tabla td { border: 1px solid #d9d9de; padding: .3rem .5rem; text-align: center; }
    .pv-tabla td.t { text-align: left; }
    .pv-tabla td.apil div { white-space: nowrap; text-align: left; }
    .pv-tabla td.prop { font-weight: 800; color: #1b5e20; background: #f1faf2; }
    @media print { .pv .ctl, .pv a.volver, .pv .acc, .pv .masivo { display: none; } .pv { height: auto; } }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<section class="pv">
    <div class="barra">
        <div>
            <a class="volver" href="<?php echo $basePath; ?>/proyeccion">← Proyecciones</a>
            <h1><?php echo $h($abierto['nombre']); ?></h1>
            <p class="sub">Base <?php echo $h($rango); ?> → decidir las metas de <?php echo $obj; ?> · <?php echo $h(ProyeccionModel::METODOS[$metodo]); ?><?php echo $ajuste != 0 ? ' · ajuste ' . ($ajuste > 0 ? '+' : '') . $h($ajuste) . ' %' : ''; ?></p>
        </div>
        <form class="ctl" method="get" action="<?php echo $basePath; ?>/proyeccion/ver">
            <input type="hidden" name="id" value="<?php echo (int)$abierto['id']; ?>">
            <div class="hist-sel"><label>Años históricos</label>
                <details class="hist"><summary class="btn"><span id="histTxt"><?php echo $h($rango); ?></span> ▾</summary>
                    <div class="menu">
                        <?php foreach ($todosPlanes as $tp): ?>
                            <label><input type="checkbox" name="h[]" value="<?php echo (int)$tp['id']; ?>" <?php echo in_array((int)$tp['id'], $idsBase, true) ? 'checked' : ''; ?>>
                                <b><?php echo (int)$tp['anio']; ?></b> <?php echo $h(mb_strimwidth($tp['titulo'], 0, 32, '…', 'UTF-8')); ?></label>
                        <?php endforeach; ?>
                    </div>
                </details></div>
            <div><label for="y">Año a proyectar</label><input id="y" type="number" name="y" min="2000" max="2100" value="<?php echo (int)$obj; ?>" style="width:76px"></div>
            <div><label for="m">Cómo se calcula</label>
                <select id="m" name="m"><?php foreach (ProyeccionModel::METODOS as $k => $v): ?><option value="<?php echo $k; ?>" <?php echo $k === $metodo ? 'selected' : ''; ?>><?php echo $h($v); ?></option><?php endforeach; ?></select></div>
            <div><label for="a">Ajuste %</label><input id="a" type="number" step="0.5" name="a" value="<?php echo $h($ajuste); ?>" style="width:64px"></div>
            <button class="btn primario" type="submit">Recalcular</button>
            <button class="btn" type="submit" formmethod="post" name="accion" value="actualizar" title="Guarda el año, el método y el ajuste en esta proyección">Guardar</button>
            <button class="btn" type="button" id="btnTabla">Tabla completa</button>
            <a class="btn" href="<?php echo $basePath; ?>/proyeccion/ver?id=<?php echo (int)$abierto['id']; ?>&export=1<?php foreach ($idsBase as $hid) echo '&h[]=' . (int)$hid; ?>&y=<?php echo (int)$obj; ?>&m=<?php echo $h($metodo); ?>&a=<?php echo $h($ajuste); ?>">Excel</a>
        </form>
    </div>

    <div class="kpis" id="kpis"></div>

    <div class="cuerpo">
        <div class="panel col-dona">
            <h2>Estado último año <span class="limpiar" id="limpiarEstado">quitar</span></h2>
            <div class="dona" id="dona"></div>
            <div class="leyenda" id="leyenda"></div>
            <div class="decide">
                <div class="tit">Decisión metas <?php echo $obj; ?></div>
                <div class="progreso" id="progreso"></div>
                <div class="leyenda" id="leyDec"></div>
            </div>
            <div class="nota">Cumplido ≥ 100 %, parcial 60–99 %, no cumplido &lt; 60 % en el último año con meta y logrado aprobado.</div>
        </div>

        <div class="panel">
            <h2>Desglose <em>cumplimiento de la meta por año · clic para entrar</em></h2>
            <div class="migas" id="migas"></div>
            <div class="lista" id="nodos"></div>
            <div class="pager"><span id="pagInfoN"></span><span><button id="pagAntN">‹</button> <button id="pagSigN">›</button></span></div>
        </div>

        <div class="panel">
            <h2><span id="tituloLista">Indicadores</span>
                <span class="masivo"><button class="si" id="apTodas" title="Aprueba con la meta propuesta todos los pendientes de esta lista">✓ Aprobar pendientes</button><button class="no" id="reTodas">✕ Rechazar pendientes</button></span></h2>
            <div class="lista" id="lista"></div>
            <div class="pager"><span id="pagInfo"></span><span><button id="pagAnt">‹</button> <button id="pagSig">›</button></span></div>
        </div>
    </div>
</section>

<!-- Detalle y decisión de un indicador -->
<div class="pv-modal" id="mDetalle"><div class="caja" id="cDetalle"></div></div>

<!-- Tabla completa con el formato del FOR-DE-144 -->
<div class="pv-modal" id="mTabla">
    <div class="caja ancha">
        <button class="cerrar" data-cerrar aria-label="Cerrar">×</button>
        <h3>Tabla completa · <?php echo $h($abierto['nombre']); ?></h3>
        <div style="max-height:72vh;overflow:auto;margin-top:.6rem;">
        <table class="pv-tabla">
            <thead><tr>
                <th>Línea estratégica</th><th>Motor de desarrollo</th><th>Proyecto</th><th>Responsable</th><th>Nombre del indicador</th><th>Fórmula del indicador</th>
                <th>Unidad</th><th>Tipo de medición</th><th>Metas <?php echo $h($rango); ?></th><th>Cumplimiento <?php echo $h($rango); ?></th><th>Estado</th>
                <th class="prop">Meta propuesta <?php echo $obj; ?></th><th class="prop">Descripción <?php echo $obj; ?></th><th class="prop">Decisión</th><th class="prop">Meta final <?php echo $obj; ?></th><th>ID indicador</th>
            </tr></thead>
            <tbody>
            <?php foreach ($datos['filas'] as $f): $dc = $decisiones[$f['clave']] ?? null; ?>
                <tr>
                    <td class="t"><?php echo $h($f['linea']); ?></td><td class="t"><?php echo $h($f['motor']); ?></td><td class="t"><?php echo $h($f['proyecto']); ?></td>
                    <td class="t"><?php echo $h($f['responsable']); ?></td><td class="t"><?php echo $h($f['nombre']); ?></td><td class="t"><?php echo $h($f['formula']); ?></td>
                    <td><?php echo $h($f['unidad']); ?></td><td><?php echo $h($f['tipo']); ?></td>
                    <td class="apil"><?php foreach ($metasTxt($f) as $l): ?><div><?php echo $h($l); ?></div><?php endforeach; ?></td>
                    <td class="apil"><?php foreach ($cumplTxt($f) as $l): ?><div><?php echo $h($l); ?></div><?php endforeach; ?></td>
                    <td><?php echo $h($f['estado']); ?></td>
                    <td class="prop"><?php echo $f['propuesta'] === null ? '—' : $h($num($f['propuesta'], $f['unidad'])); ?></td>
                    <td class="t"><?php echo $h($f['descripcion']); ?></td>
                    <td><?php echo $dc ? $h(ucfirst($dc['e'])) : 'Pendiente'; ?></td>
                    <td class="prop"><?php echo ($dc && $dc['e'] === 'aprobada' && $dc['v'] !== null) ? $h($num($dc['v'], $f['unidad'])) : '—'; ?></td>
                    <td><?php echo $h($f['codigo']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
(function () {
    var D = <?php echo $jsonDatos; ?>;
    var COL = { 'Cumplido': '#1f8a3d', 'Parcial': '#e0a100', 'No cumplido': '#d70015', 'Sin dato': '#aeaeb2' };
    var ORDEN = ['No cumplido', 'Parcial', 'Cumplido', 'Sin dato'];
    var fEstado = '', fDec = '', ruta = [], pagina = 0, paginaN = 0;
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    var fmt = function (n, d) { return Number(n).toLocaleString('es-CO', { maximumFractionDigits: d == null ? 1 : d }); };
    var colPct = function (p) { return p == null ? '#d9d9de' : (p >= 100 ? COL['Cumplido'] : (p >= 60 ? COL['Parcial'] : COL['No cumplido'])); };
    var clsPct = function (p) { return p == null ? 'v-na' : (p >= 100 ? 'v-ok' : (p >= 60 ? 'v-md' : 'v-no')); };
    var val = function (v, u) { return v == null ? '—' : fmt(v, 2) + (/porcentaje/i.test(u || '') ? '%' : ''); };
    var decDe = function (f) { return D.dec[f.k] || null; };
    var estadoDec = function (f) { var d = decDe(f); return d ? d.e : 'pendiente'; };
    var ULT = function (f) { var u = null; f.h.forEach(function (x) { if (x.p != null) u = x.p; }); return u; };

    // Filas que cumplen el desglose actual (línea / motor / proyecto) y los filtros de estado y decisión
    function enRuta(f) { return (!ruta[0] || f.l === ruta[0]) && (!ruta[1] || f.m === ruta[1]) && (!ruta[2] || f.p === ruta[2]); }
    function visibles() { return D.filas.filter(function (f) { return enRuta(f) && (!fEstado || f.e === fEstado) && (!fDec || estadoDec(f) === fDec); }); }

    // Cumplimiento medio de la meta por año (cada indicador topado en 100 %) y el del último año con datos
    function porAnio(fs) {
        return D.anios.map(function (a) {
            var v = [];
            fs.forEach(function (f) { f.h.forEach(function (x) { if (x.a === a && x.p != null) v.push(Math.min(x.p, 100)); }); });
            return v.length ? v.reduce(function (s, x) { return s + x; }, 0) / v.length : null;
        });
    }
    function cumplidoGeneral(fs) {
        var v = [];
        fs.forEach(function (f) { var u = ULT(f); if (u != null) v.push(Math.min(u, 100)); });
        return v.length ? v.reduce(function (s, x) { return s + x; }, 0) / v.length : null;
    }
    function variacion(fs) {
        var v = [];
        fs.forEach(function (f) { if (f.pr != null && f.um > 0) v.push((f.pr / f.um - 1) * 100); });
        return v.length ? v.reduce(function (s, x) { return s + x; }, 0) / v.length : null;
    }
    // Años en orden, con el año arriba y el % abajo
    function chips(vals) {
        return '<div class="anios">' + D.anios.map(function (a, i) {
            var v = vals[i];
            return '<div class="yr ' + clsPct(v) + '" title="' + a + (v == null ? ': sin dato' : ': ' + fmt(v) + ' %') + '"><small>' + a + '</small><b>' + (v == null ? '—' : Math.round(v) + '%') + '</b></div>';
        }).join('') + '</div>';
    }

    function pintarKpis() {
        var t = D.filas.length, c = {}; ORDEN.forEach(function (e) { c[e] = 0; });
        D.filas.forEach(function (f) { c[f.e]++; });
        var cg = cumplidoGeneral(D.filas), vr = variacion(D.filas);
        var ap = 0, re = 0; D.filas.forEach(function (f) { var e = estadoDec(f); if (e === 'aprobada') ap++; else if (e === 'rechazada') re++; });
        var k = [
            [t, 'indicadores en la base', '#0071e3'],
            [cg == null ? '—' : fmt(cg, 0) + ' %', 'de la meta general cumplida (último año)', colPct(cg)],
            [c['Cumplido'] + ' / ' + t, 'indicadores cumplidos', COL['Cumplido']],
            [vr == null ? '—' : (vr > 0 ? '+' : '') + fmt(vr) + ' %', 'variación media de la meta ' + D.obj, '#6a4cc4'],
            [(ap + re) + ' / ' + t, 'decididas · ' + ap + ' aprobadas · ' + re + ' rechazadas', '#1b5e20']
        ];
        $('kpis').innerHTML = k.map(function (x) { return '<div class="kpi" style="--c:' + x[2] + '"><b>' + x[0] + '</b><span>' + x[1] + '</span></div>'; }).join('');
    }

    function pintarDona() {
        var base = D.filas.filter(function (f) { return enRuta(f) && (!fDec || estadoDec(f) === fDec); });
        var c = {}; ORDEN.forEach(function (e) { c[e] = 0; });
        base.forEach(function (f) { c[f.e]++; });
        var tot = base.length || 1, R = 46, L = 2 * Math.PI * R, off = 0, s = '<svg viewBox="0 0 128 128">';
        ORDEN.forEach(function (e) {
            if (!c[e]) return;
            var len = L * c[e] / tot;
            s += '<circle data-e="' + e + '" cx="64" cy="64" r="' + R + '" stroke="' + COL[e] + '" stroke-dasharray="' + len + ' ' + (L - len) + '" stroke-dashoffset="' + (-off) + '" opacity="' + (!fEstado || fEstado === e ? 1 : .25) + '"><title>' + e + ': ' + c[e] + '</title></circle>';
            off += len;
        });
        $('dona').innerHTML = s + '</svg><div class="centro"><b>' + base.length + '</b><span>indicadores</span></div>';
        $('leyenda').innerHTML = ORDEN.map(function (e) {
            return '<button data-e="' + e + '" class="' + (fEstado === e ? 'act' : '') + '"><i style="background:' + COL[e] + '"></i>' + e + '<strong>' + c[e] + '</strong></button>';
        }).join('');
        document.querySelectorAll('#dona circle, #leyenda button').forEach(function (el) {
            el.addEventListener('click', function () { fEstado = fEstado === el.dataset.e ? '' : el.dataset.e; pagina = 0; pintar(); });
        });
        $('limpiarEstado').style.display = fEstado ? 'inline' : 'none';

        // Decisión sobre las metas propuestas
        var d = { aprobada: 0, rechazada: 0, pendiente: 0 };
        D.filas.filter(function (f) { return enRuta(f) && (!fEstado || f.e === fEstado); }).forEach(function (f) { d[estadoDec(f)]++; });
        var T = d.aprobada + d.rechazada + d.pendiente || 1;
        $('progreso').innerHTML = '<i style="width:' + d.aprobada * 100 / T + '%;background:#1f8a3d"></i><i style="width:' + d.rechazada * 100 / T + '%;background:#d70015"></i>';
        var meta = { aprobada: ['Aprobadas', '#1f8a3d'], rechazada: ['Rechazadas', '#d70015'], pendiente: ['Pendientes', '#aeaeb2'] };
        $('leyDec').innerHTML = Object.keys(meta).map(function (k) {
            return '<button data-d="' + k + '" class="' + (fDec === k ? 'act' : '') + '"><i style="background:' + meta[k][1] + '"></i>' + meta[k][0] + '<strong>' + d[k] + '</strong></button>';
        }).join('');
        document.querySelectorAll('#leyDec button').forEach(function (el) {
            el.addEventListener('click', function () { fDec = fDec === el.dataset.d ? '' : el.dataset.d; pagina = 0; pintar(); });
        });
    }

    // Desglose: líneas → motores → proyectos (cada uno con cuánto de su meta se cumplió)
    function pintarNodos() {
        var nivel = ruta.length >= 3 ? 2 : ruta.length, campo = ['l', 'm', 'p'][nivel], grupos = {}, orden = [];
        D.filas.filter(function (f) { return enRuta2(f, nivel) && (!fEstado || f.e === fEstado) && (!fDec || estadoDec(f) === fDec); }).forEach(function (f) {
            if (!grupos[f[campo]]) { grupos[f[campo]] = []; orden.push(f[campo]); }
            grupos[f[campo]].push(f);
        });
        orden.sort(function (a, b) { return a.localeCompare(b, 'es', { numeric: true }); });

        var etiq = ['Todas las líneas'].concat(ruta.slice(0, 3).map(function (x) { var m = x.match(/^([A-Z]\d+)\s*-/); return m ? m[1] : x; }));
        $('migas').innerHTML = etiq.map(function (t, i) {
            var ult = i === Math.min(ruta.length, 3);
            return (i ? '<span>›</span>' : '') + '<button data-n="' + i + '" class="' + (ult ? 'ult' : '') + '" ' + (ult ? 'disabled' : '') + ' title="' + esc(i ? ruta[i - 1] : '') + '">' + esc(t) + '</button>';
        }).join('');
        document.querySelectorAll('#migas button').forEach(function (b) {
            b.addEventListener('click', function () { ruta = ruta.slice(0, +b.dataset.n); pagina = 0; paginaN = 0; pintar(); });
        });

        var alto = $('nodos').clientHeight || 300, porPag = Math.max(3, Math.floor((alto + 5) / 71));
        var paginas = Math.max(1, Math.ceil(orden.length / porPag));
        if (paginaN >= paginas) paginaN = paginas - 1;
        var vis = orden.slice(paginaN * porPag, (paginaN + 1) * porPag);
        $('nodos').innerHTML = vis.length ? vis.map(function (n) {
            var fs = grupos[n], cg = cumplidoGeneral(fs), cu = fs.filter(function (f) { return f.e === 'Cumplido'; }).length;
            var m = n.match(/^([A-Z]\d+)\s*-\s*(.*)$/), sel = ruta[nivel] === n;
            return '<div class="nodo ' + (sel ? 'act' : '') + '" data-n="' + esc(n) + '"><div class="t1"><div class="nom" title="' + esc(n) + '">' + esc(m ? m[2] : n) + '<small>' + (m ? m[1] + ' · ' : '') + fs.length + ' ind. · ' + cu + ' cumplidos</small></div>' +
                '<span class="ir">' + (nivel < 2 ? 'entrar ›' : (sel ? 'filtrando' : 'ver indicadores')) + '</span></div>' +
                '<div class="t2">' + chips(porAnio(fs)) + '<div class="cumple"><div class="txt"><span>meta cumplida</span><b style="color:' + colPct(cg) + '">' + (cg == null ? '—' : fmt(cg, 0) + ' %') + '</b></div><div class="bar"><i style="width:' + (cg || 0) + '%;background:' + colPct(cg) + '"></i></div></div></div></div>';
        }).join('') : '<div class="vacio">Sin elementos con este filtro.</div>';
        $('pagInfoN').textContent = orden.length ? (paginaN * porPag + 1) + '–' + Math.min(orden.length, (paginaN + 1) * porPag) + ' de ' + orden.length : '0';
        $('pagAntN').disabled = paginaN === 0; $('pagSigN').disabled = paginaN >= paginas - 1;
        document.querySelectorAll('#nodos .nodo').forEach(function (el) {
            el.addEventListener('click', function () {
                var n = el.dataset.n;
                if (nivel < 2) ruta = ruta.slice(0, nivel).concat([n]);
                else ruta = ruta[2] === n ? ruta.slice(0, 2) : ruta.slice(0, 2).concat([n]);
                pagina = 0; paginaN = 0; pintar();
            });
        });
    }
    // En el desglose se muestran los hermanos del nivel actual: solo se filtra por los niveles superiores
    function enRuta2(f, nivel) { return (nivel < 1 || f.l === ruta[0]) && (nivel < 2 || f.m === ruta[1]); }

    function filaInd(f) {
        var d = decDe(f), est = d ? d.e : 'pendiente';
        var der;
        if (est === 'aprobada') der = '<div class="lab">Meta aprobada ' + D.obj + '</div><b>' + val(d.v != null ? d.v : f.pr, f.u) + '</b><div class="acc"><button class="un" data-a="un" data-k="' + esc(f.k) + '">Deshacer</button></div>';
        else if (est === 'rechazada') der = '<div class="lab">Propuesta rechazada</div><b class="tach">' + val(f.pr, f.u) + '</b><div class="acc"><button class="un" data-a="un" data-k="' + esc(f.k) + '">Deshacer</button></div>';
        else der = '<div class="lab">Propuesta ' + D.obj + '</div><b>' + val(f.pr, f.u) + '</b>' + (f.pr == null ? '' : '<div class="acc"><button class="si" data-a="si" data-k="' + esc(f.k) + '">✓ Aprobar</button><button class="no" data-a="no" data-k="' + esc(f.k) + '">✕ Rechazar</button></div>');
        // Meta cumplida: lo logrado en el último año con dato, frente a la meta de ese año
        var ux = null; f.h.forEach(function (x) { if (x.lf != null) ux = x; });
        var cum = ux ? '<div class="lab">Cumplida ' + ux.a + '</div><b style="color:' + colPct(ux.p) + '">' + esc(ux.lf) + '</b><small>de ' + (ux.mf == null ? '—' : esc(ux.mf)) + (ux.p == null ? '' : ' · ' + fmt(ux.p, 0) + '%') + '</small>'
                     : '<div class="lab">Cumplida</div><b style="color:#aeaeb2">—</b><small>sin dato aprobado</small>';
        return '<div class="ind ' + (est === 'aprobada' ? 'ap' : (est === 'rechazada' ? 're' : '')) + '" data-i="' + f.i + '"><span class="pt" style="background:' + COL[f.e] + '" title="' + f.e + '"></span>' +
            '<div class="cen" data-i="' + f.i + '"><div class="nom" title="' + esc(f.n) + '">' + esc(f.n) + '</div><div class="fil2">' +
            chips(D.anios.map(function (a) { var x = f.h.filter(function (y) { return y.a === a; })[0]; return x ? x.p : null; })) + '</div></div>' +
            '<div class="cum">' + cum + '</div><div class="der">' + der + '</div></div>';
    }

    function pintarLista() {
        var fs = visibles().slice().sort(function (a, b) { return ORDEN.indexOf(a.e) - ORDEN.indexOf(b.e) || a.n.localeCompare(b.n, 'es'); });
        var alto = $('lista').clientHeight || 300, porPag = Math.max(3, Math.floor((alto + 5) / 81));
        var paginas = Math.max(1, Math.ceil(fs.length / porPag));
        if (pagina >= paginas) pagina = paginas - 1;
        var vis = fs.slice(pagina * porPag, (pagina + 1) * porPag);
        $('tituloLista').textContent = (ruta.length || fEstado || fDec) ? 'Indicadores filtrados' : 'Indicadores · decidir metas';
        $('lista').innerHTML = vis.length ? vis.map(filaInd).join('') : '<div class="vacio">Sin indicadores con este filtro.</div>';
        $('pagInfo').textContent = fs.length ? (pagina * porPag + 1) + '–' + Math.min(fs.length, (pagina + 1) * porPag) + ' de ' + fs.length : '0';
        $('pagAnt').disabled = pagina === 0; $('pagSig').disabled = pagina >= paginas - 1;
        document.querySelectorAll('#lista .cen').forEach(function (el) { el.addEventListener('click', function () { detalle(+el.dataset.i); }); });
        document.querySelectorAll('#lista .acc button').forEach(function (b) {
            b.addEventListener('click', function (e) {
                e.stopPropagation();
                var f = D.filas.filter(function (x) { return x.k === b.dataset.k; })[0];
                decidir([f], b.dataset.a === 'si' ? 'aprobada' : (b.dataset.a === 'no' ? 'rechazada' : ''));
            });
        });
    }

    // Guarda la decisión en el servidor y actualiza el tablero
    function decidir(fs, estado, valores) {
        var items = fs.filter(function (f) { return estado === '' || estado === 'rechazada' || (valores && valores[f.k] != null) || f.pr != null; })
                      .map(function (f) { return { k: f.k, v: valores && valores[f.k] != null ? valores[f.k] : f.pr }; });
        if (!items.length) return;
        var fd = new FormData(); fd.append('accion', 'decidir'); fd.append('estado', estado); fd.append('items', JSON.stringify(items));
        fetch(D.url, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (r) {
            if (!r.ok) throw new Error();
            items.forEach(function (it) { if (estado === '') delete D.dec[it.k]; else D.dec[it.k] = { e: estado, v: estado === 'aprobada' ? it.v : null }; });
            pintar();
        }).catch(function () { alert('No se pudo guardar la decisión. Intenta de nuevo.'); });
    }

    function detalle(i) {
        var f = D.filas[i], d = decDe(f), sug = d && d.v != null ? d.v : f.pr;
        var filas = f.h.map(function (x) {
            return '<tr><td><b>' + x.a + '</b></td><td>' + (x.mf == null ? '<span style="color:#aeaeb2">No formulado</span>' : esc(x.mf)) + '</td><td>' + (x.lf == null ? '<span style="color:#aeaeb2">sin dato</span>' : esc(x.lf)) + '</td>' +
                '<td>' + (x.p == null ? '—' : fmt(x.p) + ' %<div class="barra-pct"><i style="width:' + Math.min(100, x.p) + '%;background:' + colPct(x.p) + '"></i></div>') + '</td></tr>';
        }).join('');
        $('cDetalle').innerHTML = '<button class="cerrar" data-cerrar aria-label="Cerrar">×</button><h3>' + esc(f.n) + '</h3>' +
            '<div class="meta-linea">' + esc(f.l) + ' › ' + esc(f.m) + ' › ' + esc(f.p) + '</div>' +
            '<dl><dt>Responsable</dt><dd>' + esc(f.r) + '</dd><dt>Fórmula</dt><dd>' + esc(f.f) + '</dd><dt>Unidad · tipo</dt><dd>' + esc(f.u) + ' · ' + esc(f.t) + '</dd><dt>ID indicador</dt><dd>' + esc(f.c) + '</dd>' +
            '<dt>Estado último año</dt><dd><b style="color:' + COL[f.e] + '">' + f.e + '</b></dd></dl>' +
            '<table><thead><tr><th>Año</th><th>Meta</th><th>Logrado</th><th>Cumplimiento</th></tr></thead><tbody>' + filas + '</tbody></table>' +
            '<div class="propuesta"><div class="lab">Meta propuesta para ' + D.obj + '</div><b>' + val(f.pr, f.u) + '</b><div class="lab">' + esc(f.d) + '</div>' +
            '<div class="decidir"><div><label for="vFinal">Meta a aprobar (puedes ajustarla)</label><input id="vFinal" type="number" step="any" value="' + (sug == null ? '' : sug) + '"></div>' +
            '<button class="b-si" id="bSi">✓ Aprobar</button><button class="b-no" id="bNo">✕ Rechazar</button>' + (d ? '<button class="b-un" id="bUn">Dejar pendiente</button>' : '') + '</div>' +
            (d ? '<div class="lab" style="margin-top:.5rem">Estado actual: <b>' + (d.e === 'aprobada' ? 'aprobada con ' + val(d.v, f.u) : 'rechazada') + '</b></div>' : '') + '</div>';
        $('mDetalle').classList.add('abierto');
        $('bSi').onclick = function () {
            var v = parseFloat($('vFinal').value);
            if (isNaN(v)) { alert('Escribe la meta que vas a aprobar.'); return; }
            var o = {}; o[f.k] = v; decidir([f], 'aprobada', o); $('mDetalle').classList.remove('abierto');
        };
        $('bNo').onclick = function () { decidir([f], 'rechazada'); $('mDetalle').classList.remove('abierto'); };
        if ($('bUn')) $('bUn').onclick = function () { decidir([f], ''); $('mDetalle').classList.remove('abierto'); };
    }

    function pintar() { pintarKpis(); pintarDona(); pintarNodos(); pintarLista(); }

    $('limpiarEstado').addEventListener('click', function () { fEstado = ''; pagina = 0; pintar(); });
    $('pagAnt').addEventListener('click', function () { pagina = Math.max(0, pagina - 1); pintarLista(); });
    $('pagSig').addEventListener('click', function () { pagina++; pintarLista(); });
    $('pagAntN').addEventListener('click', function () { paginaN = Math.max(0, paginaN - 1); pintarNodos(); });
    $('pagSigN').addEventListener('click', function () { paginaN++; pintarNodos(); });
    function masivo(estado) {
        var fs = visibles().filter(function (f) { return estadoDec(f) === 'pendiente' && f.pr != null; });
        if (!fs.length) { alert('No hay propuestas pendientes en esta lista.'); return; }
        if (confirm((estado === 'aprobada' ? 'Aprobar' : 'Rechazar') + ' ' + fs.length + ' propuestas pendientes de esta lista?')) decidir(fs, estado);
    }
    $('apTodas').addEventListener('click', function () { masivo('aprobada'); });
    $('reTodas').addEventListener('click', function () { masivo('rechazada'); });
    $('btnTabla').addEventListener('click', function () { $('mTabla').classList.add('abierto'); });
    document.addEventListener('click', function (e) {
        if (e.target.matches('[data-cerrar]') || e.target.classList.contains('pv-modal')) { document.querySelectorAll('.pv-modal').forEach(function (m) { m.classList.remove('abierto'); }); }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') document.querySelectorAll('.pv-modal').forEach(function (m) { m.classList.remove('abierto'); }); });
    window.addEventListener('resize', function () { pintarNodos(); pintarLista(); });
    // Años históricos elegidos: el texto del botón se actualiza al marcar y el menú se cierra al hacer clic fuera
    var boxes = document.querySelectorAll('details.hist input[type=checkbox]');
    function textoHist() {
        var a = []; boxes.forEach(function (c) { if (c.checked) a.push(+c.parentNode.querySelector('b').textContent); });
        a = a.filter(function (v, i) { return a.indexOf(v) === i; }).sort();
        $('histTxt').textContent = a.length ? (a.length > 1 ? a[0] + '-' + a[a.length - 1] + ' (' + a.length + ')' : a[0]) : 'Elegir';
    }
    boxes.forEach(function (c) { c.addEventListener('change', function () {
        if (Array.prototype.filter.call(boxes, function (x) { return x.checked; }).length > 8) c.checked = false;
        textoHist();
    }); });
    document.addEventListener('click', function (e) { var d = document.querySelector('details.hist'); if (d && !d.contains(e.target)) d.open = false; });
    pintar();
})();
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
