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
$abierto = $pry->obtener((int)($_GET['id'] ?? 0));
if (!$abierto) { header('Location: ' . $basePath . '/proyeccion'); exit; }

// Guardar el método y el ajuste elegidos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'actualizar') {
    $pry->actualizar($abierto['id'], (string)($_POST['metodo'] ?? 'prom'), (float)str_replace(',', '.', (string)($_POST['ajuste'] ?? 0)));
    header('Location: ' . $basePath . '/proyeccion/ver?id=' . (int)$abierto['id']);
    exit;
}

// Método y ajuste: lo guardado, o lo que se está probando por la URL (sin guardar)
$metodo = isset($_GET['m'], ProyeccionModel::METODOS[$_GET['m']]) ? $_GET['m'] : $abierto['metodo'];
$ajuste = isset($_GET['a']) ? (float)str_replace(',', '.', (string)$_GET['a']) : (float)$abierto['ajuste'];

$forde = new FORDE144Model();
$planes = [];
foreach ($abierto['ids'] as $fid) { $f = $forde->getById($fid); if ($f) $planes[] = $f; }
usort($planes, function ($a, $b) { return ((int)$a['anio'] <=> (int)$b['anio']) ?: ((int)$a['id'] <=> (int)$b['id']); });
$datos = $pry->construir($planes, (int)$abierto['anio_objetivo'], $metodo, $ajuste);
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
                 'Metas ' . $rango, 'Cumplimiento ' . $rango, 'Estado', 'Meta propuesta ' . $obj, 'Descripción ' . $obj, 'ID indicador'], ';');
    foreach ($datos['filas'] as $f) {
        fputcsv($o, [$f['linea'], $f['motor'], $f['proyecto'], $f['responsable'], $f['nombre'], $f['formula'], $f['unidad'], $f['tipo'],
                     implode("\n", $metasTxt($f)), implode("\n", $cumplTxt($f)), $f['estado'],
                     $f['propuesta'] === null ? '' : $num($f['propuesta'], $f['unidad']), $f['descripcion'], $f['codigo']], ';');
    }
    fclose($o);
    exit;
}

$cuenta = ['Cumplido' => 0, 'Parcial' => 0, 'No cumplido' => 0, 'Sin dato' => 0];
foreach ($datos['filas'] as $f) $cuenta[$f['estado']]++;

// Datos para el tablero (el navegador dibuja los gráficos y filtra sin recargar)
$js = [];
foreach ($datos['filas'] as $i => $f) {
    $hist = [];
    foreach ($f['hist'] as $hh) {
        $p = null;
        if ($hh['meta'] !== null && $hh['logrado'] !== null) {
            $p = $f['tipo'] === 'Límite' ? ($hh['logrado'] <= $hh['meta'] ? 100 : 0) : ($hh['meta'] > 0 ? $hh['logrado'] / $hh['meta'] * 100 : null);
        }
        $hist[] = ['a' => $hh['anio'], 'm' => $hh['meta'], 'lg' => $hh['logrado'], 'p' => $p === null ? null : round($p, 1),
                   'mf' => $hh['meta'] === null ? null : $num($hh['meta'], $f['unidad']), 'lf' => $hh['logrado'] === null ? null : $num($hh['logrado'], $f['unidad'])];
    }
    $ultMeta = null;
    foreach ($f['hist'] as $hh) if ($hh['meta'] !== null) $ultMeta = $hh['meta'];
    $js[] = ['i' => $i, 'n' => $f['nombre'], 'l' => $f['linea'], 'm' => $f['motor'], 'p' => $f['proyecto'], 'r' => $f['responsable'],
             'u' => $f['unidad'], 't' => $f['tipo'], 'f' => $f['formula'], 'c' => $f['codigo'], 'e' => $f['estado'],
             'pr' => $f['propuesta'], 'pf' => $f['propuesta'] === null ? null : $num($f['propuesta'], $f['unidad']), 'um' => $ultMeta,
             'd' => $f['descripcion'], 'h' => $hist];
}
$jsonDatos = json_encode(['filas' => $js, 'anios' => $anios, 'obj' => $obj], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);

