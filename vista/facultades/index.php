<?php
// vista/facultades/index.php
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Facultades — Configuración';
$paginaActual = 'facultades';
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
        --ios-purple:  #9C27B0;
        --ios-gray:    #8E8E93;
        --ios-bg:      #F2F2F7;
        --ios-surface: #FFFFFF;
        --ios-label:   #000000;
        --ios-label2:  rgba(60,60,67,.6);
        --ios-sep:     rgba(60,60,67,.12);
        --font: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
    }
    body { font-family: var(--font); }
    .fac-wrap { padding: 0 4px; }
    .fac-header {
        background: var(--ios-purple);
        color: white;
        border-radius: 20px;
        padding: 26px 28px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 6px 24px rgba(156,39,176,.3);
    }
    .fac-header-left { display: flex; align-items: center; gap: 16px; }
    .fac-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,.2);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .fac-header h1 { font-size: 22px; font-weight: 700; margin: 0 0 3px; letter-spacing: -.3px; }
    .fac-header p  { font-size: 14px; opacity: .85; margin: 0; }
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
        background: var(--ios-purple);
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
    .search-input:focus { border-color: var(--ios-purple); }
    .fac-table { width: 100%; border-collapse: collapse; }
    .fac-table thead th {
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
    .fac-table tbody tr { border-bottom: 1px solid var(--ios-sep); transition: background .15s; }
    .fac-table tbody tr:last-child { border-bottom: none; }
    .fac-table tbody tr:hover { background: rgba(156,39,176,.04); }
    .fac-table td { padding: 14px 16px; font-size: 14px; color: var(--ios-label); vertical-align: middle; }
    .badge-codigo {
        display: inline-flex; align-items: center;
        padding: 3px 10px; border-radius: 20px;
        font-size: 12px; font-weight: 700;
        background: rgba(156,39,176,.1); color: var(--ios-purple);
        border: 1px solid rgba(156,39,176,.25);
    }
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
    .btn-edit       { background: rgba(156,39,176,.12); color: var(--ios-purple); }
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
        border-color: var(--ios-purple);
        background: var(--ios-surface);
        box-shadow: 0 0 0 3px rgba(156,39,176,.15);
    }
    .btn-ios { padding: 11px 22px; border: none; border-radius: 12px; font-weight: 600; font-size: 15px; cursor: pointer; font-family: var(--font); transition: opacity .2s, transform .15s; }
    .btn-ios:hover { opacity: .87; transform: translateY(-1px); }
    .btn-ios-primary   { background: var(--ios-purple); color: white; }
    .btn-ios-secondary { background: var(--ios-bg); color: var(--ios-purple); border: 1.5px solid var(--ios-sep); }
    .btn-ios-danger    { background: var(--ios-red); color: white; }
    @media (max-width: 768px) { .stats-row { grid-template-columns: 1fr 1fr; } .search-input { width: 100%; } }
    @media (max-width: 480px) { .stats-row { grid-template-columns: 1fr; } .fac-header { flex-direction: column; align-items: flex-start; } }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<div class="fac-wrap">

    <div class="fac-header">
        <div class="fac-header-left">
            <div class="fac-header-icon"><i class="fas fa-university"></i></div>
            <div>
                <h1>Facultades</h1>
                <p>Gestiona las facultades del sistema</p>
            </div>
        </div>
        <button class="btn-nueva" onclick="abrirModalCrear()">
            <i class="fas fa-plus"></i> Nueva Facultad
        </button>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-num" id="stat-total" style="color: var(--ios-purple);">—</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" id="stat-activos" style="color: var(--ios-green);">—</div>
            <div class="stat-label">Activas</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" id="stat-inactivos" style="color: var(--ios-red);">—</div>
            <div class="stat-label">Inactivas</div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="fas fa-list" style="color:var(--ios-purple);"></i>
                Listado
                <span class="badge-count" id="badge-count">0</span>
            </div>
            <input type="text" class="search-input" id="searchInput" placeholder="Buscar facultad...">
        </div>
        <div class="table-responsive">
            <table class="fac-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaFacsBody">
                    <tr class="empty-row">
                        <td colspan="4">
                            <div class="empty-icon"><i class="fas fa-spinner fa-spin"></i></div>
                            <div>Cargando...</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Crear / Editar -->
