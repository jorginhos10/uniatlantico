<?php
// vista/asivamos/index.php
// "Así vamos": checklist de todos los Planes de Acción del sistema y comparativo entre los marcados.
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../modelo/FORDE144Model.php';

$titulo       = 'Así vamos';
$paginaActual = 'asivamos';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$fmt = function ($n, $dec = 1) { return number_format((float)$n, $dec, ',', '.'); };
$tono = function ($p) { return $p >= 80 ? 'ok' : ($p >= 60 ? 'warn' : 'bad'); };

// Quién puede ver las cifras de toda la institución (mismo criterio del dashboard)
$uid     = (int)($_SESSION['usuario_id'] ?? 0);
$rolNorm = strtr(mb_strtolower(trim((string)($_SESSION['usuario_rol'] ?? '')), 'UTF-8'), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
$veTodo  = $uid === 1 || in_array($rolNorm, ['admin', 'administrador', 'sub administrador'], true);

$planes = [];
$comparativo = [];
$seleccion = [];
if ($veTodo) {
    $modelo = new FORDE144Model();
    $planes = $modelo->getAllAdmin();
    usort($planes, function ($a, $b) { return ((int)$a['anio'] <=> (int)$b['anio']) ?: ((int)$a['id'] <=> (int)$b['id']); });

    $validos = array_map('intval', array_column($planes, 'id'));
    $pedidos = isset($_GET['p']) && is_array($_GET['p']) ? array_map('intval', $_GET['p']) : [];
    $seleccion = array_values(array_intersect($validos, $pedidos));
    $seleccion = array_slice($seleccion, 0, 8);

    foreach ($planes as $pl) {
        if (!in_array((int)$pl['id'], $seleccion, true)) continue;
        $inf = $modelo->getInformeCompleto((int)$pl['id']);
        $lineas = [];
        foreach (($inf['lineas'] ?? []) as $l) $lineas[$l['codigo'] !== '—' ? $l['codigo'] : $l['nombre']] = $l;
        $comparativo[] = [
            'id' => (int)$pl['id'], 'titulo' => $pl['titulo'], 'anio' => (int)$pl['anio'],
            'global' => (float)($inf['global_cumplimiento'] ?? 0),
            'metas' => (int)($inf['metas_total'] ?? 0),
            'reportados' => (int)($inf['reportados'] ?? 0),
            'trim' => (int)($inf['trimestre_reporte'] ?? 0),
            'lineas' => $lineas,
        ];
    }
}

// Filas del comparativo: todas las líneas que aparezcan en algún plan
$filasLinea = [];
foreach ($comparativo as $c) foreach ($c['lineas'] as $k => $l) $filasLinea[$k] = $l['titulo'];
uksort($filasLinea, 'strnatcasecmp');

ob_start();
?>
<style>
    .asi-vamos { max-width: 1100px; margin: 0 auto; padding: 1.5rem 1rem 3rem; color: var(--color-texto, #1d1d1f); }
    .asi-vamos h1 { font-size: 2rem; font-weight: 800; margin: 0 0 .25rem; letter-spacing: -0.02em; }
    .asi-vamos .sub { color: #86868b; margin: 0 0 1.5rem; }
    .asi-vamos .caja { background: #fff; border: 1px solid #d2d2d7; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 6px rgba(0,0,0,.05); }
    .asi-vamos .caja h2 { font-size: 1.05rem; margin: 0 0 .75rem; }
    .asi-vamos .lista { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: .5rem; }
    .asi-vamos .lista label { display: flex; gap: .6rem; align-items: center; padding: .55rem .75rem; border: 1px solid #d2d2d7; border-radius: 8px; cursor: pointer; }
    .asi-vamos .lista label:hover { border-color: #0071e3; background: #f5faff; }
    .asi-vamos .lista input { width: 1.1rem; height: 1.1rem; }
    .asi-vamos .lista small { color: #86868b; display: block; }
    .asi-vamos .acciones { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: 1rem; }
    .asi-vamos .btn { border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .5rem 1rem; cursor: pointer; font-size: .9rem; }
    .asi-vamos .btn.primario { background: #0071e3; border-color: #0071e3; color: #fff; font-weight: 600; }
    .asi-vamos table { width: 100%; border-collapse: collapse; font-size: .9rem; }
    .asi-vamos th, .asi-vamos td { padding: .55rem .6rem; border-bottom: 1px solid #ececf0; text-align: center; }
    .asi-vamos th:first-child, .asi-vamos td:first-child { text-align: left; }
    .asi-vamos thead th { background: #f5f5f7; font-weight: 700; }
    .asi-vamos tr.resumen td { font-weight: 700; background: #fafafc; }
    .asi-vamos .barra { height: 6px; background: #ececf0; border-radius: 3px; margin-top: 4px; overflow: hidden; }
    .asi-vamos .barra i { display: block; height: 100%; }
    .asi-vamos .ok { color: #1f8a3d; } .asi-vamos .warn { color: #b26a00; } .asi-vamos .bad { color: #d70015; }
    .asi-vamos .barra .ok { background: #1f8a3d; } .asi-vamos .barra .warn { background: #e0a100; } .asi-vamos .barra .bad { background: #d70015; }
    .asi-vamos .delta { display: block; font-size: .75rem; color: #86868b; }
    .asi-vamos .tabla-scroll { overflow-x: auto; }
    @media print { .asi-vamos .caja.filtro, .asi-vamos .acciones { display: none; } }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<section class="asi-vamos">
    <h1>Así vamos</h1>
    <p class="sub">Marca los Planes de Acción que quieres comparar.</p>

<?php if (!$veTodo): ?>
    <div class="caja">El comparativo entre planes está disponible para los administradores.</div>
<?php elseif (!$planes): ?>
    <div class="caja">Todavía no hay Planes de Acción en el sistema.</div>
<?php else: ?>
    <form method="get" action="<?php echo $basePath; ?>/asivamos" class="caja filtro">
        <h2>Planes de Acción (<?php echo count($planes); ?>)</h2>
        <ul class="lista">
            <?php foreach ($planes as $pl): ?>
                <li>
                    <label>
                        <input type="checkbox" name="p[]" value="<?php echo (int)$pl['id']; ?>" <?php echo in_array((int)$pl['id'], $seleccion, true) ? 'checked' : ''; ?>>
                        <span><?php echo $h($pl['titulo']); ?><small>Año <?php echo (int)$pl['anio']; ?></small></span>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="acciones">
            <button type="submit" class="btn primario">Generar comparativo</button>
            <button type="button" class="btn" onclick="document.querySelectorAll('.asi-vamos .lista input').forEach(function(c){c.checked=true;})">Marcar todos</button>
            <button type="button" class="btn" onclick="document.querySelectorAll('.asi-vamos .lista input').forEach(function(c){c.checked=false;})">Quitar todos</button>
            <?php if ($comparativo): ?><button type="button" class="btn" onclick="window.print()">Imprimir</button><?php endif; ?>
        </div>
        <small style="color:#86868b;display:block;margin-top:.5rem;">Máximo 8 planes a la vez.</small>
    </form>

    <?php if ($comparativo): ?>
    <div class="caja">
        <h2>Comparativo</h2>
        <div class="tabla-scroll">
        <table>
            <thead>
                <tr>
                    <th>Indicador</th>
                    <?php foreach ($comparativo as $c): ?><th><?php echo $h($c['titulo']); ?><span class="delta">Año <?php echo $c['anio']; ?></span></th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr class="resumen">
                    <td>Cumplimiento global</td>
                    <?php $prev = null; foreach ($comparativo as $c): ?>
                        <td>
                            <span class="<?php echo $tono($c['global']); ?>"><?php echo $fmt($c['global']); ?>%</span>
                            <?php if ($prev !== null): $d = $c['global'] - $prev; ?><span class="delta"><?php echo ($d >= 0 ? '+' : '') . $fmt($d); ?> pts vs. anterior</span><?php endif; ?>
                            <div class="barra"><i class="<?php echo $tono($c['global']); ?>" style="width:<?php echo min(100, max(0, $c['global'])); ?>%"></i></div>
                        </td>
                    <?php $prev = $c['global']; endforeach; ?>
                </tr>
                <tr>
                    <td>Indicadores</td>
                    <?php foreach ($comparativo as $c): ?><td><?php echo $c['metas']; ?></td><?php endforeach; ?>
                </tr>
                <tr>
                    <td>Reportados (trimestre vigente aprobado)</td>
                    <?php foreach ($comparativo as $c): ?><td><?php echo $c['reportados']; ?> de <?php echo $c['metas']; ?><span class="delta">T<?php echo $c['trim'] ?: 1; ?></span></td><?php endforeach; ?>
                </tr>
                <?php foreach ($filasLinea as $k => $nombre): ?>
                <tr>
                    <td><?php echo $h($nombre); ?></td>
                    <?php foreach ($comparativo as $c): ?>
                        <?php if (isset($c['lineas'][$k])): $v = (float)$c['lineas'][$k]['cumplimiento']; ?>
                            <td><span class="<?php echo $tono($v); ?>"><?php echo $fmt($v); ?>%</span>
                                <div class="barra"><i class="<?php echo $tono($v); ?>" style="width:<?php echo min(100, max(0, $v)); ?>%"></i></div></td>
                        <?php else: ?><td>—</td><?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>
</section>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
