<?php
// vista/FOR-DE-144/index.php

require_once __DIR__ . '/../../config/security.php';

$titulo      = 'FOR-DE-144 — Formularios';
$paginaActual = 'FOR-DE-144';

ob_start();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --ios-blue:       #007AFF;
        --ios-green:      #34C759;
        --ios-red:        #FF3B30;
        --ios-orange:     #FF9500;
        --ios-purple:     #AF52DE;
        --ios-bg:         #F2F2F7;
        --ios-surface:    #FFFFFF;
        --ios-label:      #000000;
        --ios-label2:     rgba(60,60,67,.6);
        --ios-label3:     rgba(60,60,67,.3);
        --ios-sep:        rgba(60,60,67,.12);
        --ios-fill:       rgba(120,120,128,.12);
        --ios-fill2:      rgba(120,120,128,.06);
        --r-sm:  8px;
        --r:    12px;
        --r-lg: 16px;
        --r-xl: 20px;
    }

    /* ── Body ── */
    body {
        background: var(--ios-bg);
        font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        color: var(--ios-label);
    }

    /* ── Page wrapper ── */
    .f144-wrap {
        padding: 0 20px 56px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* ── Page header ── */
    .f144-header {
        background: var(--ios-surface);
        border-radius: var(--r-lg);
        padding: 24px 28px;
        margin-bottom: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 .5px var(--ios-sep);
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .f144-header-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--r);
        background: linear-gradient(135deg, var(--ios-blue) 0%, #5856d6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0,122,255,.3);
    }

    .f144-header-text h1 {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -.4px;
        margin: 0 0 2px;
        color: var(--ios-label);
    }

    .f144-header-text p {
        font-size: 14px;
        color: var(--ios-label2);
        margin: 0;
    }

    .f144-header-date {
        margin-left: auto;
        font-size: 13px;
        color: var(--ios-label3);
        white-space: nowrap;
    }

    /* ── List ── */
    .card-grid {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    /* ── Add row ── */
    .formulario-add {
        background: var(--ios-blue);
        border-radius: var(--r-lg);
        padding: 16px 22px;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 16px;
        cursor: pointer;
        border: none;
        color: white;
        box-shadow: 0 4px 16px rgba(0,122,255,.32);
        transition: transform .2s, box-shadow .2s;
    }

    .formulario-add:hover {
        background: #0066E0;
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(0,122,255,.42);
        color: white;
    }

    .add-icon { font-size: 26px; flex-shrink: 0; }

    .formulario-add h3 {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
        color: white;
    }

    .formulario-add p {
        font-size: 13px;
        opacity: .75;
        margin: 0;
        color: white;
    }

    /* ── Formulario row ── */
    .formulario-card {
        background: var(--ios-surface);
        border-radius: var(--r-lg);
        padding: 14px 20px;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 18px;
        box-shadow: 0 1px 4px rgba(0,0,0,.07), 0 0 0 .5px var(--ios-sep);
        border-left: 4px solid var(--ios-sep);
        transition: transform .15s, box-shadow .15s;
    }

    .formulario-card:hover {
        transform: translateX(2px);
        box-shadow: 0 4px 14px rgba(0,0,0,.1), 0 0 0 .5px var(--ios-sep);
    }

    .formulario-card.disponible    { border-left-color: var(--ios-green); }
    .formulario-card.proximamente  { border-left-color: var(--ios-orange); }
    .formulario-card.no-disponible { border-left-color: var(--ios-red); opacity: .85; }

    /* ── Row main (title + description) ── */
    .formulario-main {
        flex: 1 1 auto;
        min-width: 0;
    }

    /* ── Card title ── */
    .formulario-titulo {
        font-size: 15px;
        font-weight: 600;
        color: var(--ios-label);
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 2px;
        padding-right: 0;
        line-height: 1.35;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Card description ── */
    .formulario-descripcion {
        font-size: 13px;
        color: var(--ios-label2);
        line-height: 1.4;
        flex-grow: 0;
        margin-bottom: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Row meta (estado + fechas) ── */
    .formulario-meta {
        flex: 0 0 auto;
        min-width: 220px;
        max-width: 260px;
    }

    /* ── Time info ── */
    .tiempo-info {
        background: none;
        border-radius: 0;
        padding: 0;
        margin: 0;
        font-size: 12px;
    }

    /* ── Status pill ── */
    .badge-estado {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 7px;
    }

    .badge-disponible    { background: rgba(52,199,89,.15);  color: #1A7A35; }
    .badge-no-disponible { background: rgba(255,59,48,.12);  color: #C0392B; }
    .badge-proximamente  { background: rgba(255,149,0,.15);  color: #8B5E00; }

    /* ── Date row ── */
    .fecha-rango { display: flex; flex-direction: column; gap: 2px; margin-top: 3px; }

    .fecha-item {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--ios-label2);
        font-size: 12px;
    }

    .fecha-item i { width: 15px; color: var(--ios-blue); font-size: 11px; }

    /* ── Card footer date ── */
    .formulario-fecha {
        font-size: 12px;
        color: var(--ios-label3);
        margin: 3px 0 0;
        padding-top: 0;
        border-top: none;
    }

    /* ── Action buttons ── */
    .btn-actions {
        margin-top: 0;
        flex: 0 0 auto;
        display: flex;
        gap: 7px;
        justify-content: flex-end;
    }

    .btn-sm {
        padding: 7px 13px;
        font-size: 13px;
        font-weight: 600;
        border-radius: var(--r-sm);
        border: none;
        cursor: pointer;
        transition: opacity .15s, transform .15s;
    }

    .btn-sm:hover:not(:disabled) { opacity: .85; transform: translateY(-1px); }

    .btn-success          { background: var(--ios-green);  color: #fff; }
    .btn-warning          { background: var(--ios-orange); color: #fff; }
    .btn-danger           { background: var(--ios-red);    color: #fff; }
    .btn-success:disabled { background: rgba(52,199,89,.35); color: rgba(255,255,255,.7); cursor: not-allowed; }
    .btn-info             { background: var(--ios-blue);    color: #fff; }

    /* Bootstrap badges override */
    .badge { padding: 4px 9px; font-weight: 600; border-radius: 20px; font-size: 11px; }
    .badge.bg-success   { background: var(--ios-green)  !important; }
    .badge.bg-warning   { background: var(--ios-orange) !important; color: #fff !important; }
    .badge.bg-danger    { background: var(--ios-red)    !important; }
    .badge.bg-secondary { background: var(--ios-label2) !important; }

    /* ── Empty state ── */
    .empty-state {
        grid-column: 1 / -1;
        background: var(--ios-surface);
        border-radius: var(--r-lg);
        padding: 48px 24px;
        text-align: center;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }

    .empty-state i     { font-size: 40px; color: var(--ios-label3); margin-bottom: 14px; }
    .empty-state h5    { font-size: 17px; font-weight: 600; margin-bottom: 6px; }
    .empty-state p     { font-size: 14px; color: var(--ios-label2); margin: 0; }

    /* ── Modal ── */
    .modal-content {
        border-radius: var(--r-xl);
        border: none;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,.22);
    }

    .modal-header {
        background: var(--ios-surface);
        border-bottom: .5px solid var(--ios-sep);
        padding: 18px 20px;
    }

    .modal-title { font-size: 17px; font-weight: 600; color: var(--ios-label); }

    .modal-body {
        background: var(--ios-bg);
        padding: 20px;
    }

    .modal-footer {
        background: var(--ios-surface);
        border-top: .5px solid var(--ios-sep);
        padding: 14px 20px;
    }

    /* ── Form ── */
    .form-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--ios-label2);
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 5px;
    }

    .form-control, .form-select {
        border-radius: var(--r-sm);
        border: .5px solid rgba(60,60,67,.2);
        padding: 11px 14px;
        font-size: 15px;
        background: var(--ios-surface);
        color: var(--ios-label);
        transition: border-color .15s, box-shadow .15s;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--ios-blue);
        box-shadow: 0 0 0 3px rgba(0,122,255,.15);
        outline: none;
        background: var(--ios-surface);
    }

    /* ── Tiempo opciones (iOS list) ── */
    .tiempo-opciones {
        background: var(--ios-surface);
        border-radius: var(--r);
        border: .5px solid var(--ios-sep);
        overflow: hidden;
        padding: 0;
    }

    .tiempo-opciones > div {
        padding: 13px 16px;
        border-bottom: .5px solid var(--ios-sep);
        display: flex;
        align-items: flex-start;
        gap: 10px;
        cursor: pointer;
    }

    .tiempo-opciones > div:last-child { border-bottom: none; }

    .tiempo-opciones input[type="radio"] { margin-top: 3px; accent-color: var(--ios-blue); }

    .tiempo-opciones label { cursor: pointer; line-height: 1.4; }

    .tiempo-opt-sub { font-size: 12px; color: var(--ios-label2); display: block; margin-top: 2px; font-weight: 400; }

    /* ── Rango fechas ── */
    .rango-fechas {
        background: var(--ios-surface);
        border-radius: var(--r);
        border: .5px solid var(--ios-sep);
        padding: 16px;
        margin-top: 12px;
    }

    /* ── Alert ── */
    .alert-info {
        background: rgba(0,122,255,.08);
        border: none;
        border-radius: var(--r-sm);
        color: var(--ios-blue);
        font-size: 13px;
    }

    /* ── Modal buttons ── */
    .btn-primary {
        background: var(--ios-blue);
        border: none;
        padding: 11px 22px;
        border-radius: var(--r-sm);
        font-weight: 600;
        font-size: 15px;
        color: white;
        transition: background .15s;
    }

    .btn-primary:hover { background: #0066E0; color: white; transform: none; box-shadow: none; }

    .btn-secondary {
        background: var(--ios-fill);
        border: none;
        padding: 11px 22px;
        border-radius: var(--r-sm);
        font-weight: 600;
        font-size: 15px;
        color: var(--ios-label);
    }

    .btn-secondary:hover { background: rgba(120,120,128,.2); color: var(--ios-label); }

    /* ── Fade in ── */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .formulario-card, .formulario-add { animation: fadeInUp .25s ease-out; }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .f144-wrap      { padding: 0 12px 40px; }
        .f144-header    { flex-wrap: wrap; gap: 12px; }
        .f144-header-date { margin-left: 0; }
        .formulario-titulo { padding-right: 0; }
        .formulario-card, .formulario-add {
            flex-direction: column;
            align-items: flex-start;
        }
        .formulario-main, .formulario-meta { max-width: 100%; width: 100%; }
        .formulario-titulo, .formulario-descripcion { white-space: normal; }
        .btn-actions { width: 100%; justify-content: flex-start; flex-wrap: wrap; }
    }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<div class="f144-wrap">

    <!-- Page header -->
    <div class="f144-header">
        <div class="f144-header-icon">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="f144-header-text">
            <h1>FOR-DE-144</h1>
            <p>Crea, edita y gestiona tus formularios con control de tiempo</p>
        </div>
        <div class="f144-header-date">
            <i class="far fa-clock me-1"></i><?php echo date('d/m/Y H:i'); ?>
        </div>
    </div>

    <!-- List -->
    <div id="formulariosContainer" class="card-grid">

        <!-- Add new -->
        <?php if ($perms_f144['crear']): ?>
        <div class="formulario-add" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            <i class="fas fa-plus-circle add-icon"></i>
            <div>
                <h3>Nuevo Formulario</h3>
                <p>Toca para crear un nuevo formulario</p>
            </div>
        </div>
        <?php endif; ?>

        <?php
        if (isset($formularios) && is_array($formularios) && count($formularios) > 0):
            foreach ($formularios as $formulario):
                $fecha           = new DateTime($formulario['fecha_creacion']);
                $fechaFormateada = $fecha->format('d/m/Y H:i');
                $ahora           = new DateTime();
                $disponible      = false;
                $estadoTiempo    = 'no-disponible';
                $mensajeEstado   = '';

                if ($formulario['estado'] != 1) {
                    $estadoTiempo  = 'inactivo';
                    $mensajeEstado = 'Formulario inactivo';
                } elseif ($formulario['tipo_tiempo'] == 'libre') {
                    $disponible   = true;
                    $estadoTiempo = 'disponible';
                } elseif ($formulario['tipo_tiempo'] == 'rango') {
                    $inicio = new DateTime($formulario['fecha_inicio']);
                    $fin    = new DateTime($formulario['fecha_fin']);
                    if ($ahora < $inicio) {
                        $estadoTiempo  = 'proximamente';
                        $mensajeEstado = 'Disponible desde ' . $inicio->format('d/m/Y H:i');
                    } elseif ($ahora > $fin) {
                        $estadoTiempo  = 'finalizado';
                        $mensajeEstado = 'Finalizado el ' . $fin->format('d/m/Y H:i');
                    } else {
                        $disponible   = true;
                        $estadoTiempo = 'disponible';
                    }
                }

                $cardClass = ($estadoTiempo === 'disponible') ? 'disponible'
                           : (($estadoTiempo === 'proximamente') ? 'proximamente' : 'no-disponible');
        ?>

        <div class="formulario-card <?php echo $cardClass; ?>" id="formulario-<?php echo $formulario['id']; ?>">

            <div class="formulario-main">
                <div class="formulario-titulo">
                    <?php echo htmlspecialchars($formulario['titulo']); ?>
                    <?php if ($formulario['estado'] != 1): ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                </div>

                <div class="formulario-descripcion">
                    <?php echo htmlspecialchars($formulario['descripcion'] ?: 'Sin descripción'); ?>
                </div>
            </div>

            <div class="formulario-meta">
                <div class="tiempo-info">
                    <?php if ($formulario['estado'] != 1): ?>
                        <span class="badge-estado badge-no-disponible"><i class="fas fa-times-circle"></i> Inactivo</span>
                        <div class="fecha-item"><i class="fas fa-ban"></i> No disponible</div>

                    <?php elseif ($formulario['tipo_tiempo'] == 'libre'): ?>
                        <span class="badge-estado badge-disponible"><i class="fas fa-infinity"></i> Tiempo libre</span>
                        <div class="fecha-item"><i class="fas fa-check-circle" style="color:var(--ios-green);"></i> Siempre disponible</div>

                    <?php else: ?>
                        <?php if ($estadoTiempo == 'disponible'): ?>
                            <span class="badge-estado badge-disponible"><i class="fas fa-clock"></i> Disponible ahora</span>
                        <?php elseif ($estadoTiempo == 'proximamente'): ?>
                            <span class="badge-estado badge-proximamente"><i class="fas fa-hourglass-half"></i> Próximamente</span>
                        <?php else: ?>
                            <span class="badge-estado badge-no-disponible"><i class="fas fa-ban"></i> Finalizado</span>
                        <?php endif; ?>

                        <div class="fecha-rango">
                            <div class="fecha-item"><i class="fas fa-play-circle"></i> Inicio: <?php echo date('d/m/Y H:i', strtotime($formulario['fecha_inicio'])); ?></div>
                            <div class="fecha-item"><i class="fas fa-stop-circle"></i> Fin: <?php echo date('d/m/Y H:i', strtotime($formulario['fecha_fin'])); ?></div>
                        </div>

                        <?php if ($mensajeEstado): ?>
                            <small style="color:var(--ios-label2);display:block;margin-top:5px;">
                                <i class="fas fa-info-circle"></i> <?php echo $mensajeEstado; ?>
                            </small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="formulario-fecha">
                    <i class="far fa-clock me-1"></i><?php echo $fechaFormateada; ?>
                    <?php if (!empty($formulario['anio'])): ?>
                        &nbsp;·&nbsp;<i class="fas fa-calendar-check me-1"></i><?php echo htmlspecialchars($formulario['anio']); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="btn-actions">
                <?php if ($perms_f144['informe']): ?>
                <a class="btn btn-sm btn-info"
                   href="<?php echo Config::getBasePath(); ?>/FOR-DE-144?action=informePage&id=<?php echo $formulario['id']; ?>"
                   target="_blank">
                    <i class="fas fa-chart-bar me-1"></i>Informe
                </a>
                <?php endif; ?>
                <?php if ($perms_f144['ver']): ?>
                <button class="btn btn-sm btn-success"
                        onclick="window.location.href='<?php echo Config::getBasePath(); ?>/modulo144?id=<?php echo $formulario['id']; ?>'"
                        <?php echo !$disponible ? 'disabled' : ''; ?>>
                    <i class="fas fa-eye me-1"></i>Ver
                </button>
                <?php endif; ?>
                <?php if ($perms_f144['editar']): ?>
                <button class="btn btn-sm btn-warning" onclick="editarFormulario(<?php echo $formulario['id']; ?>)">
                    <i class="fas fa-edit me-1"></i>Editar
                </button>
                <?php endif; ?>
                <?php if ($perms_f144['eliminar']): ?>
                <button class="btn btn-sm btn-danger" onclick="eliminarFormulario(<?php echo $formulario['id']; ?>)">
                    <i class="fas fa-trash me-1"></i>Eliminar
                </button>
                <?php endif; ?>
            </div>
        </div>

        <?php
            endforeach;
        else:
        ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h5>Sin formularios</h5>
            <p>Crea tu primer formulario tocando el botón de arriba</p>
        </div>
        <?php endif; ?>

    </div><!-- /card-grid -->
</div><!-- /f144-wrap -->


<!-- ══════════════ MODAL AGREGAR ══════════════ -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarLabel">
                    <i class="fas fa-plus-circle me-2" style="color:var(--ios-blue,#007AFF);"></i>Nuevo Formulario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAgregarFormulario">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required
                               placeholder="Ingresa un título descriptivo">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion"
                                  rows="3" placeholder="Describe el propósito (opcional)"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="anio" class="form-label">Año de Vigencia</label>
                        <select class="form-control" id="anio" name="anio" required>
                            <option value="">— Seleccione un año —</option>
                            <?php if (!empty($anios)): ?>
                                <?php foreach ($anios as $a): ?>
                                    <option value="<?php echo htmlspecialchars($a['anio']); ?>"
                                        <?php echo ($a['anio'] == date('Y')) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($a['anio']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No hay años disponibles</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Configuración de Tiempo</label>
                        <div class="tiempo-opciones">
                            <div>
                                <input type="radio" name="tipo_tiempo" id="tipo_libre" value="libre" checked>
                                <label for="tipo_libre">
                                    <strong><i class="fas fa-infinity me-1" style="color:var(--ios-green);"></i> Tiempo Libre</strong>
                                    <span class="tiempo-opt-sub">El formulario estará siempre disponible</span>
                                </label>
                            </div>
                            <div>
                                <input type="radio" name="tipo_tiempo" id="tipo_rango" value="rango">
                                <label for="tipo_rango">
                                    <strong><i class="fas fa-calendar-alt me-1" style="color:var(--ios-blue);"></i> Rango de Tiempo</strong>
                                    <span class="tiempo-opt-sub">Definir fecha y hora de inicio y fin</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="rangoFechasContainer" style="display:none;" class="rango-fechas">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <small><i class="fas fa-info-circle me-1"></i>
                            Los formularios con rango de tiempo solo estarán disponibles dentro del período configurado.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ══════════════ MODAL EDITAR ══════════════ -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarLabel">
                    <i class="fas fa-edit me-2" style="color:var(--ios-blue,#007AFF);"></i>Editar Formulario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarFormulario">
                <input type="hidden" id="formularioIdEditar" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tituloEditar" class="form-label">Título</label>
                        <input type="text" class="form-control" id="tituloEditar" name="titulo" required>
                    </div>

                    <div class="mb-3">
                        <label for="descripcionEditar" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcionEditar" name="descripcion" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="anioEditar" class="form-label">Año de Vigencia</label>
                        <select class="form-control" id="anioEditar" name="anio" required>
                            <option value="">— Seleccione un año —</option>
                            <?php if (!empty($anios)): ?>
                                <?php foreach ($anios as $a): ?>
                                    <option value="<?php echo htmlspecialchars($a['anio']); ?>">
                                        <?php echo htmlspecialchars($a['anio']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No hay años disponibles</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Configuración de Tiempo</label>
                        <div class="tiempo-opciones">
                            <div>
                                <input type="radio" name="tipo_tiempo" id="tipo_libre_editar" value="libre">
                                <label for="tipo_libre_editar">
                                    <strong><i class="fas fa-infinity me-1" style="color:var(--ios-green);"></i> Tiempo Libre</strong>
                                </label>
                            </div>
                            <div>
                                <input type="radio" name="tipo_tiempo" id="tipo_rango_editar" value="rango">
                                <label for="tipo_rango_editar">
                                    <strong><i class="fas fa-calendar-alt me-1" style="color:var(--ios-blue);"></i> Rango de Tiempo</strong>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="rangoFechasContainerEditar" style="display:none;" class="rango-fechas">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inicio_editar" class="form-label">Fecha de Inicio</label>
                                <input type="datetime-local" class="form-control" id="fecha_inicio_editar" name="fecha_inicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin_editar" class="form-label">Fecha de Fin</label>
                                <input type="datetime-local" class="form-control" id="fecha_fin_editar" name="fecha_fin">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="estadoEditar" class="form-label">Estado</label>
                        <select class="form-control" id="estadoEditar" name="estado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ══════════════ MODAL INFORME ══════════════ -->
<div class="modal fade" id="modalInforme" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#007AFF,#5856D6);border-bottom:none;padding:20px 24px;">
                <div>
                    <h5 class="modal-title" style="color:#fff;font-size:18px;font-weight:700;letter-spacing:-.3px;margin:0 0 2px;">
                        <i class="fas fa-chart-bar me-2"></i><span id="infTitulo">Informe</span>
                    </h5>
                    <small style="color:rgba(255,255,255,.75);font-size:13px;">Cumplimiento · Líneas Estratégicas · Dependencias</small>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="background:#F2F2F7;padding:20px;">

                <!-- Loading state -->
                <div id="infLoading" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x" style="color:#007AFF;"></i>
                    <p class="mt-3" style="color:#6e6e73;font-size:14px;">Generando informe…</p>
                </div>

                <!-- Content (hidden until loaded) -->
                <div id="infContent" style="display:none;">

                    <!-- Global stats row -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div style="background:#fff;border-radius:16px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.07);text-align:center;">
                                <div style="font-size:13px;color:#6e6e73;font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Cumplimiento Global</div>
                                <div id="infPct" style="font-size:48px;font-weight:800;letter-spacing:-2px;line-height:1;color:#007AFF;">—</div>
                                <div style="font-size:13px;color:#aeaeb2;margin-top:4px;">promedio</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background:#fff;border-radius:16px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.07);text-align:center;">
                                <div style="font-size:13px;color:#6e6e73;font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Total Indicadores</div>
                                <div id="infTotal" style="font-size:48px;font-weight:800;letter-spacing:-2px;line-height:1;color:#1d1d1f;">—</div>
                                <div style="font-size:13px;color:#aeaeb2;margin-top:4px;">registros</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background:#fff;border-radius:16px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.07);text-align:center;">
                                <div style="font-size:13px;color:#6e6e73;font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Líneas Estratégicas</div>
                                <div id="infLineas" style="font-size:48px;font-weight:800;letter-spacing:-2px;line-height:1;color:#AF52DE;">—</div>
                                <div style="font-size:13px;color:#aeaeb2;margin-top:4px;">con registros</div>
                            </div>
                        </div>
                    </div>

                    <!-- Líneas estratégicas grid -->
                    <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:20px;">
                        <div style="font-size:15px;font-weight:700;color:#1d1d1f;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-layer-group" style="color:#007AFF;"></i>Líneas Estratégicas
                        </div>
                        <div id="infLineasGrid" class="row g-3"></div>
                    </div>

                    <!-- Chart -->
                    <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:20px;">
                        <div style="font-size:15px;font-weight:700;color:#1d1d1f;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-chart-bar" style="color:#34C759;"></i>Cumplimiento por Línea
                        </div>
                        <div style="position:relative;height:220px;">
                            <canvas id="infChart"></canvas>
                        </div>
                    </div>

                    <!-- Dependencias table -->
                    <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
                        <div style="font-size:15px;font-weight:700;color:#1d1d1f;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-university" style="color:#FF9500;"></i>Estadísticas por Dependencia
                        </div>
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:separate;border-spacing:0;font-size:14px;">
                                <thead>
                                    <tr style="background:#F2F2F7;">
                                        <th style="padding:10px 14px;font-weight:600;color:#6e6e73;font-size:12px;text-transform:uppercase;letter-spacing:.4px;border-radius:8px 0 0 8px;">Dependencia</th>
                                        <th style="padding:10px 14px;font-weight:600;color:#6e6e73;font-size:12px;text-transform:uppercase;letter-spacing:.4px;text-align:center;">Total Indicadores</th>
                                        <th style="padding:10px 14px;font-weight:600;color:#6e6e73;font-size:12px;text-transform:uppercase;letter-spacing:.4px;text-align:center;">Indicadores ≥80%</th>
                                        <th style="padding:10px 14px;font-weight:600;color:#6e6e73;font-size:12px;text-transform:uppercase;letter-spacing:.4px;text-align:center;border-radius:0 8px 8px 0;">Cumplimiento</th>
                                    </tr>
                                </thead>
                                <tbody id="infDepBody"></tbody>
                            </table>
                        </div>
                    </div>

                </div><!-- /infContent -->
            </div>
            <div class="modal-footer" style="background:#fff;border-top:.5px solid rgba(60,60,67,.1);padding:14px 20px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- ══════════════════════════════════════════ -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    const basePath = '<?php echo Config::getBasePath(); ?>';

    function mostrarRangoFechas(mostrar, sufijo) {
        sufijo = sufijo || '';
        const c  = document.getElementById('rangoFechasContainer' + sufijo);
        const fi = document.getElementById('fecha_inicio' + (sufijo ? '_' + sufijo.replace('Editar','editar') : ''));
        const ff = document.getElementById('fecha_fin'    + (sufijo ? '_' + sufijo.replace('Editar','editar') : ''));
        if (!c) return;
        c.style.display   = mostrar ? 'block' : 'none';
        if (fi) fi.required = mostrar;
        if (ff) ff.required = mostrar;
    }

    $(document).ready(function () {

        /* Radio buttons — modal agregar */
        $('input[name="tipo_tiempo"]:not(#modalEditar input)').on('change', function () {
            mostrarRangoFechas($(this).val() === 'rango', '');
        });

        /* Radio buttons — modal editar */
        $('#modalEditar input[name="tipo_tiempo"]').on('change', function () {
            mostrarRangoFechas($(this).val() === 'rango', 'Editar');
        });

        /* ── Agregar ── */
        $('#formAgregarFormulario').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            const orig = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...').prop('disabled', true);

            $.ajax({
                url: basePath + '/FOR-DE-144?action=crear',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (r) {
                    btn.html(orig).prop('disabled', false);
                    if (r.success) {
                        $('#modalAgregar').modal('hide');
                        $('#formAgregarFormulario')[0].reset();
                        mostrarRangoFechas(false, '');
                        Swal.fire({ icon: 'success', title: '¡Guardado!', text: r.message, timer: 1400, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: r.message });
                    }
                },
                error: function () {
                    btn.html(orig).prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
                }
            });
        });

        /* ── Editar ── */
        $('#formEditarFormulario').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            const orig = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...').prop('disabled', true);

            $.ajax({
                url: basePath + '/FOR-DE-144?action=editar',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (r) {
                    btn.html(orig).prop('disabled', false);
                    if (r.success) {
                        $('#modalEditar').modal('hide');
                        Swal.fire({ icon: 'success', title: '¡Actualizado!', text: r.message, timer: 1400, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: r.message });
                    }
                },
                error: function () {
                    btn.html(orig).prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
                }
            });
        });
    });

    function eliminarFormulario(id) {
        Swal.fire({
            title: '¿Eliminar formulario?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FF3B30',
            cancelButtonColor:  '#8E8E93',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText:  'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: basePath + '/FOR-DE-144?action=eliminar',
                type: 'POST',
                data: { id },
                dataType: 'json',
                success: function (r) {
                    if (r.success) {
                        Swal.fire({ icon: 'success', title: 'Eliminado', text: r.message, timer: 1400, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: r.message });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
                }
            });
        });
    }

    var infChart = null;

    function verInforme(id, titulo) {
        document.getElementById('infTitulo').textContent = titulo;
        document.getElementById('infLoading').style.display = '';
        document.getElementById('infContent').style.display = 'none';

        var modal = new bootstrap.Modal(document.getElementById('modalInforme'));
        modal.show();

        $.ajax({
            url: basePath + '/FOR-DE-144?action=informe&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                if (!r.success) {
                    document.getElementById('infLoading').innerHTML =
                        '<i class="fas fa-exclamation-circle fa-2x" style="color:#FF3B30;"></i>'
                        + '<p class="mt-3" style="color:#6e6e73;">No se pudo cargar el informe.</p>';
                    return;
                }
                renderInforme(r.data);
            },
            error: function() {
                document.getElementById('infLoading').innerHTML =
                    '<i class="fas fa-wifi fa-2x" style="color:#FF9500;"></i>'
                    + '<p class="mt-3" style="color:#6e6e73;">Error de conexión.</p>';
            }
        });
    }

    function badgeColor(pct) {
        if (pct >= 80) return { bg:'rgba(52,199,89,.15)', color:'#1A7A35' };
        if (pct >= 60) return { bg:'rgba(255,149,0,.15)',  color:'#7A4500' };
        return              { bg:'rgba(255,59,48,.12)',  color:'#C0392B' };
    }

    function renderInforme(data) {
        var g      = data.global     || {};
        var lineas = data.lineas     || [];
        var deps   = data.dependencias || [];

        var pct    = parseFloat(g.cumplimiento_global || 0);
        var total  = parseInt(g.total || 0);

        // Global numbers
        document.getElementById('infPct').textContent   = pct.toFixed(1) + '%';
        document.getElementById('infTotal').textContent = total;
        document.getElementById('infLineas').textContent = lineas.length;

        // Color of global percentage
        var bc = badgeColor(pct);
        document.getElementById('infPct').style.color = bc.color;

        // Líneas grid
        var grid = document.getElementById('infLineasGrid');
        grid.innerHTML = '';
        if (lineas.length === 0) {
            grid.innerHTML = '<div class="col-12"><p style="color:#aeaeb2;text-align:center;font-size:14px;">Sin datos de líneas estratégicas.</p></div>';
        }
        lineas.forEach(function(l) {
            var p = parseFloat(l.cumplimiento || 0);
            var c = badgeColor(p);
            var barColor = p >= 80 ? '#34C759' : (p >= 60 ? '#FF9500' : '#FF3B30');
            grid.innerHTML +=
                '<div class="col-md-6 col-lg-4">'
                + '<div style="background:#F2F2F7;border-radius:12px;padding:14px 16px;">'
                + '<div style="font-size:11px;font-weight:700;color:#007AFF;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">' + esc(l.codigo) + '</div>'
                + '<div style="font-size:13px;font-weight:600;color:#1d1d1f;margin-bottom:10px;line-height:1.3;">' + esc(l.linea) + '</div>'
                + '<div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">'
                + '<div style="flex:1;background:rgba(0,0,0,.06);border-radius:6px;height:8px;overflow:hidden;">'
                + '<div style="width:' + Math.min(p,100) + '%;height:100%;background:' + barColor + ';border-radius:6px;transition:width .6s;"></div>'
                + '</div>'
                + '<span style="font-size:13px;font-weight:700;color:' + c.color + ';white-space:nowrap;">' + p.toFixed(1) + '%</span>'
                + '</div>'
                + '<div style="font-size:11px;color:#aeaeb2;">' + l.total + ' indicador' + (l.total != 1 ? 'es' : '') + '</div>'
                + '</div></div>';
        });

        // Chart
        if (infChart) { infChart.destroy(); infChart = null; }
        if (lineas.length > 0) {
            var ctx = document.getElementById('infChart').getContext('2d');
            var labels  = lineas.map(function(l){ return l.codigo !== '—' ? l.codigo : l.linea.substring(0,20); });
            var valores = lineas.map(function(l){ return parseFloat(l.cumplimiento || 0).toFixed(1); });
            var colors  = lineas.map(function(l){
                var p = parseFloat(l.cumplimiento || 0);
                return p >= 80 ? 'rgba(52,199,89,.85)' : (p >= 60 ? 'rgba(255,149,0,.85)' : 'rgba(255,59,48,.85)');
            });
            infChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cumplimiento (%)',
                        data: valores,
                        backgroundColor: colors,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function(ctx){ return ctx.parsed.y + '%'; } } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true, max: 100,
                            ticks: { callback: function(v){ return v + '%'; }, font: { size: 11 } },
                            grid: { color: 'rgba(0,0,0,.05)' }
                        },
                        x: {
                            ticks: { font: { size: 11 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // Dependencias table
        var tbody = document.getElementById('infDepBody');
        tbody.innerHTML = '';
        if (deps.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="padding:20px;text-align:center;color:#aeaeb2;font-size:14px;">Sin datos de dependencias.</td></tr>';
        }
        deps.forEach(function(d, i) {
            var p  = parseFloat(d.cumplimiento || 0);
            var bc = badgeColor(p);
            var bg = i % 2 === 0 ? '#fff' : '#fafafa';
            tbody.innerHTML +=
                '<tr style="background:' + bg + ';">'
                + '<td style="padding:12px 14px;font-weight:500;color:#1d1d1f;">' + esc(d.dependencia) + '</td>'
                + '<td style="padding:12px 14px;text-align:center;color:#3a3a3c;">' + d.total_indicadores + '</td>'
                + '<td style="padding:12px 14px;text-align:center;"><span style="background:rgba(52,199,89,.15);color:#1A7A35;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;">' + (d.indicadores_alto || 0) + '</span></td>'
                + '<td style="padding:12px 14px;text-align:center;"><span style="background:' + bc.bg + ';color:' + bc.color + ';padding:3px 12px;border-radius:20px;font-size:13px;font-weight:700;">' + p.toFixed(1) + '%</span></td>'
                + '</tr>';
        });

        document.getElementById('infLoading').style.display = 'none';
        document.getElementById('infContent').style.display = '';
    }

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Destroy chart when modal closes to avoid canvas reuse issues
    document.getElementById('modalInforme').addEventListener('hidden.bs.modal', function() {
        if (infChart) { infChart.destroy(); infChart = null; }
    });

    function editarFormulario(id) {
        Swal.fire({ title: 'Cargando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: basePath + '/FOR-DE-144?action=getFormulario&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function (r) {
                Swal.close();
                if (r.success && r.formulario) {
                    const f = r.formulario;
                    $('#formularioIdEditar').val(f.id);
                    $('#tituloEditar').val(f.titulo);
                    $('#descripcionEditar').val(f.descripcion);
                    $('#estadoEditar').val(f.estado);
                    $('#anioEditar').val(f.anio || '').trigger('change');

                    if (f.tipo_tiempo === 'libre') {
                        $('#tipo_libre_editar').prop('checked', true);
                        mostrarRangoFechas(false, 'Editar');
                    } else {
                        $('#tipo_rango_editar').prop('checked', true);
                        mostrarRangoFechas(true, 'Editar');
                        $('#fecha_inicio_editar').val(f.fecha_inicio ? f.fecha_inicio.replace(' ', 'T') : '');
                        $('#fecha_fin_editar').val(f.fecha_fin     ? f.fecha_fin.replace(' ', 'T')     : '');
                    }

                    $('#modalEditar').modal('show');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: r.message || 'Error al cargar el formulario' });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
