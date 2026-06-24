<?php
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/config.php';

$titulo       = 'Novedades — Universidad del Atlántico';
$paginaActual = 'configuraciones';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

ob_start();
?>
<style>
:root {
    --nov-blue:   #007AFF;
    --nov-green:  #34C759;
    --nov-red:    #FF3B30;
    --nov-orange: #FF9500;
    --nov-bg:     #F2F2F7;
    --nov-card:   #FFFFFF;
    --nov-border: rgba(0,0,0,0.07);
    --nov-text:   #1d1d1f;
    --nov-sub:    #6e6e73;
    --nov-r:      14px;
}

.nov-page { max-width: 960px; margin: 0 auto; padding: 0 4px; }

.nov-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 10px; flex-wrap: wrap; gap: 12px;
}
.nov-header h1 {
    font-size: 22px; font-weight: 700; color: var(--nov-text);
    margin: 0; display: flex; align-items: center; gap: 10px;
}
.nov-header h1 i { color: var(--nov-orange); }
.nov-hint { font-size: 12px; color: var(--nov-sub); margin-bottom: 18px; display: flex; align-items: center; gap: 6px; }

.btn-nov-add {
    display: inline-flex; align-items: center; gap: 7px;
    background: var(--nov-blue); color: #fff;
    border: none; border-radius: 10px; padding: 10px 18px;
    font-size: 14px; font-weight: 600; cursor: pointer;
    transition: opacity .15s, transform .12s;
}
.btn-nov-add:hover { opacity: .88; transform: translateY(-1px); }

/* Toast */
.nov-toast {
    position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%) translateY(20px);
    background: #1d1d1f; color: #fff; padding: 11px 22px; border-radius: 50px;
    font-size: 13.5px; font-weight: 500; opacity: 0; pointer-events: none;
    transition: opacity .25s, transform .25s; z-index: 9999; white-space: nowrap;
}
.nov-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
.nov-toast.error { background: var(--nov-red); }

/* Empty */
.nov-empty {
    background: var(--nov-card); border-radius: var(--nov-r);
    border: 1px solid var(--nov-border);
    display: flex; flex-direction: column; align-items: center;
    gap: 10px; padding: 52px 20px; color: var(--nov-sub);
}
.nov-empty i { font-size: 40px; opacity: .2; }
.nov-empty p { font-size: 14px; margin: 0; }

/* Grid draggable */
.nov-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
}

.nov-card-item {
    background: var(--nov-card);
    border-radius: var(--nov-r);
    border: 1px solid var(--nov-border);
    overflow: hidden;
    transition: box-shadow .15s, transform .15s, opacity .15s;
    display: flex; flex-direction: column;
    cursor: grab;
    user-select: none;
}
.nov-card-item:active { cursor: grabbing; }
.nov-card-item:hover { box-shadow: 0 6px 24px rgba(0,0,0,.09); }
.nov-card-item.inactiva { opacity: .55; }
.nov-card-item.sortable-ghost { opacity: .35; border: 2px dashed var(--nov-blue); }
.nov-card-item.sortable-drag  { box-shadow: 0 12px 40px rgba(0,0,0,.18); transform: scale(1.02); cursor: grabbing; }

.nov-card-drag-handle {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 14px 0; color: var(--nov-sub); font-size: 11px;
}
.nov-card-drag-handle i { font-size: 13px; opacity: .4; }

.nov-card-top {
    padding: 8px 18px 14px;
    flex: 1;
    border-bottom: 1px solid var(--nov-border);
}
.nov-card-titulo {
    font-size: 15px; font-weight: 700; color: var(--nov-text);
    margin-bottom: 8px; line-height: 1.35;
}
.nov-card-contenido {
    font-size: 13px; color: var(--nov-sub); line-height: 1.6;
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
    overflow: hidden;
}

