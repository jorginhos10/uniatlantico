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

// Datos para la pantalla (el navegador arma el árbol línea → motor → proyecto y la tabla del proyecto elegido)
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
             'u' => $f['unidad'], 'c' => $f['codigo'], 'pr' => $f['propuesta'], 'um' => $ultMeta, 'd' => $f['descripcion'], 'h' => $hist];
}
$jsonDatos = json_encode(['filas' => $js, 'anios' => $anios, 'obj' => $obj, 'dec' => (object)$decisiones, 'url' => $basePath . '/proyeccion/ver?id=' . (int)$abierto['id']],
                         JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);

ob_start();
?>
<style>
    .pv { height: calc(100vh - 120px); min-height: 520px; display: flex; flex-direction: column; gap: .8rem; color: #1d1d1f; overflow: hidden; }
    .pv * { box-sizing: border-box; }
    .pv .top { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; }
    .pv a.volver { color: #0071e3; text-decoration: none; font-size: .8rem; }
    .pv h1 { font-size: 1.45rem; font-weight: 800; margin: .1rem 0 .35rem; letter-spacing: -.02em; }
    .pv .resumen { display: flex; gap: .5rem 1.1rem; flex-wrap: wrap; align-items: center; font-size: .82rem; color: #424245; }
    .pv .tag { display: inline-flex; align-items: center; gap: .35rem; background: #f0f0f3; border-radius: 999px; padding: .2rem .7rem; font-size: .8rem; }
    .pv .tag b { color: #1d1d1f; }
    .pv .tag.verde { background: #e3f8e9; color: #1b5e20; } .pv .tag.verde b { color: #1b5e20; }
    .pv .acc-top { display: flex; gap: .4rem; align-items: center; flex-wrap: wrap; }
    .pv .btn { border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .4rem .8rem; cursor: pointer; font-size: .8rem; color: inherit; text-decoration: none; display: inline-block; }
    .pv .btn:hover { border-color: #0071e3; color: #0071e3; }
    .pv .btn.primario { background: #0071e3; border-color: #0071e3; color: #fff; font-weight: 600; }
    /* Configuración (cerrada por defecto para que la vista quede limpia) */
    .pv details.conf { position: relative; }
    .pv details.conf > summary { list-style: none; cursor: pointer; }
    .pv details.conf > summary::-webkit-details-marker { display: none; }
    .pv .conf-box { position: absolute; right: 0; top: 100%; margin-top: 6px; z-index: 30; width: min(520px, 92vw); background: #fff; border: 1px solid #d2d2d7; border-radius: 12px; box-shadow: 0 12px 36px rgba(0,0,0,.18); padding: 1rem 1.1rem; }
    .pv .conf-box h4 { margin: 0 0 .4rem; font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: #6e6e73; }
    .pv .conf-box .campo { padding: .45rem .6rem; border: 1px solid #d2d2d7; border-radius: 8px; font-size: .9rem; background: #fff; }
    .pv .conf-box .fila { display: flex; gap: .6rem; flex-wrap: wrap; margin-bottom: .9rem; }
    .pv .conf-box .chips { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .9rem; }
    .pv .conf-box .chip input { position: absolute; opacity: 0; pointer-events: none; }
    .pv .conf-box .chip span { display: block; padding: .3rem .7rem; border: 1px solid #d2d2d7; border-radius: 999px; cursor: pointer; font-size: .8rem; user-select: none; }
    .pv .conf-box .chip input:checked + span { background: #0071e3; border-color: #0071e3; color: #fff; }
    .pv .conf-box small { color: #86868b; display: block; margin: -.5rem 0 .9rem; font-size: .72rem; }
    /* Cuerpo: árbol a la izquierda, tabla del proyecto a la derecha */
    .pv .cuerpo { flex: 1; min-height: 0; display: grid; grid-template-columns: 340px minmax(0, 1fr); gap: .8rem; }
    @media (max-width: 1000px) { .pv .cuerpo { grid-template-columns: 1fr; } .pv .izq { max-height: 220px; } }
    .pv .panel { background: #fff; border: 1px solid #e0e0e5; border-radius: 12px; min-height: 0; display: flex; flex-direction: column; overflow: hidden; }
    .pv .panel > h2 { font-size: .74rem; margin: 0; padding: .75rem 1rem .5rem; text-transform: uppercase; letter-spacing: .05em; color: #6e6e73; }
    .pv .arbol { flex: 1; min-height: 0; overflow: auto; padding: 0 .5rem .7rem; }
    .pv .tn { display: grid; grid-template-columns: 16px minmax(0, 1fr) auto; align-items: center; gap: .3rem; padding: .42rem .5rem; border-radius: 8px; cursor: pointer; font-size: .8rem; }
    .pv .tn:hover { background: #f5f5f7; }
    .pv .tn.sel { background: #e8f2ff; }
    .pv .tn .car { color: #86868b; font-size: .7rem; transition: transform .12s; display: inline-block; }
    .pv .tn.abierto .car { transform: rotate(90deg); }
    .pv .tn .car.sin { visibility: hidden; }
    .pv .tn .nm { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pv .tn .nm small { color: #86868b; margin-left: .3rem; font-size: .68rem; }
    .pv .tn .pc { font-weight: 700; font-size: .78rem; font-variant-numeric: tabular-nums; }
    .pv .tn .brr { grid-column: 2 / 4; height: 4px; background: #ececf0; border-radius: 2px; overflow: hidden; margin-top: -.05rem; }
    .pv .tn .brr i { display: block; height: 100%; }
    .pv .n1 { font-weight: 700; margin-top: .25rem; } .pv .n2 { margin-left: 1rem; font-weight: 600; } .pv .n3 { margin-left: 2rem; font-weight: 500; }
    .pv .der { display: flex; flex-direction: column; min-width: 0; }
    .pv .cab-tabla { padding: .8rem 1rem .6rem; border-bottom: 1px solid #ececf0; display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; align-items: center; }
    .pv .cab-tabla .ruta { font-size: .72rem; color: #86868b; }
    .pv .cab-tabla h3 { margin: .1rem 0 0; font-size: 1.05rem; }
    .pv .cab-tabla .nums { display: flex; gap: 1.2rem; font-size: .75rem; color: #6e6e73; align-items: center; }
    .pv .cab-tabla .nums b { display: block; font-size: 1.1rem; color: #1d1d1f; }
    .pv .marco { flex: 1; min-height: 0; overflow: auto; }
    .pv table { border-collapse: separate; border-spacing: 0; font-size: .78rem; width: max-content; min-width: 100%; }
    .pv thead th { position: sticky; z-index: 3; background: #1f3864; color: #fff; padding: .4rem .6rem; text-align: center; border-left: 1px solid rgba(255,255,255,.25); white-space: nowrap; }
    .pv thead tr:first-child th { top: 0; font-size: .85rem; }
    .pv thead tr:nth-child(2) th { top: 30px; font-weight: 600; font-size: .7rem; background: #2c4a85; }
    .pv thead th.prop { background: #1b5e20; } .pv thead tr:nth-child(2) th.prop { background: #2e7d32; }
    .pv th.ind-col, .pv td.ind-col { position: sticky; left: 0; z-index: 4; text-align: left; min-width: 280px; max-width: 320px; }
    .pv td.ind-col { background: #fff; z-index: 2; border-right: 1px solid #e0e0e5; }
    .pv thead th.ind-col { z-index: 5; }
    .pv tbody td { padding: .45rem .6rem; border-bottom: 1px solid #f0f0f3; text-align: center; vertical-align: middle; }
    .pv tbody tr:hover td { background: #fafcff; } .pv tbody tr:hover td.ind-col { background: #fafcff; }
    .pv td.ind-col .nom { font-weight: 600; line-height: 1.25; }
    .pv td.ind-col small { color: #86868b; display: block; font-size: .68rem; margin-top: 2px; }
    .pv td.gr { background: #fafafc; font-weight: 700; text-align: left; color: #424245; font-size: .75rem; }
    .pv .cu b { display: block; font-size: .85rem; font-variant-numeric: tabular-nums; }
    .pv .cu span { font-size: .66rem; font-weight: 700; padding: 0 .35rem; border-radius: 999px; }
    .pv .cu .v-ok { background: #e3f8e9; color: #1f6f3b; } .pv .cu .v-md { background: #fff1c9; color: #7a5b00; } .pv .cu .v-no { background: #ffe0dd; color: #b3001b; }
    .pv .nd { color: #c0c0c6; }
    .pv td.prop-c { background: #f4fbf5; }
    .pv td.prop-c input { width: 86px; padding: .3rem .4rem; border: 1px solid #b9e0c3; border-radius: 6px; font-weight: 700; color: #1b5e20; text-align: center; font-size: .85rem; background: #fff; }
    .pv td.prop-c .fin { font-weight: 800; font-size: .95rem; color: #1b5e20; }
    .pv td.prop-c .fin.tach { color: #b3001b; text-decoration: line-through; font-size: .8rem; }
    .pv td.dec-c { background: #f4fbf5; white-space: nowrap; }
    .pv .bd { border: 1px solid #d2d2d7; background: #fff; border-radius: 7px; padding: .25rem .6rem; font-size: .74rem; font-weight: 700; cursor: pointer; }
    .pv .bd.si { color: #1f8a3d; border-color: #b9e0c3; } .pv .bd.si:hover { background: #e3f8e9; }
    .pv .bd.no { color: #d70015; border-color: #f2c4bf; } .pv .bd.no:hover { background: #ffe5e3; }
    .pv .bd.un { color: #6e6e73; font-weight: 600; }
    .pv .est-ap { color: #1f8a3d; font-weight: 700; } .pv .est-re { color: #d70015; font-weight: 700; }
    .pv .vacio { color: #86868b; text-align: center; padding: 3rem 1rem; }
    @media print { .pv .acc-top, .pv a.volver, .pv .bd { display: none; } .pv { height: auto; overflow: visible; } .pv .marco, .pv .arbol { overflow: visible; } }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<section class="pv">
    <div class="top">
        <div>
            <a class="volver" href="<?php echo $basePath; ?>/proyeccion">← Proyecciones</a>
            <h1><?php echo $h($abierto['nombre']); ?></h1>
            <div class="resumen">
                <span class="tag verde">Año a proyectar <b><?php echo (int)$obj; ?></b></span>
                <span class="tag">Años históricos <b><?php echo $anios ? $h(implode(' · ', $anios)) : '—'; ?></b></span>
                <span class="tag"><?php echo $h(ProyeccionModel::METODOS[$metodo]); ?><?php echo $ajuste != 0 ? ' · ajuste ' . ($ajuste > 0 ? '+' : '') . $h($ajuste) . ' %' : ''; ?></span>
                <span id="contDec" style="color:#6e6e73"></span>
            </div>
        </div>
        <div class="acc-top">
            <details class="conf">
                <summary class="btn">⚙ Configurar</summary>
                <form class="conf-box" method="get" action="<?php echo $basePath; ?>/proyeccion/ver">
                    <input type="hidden" name="id" value="<?php echo (int)$abierto['id']; ?>">
                    <h4>1 · Año a proyectar</h4>
                    <div class="fila"><input class="campo" type="number" name="y" min="2000" max="2100" value="<?php echo (int)$obj; ?>" style="width:110px"></div>
                    <h4>2 · Años históricos (base)</h4>
                    <div class="chips">
                        <?php foreach ($todosPlanes as $tp): ?>
                            <label class="chip" title="<?php echo $h($tp['titulo']); ?>"><input type="checkbox" name="h[]" value="<?php echo (int)$tp['id']; ?>" <?php echo in_array((int)$tp['id'], $idsBase, true) ? 'checked' : ''; ?>>
                                <span><b><?php echo (int)$tp['anio']; ?></b> <?php echo $h(mb_strimwidth($tp['titulo'], 0, 20, '…', 'UTF-8')); ?></span></label>
                        <?php endforeach; ?>
                    </div>
                    <small>Solo se usan años anteriores al que proyectas.</small>
                    <h4>3 · Cómo se calcula la propuesta</h4>
                    <div class="fila">
                        <select class="campo" name="m"><?php foreach (ProyeccionModel::METODOS as $k => $v): ?><option value="<?php echo $k; ?>" <?php echo $k === $metodo ? 'selected' : ''; ?>><?php echo $h($v); ?></option><?php endforeach; ?></select>
                        <input class="campo" type="number" step="0.5" name="a" value="<?php echo $h($ajuste); ?>" style="width:90px" title="Ajuste en %">
                    </div>
                    <button class="btn primario" type="submit">Recalcular</button>
                    <button class="btn" type="submit" formmethod="post" name="accion" value="actualizar" title="Guarda estos valores en la proyección">Guardar</button>
                </form>
            </details>
            <a class="btn" href="<?php echo $basePath; ?>/proyeccion/ver?id=<?php echo (int)$abierto['id']; ?>&export=1<?php foreach ($idsBase as $hid) echo '&h[]=' . (int)$hid; ?>&y=<?php echo (int)$obj; ?>&m=<?php echo $h($metodo); ?>&a=<?php echo $h($ajuste); ?>">Excel</a>
            <button class="btn" onclick="window.print()">Imprimir</button>
        </div>
    </div>

    <div class="cuerpo">
        <div class="panel izq">
            <h2>Línea › Motor › Proyecto</h2>
            <div class="arbol" id="arbol"></div>
        </div>
        <div class="panel der" id="derecha"></div>
    </div>
</section>

<script>
(function () {
    var D = <?php echo $jsonDatos; ?>;
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    var fmt = function (n, d) { return Number(n).toLocaleString('es-CO', { maximumFractionDigits: d == null ? 1 : d }); };
    var col = function (p) { return p == null ? '#aeaeb2' : (p >= 100 ? '#1f8a3d' : (p >= 60 ? '#c58a00' : '#d70015')); };
    var cls = function (p) { return p >= 100 ? 'v-ok' : (p >= 60 ? 'v-md' : 'v-no'); };
    var unidad = function (u) { return /porcentaje/i.test(u || '') ? '%' : ''; };
    var estDe = function (f) { var d = D.dec[f.k]; return d ? d.e : 'pendiente'; };
    var ULT = function (f) { var u = null; f.h.forEach(function (x) { if (x.p != null) u = x.p; }); return u; };
    var sel = [];               // selección: [línea], [línea, motor] o [línea, motor, proyecto]
    var abiertos = {};          // nodos del árbol desplegados
    var ediciones = {};         // meta propuesta editada por el usuario (aún sin decidir)

    function cumplido(fs) {
        var v = [];
        fs.forEach(function (f) { var u = ULT(f); if (u != null) v.push(Math.min(u, 100)); });
        return v.length ? v.reduce(function (s, x) { return s + x; }, 0) / v.length : null;
    }
    function enSel(f) { return (!sel[0] || f.l === sel[0]) && (!sel[1] || f.m === sel[1]) && (!sel[2] || f.p === sel[2]); }
    function nombreCorto(n) { var m = n.match(/^([A-Z]\d+)\s*-\s*(.*)$/); return m ? { c: m[1], t: m[2] } : { c: '', t: n }; }

    // ── Árbol ──
    function arbol() {
        var L = {}, ordL = [];
        D.filas.forEach(function (f) {
            if (!L[f.l]) { L[f.l] = {}; ordL.push(f.l); }
            if (!L[f.l][f.m]) L[f.l][f.m] = {};
            if (!L[f.l][f.m][f.p]) L[f.l][f.m][f.p] = [];
            L[f.l][f.m][f.p].push(f);
        });
        var cmp = function (a, b) { return a.localeCompare(b, 'es', { numeric: true }); };
        function nodo(nivel, ruta, nombre, fs, hijos) {
            var id = ruta.join('||'), n = nombreCorto(nombre), c = cumplido(fs), dec = fs.filter(function (f) { return estDe(f) !== 'pendiente'; }).length;
            var esSel = sel.length === ruta.length && sel.every(function (x, i) { return x === ruta[i]; });
            var h = '<div class="tn n' + nivel + (esSel ? ' sel' : '') + (abiertos[id] ? ' abierto' : '') + '" data-r=\'' + esc(JSON.stringify(ruta)) + '\' title="' + esc(nombre) + '">' +
                '<span class="car ' + (hijos ? '' : 'sin') + '">▶</span><span class="nm">' + esc(n.t) + '<small>' + (n.c ? n.c + ' · ' : '') + fs.length + ' ind. · ' + dec + ' decididas</small></span>' +
                '<span class="pc" style="color:' + col(c) + '">' + (c == null ? '—' : fmt(c, 0) + '%') + '</span>' +
                '<div class="brr"><i style="width:' + (c || 0) + '%;background:' + col(c) + '"></i></div></div>';
            return h + (hijos && abiertos[id] ? hijos : '');
        }
        var out = '';
        ordL.sort(cmp).forEach(function (l) {
            var fL = D.filas.filter(function (f) { return f.l === l; });
            var hm = '';
            Object.keys(L[l]).sort(cmp).forEach(function (m) {
                var fM = fL.filter(function (f) { return f.m === m; }), hp = '';
                Object.keys(L[l][m]).sort(cmp).forEach(function (p) { hp += nodo(3, [l, m, p], p, L[l][m][p], null); });
                hm += nodo(2, [l, m], m, fM, hp);
            });
            out += nodo(1, [l], l, fL, hm);
        });
        $('arbol').innerHTML = out;
        document.querySelectorAll('#arbol .tn').forEach(function (el) {
            el.addEventListener('click', function () {
                var r = JSON.parse(el.dataset.r), id = r.join('||'), igual = sel.length === r.length && sel.every(function (x, i) { return x === r[i]; });
                if (r.length < 3) abiertos[id] = igual ? !abiertos[id] : true;
                sel = r; pintar();
            });
        });
    }

    // ── Tabla del elemento elegido ──
    function tabla() {
        var d = $('derecha');
        if (!sel.length) { d.innerHTML = '<div class="vacio">Elige una línea, un motor o un proyecto de la izquierda para ver sus indicadores.</div>'; return; }
        var fs = D.filas.filter(enSel).sort(function (a, b) { return a.p.localeCompare(b.p, 'es', { numeric: true }) || a.n.localeCompare(b.n, 'es'); });
        var c = cumplido(fs), ap = fs.filter(function (f) { return estDe(f) === 'aprobada'; }).length, re = fs.filter(function (f) { return estDe(f) === 'rechazada'; }).length;
        var pend = fs.filter(function (f) { return estDe(f) === 'pendiente' && f.pr != null; });
        var ruta = sel.map(function (x) { var n = nombreCorto(x); return n.c || n.t; }).join(' › ');
        var tit = nombreCorto(sel[sel.length - 1]).t;

        var h = '<div class="cab-tabla"><div><div class="ruta">' + esc(ruta) + '</div><h3>' + esc(tit) + '</h3></div><div class="nums">' +
            '<div><b>' + fs.length + '</b>indicadores</div><div><b style="color:' + col(c) + '">' + (c == null ? '—' : fmt(c, 0) + ' %') + '</b>de la meta cumplida</div>' +
            '<div><b>' + (ap + re) + ' / ' + fs.length + '</b>decididas</div>' +
            (pend.length ? '<div><button class="bd si" id="apTodas">✓ Aprobar las ' + pend.length + ' pendientes</button></div>' : '') + '</div></div>';

        h += '<div class="marco"><table><thead><tr><th class="ind-col" rowspan="2">Indicador</th>';
        D.anios.forEach(function (a) { h += '<th colspan="2">' + a + '</th>'; });
        h += '<th colspan="2" class="prop">Proyección ' + D.obj + '</th></tr><tr>';
        D.anios.forEach(function () { h += '<th>Meta</th><th>Cumplido</th>'; });
        h += '<th class="prop">Meta propuesta</th><th class="prop">Decisión</th></tr></thead><tbody>';

        var ultimoP = null, ncols = D.anios.length * 2 + 3;
        fs.forEach(function (f) {
            if (sel.length < 3 && f.p !== ultimoP) { h += '<tr><td class="gr" colspan="' + ncols + '">' + esc(f.p) + '</td></tr>'; ultimoP = f.p; }
            var e = estDe(f), dc = D.dec[f.k];
            h += '<tr><td class="ind-col"><div class="nom">' + esc(f.n) + '</div><small>' + esc(f.r) + (f.c ? ' · ' + esc(f.c) : '') + ' · ' + esc(f.u) + '</small></td>';
            D.anios.forEach(function (a) {
                var x = f.h.filter(function (y) { return y.a === a; })[0];
                if (!x || x.mf == null) { h += '<td class="nd">—</td><td class="nd">—</td>'; return; }
                h += '<td>' + esc(x.mf) + '</td><td class="cu">' + (x.lf == null ? '<span class="nd">sin dato</span>' : '<b>' + esc(x.lf) + '</b>' + (x.p == null ? '' : '<span class="' + cls(x.p) + '">' + fmt(x.p, 0) + '%</span>')) + '</td>';
            });
            var val = ediciones[f.k] != null ? ediciones[f.k] : f.pr;
            if (e === 'aprobada') h += '<td class="prop-c"><span class="fin">' + fmt(dc.v != null ? dc.v : f.pr, 2) + unidad(f.u) + '</span></td><td class="dec-c"><span class="est-ap">✓ Aprobada</span> <button class="bd un" data-a="un" data-k="' + esc(f.k) + '">Deshacer</button></td>';
            else if (e === 'rechazada') h += '<td class="prop-c"><span class="fin tach">' + (f.pr == null ? '—' : fmt(f.pr, 2) + unidad(f.u)) + '</span></td><td class="dec-c"><span class="est-re">✕ Rechazada</span> <button class="bd un" data-a="un" data-k="' + esc(f.k) + '">Deshacer</button></td>';
            else if (f.pr == null) h += '<td class="prop-c nd">sin histórico</td><td class="dec-c nd">—</td>';
            else h += '<td class="prop-c"><input type="number" step="any" value="' + esc(val) + '" data-k="' + esc(f.k) + '" title="' + esc(f.d) + '">' + (unidad(f.u) ? ' %' : '') + '</td>' +
                '<td class="dec-c"><button class="bd si" data-a="si" data-k="' + esc(f.k) + '">✓ Aprobar</button> <button class="bd no" data-a="no" data-k="' + esc(f.k) + '">✕ Rechazar</button></td>';
            h += '</tr>';
        });
        d.innerHTML = h + '</tbody></table></div>';

        d.querySelectorAll('input[data-k]').forEach(function (i) { i.addEventListener('input', function () { ediciones[i.dataset.k] = i.value === '' ? null : parseFloat(i.value); }); });
        d.querySelectorAll('button[data-a]').forEach(function (b) {
            b.addEventListener('click', function () {
                var f = D.filas.filter(function (x) { return x.k === b.dataset.k; })[0], v = {};
                if (b.dataset.a === 'si') {
                    var n = ediciones[f.k] != null ? ediciones[f.k] : f.pr;
                    if (n == null || isNaN(n)) { alert('Escribe la meta que vas a aprobar.'); return; }
                    v[f.k] = n; decidir([f], 'aprobada', v);
                } else decidir([f], b.dataset.a === 'no' ? 'rechazada' : '');
            });
        });
        if ($('apTodas')) $('apTodas').addEventListener('click', function () {
            if (confirm('¿Aprobar con la meta propuesta los ' + pend.length + ' indicadores pendientes de esta selección?')) {
                var v = {}; pend.forEach(function (f) { v[f.k] = ediciones[f.k] != null ? ediciones[f.k] : f.pr; }); decidir(pend, 'aprobada', v);
            }
        });
    }

    // Guarda la decisión en el servidor y refresca la pantalla
    function decidir(fs, estado, valores) {
        var items = fs.map(function (f) { return { k: f.k, v: valores && valores[f.k] != null ? valores[f.k] : f.pr }; });
        var fd = new FormData(); fd.append('accion', 'decidir'); fd.append('estado', estado); fd.append('items', JSON.stringify(items));
        fetch(D.url, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (r) {
            if (!r.ok) throw new Error();
            items.forEach(function (it) { if (estado === '') delete D.dec[it.k]; else { D.dec[it.k] = { e: estado, v: estado === 'aprobada' ? it.v : null }; delete ediciones[it.k]; } });
            pintar();
        }).catch(function () { alert('No se pudo guardar la decisión. Intenta de nuevo.'); });
    }

    function pintar() {
        var ap = 0, re = 0; D.filas.forEach(function (f) { var e = estDe(f); if (e === 'aprobada') ap++; else if (e === 'rechazada') re++; });
        $('contDec').textContent = D.filas.length + ' indicadores · ' + ap + ' aprobadas · ' + re + ' rechazadas · ' + (D.filas.length - ap - re) + ' pendientes';
        arbol(); tabla();
    }

    // Abre de entrada el primer proyecto para que la pantalla no quede vacía
    (function () {
        if (!D.filas.length) return;
        var f = D.filas.slice().sort(function (a, b) { return a.l.localeCompare(b.l, 'es', { numeric: true }) || a.m.localeCompare(b.m, 'es', { numeric: true }) || a.p.localeCompare(b.p, 'es', { numeric: true }); })[0];
        abiertos[f.l] = true; abiertos[f.l + '||' + f.m] = true; sel = [f.l, f.m, f.p];
    })();
    document.addEventListener('click', function (e) { var d = document.querySelector('details.conf'); if (d && !d.contains(e.target)) d.open = false; });
    pintar();
})();
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
