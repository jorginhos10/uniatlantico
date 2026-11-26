<?php
// vista/asivamos/ver.php
// Detalle de un comparativo guardado: tarjetas por plan y árbol línea → motor → proyecto → indicador,
// con la trascendencia de cada indicador (en cuántos de los planes comparados aparece).
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../modelo/FORDE144Model.php';
require_once __DIR__ . '/../../modelo/ComparativoModel.php';

$titulo       = 'Así vamos';
$paginaActual = 'asivamos';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

$h   = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$fmt = function ($n, $dec = 1) { return number_format((float)$n, $dec, ',', '.'); };
$tono = function ($p) { return $p >= 80 ? 'ok' : ($p >= 60 ? 'warn' : 'bad'); };

$uid     = (int)($_SESSION['usuario_id'] ?? 0);
$rolNorm = strtr(mb_strtolower(trim((string)($_SESSION['usuario_rol'] ?? '')), 'UTF-8'), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
$veTodo  = $uid === 1 || in_array($rolNorm, ['admin', 'administrador', 'sub administrador'], true);
if (!$veTodo) { header('Location: ' . $basePath . '/asivamos'); exit; }

$cmpModel = new ComparativoModel();
$abierto = $cmpModel->obtener((int)($_GET['id'] ?? 0));
if (!$abierto) { header('Location: ' . $basePath . '/asivamos'); exit; }

$forde = new FORDE144Model();
$planes = [];
foreach ($abierto['ids'] as $fid) { $f = $forde->getById($fid); if ($f) $planes[] = $f; }
usort($planes, function ($a, $b) { return ((int)$a['anio'] <=> (int)$b['anio']) ?: ((int)$a['id'] <=> (int)$b['id']); });
$datos = $cmpModel->construir($planes);
$cab = $datos['planes'];
$n = count($cab);

// Celda de avance (valor + barra); null = el elemento no existe en ese plan
$celda = function ($v) use ($fmt, $tono) {
    if ($v === null) return '<div class="cel"><span class="nd">—</span></div>';
    $t = $tono($v);
    return '<div class="cel"><span class="v ' . $t . '">' . $fmt($v) . '%</span><div class="bar"><i class="' . $t . '" style="width:' . min(100, max(0, $v)) . '%"></i></div></div>';
};
// Tendencia: del primer al último plan en que existe
$tendencia = function (array $vals) use ($fmt) {
    $v = array_values(array_filter($vals, function ($x) { return $x !== null; }));
    if (count($v) < 2) return '<div class="cel"><span class="nd">—</span></div>';
    $d = end($v) - reset($v);
    $c = $d > 0.05 ? 'ok' : ($d < -0.05 ? 'bad' : 'nd');
    return '<div class="cel"><span class="v ' . $c . '">' . ($d > 0.05 ? '▲ +' : ($d < -0.05 ? '▼ ' : '= ')) . $fmt($d) . '</span></div>';
};
$valores = function (array $vals) use ($celda, $n) {
    $o = '';
    for ($i = 0; $i < $n; $i++) $o .= $celda($vals[$i] ?? null);
    return $o;
};

ob_start();
?>
<style>
    .av { max-width: 1240px; margin: 0 auto; padding: 1.25rem 1rem 3rem; color: #1d1d1f; --n: <?php echo $n; ?>; }
    .av a.volver { color: #0071e3; text-decoration: none; font-size: .9rem; }
    .av .cab { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin: .5rem 0 1.25rem; }
    .av h1 { font-size: 1.9rem; font-weight: 800; margin: 0; letter-spacing: -.02em; }
    .av .sub { color: #86868b; margin: .2rem 0 0; font-size: .9rem; }
    .av .btn { border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .45rem .9rem; cursor: pointer; font-size: .88rem; }
    .av .btn.on { background: #0071e3; border-color: #0071e3; color: #fff; }
    .av .planes { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: .75rem; margin-bottom: 1rem; }
    .av .plan { background: #fff; border: 1px solid #d2d2d7; border-radius: 12px; padding: .9rem 1rem; box-shadow: 0 1px 6px rgba(0,0,0,.05); }
    .av .plan .anio { font-size: .8rem; color: #86868b; font-weight: 600; letter-spacing: .04em; }
    .av .plan .gl { font-size: 1.9rem; font-weight: 800; line-height: 1.15; margin: .15rem 0; }
    .av .plan .meta { font-size: .78rem; color: #424245; }
    .av .plan .dl { font-size: .78rem; margin-top: .2rem; }
    .av .resumen { display: flex; gap: 1.5rem; flex-wrap: wrap; background: #f5faff; border: 1px solid #cfe3fb; border-radius: 12px; padding: .8rem 1.1rem; margin-bottom: 1rem; font-size: .9rem; }
    .av .resumen b { font-size: 1.35rem; display: block; color: #0058b0; }
    .av .barra-tools { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; margin-bottom: .75rem; }
    .av .barra-tools input[type=search] { padding: .45rem .8rem; border: 1px solid #d2d2d7; border-radius: 8px; min-width: 240px; font-size: .9rem; }
    .av .arbol { background: #fff; border: 1px solid #d2d2d7; border-radius: 12px; overflow-x: auto; box-shadow: 0 1px 6px rgba(0,0,0,.05); }
    .av .fila { display: grid; grid-template-columns: minmax(300px, 1fr) repeat(var(--n), 96px) 92px 150px; align-items: center; gap: .25rem; padding: .5rem .8rem; min-width: calc(300px + var(--n) * 96px + 260px); }
    .av .fila.enc { background: #f5f5f7; font-weight: 700; font-size: .82rem; position: sticky; top: 0; z-index: 1; border-bottom: 1px solid #d2d2d7; }
    .av .fila.enc .c { text-align: center; }
    .av .fila.enc small { display: block; color: #86868b; font-weight: 500; }
    .av summary { list-style: none; cursor: pointer; }
    .av summary::-webkit-details-marker { display: none; }
    .av summary .nom::before { content: '▸'; display: inline-block; width: 1.1rem; color: #86868b; transition: transform .12s; }
    .av details[open] > summary .nom::before { transform: rotate(90deg); }
    .av details > summary:hover { background: #fafafc; }
    .av .n1 > summary { background: #f9fafc; font-weight: 700; border-top: 1px solid #ececf0; }
    .av .n2 > summary { font-weight: 600; padding-left: 1.6rem; }
    .av .n3 > summary { padding-left: 2.4rem; font-weight: 500; }
    .av .ind { padding-left: 3.4rem; font-size: .88rem; border-top: 1px dashed #f0f0f3; }
    .av .ind .nom { padding-left: .1rem; }
    .av .ind .nom small { display: block; color: #86868b; font-size: .74rem; }
    .av .cel { text-align: center; }
    .av .cel .v { font-size: .85rem; font-weight: 600; }
    .av .bar { height: 5px; background: #ececf0; border-radius: 3px; margin: 3px 10px 0; overflow: hidden; }
    .av .bar i { display: block; height: 100%; }
    .av .ok { color: #1f8a3d; } .av .warn { color: #b26a00; } .av .bad { color: #d70015; } .av .nd { color: #aeaeb2; }
    .av .bar .ok { background: #1f8a3d; } .av .bar .warn { background: #e0a100; } .av .bar .bad { background: #d70015; }
    .av .tr { text-align: center; font-size: .78rem; }
    .av .badge { display: inline-block; padding: .12rem .55rem; border-radius: 999px; font-size: .72rem; font-weight: 600; }
    .av .badge.todos { background: #e3f8e9; color: #1f8a3d; }
    .av .badge.varios { background: #e8f2ff; color: #0058b0; }
    .av .badge.uno { background: #f0f0f3; color: #6e6e73; }
    .av .oculto { display: none !important; }
    .av .leyenda { font-size: .78rem; color: #86868b; margin-top: .6rem; }
    @media print { .av .barra-tools, .av a.volver, .av .acciones-cab { display: none; } .av .arbol { overflow: visible; } }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<section class="av">
    <a class="volver" href="<?php echo $basePath; ?>/asivamos">← Volver a comparativos</a>
    <div class="cab">
        <div>
            <h1><?php echo $h($abierto['nombre']); ?></h1>
            <p class="sub">Generado el <?php echo $h(date('d/m/Y H:i', strtotime($abierto['fecha_creacion']))); ?> · las cifras se calculan con el avance actual de cada plan</p>
        </div>
        <div class="acciones-cab"><button class="btn" onclick="window.print()">Imprimir</button></div>
    </div>

    <div class="planes">
        <?php $prev = null; foreach ($cab as $c): ?>
            <div class="plan">
                <div class="anio">AÑO <?php echo (int)$c['anio']; ?></div>
                <div class="gl <?php echo $tono($c['global']); ?>"><?php echo $fmt($c['global']); ?>%</div>
                <div class="meta"><?php echo $c['reportados']; ?> de <?php echo $c['metas']; ?> reportados (T<?php echo $c['trim']; ?>)</div>
                <?php if ($prev !== null): $d = $c['global'] - $prev; ?>
                    <div class="dl <?php echo $d >= 0 ? 'ok' : 'bad'; ?>"><?php echo ($d >= 0 ? '▲ +' : '▼ ') . $fmt($d); ?> pts vs. plan anterior</div>
                <?php endif; ?>
            </div>
        <?php $prev = $c['global']; endforeach; ?>
    </div>

    <div class="resumen">
        <div><b><?php echo $datos['indicadores']; ?></b>indicadores distintos</div>
        <div><b><?php echo $datos['trascienden']; ?></b>trascienden (aparecen en 2 o más planes)</div>
        <div><b><?php echo $datos['en_todos']; ?></b>están en los <?php echo $n; ?> planes</div>
    </div>

    <div class="barra-tools">
        <input type="search" id="buscar" placeholder="Buscar indicador, línea, motor o proyecto…">
        <button class="btn" id="fTrasc" type="button">Solo los que trascienden</button>
        <button class="btn" id="fCrit" type="button">Solo críticos (&lt; 60 % en su último plan)</button>
        <button class="btn" id="abrirTodo" type="button">Expandir todo</button>
        <button class="btn" id="cerrarTodo" type="button">Contraer todo</button>
    </div>

    <div class="arbol" id="arbol">
        <div class="fila enc">
            <div>Línea / Motor / Proyecto / Indicador</div>
            <?php foreach ($cab as $c): ?><div class="c"><?php echo (int)$c['anio']; ?><small>avance</small></div><?php endforeach; ?>
            <div class="c">Tendencia</div>
            <div class="c">Trascendencia</div>
        </div>

        <?php foreach ($datos['arbol'] as $L): ?>
        <details class="nodo n1">
            <summary class="fila">
                <div class="nom"><?php echo $h($L['nombre']); ?></div>
                <?php echo $valores($L['vals']); ?>
                <?php echo $tendencia($L['vals']); ?>
                <div class="tr"><?php echo $L['trasc']; ?> de <?php echo $L['total']; ?> trascienden</div>
            </summary>
            <?php foreach ($L['motores'] as $M): ?>
            <details class="nodo n2">
                <summary class="fila">
                    <div class="nom"><?php echo $h($M['nombre']); ?></div>
                    <?php echo $valores($M['vals']); ?>
                    <?php echo $tendencia($M['vals']); ?>
                    <div class="tr"><?php echo $M['trasc']; ?> de <?php echo $M['total']; ?></div>
                </summary>
                <?php foreach ($M['proyectos'] as $P): ?>
                <details class="nodo n3">
                    <summary class="fila">
                        <div class="nom"><?php echo $h($P['nombre']); ?></div>
                        <?php echo $valores($P['vals']); ?>
                        <?php echo $tendencia($P['vals']); ?>
                        <div class="tr"><?php echo $P['trasc']; ?> de <?php echo $P['total']; ?></div>
                    </summary>
                    <?php foreach ($P['inds'] as $I):
                        $presentes = array_filter($I['vals'], function ($x) { return $x !== null; });
                        $ultimo = $presentes ? end($presentes) : null;
                        $critico = $ultimo !== null && $ultimo < 60;
                        $clase = $I['todos'] ? 'todos' : ($I['presentes'] >= 2 ? 'varios' : 'uno');
                        $texto = $I['todos'] ? 'En los ' . $n . ' planes' : ($I['presentes'] >= 2 ? 'En ' . $I['presentes'] . ' de ' . $n : 'Solo ' . (isset($cab[array_key_first($I['vals'])]) ? (int)$cab[array_key_first($I['vals'])]['anio'] : '1 plan'));
                    ?>
                    <div class="fila ind" data-pres="<?php echo (int)$I['presentes']; ?>" data-crit="<?php echo $critico ? 1 : 0; ?>"
                         data-txt="<?php echo $h(mb_strtolower($I['nombre'] . ' ' . $I['codigo'] . ' ' . $I['resp'], 'UTF-8')); ?>">
                        <div class="nom"><?php echo $h($I['nombre']); ?><small><?php echo $h(trim($I['codigo'] . ' ' . ($I['resp'] ? '· ' . $I['resp'] : ''))); ?></small></div>
                        <?php echo $valores($I['vals']); ?>
                        <div class="cel"><?php if ($I['cambio'] === null): ?><span class="nd">—</span><?php else: $c = $I['cambio'] > 0.05 ? 'ok' : ($I['cambio'] < -0.05 ? 'bad' : 'nd'); ?><span class="v <?php echo $c; ?>"><?php echo ($I['cambio'] > 0.05 ? '▲ +' : ($I['cambio'] < -0.05 ? '▼ ' : '= ')) . $fmt($I['cambio']); ?></span><?php endif; ?></div>
                        <div class="tr"><span class="badge <?php echo $clase; ?>"><?php echo $h($texto); ?></span></div>
                    </div>
                    <?php endforeach; ?>
                </details>
                <?php endforeach; ?>
            </details>
            <?php endforeach; ?>
        </details>
        <?php endforeach; ?>
    </div>
    <p class="leyenda">Un indicador "trasciende" cuando el mismo indicador (mismo nombre) aparece en dos o más de los planes comparados. La tendencia va del primer al último plan en que aparece. Verde ≥ 80 %, ámbar 60–80 %, rojo &lt; 60 %.</p>
</section>

<script>
(function () {
    var arbol = document.getElementById('arbol');
    var inds = arbol.querySelectorAll('.ind');
    var nodos = Array.prototype.slice.call(arbol.querySelectorAll('details')).reverse(); // de adentro hacia afuera
    var q = document.getElementById('buscar'), fT = document.getElementById('fTrasc'), fC = document.getElementById('fCrit');

    function aplicar() {
        var txt = q.value.trim().toLowerCase(), soloT = fT.classList.contains('on'), soloC = fC.classList.contains('on');
        var filtrando = txt !== '' || soloT || soloC;
        inds.forEach(function (el) {
            var ok = (!txt || el.dataset.txt.indexOf(txt) !== -1) && (!soloT || +el.dataset.pres >= 2) && (!soloC || el.dataset.crit === '1');
            el.classList.toggle('oculto', !ok);
        });
        nodos.forEach(function (d) {
            var visible = d.querySelector('.ind:not(.oculto)');
            var nombre = (d.querySelector(':scope > summary .nom') || {}).textContent || '';
            var coincideNombre = txt && nombre.toLowerCase().indexOf(txt) !== -1;
            d.classList.toggle('oculto', filtrando && !visible && !coincideNombre);
            if (filtrando && visible) d.open = true;
        });
    }
    q.addEventListener('input', aplicar);
    [fT, fC].forEach(function (b) { b.addEventListener('click', function () { b.classList.toggle('on'); aplicar(); }); });
    document.getElementById('abrirTodo').addEventListener('click', function () { nodos.forEach(function (d) { d.open = true; }); });
    document.getElementById('cerrarTodo').addEventListener('click', function () { nodos.forEach(function (d) { d.open = false; }); });
    window.addEventListener('beforeprint', function () { nodos.forEach(function (d) { d.open = true; }); });
})();
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