.nov-card-foot {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 14px; gap: 8px;
}
.nov-badge-activa   { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; background: #e8f5e9; color: #2e7d32; }
.nov-badge-inactiva { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; background: #f5f5f5; color: #9e9e9e; }

.nov-actions { display: flex; gap: 6px; }
.nov-btn-icon {
    width: 32px; height: 32px; border-radius: 8px; border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; cursor: pointer; transition: background .12s;
}
.nov-btn-edit   { background: rgba(0,122,255,.1); color: var(--nov-blue); }
.nov-btn-edit:hover { background: rgba(0,122,255,.2); }
.nov-btn-toggle { background: rgba(52,199,89,.1); color: var(--nov-green); }
.nov-btn-toggle:hover { background: rgba(52,199,89,.2); }
.nov-btn-del    { background: rgba(255,59,48,.1);  color: var(--nov-red); }
.nov-btn-del:hover  { background: rgba(255,59,48,.2); }

/* Modal crear/editar */
.nov-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    display: flex; align-items: center; justify-content: center;
    z-index: 1000; opacity: 0; pointer-events: none;
    transition: opacity .2s;
}
.nov-modal-overlay.open { opacity: 1; pointer-events: all; }

.nov-modal {
    background: #fff; border-radius: 18px;
    width: 100%; max-width: 520px; margin: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
    transform: scale(.95) translateY(10px);
    transition: transform .25s cubic-bezier(0.34,1.56,0.64,1);
}
.nov-modal-overlay.open .nov-modal { transform: scale(1) translateY(0); }

.nov-modal-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 22px 16px; border-bottom: 1px solid var(--nov-border);
}
.nov-modal-head h3 { font-size: 17px; font-weight: 700; margin: 0; color: var(--nov-text); }
.nov-modal-close {
    width: 30px; height: 30px; border-radius: 50%; border: none;
    background: rgba(0,0,0,.06); color: var(--nov-sub); cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: 14px;
    transition: background .12s;
}
.nov-modal-close:hover { background: rgba(0,0,0,.12); }

.nov-modal-body { padding: 20px 22px; display: flex; flex-direction: column; gap: 16px; }

.nov-field label {
    display: block; font-size: 11px; font-weight: 700; color: var(--nov-sub);
    text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px;
}
.nov-field input, .nov-field textarea, .nov-field select {
    width: 100%; border: 1.5px solid rgba(0,0,0,.12); border-radius: 10px;
    padding: 10px 13px; font-size: 14px; color: var(--nov-text);
    outline: none; transition: border-color .15s; background: #fafafa;
    box-sizing: border-box; font-family: inherit;
}
.nov-field input:focus, .nov-field textarea:focus, .nov-field select:focus {
    border-color: var(--nov-blue); background: #fff;
}
.nov-field textarea { resize: vertical; min-height: 110px; }

.nov-modal-foot {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 22px 20px; border-top: 1px solid var(--nov-border);
}
.btn-cancel {
    padding: 9px 18px; border-radius: 10px; border: 1.5px solid var(--nov-border);
    background: #fff; color: var(--nov-sub); font-size: 14px; font-weight: 600;
    cursor: pointer; transition: background .12s;
}
.btn-cancel:hover { background: var(--nov-bg); }
.btn-save {
    padding: 9px 22px; border-radius: 10px; border: none;
    background: var(--nov-blue); color: #fff; font-size: 14px; font-weight: 600;
    cursor: pointer; transition: opacity .15s;
}
.btn-save:hover { opacity: .88; }
.btn-save:disabled { opacity: .5; cursor: not-allowed; }
</style>
<?php
$cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php';
?>

<div class="nov-page">

    <div class="nov-header">
        <h1><i class="fas fa-bullhorn"></i> Novedades</h1>
        <button class="btn-nov-add" onclick="novAbrirModal()">
            <i class="fas fa-plus"></i> Nueva novedad
        </button>
    </div>
    <p class="nov-hint"><i class="fas fa-grip-vertical"></i> Arrastra las tarjetas para cambiar el orden en que aparecen en el dashboard</p>

    <div id="novGrid">
        <?php if (empty($novedades)): ?>
        <div class="nov-empty">
            <i class="fas fa-bullhorn"></i>
            <p>No hay novedades. Crea la primera.</p>
        </div>
        <?php else: ?>
        <div class="nov-grid" id="novSortable">
            <?php foreach ($novedades as $n): ?>
            <div class="nov-card-item <?php echo $n['activo'] ? '' : 'inactiva'; ?>"
                 data-id="<?php echo $n['id']; ?>">
                <div class="nov-card-drag-handle">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <div class="nov-card-top">
                    <div class="nov-card-titulo"><?php echo htmlspecialchars($n['titulo']); ?></div>
                    <div class="nov-card-contenido"><?php echo htmlspecialchars($n['contenido']); ?></div>
                </div>
                <div class="nov-card-foot">
                    <span class="<?php echo $n['activo'] ? 'nov-badge-activa' : 'nov-badge-inactiva'; ?>">
                        <?php echo $n['activo'] ? 'Activa' : 'Inactiva'; ?>
                    </span>
                    <div class="nov-actions">
                        <button class="nov-btn-icon nov-btn-edit" title="Editar"
                                onclick="novEditar(<?php echo $n['id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="nov-btn-icon nov-btn-toggle" title="Activar/Desactivar"
                                onclick="novToggle(<?php echo $n['id']; ?>)">
                            <i class="fas fa-<?php echo $n['activo'] ? 'eye-slash' : 'eye'; ?>"></i>
                        </button>
                        <button class="nov-btn-icon nov-btn-del" title="Eliminar"
                                onclick="novEliminar(<?php echo $n['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal crear/editar -->
