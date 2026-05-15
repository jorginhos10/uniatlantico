<?php
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/config.php';

$titulo       = 'Dashboard — Universidad del Atlántico';
$paginaActual = 'dashboard';

$basePath = Config::getBasePath();
$baseUrl  = Config::getBaseUrl();
$uid      = (int)($_SESSION['usuario_id'] ?? 0);
$urol     = $_SESSION['usuario_rol'] ?? '';
$unombre  = $_SESSION['usuario_nombre'] ?? 'Usuario';

// ── Conexión única ───────────────────────────────────────────────────────
$pdo = null;
try {
    $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
    $pdo = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Dashboard DB: " . $e->getMessage());
}

$esAdmin = ($urol === 'admin');

// ── Stats — cada una aislada para que un fallo no rompa las demás ─────────
$stats = [
    'formularios'   => 0,   // formularios activos (global)
    'mis_registros' => 0,   // registros del módulo 144 creados por este usuario
    'borradores'    => 0,   // sus borradores pendientes
    'usuarios'      => 0,   // usuarios del sistema (admin) / sus colegas (otros)
    'mensajes_nl'   => 0,   // mensajes sin leer para este usuario
    'dependencias'  => 0,   // dependencias activas
];
$actividad   = [];
$userStats   = [];
$msgRecentes = [];

if ($pdo) {

    // 1. Formularios FOR-DE-144 activos
    try {
        $stats['formularios'] = (int)$pdo->query(
            "SELECT COUNT(*) FROM formularios WHERE estado = 1"
        )->fetchColumn();
    } catch (PDOException $e) { error_log("Dash formularios: " . $e->getMessage()); }

    // 2. Mis registros en Módulo 144 (creados por mí)
    try {
        $s = $pdo->prepare("SELECT COUNT(*) FROM formulacion_144 WHERE creado_por = :uid");
        $s->execute([':uid' => $uid]);
        $stats['mis_registros'] = (int)$s->fetchColumn();
    } catch (PDOException $e) { error_log("Dash mis_registros: " . $e->getMessage()); }

    // 3. Mis borradores pendientes (estado = 0)
    try {
        $s = $pdo->prepare("SELECT COUNT(*) FROM formulacion_144 WHERE creado_por = :uid AND estado = 0");
        $s->execute([':uid' => $uid]);
        $stats['borradores'] = (int)$s->fetchColumn();
    } catch (PDOException $e) { error_log("Dash borradores: " . $e->getMessage()); }

    // 4. Usuarios: admin ve todos; otros ven cuántos hay con su mismo rol
    try {
        if ($esAdmin) {
            $stats['usuarios'] = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE activo = 1")->fetchColumn();
        } else {
            $s = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE activo = 1 AND rol = :rol");
            $s->execute([':rol' => $urol]);
            $stats['usuarios'] = (int)$s->fetchColumn();
        }
    } catch (PDOException $e) { error_log("Dash usuarios: " . $e->getMessage()); }

    // 5. Mis mensajes no leídos
    try {
        $s = $pdo->prepare(
            "SELECT COUNT(*) FROM mensajes m
             LEFT JOIN mensajes_leidos ml ON ml.mensaje_id = m.id AND ml.usuario_id = :uid
             WHERE ml.usuario_id IS NULL
               AND m.remitente_id != :uid2
               AND (
                 (m.tipo_destinatario = 'usuario' AND CAST(m.destinatario_id AS UNSIGNED) = :uid3)
              OR (m.tipo_destinatario = 'rol'     AND m.destinatario_id = :rol)
               )"
        );
        $s->execute([':uid'=>$uid, ':uid2'=>$uid, ':uid3'=>$uid, ':rol'=>$urol]);
        $stats['mensajes_nl'] = (int)$s->fetchColumn();
    } catch (PDOException $e) { error_log("Dash mensajes_nl: " . $e->getMessage()); }

    // 6. Dependencias activas
    try {
        $stats['dependencias'] = (int)$pdo->query("SELECT COUNT(*) FROM cargos WHERE activo = 1")->fetchColumn();
    } catch (PDOException $e) { error_log("Dash dependencias: " . $e->getMessage()); }

    // 7. Actividad reciente: mis últimos 6 registros
    try {
        $s = $pdo->prepare(
            "SELECT f.nombre_borrador AS nombre,
                    CASE f.estado WHEN 0 THEN 'borrador' ELSE 'publicado' END AS estado,
                    f.fecha_creacion,
                    'formulacion' AS modulo,
                    u.nombre AS creado_por_nombre
             FROM formulacion_144 f
             LEFT JOIN usuarios u ON u.id = f.creado_por
             WHERE f.creado_por = :uid
             ORDER BY f.fecha_creacion DESC
             LIMIT 6"
        );
        $s->execute([':uid' => $uid]);
        $actividad = $s->fetchAll();
    } catch (PDOException $e) { error_log("Dash actividad: " . $e->getMessage()); }

    // 8. Distribución usuarios por rol (admin: todos; otros: su mismo rol)
    try {
        if ($esAdmin) {
            $userStats = $pdo->query(
                "SELECT rol, COUNT(*) as total FROM usuarios WHERE activo=1 GROUP BY rol ORDER BY total DESC"
            )->fetchAll();
        } else {
            $s = $pdo->prepare(
                "SELECT rol, COUNT(*) as total FROM usuarios WHERE activo=1 AND rol = :rol GROUP BY rol"
            );
            $s->execute([':rol' => $urol]);
            $userStats = $s->fetchAll();
        }
    } catch (PDOException $e) { error_log("Dash userStats: " . $e->getMessage()); }

    // 9. Últimos mensajes no leídos (widget)
    try {
        $s = $pdo->prepare(
            "SELECT m.asunto, m.fecha_envio, u.nombre AS de
             FROM mensajes m
             JOIN usuarios u ON u.id = m.remitente_id
             LEFT JOIN mensajes_leidos ml ON ml.mensaje_id = m.id AND ml.usuario_id = :uid
             WHERE ml.usuario_id IS NULL
               AND m.remitente_id != :uid2
               AND (
                 (m.tipo_destinatario = 'usuario' AND CAST(m.destinatario_id AS UNSIGNED) = :uid3)
              OR (m.tipo_destinatario = 'rol'     AND m.destinatario_id = :rol)
               )
             ORDER BY m.fecha_envio DESC LIMIT 4"
        );
        $s->execute([':uid'=>$uid, ':uid2'=>$uid, ':uid3'=>$uid, ':rol'=>$urol]);
        $msgRecentes = $s->fetchAll();
    } catch (PDOException $e) { error_log("Dash msgRecentes: " . $e->getMessage()); }

}

