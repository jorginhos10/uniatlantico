<?php
// vista/plan_indicativo/index.php
// Plan Indicativo PDI 2022–2031: metas por trienio de cada indicador de resultado, agrupadas por línea, motor y proyecto.
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../modelo/PlanIndicativoModel.php';

$titulo       = 'Plan indicativo';
$paginaActual = 'plan-indicativo';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$filas = (new PlanIndicativoModel())->listar();
$periodos = PlanIndicativoModel::PERIODOS;
$anioHoy = (int)date('Y');
$actual = '';
foreach ($periodos as $col => $p) if ($anioHoy >= $p[1] && $anioHoy <= $p[2]) $actual = $col;
$fmt = function ($f, $col) { return PlanIndicativoModel::formato($f[$col], (int)$f['es_porcentaje'] === 1); };

// Exportar a CSV (se abre en Excel)
if (isset($_GET['export']) && $filas) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="plan-indicativo-pdi.csv"');
    echo "\xEF\xBB\xBF";
    $o = fopen('php://output', 'w');
    fputcsv($o, array_merge(['N°', 'Línea estratégica', 'Motor de desarrollo', 'Proyecto', 'Meta de resultado', 'Nombre del indicador', 'Fórmula de medición', 'Línea base'],
                            array_column($periodos, 0), ['Meta PDI']), ';');
    foreach ($filas as $f) {
        $fila = [$f['numero'], $f['linea'], $f['motor'], $f['proyecto'], $f['meta_resultado'], $f['indicador'], $f['formula'], $fmt($f, 'linea_base')];
        foreach ($periodos as $col => $p) $fila[] = $fmt($f, $col);
        $fila[] = $fmt($f, 'meta_pdi');
        fputcsv($o, $fila, ';');
    }
    fclose($o);
    exit;
}

$lineas = [];
foreach ($filas as $f) $lineas[$f['linea']] = ($lineas[$f['linea']] ?? 0) + 1;
uksort($lineas, 'strnatcasecmp');
// Orden de lectura: línea → motor → proyecto → número
usort($filas, function ($a, $b) {
    return strnatcasecmp($a['linea'], $b['linea']) ?: strnatcasecmp($a['motor'], $b['motor']) ?: strnatcasecmp($a['proyecto'], $b['proyecto']) ?: ((int)$a['numero'] <=> (int)$b['numero']);
});

