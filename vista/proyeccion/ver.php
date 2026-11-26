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

$lineas = array_values(array_unique(array_column($datos['filas'], 'linea')));
$cuenta = ['Cumplido' => 0, 'Parcial' => 0, 'No cumplido' => 0, 'Sin dato' => 0];
foreach ($datos['filas'] as $f) $cuenta[$f['estado']]++;
$conProp = count(array_filter($datos['filas'], function ($f) { return $f['propuesta'] !== null; }));

ob_start();
?>
<style>
    .av { max-width: 1500px; margin: 0 auto; padding: 1.25rem 1rem 3rem; color: #1d1d1f; }
    .av a.volver { color: #0071e3; text-decoration: none; font-size: .9rem; }
    .av .cab { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin: .5rem 0 1rem; }
    .av h1 { font-size: 1.8rem; font-weight: 800; margin: 0; letter-spacing: -.02em; }
    .av .sub { color: #86868b; margin: .2rem 0 0; font-size: .9rem; }
    .av .btn { border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .45rem .9rem; cursor: pointer; font-size: .88rem; color: inherit; text-decoration: none; display: inline-block; }
    .av .btn.primario { background: #0071e3; border-color: #0071e3; color: #fff; font-weight: 600; }
    .av .btn.on { background: #0071e3; border-color: #0071e3; color: #fff; }
    .av .resumen { display: flex; gap: 1.5rem; flex-wrap: wrap; background: #f5faff; border: 1px solid #cfe3fb; border-radius: 12px; padding: .7rem 1.1rem; margin-bottom: .9rem; font-size: .88rem; }
    .av .resumen b { font-size: 1.3rem; display: block; color: #0058b0; }
    .av .calc { display: flex; gap: .6rem; align-items: flex-end; flex-wrap: wrap; background: #fff; border: 1px solid #d2d2d7; border-radius: 12px; padding: .8rem 1rem; margin-bottom: .9rem; }
    .av .calc label { font-size: .75rem; font-weight: 600; color: #424245; display: block; margin-bottom: .2rem; }
    .av .calc select, .av .calc input { padding: .45rem .7rem; border: 1px solid #d2d2d7; border-radius: 8px; font-size: .88rem; background: #fff; }
    .av .tools { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; margin-bottom: .75rem; }
    .av .tools input[type=search] { padding: .45rem .8rem; border: 1px solid #d2d2d7; border-radius: 8px; min-width: 260px; font-size: .88rem; }
    .av .tools select { padding: .45rem .7rem; border: 1px solid #d2d2d7; border-radius: 8px; font-size: .88rem; background: #fff; }
    .av .marco { background: #fff; border: 1px solid #b7b7bd; border-radius: 6px; overflow: auto; max-height: 72vh; }
    .av table { border-collapse: collapse; font-size: .74rem; width: max-content; min-width: 100%; }
    .av th { background: #1f3864; color: #fff; font-weight: 700; padding: .45rem .5rem; border: 1px solid #fff; text-align: center; position: sticky; top: 0; z-index: 2; }
    .av th.prop { background: #1b5e20; }
    .av td { border: 1px solid #d9d9de; padding: .35rem .5rem; vertical-align: middle; text-align: center; }
    .av td.t { text-align: left; }
    .av td.linea { color: #1f6f3b; } .av td.motor { color: #2d4fa1; } .av td.proy { color: #1f6f6f; }
    .av td.apil div { white-space: nowrap; text-align: left; }
    .av td.apil div.nf { color: #a0a0a6; }
    .av td.est { font-weight: 600; }
    .av td.est.NC { background: #f8cbd0; color: #9c0006; } .av td.est.CU { background: #c6efce; color: #006100; } .av td.est.PA { background: #ffeb9c; color: #7a5b00; }
    .av td.est.SD { background: #f0f0f3; color: #6e6e73; }
    .av td.prop { font-weight: 800; font-size: .9rem; color: #1b5e20; background: #f1faf2; }
    .av td.desc { text-align: left; min-width: 220px; color: #424245; }
    .av .col-ind { min-width: 260px; } .av .col-form { min-width: 260px; } .av .col-resp { min-width: 150px; }
    .av .oculto { display: none; }
    .av .leyenda { font-size: .78rem; color: #86868b; margin-top: .6rem; }
    @media print { .av .tools, .av .calc, .av a.volver, .av .acciones-cab { display: none; } .av .marco { max-height: none; overflow: visible; } }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<section class="av">
    <a class="volver" href="<?php echo $basePath; ?>/proyeccion">← Volver a proyecciones</a>
    <div class="cab">
        <div>
            <h1><?php echo $h($abierto['nombre']); ?></h1>
            <p class="sub">Base <?php echo $h($rango); ?> → metas propuestas <?php echo $obj; ?> · generada el <?php echo $h(date('d/m/Y H:i', strtotime($abierto['fecha_creacion']))); ?></p>
        </div>
        <div class="acciones-cab">
            <a class="btn" href="<?php echo $basePath; ?>/proyeccion/ver?id=<?php echo (int)$abierto['id']; ?>&export=1&m=<?php echo $h($metodo); ?>&a=<?php echo $h($ajuste); ?>">Exportar a Excel</a>
            <button class="btn" onclick="window.print()">Imprimir</button>
        </div>
    </div>

    <div class="resumen">
        <div><b><?php echo count($datos['filas']); ?></b>indicadores</div>
        <div><b><?php echo $conProp; ?></b>con meta propuesta</div>
        <div><b><?php echo $cuenta['Cumplido']; ?></b>cumplidos en su último año</div>
        <div><b><?php echo $cuenta['Parcial']; ?></b>parciales</div>
        <div><b><?php echo $cuenta['No cumplido']; ?></b>no cumplidos</div>
    </div>

    <form class="calc" method="get" action="<?php echo $basePath; ?>/proyeccion/ver">
        <input type="hidden" name="id" value="<?php echo (int)$abierto['id']; ?>">
        <div><label for="m">Cómo se calcula la meta <?php echo $obj; ?></label>
            <select name="m" id="m"><?php foreach (ProyeccionModel::METODOS as $k => $v): ?><option value="<?php echo $k; ?>" <?php echo $k === $metodo ? 'selected' : ''; ?>><?php echo $h($v); ?></option><?php endforeach; ?></select></div>
        <div><label for="a">Ajuste (%)</label><input type="number" step="0.5" name="a" id="a" value="<?php echo $h($ajuste); ?>" style="width:90px"></div>
        <button class="btn primario" type="submit">Recalcular</button>
        <button class="btn" type="submit" formmethod="post" name="accion" value="actualizar" title="Guarda este método y ajuste en la proyección">Guardar</button>
    </form>

    <div class="tools">
        <input type="search" id="buscar" placeholder="Buscar indicador, responsable, proyecto…">
        <select id="fLinea"><option value="">Todas las líneas</option><?php foreach ($lineas as $l): ?><option><?php echo $h($l); ?></option><?php endforeach; ?></select>
        <select id="fEstado"><option value="">Todos los estados</option><?php foreach (array_keys($cuenta) as $e): ?><option><?php echo $h($e); ?></option><?php endforeach; ?></select>
        <button class="btn" id="fTrasc" type="button">Solo los que aparecen en 2 o más años</button>
        <span id="vistos" style="color:#86868b;font-size:.82rem;"></span>
    </div>

    <div class="marco">
        <table id="tabla">
            <thead>
                <tr>
                    <th>Línea estratégica</th><th>Motor de desarrollo</th><th>Proyecto</th><th class="col-resp">Responsable</th>
                    <th class="col-ind">Nombre del indicador</th><th class="col-form">Fórmula del indicador</th><th>Unidad</th><th>Tipo de medición</th>
                    <th>Metas <?php echo $h($rango); ?></th><th>Cumplimiento <?php echo $h($rango); ?></th><th>Estado</th>
                    <th class="prop">Meta propuesta <?php echo $obj; ?></th><th class="prop">Descripción <?php echo $obj; ?></th><th>ID indicador</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($datos['filas'] as $f):
                $cls = ['Cumplido' => 'CU', 'Parcial' => 'PA', 'No cumplido' => 'NC', 'Sin dato' => 'SD'][$f['estado']];
            ?>
                <tr data-linea="<?php echo $h($f['linea']); ?>" data-estado="<?php echo $h($f['estado']); ?>" data-pres="<?php echo (int)$f['presentes']; ?>"
                    data-txt="<?php echo $h(mb_strtolower($f['nombre'] . ' ' . $f['responsable'] . ' ' . $f['proyecto'] . ' ' . $f['motor'] . ' ' . $f['codigo'], 'UTF-8')); ?>">
                    <td class="t linea"><?php echo $h($f['linea']); ?></td>
                    <td class="t motor"><?php echo $h($f['motor']); ?></td>
                    <td class="t proy"><?php echo $h($f['proyecto']); ?></td>
                    <td class="t"><?php echo $h($f['responsable']); ?></td>
                    <td class="t"><?php echo $h($f['nombre']); ?></td>
                    <td class="t"><?php echo $h($f['formula']); ?></td>
                    <td><?php echo $h($f['unidad']); ?></td>
                    <td><?php echo $h($f['tipo']); ?></td>
                    <td class="apil"><?php foreach ($metasTxt($f) as $l): ?><div class="<?php echo strpos($l, 'No formulado') !== false ? 'nf' : ''; ?>"><?php echo $h($l); ?></div><?php endforeach; ?></td>
                    <td class="apil"><?php foreach ($cumplTxt($f) as $l): ?><div class="<?php echo (strpos($l, 'No formulado') !== false || strpos($l, 'sin dato') !== false) ? 'nf' : ''; ?>"><?php echo $h($l); ?></div><?php endforeach; ?></td>
                    <td class="est <?php echo $cls; ?>"><?php echo $h($f['estado']); ?></td>
                    <td class="prop"><?php echo $f['propuesta'] === null ? '—' : $h($num($f['propuesta'], $f['unidad'])); ?></td>
                    <td class="desc"><?php echo $h($f['descripcion']); ?></td>
                    <td><?php echo $h($f['codigo']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="leyenda">La meta propuesta se calcula con lo LOGRADO aprobado en los años base (trimestres aprobados); si un año no tiene seguimiento aprobado se muestra "sin dato". Un mismo indicador se reconoce entre años por su nombre. Estado: cumplido ≥ 100 %, parcial 60–99 %, no cumplido &lt; 60 % en el último año con meta y logrado. En los indicadores de tipo Límite no se sube la meta: se mantiene el último límite.</p>
</section>

<script>
(function () {
    var filas = document.querySelectorAll('#tabla tbody tr');
    var q = document.getElementById('buscar'), fl = document.getElementById('fLinea'), fe = document.getElementById('fEstado'), ft = document.getElementById('fTrasc');
    var vistos = document.getElementById('vistos');
    function aplicar() {
        var txt = q.value.trim().toLowerCase(), n = 0;
        filas.forEach(function (tr) {
            var ok = (!txt || tr.dataset.txt.indexOf(txt) !== -1) && (!fl.value || tr.dataset.linea === fl.value) &&
                     (!fe.value || tr.dataset.estado === fe.value) && (!ft.classList.contains('on') || +tr.dataset.pres >= 2);
            tr.classList.toggle('oculto', !ok);
            if (ok) n++;
        });
        vistos.textContent = n + ' de ' + filas.length + ' indicadores';
    }
    q.addEventListener('input', aplicar); fl.addEventListener('change', aplicar); fe.addEventListener('change', aplicar);
    ft.addEventListener('click', function () { ft.classList.toggle('on'); aplicar(); });
    aplicar();
})();
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
