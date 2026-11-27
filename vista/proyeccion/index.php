<?php
// vista/proyeccion/index.php
// "Proyección de metas": crear una proyección a partir de Planes de Acción anteriores y ver las ya generadas.
// El detalle (tabla con el formato del FOR-DE-144) está en vista/proyeccion/ver.php (/proyeccion/ver?id=).
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../modelo/FORDE144Model.php';
require_once __DIR__ . '/../../modelo/ProyeccionModel.php';

$titulo       = 'Proyección de metas';
$paginaActual = 'proyeccion';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

// Quién puede ver las cifras de toda la institución (mismo criterio del dashboard)
$uid     = (int)($_SESSION['usuario_id'] ?? 0);
$rolNorm = strtr(mb_strtolower(trim((string)($_SESSION['usuario_rol'] ?? '')), 'UTF-8'), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
$veTodo  = $uid === 1 || in_array($rolNorm, ['admin', 'administrador', 'sub administrador'], true);

$planes = $guardados = [];
$edit = null;
$aviso = '';
$anioDefecto = (int)date('Y');
if ($veTodo) {
    $forde = new FORDE144Model();
    $pry   = new ProyeccionModel();
    $planes = $forde->getAllAdmin();
    usort($planes, function ($a, $b) { return ((int)$a['anio'] <=> (int)$b['anio']) ?: ((int)$a['id'] <=> (int)$b['id']); });
    $tituloPlan = [];
    foreach ($planes as $pl) $tituloPlan[(int)$pl['id']] = ['titulo' => $pl['titulo'], 'anio' => (int)$pl['anio']];

    $accion = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['accion'] ?? '') : '';
    if ($accion === 'crear' || $accion === 'editar') {
        $editId = $accion === 'editar' ? (int)($_POST['id'] ?? 0) : 0;
        $ids = array_values(array_intersect(array_keys($tituloPlan), array_map('intval', (array)($_POST['p'] ?? []))));
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $anioObj = (int)($_POST['anio'] ?? $anioDefecto);
        if ($anioObj < 2000 || $anioObj > 2100) $anioObj = $anioDefecto;
        // Solo años anteriores al que se proyecta
        $ids = array_values(array_filter($ids, function ($i) use ($tituloPlan, $anioObj) { return $tituloPlan[$i]['anio'] < $anioObj; }));
        $ids = array_slice($ids, 0, 8);
        if (count($ids) < 1) {
            $aviso = 'Marca al menos un año histórico anterior al año a proyectar.';
        } else {
            if ($nombre === '') $nombre = 'Proyección ' . $anioObj;
            $metodo = (string)($_POST['metodo'] ?? 'prom');
            $ajuste = (float)str_replace(',', '.', (string)($_POST['ajuste'] ?? 0));
            if ($editId) {
                $pry->editar($editId, $nombre, $ids, $anioObj, $metodo, $ajuste);
                header('Location: ' . $basePath . '/proyeccion');
            } else {
                $nuevo = $pry->crear($nombre, $ids, $anioObj, $metodo, $ajuste, $uid);
                header('Location: ' . $basePath . '/proyeccion/ver?id=' . $nuevo);
            }
            exit;
        }
    } elseif ($accion === 'eliminar') {
        $pry->eliminar((int)($_POST['id'] ?? 0));
        header('Location: ' . $basePath . '/proyeccion');
        exit;
    }
    $guardados = $pry->listar();
    // Proyección que se está editando (si el guardado falló, se conserva la que venía en el formulario)
    $idEdit = (int)($_GET['editar'] ?? (($_POST['accion'] ?? '') === 'editar' ? ($_POST['id'] ?? 0) : 0));
    if ($idEdit) $edit = $pry->obtener($idEdit);
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
    .av label.et { display: block; font-size: .8rem; font-weight: 600; color: #424245; margin: 0 0 .3rem; }
    .av .campo { width: 100%; padding: .55rem .8rem; border: 1px solid #d2d2d7; border-radius: 8px; font-size: .95rem; background: #fff; }
    .av .campo:focus { outline: 2px solid #0071e3; border-color: transparent; }
    .av .fila2 { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin: .7rem 0 0; }
    .av .paso { display: flex; gap: .9rem; padding: .9rem 0; border-bottom: 1px solid #f0f0f3; }
    .av .paso:last-of-type { border-bottom: 0; }
    .av .paso .num { flex: none; width: 28px; height: 28px; border-radius: 50%; background: #0071e3; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: .85rem; }
    .av .paso .cont { flex: 1; min-width: 0; }
    .av .campo.grande { width: 140px; font-size: 1.3rem; font-weight: 700; padding: .45rem .7rem; }
    .av .ayuda { display: block; color: #86868b; font-size: .75rem; margin-top: .45rem; }
    .av details.avanz { margin-top: .7rem; font-size: .82rem; }
    .av details.avanz summary { cursor: pointer; color: #0071e3; }
    .av .chips { display: flex; flex-wrap: wrap; gap: .5rem; }
    .av .chip { position: relative; }
    .av .chip .tip { position: absolute; left: 0; bottom: calc(100% + 8px); z-index: 50; width: max-content; max-width: 280px; white-space: normal; text-align: left;
        background: #1d1d1f; color: #fff; border: 0; border-radius: 10px; padding: .55rem .75rem; font-size: .76rem; font-weight: 400; line-height: 1.35; text-transform: none; letter-spacing: 0;
        box-shadow: 0 8px 24px rgba(0,0,0,.25); opacity: 0; visibility: hidden; transform: translateY(4px); transition: opacity .15s ease, transform .15s ease, visibility 0s linear .15s; pointer-events: none; }
    .av .chip .tip strong { display: block; font-size: .8rem; margin-bottom: .2rem; }
    .av .chip .tip em { display: block; font-style: normal; color: #a1a1a6; margin-top: .25rem; font-size: .7rem; }
    .av .chip .tip::after { content: ''; position: absolute; top: 100%; left: 18px; border: 6px solid transparent; border-top-color: #1d1d1f; }
    .av .chip:hover .tip, .av .chip:focus-within .tip { opacity: 1; visibility: visible; transform: translateY(0); transition: opacity .15s ease .1s, transform .15s ease .1s; }
    .av .chip input { position: absolute; opacity: 0; pointer-events: none; }
    .av .chip input + span { display: block; padding: .5rem .85rem; border: 1px solid #d2d2d7; border-radius: 999px; cursor: pointer; font-size: .88rem; background: #fff; user-select: none; }
    .av .chip input + span b { font-size: 1rem; margin-right: .3rem; }
    .av .chip input:checked + span { background: #0071e3; border-color: #0071e3; color: #fff; }
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
    .av .tarjeta .anios { display: flex; gap: .25rem; flex-wrap: wrap; margin: .25rem 0; align-items: center; }
    .av .tarjeta .anios i { font-style: normal; font-size: .72rem; background: #e8f2ff; color: #0058b0; padding: .1rem .5rem; border-radius: 999px; }
    .av .tarjeta .anios em { font-style: normal; font-size: .72rem; background: #e3f8e9; color: #1f8a3d; padding: .1rem .5rem; border-radius: 999px; font-weight: 700; }
    .av .tarjeta small { color: #86868b; }
    .av .icono { display: inline-flex; border: 1px solid #d2d2d7; background: #fff; border-radius: 8px; padding: .4rem .55rem; cursor: pointer; color: inherit; text-decoration: none; }
    .av .icono:hover { border-color: #0071e3; color: #0071e3; }
    .av .icono.peligro:hover { border-color: #d70015; color: #d70015; }
    .av .vacio { color: #86868b; margin: 0; }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<section class="av">
    <h1>Proyección de metas</h1>
    <p class="sub">Toma lo formulado y lo logrado en años anteriores y propone la meta de cada indicador para el año que eliges.</p>

<?php if (!$veTodo): ?>
    <div class="caja">La proyección de metas está disponible para los administradores.</div>
<?php elseif (!$planes): ?>
    <div class="caja">Todavía no hay Planes de Acción en el sistema.</div>
<?php else: ?>
    <?php if ($aviso): ?><div class="caja error"><?php echo $h($aviso); ?></div><?php endif; ?>
    <div class="rejilla">
        <form method="post" action="<?php echo $basePath; ?>/proyeccion" class="caja" id="formNuevo">
            <input type="hidden" name="accion" value="<?php echo $edit ? 'editar' : 'crear'; ?>">
            <?php if ($edit): ?><input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>"><?php endif; ?>
            <h2><?php echo $edit ? 'Editar proyección' : 'Nueva proyección'; ?></h2>

            <div class="paso"><span class="num">1</span>
                <div class="cont">
                    <label class="et" for="anio">¿Qué año quieres proyectar?</label>
                    <input class="campo grande" id="anio" type="number" name="anio" min="2000" max="2100" value="<?php echo $edit ? (int)$edit['anio_objetivo'] : $anioDefecto; ?>" required>
                    <small class="ayuda">Es el año para el que se van a proponer y decidir las metas.</small>
                </div>
            </div>

            <div class="paso"><span class="num">2</span>
                <div class="cont">
                    <label class="et">¿Qué años históricos usarás como base?</label>
                    <div class="chips">
                        <?php foreach ($planes as $pl): ?>
                            <label class="chip">
                                <input type="checkbox" name="p[]" value="<?php echo (int)$pl['id']; ?>" <?php echo $edit && in_array((int)$pl['id'], $edit['ids'], true) ? 'checked' : ''; ?>>
                                <span><b><?php echo (int)$pl['anio']; ?></b><?php echo $h(mb_strimwidth($pl['titulo'], 0, 22, '…', 'UTF-8')); ?></span>
                                <span class="tip" role="tooltip"><strong><?php echo $h($pl['titulo']); ?></strong><?php echo $h(trim((string)($pl['descripcion'] ?? '')) ?: 'Sin descripción.'); ?><em>Año <?php echo (int)$pl['anio']; ?></em></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small class="ayuda">De aquí se toma lo formulado y lo logrado. Solo se usan años anteriores al que proyectas.</small>
                </div>
            </div>

            <div class="paso"><span class="num">3</span>
                <div class="cont">
                    <label class="et" for="nombre">Nombre <span style="font-weight:400;color:#86868b">(opcional)</span></label>
                    <input class="campo" id="nombre" type="text" name="nombre" maxlength="150" placeholder="Ej. Metas 2027 con base 2022-2025" value="<?php echo $edit ? $h($edit['nombre']) : ''; ?>">
                    <details class="avanz" <?php echo $edit ? 'open' : ''; ?>><summary>Cómo se calcula la propuesta</summary>
                        <div class="fila2">
                            <div><label class="et" for="metodo">Método</label>
                                <select class="campo" id="metodo" name="metodo"><?php foreach (ProyeccionModel::METODOS as $k => $v): ?><option value="<?php echo $k; ?>" <?php echo $edit && $edit['metodo'] === $k ? 'selected' : ''; ?>><?php echo $h($v); ?></option><?php endforeach; ?></select></div>
                            <div><label class="et" for="ajuste">Ajuste (%)</label>
                                <input class="campo" id="ajuste" type="number" step="0.5" name="ajuste" value="<?php echo $edit ? (float)$edit['ajuste'] : 0; ?>"></div>
                        </div>
                    </details>
                </div>
            </div>

            <div class="pie">
                <button type="submit" class="btn primario" id="btnGenerar" disabled><?php echo $edit ? 'Guardar cambios' : 'Generar proyección'; ?></button>
                <?php if ($edit): ?><a class="btn" href="<?php echo $basePath; ?>/proyeccion" style="text-decoration:none;color:inherit;">Cancelar</a><?php endif; ?>
                <button type="button" class="enlace" id="btnTodos">Marcar todos</button>
                <button type="button" class="enlace" id="btnNinguno">Quitar todos</button>
                <span class="contador" id="contador">0 años seleccionados</span>
            </div>
        </form>

        <div class="caja">
            <h2>Proyecciones generadas <small><?php echo count($guardados); ?></small></h2>
            <?php if (!$guardados): ?>
                <p class="vacio">Aún no has generado ninguna.</p>
            <?php else: foreach ($guardados as $g):
                $anios = [];
                foreach ((array)json_decode($g['formularios'], true) as $fid) if (isset($tituloPlan[(int)$fid])) $anios[] = $tituloPlan[(int)$fid]['anio'];
                sort($anios);
            ?>
                <div class="tarjeta">
                    <div class="info">
                        <strong><?php echo $h($g['nombre']); ?></strong>
                        <div class="anios">
                            <?php foreach ($anios as $a): ?><i><?php echo (int)$a; ?></i><?php endforeach; ?>
                            <span>→</span><em><?php echo (int)$g['anio_objetivo']; ?></em>
                        </div>
                        <small><?php echo $h(ProyeccionModel::METODOS[$g['metodo']] ?? ''); ?> · <?php echo $h(date('d/m/Y H:i', strtotime($g['fecha_creacion']))); ?></small>
                    </div>
                    <a class="icono" href="<?php echo $basePath; ?>/proyeccion/ver?id=<?php echo (int)$g['id']; ?>" title="Ver" aria-label="Ver">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </a>
                    <a class="icono" href="<?php echo $basePath; ?>/proyeccion?editar=<?php echo (int)$g['id']; ?>" title="Editar" aria-label="Editar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path></svg>
                    </a>
                    <form method="post" action="<?php echo $basePath; ?>/proyeccion" onsubmit="return confirm('¿Eliminar esta proyección?');">
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
        cont.textContent = n + (n === 1 ? ' año seleccionado' : ' años seleccionados');
        btn.disabled = n < 1;
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