// Hora del saludo
$hora = (int)date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');

ob_start();
?>
<style>
/* ── Variables ── */
:root {
    --d-blue:   #007AFF;
    --d-green:  #34C759;
    --d-orange: #FF9500;
    --d-red:    #FF3B30;
    --d-purple: #AF52DE;
    --d-teal:   #32ADE6;
    --d-bg:     #F2F2F7;
    --d-card:   #FFFFFF;
    --d-border: rgba(0,0,0,0.07);
    --d-text:   #1d1d1f;
    --d-sub:    #6e6e73;
    --d-r:      14px;
}

/* ── Grid principal ── */
.db-page { display: flex; flex-direction: column; gap: 22px; }

/* ── Bienvenida ── */
.db-welcome {
    background: linear-gradient(135deg, #007AFF 0%, #5856D6 100%);
    border-radius: var(--d-r);
    padding: 26px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #fff;
    box-shadow: 0 4px 20px rgba(0,122,255,.3);
}
.db-welcome-left h1 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 4px;
    letter-spacing: -0.4px;
}
.db-welcome-left p { margin: 0; font-size: 14px; opacity: .82; }
.db-welcome-right {
    font-size: 13px;
    opacity: .78;
    text-align: right;
    line-height: 1.6;
}
.db-welcome-right strong { font-size: 15px; font-weight: 700; opacity: 1; }