<div class="nov-modal-overlay" id="novModal">
    <div class="nov-modal">
        <div class="nov-modal-head">
            <h3 id="novModalTitle">Nueva novedad</h3>
            <button class="nov-modal-close" onclick="novCerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="nov-modal-body">
            <input type="hidden" id="novId">
            <div class="nov-field">
                <label>Título</label>
                <input type="text" id="novTitulo" placeholder="Ej: Actualización del sistema" maxlength="255">
            </div>
            <div class="nov-field">
                <label>Contenido</label>
                <textarea id="novContenido" placeholder="Describe la novedad..."></textarea>
            </div>
            <div class="nov-field">
                <label>Estado</label>
                <select id="novActivo">
                    <option value="1">Activa</option>
                    <option value="0">Inactiva</option>
                </select>
            </div>
        </div>
        <div class="nov-modal-foot">
            <button class="btn-cancel" onclick="novCerrarModal()">Cancelar</button>
            <button class="btn-save" id="novBtnSave" onclick="novGuardar()">Guardar</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="nov-toast" id="novToast"></div>

<!-- Sortable.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const NOV_BASE = '<?php echo $basePath; ?>/novedades';

function novToast(msg, tipo = 'ok') {
    const el = document.getElementById('novToast');
    el.textContent = msg;
    el.className = 'nov-toast show' + (tipo === 'error' ? ' error' : '');
    setTimeout(() => el.className = 'nov-toast', 2800);
}

function novAbrirModal(id = null) {
    document.getElementById('novId').value = '';
    document.getElementById('novTitulo').value = '';
    document.getElementById('novContenido').value = '';
    document.getElementById('novActivo').value = 1;
    document.getElementById('novModalTitle').textContent = 'Nueva novedad';
    document.getElementById('novModal').classList.add('open');
    if (id) novCargarDatos(id);
    else document.getElementById('novTitulo').focus();
}

function novCerrarModal() {
    document.getElementById('novModal').classList.remove('open');
}

async function novCargarDatos(id) {
    const r = await fetch(`${NOV_BASE}/get?id=${id}`);
    const d = await r.json();
    if (!d.success) return;
    const n = d.novedad;
    document.getElementById('novId').value        = n.id;
    document.getElementById('novTitulo').value    = n.titulo;
    document.getElementById('novContenido').value = n.contenido;
    document.getElementById('novActivo').value    = n.activo;
    document.getElementById('novModalTitle').textContent = 'Editar novedad';
}

function novEditar(id) { novAbrirModal(id); }

async function novGuardar() {
    const id        = document.getElementById('novId').value;
    const titulo    = document.getElementById('novTitulo').value.trim();
    const contenido = document.getElementById('novContenido').value.trim();
    const activo    = parseInt(document.getElementById('novActivo').value);

    if (!titulo || !contenido) { novToast('Completa título y contenido', 'error'); return; }

    const btn = document.getElementById('novBtnSave');
    btn.disabled = true;

    const url  = id ? `${NOV_BASE}/actualizar` : `${NOV_BASE}/crear`;
    const body = { titulo, contenido, activo };
    if (id) body.id = parseInt(id);

    const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });
    const d = await r.json();
    btn.disabled = false;

    if (d.success) {
        novToast(d.message);
        novCerrarModal();
        setTimeout(() => location.reload(), 700);
    } else {
        novToast(d.message, 'error');
    }
}

async function novToggle(id) {
    const r = await fetch(`${NOV_BASE}/toggle`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });
    const d = await r.json();
    if (d.success) { novToast('Estado actualizado'); setTimeout(() => location.reload(), 700); }
    else novToast('Error al actualizar', 'error');
}

async function novEliminar(id) {
    if (!confirm('¿Eliminar esta novedad?')) return;
    const r = await fetch(`${NOV_BASE}/eliminar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });
    const d = await r.json();
    if (d.success) { novToast('Novedad eliminada'); setTimeout(() => location.reload(), 700); }
    else novToast('Error al eliminar', 'error');
}

// Drag & drop con Sortable.js
const sortableEl = document.getElementById('novSortable');
if (sortableEl) {
    Sortable.create(sortableEl, {
        animation: 180,
        ghostClass: 'sortable-ghost',
        dragClass:  'sortable-drag',
        handle:     '.nov-card-drag-handle',
        onEnd: async function() {
            const ids = [...sortableEl.querySelectorAll('.nov-card-item')]
                .map(el => parseInt(el.dataset.id));
            await fetch(`${NOV_BASE}/reordenar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids })
            });
            novToast('Orden guardado');
        }
    });
}

// Cerrar modal al hacer clic fuera
document.getElementById('novModal').addEventListener('click', function(e) {
    if (e.target === this) novCerrarModal();
});
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
