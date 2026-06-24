<?php
// vista/roles/index.php
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Roles de Usuario — Configuración';
$paginaActual = 'roles';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

ob_start();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --ios-purple:  #AF52DE;
        --ios-green:   #34C759;
        --ios-red:     #FF3B30;
        --ios-gray:    #8E8E93;
        --ios-bg:      #F2F2F7;
        --ios-surface: #FFFFFF;
        --ios-label:   #000000;
        --ios-label2:  rgba(60,60,67,.6);
        --ios-sep:     rgba(60,60,67,.12);
        --font: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
    }
    body { font-family: var(--font); }
    .rol-wrap { padding: 0 4px; }

    .rol-header {
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
        box-shadow: 0 6px 24px rgba(175,82,222,.3);
    }
    .rol-header-left { display: flex; align-items: center; gap: 16px; }
    .rol-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,.2);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; flex-shrink: 0;
    }
    .rol-header h1 { font-size: 22px; font-weight: 700; margin: 0 0 3px; letter-spacing: -.3px; }
    .rol-header p  { font-size: 14px; opacity: .85; margin: 0; }

    .info-banner {
        background: rgba(175,82,222,.08);
        border: 1px solid rgba(175,82,222,.2);
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 13px;
        color: #6b2fa0;
        line-height: 1.5;
    }

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
        font-size: 16px; font-weight: 700; color: var(--ios-label);
        display: flex; align-items: center; gap: 8px;
    }
    .badge-count {
        background: var(--ios-purple);
        color: white;
        font-size: 11px; font-weight: 700;
        padding: 2px 8px; border-radius: 10px;
    }
    .search-input {
        border: 1.5px solid var(--ios-sep);
        border-radius: 10px;
        padding: 7px 12px;
        font-size: 14px;
        font-family: var(--font);
        outline: none; width: 220px;
        transition: border-color .2s;
    }
    .search-input:focus { border-color: var(--ios-purple); }

    .rol-table { width: 100%; border-collapse: collapse; }
    .rol-table thead th {
        background: var(--ios-bg);
        color: var(--ios-label2);
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .6px;
        padding: 11px 16px;
        border-bottom: 1px solid var(--ios-sep);
        white-space: nowrap;
    }
    .rol-table tbody tr { border-bottom: 1px solid var(--ios-sep); transition: background .15s; }
    .rol-table tbody tr:last-child { border-bottom: none; }
    .rol-table tbody tr:hover { background: rgba(175,82,222,.04); }
    .rol-table td { padding: 14px 16px; font-size: 14px; color: var(--ios-label); vertical-align: middle; }

    .slug-pill {
        background: rgba(175,82,222,.1);
        color: #6b2fa0;
        font-family: 'SF Mono', 'Menlo', monospace;
        font-size: 12px;
        padding: 3px 9px;
        border-radius: 8px;
        border: 1px solid rgba(175,82,222,.2);
    }
    .users-badge {
        background: var(--ios-bg);
        color: var(--ios-label2);
        font-size: 12px; font-weight: 600;
        padding: 3px 10px; border-radius: 20px;
        border: 1px solid var(--ios-sep);
    }

    .btn-action {
        padding: 5px 12px;
        border: none; border-radius: 8px;
        cursor: pointer; font-size: 13px;
        font-family: var(--font);
        transition: opacity .2s, transform .15s;
    }
    .btn-action:hover { opacity: .82; transform: translateY(-1px); }
    .btn-edit { background: rgba(175,82,222,.12); color: var(--ios-purple); }

    .empty-row td { text-align: center; padding: 60px 20px !important; color: var(--ios-label2); }
    .empty-row .empty-icon { font-size: 48px; color: var(--ios-gray); margin-bottom: 12px; }

    /* Modal */
    .modal-content { border-radius: 20px; border: none; overflow: hidden; }
    .modal-header { background: var(--ios-surface); border-bottom: 1px solid var(--ios-sep); padding: 20px 24px; }
    .modal-header .modal-title { font-weight: 700; font-size: 18px; color: var(--ios-label); }
    .modal-body  { padding: 24px; }
    .modal-footer { background: var(--ios-bg); border-top: 1px solid var(--ios-sep); padding: 16px 24px; }
    .form-label { font-weight: 600; font-size: 13px; color: var(--ios-label); margin-bottom: 6px; }
    .form-hint  { font-size: 12px; color: var(--ios-label2); margin-top: 5px; }
    .form-control {
        border: 1.5px solid var(--ios-sep);
        border-radius: 12px; padding: 11px 14px;
        font-family: var(--font); font-size: 15px;
        background: var(--ios-bg); color: var(--ios-label);
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus {
        outline: none; border-color: var(--ios-purple);
        background: var(--ios-surface);
        box-shadow: 0 0 0 3px rgba(175,82,222,.15);
    }
    .form-control[readonly] { opacity: .65; cursor: not-allowed; }
    .btn-ios { padding: 11px 22px; border: none; border-radius: 12px; font-weight: 600; font-size: 15px; cursor: pointer; font-family: var(--font); transition: opacity .2s, transform .15s; }
    .btn-ios:hover { opacity: .87; transform: translateY(-1px); }
    .btn-ios-primary   { background: var(--ios-purple); color: white; }
    .btn-ios-secondary { background: var(--ios-bg); color: var(--ios-purple); border: 1.5px solid var(--ios-sep); }

    @media (max-width: 768px) { .stats-row { grid-template-columns: 1fr 1fr; } .search-input { width: 100%; } }
    @media (max-width: 480px) { .stats-row { grid-template-columns: 1fr; } .rol-header { flex-direction: column; align-items: flex-start; } }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<div class="rol-wrap">

    <div class="rol-header">
        <div class="rol-header-left">
            <div class="rol-header-icon"><i class="fas fa-shield-alt"></i></div>
            <div>
                <h1>Roles de Usuario</h1>
                <p>Administra los nombres visibles de cada rol del sistema</p>
            </div>
        </div>
    </div>

    <div class="info-banner">
        <i class="fas fa-info-circle" style="font-size:18px;flex-shrink:0;margin-top:1px;"></i>
        <span>
            Los roles se leen directamente del ENUM <code>usuarios.rol</code> de la base de datos.
            Para <strong>agregar o eliminar</strong> un valor del ENUM, hazlo desde phpMyAdmin — aparecerá aquí automáticamente.
            Desde esta pantalla puedes editar el <strong>nombre visible</strong> que se muestra en el sistema.
        </span>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-num" id="stat-total" style="color: var(--ios-purple);">—</div>
            <div class="stat-label">Roles definidos</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" id="stat-usuarios" style="color: var(--ios-green);">—</div>
            <div class="stat-label">Usuarios asignados</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" id="stat-vacios" style="color: var(--ios-gray);">—</div>
            <div class="stat-label">Roles sin usuarios</div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <i class="fas fa-list" style="color:var(--ios-purple);"></i>
                Roles del sistema
                <span class="badge-count" id="badge-count">0</span>
            </div>
            <input type="text" class="search-input" id="searchInput" placeholder="Buscar rol...">
        </div>
        <div class="table-responsive">
            <table class="rol-table">
                <thead>
                    <tr>
                        <th>Identificador (slug)</th>
                        <th>Nombre visible</th>
                        <th>Usuarios</th>
                        <th>Editar nombre</th>
                    </tr>
                </thead>
                <tbody id="tablaRolesBody">
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

<!-- Modal editar nombre -->
<div class="modal fade" id="modalRol" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar nombre del rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRol">
                <input type="hidden" id="rol_slug" name="slug">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Identificador (slug)</label>
                        <input type="text" class="form-control" id="rol_slug_display" readonly>
                        <p class="form-hint">Valor guardado en la base de datos. Solo editable desde phpMyAdmin.</p>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Nombre visible *</label>
                        <input type="text" class="form-control" name="nombre" id="rol_nombre" required
                               placeholder="Ej: Administrador del sistema">
                        <p class="form-hint">Este nombre aparece en los selectores y reportes del sistema.</p>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const basePath = '<?php echo $basePath; ?>';
    let todosLosRoles = [];

    $(document).ready(function() {
        cargarRoles();
        $('#searchInput').on('input', function() { filtrar($(this).val().toLowerCase()); });
    });

    function cargarRoles() {
        $.ajax({
            url: basePath + '/roles/listar',
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    todosLosRoles = res.roles;
                    renderTabla(todosLosRoles);
                    actualizarStats(todosLosRoles);
                } else { mostrarError(); }
            },
            error: function() { mostrarError(); }
        });
    }

    function actualizarStats(roles) {
        const totalUsuarios = roles.reduce((s, r) => s + (r.usuarios || 0), 0);
        const vacios = roles.filter(r => !r.usuarios || r.usuarios === 0).length;
        $('#stat-total').text(roles.length);
        $('#stat-usuarios').text(totalUsuarios);
        $('#stat-vacios').text(vacios);
        $('#badge-count').text(roles.length);
    }

    function renderTabla(roles) {
        if (roles.length === 0) {
            $('#tablaRolesBody').html(`<tr class="empty-row"><td colspan="4"><div class="empty-icon"><i class="fas fa-shield-alt"></i></div><div>No se encontraron roles</div></td></tr>`);
            return;
        }
        let html = '';
        roles.forEach(function(r) {
            const usuariosBadge = r.usuarios > 0
                ? `<span class="users-badge"><i class="fas fa-user" style="font-size:10px;margin-right:4px;"></i>${r.usuarios}</span>`
                : `<span class="users-badge" style="opacity:.5;">0</span>`;
            html += `<tr>
                <td><span class="slug-pill">${escHtml(r.slug)}</span></td>
                <td><strong>${escHtml(r.nombre)}</strong></td>
                <td>${usuariosBadge}</td>
                <td>
                    <button class="btn-action btn-edit" onclick="editarRol('${escHtml(r.slug)}', '${escHtml(r.nombre)}')">
                        <i class="fas fa-edit me-1"></i> Editar nombre
                    </button>
                </td>
            </tr>`;
        });
        $('#tablaRolesBody').html(html);
    }

    function filtrar(q) {
        if (!q) { renderTabla(todosLosRoles); return; }
        renderTabla(todosLosRoles.filter(r =>
            (r.slug   || '').toLowerCase().includes(q) ||
            (r.nombre || '').toLowerCase().includes(q)
        ));
    }

    function mostrarError() {
        $('#tablaRolesBody').html('<tr class="empty-row"><td colspan="4"><div class="empty-icon" style="color:var(--ios-red);"><i class="fas fa-exclamation-circle"></i></div><div>Error al cargar los roles</div></td></tr>');
    }

    function editarRol(slug, nombre) {
        $('#rol_slug').val(slug);
        $('#rol_slug_display').val(slug);
        $('#rol_nombre').val(nombre);
        $('#modalRol').modal('show');
    }

    $('#formRol').on('submit', function(e) {
        e.preventDefault();
        const data = { slug: $('#rol_slug').val(), nombre: $('#rol_nombre').val() };
        $('#btnGuardar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');
        $.ajax({
            url: basePath + '/roles/actualizarNombre',
            type: 'POST', data: data, dataType: 'json',
            success: function(res) {
                $('#btnGuardar').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Guardado!', text: res.message, timer: 1800, showConfirmButton: false });
                    $('#modalRol').modal('hide');
                    cargarRoles();
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() {
                $('#btnGuardar').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
            }
        });
    });

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
