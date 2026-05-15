<?php
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Mensajes — Universidad del Atlántico';
$paginaActual = 'mensajes';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

$noLeidos = $noLeidos ?? 0;

ob_start();
?>
<style>
/* ── Variables ── */
:root {
    --msg-blue:     #0071e3;
    --msg-bg:       #f5f5f7;
    --msg-white:    #ffffff;
    --msg-border:   rgba(0,0,0,0.08);
    --msg-unread:   #e8f0fe;
    --msg-text:     #1d1d1f;
    --msg-sub:      #6e6e73;
    --msg-danger:   #ff3b30;
    --msg-green:    #34c759;
    --msg-radius:   14px;
}

/* ── Layout ── */
.msg-page {
    display: flex;
    height: calc(100vh - 156px);
    min-height: 400px;
    gap: 0;
    background: var(--msg-bg);
    border-radius: var(--msg-radius);
    overflow: visible;
    box-shadow: 0 2px 20px rgba(0,0,0,0.07);
    border: 1px solid var(--msg-border);
}

/* ── Panel izquierdo ── */
.msg-sidebar {
    width: 220px;
    min-width: 220px;
    background: var(--msg-white);
    border-right: 1px solid var(--msg-border);
    border-radius: var(--msg-radius) 0 0 var(--msg-radius);
    display: flex;
    flex-direction: column;
    padding: 20px 12px;
    gap: 4px;
    overflow: hidden;
}

.msg-compose-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--msg-blue);
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 16px;
    transition: background 0.2s, transform 0.15s;
    width: 100%;
    justify-content: center;
}
.msg-compose-btn:hover { background: #005ecb; transform: translateY(-1px); }
.msg-compose-btn i { font-size: 13px; }

.msg-folder {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 14px;
    border-radius: 10px;
    cursor: pointer;
    color: var(--msg-text);
    font-size: 14px;
    font-weight: 400;
    transition: background 0.15s;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}
.msg-folder:hover { background: rgba(0,113,227,0.07); }
.msg-folder.active {
    background: rgba(0,113,227,0.12);
    color: var(--msg-blue);
    font-weight: 600;
}
.msg-folder i { width: 16px; font-size: 14px; color: var(--msg-sub); }
.msg-folder.active i { color: var(--msg-blue); }
.msg-folder .folder-badge {
    margin-left: auto;
    background: var(--msg-blue);
    color: #fff;
    border-radius: 10px;
    padding: 1px 7px;
    font-size: 11px;
    font-weight: 700;
}

/* ── Panel derecho ── */
.msg-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: var(--msg-white);
    border-radius: 0 var(--msg-radius) var(--msg-radius) 0;
    min-width: 0;
    overflow: hidden;
}

/* ── Barra superior ── */
.msg-toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--msg-border);
    background: var(--msg-white);
}
.msg-toolbar-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--msg-text);
    letter-spacing: -0.3px;
    flex: 1;
}
.msg-search {
    display: flex;
    align-items: center;
    background: var(--msg-bg);
    border-radius: 10px;
    padding: 7px 12px;
    gap: 8px;
    border: 1px solid var(--msg-border);
    min-width: 200px;
}
.msg-search input {
    border: none;
    background: none;
    outline: none;
    font-size: 13px;
    color: var(--msg-text);
    width: 100%;
}
.msg-search i { color: var(--msg-sub); font-size: 13px; }

/* ── Lista de mensajes ── */
.msg-list-header {
    display: grid;
    grid-template-columns: 36px 44px 160px 1fr 90px;
    align-items: center;
    padding: 8px 20px;
    border-bottom: 1px solid var(--msg-border);
    font-size: 12px;
    font-weight: 600;
    color: var(--msg-sub);
    text-transform: uppercase;
    letter-spacing: 0.4px;
    background: #fafafa;
}

.msg-list { flex: 1; overflow-y: auto; overflow-x: hidden; }

