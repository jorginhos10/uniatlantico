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
        <button class="btn-nueva" onclick="abrirModalCrear()">
            <i class="fas fa-plus"></i> Nueva Dependencia
        </button>
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
        $('#searchInput').on('input', function() { filtrar($(this).val().toLowerCase()); });
    });

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
                <td>
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
