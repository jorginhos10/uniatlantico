<?php
// vista/organigrama/index.php
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Organigrama';
$paginaActual = 'organigrama';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

ob_start();
?>
<style>
    :root {
        --ios-blue:    #007AFF;
        --ios-bg:      #F2F2F7;
        --ios-surface: #FFFFFF;
        --ios-label:   #000000;
        --ios-label2:  rgba(60,60,67,.6);
        --ios-sep:     rgba(60,60,67,.12);
    }
    body { font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif; }

    .org-wrap { padding: 0 4px; }

    .org-header {
        background: var(--ios-surface);
        border-radius: 20px;
        padding: 24px 28px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 .5px var(--ios-sep);
    }

    .org-header-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #007AFF 0%, #5856D6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 22px;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(0,122,255,.32);
    }

    .org-header h1 {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 2px;
        color: var(--ios-label);
    }

    .org-header p {
        font-size: 13px;
        color: var(--ios-label2);
        margin: 0;
    }

    .org-empty {
        background: var(--ios-surface);
        border-radius: 20px;
        padding: 70px 24px;
        text-align: center;
        box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 .5px var(--ios-sep);
    }

    .org-empty i {
        font-size: 46px;
        color: rgba(60,60,67,.25);
        margin-bottom: 16px;
    }

    .org-empty h5 {
        font-size: 17px;
        font-weight: 600;
        color: var(--ios-label);
        margin-bottom: 6px;
    }

    .org-empty p {
        font-size: 14px;
        color: var(--ios-label2);
        margin: 0;
    }

    /* ═══ NIVELES DEL ORGANIGRAMA ═══ */
    .org-chart {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 30px 10px;
    }

    .org-node {
        background: var(--ios-surface);
        border-radius: 16px;
        padding: 18px 26px;
        min-width: 260px;
        max-width: 420px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,.08), 0 0 0 .5px var(--ios-sep);
        border-top: 4px solid var(--ios-blue);
    }

    .org-node-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--ios-label);
        margin-bottom: 8px;
    }

    .org-node-users {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: center;
    }

    .org-node-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: var(--ios-bg);
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 12.5px;
        color: var(--ios-label);
    }

    .org-node-chip i { color: var(--ios-blue); font-size: 11px; }

    .org-node-empty {
        font-size: 13px;
        color: var(--ios-label2);
        font-style: italic;
    }

    .org-connector {
        width: 2px;
        height: 34px;
        background: var(--ios-sep);
    }

    /* ═══ RAMA CON VARIOS HIJOS EN EL MISMO NIVEL ═══ */
    .org-branch-row {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        gap: 50px;
        flex-wrap: wrap;
        padding-top: 20px;
    }

    .org-branch-item {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Tallo vertical hacia el nodo de este ítem */
    .org-branch-item::before {
        content: '';
        position: absolute;
        top: -20px;
        left: 50%;
        width: 2px;
        height: 20px;
        background: var(--ios-sep);
    }

    /* Barra horizontal que conecta con los hermanos (se extiende medio "gap" a cada lado) */
    .org-branch-item::after {
        content: '';
        position: absolute;
        top: -20px;
        left: -25px;
        right: -25px;
        height: 2px;
        background: var(--ios-sep);
    }

    /* El primero no debe sobresalir por la izquierda; el último no debe sobresalir por la derecha */
    .org-branch-item:first-child::after { left: 50%; }
    .org-branch-item:last-child::after  { right: 50%; }

    /* Si es hijo único, no hace falta barra horizontal */
    .org-branch-item:only-child::after { display: none; }

    /* Las raíces no cuelgan de nadie: sin tallo ni barra */
    .org-roots { padding-top: 0; }
    .org-roots > .org-branch-item::before,
    .org-roots > .org-branch-item::after { display: none; }

    .org-node.inactivo { opacity: .55; border-top-color: rgba(60,60,67,.3); }
    .org-node-badge {
        display: inline-block;
        margin-left: 6px;
        padding: 1px 8px;
        border-radius: 10px;
        background: rgba(255,59,48,.12);
        color: #D70015;
        font-size: 11px;
        font-weight: 600;
    }

    /* ═══ ADMINISTRACIÓN (vista Estructural) ═══ */
    .org-header { justify-content: space-between; flex-wrap: wrap; }
    .org-header-info { display: flex; align-items: center; gap: 16px; }

    .org-modo { display: inline-flex; background: var(--ios-bg); border-radius: 12px; padding: 4px; }
    .org-modo button {
        border: 0;
        background: transparent;
        padding: 8px 18px;
        border-radius: 9px;
        font-size: 14px;
        font-weight: 600;
        color: var(--ios-label2);
    }
    .org-modo button.activo { background: var(--ios-surface); color: var(--ios-blue); box-shadow: 0 1px 3px rgba(0,0,0,.12); }

    .org-card {
        background: var(--ios-surface);
        border-radius: 20px;
        padding: 22px 24px;
        margin-bottom: 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 .5px var(--ios-sep);
    }
    .org-card h2 { font-size: 16px; font-weight: 700; margin: 0 0 14px; }

    .org-form { display: grid; grid-template-columns: 2fr 2fr 2fr 90px auto; gap: 12px; align-items: end; }
    @media (max-width: 900px) { .org-form { grid-template-columns: 1fr; } }
    .org-form label { font-size: 12.5px; font-weight: 600; color: var(--ios-label2); margin-bottom: 4px; display: block; }

    .org-btn {
        border: 0;
        border-radius: 10px;
        padding: 9px 18px;
        font-size: 14px;
        font-weight: 600;
        background: var(--ios-blue);
        color: #fff;
        white-space: nowrap;
    }

    .org-table { width: 100%; border-collapse: collapse; }
    .org-table th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--ios-label2);
        text-align: left;
        padding: 10px 12px;
        border-bottom: 1px solid var(--ios-sep);
    }
    .org-table td { padding: 10px 12px; border-bottom: 1px solid var(--ios-sep); font-size: 14px; vertical-align: middle; }
    .org-table tr:last-child td { border-bottom: 0; }
    .org-tree-name { font-weight: 600; }
    .org-tree-guide { color: rgba(60,60,67,.35); margin-right: 6px; }
    .org-muted { color: var(--ios-label2); }

    .org-estado {
        border: 0;
        border-radius: 20px;
        padding: 3px 12px;
        font-size: 12.5px;
        font-weight: 600;
        background: rgba(52,199,89,.14);
        color: #1E8E3E;
    }
    .org-estado.inactivo { background: rgba(60,60,67,.1); color: var(--ios-label2); }

    .org-accion {
        border: 0;
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: rgba(0,122,255,.1);
        color: var(--ios-blue);
    }
    .org-accion.eliminar { background: rgba(255,59,48,.1); color: #D70015; }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<?php
$esAdminOrganigrama = !empty($esAdminOrganigrama);
$nodosPlanos   = $nodosPlanos ?? [];
$nodosAnidados = $nodosAnidados ?? [];
$roles         = $roles ?? [];

function org_normalizar($s) {
    $s = mb_strtolower(trim((string)$s), 'UTF-8');
    return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
}

// Busca la clave real de $usuariosPorRol cuyo nombre de rol coincida (sin acentos/mayúsculas) con el patrón buscado
function org_buscarUsuariosPorRol($usuariosPorRol, $patron) {
    foreach ($usuariosPorRol as $rol => $usuarios) {
        if (org_normalizar($rol) === org_normalizar($patron)) {
            return $usuarios;
        }
    }
    return [];
}

function org_renderChips($usuarios) {
    if (empty($usuarios)) {
        echo '<span class="org-node-empty">Sin usuarios asignados</span>';
        return;
    }
    foreach ($usuarios as $u) {
        echo '<span class="org-node-chip"><i class="fas fa-user"></i>' . htmlspecialchars($u['nombre']) . '</span>';
    }
}

// Dibuja un nodo y, debajo, sus hijos. Los nodos inactivos (y su rama) solo los ve el administrador.
function org_renderNodo($nodo, $usuariosPorRol, $esAdmin) {
    $inactivo = (int)$nodo['activo'] !== 1;
    echo '<div class="org-node' . ($inactivo ? ' inactivo' : '') . '">';
    echo '<div class="org-node-title">' . htmlspecialchars($nodo['nombre']);
    if ($inactivo) echo '<span class="org-node-badge">Inactivo</span>';
    echo '</div>';
    if (!empty($nodo['rol'])) {
        echo '<div class="org-node-users">';
        org_renderChips(org_buscarUsuariosPorRol($usuariosPorRol, $nodo['rol']));
        echo '</div>';
    }
    echo '</div>';

    $hijos = array_values(array_filter($nodo['hijos'], function ($h) use ($esAdmin) {
        return $esAdmin || (int)$h['activo'] === 1;
    }));
    if (!$hijos) return;

    echo '<div class="org-connector"></div>';
    echo '<div class="org-branch-row">';
    foreach ($hijos as $hijo) {
        echo '<div class="org-branch-item">';
        org_renderNodo($hijo, $usuariosPorRol, $esAdmin);
        echo '</div>';
    }
    echo '</div>';
}

$raicesVisibles = array_values(array_filter($nodosAnidados, function ($n) use ($esAdminOrganigrama) {
    return $esAdminOrganigrama || (int)$n['activo'] === 1;
}));
?>

<div class="org-wrap">
    <div class="org-header">
        <div class="org-header-info">
            <div class="org-header-icon">
                <i class="fas fa-sitemap"></i>
            </div>
            <div>
                <h1>Organigrama</h1>
                <p>Estructura organizacional de la Universidad del Atlántico</p>
            </div>
        </div>
        <?php if ($esAdminOrganigrama): ?>
        <div class="org-modo">
            <button type="button" class="activo" data-org-modo="mapa">Mapa</button>
            <button type="button" data-org-modo="estructura">Estructural</button>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===================== MAPA ===================== -->
    <div data-org-vista="mapa">
        <?php if (empty($raicesVisibles)): ?>
        <div class="org-empty">
            <i class="fas fa-sitemap"></i>
            <h5>El organigrama aún no tiene nodos</h5>
            <p><?php echo $esAdminOrganigrama ? 'Agrégalos desde la vista Estructural.' : 'El administrador todavía no lo ha configurado.'; ?></p>
        </div>
        <?php else: ?>
        <div class="org-chart">
            <div class="org-branch-row org-roots">
                <?php foreach ($raicesVisibles as $raiz): ?>
                <div class="org-branch-item">
                    <?php org_renderNodo($raiz, $usuariosPorRol ?? [], $esAdminOrganigrama); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($esAdminOrganigrama): ?>
    <!-- ===================== ESTRUCTURAL (solo administrador) ===================== -->
    <div data-org-vista="estructura" hidden>
        <div class="org-card">
            <h2>Agregar nodo</h2>
            <form id="formOrgCrear" class="org-form">
                <div>
                    <label for="org_nombre">Nombre *</label>
                    <input type="text" class="form-control" id="org_nombre" name="nombre" maxlength="150" required placeholder="Ej. Líder de Metas">
                </div>
                <div>
                    <label for="org_padre">Depende de</label>
                    <select class="form-select" id="org_padre" name="padre_id">
                        <option value="0">— Sin padre (raíz) —</option>
                        <?php foreach ($nodosPlanos as $n): ?>
                        <option value="<?php echo (int)$n['id']; ?>"><?php echo str_repeat('— ', (int)$n['nivel']) . htmlspecialchars($n['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="org_rol">Rol asociado</label>
                    <select class="form-select" id="org_rol" name="rol">
                        <option value="">Sin rol</option>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?php echo htmlspecialchars($r['slug']); ?>"><?php echo htmlspecialchars($r['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="org_orden">Orden</label>
                    <input type="number" class="form-control" id="org_orden" name="orden" value="0" min="0">
                </div>
                <button type="submit" class="org-btn"><i class="fas fa-plus me-1"></i> Agregar</button>
            </form>
        </div>

        <div class="org-card">
            <h2>Estructura</h2>
            <div class="table-responsive">
                <table class="org-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Depende de</th>
                            <th>Rol asociado</th>
                            <th style="width:80px">Orden</th>
                            <th style="width:110px">Estado</th>
                            <th style="width:100px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nodosPlanos as $n): ?>
                        <tr>
                            <td>
                                <span style="padding-left: <?php echo (int)$n['nivel'] * 22; ?>px;">
                                    <?php if ((int)$n['nivel'] > 0): ?><span class="org-tree-guide">↳</span><?php endif; ?>
                                    <span class="org-tree-name"><?php echo htmlspecialchars($n['nombre']); ?></span>
                                </span>
                            </td>
                            <td class="org-muted"><?php echo $n['nombre_padre'] !== null ? htmlspecialchars($n['nombre_padre']) : '—'; ?></td>
                            <td class="org-muted"><?php echo !empty($n['rol']) ? htmlspecialchars($n['rol']) : '—'; ?></td>
                            <td class="org-muted"><?php echo (int)$n['orden']; ?></td>
                            <td>
                                <button type="button" class="org-estado <?php echo (int)$n['activo'] === 1 ? '' : 'inactivo'; ?>"
                                        onclick="orgCambiarEstado(<?php echo (int)$n['id']; ?>)"
                                        title="<?php echo (int)$n['activo'] === 1 ? 'Activo (clic para desactivar)' : 'Inactivo (clic para activar)'; ?>">
                                    <?php echo (int)$n['activo'] === 1 ? 'Activo' : 'Inactivo'; ?>
                                </button>
                            </td>
                            <td>
                                <button type="button" class="org-accion" title="Editar"
                                        data-id="<?php echo (int)$n['id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($n['nombre']); ?>"
                                        data-padre-id="<?php echo (int)($n['padre_id'] ?? 0); ?>"
                                        data-rol="<?php echo htmlspecialchars($n['rol'] ?? ''); ?>"
                                        data-orden="<?php echo (int)$n['orden']; ?>"
                                        onclick="orgEditar(this)"><i class="fas fa-edit"></i></button>
                                <button type="button" class="org-accion eliminar" title="Eliminar"
                                        data-id="<?php echo (int)$n['id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($n['nombre']); ?>"
                                        onclick="orgEliminar(this)"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($nodosPlanos)): ?>
                        <tr><td colspan="6" class="org-muted text-center">No hay nodos registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal editar nodo -->
    <div class="modal fade" id="modalOrgEditar" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar nodo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formOrgEditar">
                    <input type="hidden" id="org_editar_id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="org_editar_nombre">Nombre *</label>
                            <input type="text" class="form-control" id="org_editar_nombre" name="nombre" maxlength="150" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="org_editar_padre">Depende de</label>
                            <select class="form-select" id="org_editar_padre" name="padre_id">
                                <option value="0">— Sin padre (raíz) —</option>
                                <?php foreach ($nodosPlanos as $n): ?>
                                <option value="<?php echo (int)$n['id']; ?>"><?php echo str_repeat('— ', (int)$n['nivel']) . htmlspecialchars($n['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="org_editar_rol">Rol asociado</label>
                            <select class="form-select" id="org_editar_rol" name="rol">
                                <option value="">Sin rol</option>
                                <?php foreach ($roles as $r): ?>
                                <option value="<?php echo htmlspecialchars($r['slug']); ?>"><?php echo htmlspecialchars($r['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Los usuarios activos con este rol aparecen dentro del nodo.</div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="org_editar_orden">Orden</label>
                            <input type="number" class="form-control" id="org_editar_orden" name="orden" min="0">
                            <div class="form-text">Posición entre los nodos que dependen del mismo padre.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="org-btn"><i class="fas fa-save me-1"></i> Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($esAdminOrganigrama): ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const orgBasePath = '<?php echo $basePath; ?>';
    const ORG_MODO_KEY = 'organigrama_modo';

    function orgMostrarModo(modo) {
        document.querySelectorAll('[data-org-vista]').forEach(v => { v.hidden = v.getAttribute('data-org-vista') !== modo; });
        document.querySelectorAll('[data-org-modo]').forEach(b => { b.classList.toggle('activo', b.getAttribute('data-org-modo') === modo); });
        sessionStorage.setItem(ORG_MODO_KEY, modo);
    }

    document.querySelectorAll('[data-org-modo]').forEach(b => {
        b.addEventListener('click', () => orgMostrarModo(b.getAttribute('data-org-modo')));
    });
    // Tras guardar se recarga la página: se conserva la vista en la que estaba el administrador
    orgMostrarModo(sessionStorage.getItem(ORG_MODO_KEY) === 'estructura' ? 'estructura' : 'mapa');

    function orgEnviar(accion, datos) {
        $.ajax({
            url: orgBasePath + '/organigrama/' + accion,
            type: 'POST', data: datos, dataType: 'json',
            success: function(res) {
                if (res.success) { location.reload(); }
                else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() { Swal.fire('Error', 'No se pudo guardar el cambio', 'error'); }
        });
    }

    $('#formOrgCrear').on('submit', function(e) {
        e.preventDefault();
        orgEnviar('crear', $(this).serialize());
    });

    function orgEditar(boton) {
        const rol = boton.dataset.rol || '';
        const selectRol = document.getElementById('org_editar_rol');
        // Si el rol guardado ya no está en el catálogo, se conserva como opción para no perderlo
        if (rol !== '' && !Array.from(selectRol.options).some(o => o.value === rol)) {
            selectRol.add(new Option(rol, rol));
        }
        $('#org_editar_id').val(boton.dataset.id);
        $('#org_editar_nombre').val(boton.dataset.nombre);
        $('#org_editar_padre').val(boton.dataset.padreId);
        $('#org_editar_rol').val(rol);
        $('#org_editar_orden').val(boton.dataset.orden);
        // Un nodo no puede depender de sí mismo
        $('#org_editar_padre option').prop('disabled', false);
        $('#org_editar_padre option[value="' + boton.dataset.id + '"]').prop('disabled', true);
        $('#modalOrgEditar').modal('show');
    }

    $('#formOrgEditar').on('submit', function(e) {
        e.preventDefault();
        orgEnviar('actualizar', $(this).serialize());
    });

    function orgCambiarEstado(id) {
        orgEnviar('cambiarEstado', { id: id });
    }

    function orgEliminar(boton) {
        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar nodo?',
            text: boton.dataset.nombre + ' — esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#FF3B30'
        }).then(r => { if (r.isConfirmed) orgEnviar('eliminar', { id: boton.dataset.id }); });
    }
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