.msg-item {
    display: grid;
    grid-template-columns: 36px 44px 160px 1fr 90px;
    align-items: center;
    padding: 0 20px;
    height: 52px;
    border-bottom: 1px solid var(--msg-border);
    cursor: pointer;
    transition: background 0.12s;
    position: relative;
}
.msg-item:hover { background: #f0f4ff; }
.msg-item.unread { background: var(--msg-unread); }
.msg-item.unread:hover { background: #d8e8fd; }

/* indicador de no leído */
.msg-item.unread::before {
    content: '';
    position: absolute;
    left: 0; top: 12px; bottom: 12px;
    width: 3px;
    background: var(--msg-blue);
    border-radius: 0 2px 2px 0;
}

.msg-check { display: flex; align-items: center; justify-content: center; }
.msg-check input[type=checkbox] {
    width: 15px; height: 15px; cursor: pointer;
    accent-color: var(--msg-blue);
}

.msg-avatar-cell { display: flex; align-items: center; justify-content: center; }
.msg-avatar-sm {
    width: 32px; height: 32px; border-radius: 50%;
    object-fit: cover;
    border: 1.5px solid var(--msg-border);
}
.msg-avatar-initials {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff;
}

.msg-from {
    font-size: 13.5px;
    font-weight: 400;
    color: var(--msg-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 8px;
}
.msg-item.unread .msg-from { font-weight: 700; }

.msg-subject-cell {
    display: flex;
    align-items: baseline;
    gap: 6px;
    min-width: 0;
}
.msg-subj {
    font-size: 13.5px;
    font-weight: 400;
    color: var(--msg-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 240px;
}
.msg-item.unread .msg-subj { font-weight: 700; }
.msg-preview {
    font-size: 13px;
    color: var(--msg-sub);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.msg-date {
    font-size: 12px;
    color: var(--msg-sub);
    text-align: right;
    white-space: nowrap;
}
.msg-item.unread .msg-date { color: var(--msg-blue); font-weight: 600; }

/* ── Estado vacío ── */
.msg-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 14px;
    color: var(--msg-sub);
    padding: 80px 20px;
    text-align: center;
    width: 100%;
}
.msg-empty i { font-size: 52px; opacity: 0.2; display: block; }
.msg-empty p { font-size: 15px; margin: 0; }

/* ── Modal Redactar ── */
.msg-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.35);
    backdrop-filter: blur(4px);
    z-index: 2000;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; visibility: hidden;
    transition: all 0.2s ease;
}
.msg-modal-overlay.show { opacity: 1; visibility: visible; }

.msg-modal {
    background: var(--msg-white);
    border-radius: 18px;
    width: 560px;
    max-width: 95vw;
    box-shadow: 0 24px 60px rgba(0,0,0,0.2);
    overflow: hidden;
    transform: scale(0.95) translateY(10px);
    transition: transform 0.2s cubic-bezier(0.34,1.56,0.64,1);
}
.msg-modal-overlay.show .msg-modal { transform: scale(1) translateY(0); }

.msg-modal-header {
    display: flex;
    align-items: center;
    padding: 18px 22px 14px;
    border-bottom: 1px solid var(--msg-border);
}
.msg-modal-header h5 {
    flex: 1;
    font-size: 17px;
    font-weight: 700;
    color: var(--msg-text);
    margin: 0;
    letter-spacing: -0.2px;
}
.msg-modal-close {
    width: 28px; height: 28px;
    border-radius: 50%;
    border: none;
    background: rgba(0,0,0,0.07);
    color: var(--msg-sub);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    transition: background 0.15s;
}
.msg-modal-close:hover { background: rgba(0,0,0,0.12); }

.msg-modal-body { padding: 18px 22px; display: flex; flex-direction: column; gap: 14px; }

.msg-field { display: flex; flex-direction: column; gap: 5px; }
.msg-field label { font-size: 12px; font-weight: 600; color: var(--msg-sub); text-transform: uppercase; letter-spacing: 0.5px; }

.msg-to-row { display: flex; gap: 8px; align-items: center; }
.msg-tipo-sel {
    padding: 9px 12px;
    border: 1.5px solid var(--msg-border);
    border-radius: 10px;
    font-size: 14px;
    background: var(--msg-bg);
    color: var(--msg-text);
    outline: none;
    min-width: 130px;
    cursor: pointer;
    transition: border-color 0.15s;
}
.msg-tipo-sel:focus { border-color: var(--msg-blue); }
.msg-dest-sel {
    flex: 1;
    padding: 9px 12px;
    border: 1.5px solid var(--msg-border);
    border-radius: 10px;
    font-size: 14px;
    background: var(--msg-bg);
    color: var(--msg-text);
    outline: none;
    cursor: pointer;
    transition: border-color 0.15s;
}
.msg-dest-sel:focus { border-color: var(--msg-blue); }

.msg-input {
    padding: 9px 13px;
    border: 1.5px solid var(--msg-border);
    border-radius: 10px;
    font-size: 14px;
    background: var(--msg-bg);
    color: var(--msg-text);
    outline: none;
    transition: border-color 0.15s;
}
.msg-input:focus { border-color: var(--msg-blue); background: #fff; }

.msg-textarea {
    padding: 11px 13px;
    border: 1.5px solid var(--msg-border);
    border-radius: 10px;
    font-size: 14px;
    background: var(--msg-bg);
    color: var(--msg-text);
    outline: none;
    resize: vertical;
    min-height: 130px;
    font-family: inherit;
    transition: border-color 0.15s;
}
.msg-textarea:focus { border-color: var(--msg-blue); background: #fff; }

.msg-modal-footer {
    padding: 14px 22px 18px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid var(--msg-border);
}
.btn-msg-cancel {
    padding: 9px 20px;
    border-radius: 10px;
    border: 1.5px solid var(--msg-border);
    background: #fff;
    color: var(--msg-sub);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-msg-cancel:hover { background: var(--msg-bg); }
.btn-msg-send {
    padding: 9px 22px;
    border-radius: 10px;
    border: none;
    background: var(--msg-blue);
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex; align-items: center; gap: 7px;
    transition: all 0.15s;
}
.btn-msg-send:hover { background: #005ecb; }
.btn-msg-send:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Modal Leer ── */
.msg-read-modal .msg-modal { width: 640px; }
.msg-read-header-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 22px;
    border-bottom: 1px solid var(--msg-border);
}
.msg-read-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--msg-border);
}
.msg-read-avatar-initials {
    width: 42px; height: 42px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700; color: #fff;
}
.msg-read-meta { flex: 1; }
.msg-read-from { font-size: 15px; font-weight: 700; color: var(--msg-text); }
.msg-read-role { font-size: 12px; color: var(--msg-sub); }
.msg-read-date { font-size: 12px; color: var(--msg-sub); }
.msg-read-subject {
    padding: 14px 22px 0;
    font-size: 19px;
    font-weight: 700;
    color: var(--msg-text);
    letter-spacing: -0.3px;
}
.msg-read-body {
    padding: 14px 22px 20px;
    font-size: 15px;
    line-height: 1.65;
    color: var(--msg-text);
    white-space: pre-wrap;
    min-height: 100px;
}

/* ── Autocomplete ── */
.msg-autocomplete-wrap {
    flex: 1;
    position: relative;
}
.msg-autocomplete-input {
    width: 100%;
    padding: 9px 13px;
    border: 1.5px solid var(--msg-border);
    border-radius: 10px;
    font-size: 14px;
    background: var(--msg-bg);
    color: var(--msg-text);
    outline: none;
    transition: border-color 0.15s;
    box-sizing: border-box;
}
.msg-autocomplete-input:focus { border-color: var(--msg-blue); background: #fff; }
.msg-autocomplete-input.has-value { border-color: var(--msg-blue); }

.msg-autocomplete-list {
    position: absolute;
    top: calc(100% + 4px);
    left: 0; right: 0;
    background: #fff;
    border: 1.5px solid rgba(0,113,227,0.25);
    border-radius: 10px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.10);
    z-index: 3000;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}
.msg-autocomplete-list.open { display: block; }

.msg-ac-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 13px;
    cursor: pointer;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    transition: background 0.1s;
}
.msg-ac-item:last-child { border-bottom: none; }
.msg-ac-item:hover, .msg-ac-item.focused { background: #eef4ff; }

.msg-ac-av {
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.msg-ac-info { flex: 1; min-width: 0; }
.msg-ac-nombre { font-size: 13.5px; font-weight: 500; color: var(--msg-text); }
.msg-ac-rol    { font-size: 11.5px; color: var(--msg-sub); }
.msg-ac-empty  { padding: 12px 13px; color: var(--msg-sub); font-size: 13px; }

/* ── Tipo badge ── */
.tipo-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;
}
.tipo-badge.usuario     { background: #e3f0ff; color: var(--msg-blue); }
.tipo-badge.dependencia { background: #fff3e0; color: #f57c00; }
.tipo-badge.rol         { background: #f3e8ff; color: #7c3aed; }
</style>
<?php
$cssExtra = ob_get_clean();

require_once __DIR__ . '/../complementos/header.php';

// Colores para avatares por iniciales
$avatarColors = ['#007AFF','#34C759','#FF9500','#FF3B30','#AF52DE','#5856D6','#32ADE6','#FF2D55'];

function getInitials(string $nombre): string {
    $parts = explode(' ', trim($nombre));
    $i = strtoupper(substr($parts[0], 0, 1));
    $i .= isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) : '';
    return $i;
}

function getAvatarColor(string $nombre, array $colors): string {
    return $colors[abs(crc32($nombre)) % count($colors)];
}
?>

<!-- ═══════════════════════════ PÁGINA ═══════════════════════════ -->
<div class="msg-page">

    <!-- ── Sidebar izquierdo ── -->
    <aside class="msg-sidebar">
        <button class="msg-compose-btn" id="btnRedactar">
            <i class="fas fa-pen"></i> Redactar
        </button>
        <button class="msg-folder active" data-folder="recibidos">
            <i class="fas fa-inbox"></i> Recibidos
            <?php if ($noLeidos > 0): ?>
                <span class="folder-badge" id="sidebarBadge"><?php echo $noLeidos; ?></span>
            <?php else: ?>
                <span class="folder-badge" id="sidebarBadge" style="display:none">0</span>
            <?php endif; ?>
        </button>
        <button class="msg-folder" data-folder="enviados">
            <i class="fas fa-paper-plane"></i> Enviados
        </button>
    </aside>

    <!-- ── Panel principal ── -->
    <div class="msg-main">
        <!-- Toolbar -->
        <div class="msg-toolbar">
            <span class="msg-toolbar-title" id="toolbarTitle">Recibidos</span>
            <div class="msg-search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar mensajes…">
            </div>
        </div>

        <!-- Cabecera de columnas -->
        <div class="msg-list-header">
            <div><input type="checkbox" id="selectAll" title="Seleccionar todo"></div>
            <div></div>
            <div>De</div>
            <div>Asunto</div>
            <div style="text-align:right">Fecha</div>
        </div>

        <!-- Lista -->
        <div class="msg-list" id="msgList">
            <?php
            // Render inicial: Recibidos
            $lista = $recibidos;
            if (empty($lista)):
            ?>
                <div class="msg-empty" id="emptyState">
                    <i class="fas fa-envelope-open"></i>
                    <p>No tienes mensajes recibidos</p>
                </div>
            <?php else: foreach ($lista as $msg):
                $leido   = (int)($msg['leido'] ?? 0);
                $cls     = $leido ? '' : 'unread';
                $nombre  = $msg['remitente_nombre'] ?? 'Usuario';
                $avatar  = $msg['remitente_avatar'] ?? '';
                $color   = getAvatarColor($nombre, $avatarColors);
                $initials = getInitials($nombre);
                $fechaRaw = $msg['fecha_envio'] ?? '';
                $ts  = $fechaRaw ? strtotime($fechaRaw) : 0;
                $hoy = strtotime('today');
                $ayer = strtotime('yesterday');
                if ($ts >= $hoy) {
                    $fechaLabel = date('H:i', $ts);
                } elseif ($ts >= $ayer) {
                    $fechaLabel = 'Ayer';
                } else {
                    $fechaLabel = date('d/m/Y', $ts);
                }
                $asunto  = htmlspecialchars($msg['asunto'] ?? '');
                $cuerpo  = htmlspecialchars($msg['cuerpo'] ?? '');
                $preview = mb_substr(strip_tags($cuerpo), 0, 60);
            ?>
            <div class="msg-item <?php echo $cls; ?>"
                 data-id="<?php echo (int)$msg['id']; ?>"
                 data-folder="recibidos"
                 title="<?php echo $asunto; ?>">
                <div class="msg-check">
                    <input type="checkbox" class="msg-cb" data-id="<?php echo (int)$msg['id']; ?>">
                </div>
                <div class="msg-avatar-cell">
                    <?php if ($avatar && $avatar !== 'default.png'): ?>
                        <img src="<?php echo $baseUrl; ?>/assets/media/users/<?php echo htmlspecialchars($avatar); ?>"
                             class="msg-avatar-sm"
                             onerror="this.outerHTML='<div class=\'msg-avatar-initials\' style=\'background:<?php echo $color; ?>\'><?php echo $initials; ?></div>'">
                    <?php else: ?>
                        <div class="msg-avatar-initials" style="background:<?php echo $color; ?>"><?php echo $initials; ?></div>
                    <?php endif; ?>
                </div>
                <div class="msg-from"><?php echo htmlspecialchars($nombre); ?></div>
                <div class="msg-subject-cell">
                    <span class="msg-subj"><?php echo $asunto; ?></span>
                    <?php if ($preview): ?>
                        <span class="msg-preview">— <?php echo htmlspecialchars($preview); ?></span>
                    <?php endif; ?>
                </div>
                <div class="msg-date"><?php echo $fechaLabel; ?></div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<!-- ════════════════ MODAL REDACTAR ════════════════ -->
<div class="msg-modal-overlay" id="modalRedactar">
    <div class="msg-modal">
        <div class="msg-modal-header">
            <h5><i class="fas fa-pen" style="color:var(--msg-blue);margin-right:8px"></i>Nuevo mensaje</h5>
            <button class="msg-modal-close" id="closeRedactar"><i class="fas fa-times"></i></button>
        </div>
        <div class="msg-modal-body">
            <div class="msg-field">
                <label>Para</label>
                <div class="msg-to-row">
                    <select class="msg-tipo-sel" id="tipoDestinatario">
                        <option value="usuario">Persona</option>
                        <option value="dependencia">Dependencia</option>
                        <option value="rol">Rol</option>
                    </select>

                    <!-- Autocomplete para Persona -->
                    <div class="msg-autocomplete-wrap" id="acWrap">
                        <input type="text"   class="msg-autocomplete-input" id="acInput"  placeholder="Buscar persona…" autocomplete="off">
                        <input type="hidden" id="destinatarioId" name="destinatario_id">
                        <div class="msg-autocomplete-list" id="acList"></div>
                    </div>

                    <!-- Select normal para Dependencia / Rol -->
                    <select class="msg-dest-sel" id="destSelect" style="display:none">
                        <option value="">— Seleccionar —</option>
                    </select>
                </div>
            </div>
            <div class="msg-field">
                <label>Asunto</label>
                <input type="text" class="msg-input" id="asunto" placeholder="Escribe el asunto…" maxlength="255">
            </div>
            <div class="msg-field">
                <label>Mensaje</label>
                <textarea class="msg-textarea" id="cuerpo" placeholder="Escribe tu mensaje aquí…"></textarea>
            </div>
        </div>
        <div class="msg-modal-footer">
            <button class="btn-msg-cancel" id="cancelRedactar">Cancelar</button>
            <button class="btn-msg-send" id="btnEnviar">
                <i class="fas fa-paper-plane"></i> Enviar
            </button>
        </div>
    </div>
</div>

<!-- ════════════════ MODAL LEER ════════════════ -->
<div class="msg-modal-overlay msg-read-modal" id="modalLeer">
    <div class="msg-modal">
        <div class="msg-modal-header" style="padding-bottom:10px">
            <h5 id="readTitle" style="font-size:15px;color:#6e6e73;font-weight:500">Cargando…</h5>
            <button class="msg-modal-close" id="closeLeer"><i class="fas fa-times"></i></button>
        </div>
        <div class="msg-read-header-meta">
            <div id="readAvatarWrap"></div>
            <div class="msg-read-meta">
                <div class="msg-read-from" id="readFrom"></div>
                <div class="msg-read-role" id="readRole"></div>
            </div>
            <div class="msg-read-date" id="readDate"></div>
        </div>
        <div class="msg-read-subject" id="readSubject"></div>
        <div class="msg-read-body" id="readBody"></div>
        <div class="msg-modal-footer" style="justify-content:space-between">
            <button class="btn-msg-cancel" id="btnResponder" style="display:none">
                <i class="fas fa-reply"></i> Responder
            </button>
            <button class="btn-msg-cancel" id="closeLeer2">Cerrar</button>
        </div>
    </div>
</div>

<script>
(function() {
    const basePath    = '<?php echo $basePath; ?>';
    const baseUrl     = '<?php echo $baseUrl; ?>';
    const avatarColors = <?php echo json_encode($avatarColors); ?>;

    /* ── Datos en memoria ── */
    const data = {
        recibidos: <?php echo json_encode(array_values($recibidos)); ?>,
        enviados:  <?php echo json_encode(array_values($enviados));  ?>
    };

    let carpetaActiva = 'recibidos';

    /* ── Opciones de destinatario por tipo ── */
    const opcionesPorTipo = {
        usuario: <?php echo json_encode(array_map(fn($u) => [
            'value' => (string)$u['id'],
            'label' => $u['nombre'] . ' (' . ucfirst($u['rol']) . ')'
        ], $usuarios)); ?>,
        dependencia: <?php echo json_encode(array_map(fn($d) => [
            'value' => (string)$d['id'],
            'label' => $d['nombre']
        ], $dependencias)); ?>,
        rol: <?php echo json_encode(array_map(fn($r) => [
            'value' => $r['slug'],
            'label' => $r['nombre']
        ], $roles)); ?>
    };

    /* ═══════════ HELPERS ═══════════ */
    function getInitials(nombre) {
        const p = (nombre || '').trim().split(' ');
        return (p[0]?.[0] || '').toUpperCase() + (p[1]?.[0] || '').toUpperCase();
    }
    function getColor(nombre) {
        let h = 0;
        for (let i = 0; i < nombre.length; i++) h = nombre.charCodeAt(i) + ((h << 5) - h);
        return avatarColors[Math.abs(h) % avatarColors.length];
    }
    function avatarHtml(avatar, nombre, size) {
        const sz  = size || 32;
        const init = getInitials(nombre);
        const col  = getColor(nombre);
        if (avatar && avatar !== 'default.png') {
            return `<img src="${baseUrl}/assets/media/users/${avatar}"
                         style="width:${sz}px;height:${sz}px;border-radius:50%;object-fit:cover;border:1.5px solid rgba(0,0,0,.08)"
                         onerror="this.outerHTML='<div style=\'width:${sz}px;height:${sz}px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:${Math.floor(sz*0.38)}px;font-weight:700;color:#fff;background:${col}\'>${init}</div>'">`;
        }
        return `<div style="width:${sz}px;height:${sz}px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:${Math.floor(sz*0.38)}px;font-weight:700;color:#fff;background:${col}">${init}</div>`;
    }
    function fechaLabel(raw) {
        if (!raw) return '';
        const d   = new Date(raw.replace(' ', 'T'));
        const now = new Date();
        const hoy = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const ayer = new Date(hoy); ayer.setDate(ayer.getDate() - 1);
        if (d >= hoy)  return d.toLocaleTimeString('es-CO', {hour:'2-digit', minute:'2-digit'});
        if (d >= ayer) return 'Ayer';
        return d.toLocaleDateString('es-CO', {day:'2-digit', month:'2-digit', year:'numeric'});
    }

    /* ═══════════ RENDER LISTA ═══════════ */
    function renderLista(carpeta) {
        const lista  = data[carpeta] || [];
        const search = (document.getElementById('searchInput').value || '').toLowerCase();
        const filtrada = lista.filter(m => {
            const asunto = (m.asunto || '').toLowerCase();
            const from   = (m.remitente_nombre || m.destinatario_nombre || '').toLowerCase();
            return !search || asunto.includes(search) || from.includes(search);
        });

        const container = document.getElementById('msgList');
        if (filtrada.length === 0) {
            container.innerHTML = `<div class="msg-empty">
                <i class="fas fa-envelope-open"></i>
                <p>No hay mensajes en esta carpeta</p>
            </div>`;
            return;
        }

        container.innerHTML = filtrada.map(m => {
            const leido   = parseInt(m.leido) === 1;
            const cls     = leido ? '' : 'unread';
            const nombre  = carpeta === 'enviados'
                ? (m.destinatario_nombre || 'Desconocido')
                : (m.remitente_nombre    || 'Desconocido');
            const avHtml  = avatarHtml(
                carpeta === 'enviados' ? '' : (m.remitente_avatar || ''),
                nombre, 32
            );
            const asunto  = escHtml(m.asunto || '');
            const preview = escHtml((m.cuerpo || '').replace(/<[^>]+>/g,'').substring(0, 60));
            const fecha   = fechaLabel(m.fecha_envio);

            return `<div class="msg-item ${cls}" data-id="${m.id}" data-folder="${carpeta}" title="${asunto}">
                <div class="msg-check"><input type="checkbox" class="msg-cb" data-id="${m.id}"></div>
                <div class="msg-avatar-cell">${avHtml}</div>
                <div class="msg-from">${escHtml(nombre)}</div>
                <div class="msg-subject-cell">
                    <span class="msg-subj">${asunto}</span>
                    ${preview ? `<span class="msg-preview">— ${preview}</span>` : ''}
                </div>
                <div class="msg-date">${fecha}</div>
            </div>`;
        }).join('');

        // Re-bind clicks
        container.querySelectorAll('.msg-item').forEach(el => {
            el.addEventListener('click', function(e) {
                if (e.target.type === 'checkbox') return;
                abrirMensaje(parseInt(this.dataset.id), this.dataset.folder);
            });
        });
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ═══════════ CARPETAS ═══════════ */
    document.querySelectorAll('.msg-folder').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.msg-folder').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            carpetaActiva = this.dataset.folder;
            document.getElementById('toolbarTitle').textContent =
                carpetaActiva === 'recibidos' ? 'Recibidos' : 'Enviados';
            renderLista(carpetaActiva);
        });
    });

    /* ═══════════ BÚSQUEDA ═══════════ */
    document.getElementById('searchInput').addEventListener('input', () => renderLista(carpetaActiva));

    /* ═══════════ SELECT ALL ═══════════ */
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.msg-cb').forEach(cb => cb.checked = this.checked);
    });

    /* ═══════════ CLICK ÍTEM ═══════════ */
    document.querySelectorAll('.msg-item').forEach(el => {
        el.addEventListener('click', function(e) {
            if (e.target.type === 'checkbox') return;
            abrirMensaje(parseInt(this.dataset.id), this.dataset.folder);
        });
    });

    /* ═══════════ ABRIR MENSAJE ═══════════ */
    function abrirMensaje(id, folder) {
        const overlay = document.getElementById('modalLeer');
        overlay.classList.add('show');
        document.getElementById('readTitle').textContent = 'Cargando…';
        document.getElementById('readSubject').textContent = '';
        document.getElementById('readBody').textContent = '';

        fetch(`${basePath}/mensajes/ver?id=${id}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                const m = res.mensaje;
                const nombre = m.remitente_nombre || 'Desconocido';

                document.getElementById('readTitle').textContent = folder === 'enviados' ? 'Enviado' : 'Recibido';
                document.getElementById('readAvatarWrap').innerHTML = avatarHtml(m.remitente_avatar, nombre, 42);
                document.getElementById('readFrom').textContent = nombre;
                document.getElementById('readRole').textContent = ucFirst(m.remitente_rol || '');
                document.getElementById('readDate').textContent = fechaLabel(m.fecha_envio);
                document.getElementById('readSubject').textContent = m.asunto || '';
                document.getElementById('readBody').textContent = m.cuerpo || '';

                // Marcar ítem como leído en UI
                const item = document.querySelector(`.msg-item[data-id="${id}"]`);
                if (item) item.classList.remove('unread');

                // Actualizar datos locales
                const arr = data[folder] || [];
                const idx = arr.findIndex(x => parseInt(x.id) === id);
                if (idx >= 0) arr[idx].leido = 1;

                actualizarBadge();
            });
    }

    function ucFirst(s) { return s ? s[0].toUpperCase() + s.slice(1) : ''; }

    /* ═══════════ MODAL LEER: cerrar ═══════════ */
    ['closeLeer','closeLeer2'].forEach(id => {
        document.getElementById(id).addEventListener('click', () => {
            document.getElementById('modalLeer').classList.remove('show');
        });
    });
    document.getElementById('modalLeer').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('show');
    });

    /* ═══════════ MODAL REDACTAR ═══════════ */
    function abrirRedactar() {
        document.getElementById('asunto').value = '';
        document.getElementById('cuerpo').value = '';
        document.getElementById('tipoDestinatario').value = 'usuario';
        actualizarDestSelector('usuario');
        document.getElementById('modalRedactar').classList.add('show');
        setTimeout(() => acInput.focus(), 150);
    }

    document.getElementById('btnRedactar').addEventListener('click', abrirRedactar);
    document.getElementById('cancelRedactar').addEventListener('click', cerrarRedactar);
    document.getElementById('closeRedactar').addEventListener('click', cerrarRedactar);
    document.getElementById('modalRedactar').addEventListener('click', function(e) {
        if (e.target === this) cerrarRedactar();
    });
    function cerrarRedactar() {
        document.getElementById('modalRedactar').classList.remove('show');
    }

    /* ═══════════ AUTOCOMPLETE DE PERSONA ═══════════ */
    const acInput  = document.getElementById('acInput');
    const acList   = document.getElementById('acList');
    const acWrap   = document.getElementById('acWrap');
    const destHide = document.getElementById('destinatarioId');
    const destSel  = document.getElementById('destSelect');

    let acFocusIdx = -1;

    function acRenderSuggestions(q) {
        const opts = opcionesPorTipo['usuario'] || [];
        const lower = q.toLowerCase().trim();
        const filtered = lower
            ? opts.filter(o => o.label.toLowerCase().includes(lower))
            : opts;

        acFocusIdx = -1;
        if (!filtered.length) {
            acList.innerHTML = '<div class="msg-ac-empty">Sin resultados</div>';
            acList.classList.add('open');
            return;
        }
        acList.innerHTML = filtered.map((o, i) => {
            const parts = o.label.split(' (');
            const nombre = parts[0] || o.label;
            const rol    = parts[1] ? parts[1].replace(')','') : '';
            const init   = nombre.trim().split(' ').map(w => w[0]||'').join('').toUpperCase().slice(0,2);
            const col    = avatarColors[Math.abs([...nombre].reduce((a,c) => a+c.charCodeAt(0),0)) % avatarColors.length];
            return `<div class="msg-ac-item" data-value="${escHtml(o.value)}" data-label="${escHtml(nombre)}" data-idx="${i}">
                <div class="msg-ac-av" style="background:${col}">${init}</div>
                <div class="msg-ac-info">
                    <div class="msg-ac-nombre">${escHtml(nombre)}</div>
                    ${rol ? `<div class="msg-ac-rol">${escHtml(rol)}</div>` : ''}
                </div>
            </div>`;
        }).join('');
        acList.classList.add('open');

        acList.querySelectorAll('.msg-ac-item').forEach(el => {
            el.addEventListener('mousedown', function(e) {
                e.preventDefault();
                acSelectItem(this.dataset.value, this.dataset.label);
            });
        });
    }

    function acSelectItem(value, label) {
        acInput.value   = label;
        destHide.value  = value;
        acInput.classList.add('has-value');
        acList.classList.remove('open');
        acFocusIdx = -1;
    }

    function acClear() {
        acInput.value  = '';
        destHide.value = '';
        acInput.classList.remove('has-value');
        acList.classList.remove('open');
    }

    acInput.addEventListener('input', function() {
        destHide.value = '';
        acInput.classList.remove('has-value');
        acRenderSuggestions(this.value);
    });
    acInput.addEventListener('focus', function() {
        acRenderSuggestions(this.value);
    });
    acInput.addEventListener('blur', function() {
        setTimeout(() => acList.classList.remove('open'), 150);
    });
    // Teclado: arriba/abajo/enter/escape
    acInput.addEventListener('keydown', function(e) {
        const items = acList.querySelectorAll('.msg-ac-item');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            acFocusIdx = Math.min(acFocusIdx + 1, items.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            acFocusIdx = Math.max(acFocusIdx - 1, 0);
        } else if (e.key === 'Enter' && acFocusIdx >= 0) {
            e.preventDefault();
            const el = items[acFocusIdx];
            if (el) acSelectItem(el.dataset.value, el.dataset.label);
            return;
        } else if (e.key === 'Escape') {
            acList.classList.remove('open');
            return;
        } else { return; }
        items.forEach((el, i) => el.classList.toggle('focused', i === acFocusIdx));
        if (items[acFocusIdx]) items[acFocusIdx].scrollIntoView({block:'nearest'});
    });

    /* ── Cambiar tipo de destinatario ── */
    document.getElementById('tipoDestinatario').addEventListener('change', function() {
        actualizarDestSelector(this.value);
    });

    function actualizarDestSelector(tipo) {
        if (tipo === 'usuario') {
            acWrap.style.display  = '';
            destSel.style.display = 'none';
            acClear();
        } else {
            acWrap.style.display  = 'none';
            destSel.style.display = '';
            destHide.value = '';
            const opts = opcionesPorTipo[tipo] || [];
            destSel.innerHTML = '<option value="">— Seleccionar —</option>' +
                opts.map(o => `<option value="${escHtml(o.value)}">${escHtml(o.label)}</option>`).join('');
            destSel.onchange = () => { destHide.value = destSel.value; };
        }
    }

    /* ═══════════ ENVIAR MENSAJE ═══════════ */
    document.getElementById('btnEnviar').addEventListener('click', function() {
        const tipo  = document.getElementById('tipoDestinatario').value;
        const dest  = document.getElementById('destinatarioId').value;
        const asunto = document.getElementById('asunto').value.trim();
        const cuerpo = document.getElementById('cuerpo').value.trim();

        if (!dest)   { flash('Selecciona un destinatario', 'warning'); return; }
        if (!asunto) { flash('Escribe el asunto', 'warning'); return; }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando…';

        const fd = new FormData();
        fd.append('tipo_destinatario', tipo);
        fd.append('destinatario_id',   dest);
        fd.append('asunto',  asunto);
        fd.append('cuerpo',  cuerpo);

        fetch(`${basePath}/mensajes/crear`, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar';
                if (res.success) {
                    cerrarRedactar();
                    flash('Mensaje enviado correctamente', 'success');
                    // Recargar enviados
                    fetch(`${basePath}/mensajes/listar?tab=enviados`)
                        .then(r => r.json())
                        .then(r2 => {
                            if (r2.success) {
                                data.enviados = r2.mensajes;
                                if (carpetaActiva === 'enviados') renderLista('enviados');
                            }
                        });
                } else {
                    flash(res.message || 'Error al enviar', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar';
                flash('Error de conexión', 'error');
            });
    });

    /* ═══════════ BADGE CAMPANITA ═══════════ */
    function actualizarBadge() {
        const noLeidos = (data.recibidos || []).filter(m => parseInt(m.leido) === 0).length;
        // Sidebar badge
        const sb = document.getElementById('sidebarBadge');
        if (sb) {
            sb.textContent = noLeidos;
            sb.style.display = noLeidos > 0 ? '' : 'none';
        }
        // Header bell (global)
        if (window.actualizarBellBadge) window.actualizarBellBadge(noLeidos);
    }

    /* ═══════════ FLASH ═══════════ */
    function flash(msg, type) {
        if (window.Swal) {
            Swal.fire({ icon: type, title: msg, timer: 2200, showConfirmButton: false, toast: true, position: 'top-end' });
        } else {
            alert(msg);
        }
    }

    // Init
    actualizarBadge();
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