/* ── Tarjetas de stats ── */
.db-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
    gap: 14px;
}
.db-stat {
    background: var(--d-card);
    border-radius: var(--d-r);
    padding: 20px 18px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    border: 1px solid var(--d-border);
    transition: transform .15s, box-shadow .15s;
    cursor: default;
}
.db-stat:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,0,0,.08); }
.db-stat-icon {
    width: 40px; height: 40px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.db-stat-val  { font-size: 30px; font-weight: 700; color: var(--d-text); letter-spacing: -1px; line-height: 1; }
.db-stat-lbl  { font-size: 12.5px; color: var(--d-sub); font-weight: 500; }
.db-stat-note { font-size: 11px; color: var(--d-sub); opacity: .7; }

/* ── Contenido inferior (2 columnas) ── */
.db-cols {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 14px;
    align-items: start;
}
@media (max-width: 900px) { .db-cols { grid-template-columns: 1fr; } }

/* ── Panel genérico ── */
.db-panel {
    background: var(--d-card);
    border-radius: var(--d-r);
    border: 1px solid var(--d-border);
    overflow: hidden;
}
.db-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 18px 12px;
    border-bottom: 1px solid var(--d-border);
}
.db-panel-head h3 {
    font-size: 15px;
    font-weight: 700;
    color: var(--d-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.db-panel-head h3 i { font-size: 14px; }
.db-panel-link {
    font-size: 12px;
    font-weight: 600;
    color: var(--d-blue);
    text-decoration: none;
    padding: 4px 10px;
    border-radius: 8px;
    background: rgba(0,122,255,.07);
    transition: background .15s;
}
.db-panel-link:hover { background: rgba(0,122,255,.14); }

/* ── Actividad reciente ── */
.db-act-list { padding: 0; list-style: none; margin: 0; }
.db-act-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 18px;
    border-bottom: 1px solid var(--d-border);
    transition: background .12s;
}
.db-act-item:last-child { border-bottom: none; }
.db-act-item:hover { background: #f8f8fa; }
.db-act-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px;
}
.db-act-body { flex: 1; min-width: 0; }
.db-act-name {
    font-size: 13.5px; font-weight: 600; color: var(--d-text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.db-act-meta { font-size: 12px; color: var(--d-sub); margin-top: 2px; }
.db-act-badge {
    font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px;
}
.badge-borrador  { background: #fff3e0; color: #e65100; }
.badge-publicado { background: #e8f5e9; color: #2e7d32; }

.db-act-empty {
    display: flex; flex-direction: column; align-items: center;
    gap: 8px; padding: 36px 20px; color: var(--d-sub);
}
.db-act-empty i { font-size: 32px; opacity: .25; }
.db-act-empty span { font-size: 13px; }

/* ── Panel derecho: roles ── */
.db-roles-list { padding: 0 18px 14px; list-style: none; margin: 0; }
.db-role-item { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--d-border); }
.db-role-item:last-child { border-bottom: none; }
.db-role-name { flex: 1; font-size: 13px; color: var(--d-text); text-transform: capitalize; font-weight: 500; }
.db-role-total { font-size: 13px; font-weight: 700; color: var(--d-text); }
.db-role-bar-wrap { width: 80px; height: 6px; background: var(--d-bg); border-radius: 3px; overflow: hidden; }
.db-role-bar { height: 100%; border-radius: 3px; background: var(--d-blue); }

/* ── Panel mensajes no leídos ── */
.db-msg-item {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 11px 18px; border-bottom: 1px solid var(--d-border);
    text-decoration: none; color: var(--d-text); transition: background .12s;
}
.db-msg-item:last-child { border-bottom: none; }
.db-msg-item:hover { background: #f0f5ff; }
.db-msg-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--d-blue); margin-top: 5px; flex-shrink: 0; }
.db-msg-body { flex: 1; min-width: 0; }
.db-msg-de   { font-size: 12.5px; font-weight: 700; }
.db-msg-subj { font-size: 12px; color: var(--d-sub); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.db-msg-empty { display: flex; align-items: center; gap: 8px; padding: 20px 18px; color: var(--d-sub); font-size: 13px; }
.db-msg-empty i { color: var(--d-green); }
</style>
<?php
$cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php';

// Helpers
function relTime(string $raw): string {
    $ts  = strtotime($raw);
    $seg = time() - $ts;
    if ($seg < 60)    return 'Ahora';
    if ($seg < 3600)  return 'Hace ' . round($seg/60) . ' min';
    if ($seg < 86400) return 'Hace ' . round($seg/3600) . ' h';
    return date('d/m/Y', $ts);
}

$iconoModulo = ['formulacion' => 'fas fa-clipboard-list', 'plan' => 'fas fa-project-diagram'];
$dotColor    = ['borrador' => '#FF9500', 'publicado' => '#34C759'];

$maxRol = !empty($userStats) ? max(array_column($userStats, 'total')) : 1;
?>

<div class="db-page">

    <!-- ── Bienvenida ── -->
    <div class="db-welcome">
        <div class="db-welcome-left">
            <h1><?php echo $saludo . ', ' . htmlspecialchars(explode(' ', $unombre)[0]); ?> 👋</h1>
            <p>Universidad del Atlántico — Sistema de Gestión Institucional 144</p>
        </div>
        <div class="db-welcome-right">
            <strong><?php echo date('l', time()); ?></strong><br>
            <?php echo date('d \d\e F \d\e Y'); ?><br>
            <span id="dbClock"><?php echo date('H:i'); ?></span>
        </div>
    </div>

    <!-- ── Estadísticas ── -->
    <div class="db-stats">

        <!-- Formularios activos -->
        <div class="db-stat">
            <div class="db-stat-icon" style="background:rgba(0,122,255,.12);color:var(--d-blue)">
                <i class="fas fa-wpforms"></i>
            </div>
            <div class="db-stat-val"><?php echo $stats['formularios']; ?></div>
            <div class="db-stat-lbl">Formularios FOR-DE-144</div>
            <div class="db-stat-note"><a href="<?php echo $basePath; ?>/FOR-DE-144" style="color:var(--d-blue);text-decoration:none">Ver todos →</a></div>
        </div>

        <!-- Mis registros módulo 144 -->
        <div class="db-stat">
            <div class="db-stat-icon" style="background:rgba(52,199,89,.12);color:var(--d-green)">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="db-stat-val"><?php echo $stats['mis_registros']; ?></div>
            <div class="db-stat-lbl">Mis registros Módulo 144</div>
            <div class="db-stat-note">
                <?php if ($stats['borradores'] > 0): ?>
                    <span style="color:var(--d-orange)"><?php echo $stats['borradores']; ?> borrador<?php echo $stats['borradores'] > 1 ? 'es' : ''; ?> pendiente<?php echo $stats['borradores'] > 1 ? 's' : ''; ?></span>
                <?php else: ?>
                    <span style="color:var(--d-green)">Sin borradores pendientes</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Usuarios -->
        <div class="db-stat">
            <div class="db-stat-icon" style="background:rgba(175,82,222,.12);color:var(--d-purple)">
                <i class="fas fa-users"></i>
            </div>
            <div class="db-stat-val"><?php echo $stats['usuarios']; ?></div>
            <div class="db-stat-lbl"><?php echo $esAdmin ? 'Usuarios activos' : 'Colegas con rol ' . ucfirst($urol); ?></div>
            <?php if ($esAdmin): ?>
            <div class="db-stat-note"><a href="<?php echo $basePath; ?>/usuarios" style="color:var(--d-purple);text-decoration:none">Gestionar →</a></div>
            <?php endif; ?>
        </div>

        <!-- Mensajes sin leer -->
        <div class="db-stat">
            <div class="db-stat-icon" style="background:rgba(255,59,48,.12);color:var(--d-red)">
                <i class="fas fa-envelope<?php echo $stats['mensajes_nl'] > 0 ? '' : '-open'; ?>"></i>
            </div>
            <div class="db-stat-val" style="color:<?php echo $stats['mensajes_nl'] > 0 ? 'var(--d-red)' : 'var(--d-text)'; ?>">
                <?php echo $stats['mensajes_nl']; ?>
            </div>
            <div class="db-stat-lbl">Mensajes sin leer</div>
            <div class="db-stat-note"><a href="<?php echo $basePath; ?>/mensajes" style="color:var(--d-red);text-decoration:none">
                <?php echo $stats['mensajes_nl'] > 0 ? 'Ver bandeja →' : 'Abrir mensajes →'; ?>
            </a></div>
        </div>

        <!-- Dependencias -->
        <div class="db-stat">
            <div class="db-stat-icon" style="background:rgba(255,149,0,.12);color:var(--d-orange)">
                <i class="fas fa-building"></i>
            </div>
            <div class="db-stat-val"><?php echo $stats['dependencias']; ?></div>
            <div class="db-stat-lbl">Dependencias activas</div>
            <?php if ($esAdmin): ?>
            <div class="db-stat-note"><a href="<?php echo $basePath; ?>/dependencias" style="color:var(--d-orange);text-decoration:none">Administrar →</a></div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ── Columnas inferiores ── -->
    <div class="db-cols">

        <!-- Columna principal: Actividad reciente -->
        <div style="display:flex;flex-direction:column;gap:14px">

            <!-- Actividad Módulo 144 -->
            <div class="db-panel">
                <div class="db-panel-head">
                    <h3><i class="fas fa-history" style="color:var(--d-blue)"></i> Actividad reciente — Módulo 144</h3>
                    <a href="<?php echo $basePath; ?>/FOR-DE-144" class="db-panel-link">Ver formularios</a>
                </div>
                <?php if (empty($actividad)): ?>
                <div class="db-act-empty">
                    <i class="fas fa-inbox"></i>
                    <span>No hay registros recientes</span>
                </div>
                <?php else: ?>
                <ul class="db-act-list">
                    <?php foreach ($actividad as $a):
                        $estado = $a['estado'] ?? 'borrador';
                        $dot    = $dotColor[$estado] ?? '#aeaeb2';
                        $modulo = $a['modulo'] ?? 'formulacion';
                    ?>
                    <li class="db-act-item">
                        <div class="db-act-dot" style="background:<?php echo $dot; ?>"></div>
                        <div class="db-act-body">
                            <div class="db-act-name"><?php echo htmlspecialchars($a['nombre'] ?? '—'); ?></div>
                            <div class="db-act-meta">
                                <?php echo htmlspecialchars(ucfirst($modulo)); ?>
                                · Por <?php echo htmlspecialchars($a['creado_por_nombre'] ?? 'Sistema'); ?>
                                · <?php echo relTime($a['fecha_creacion']); ?>
                            </div>
                        </div>
                        <span class="db-act-badge badge-<?php echo $estado; ?>">
                            <?php echo ucfirst($estado); ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>

            <!-- Mensajes no leídos -->
            <?php if ($stats['mensajes_nl'] > 0): ?>
            <div class="db-panel">
                <div class="db-panel-head">
                    <h3><i class="fas fa-envelope" style="color:var(--d-red)"></i>
                        Mensajes sin leer
                        <span style="background:#ff3b30;color:#fff;border-radius:8px;padding:1px 7px;font-size:11px;font-weight:700">
                            <?php echo $stats['mensajes_nl']; ?>
                        </span>
                    </h3>
                    <a href="<?php echo $basePath; ?>/mensajes" class="db-panel-link">Ir a mensajes</a>
                </div>
                <?php foreach ($msgRecentes as $msg): ?>
                <a href="<?php echo $basePath; ?>/mensajes" class="db-msg-item">
                    <div class="db-msg-dot"></div>
                    <div class="db-msg-body">
                        <div class="db-msg-de"><?php echo htmlspecialchars($msg['de'] ?? ''); ?></div>
                        <div class="db-msg-subj"><?php echo htmlspecialchars($msg['asunto'] ?? ''); ?></div>
                    </div>
                    <div style="font-size:11px;color:#aeaeb2;white-space:nowrap">
                        <?php echo relTime($msg['fecha_envio']); ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>

        <!-- Columna derecha: Distribución por rol -->
        <div style="display:flex;flex-direction:column;gap:14px">

            <div class="db-panel">
                <div class="db-panel-head">
                    <h3><i class="fas fa-user-tag" style="color:var(--d-purple)"></i> Usuarios por rol</h3>
                    <a href="<?php echo $basePath; ?>/usuarios" class="db-panel-link">Gestionar</a>
                </div>
                <?php if (empty($userStats)): ?>
                <div class="db-msg-empty"><i class="fas fa-users"></i> Sin datos</div>
                <?php else: ?>
                <ul class="db-roles-list">
                    <?php foreach ($userStats as $r):
                        $pct = $maxRol > 0 ? round(($r['total'] / $maxRol) * 100) : 0;
                        $roleColors = [
                            'admin'=>'#AF52DE','director'=>'#007AFF','coordinador'=>'#34C759',
                            'jefe'=>'#FF9500','analista'=>'#32ADE6','secretario'=>'#FF2D55',
                            'auxiliar'=>'#5856D6','tecnico'=>'#FF3B30','asesor'=>'#FF9500','pasante'=>'#aeaeb2'
                        ];
                        $rc = $roleColors[$r['rol']] ?? '#007AFF';
                    ?>
                    <li class="db-role-item">
                        <div style="width:8px;height:8px;border-radius:50%;background:<?php echo $rc; ?>;flex-shrink:0"></div>
                        <span class="db-role-name"><?php echo htmlspecialchars(ucfirst($r['rol'])); ?></span>
                        <div class="db-role-bar-wrap">
                            <div class="db-role-bar" style="width:<?php echo $pct; ?>%;background:<?php echo $rc; ?>"></div>
                        </div>
                        <span class="db-role-total"><?php echo $r['total']; ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>

            <!-- Accesos rápidos -->
            <div class="db-panel">
                <div class="db-panel-head">
                    <h3><i class="fas fa-bolt" style="color:var(--d-orange)"></i> Accesos rápidos</h3>
                </div>
                <div style="padding:12px 14px;display:grid;grid-template-columns:1fr 1fr;gap:8px">
                    <?php
                    $accesos = [
                        ['icon'=>'fas fa-wpforms',        'label'=>'Formularios',  'href'=>'/FOR-DE-144',     'color'=>'#007AFF'],
                        ['icon'=>'fas fa-layer-group',    'label'=>'Módulo 144',   'href'=>'/modulo144',      'color'=>'#34C759'],
                        ['icon'=>'fas fa-cog',            'label'=>'Config 144',   'href'=>'/config144',      'color'=>'#FF9500'],
                        ['icon'=>'fas fa-users',          'label'=>'Usuarios',     'href'=>'/usuarios',       'color'=>'#AF52DE'],
                        ['icon'=>'fas fa-building',       'label'=>'Dependencias', 'href'=>'/dependencias',   'color'=>'#FF9500'],
                        ['icon'=>'fas fa-envelope',       'label'=>'Mensajes',     'href'=>'/mensajes',       'color'=>'#FF3B30'],
                    ];
                    foreach ($accesos as $a): ?>
                    <a href="<?php echo $basePath . $a['href']; ?>"
                       style="display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:10px;
                              background:rgba(0,0,0,0.03);text-decoration:none;color:#1d1d1f;
                              font-size:13px;font-weight:500;transition:background .15s;border:1px solid rgba(0,0,0,.06)"
                       onmouseover="this.style.background='rgba(0,122,255,.08)'"
                       onmouseout="this.style.background='rgba(0,0,0,.03)'">
                        <i class="<?php echo $a['icon']; ?>" style="color:<?php echo $a['color']; ?>;font-size:14px;width:16px;text-align:center"></i>
                        <?php echo $a['label']; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
// Reloj en tiempo real
(function() {
    var el = document.getElementById('dbClock');
    if (!el) return;
    setInterval(function() {
        var n = new Date();
        el.textContent = String(n.getHours()).padStart(2,'0') + ':' + String(n.getMinutes()).padStart(2,'0') + ':' + String(n.getSeconds()).padStart(2,'0');
    }, 1000);
})();
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
