<?php
// vista/asivamos/index.php
// "Así vamos": crear un comparativo entre Planes de Acción y ver la lista de los ya generados.
// El detalle de cada comparativo está en vista/asivamos/ver.php (/asivamos/ver?id=).
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../modelo/FORDE144Model.php';
require_once __DIR__ . '/../../modelo/ComparativoModel.php';

$titulo       = 'Así vamos';
$paginaActual = 'asivamos';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

// Quién puede ver las cifras de toda la institución (mismo criterio del dashboard)
$uid     = (int)($_SESSION['usuario_id'] ?? 0);
$rolNorm = strtr(mb_strtolower(trim((string)($_SESSION['usuario_rol'] ?? '')), 'UTF-8'), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
$veTodo  = $uid === 1 || in_array($rolNorm, ['admin', 'administrador', 'sub administrador'], true);

$planes = $guardados = [];
$aviso = '';
if ($veTodo) {
    $forde = new FORDE144Model();
    $cmp   = new ComparativoModel();
    $planes = $forde->getAllAdmin();
    usort($planes, function ($a, $b) { return ((int)$a['anio'] <=> (int)$b['anio']) ?: ((int)$a['id'] <=> (int)$b['id']); });
    $tituloPlan = [];
    foreach ($planes as $pl) $tituloPlan[(int)$pl['id']] = ['titulo' => $pl['titulo'], 'anio' => (int)$pl['anio']];

    $accion = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['accion'] ?? '') : '';
    if ($accion === 'crear') {
        $validos = array_keys($tituloPlan);
        $ids = array_values(array_intersect($validos, array_map('intval', (array)($_POST['p'] ?? []))));
        $ids = array_slice($ids, 0, 8);
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        if (count($ids) < 2) {
            $aviso = 'Marca al menos dos planes para comparar.';
        } else {
            if ($nombre === '') $nombre = 'Comparativo ' . date('d/m/Y H:i');
            $nuevo = $cmp->crear($nombre, $ids, $uid);
            header('Location: ' . $basePath . '/asivamos/ver?id=' . $nuevo);
            exit;
        }
    } elseif ($accion === 'eliminar') {
        $cmp->eliminar((int)($_POST['id'] ?? 0));
        header('Location: ' . $basePath . '/asivamos');
        exit;
    }
    $guardados = $cmp->listar();
}

