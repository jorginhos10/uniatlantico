<?php
// vista/dependencias/index.php
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Dependencias — Configuración';
$paginaActual = 'dependencias';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

ob_start();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --ios-blue:    #007AFF;
        --ios-green:   #34C759;
        --ios-red:     #FF3B30;
        --ios-orange:  #FF9500;
        --ios-gray:    #8E8E93;
        --ios-bg:      #F2F2F7;
        --ios-surface: #FFFFFF;
        --ios-label:   #000000;
        --ios-label2:  rgba(60,60,67,.6);
        --ios-sep:     rgba(60,60,67,.12);
        --font: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
    }
    body { font-family: var(--font); }
    .dep-wrap { padding: 0 4px; }
    .dep-header {
        background: var(--ios-orange);
        color: white;
        border-radius: 20px;
        padding: 26px 28px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 6px 24px rgba(255,149,0,.3);
    }
    .dep-header-left { display: flex; align-items: center; gap: 16px; }
    .dep-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,.2);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .dep-header h1 { font-size: 22px; font-weight: 700; margin: 0 0 3px; letter-spacing: -.3px; }
    .dep-header p  { font-size: 14px; opacity: .85; margin: 0; }
    .btn-nueva {
        background: rgba(255,255,255,.22);
        color: white;
        border: 1.5px solid rgba(255,255,255,.4);
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        display: flex; align-items: center; gap: 7px;
        transition: background .2s;
        font-family: var(--font);
        white-space: nowrap;
    }
    .btn-nueva:hover { background: rgba(255,255,255,.32); }
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 22px;
    }
    .stat-card {
        background: var(--ios-surface);
        border-radius: 16px;
        padding: 18px 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,.07);
        text-align: center;
    }
    .stat-num   { font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 1; }
    .stat-label { font-size: 12px; color: var(--ios-label2); margin-top: 4px; text-transform: uppercase; letter-spacing: .4px; }
    .table-card {
        background: var(--ios-surface);
        border-radius: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,.08);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .table-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--ios-sep);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .table-card-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--ios-label);
        display: flex; align-items: center; gap: 8px;
    }
    .badge-count {
        background: var(--ios-orange);
        color: white;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 10px;
    }
    .search-input {
        border: 1.5px solid var(--ios-sep);
        border-radius: 10px;
        padding: 7px 12px;
        font-size: 14px;
        font-family: var(--font);
        outline: none;
        width: 220px;
        transition: border-color .2s;
    }
    .search-input:focus { border-color: var(--ios-orange); }
    .dep-table { width: 100%; border-collapse: collapse; }
    .dep-table thead th {
        background: var(--ios-bg);
        color: var(--ios-label2);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        padding: 11px 16px;
        border-bottom: 1px solid var(--ios-sep);
        white-space: nowrap;
    }
    .dep-table tbody tr { border-bottom: 1px solid var(--ios-sep); transition: background .15s; }
    .dep-table tbody tr:last-child { border-bottom: none; }
    .dep-table tbody tr:hover { background: rgba(255,149,0,.04); }
    .dep-table td { padding: 14px 16px; font-size: 14px; color: var(--ios-label); vertical-align: middle; }
    .badge-activo   { background: rgba(52,199,89,.15);  color: #1a7a3a; border: 1px solid rgba(52,199,89,.3); }
    .badge-inactivo { background: rgba(255,59,48,.1);   color: #c0392b; border: 1px solid rgba(255,59,48,.25); }
    .badge-estado {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 12px; font-weight: 600;
    }
    .btn-action {
        padding: 5px 10px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-family: var(--font);
        transition: opacity .2s, transform .15s;
        margin: 0 2px;
    }
    .btn-action:hover { opacity: .82; transform: translateY(-1px); }
    .btn-edit       { background: rgba(255,149,0,.12); color: var(--ios-orange); }
    .btn-conectar   { background: rgba(0,122,255,.1); color: var(--ios-blue); font-weight: 600; }
    .conexiones-count { display: inline-block; margin-left: 5px; min-width: 18px; padding: 0 5px; border-radius: 9px; background: var(--ios-blue); color: #fff; font-size: 11px; line-height: 18px; text-align: center; }
    .flujo-conexiones { background: var(--ios-bg); border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; font-size: 13px; }
    .flujo-conexiones-titulo { font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; color: var(--ios-label2); margin-bottom: 6px; }
    .flujo-conexiones ul { margin: 0; padding-left: 18px; }
    .flujo-conexiones li { margin: 2px 0; }
    .btn-delete     { background: rgba(255,59,48,.1);  color: var(--ios-red); }
    .btn-toggle-on  { background: rgba(255,59,48,.1);  color: var(--ios-red); }
    .btn-toggle-off { background: rgba(52,199,89,.12); color: #1a7a3a; }
    .empty-row td { text-align: center; padding: 60px 20px !important; color: var(--ios-label2); }
    .empty-row .empty-icon { font-size: 48px; color: var(--ios-gray); margin-bottom: 12px; }
    .modal-content { border-radius: 20px; border: none; overflow: hidden; }
    .modal-header { background: var(--ios-surface); border-bottom: 1px solid var(--ios-sep); padding: 20px 24px; }
    .modal-header .modal-title { font-weight: 700; font-size: 18px; color: var(--ios-label); }
    .modal-body  { padding: 24px; }
    .modal-footer { background: var(--ios-bg); border-top: 1px solid var(--ios-sep); padding: 16px 24px; }
    .form-label { font-weight: 600; font-size: 13px; color: var(--ios-label); margin-bottom: 6px; }
    .form-control {
        border: 1.5px solid var(--ios-sep);
        border-radius: 12px;
        padding: 11px 14px;
        font-family: var(--font);
        font-size: 15px;
        background: var(--ios-bg);
        color: var(--ios-label);
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--ios-orange);
        background: var(--ios-surface);
        box-shadow: 0 0 0 3px rgba(255,149,0,.15);
    }
    .btn-ios { padding: 11px 22px; border: none; border-radius: 12px; font-weight: 600; font-size: 15px; cursor: pointer; font-family: var(--font); transition: opacity .2s, transform .15s; }
    .btn-ios:hover { opacity: .87; transform: translateY(-1px); }
    .btn-ios-primary   { background: var(--ios-orange); color: white; }
    .btn-ios-secondary { background: var(--ios-bg); color: var(--ios-orange); border: 1.5px solid var(--ios-sep); }
    .btn-ios-danger    { background: var(--ios-red); color: white; }
    /* Pestañas Listado / Flujo de información */
    .dep-tabs { display: inline-flex; gap: 4px; background: var(--ios-surface); padding: 4px; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.08); margin-bottom: 16px; flex-wrap: wrap; }
    .dep-tab { border: none; background: transparent; padding: 9px 18px; border-radius: 10px; font-size: 14px; font-weight: 600; color: var(--ios-label2); font-family: var(--font); cursor: pointer; transition: background .15s, color .15s; }
    .dep-tab.active { background: var(--ios-orange); color: #fff; }
    .dep-tab-panel[hidden] { display: none; }
    .btn-add-flujo { border: none; background: var(--ios-orange); color: #fff; border-radius: 10px; padding: 8px 14px; font-size: 14px; font-weight: 600; font-family: var(--font); cursor: pointer; }
    .btn-add-flujo:hover { opacity: .87; }
    .flujo-flecha { color: var(--ios-orange); font-size: 13px; text-align: center; width: 40px; }
    .flujo-dep { font-weight: 600; }
    .flujo-rol { display: block; font-size: 11px; font-weight: 500; color: var(--ios-label2); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }
    .badge-aplica { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: rgba(0,122,255,.1); color: #0058b0; white-space: nowrap; }
    .badge-aplica.formulacion { background: rgba(175,82,222,.12); color: #7d3aa6; }
    .badge-aplica.seguimiento { background: rgba(52,199,89,.14); color: #1a7a3a; }
    .flujo-obs { color: var(--ios-label2); font-size: 13px; }
    .flujo-ayuda { padding: 12px 20px; font-size: 13px; color: var(--ios-label2); border-bottom: 1px solid var(--ios-sep); background: rgba(255,149,0,.05); }
    .form-select { border: 1.5px solid var(--ios-sep); border-radius: 12px; padding: 11px 14px; font-family: var(--font); font-size: 15px; background-color: var(--ios-bg); }
    .form-select:focus { border-color: var(--ios-orange); box-shadow: 0 0 0 3px rgba(255,149,0,.15); }
    @media (max-width: 768px) { .stats-row { grid-template-columns: 1fr 1fr; } .search-input { width: 100%; } }
    @media (max-width: 480px) { .stats-row { grid-template-columns: 1fr; } .dep-header { flex-direction: column; align-items: flex-start; } }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<div class="dep-wrap">

    <div class="dep-header">
        <div class="dep-header-left">
            <div class="dep-header-icon"><i class="fas fa-user-tie"></i></div>
            <div>
                <h1>Dependencias</h1>
                <p>Gestiona las dependencias del sistema</p>
            </div>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn-nueva" onclick="exportarExcel()" title="Descargar el listado en Excel">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
            <button class="btn-nueva" onclick="abrirModalCrear()">
                <i class="fas fa-plus"></i> Nueva Dependencia
            </button>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-num" id="stat-total" style="color: var(--ios-orange);">—</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" id="stat-activos" style="color: var(--ios-green);">—</div>
            <div class="stat-label">Activos</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" id="stat-inactivos" style="color: var(--ios-red);">—</div>
            <div class="stat-label">Inactivos</div>
        </div>
    </div>

    <div class="dep-tabs" role="tablist">
        <button type="button" class="dep-tab active" data-dep-tab="listado"><i class="fas fa-list me-1"></i> Listado</button>
        <button type="button" class="dep-tab" data-dep-tab="flujo"><i class="fas fa-project-diagram me-1"></i> Flujo de información</button>
    </div>

    <div class="dep-tab-panel" data-dep-panel="listado">
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="fas fa-list" style="color:var(--ios-orange);"></i>
                Listado
                <span class="badge-count" id="badge-count">0</span>
            </div>
            <input type="text" class="search-input" id="searchInput" placeholder="Buscar dependencia...">
        </div>
        <div class="table-responsive">
            <table class="dep-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaDepsBody">
                    <tr class="empty-row">
                        <td colspan="5">
                            <div class="empty-icon"><i class="fas fa-spinner fa-spin"></i></div>
                            <div>Cargando...</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>

    <!-- ===================== FLUJO DE INFORMACIÓN ===================== -->
    <div class="dep-tab-panel" data-dep-panel="flujo" hidden>
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="fas fa-project-diagram" style="color:var(--ios-orange);"></i>
                Flujo de información
                <span class="badge-count" id="badge-count-flujo">0</span>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <input type="text" class="search-input" id="searchFlujo" placeholder="Buscar dependencia...">
                <button type="button" class="btn-add-flujo" onclick="abrirModalFlujo()">
                    <i class="fas fa-plus me-1"></i> Nueva relación
                </button>
            </div>
        </div>
        <div class="flujo-ayuda">
            <i class="fas fa-info-circle me-1" style="color:var(--ios-orange);"></i>
            Cada relación indica a qué dependencia <strong>envía</strong> una dependencia su formulación y/o seguimiento, y cuál se lo <strong>aprueba</strong>.
        </div>
        <div class="table-responsive">
            <table class="dep-table">
                <thead>
                    <tr>
                        <th>Envía</th>
                        <th></th>
                        <th>Aprueba</th>
                        <th>Aplica a</th>
                        <th>Observación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaFlujoBody">
                    <tr class="empty-row">
                        <td colspan="6">
                            <div class="empty-icon"><i class="fas fa-spinner fa-spin"></i></div>
                            <div>Cargando...</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>

</div>

<!-- Modal Crear / Editar -->
<div class="modal fade" id="modalDep" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDepTitulo">Nueva Dependencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formDep">
                <input type="hidden" id="dep_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" id="dep_nombre" required
                               placeholder="Ej: Facultad de Ingeniería">
                    </div>
                    <div class="mb-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activo" id="dep_activo" value="1" checked>
                            <label class="form-check-label" for="dep_activo"><strong>Activo</strong></label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ios btn-ios-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-ios btn-ios-primary" id="btnGuardar">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Relación del flujo -->
<div class="modal fade" id="modalFlujo" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFlujoTitulo">Nueva relación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formFlujo">
                <input type="hidden" id="flujo_id" name="id">
                <div class="modal-body">
                    <div class="flujo-conexiones" id="flujo_conexiones" style="display:none;"></div>
                    <div class="mb-3">
                        <label class="form-label" for="flujo_origen">Dependencia que envía *</label>
                        <select class="form-select" id="flujo_origen" name="origen_id" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="flujo_destino">Dependencia que aprueba *</label>
                        <select class="form-select" id="flujo_destino" name="destino_id" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="flujo_aplica">Aplica a</label>
                        <select class="form-select" id="flujo_aplica" name="aplica_a">
                            <option value="ambos">Formulación y seguimiento</option>
                            <option value="formulacion">Solo formulación</option>
                            <option value="seguimiento">Solo seguimiento</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label" for="flujo_observacion">Observación</label>
                        <input type="text" class="form-control" id="flujo_observacion" name="observacion" maxlength="255" placeholder="Opcional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ios btn-ios-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-ios btn-ios-primary"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar Dependencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding: 28px 24px;">
                <div style="width:64px;height:64px;background:rgba(255,59,48,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--ios-red);">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <p style="font-size:15px;color:var(--ios-label2);margin:0;">
                    ¿Eliminar <strong id="depNombreEliminar"></strong>? Esta acción no se puede deshacer.
                </p>
                <input type="hidden" id="dep_id_eliminar">
            </div>
            <div class="modal-footer" style="justify-content:center;gap:12px;">
                <button type="button" class="btn-ios btn-ios-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn-ios btn-ios-danger" onclick="confirmarEliminar()">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const basePath = '<?php echo $basePath; ?>';
    let todasLasDeps = [];

    $(document).ready(function() {
        cargarDependencias();
        cargarFlujos();
        $('#searchInput').on('input', function() { filtrar($(this).val().toLowerCase()); });
        $('#searchFlujo').on('input', function() { renderFlujos(); });

        // Pestañas (se recuerda la última abierta)
        $('[data-dep-tab]').on('click', function() { mostrarTab($(this).data('dep-tab')); });
        let tabGuardada = null;
        try { tabGuardada = sessionStorage.getItem('dependencias_tab'); } catch (e) {}
        if (tabGuardada === 'flujo') mostrarTab('flujo');
    });

    function mostrarTab(tab) {
        $('[data-dep-tab]').each(function() { $(this).toggleClass('active', $(this).data('dep-tab') === tab); });
        $('[data-dep-panel]').each(function() { this.hidden = $(this).data('dep-panel') !== tab; });
        try { sessionStorage.setItem('dependencias_tab', tab); } catch (e) {}
    }

    // ===================== FLUJO DE INFORMACIÓN =====================
    let todosLosFlujos = [];
    const APLICA_TEXTO = { ambos: 'Formulación y seguimiento', formulacion: 'Formulación', seguimiento: 'Seguimiento' };

    function cargarFlujos() {
        $.ajax({
            url: basePath + '/dependencias/listarFlujos',
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    todosLosFlujos = res.flujos;
                    renderFlujos();
                    // Actualiza el contador del botón "Conectar" en el listado
                    if (todasLasDeps.length) filtrar(($('#searchInput').val() || '').toLowerCase());
                }
                else { errorFlujos(); }
            },
            error: errorFlujos
        });
    }

    function errorFlujos() {
        $('#tablaFlujoBody').html('<tr class="empty-row"><td colspan="6"><div class="empty-icon" style="color:var(--ios-red);"><i class="fas fa-exclamation-circle"></i></div><div>Error al cargar el flujo de información</div></td></tr>');
    }

    function renderFlujos() {
        const q = ($('#searchFlujo').val() || '').toLowerCase().trim();
        const lista = q ? todosLosFlujos.filter(f =>
            (f.origen_nombre || '').toLowerCase().includes(q) || (f.destino_nombre || '').toLowerCase().includes(q)
        ) : todosLosFlujos;

        $('#badge-count-flujo').text(todosLosFlujos.length);
        if (!lista.length) {
            $('#tablaFlujoBody').html('<tr class="empty-row"><td colspan="6"><div class="empty-icon"><i class="fas fa-project-diagram"></i></div><div>' +
                (q ? 'Ninguna relación coincide con la búsqueda' : 'Aún no hay relaciones. Agrega la primera con "Nueva relación".') + '</div></td></tr>');
            return;
        }

        let html = '';
        lista.forEach(f => {
            html += `<tr>
                <td><span class="flujo-rol">Envía</span><span class="flujo-dep">${escHtml(f.origen_nombre)}</span></td>
                <td class="flujo-flecha"><i class="fas fa-arrow-right"></i></td>
                <td><span class="flujo-rol">Aprueba</span><span class="flujo-dep">${escHtml(f.destino_nombre)}</span></td>
                <td><span class="badge-aplica ${f.aplica_a}">${APLICA_TEXTO[f.aplica_a] || escHtml(f.aplica_a)}</span></td>
                <td class="flujo-obs">${f.observacion ? escHtml(f.observacion) : '—'}</td>
                <td style="white-space:nowrap;">
                    <button class="btn-action btn-edit" onclick="editarFlujo(${f.id})" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn-action btn-delete" onclick="eliminarFlujo(${f.id})" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        $('#tablaFlujoBody').html(html);
    }

    // Llena un select con las dependencias activas (y la ya seleccionada, aunque esté inactiva)
    function llenarSelectDependencias(selector, seleccionado) {
        let html = '<option value="">Seleccione...</option>';
        todasLasDeps.forEach(d => {
            if (d.activo != 1 && d.id != seleccionado) return;
            html += `<option value="${d.id}">${escHtml(d.nombre)}${d.activo != 1 ? ' (inactiva)' : ''}</option>`;
        });
        $(selector).html(html).val(seleccionado || '');
    }

    // Cantidad de conexiones (envía o aprueba) de una dependencia, para el botón "Conectar"
    function badgeConexiones(depId) {
        const n = todosLosFlujos.filter(f => f.origen_id == depId || f.destino_id == depId).length;
        return n ? `<span class="conexiones-count" title="${n} conexión(es)">${n}</span>` : '';
    }

    // Botón "Conectar" del listado: abre el pop up con esta dependencia como la que envía
    // y muestra arriba sus conexiones actuales
    function conectarDep(depId) {
        abrirModalFlujo(null, depId);
        const dep = todasLasDeps.find(d => d.id == depId);
        const envia  = todosLosFlujos.filter(f => f.origen_id == depId);
        const recibe = todosLosFlujos.filter(f => f.destino_id == depId);
        if (!envia.length && !recibe.length) return;

        let html = `<div class="flujo-conexiones-titulo">Conexiones actuales de ${escHtml(dep ? dep.nombre : '')}</div><ul>`;
        envia.forEach(f => {
            html += `<li>Envía a <strong>${escHtml(f.destino_nombre)}</strong> <span class="badge-aplica ${f.aplica_a}">${APLICA_TEXTO[f.aplica_a] || ''}</span></li>`;
        });
        recibe.forEach(f => {
            html += `<li>Le aprueba a <strong>${escHtml(f.origen_nombre)}</strong> <span class="badge-aplica ${f.aplica_a}">${APLICA_TEXTO[f.aplica_a] || ''}</span></li>`;
        });
        $('#flujo_conexiones').html(html + '</ul>').show();
    }

    function abrirModalFlujo(flujo, origenId) {
        $('#flujo_conexiones').hide().empty();
        $('#flujo_id').val(flujo ? flujo.id : '');
        llenarSelectDependencias('#flujo_origen', flujo ? flujo.origen_id : (origenId || ''));
        llenarSelectDependencias('#flujo_destino', flujo ? flujo.destino_id : '');
        $('#flujo_aplica').val(flujo ? flujo.aplica_a : 'ambos');
        $('#flujo_observacion').val(flujo ? (flujo.observacion || '') : '');
        $('#modalFlujoTitulo').text(flujo ? 'Editar relación' : 'Nueva relación');
        $('#modalFlujo').modal('show');
    }

    function editarFlujo(id) {
        const flujo = todosLosFlujos.find(f => f.id == id);
        if (flujo) abrirModalFlujo(flujo);
    }

    $('#formFlujo').on('submit', function(e) {
        e.preventDefault();
        const id = $('#flujo_id').val();
        $.ajax({
            url: basePath + '/dependencias/' + (id ? 'actualizarFlujo' : 'crearFlujo'),
            type: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#modalFlujo').modal('hide');
                    Swal.fire({ icon: 'success', title: res.message, timer: 1400, showConfirmButton: false, toast: true, position: 'top-end' });
                    cargarFlujos();
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() { Swal.fire('Error', 'No se pudo guardar la relación', 'error'); }
        });
    });

    function eliminarFlujo(id) {
        const flujo = todosLosFlujos.find(f => f.id == id);
        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar relación?',
            html: flujo ? `<strong>${escHtml(flujo.origen_nombre)}</strong> → <strong>${escHtml(flujo.destino_nombre)}</strong>` : '',
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#FF3B30'
        }).then(r => {
            if (!r.isConfirmed) return;
            $.ajax({
                url: basePath + '/dependencias/eliminarFlujo',
                type: 'POST', data: { id: id }, dataType: 'json',
                success: function(res) {
                    if (res.success) { cargarFlujos(); }
                    else { Swal.fire('Error', res.message, 'error'); }
                }
            });
        });
    }

    function cargarDependencias() {
        $.ajax({
            url: basePath + '/dependencias/listar',
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    todasLasDeps = res.dependencias;
                    renderTabla(todasLasDeps);
                    actualizarStats(todasLasDeps);
                } else { mostrarError(); }
            },
            error: function() { mostrarError(); }
        });
    }

    function actualizarStats(deps) {
        const activos = deps.filter(d => d.activo == 1).length;
        $('#stat-total').text(deps.length);
        $('#stat-activos').text(activos);
        $('#stat-inactivos').text(deps.length - activos);
        $('#badge-count').text(deps.length);
    }

    function renderTabla(deps) {
        if (deps.length === 0) {
            $('#tablaDepsBody').html(`<tr class="empty-row"><td colspan="5"><div class="empty-icon"><i class="fas fa-user-tie"></i></div><div>No hay dependencias registradas</div></td></tr>`);
            return;
        }
        let html = '';
        deps.forEach(function(d) {
            const estadoClass = d.activo == 1 ? 'badge-activo' : 'badge-inactivo';
            const estadoText  = d.activo == 1 ? '<i class="fas fa-check-circle"></i> Activo' : '<i class="fas fa-times-circle"></i> Inactivo';
            const toggleClass = d.activo == 1 ? 'btn-toggle-on' : 'btn-toggle-off';
            const toggleIcon  = d.activo == 1 ? 'fa-ban' : 'fa-check-circle';
            const fecha = d.fecha_creacion ? new Date(d.fecha_creacion).toLocaleDateString('es-CO') : '—';
            html += `<tr>
                <td><strong style="color:var(--ios-orange);">#${d.id}</strong></td>
                <td><strong>${escHtml(d.nombre)}</strong></td>
                <td><span class="badge-estado ${estadoClass}">${estadoText}</span></td>
                <td style="color:var(--ios-label2);font-size:13px;">${fecha}</td>
                <td style="white-space:nowrap;">
                    <button class="btn-action btn-conectar" onclick="conectarDep(${d.id})" title="Conectar con otra dependencia"><i class="fas fa-link me-1"></i>Conectar${badgeConexiones(d.id)}</button>
                    <button class="btn-action btn-edit"   onclick="editarDep(${d.id})" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn-action ${toggleClass}" onclick="cambiarEstado(${d.id}, ${d.activo == 1 ? 0 : 1})"><i class="fas ${toggleIcon}"></i></button>
                    <button class="btn-action btn-delete" onclick="pedirEliminar(${d.id}, '${escHtml(d.nombre)}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        $('#tablaDepsBody').html(html);
    }

    function filtrar(q) {
        if (!q) { renderTabla(todasLasDeps); return; }
        renderTabla(todasLasDeps.filter(d => (d.nombre || '').toLowerCase().includes(q)));
    }

    // Descarga el listado en Excel; si hay texto en el buscador, exporta solo lo filtrado
    function exportarExcel() {
        const q = ($('#searchInput').val() || '').trim();
        window.location.href = basePath + '/dependencias/exportar' + (q ? '?q=' + encodeURIComponent(q) : '');
    }

    function mostrarError() {
        $('#tablaDepsBody').html('<tr class="empty-row"><td colspan="5"><div class="empty-icon" style="color:var(--ios-red);"><i class="fas fa-exclamation-circle"></i></div><div>Error al cargar los datos</div></td></tr>');
    }

    function abrirModalCrear() {
        $('#modalDepTitulo').text('Nueva Dependencia');
        $('#dep_id').val('');
        $('#formDep')[0].reset();
        $('#dep_activo').prop('checked', true);
        $('#modalDep').modal('show');
    }

    function editarDep(id) {
        $.ajax({
            url: basePath + '/dependencias/get?id=' + id,
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const d = res.dependencia;
                    $('#modalDepTitulo').text('Editar Dependencia');
                    $('#dep_id').val(d.id);
                    $('#dep_nombre').val(d.nombre);
                    $('#dep_activo').prop('checked', d.activo == 1);
                    $('#modalDep').modal('show');
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() { Swal.fire('Error', 'No se pudo cargar el cargo', 'error'); }
        });
    }

    $('#formDep').on('submit', function(e) {
        e.preventDefault();
        const id  = $('#dep_id').val();
        const url = id ? basePath + '/dependencias/actualizar' : basePath + '/dependencias/crear';
        const data = { id: id, nombre: $('#dep_nombre').val(), activo: $('#dep_activo').is(':checked') ? 1 : 0 };

        $('#btnGuardar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');
        $.ajax({
            url: url, type: 'POST', data: data, dataType: 'json',
            success: function(res) {
                $('#btnGuardar').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Guardado!', text: res.message, timer: 1800, showConfirmButton: false });
                    $('#modalDep').modal('hide');
                    cargarDependencias();
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() {
                $('#btnGuardar').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
            }
        });
    });

    function cambiarEstado(id, nuevoEstado) {
        $.ajax({
            url: basePath + '/dependencias/cambiarEstado',
            type: 'POST', data: { id: id, activo: nuevoEstado }, dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: res.message, timer: 1400, showConfirmButton: false, toast: true, position: 'top-end' });
                    cargarDependencias();
                } else { Swal.fire('Error', res.message, 'error'); }
            }
        });
    }

    function pedirEliminar(id, nombre) {
        $('#dep_id_eliminar').val(id);
        $('#depNombreEliminar').text(nombre);
        $('#modalEliminar').modal('show');
    }

    function confirmarEliminar() {
        $.ajax({
            url: basePath + '/dependencias/eliminar',
            type: 'POST', data: { id: $('#dep_id_eliminar').val() }, dataType: 'json',
            success: function(res) {
                $('#modalEliminar').modal('hide');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Eliminado!', text: res.message, timer: 1800, showConfirmButton: false });
                    cargarDependencias();
                    cargarFlujos(); // sus relaciones del flujo también se borran
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() { $('#modalEliminar').modal('hide'); Swal.fire('Error', 'Error al eliminar', 'error'); }
        });
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