<div class="modal fade" id="modalFac" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFacTitulo">Nueva Facultad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formFac">
                <input type="hidden" id="fac_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" class="form-control" name="codigo" id="fac_codigo"
                               placeholder="Ej: FAC-ING">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" id="fac_nombre" required
                               placeholder="Ej: Facultad de Ingeniería">
                    </div>
                    <div class="mb-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="estado" id="fac_estado" value="1" checked>
                            <label class="form-check-label" for="fac_estado"><strong>Activa</strong></label>
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

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar Facultad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding: 28px 24px;">
                <div style="width:64px;height:64px;background:rgba(255,59,48,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--ios-red);">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <p style="font-size:15px;color:var(--ios-label2);margin:0;">
                    ¿Eliminar <strong id="facNombreEliminar"></strong>? Esta acción no se puede deshacer.
                </p>
                <input type="hidden" id="fac_id_eliminar">
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
    let todasLasFacs = [];

    $(document).ready(function() {
        cargarFacultades();
        $('#searchInput').on('input', function() { filtrar($(this).val().toLowerCase()); });
    });

    function cargarFacultades() {
        $.ajax({
            url: basePath + '/facultades/listar',
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    todasLasFacs = res.facultades;
                    renderTabla(todasLasFacs);
                    actualizarStats(todasLasFacs);
                } else { mostrarError(); }
            },
            error: function() { mostrarError(); }
        });
    }

    function actualizarStats(facs) {
        const activas = facs.filter(f => f.estado == 1).length;
        $('#stat-total').text(facs.length);
        $('#stat-activos').text(activas);
        $('#stat-inactivos').text(facs.length - activas);
        $('#badge-count').text(facs.length);
    }

    function renderTabla(facs) {
        if (facs.length === 0) {
            $('#tablaFacsBody').html(`<tr class="empty-row"><td colspan="4"><div class="empty-icon"><i class="fas fa-university"></i></div><div>No hay facultades registradas</div></td></tr>`);
            return;
        }
        let html = '';
        facs.forEach(function(f) {
            const estadoClass = f.estado == 1 ? 'badge-activo' : 'badge-inactivo';
            const estadoText  = f.estado == 1 ? '<i class="fas fa-check-circle"></i> Activa' : '<i class="fas fa-times-circle"></i> Inactiva';
            const toggleClass = f.estado == 1 ? 'btn-toggle-on' : 'btn-toggle-off';
            const toggleIcon  = f.estado == 1 ? 'fa-ban' : 'fa-check-circle';
            html += `<tr>
                <td><span class="badge-codigo">${escHtml(f.codigo || '—')}</span></td>
                <td><strong>${escHtml(f.nombre)}</strong></td>
                <td><span class="badge-estado ${estadoClass}">${estadoText}</span></td>
                <td>
                    <button class="btn-action btn-edit"   onclick="editarFac(${f.id})" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn-action ${toggleClass}" onclick="cambiarEstado(${f.id}, ${f.estado == 1 ? 0 : 1})"><i class="fas ${toggleIcon}"></i></button>
                    <button class="btn-action btn-delete" onclick="pedirEliminar(${f.id}, '${escHtml(f.nombre)}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        $('#tablaFacsBody').html(html);
    }

    function filtrar(q) {
        if (!q) { renderTabla(todasLasFacs); return; }
        renderTabla(todasLasFacs.filter(f => (f.nombre || '').toLowerCase().includes(q) || (f.codigo || '').toLowerCase().includes(q)));
    }

    function mostrarError() {
        $('#tablaFacsBody').html('<tr class="empty-row"><td colspan="4"><div class="empty-icon" style="color:var(--ios-red);"><i class="fas fa-exclamation-circle"></i></div><div>Error al cargar los datos</div></td></tr>');
    }

    function abrirModalCrear() {
        $('#modalFacTitulo').text('Nueva Facultad');
        $('#fac_id').val('');
        $('#formFac')[0].reset();
        $('#fac_estado').prop('checked', true);
        $('#modalFac').modal('show');
    }

    function editarFac(id) {
        $.ajax({
            url: basePath + '/facultades/get?id=' + id,
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const f = res.facultad;
                    $('#modalFacTitulo').text('Editar Facultad');
                    $('#fac_id').val(f.id);
                    $('#fac_codigo').val(f.codigo);
                    $('#fac_nombre').val(f.nombre);
                    $('#fac_estado').prop('checked', f.estado == 1);
                    $('#modalFac').modal('show');
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() { Swal.fire('Error', 'No se pudo cargar la facultad', 'error'); }
        });
    }

    $('#formFac').on('submit', function(e) {
        e.preventDefault();
        const id  = $('#fac_id').val();
        const url = id ? basePath + '/facultades/actualizar' : basePath + '/facultades/crear';
        const data = { id: id, codigo: $('#fac_codigo').val(), nombre: $('#fac_nombre').val(), estado: $('#fac_estado').is(':checked') ? 1 : 0 };

        $('#btnGuardar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');
        $.ajax({
            url: url, type: 'POST', data: data, dataType: 'json',
            success: function(res) {
                $('#btnGuardar').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Guardado!', text: res.message, timer: 1800, showConfirmButton: false });
                    $('#modalFac').modal('hide');
                    cargarFacultades();
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
            url: basePath + '/facultades/cambiarEstado',
            type: 'POST', data: { id: id, estado: nuevoEstado }, dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: res.message, timer: 1400, showConfirmButton: false, toast: true, position: 'top-end' });
                    cargarFacultades();
                } else { Swal.fire('Error', res.message, 'error'); }
            }
        });
    }

    function pedirEliminar(id, nombre) {
        $('#fac_id_eliminar').val(id);
        $('#facNombreEliminar').text(nombre);
        $('#modalEliminar').modal('show');
    }

    function confirmarEliminar() {
        $.ajax({
            url: basePath + '/facultades/eliminar',
            type: 'POST', data: { id: $('#fac_id_eliminar').val() }, dataType: 'json',
            success: function(res) {
                $('#modalEliminar').modal('hide');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Eliminado!', text: res.message, timer: 1800, showConfirmButton: false });
                    cargarFacultades();
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
