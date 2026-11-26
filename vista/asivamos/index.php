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
    $tituloPlan = [];
    foreach ($planes as $pl) $tituloPlan[(int)$pl['id']] = $pl['titulo'];

    // Comparativos guardados (la selección y el nombre; las cifras se calculan al abrirlos, siempre al día)
    $pdoAv = new PDO("mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET, Config::DB_USER, Config::DB_PASS);
    $pdoAv->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoAv->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdoAv->exec("CREATE TABLE IF NOT EXISTS comparativos_144 (
        id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(150) NOT NULL, formularios TEXT NOT NULL,
        creado_por INT NULL, fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $aviso = '';
    $accion = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['accion'] ?? '') : '';
    if ($accion === 'crear') {
        $ids = array_values(array_intersect($validos, array_map('intval', (array)($_POST['p'] ?? []))));
        $ids = array_slice($ids, 0, 8);
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        if (count($ids) < 2) {
            $aviso = 'Marca al menos dos planes para comparar.';
        } else {
            if ($nombre === '') $nombre = 'Comparativo ' . date('d/m/Y H:i');
            $pdoAv->prepare("INSERT INTO comparativos_144 (nombre, formularios, creado_por) VALUES (?, ?, ?)")
                  ->execute([mb_substr($nombre, 0, 150), json_encode($ids), $uid]);
            header('Location: ' . $basePath . '/asivamos?ver=' . (int)$pdoAv->lastInsertId());
            exit;
        }
    } elseif ($accion === 'eliminar') {
        $pdoAv->prepare("DELETE FROM comparativos_144 WHERE id = ?")->execute([(int)($_POST['id'] ?? 0)]);
        header('Location: ' . $basePath . '/asivamos');
        exit;
    }

    $guardados = $pdoAv->query("SELECT * FROM comparativos_144 ORDER BY fecha_creacion DESC")->fetchAll();
    $verId = (int)($_GET['ver'] ?? 0);
    $abierto = null;
    foreach ($guardados as $g) if ((int)$g['id'] === $verId) $abierto = $g;
    if ($abierto) {
        $seleccion = array_values(array_intersect($validos, array_map('intval', (array)json_decode($abierto['formularios'], true))));
    }

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
    <?php if ($aviso): ?><div class="caja" style="border-color:#d70015;color:#d70015;"><?php echo $h($aviso); ?></div><?php endif; ?>
    <form method="post" action="<?php echo $basePath; ?>/asivamos" class="caja filtro">
        <input type="hidden" name="accion" value="crear">
        <h2>Planes de Acción (<?php echo count($planes); ?>)</h2>
        <ul class="lista">
            <?php foreach ($planes as $pl): ?>
                <li>
                    <label>
                        <input type="checkbox" name="p[]" value="<?php echo (int)$pl['id']; ?>">
                        <span><?php echo $h($pl['titulo']); ?><small>Año <?php echo (int)$pl['anio']; ?></small></span>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="acciones">
            <input type="text" name="nombre" maxlength="150" placeholder="Nombre del comparativo" class="btn" style="min-width:260px;cursor:text;">
            <button type="submit" class="btn primario">Generar comparativo</button>
            <button type="button" class="btn" onclick="document.querySelectorAll('.asi-vamos .lista input').forEach(function(c){c.checked=true;})">Marcar todos</button>
            <button type="button" class="btn" onclick="document.querySelectorAll('.asi-vamos .lista input').forEach(function(c){c.checked=false;})">Quitar todos</button>
        </div>
        <small style="color:#86868b;display:block;margin-top:.5rem;">Mínimo 2 y máximo 8 planes. El comparativo queda guardado en la lista de abajo.</small>
    </form>

    <div class="caja filtro">
        <h2>Comparativos generados (<?php echo count($guardados); ?>)</h2>
        <?php if (!$guardados): ?>
            <p style="color:#86868b;margin:0;">Aún no has generado ninguno.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Nombre</th><th>Planes</th><th>Fecha</th><th style="width:110px;">Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($guardados as $g):
                $nombresPl = [];
                foreach ((array)json_decode($g['formularios'], true) as $fid) if (isset($tituloPlan[(int)$fid])) $nombresPl[] = $tituloPlan[(int)$fid];
            ?>
                <tr<?php echo $abierto && (int)$abierto['id'] === (int)$g['id'] ? ' style="background:#f5faff"' : ''; ?>>
                    <td><strong><?php echo $h($g['nombre']); ?></strong></td>
                    <td style="font-size:.8rem;color:#424245;"><?php echo $h(implode(' · ', $nombresPl)); ?></td>
                    <td style="white-space:nowrap;"><?php echo $h(date('d/m/Y H:i', strtotime($g['fecha_creacion']))); ?></td>
                    <td style="white-space:nowrap;">
                        <a href="<?php echo $basePath; ?>/asivamos?ver=<?php echo (int)$g['id']; ?>" class="btn" title="Ver" aria-label="Ver" style="display:inline-block;padding:.35rem .6rem;text-decoration:none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </a>
                        <form method="post" action="<?php echo $basePath; ?>/asivamos" style="display:inline;" onsubmit="return confirm('¿Eliminar este comparativo?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?php echo (int)$g['id']; ?>">
                            <button type="submit" class="btn" title="Eliminar" aria-label="Eliminar" style="padding:.35rem .6rem;color:#d70015;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <?php if ($comparativo): ?>
    <div class="caja">
        <h2><?php echo $h($abierto['nombre'] ?? 'Comparativo'); ?>
            <button type="button" class="btn acciones" style="float:right;" onclick="window.print()">Imprimir</button></h2>
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
