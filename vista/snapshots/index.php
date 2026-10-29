<?php
// vista/snapshots/index.php
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Snapshot';
$paginaActual = 'snapshots';
$baseUrl      = Config::getBaseUrl();
$basePath     = Config::getBasePath();

$snapshots = $snapshots ?? [];

function snap_tamano($bytes) {
    $bytes = (float)$bytes;
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
    if ($bytes >= 1024)    return number_format($bytes / 1024, 1, ',', '.') . ' KB';
    return (int)$bytes . ' B';
}

ob_start();
?>
<style>
    .snap {
        --ios-blue: #007AFF; --ios-green: #34C759; --ios-red: #FF3B30; --ios-purple: #5856D6;
        --ios-bg: #F2F2F7; --ios-surface: #FFFFFF; --ios-label: #1D1D1F; --ios-label2: rgba(60,60,67,.6);
        --ios-sep: rgba(60,60,67,.12);
        font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
        max-width: 1100px; margin: 0 auto; padding: 8px 4px 28px; color: var(--ios-label);
    }
    .snap-header {
        background: linear-gradient(135deg, #0A84FF 0%, #5856D6 100%);
        color: #fff; border-radius: 20px; padding: 26px 30px; margin-bottom: 24px;
        display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
        box-shadow: 0 6px 24px rgba(88,86,214,.3);
    }
    .snap-header-left { display: flex; align-items: center; gap: 16px; }
    .snap-header-icon { width: 54px; height: 54px; border-radius: 16px; background: rgba(255,255,255,.2); display: grid; place-items: center; font-size: 24px; }
    .snap-header h1 { margin: 0; font-size: 24px; font-weight: 700; color: #fff; }
    .snap-header p { margin: 4px 0 0; opacity: .9; font-size: 14px; }
    .snap-volver { color: #fff !important; text-decoration: none; background: rgba(255,255,255,.18); padding: 9px 16px; border-radius: 12px; font-weight: 600; font-size: 14px; }
    .snap-volver:hover { background: rgba(255,255,255,.28); }

    .snap-card { background: var(--ios-surface); border-radius: 18px; box-shadow: 0 2px 12px rgba(0,0,0,.07); padding: 22px 24px; margin-bottom: 20px; }
    .snap-card h2 { font-size: 17px; font-weight: 700; margin: 0 0 4px; }
    .snap-card .ayuda { font-size: 13px; color: var(--ios-label2); margin: 0 0 16px; }
    .snap-form { display: flex; gap: 10px; flex-wrap: wrap; }
    .snap-form input {
        flex: 1 1 260px; border: 1.5px solid var(--ios-sep); border-radius: 12px; padding: 10px 14px;
        font-size: 15px; background: var(--ios-bg); outline: none;
    }
    .snap-form input:focus { border-color: var(--ios-blue); background: #fff; box-shadow: 0 0 0 3px rgba(0,122,255,.15); }
    .snap-sel { border: 1.5px solid var(--ios-sep); border-radius: 12px; padding: 10px 12px; font-size: 14px; background: var(--ios-bg); max-width: 220px; }
    .snap-asig { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; margin: 0; }
    .snap-asig .snap-sel { padding: 6px 8px; font-size: 13px; border-radius: 9px; max-width: 150px; }
    .snap-tri { display: inline-block; font-size: 12px; font-weight: 700; padding: 2px 10px; border-radius: 99px; background: rgba(52,199,89,.14); color: #1E7B34; margin-bottom: 4px; }
    .snap-btn { border: 0; border-radius: 12px; padding: 10px 20px; font-size: 15px; font-weight: 600; background: var(--ios-blue); color: #fff; cursor: pointer; }
    .snap-btn:hover { background: #0066CC; }
    .snap-btn:disabled { opacity: .6; cursor: wait; }

    .snap-msg { padding: 12px 16px; border-radius: 12px; font-size: 14px; margin-bottom: 18px; }
    .snap-msg.ok { background: rgba(52,199,89,.14); color: #1E7B34; }
    .snap-msg.error { background: rgba(255,59,48,.1); color: #C0392B; }

    .snap-table { width: 100%; border-collapse: collapse; }
    .snap-table th { text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--ios-label2); padding: 10px 12px; border-bottom: 1px solid var(--ios-sep); white-space: nowrap; }
    .snap-table td { padding: 13px 12px; border-bottom: 1px solid var(--ios-sep); font-size: 14px; vertical-align: middle; }
    .snap-table tr:last-child td { border-bottom: 0; }
    .snap-table .nombre { font-weight: 600; }
    .snap-table .sub { color: var(--ios-label2); font-size: 13px; }
    .snap-table .num { font-variant-numeric: tabular-nums; }
    .snap-acciones { display: flex; gap: 6px; white-space: nowrap; }
    .snap-accion { border: 0; border-radius: 9px; padding: 6px 12px; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
    .snap-accion.descargar { background: rgba(0,122,255,.1); color: var(--ios-blue); }
    .snap-accion.eliminar { background: rgba(255,59,48,.1); color: var(--ios-red); }
    .snap-accion:hover { filter: brightness(.95); }
    .snap-vacio { text-align: center; padding: 40px 20px; color: var(--ios-label2); }
    .snap-vacio i { font-size: 40px; opacity: .4; display: block; margin-bottom: 12px; }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<div class="snap">
    <div class="snap-header">
        <div class="snap-header-left">
            <div class="snap-header-icon"><i class="fas fa-camera"></i></div>
            <div>
                <h1>Snapshot</h1>
                <p>Copias completas de la base de datos tomadas en un momento dado</p>
            </div>
        </div>
        <a class="snap-volver" href="<?php echo $basePath; ?>/configuraciones"><i class="fas fa-arrow-left me-1"></i> Volver</a>
    </div>

    <?php if (!empty($error)): ?><div class="snap-msg error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if (!empty($exito)): ?><div class="snap-msg ok"><?php echo htmlspecialchars($exito); ?></div><?php endif; ?>

    <div class="snap-card">
        <h2>Crear snapshot</h2>
        <p class="ayuda">Guarda una copia de todas las tablas tal como están ahora (formularios, indicadores, seguimiento, usuarios, configuraciones…). Los cambios posteriores no la afectan.</p>
        <form method="POST" action="<?php echo $basePath; ?>/snapshots" class="snap-form" id="snapFormCrear">
            <input type="hidden" name="accion" value="crear">
            <input type="text" name="nombre" maxlength="150" placeholder="Nombre (opcional). Ej: Cierre formulación 2026">
            <?php if (!empty($formularios)): ?>
            <select name="formulario_id" aria-label="Plan" class="snap-sel">
                <?php foreach ($formularios as $f): ?><option value="<?php echo (int)$f['id']; ?>"><?php echo htmlspecialchars($f['titulo']); ?></option><?php endforeach; ?>
            </select>
            <select name="trimestre" aria-label="Trimestre al que corresponde" class="snap-sel">
                <option value="0">Sin trimestre</option>
                <option value="1">Trimestre 1</option><option value="2">Trimestre 2</option><option value="3">Trimestre 3</option><option value="4">Trimestre 4</option>
            </select>
            <?php endif; ?>
            <button type="submit" class="snap-btn" id="snapBtnCrear"><i class="fas fa-camera me-1"></i> Tomar snapshot</button>
        </form>
    </div>

    <div class="snap-card">
        <h2>Snapshots guardados</h2>
        <p class="ayuda"><?php echo count($snapshots); ?> snapshot<?php echo count($snapshots) === 1 ? '' : 's'; ?>. Descárgalos en JSON para guardarlos fuera del sistema.</p>
        <?php if (empty($snapshots)): ?>
        <div class="snap-vacio"><i class="fas fa-camera"></i>Aún no hay snapshots. Toma el primero arriba.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="snap-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Fecha</th>
                        <th>Creado por</th>
                        <th>Trimestre</th>
                        <th>Tablas</th>
                        <th>Registros</th>
                        <th>Tamaño</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($snapshots as $s): ?>
                    <tr>
                        <td class="nombre"><?php echo htmlspecialchars($s['nombre']); ?></td>
                        <td class="sub"><?php echo date('d/m/Y H:i', strtotime($s['creado_en'])); ?></td>
                        <td class="sub"><?php echo htmlspecialchars($s['creado_por_nombre'] ?? '—'); ?></td>
                        <td>
                            <?php if (!empty($s['trimestre'])): ?>
                            <div class="snap-tri">T<?php echo (int)$s['trimestre']; ?> · <?php echo htmlspecialchars($s['formulario_titulo'] ?? ''); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($formularios)): ?>
                            <form method="POST" action="<?php echo $basePath; ?>/snapshots" class="snap-asig">
                                <input type="hidden" name="accion" value="asignar">
                                <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                                <select name="formulario_id" class="snap-sel" aria-label="Plan">
                                    <?php foreach ($formularios as $f): ?><option value="<?php echo (int)$f['id']; ?>" <?php echo (int)($s['formulario_id'] ?? 0) === (int)$f['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['titulo']); ?></option><?php endforeach; ?>
                                </select>
                                <select name="trimestre" class="snap-sel" aria-label="Trimestre">
                                    <option value="0">Sin trimestre</option>
                                    <?php for ($t = 1; $t <= 4; $t++): ?><option value="<?php echo $t; ?>" <?php echo (int)($s['trimestre'] ?? 0) === $t ? 'selected' : ''; ?>>Trimestre <?php echo $t; ?></option><?php endfor; ?>
                                </select>
                                <button type="submit" class="snap-accion descargar">Asignar</button>
                            </form>
                            <?php endif; ?>
                        </td>
                        <td class="num"><?php echo (int)$s['total_tablas']; ?></td>
                        <td class="num"><?php echo number_format((int)$s['total_filas'], 0, ',', '.'); ?></td>
                        <td class="num sub"><?php echo snap_tamano($s['bytes']); ?></td>
                        <td>
                            <div class="snap-acciones">
                                <a class="snap-accion descargar" href="<?php echo $basePath; ?>/snapshots/descargar/<?php echo (int)$s['id']; ?>">
                                    <i class="fas fa-download"></i> Descargar
                                </a>
                                <form method="POST" action="<?php echo $basePath; ?>/snapshots" class="snap-form-eliminar" style="margin:0">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                                    <button type="submit" class="snap-accion eliminar"><i class="fas fa-trash"></i> Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('snapFormCrear').addEventListener('submit', function () {
    var boton = document.getElementById('snapBtnCrear');
    boton.disabled = true;
    boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Tomando snapshot…';
});
document.querySelectorAll('.snap-form-eliminar').forEach(function (formulario) {
    formulario.addEventListener('submit', function (evento) {
        if (!confirm('¿Eliminar este snapshot? Esta acción no se puede deshacer.')) evento.preventDefault();
    });
});
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