ob_start();
?>
<style>
    /* Cabe en la pantalla sin desplazamiento: encabezado 64 px + 28 px de margen arriba y abajo */
    .pv { height: calc(100vh - 120px); min-height: 520px; display: flex; flex-direction: column; gap: .7rem; color: #1d1d1f; overflow: hidden; }
    .pv * { box-sizing: border-box; }
    .pv .barra { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .pv a.volver { color: #0071e3; text-decoration: none; font-size: .8rem; }
    .pv h1 { font-size: 1.35rem; font-weight: 800; margin: 0; letter-spacing: -.02em; line-height: 1.15; }
    .pv .sub { color: #86868b; font-size: .78rem; margin: 0; }
    .pv .ctl { display: flex; gap: .4rem; align-items: center; flex-wrap: wrap; }
    .pv .ctl select, .pv .ctl input { padding: .35rem .55rem; border: 1px solid #d2d2d7; border-radius: 8px; font-size: .8rem; background: #fff; }
    .pv .btn { border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .36rem .7rem; cursor: pointer; font-size: .8rem; color: inherit; text-decoration: none; display: inline-block; }
    .pv .btn:hover { border-color: #0071e3; color: #0071e3; }
    .pv .btn.primario { background: #0071e3; border-color: #0071e3; color: #fff; font-weight: 600; }
    .pv .kpis { display: grid; grid-template-columns: repeat(5, 1fr); gap: .6rem; }
    .pv .kpi { background: #fff; border: 1px solid #e0e0e5; border-radius: 12px; padding: .55rem .85rem; position: relative; overflow: hidden; }
    .pv .kpi::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--c, #0071e3); }
    .pv .kpi b { font-size: 1.55rem; line-height: 1.1; display: block; color: var(--c, #0071e3); font-variant-numeric: tabular-nums; }
    .pv .kpi span { font-size: .72rem; color: #6e6e73; }
    .pv .cuerpo { flex: 1; min-height: 0; display: grid; grid-template-columns: 250px minmax(0, 1fr) minmax(0, 1.25fr); gap: .7rem; }
    @media (max-width: 1100px) { .pv .cuerpo { grid-template-columns: 1fr 1fr; } .pv .col-dona { display: none; } }
    .pv .panel { background: #fff; border: 1px solid #e0e0e5; border-radius: 12px; padding: .7rem .85rem; display: flex; flex-direction: column; min-height: 0; }
    .pv .panel h2 { font-size: .78rem; margin: 0 0 .5rem; text-transform: uppercase; letter-spacing: .05em; color: #6e6e73; display: flex; justify-content: space-between; align-items: center; }
    .pv .panel h2 em { font-style: normal; text-transform: none; letter-spacing: 0; font-weight: 500; font-size: .72rem; }
    .pv .limpiar { color: #0071e3; cursor: pointer; font-size: .72rem; display: none; }
    /* Dona */
    .pv .dona { position: relative; width: 150px; height: 150px; margin: 0 auto .6rem; }
    .pv .dona svg { width: 100%; height: 100%; transform: rotate(-90deg); }
    .pv .dona circle { fill: none; stroke-width: 22; cursor: pointer; transition: stroke-width .15s, opacity .15s; }
    .pv .dona circle:hover { stroke-width: 26; }
    .pv .dona .centro { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none; }
    .pv .dona .centro b { font-size: 1.6rem; line-height: 1; }
    .pv .dona .centro span { font-size: .68rem; color: #6e6e73; }
    .pv .leyenda { display: flex; flex-direction: column; gap: .25rem; }
    .pv .leyenda button { display: flex; align-items: center; gap: .5rem; border: 1px solid transparent; background: none; padding: .25rem .4rem; border-radius: 8px; font-size: .78rem; cursor: pointer; text-align: left; }
    .pv .leyenda button:hover, .pv .leyenda button.act { background: #f5f5f7; border-color: #d2d2d7; }
    .pv .leyenda i { width: 10px; height: 10px; border-radius: 50%; flex: none; }
    .pv .leyenda strong { margin-left: auto; font-variant-numeric: tabular-nums; }
    .pv .nota { margin-top: auto; font-size: .68rem; color: #86868b; line-height: 1.35; }
    /* Líneas */
    .pv .lineas { flex: 1; min-height: 0; display: flex; flex-direction: column; gap: .4rem; }
    .pv .linea { flex: 1; min-height: 0; border: 1px solid #ececf0; border-radius: 10px; padding: .35rem .6rem; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: .5rem; align-items: center; cursor: pointer; transition: border-color .15s, background .15s; }
    .pv .linea:hover { border-color: #0071e3; background: #f7fbff; }
    .pv .linea.act { border-color: #0071e3; background: #eaf3ff; }
    .pv .linea .nom { font-size: .76rem; font-weight: 600; line-height: 1.2; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .pv .linea .nom small { display: block; font-weight: 500; color: #86868b; font-size: .68rem; }
    .pv .linea .prop { font-size: .68rem; margin-top: .15rem; font-weight: 600; }
    .pv .linea svg { height: 100%; max-height: 54px; width: 118px; }
    .pv .sube { color: #1b5e20; } .pv .baja { color: #d70015; } .pv .igual { color: #86868b; }
    /* Lista de indicadores */
    .pv .lista { flex: 1; min-height: 0; display: flex; flex-direction: column; gap: .35rem; overflow: hidden; }
    .pv .ind { flex: none; height: 58px; border: 1px solid #ececf0; border-radius: 10px; padding: .3rem .6rem; display: grid; grid-template-columns: 8px minmax(0, 1fr) 92px 78px; gap: .55rem; align-items: center; cursor: pointer; transition: border-color .15s, transform .1s; }
    .pv .ind:hover { border-color: #0071e3; transform: translateX(2px); }
    .pv .ind .pt { width: 8px; height: 34px; border-radius: 4px; }
    .pv .ind .nom { font-size: .76rem; font-weight: 600; line-height: 1.2; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .pv .ind .nom small { display: block; font-weight: 500; color: #86868b; font-size: .66rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pv .ind svg { width: 92px; height: 34px; }
    .pv .ind .mp { text-align: right; font-size: .66rem; color: #86868b; line-height: 1.2; }
    .pv .ind .mp b { display: block; font-size: .95rem; color: #1b5e20; }
    .pv .pager { display: flex; justify-content: space-between; align-items: center; padding-top: .45rem; font-size: .74rem; color: #6e6e73; }
    .pv .pager button { border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .25rem .65rem; cursor: pointer; }
    .pv .pager button[disabled] { opacity: .4; cursor: default; }
    .pv .vacio { color: #86868b; font-size: .85rem; text-align: center; padding: 1.5rem 0; }
    /* Ventanas */
    .pv-modal { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: none; align-items: center; justify-content: center; z-index: 3000; padding: 1.5rem; }
    .pv-modal.abierto { display: flex; }
    .pv-modal .caja { background: #fff; border-radius: 14px; width: min(760px, 100%); max-height: 88vh; overflow: auto; padding: 1.2rem 1.4rem; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
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
    .pv-modal .propuesta b { font-size: 1.7rem; color: #1b5e20; display: block; line-height: 1.1; }
    .pv-modal .propuesta span { font-size: .8rem; color: #424245; }
    .pv-modal dl { display: grid; grid-template-columns: 120px 1fr; gap: .25rem .8rem; font-size: .8rem; margin: 0; }
    .pv-modal dt { color: #86868b; } .pv-modal dd { margin: 0; }
    .pv-tabla { border-collapse: collapse; font-size: .72rem; width: max-content; min-width: 100%; }
    .pv-tabla th { background: #1f3864; color: #fff; padding: .4rem .5rem; border: 1px solid #fff; position: sticky; top: 0; }
    .pv-tabla th.prop { background: #1b5e20; }
    .pv-tabla td { border: 1px solid #d9d9de; padding: .3rem .5rem; text-align: center; }
    .pv-tabla td.t { text-align: left; }
    .pv-tabla td.apil div { white-space: nowrap; text-align: left; }
    .pv-tabla td.prop { font-weight: 800; color: #1b5e20; background: #f1faf2; }
    @media print { .pv .ctl, .pv a.volver { display: none; } .pv { height: auto; } }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<section class="pv">
    <div class="barra">
        <div>
            <a class="volver" href="<?php echo $basePath; ?>/proyeccion">← Proyecciones</a>
            <h1><?php echo $h($abierto['nombre']); ?></h1>
            <p class="sub">Base <?php echo $h($rango); ?> → metas propuestas para <?php echo $obj; ?> · <?php echo $h(ProyeccionModel::METODOS[$metodo]); ?><?php echo $ajuste != 0 ? ' · ajuste ' . ($ajuste > 0 ? '+' : '') . $h($ajuste) . ' %' : ''; ?></p>
        </div>
        <form class="ctl" method="get" action="<?php echo $basePath; ?>/proyeccion/ver">
            <input type="hidden" name="id" value="<?php echo (int)$abierto['id']; ?>">
            <select name="m" title="Cómo se calcula la meta"><?php foreach (ProyeccionModel::METODOS as $k => $v): ?><option value="<?php echo $k; ?>" <?php echo $k === $metodo ? 'selected' : ''; ?>><?php echo $h($v); ?></option><?php endforeach; ?></select>
            <input type="number" step="0.5" name="a" value="<?php echo $h($ajuste); ?>" style="width:72px" title="Ajuste en %">
            <button class="btn primario" type="submit">Recalcular</button>
            <button class="btn" type="submit" formmethod="post" name="accion" value="actualizar" title="Guarda este método y ajuste en la proyección">Guardar</button>
            <button class="btn" type="button" id="btnTabla">Tabla completa</button>
            <a class="btn" href="<?php echo $basePath; ?>/proyeccion/ver?id=<?php echo (int)$abierto['id']; ?>&export=1&m=<?php echo $h($metodo); ?>&a=<?php echo $h($ajuste); ?>">Excel</a>
        </form>
    </div>

    <div class="kpis" id="kpis"></div>

    <div class="cuerpo">
        <div class="panel col-dona">
            <h2>Estado último año <span class="limpiar" id="limpiarEstado">quitar filtro</span></h2>
            <div class="dona" id="dona"></div>
            <div class="leyenda" id="leyenda"></div>
            <div class="nota">Cumplido ≥ 100 %, parcial 60–99 %, no cumplido &lt; 60 % en el último año con meta y logrado aprobado.</div>
        </div>

        <div class="panel">
            <h2>Cumplimiento por línea <em>cumplimiento medio por año base · clic para filtrar</em></h2>
            <div class="lineas" id="lineas"></div>
        </div>

        <div class="panel">
            <h2><span id="tituloLista">Indicadores</span> <span class="limpiar" id="limpiarLinea">quitar filtro</span></h2>
            <div class="lista" id="lista"></div>
            <div class="pager"><span id="pagInfo"></span><span><button id="pagAnt">‹</button> <button id="pagSig">›</button></span></div>
        </div>
    </div>
</section>

<!-- Detalle de un indicador -->
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
                <th class="prop">Meta propuesta <?php echo $obj; ?></th><th class="prop">Descripción <?php echo $obj; ?></th><th>ID indicador</th>
            </tr></thead>
            <tbody>
            <?php foreach ($datos['filas'] as $f): ?>
                <tr>
                    <td class="t"><?php echo $h($f['linea']); ?></td><td class="t"><?php echo $h($f['motor']); ?></td><td class="t"><?php echo $h($f['proyecto']); ?></td>
                    <td class="t"><?php echo $h($f['responsable']); ?></td><td class="t"><?php echo $h($f['nombre']); ?></td><td class="t"><?php echo $h($f['formula']); ?></td>
                    <td><?php echo $h($f['unidad']); ?></td><td><?php echo $h($f['tipo']); ?></td>
                    <td class="apil"><?php foreach ($metasTxt($f) as $l): ?><div><?php echo $h($l); ?></div><?php endforeach; ?></td>
                    <td class="apil"><?php foreach ($cumplTxt($f) as $l): ?><div><?php echo $h($l); ?></div><?php endforeach; ?></td>
                    <td><?php echo $h($f['estado']); ?></td>
                    <td class="prop"><?php echo $f['propuesta'] === null ? '—' : $h($num($f['propuesta'], $f['unidad'])); ?></td>
                    <td class="t"><?php echo $h($f['descripcion']); ?></td><td><?php echo $h($f['codigo']); ?></td>
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
    var fEstado = '', fLinea = '', pagina = 0, porPagina = 6;
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    var fmt = function (n, d) { return Number(n).toLocaleString('es-CO', { maximumFractionDigits: d == null ? 1 : d }); };
    var colPct = function (p) { return p == null ? '#d9d9de' : (p >= 100 ? COL['Cumplido'] : (p >= 60 ? COL['Parcial'] : COL['No cumplido'])); };

    // Cumplimiento medio por año de un conjunto de indicadores
    function porAnio(filas) {
        return D.anios.map(function (a) {
            var v = [];
            filas.forEach(function (f) { f.h.forEach(function (x) { if (x.a === a && x.p != null) v.push(Math.min(x.p, 150)); }); });
            return v.length ? v.reduce(function (s, x) { return s + x; }, 0) / v.length : null;
        });
    }
    // Variación media entre la última meta y la meta propuesta
    function variacion(filas) {
        var v = [];
        filas.forEach(function (f) { if (f.pr != null && f.um > 0) v.push((f.pr / f.um - 1) * 100); });
        return v.length ? v.reduce(function (s, x) { return s + x; }, 0) / v.length : null;
    }
    // Barras por año (altura = cumplimiento, escala hasta 120 %)
    function barras(vals, ancho, alto, etiquetas) {
        var n = vals.length || 1, bw = Math.min(22, (ancho - (n - 1) * 4) / n), h = '<svg viewBox="0 0 ' + ancho + ' ' + alto + '" preserveAspectRatio="xMidYMax meet">';
        var util = alto - (etiquetas ? 22 : 4), x0 = (ancho - (n * bw + (n - 1) * 4)) / 2;
        h += '<line x1="0" x2="' + ancho + '" y1="' + (util - util * 100 / 120) + '" y2="' + (util - util * 100 / 120) + '" stroke="#d2d2d7" stroke-dasharray="2 2" stroke-width="1"/>';
        vals.forEach(function (v, i) {
            var x = x0 + i * (bw + 4);
            if (v == null) { h += '<rect x="' + x + '" y="' + (util - 2) + '" width="' + bw + '" height="2" fill="#e5e5ea"/>'; }
            else {
                var bh = Math.max(2, util * Math.min(v, 120) / 120);
                h += '<rect x="' + x + '" y="' + (util - bh) + '" width="' + bw + '" height="' + bh + '" rx="2" fill="' + colPct(v) + '"><title>' + D.anios[i] + ': ' + fmt(v) + '%</title></rect>';
                if (etiquetas) h += '<text x="' + (x + bw / 2) + '" y="' + (util - bh - 3) + '" font-size="9" text-anchor="middle" fill="#424245">' + Math.round(v) + '</text>';
            }
            if (etiquetas) h += '<text x="' + (x + bw / 2) + '" y="' + (alto - 6) + '" font-size="9" text-anchor="middle" fill="#86868b">' + String(D.anios[i]).slice(2) + '</text>';
        });
        return h + '</svg>';
    }
    function flecha(v) {
        if (v == null) return '<span class="igual">sin propuesta</span>';
        return v > 0.5 ? '<span class="sube">▲ +' + fmt(v) + ' % la meta</span>' : (v < -0.5 ? '<span class="baja">▼ ' + fmt(v) + ' % la meta</span>' : '<span class="igual">= misma meta</span>');
    }

    function filtradas() {
        return D.filas.filter(function (f) { return (!fEstado || f.e === fEstado) && (!fLinea || f.l === fLinea); });
    }

    function pintarKpis() {
        var t = D.filas.length, c = {}; ORDEN.forEach(function (e) { c[e] = 0; });
        D.filas.forEach(function (f) { c[f.e]++; });
        var med = porAnio(D.filas), ult = null; med.forEach(function (v) { if (v != null) ult = v; });
        var vr = variacion(D.filas);
        var k = [
            [t, 'indicadores en la base', '#0071e3'],
            [ult == null ? '—' : fmt(ult, 0) + ' %', 'cumplimiento medio del último año', colPct(ult)],
            [fmt(c['Cumplido'] ? c['Cumplido'] * 100 / t : 0, 0) + ' %', c['Cumplido'] + ' indicadores cumplidos', COL['Cumplido']],
            [c['No cumplido'], 'no cumplidos · requieren atención', COL['No cumplido']],
            [vr == null ? '—' : (vr > 0 ? '+' : '') + fmt(vr) + ' %', 'variación media de la meta ' + D.obj, '#1b5e20']
        ];
        $('kpis').innerHTML = k.map(function (x) { return '<div class="kpi" style="--c:' + x[2] + '"><b>' + x[0] + '</b><span>' + x[1] + '</span></div>'; }).join('');
    }

    function pintarDona() {
        var base = D.filas.filter(function (f) { return !fLinea || f.l === fLinea; });
        var c = {}; ORDEN.forEach(function (e) { c[e] = 0; });
        base.forEach(function (f) { c[f.e]++; });
        var tot = base.length || 1, R = 54, L = 2 * Math.PI * R, off = 0, s = '<svg viewBox="0 0 150 150">';
        ORDEN.forEach(function (e) {
            if (!c[e]) return;
            var len = L * c[e] / tot;
            s += '<circle data-e="' + e + '" cx="75" cy="75" r="' + R + '" stroke="' + COL[e] + '" stroke-dasharray="' + len + ' ' + (L - len) + '" stroke-dashoffset="' + (-off) + '" opacity="' + (!fEstado || fEstado === e ? 1 : .25) + '"><title>' + e + ': ' + c[e] + '</title></circle>';
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
    }

    function pintarLineas() {
        var por = {}, orden = [];
        D.filas.forEach(function (f) { if (!por[f.l]) { por[f.l] = []; orden.push(f.l); } por[f.l].push(f); });
        $('lineas').innerHTML = orden.map(function (l) {
            var fs = por[l].filter(function (f) { return !fEstado || f.e === fEstado; });
            var m = l.match(/^(L\d+)\s*-\s*(.*)$/);
            return '<div class="linea ' + (fLinea === l ? 'act' : '') + '" data-l="' + esc(l) + '"><div><div class="nom">' + esc(m ? m[2] : l) + '<small>' + (m ? m[1] + ' · ' : '') + fs.length + ' indicadores</small></div>' +
                '<div class="prop">' + flecha(variacion(fs)) + '</div></div>' + barras(porAnio(fs), 118, 56, true) + '</div>';
        }).join('');
        document.querySelectorAll('#lineas .linea').forEach(function (el) {
            el.addEventListener('click', function () { fLinea = fLinea === el.dataset.l ? '' : el.dataset.l; pagina = 0; pintar(); });
        });
        $('limpiarLinea').style.display = fLinea ? 'inline' : 'none';
    }

    function pintarLista() {
        var fs = filtradas().slice().sort(function (a, b) { return ORDEN.indexOf(a.e) - ORDEN.indexOf(b.e); });
        // Cuántos caben sin desplazamiento: alto disponible / alto de cada fila (58 px + separación)
        var alto = $('lista').clientHeight || 300;
        porPagina = Math.max(3, Math.floor((alto + 6) / 64));
        var paginas = Math.max(1, Math.ceil(fs.length / porPagina));
        if (pagina >= paginas) pagina = paginas - 1;
        var vis = fs.slice(pagina * porPagina, (pagina + 1) * porPagina);
        $('tituloLista').textContent = (fEstado || fLinea) ? 'Indicadores filtrados' : 'Indicadores (primero los que requieren atención)';
        $('lista').innerHTML = vis.length ? vis.map(function (f) {
            return '<div class="ind" data-i="' + f.i + '"><span class="pt" style="background:' + COL[f.e] + '" title="' + f.e + '"></span>' +
                '<div class="nom">' + esc(f.n) + '<small>' + esc(f.r || f.p) + '</small></div>' +
                barras(D.anios.map(function (a) { var x = f.h.filter(function (y) { return y.a === a; })[0]; return x ? x.p : null; }), 92, 34, false) +
                '<div class="mp">meta ' + D.obj + '<b>' + (f.pf == null ? '—' : esc(f.pf)) + '</b></div></div>';
        }).join('') : '<div class="vacio">Sin indicadores con este filtro.</div>';
        $('pagInfo').textContent = fs.length ? (pagina * porPagina + 1) + '–' + Math.min(fs.length, (pagina + 1) * porPagina) + ' de ' + fs.length : '0';
        $('pagAnt').disabled = pagina === 0; $('pagSig').disabled = pagina >= paginas - 1;
        document.querySelectorAll('#lista .ind').forEach(function (el) { el.addEventListener('click', function () { detalle(+el.dataset.i); }); });
    }

    function detalle(i) {
        var f = D.filas[i];
        var filas = f.h.map(function (x) {
            return '<tr><td><b>' + x.a + '</b></td><td>' + (x.mf == null ? '<span style="color:#aeaeb2">No formulado</span>' : esc(x.mf)) + '</td><td>' + (x.lf == null ? '<span style="color:#aeaeb2">sin dato</span>' : esc(x.lf)) + '</td>' +
                '<td>' + (x.p == null ? '—' : fmt(x.p) + ' %<div class="barra-pct"><i style="width:' + Math.min(100, x.p) + '%;background:' + colPct(x.p) + '"></i></div>') + '</td></tr>';
        }).join('');
        $('cDetalle').innerHTML = '<button class="cerrar" data-cerrar aria-label="Cerrar">×</button><h3>' + esc(f.n) + '</h3>' +
            '<div class="meta-linea">' + esc(f.l) + ' › ' + esc(f.m) + ' › ' + esc(f.p) + '</div>' +
            '<dl><dt>Responsable</dt><dd>' + esc(f.r) + '</dd><dt>Fórmula</dt><dd>' + esc(f.f) + '</dd><dt>Unidad · tipo</dt><dd>' + esc(f.u) + ' · ' + esc(f.t) + '</dd><dt>ID indicador</dt><dd>' + esc(f.c) + '</dd>' +
            '<dt>Estado</dt><dd><b style="color:' + COL[f.e] + '">' + f.e + '</b></dd></dl>' +
            '<table><thead><tr><th>Año</th><th>Meta</th><th>Logrado</th><th>Cumplimiento</th></tr></thead><tbody>' + filas + '</tbody></table>' +
            '<div class="propuesta"><span>Meta propuesta ' + D.obj + '</span><b>' + (f.pf == null ? '—' : esc(f.pf)) + '</b><span>' + esc(f.d) + '</span></div>';
        $('mDetalle').classList.add('abierto');
    }

    function pintar() { pintarKpis(); pintarDona(); pintarLineas(); pintarLista(); }

    $('limpiarEstado').addEventListener('click', function () { fEstado = ''; pagina = 0; pintar(); });
    $('limpiarLinea').addEventListener('click', function () { fLinea = ''; pagina = 0; pintar(); });
    $('pagAnt').addEventListener('click', function () { pagina = Math.max(0, pagina - 1); pintarLista(); });
    $('pagSig').addEventListener('click', function () { pagina++; pintarLista(); });
    $('btnTabla').addEventListener('click', function () { $('mTabla').classList.add('abierto'); });
    document.addEventListener('click', function (e) {
        if (e.target.matches('[data-cerrar]') || e.target.classList.contains('pv-modal')) { document.querySelectorAll('.pv-modal').forEach(function (m) { m.classList.remove('abierto'); }); }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') document.querySelectorAll('.pv-modal').forEach(function (m) { m.classList.remove('abierto'); }); });
    window.addEventListener('resize', pintarLista);
    pintar();
})();
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