ob_start();
?>
<style>
    .pi { height: calc(100vh - 120px); min-height: 520px; display: flex; flex-direction: column; gap: .7rem; color: #1d1d1f; overflow: hidden; }
    .pi * { box-sizing: border-box; }
    .pi .top { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; }
    .pi h1 { font-size: 1.45rem; font-weight: 800; margin: 0; letter-spacing: -.02em; }
    .pi .sub { color: #86868b; font-size: .8rem; margin: .15rem 0 0; }
    .pi .btn { border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .4rem .8rem; cursor: pointer; font-size: .8rem; color: inherit; text-decoration: none; display: inline-block; }
    .pi .btn:hover { border-color: #0071e3; color: #0071e3; }
    .pi .lineas { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: .55rem; }
    .pi .tarj { background: #fff; border: 1px solid #e0e0e5; border-radius: 12px; padding: .5rem .8rem; cursor: pointer; text-align: left; display: flex; gap: .65rem; align-items: center; color: inherit; transition: border-color .15s, background .15s; }
    .pi .tarj:hover { border-color: #0071e3; }
    .pi .tarj.on { border-color: #0071e3; background: #e8f2ff; }
    .pi .tarj b { font-size: 1.35rem; color: #1f3864; font-variant-numeric: tabular-nums; }
    .pi .tarj span { font-size: .72rem; line-height: 1.2; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .pi .filtros { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
    .pi .filtros input, .pi .filtros select { padding: .42rem .65rem; border: 1px solid #d2d2d7; border-radius: 8px; font-size: .8rem; background: #fff; }
    .pi .filtros input { min-width: 260px; }
    .pi .filtros select { max-width: 330px; }
    .pi .filtros .conteo { color: #86868b; font-size: .76rem; margin-left: auto; }
    .pi .marco { flex: 1; min-height: 0; overflow: auto; background: #fff; border: 1px solid #e0e0e5; border-radius: 12px; }
    .pi table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .8rem; min-width: 980px; }
    .pi th { position: sticky; top: 0; z-index: 3; background: #1f3864; color: #fff; padding: .55rem .6rem; text-align: center; font-size: .76rem; border-left: 1px solid rgba(255,255,255,.2); white-space: nowrap; }
    .pi th.izq { text-align: left; }
    .pi th.act { background: #b7791f; }
    .pi th small { display: block; font-weight: 500; opacity: .85; font-size: .64rem; }
    .pi td { padding: .55rem .6rem; border-bottom: 1px solid #f0f0f3; vertical-align: middle; text-align: center; font-variant-numeric: tabular-nums; }
    .pi td.izq { text-align: left; }
    .pi td.num { color: #a1a1a6; font-size: .72rem; width: 40px; }
    .pi td.ind { width: 32%; }
    .pi td.ind .nom { font-weight: 600; line-height: 1.3; }
    .pi td.res { width: 26%; color: #424245; font-size: .75rem; line-height: 1.35; }
    .pi td.val { font-weight: 600; white-space: nowrap; }
    .pi td.base { color: #6e6e73; font-weight: 500; }
    .pi td.act { background: #fff6dd; font-weight: 800; font-size: .88rem; }
    .pi td.meta { background: #eef3fb; font-weight: 800; color: #1f3864; }
    .pi tr.grupo td { background: #f5f7fb; text-align: left; padding: .45rem .8rem; border-bottom: 1px solid #e0e0e5; font-size: .74rem; color: #424245; }
    .pi tr.grupo b { color: #1f3864; }
    .pi tbody tr.fila:hover td { background: #fafcff; } .pi tbody tr.fila:hover td.act { background: #ffefc7; }
    .pi details { margin-top: 4px; font-size: .7rem; }
    .pi details summary { list-style: none; cursor: pointer; color: #0071e3; display: inline-flex; align-items: center; gap: .3rem; }
    .pi details summary::-webkit-details-marker { display: none; }
    .pi details summary::before { content: '+'; display: inline-flex; align-items: center; justify-content: center; width: 15px; height: 15px; border: 1px solid #b9d6f7; border-radius: 4px; line-height: 1; }
    .pi details[open] summary::before { content: '−'; }
    .pi details p { margin: 4px 0 0; color: #6e6e73; line-height: 1.35; }
    .pi .oculto { display: none; }
    .pi .vacio { background: #fff; border: 1px solid #e0e0e5; border-radius: 12px; padding: 2rem; text-align: center; color: #6e6e73; }
    @media print { .pi { height: auto; overflow: visible; } .pi .filtros, .pi .btn, .pi .lineas { display: none; } .pi .marco { overflow: visible; border: 0; } }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<section class="pi">
    <div class="top">
        <div>
            <h1>Plan Indicativo PDI 2022–2031</h1>
            <p class="sub">Versión 2 · metas de resultado por trienio<?php echo $actual ? ' · resaltado el período actual ' . $h($periodos[$actual][0]) : ''; ?></p>
        </div>
        <?php if ($filas): ?>
        <div>
            <a class="btn" href="<?php echo $basePath; ?>/plan-indicativo?export=1">Excel</a>
            <button class="btn" type="button" onclick="window.print()">Imprimir</button>
        </div>
        <?php endif; ?>
    </div>

<?php if (!$filas): ?>
    <div class="vacio">Todavía no hay datos del Plan Indicativo. Ejecuta <b>database/plan_indicativo.sql</b> en la base de datos.</div>
<?php else: ?>
    <div class="lineas">
        <?php foreach ($lineas as $nombre => $cuantos): ?>
            <button type="button" class="tarj" data-linea="<?php echo $h($nombre); ?>" title="<?php echo $h($nombre); ?>"><b><?php echo $cuantos; ?></b><span><?php echo $h($nombre); ?></span></button>
        <?php endforeach; ?>
    </div>

    <div class="filtros">
        <input type="search" id="buscar" placeholder="Buscar indicador, fórmula o meta…">
        <select id="fLinea" aria-label="Línea"></select>
        <select id="fMotor" aria-label="Motor"></select>
        <select id="fProy" aria-label="Proyecto"></select>
        <span class="conteo" id="conteo"></span>
    </div>

    <div class="marco">
        <table>
            <thead><tr>
                <th>N°</th><th class="izq">Indicador</th><th class="izq">Meta de resultado</th><th>Línea base</th>
                <?php foreach ($periodos as $col => $p): ?><th class="<?php echo $col === $actual ? 'act' : ''; ?>"><?php echo $h($p[0]); ?><?php if ($col === $actual): ?><small>período actual</small><?php endif; ?></th><?php endforeach; ?>
                <th>Meta PDI</th>
            </tr></thead>
            <tbody id="cuerpo">
            <?php $grupo = null; foreach ($filas as $f):
                $g = $f['linea'] . '|' . $f['motor'] . '|' . $f['proyecto'];
                if ($g !== $grupo): $grupo = $g; ?>
                <tr class="grupo" data-g="<?php echo $h($g); ?>"><td colspan="<?php echo 5 + count($periodos); ?>"><b><?php echo $h($f['linea']); ?></b> › <?php echo $h($f['motor']); ?> › <?php echo $h($f['proyecto']); ?></td></tr>
                <?php endif; ?>
                <tr class="fila" data-g="<?php echo $h($g); ?>" data-linea="<?php echo $h($f['linea']); ?>" data-motor="<?php echo $h($f['motor']); ?>" data-proy="<?php echo $h($f['proyecto']); ?>"
                    data-txt="<?php echo $h(mb_strtolower($f['indicador'] . ' ' . $f['formula'] . ' ' . $f['meta_resultado'], 'UTF-8')); ?>">
                    <td class="num"><?php echo (int)$f['numero']; ?></td>
                    <td class="izq ind"><div class="nom"><?php echo $h($f['indicador']); ?></div>
                        <?php if (trim((string)$f['formula']) !== ''): ?><details><summary>Fórmula de medición</summary><p><?php echo $h($f['formula']); ?></p></details><?php endif; ?></td>
                    <td class="izq res"><?php echo $h($f['meta_resultado']); ?></td>
                    <td class="val base"><?php echo $h($fmt($f, 'linea_base')); ?></td>
                    <?php foreach ($periodos as $col => $p): ?><td class="val <?php echo $col === $actual ? 'act' : ''; ?>"><?php echo $h($fmt($f, $col)); ?></td><?php endforeach; ?>
                    <td class="val meta"><?php echo $h($fmt($f, 'meta_pdi')); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</section>

<script>
(function () {
    var cuerpo = document.getElementById('cuerpo');
    if (!cuerpo) return;
    var filas = Array.prototype.slice.call(cuerpo.querySelectorAll('tr.fila')), grupos = cuerpo.querySelectorAll('tr.grupo');
    var q = document.getElementById('buscar'), selL = document.getElementById('fLinea'), selM = document.getElementById('fMotor'), selP = document.getElementById('fProy');
    var tarjetas = document.querySelectorAll('.pi .tarj');

    // Cada lista muestra solo lo que corresponde a lo elegido en la anterior
    function llenar(select, todas, campo, pasa) {
        var antes = select.value, v = [];
        filas.forEach(function (f) { if (pasa(f) && v.indexOf(f.dataset[campo]) === -1) v.push(f.dataset[campo]); });
        v.sort(function (a, b) { return a.localeCompare(b, 'es', { numeric: true }); });
        select.innerHTML = '';
        select.appendChild(new Option(todas, ''));
        v.forEach(function (x) { select.appendChild(new Option(x, x)); });
        select.value = v.indexOf(antes) !== -1 ? antes : '';
    }
    function listas() {
        llenar(selL, 'Todas las líneas', 'linea', function () { return true; });
        llenar(selM, 'Todos los motores', 'motor', function (f) { return !selL.value || f.dataset.linea === selL.value; });
        llenar(selP, 'Todos los proyectos', 'proy', function (f) { return (!selL.value || f.dataset.linea === selL.value) && (!selM.value || f.dataset.motor === selM.value); });
    }
    function aplicar() {
        var txt = q.value.trim().toLowerCase(), visibles = {}, n = 0;
        filas.forEach(function (f) {
            var ok = (!txt || f.dataset.txt.indexOf(txt) !== -1) && (!selL.value || f.dataset.linea === selL.value) &&
                     (!selM.value || f.dataset.motor === selM.value) && (!selP.value || f.dataset.proy === selP.value);
            f.classList.toggle('oculto', !ok);
            if (ok) { visibles[f.dataset.g] = true; n++; }
        });
        grupos.forEach(function (g) { g.classList.toggle('oculto', !visibles[g.dataset.g]); });
        tarjetas.forEach(function (t) { t.classList.toggle('on', t.dataset.linea === selL.value); });
        document.getElementById('conteo').textContent = n + ' de ' + filas.length + ' indicadores';
    }
    q.addEventListener('input', aplicar);
    [selL, selM, selP].forEach(function (s) { s.addEventListener('change', function () { listas(); aplicar(); }); });
    tarjetas.forEach(function (t) { t.addEventListener('click', function () { selL.value = selL.value === t.dataset.linea ? '' : t.dataset.linea; listas(); aplicar(); }); });
    window.addEventListener('beforeprint', function () { document.querySelectorAll('.pi details').forEach(function (d) { d.open = true; }); });
    listas();
    aplicar();
})();
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