ob_start();
?>
<style>
    .av { max-width: 1180px; margin: 0 auto; padding: 1.5rem 1rem 3rem; color: #1d1d1f; }
    .av h1 { font-size: 2rem; font-weight: 800; margin: 0 0 .25rem; letter-spacing: -.02em; }
    .av .sub { color: #86868b; margin: 0 0 1.5rem; }
    .av .rejilla { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); gap: 1.25rem; align-items: start; }
    @media (max-width: 900px) { .av .rejilla { grid-template-columns: 1fr; } }
    .av .caja { background: #fff; border: 1px solid #d2d2d7; border-radius: 12px; padding: 1.1rem 1.25rem; box-shadow: 0 1px 6px rgba(0,0,0,.05); }
    .av .caja h2 { font-size: 1rem; margin: 0 0 .85rem; display: flex; justify-content: space-between; align-items: center; }
    .av .caja h2 small { color: #86868b; font-weight: 500; }
    .av .campo { width: 100%; padding: .6rem .8rem; border: 1px solid #d2d2d7; border-radius: 8px; font-size: .95rem; margin-bottom: .9rem; }
    .av .campo:focus { outline: 2px solid #0071e3; border-color: transparent; }
    .av .chips { display: flex; flex-wrap: wrap; gap: .5rem; }
    .av .chip input { position: absolute; opacity: 0; pointer-events: none; }
    .av .chip span { display: block; padding: .5rem .85rem; border: 1px solid #d2d2d7; border-radius: 999px; cursor: pointer; font-size: .88rem; background: #fff; user-select: none; }
    .av .chip span b { font-size: 1rem; margin-right: .3rem; }
    .av .chip span small { color: #86868b; margin-left: .35rem; }
    .av .chip input:checked + span { background: #0071e3; border-color: #0071e3; color: #fff; }
    .av .chip input:checked + span small { color: #d6e9ff; }
    .av .chip input:focus-visible + span { outline: 2px solid #0071e3; outline-offset: 2px; }
    .av .pie { display: flex; gap: .6rem; align-items: center; margin-top: 1rem; flex-wrap: wrap; }
    .av .btn { border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .5rem 1rem; cursor: pointer; font-size: .9rem; }
    .av .btn.primario { background: #0071e3; border-color: #0071e3; color: #fff; font-weight: 600; }
    .av .btn[disabled] { opacity: .45; cursor: not-allowed; }
    .av .enlace { background: none; border: 0; color: #0071e3; cursor: pointer; font-size: .85rem; padding: 0; }
    .av .contador { color: #86868b; font-size: .85rem; margin-left: auto; }
    .av .error { border-color: #d70015; color: #d70015; margin-bottom: 1rem; }
    .av .tarjeta { display: flex; gap: .75rem; align-items: center; padding: .8rem 0; border-bottom: 1px solid #ececf0; }
    .av .tarjeta:last-child { border-bottom: 0; }
    .av .tarjeta .info { flex: 1; min-width: 0; }
    .av .tarjeta strong { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .av .tarjeta .anios { display: flex; gap: .25rem; flex-wrap: wrap; margin: .25rem 0; }
    .av .tarjeta .anios i { font-style: normal; font-size: .72rem; background: #e8f2ff; color: #0058b0; padding: .1rem .5rem; border-radius: 999px; }
    .av .tarjeta small { color: #86868b; }
    .av .icono { display: inline-flex; border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .4rem .55rem; cursor: pointer; color: inherit; text-decoration: none; }
    .av .icono:hover { border-color: #0071e3; color: #0071e3; }
    .av .icono.peligro:hover { border-color: #d70015; color: #d70015; }
    .av .vacio { color: #86868b; margin: 0; }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<section class="av">
    <h1>Así vamos</h1>
    <p class="sub">Compara el avance de los Planes de Acción año contra año.</p>

<?php if (!$veTodo): ?>
    <div class="caja">El comparativo entre planes está disponible para los administradores.</div>
<?php elseif (!$planes): ?>
    <div class="caja">Todavía no hay Planes de Acción en el sistema.</div>
<?php else: ?>
    <?php if ($aviso): ?><div class="caja error"><?php echo $h($aviso); ?></div><?php endif; ?>
    <div class="rejilla">
        <form method="post" action="<?php echo $basePath; ?>/asivamos" class="caja" id="formNuevo">
            <input type="hidden" name="accion" value="crear">
            <h2>Nuevo comparativo <small>Mínimo 2, máximo 8</small></h2>
            <input class="campo" type="text" name="nombre" maxlength="150" placeholder="Nombre del comparativo (ej. Avance 2022-2026)">
            <div class="chips">
                <?php foreach ($planes as $pl): ?>
                    <label class="chip" title="<?php echo $h($pl['titulo']); ?>">
                        <input type="checkbox" name="p[]" value="<?php echo (int)$pl['id']; ?>">
                        <span><b><?php echo (int)$pl['anio']; ?></b><?php echo $h(mb_strimwidth($pl['titulo'], 0, 34, '…', 'UTF-8')); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="pie">
                <button type="submit" class="btn primario" id="btnGenerar" disabled>Generar comparativo</button>
                <button type="button" class="enlace" id="btnTodos">Marcar todos</button>
                <button type="button" class="enlace" id="btnNinguno">Quitar todos</button>
                <span class="contador" id="contador">0 seleccionados</span>
            </div>
        </form>

        <div class="caja">
            <h2>Comparativos generados <small><?php echo count($guardados); ?></small></h2>
            <?php if (!$guardados): ?>
                <p class="vacio">Aún no has generado ninguno.</p>
            <?php else: foreach ($guardados as $g):
                $anios = [];
                foreach ((array)json_decode($g['formularios'], true) as $fid) if (isset($tituloPlan[(int)$fid])) $anios[] = $tituloPlan[(int)$fid]['anio'];
                sort($anios);
            ?>
                <div class="tarjeta">
                    <div class="info">
                        <strong><?php echo $h($g['nombre']); ?></strong>
                        <div class="anios"><?php foreach ($anios as $a): ?><i><?php echo (int)$a; ?></i><?php endforeach; ?></div>
                        <small><?php echo $h(date('d/m/Y H:i', strtotime($g['fecha_creacion']))); ?></small>
                    </div>
                    <a class="icono" href="<?php echo $basePath; ?>/asivamos/ver?id=<?php echo (int)$g['id']; ?>" title="Ver" aria-label="Ver">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </a>
                    <form method="post" action="<?php echo $basePath; ?>/asivamos" onsubmit="return confirm('¿Eliminar este comparativo?');">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo (int)$g['id']; ?>">
                        <button type="submit" class="icono peligro" title="Eliminar" aria-label="Eliminar">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
                        </button>
                    </form>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
<?php endif; ?>
</section>

<script>
(function () {
    var cajas = document.querySelectorAll('#formNuevo input[type=checkbox]');
    if (!cajas.length) return;
    var btn = document.getElementById('btnGenerar'), cont = document.getElementById('contador');
    function actualizar() {
        var n = 0; cajas.forEach(function (c) { if (c.checked) n++; });
        if (n > 8) { n = 8; }
        cont.textContent = n + (n === 1 ? ' seleccionado' : ' seleccionados');
        btn.disabled = n < 2;
    }
    cajas.forEach(function (c) {
        c.addEventListener('change', function () {
            var marcados = Array.prototype.filter.call(cajas, function (x) { return x.checked; });
            if (marcados.length > 8) c.checked = false;
            actualizar();
        });
    });
    document.getElementById('btnTodos').addEventListener('click', function () { cajas.forEach(function (c, i) { c.checked = i < 8; }); actualizar(); });
    document.getElementById('btnNinguno').addEventListener('click', function () { cajas.forEach(function (c) { c.checked = false; }); actualizar(); });
    actualizar();
})();
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
