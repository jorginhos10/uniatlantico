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
$ucargo   = (int)($_SESSION['usuario_cargo_id'] ?? 0);

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

// ── Helpers ──────────────────────────────────────────────────────────────
function dbNormRol($s) {
    $s = mb_strtolower(trim((string)$s), 'UTF-8');
    return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
}
function dbNum($v) {
    if ($v === null) return null;
    $v = str_replace(['%', ' '], '', (string)$v);
    $v = str_replace(',', '.', $v);
    return is_numeric($v) ? (float)$v : null;
}
function dbFmt($n, $dec = 1) {
    return number_format((float)$n, $dec, ',', '.');
}
function dbFmtNum($n) {
    if ($n === null) return '—';
    return dbFmt($n, floor($n) == $n ? 0 : 1);
}
function dbTono($p) {
    if ($p === null) return 'none';
    return $p >= 90 ? 'ok' : ($p >= 60 ? 'warn' : 'bad');
}
function relTime(string $raw): string {
    $ts  = strtotime($raw);
    $seg = time() - $ts;
    if ($seg < 60)    return 'Ahora';
    if ($seg < 3600)  return 'Hace ' . round($seg/60) . ' min';
    if ($seg < 86400) return 'Hace ' . round($seg/3600) . ' h';
    return date('d/m/Y', $ts);
}

$esAdmin  = ($urol === 'admin');
$rolNorm  = dbNormRol($urol);
$veTodo   = $esAdmin || $uid === 1 || in_array($rolNorm, ['administrador', 'sub administrador'], true);

// Mismo mapa de etapas que usa el semáforo del Módulo 144
$semaforoRolNivel = [
    'gestor de metas' => 1,
    'gestor de metas de responsable de linea' => 1,
    'lider de meta' => 2,
    'gestor de metas de sub-admin' => 2,
    'responsable de linea' => 3,
    'sub administrador' => 4,
];
$etapaNombre = [1 => 'Gestor de Metas', 2 => 'Líder de Metas', 3 => 'Vicerrectoría', 4 => 'Oficina de Planeación'];
$miNivel = $semaforoRolNivel[$rolNorm] ?? 0;

// ── Filtros (GET) ────────────────────────────────────────────────────────
$sem   = isset($_GET['s']) && in_array((int)$_GET['s'], [1, 2], true) ? (int)$_GET['s'] : ((int)date('n') <= 6 ? 1 : 2);
$linea = isset($_GET['l']) ? trim((string)$_GET['l']) : '';

$mensajesNl  = 0;
$novedades   = [];
$planes      = [];
$plan        = null;
$rows        = [];
$ultimaAccion = [];
$historial   = [];

if ($pdo) {

    // Mensajes no leídos
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
        $mensajesNl = (int)$s->fetchColumn();
    } catch (PDOException $e) { error_log("Dash mensajes_nl: " . $e->getMessage()); }

    // Novedades activas
    try {
        $novedades = $pdo->query(
            "SELECT id, titulo, contenido, auto_abrir FROM novedades
             WHERE activo = 1
               AND (visible_desde IS NULL OR visible_desde <= CURDATE())
               AND (visible_hasta IS NULL OR visible_hasta >= CURDATE())
             ORDER BY orden ASC, fecha_creacion DESC LIMIT 10"
        )->fetchAll();
    } catch (PDOException $e) { $novedades = []; }

    // Formularios con registros en el Módulo 144 (el más reciente es el plan por defecto)
    try {
        $planes = $pdo->query(
            "SELECT frm.id, frm.titulo, frm.anio, frm.tipo_tiempo, frm.fecha_inicio, frm.fecha_fin,
                    COUNT(f.id) AS registros
             FROM formularios frm
             JOIN formulacion_144 f ON f.formulario_id = frm.id AND f.estado_formulacion != 1
             WHERE frm.estado = 1
             GROUP BY frm.id
             ORDER BY frm.anio DESC, frm.fecha_creacion DESC"
        )->fetchAll();
    } catch (PDOException $e) { error_log("Dash planes: " . $e->getMessage()); }

    $fidPedido = (int)($_GET['f'] ?? 0);
    foreach ($planes as $p) {
        if ((int)$p['id'] === $fidPedido) { $plan = $p; break; }
    }
    if (!$plan && !empty($planes)) $plan = $planes[0];

    if ($plan) {
        $fid = (int)$plan['id'];

        // Registros del plan (sin cancelados)
        try {
            $s = $pdo->prepare(
                "SELECT f.id, f.nombre_borrador, f.formula_medicion, f.linea_estrategica, f.motor_desarrollo, f.proyecto,
                        f.ponderacion_actividades, f.meta_s1, f.meta_s2,
                        f.semestre1_seguimiento, f.semestre2_seguimiento, f.porcentaje_avance,
                        f.estado_formulacion, f.solicitud_estado_formulacion, f.semaforo_etapa_formulacion,
                        f.creado_por, f.fecha_creacion,
                        le.codigo AS linea_codigo, m.codigo AS motor_codigo, p.codigo AS proyecto_codigo,
                        u.cargo_id AS creado_por_cargo_id, u.rol AS creado_por_rol
                 FROM formulacion_144 f
                 LEFT JOIN lineas_estrategicas le ON f.linea_estrategica = le.nombre AND le.activo = 1
                 LEFT JOIN motores m ON f.motor_desarrollo = m.nombre AND m.linea_id = le.id AND m.activo = 1
                 LEFT JOIN proyectos p ON f.proyecto = p.nombre AND p.motor_id = m.id AND p.activo = 1
                 LEFT JOIN usuarios u ON f.creado_por = u.id
                 WHERE f.formulario_id = :fid AND f.estado_formulacion != 1
                 ORDER BY le.codigo ASC, m.id ASC, p.codigo ASC"
            );
            $s->execute([':fid' => $fid]);
            $rows = $s->fetchAll();
        } catch (PDOException $e) { error_log("Dash rows: " . $e->getMessage()); }

        // Última acción del semáforo por registro (para la antigüedad en la bandeja)
        try {
            $s = $pdo->prepare(
                "SELECT h.formulacion_id, MAX(h.fecha_creacion) AS ultima
                 FROM semaforo_historial_144 h
                 JOIN formulacion_144 f ON f.id = h.formulacion_id
                 WHERE h.modulo = 'formulacion' AND f.formulario_id = :fid
                 GROUP BY h.formulacion_id"
            );
            $s->execute([':fid' => $fid]);
            foreach ($s->fetchAll() as $r) $ultimaAccion[(int)$r['formulacion_id']] = $r['ultima'];
        } catch (PDOException $e) { error_log("Dash ultimaAccion: " . $e->getMessage()); }

        // Actividad reciente del semáforo
        try {
            $s = $pdo->prepare(
                "SELECT h.formulacion_id, h.etapa, h.accion, h.usuario_nombre, h.motivo, h.fecha_creacion
                 FROM semaforo_historial_144 h
                 JOIN formulacion_144 f ON f.id = h.formulacion_id
                 WHERE h.modulo = 'formulacion' AND f.formulario_id = :fid
                 ORDER BY h.fecha_creacion DESC
                 LIMIT 30"
            );
            $s->execute([':fid' => $fid]);
            $historial = $s->fetchAll();
        } catch (PDOException $e) { error_log("Dash historial: " . $e->getMessage()); }
    }
}

// ── Alcance por rol: admin / sub administrador ven todo; el resto, lo suyo y su dependencia ──
$rows = array_values(array_filter($rows, function ($r) use ($veTodo, $uid, $ucargo) {
    if ($veTodo) return true;
    if ((int)$r['creado_por'] === $uid) return true;
    return $ucargo > 0 && (int)$r['creado_por_cargo_id'] === $ucargo;
}));

// Catálogo de líneas presentes en el plan (para el filtro)
$lineas = [];
foreach ($rows as $r) {
    $cod = $r['linea_codigo'] ?: ($r['linea_estrategica'] ?: 'Sin línea');
    if (!isset($lineas[$cod])) $lineas[$cod] = $r['linea_estrategica'] ?: 'Sin línea estratégica';
}
ksort($lineas);
if ($linea !== '' && !isset($lineas[$linea])) $linea = '';

// Enriquecer cada registro
$idsVisibles = [];
foreach ($rows as &$r) {
    $r['lcod']  = $r['linea_codigo'] ?: ($r['linea_estrategica'] ?: 'Sin línea');
    $r['lmp']   = implode(' · ', array_filter([$r['linea_codigo'], $r['motor_codigo'], $r['proyecto_codigo']])) ?: '—';
    $r['texto'] = trim((string)$r['formula_medicion']) !== '' ? $r['formula_medicion'] : $r['nombre_borrador'];
    $r['peso']  = (float)($r['ponderacion_actividades'] ?? 0);
    $r['meta']  = dbNum($sem === 1 ? $r['meta_s1'] : $r['meta_s2']);
    $r['ejec']  = dbNum($sem === 1 ? $r['semestre1_seguimiento'] : $r['semestre2_seguimiento']);
    $r['pct']   = null;
    if ($r['meta'] !== null && $r['meta'] > 0 && $r['ejec'] !== null) {
        $r['pct'] = min($r['ejec'] / $r['meta'] * 100, 100);
    } elseif (($av = dbNum($r['porcentaje_avance'])) !== null) {
        $r['pct'] = min(max($av, 0), 100);
    }
    $r['publicado'] = (int)$r['estado_formulacion'] === 2;
    $r['solicitud'] = (int)$r['solicitud_estado_formulacion'];
    // Igual que en el Módulo 144: si ya fue solicitado, la etapa nunca es menor al nivel del creador
    $etapa = (int)$r['semaforo_etapa_formulacion'];
    $nivelCreador = $semaforoRolNivel[dbNormRol($r['creado_por_rol'] ?? '')] ?? 0;
    if ($r['solicitud'] === 1 && $etapa < $nivelCreador) $etapa = $nivelCreador;
    $r['etapa'] = $r['publicado'] ? 4 : $etapa;
    $idsVisibles[(int)$r['id']] = $r;
}
unset($r);

$filas = $linea === '' ? $rows : array_values(array_filter($rows, fn($r) => $r['lcod'] === $linea));
$pubs  = array_values(array_filter($filas, fn($r) => $r['publicado']));

// Avance ponderado (sin reporte cuenta como 0; si no hay ponderaciones, promedio simple)
function dbPonderado(array $lista) {
    $pub = array_filter($lista, fn($r) => $r['publicado']);
    if (!$pub) return null;
    $W = array_sum(array_map(fn($r) => $r['peso'], $pub));
    if ($W > 0) {
        return array_sum(array_map(fn($r) => $r['peso'] * ($r['pct'] ?? 0), $pub)) / $W;
    }
    return array_sum(array_map(fn($r) => $r['pct'] ?? 0, $pub)) / count($pub);
}
$avance = dbPonderado($filas);

// Avance esperado según el calendario del semestre
$anioPlan  = (int)($plan['anio'] ?? date('Y')) ?: (int)date('Y');
$iniSem    = strtotime($anioPlan . ($sem === 1 ? '-01-01' : '-07-01'));
$finSem    = strtotime($anioPlan . ($sem === 1 ? '-06-30 23:59:59' : '-12-31 23:59:59'));
$esperado  = (int)round(max(0, min(1, (time() - $iniSem) / ($finSem - $iniSem))) * 100);

// Cierre del formulario
$cierreVal = '—'; $cierreUnidad = ''; $cierreNota = 'Formulario de tiempo libre';
if ($plan && $plan['tipo_tiempo'] === 'rango' && !empty($plan['fecha_fin'])) {
    $finTs = strtotime($plan['fecha_fin']);
    if ($finTs > time()) {
        $cierreVal = (string)(int)ceil(($finTs - time()) / 86400);
        $cierreUnidad = ' días';
        $cierreNota = date('d/m/Y H:i', $finTs);
    } else {
        $cierreVal = 'Cerrado';
        $cierreNota = 'Cerró el ' . date('d/m/Y', $finTs);
    }
}

$conReporte = count(array_filter($pubs, fn($r) => $r['pct'] !== null));
$enRiesgo   = count(array_filter($pubs, fn($r) => $r['pct'] !== null && $r['pct'] < 60));

// Avance por línea
$porLinea = [];
foreach ($linea === '' ? array_keys($lineas) : [$linea] as $cod) {
    $porLinea[] = [
        'cod'    => $cod,
        'nombre' => $lineas[$cod],
        'pct'    => dbPonderado(array_filter($rows, fn($r) => $r['lcod'] === $cod)),
    ];
}

// Flujo de aprobación (C = en construcción o rechazado; G/L/V = aprobado hasta esa etapa; P = Planeación o publicado)
$flujo = ['C' => 0, 'G' => 0, 'L' => 0, 'V' => 0, 'P' => 0];
$rechazados = 0;
foreach ($filas as $r) {
    if ($r['publicado'] || $r['etapa'] >= 4) { $flujo['P']++; continue; }
    if ($r['solicitud'] === 2) $rechazados++;
    if ($r['solicitud'] !== 1 || $r['etapa'] === 0) { $flujo['C']++; continue; }
    $flujo[['', 'G', 'L', 'V'][$r['etapa']]]++;
}
$flujoMax = max(1, max($flujo));
$esperanFirma = $flujo['G'] + $flujo['L'] + $flujo['V'];

// Indicadores que necesitan atención: publicados con reporte, menor avance primero
$riesgo = array_values(array_filter($pubs, fn($r) => $r['pct'] !== null));
usort($riesgo, fn($a, $b) => $a['pct'] <=> $b['pct']);
$riesgo = array_slice($riesgo, 0, 6);
$sinReporte = count($pubs) - $conReporte;

// Mi bandeja
$bandeja = [];
foreach ($filas as $r) {
    if ($r['publicado']) continue;
    $item = null;
    if ($miNivel >= 2 && $r['solicitud'] === 1 && $r['etapa'] === $miNivel - 1
        && ($veTodo || $miNivel === 4 || ((int)$r['creado_por_cargo_id'] === $ucargo && $ucargo > 0))) {
        $item = 'Aprobado por ' . $etapaNombre[$r['etapa']];
    } elseif ($miNivel === 1 && $r['solicitud'] === 2 && (int)$r['creado_por'] === $uid) {
        $item = 'Rechazado: requiere corrección';
    } elseif ($miNivel === 0 && $veTodo && $r['solicitud'] === 1 && $r['etapa'] < 4) {
        $item = 'Espera a ' . $etapaNombre[min($r['etapa'] + 1, 4)];
    }
    if ($item === null) continue;
    $desde = $ultimaAccion[(int)$r['id']] ?? $r['fecha_creacion'];
    $r['tipo'] = $item;
    $r['dias'] = max(0, (int)floor((time() - strtotime($desde)) / 86400));
    $bandeja[] = $r;
}
usort($bandeja, fn($a, $b) => $b['dias'] <=> $a['dias']);
$bandejaTotal = count($bandeja);
$bandeja = array_slice($bandeja, 0, 5);
$rolEtiqueta = $miNivel ? ($etapaNombre[$miNivel] ?? $urol) : ($veTodo ? 'Administrador' : ucfirst($urol));

// Ponderación por proyecto (suma de ponderacion_actividades por L-M-P, debe dar 100)
$pondProy = [];
foreach ($filas as $r) {
    $k = $r['lmp'];
    $pondProy[$k] = ($pondProy[$k] ?? 0) + $r['peso'];
}
$pondLista = [];
foreach ($pondProy as $k => $v) {
    $estado = abs($v - 100) < 0.01 ? 'ok' : ($v > 100 ? 'bad' : 'warn');
    $pondLista[] = ['k' => $k, 'v' => $v, 'estado' => $estado];
}
// Primero los que tienen problema
usort($pondLista, fn($a, $b) => ($a['estado'] === 'ok') <=> ($b['estado'] === 'ok') ?: strcmp($a['k'], $b['k']));
$pondProblemas = count(array_filter($pondLista, fn($p) => $p['estado'] !== 'ok'));
$pondLista = array_slice($pondLista, 0, 8);

// Actividad: solo de registros visibles y de la línea filtrada
$actividad = [];
foreach ($historial as $h) {
    $r = $idsVisibles[(int)$h['formulacion_id']] ?? null;
    if (!$r || ($linea !== '' && $r['lcod'] !== $linea)) continue;
    $h['lmp'] = $r['lmp'];
    $actividad[] = $h;
    if (count($actividad) >= 6) break;
}

// Saludo
$minTotal = (int)date('G') * 60 + (int)date('i');
$saludo = ($minTotal >= 360 && $minTotal < 720) ? 'Buenos días' : (($minTotal >= 720 && $minTotal < 1140) ? 'Buenas tardes' : 'Buenas noches');
$dias   = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
$meses  = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$fechaLarga = ucfirst($dias[(int)date('w')]) . ', ' . (int)date('j') . ' de ' . $meses[(int)date('n')] . ' de ' . date('Y');

// URL con filtros
function dbUrl($basePath, array $cambios) {
    $q = array_merge(['f' => $_GET['f'] ?? null, 's' => $_GET['s'] ?? null, 'l' => $_GET['l'] ?? null], $cambios);
    $q = array_filter($q, fn($v) => $v !== null && $v !== '');
    return $basePath . '/dashboard' . ($q ? '?' . http_build_query($q) : '');
}

$TONO = [
    'ok'   => ['bar' => '#34C759', 'ink' => '#248A3D', 'tint' => 'rgba(52,199,89,.14)',  'label' => 'En meta'],
    'warn' => ['bar' => '#FF9500', 'ink' => '#B25000', 'tint' => 'rgba(255,149,0,.14)',  'label' => 'Atención'],
    'bad'  => ['bar' => '#FF3B30', 'ink' => '#D70015', 'tint' => 'rgba(255,59,48,.10)',  'label' => 'En riesgo'],
    'none' => ['bar' => '#AEAEB2', 'ink' => '#6E6E73', 'tint' => '#F2F2F7',              'label' => 'Sin reporte'],
];
$tonoAvance = $avance === null ? 'none' : ($avance >= $esperado ? 'ok' : dbTono($esperado > 0 ? $avance / $esperado * 100 : 100));
$moduloUrl  = $plan ? $basePath . '/modulo144?id=' . (int)$plan['id'] : $basePath . '/FOR-DE-144';

ob_start();
?>
<style>
:root {
    --pa-bg: #F5F5F7; --pa-card: #FFFFFF; --pa-fill: #F2F2F7; --pa-track: #EEEEF2; --pa-seg: #E3E3E8;
    --pa-sep: #E5E5EA; --pa-sep-soft: #F0F0F3;
    --pa-text: #1D1D1F; --pa-text-2: #3A3A3C; --pa-sub: #6E6E73;
    --pa-blue: #007AFF; --pa-blue-ink: #0066CC; --pa-blue-tint: rgba(0,122,255,.12);
    --pa-shadow: 0 1px 2px rgba(0,0,0,.04), 0 6px 20px rgba(0,0,0,.04);
}
.pa { display: flex; flex-direction: column; gap: 22px; color: var(--pa-text); font-size: 15px; line-height: 1.45; max-width: 1240px; margin: 0 auto; }
.pa *, .pa *::before, .pa *::after { box-sizing: border-box; }
.pa a { text-decoration: none; }
.pa .num { font-variant-numeric: tabular-nums; }
.pa-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px 24px; }
.pa-eyebrow { font-size: 13px; font-weight: 600; color: var(--pa-sub); text-transform: uppercase; letter-spacing: .04em; }
.pa-head h1 { margin: 4px 0 2px; font-size: 32px; line-height: 1.15; font-weight: 700; letter-spacing: -.02em; }
.pa-head p { margin: 0; color: var(--pa-sub); }
.pa-tools { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
.pa-seg { display: inline-flex; flex-wrap: wrap; padding: 2px; border-radius: 9px; background: var(--pa-seg); gap: 2px; }
.pa-seg a { padding: 6px 13px; border-radius: 7px; font-size: 13px; font-weight: 600; color: var(--pa-text-2); }
.pa-seg a:hover { color: var(--pa-text); }
.pa-seg a.on { background: var(--pa-card); color: var(--pa-text); box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 1px rgba(0,0,0,.04); }
.pa-select { appearance: none; border: 0; background: var(--pa-seg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%233A3A3C' stroke-width='1.6' fill='none'/%3E%3C/svg%3E") no-repeat right 12px center; padding: 7px 32px 7px 12px; border-radius: 9px; font: 600 13px inherit; font-family: inherit; color: var(--pa-text); max-width: 280px; cursor: pointer; }
.pa-msg { display: inline-flex; align-items: center; gap: 7px; padding: 6px 12px; border-radius: 999px; background: rgba(255,59,48,.10); color: #D70015 !important; font-size: 13px; font-weight: 600; }
.pa-seg a:focus-visible, .pa-select:focus-visible, .pa a:focus-visible { outline: 3px solid rgba(0,122,255,.45); outline-offset: 2px; }

.pa-card { background: var(--pa-card); border-radius: 18px; box-shadow: var(--pa-shadow); padding: 22px 24px; min-width: 0; }
.pa-card h2 { margin: 0; font-size: 19px; font-weight: 700; letter-spacing: -.01em; }
.pa-card-head { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; flex-wrap: wrap; }
.pa-card-head .pa-link { font-size: 14px; font-weight: 500; color: var(--pa-blue-ink); }
.pa-card-head .pa-note { font-size: 12.5px; color: var(--pa-sub); }
.pa-row { display: flex; flex-wrap: wrap; gap: 16px; }

.pa-kpi { flex: 1 1 170px; display: flex; flex-direction: column; gap: 4px; padding: 20px; }
.pa-kpi .lbl { font-size: 13px; font-weight: 600; color: var(--pa-sub); }
.pa-kpi .val { font-size: 32px; font-weight: 700; letter-spacing: -.02em; line-height: 1.15; }
.pa-kpi .val small { font-size: 17px; color: var(--pa-sub); font-weight: 600; }
.pa-kpi .note { font-size: 13px; color: var(--pa-sub); }
.pa-kpi.hero { flex: 2 1 340px; flex-direction: row; align-items: center; gap: 22px; }
.pa-kpi.hero .val { font-size: 44px; letter-spacing: -.03em; }
.pa-kpi.hero .val small { font-size: 22px; }
.pa-pill { display: inline-flex; align-items: center; gap: 6px; align-self: flex-start; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
.pa-pill i.dot { width: 6px; height: 6px; border-radius: 50%; }

.pa-lines { display: flex; flex-direction: column; gap: 16px; margin-top: 18px; }
.pa-line { display: grid; grid-template-columns: minmax(0, 240px) minmax(0, 1fr) 70px; gap: 14px; align-items: center; }
.pa-line .nm { display: flex; align-items: center; gap: 10px; min-width: 0; font-size: 14px; line-height: 1.25; }
.pa-code { flex: none; font-size: 12px; font-weight: 700; color: var(--pa-blue-ink); background: rgba(0,122,255,.10); padding: 3px 7px; border-radius: 6px; }
.pa-track { position: relative; height: 10px; border-radius: 999px; background: var(--pa-track); }
.pa-track .fill { position: absolute; left: 0; top: 0; bottom: 0; border-radius: 999px; }
.pa-track .goal { position: absolute; top: -5px; bottom: -5px; width: 2px; border-radius: 1px; background: var(--pa-text); opacity: .5; }
.pa-line .pct { text-align: right; font-size: 15px; font-weight: 600; }
.pa-legend { display: flex; flex-wrap: wrap; gap: 16px; font-size: 12px; color: var(--pa-sub); margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--pa-sep-soft); }
.pa-legend span { display: flex; align-items: center; gap: 6px; }
.pa-legend i { width: 10px; height: 10px; border-radius: 3px; }

.pa-flow { display: flex; flex-direction: column; gap: 10px; margin-top: 16px; }
.pa-stage { display: grid; grid-template-columns: 30px minmax(0, 1fr) 36px; gap: 12px; align-items: center; }
.pa-stage .dot { width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; font-size: 13px; font-weight: 700; }
.pa-stage .nm { font-size: 14px; display: block; margin-bottom: 5px; }
.pa-stage .bar { height: 6px; border-radius: 999px; background: var(--pa-track); }
.pa-stage .bar span { display: block; height: 6px; border-radius: 999px; }
.pa-stage .n { text-align: right; font-size: 17px; font-weight: 600; }
.pa-chips { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
.pa-chips span { font-size: 13px; padding: 6px 12px; border-radius: 10px; background: var(--pa-fill); }

.pa-table-card { padding: 22px 0 8px; }
.pa-table-card .pa-card-head { padding: 0 24px; }
.pa-scroll { overflow-x: auto; margin-top: 8px; }
.pa-grid { min-width: 900px; padding: 0 24px; }
.pa-tr { display: grid; grid-template-columns: minmax(0, 1fr) 104px 72px 84px 72px 104px 108px; gap: 16px; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--pa-sep-soft); }
.pa-tr:last-child { border-bottom: 0; }
.pa-tr.th { padding: 10px 0; border-bottom: 1px solid var(--pa-sep); font-size: 12px; font-weight: 600; color: var(--pa-sub); }
.pa-tr .r { text-align: right; }
.pa-tr .f { font-size: 14px; line-height: 1.35; color: var(--pa-text); }
.pa-tr .f small { display: block; margin-top: 3px; font-size: 12px; color: var(--pa-sub); }
.pa-tr a.f:hover { color: var(--pa-blue-ink); }
.pa-lmp { justify-self: start; font-size: 12px; font-weight: 600; color: var(--pa-text-2); background: var(--pa-fill); padding: 3px 8px; border-radius: 6px; white-space: nowrap; }
.pa-steps { display: inline-flex; gap: 4px; }
.pa-steps span { width: 22px; height: 22px; border-radius: 50%; display: grid; place-items: center; font-size: 11px; font-weight: 700; background: var(--pa-fill); color: #8E8E93; }
.pa-steps span.on { background: rgba(52,199,89,.16); color: #248A3D; }
.pa-empty { padding: 22px 24px; font-size: 14px; color: var(--pa-sub); }
.pa-empty-box { padding: 18px; border-radius: 12px; background: var(--pa-bg); font-size: 14px; color: var(--pa-sub); }

.pa-col { flex: 1 1 300px; display: flex; flex-direction: column; gap: 14px; }
.pa-inbox { display: flex; flex-direction: column; gap: 8px; }
.pa-inbox a { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; align-items: center; padding: 12px 14px; border-radius: 12px; background: var(--pa-bg); color: var(--pa-text); }
.pa-inbox a:hover { background: #ECECF0; }
.pa-inbox .t { font-size: 14px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.pa-inbox .s { display: block; margin-top: 3px; font-size: 12px; color: var(--pa-sub); }
.pa-weights { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
.pa-w { padding: 12px 14px; border-radius: 12px; background: var(--pa-bg); display: flex; flex-direction: column; gap: 6px; }
.pa-w .top { display: flex; justify-content: space-between; align-items: baseline; gap: 6px; }
.pa-w .k { font-size: 13px; font-weight: 600; color: var(--pa-text-2); }
.pa-w .v { font-size: 20px; font-weight: 700; }
.pa-w .bar { height: 4px; border-radius: 999px; background: var(--pa-seg); }
.pa-w .bar span { display: block; height: 4px; border-radius: 999px; }
.pa-w .lbl { font-size: 12px; }
.pa-feed { list-style: none; margin: 0; padding: 0; }
.pa-feed li { display: grid; grid-template-columns: 10px minmax(0, 1fr); gap: 12px; padding: 8px 0; border-bottom: 1px solid var(--pa-sep-soft); }
.pa-feed li:last-child { border-bottom: 0; }
.pa-feed .dot { width: 8px; height: 8px; margin-top: 6px; border-radius: 50%; }
.pa-feed .t { font-size: 14px; line-height: 1.35; }
.pa-feed .s { display: block; margin-top: 2px; font-size: 12px; color: var(--pa-sub); }

/* Novedades (carrusel y popup) */
.nov-wrap { position: relative; overflow: hidden; min-height: 120px; }
.nov-track { display: flex; transition: transform .45s cubic-bezier(0.4,0,0.2,1); }
.nov-slide { min-width: 100%; padding: 4px 40px 30px; box-sizing: border-box; }
.nov-slide-card { background: var(--pa-bg); border-radius: 12px; padding: 14px 16px; }
.nov-slide-titulo { font-size: 14px; font-weight: 700; margin-bottom: 6px; line-height: 1.3; }
.nov-slide-cuerpo { font-size: 13px; color: var(--pa-sub); line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
.nov-ver-mas { display: inline-block; margin-top: 6px; font-size: 13px; font-weight: 600; color: var(--pa-blue-ink); cursor: pointer; background: none; border: none; padding: 0; }
.nov-ver-mas:hover { text-decoration: underline; }
.nov-popup-ov { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 2000; opacity: 0; pointer-events: none; transition: opacity .2s; }
.nov-popup-ov.open { opacity: 1; pointer-events: all; }
.nov-popup { background: #fff; border-radius: 18px; width: 100%; max-width: 460px; margin: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.2); transform: scale(.95) translateY(8px); transition: transform .25s cubic-bezier(0.34,1.56,0.64,1); }
.nov-popup-ov.open .nov-popup { transform: scale(1) translateY(0); }
.nov-popup-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 14px; border-bottom: 1px solid rgba(0,0,0,.07); }
.nov-popup-head h4 { margin: 0; font-size: 16px; font-weight: 700; color: #1d1d1f; flex: 1; padding-right: 12px; }
.nov-popup-close { width: 28px; height: 28px; border-radius: 50%; border: none; flex-shrink: 0; background: rgba(0,0,0,.06); cursor: pointer; font-size: 13px; color: #6e6e73; display: flex; align-items: center; justify-content: center; }
.nov-popup-body { padding: 16px 20px 22px; font-size: 14px; color: #3a3a3c; line-height: 1.7; white-space: pre-wrap; max-height: 60vh; overflow-y: auto; }
.nov-nav { position: absolute; top: 42%; transform: translateY(-50%); background: #fff; border: 1px solid var(--pa-sep); border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; color: var(--pa-text); box-shadow: 0 2px 8px rgba(0,0,0,.08); z-index: 2; }
.nov-prev { left: 2px; } .nov-next { right: 2px; }
.nov-dots { position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); display: flex; gap: 5px; align-items: center; }
.nov-dot { width: 6px; height: 6px; border-radius: 3px; background: rgba(0,0,0,.15); cursor: pointer; transition: width .25s, background .25s; }
.nov-dot.active { width: 16px; background: var(--pa-blue); }

@media (max-width: 720px) {
    .pa-head h1 { font-size: 28px; }
    .pa-line { grid-template-columns: minmax(0, 1fr) 64px; }
    .pa-line .pa-track { grid-column: 1 / -1; grid-row: 2; }
    .pa-kpi.hero { flex-direction: column; align-items: flex-start; }
    .pa-weights { grid-template-columns: 1fr; }
}
@media (prefers-reduced-motion: reduce) { .nov-track, .nov-popup, .nov-popup-ov { transition: none; } }
</style>
<?php
$cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php';
?>

<div class="pa">

    <!-- ── Encabezado ── -->
    <header class="pa-head">
        <div>
            <div class="pa-eyebrow"><?php echo htmlspecialchars($fechaLarga); ?></div>
            <h1><?php echo $plan ? htmlspecialchars($plan['titulo']) : 'Plan de Acción'; ?></h1>
            <p><?php echo $saludo . ', ' . htmlspecialchars(explode(' ', $unombre)[0]); ?>. Así va el plan y esto es lo que espera tu revisión.</p>
        </div>
        <div class="pa-tools">
            <?php if ($mensajesNl > 0): ?>
            <a class="pa-msg" href="<?php echo $basePath; ?>/mensajes"><i class="fas fa-envelope"></i> <?php echo $mensajesNl; ?> sin leer</a>
            <?php endif; ?>
            <?php if (count($planes) > 1): ?>
            <label for="paPlan" class="visually-hidden" style="position:absolute;left:-9999px">Formulario</label>
            <select id="paPlan" class="pa-select" onchange="location.href=this.value">
                <?php foreach ($planes as $p): ?>
                <option value="<?php echo htmlspecialchars(dbUrl($basePath, ['f' => $p['id'], 'l' => null])); ?>" <?php echo $plan && (int)$p['id'] === (int)$plan['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($p['titulo']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <nav class="pa-seg" aria-label="Semestre">
                <?php foreach ([1, 2] as $s): ?>
                <a href="<?php echo htmlspecialchars(dbUrl($basePath, ['s' => $s])); ?>" class="<?php echo $sem === $s ? 'on' : ''; ?>" <?php echo $sem === $s ? 'aria-current="true"' : ''; ?>>Semestre <?php echo $s; ?></a>
                <?php endforeach; ?>
            </nav>
            <?php if (count($lineas) > 1): ?>
            <nav class="pa-seg" aria-label="Línea estratégica">
                <a href="<?php echo htmlspecialchars(dbUrl($basePath, ['l' => null])); ?>" class="<?php echo $linea === '' ? 'on' : ''; ?>">Todas</a>
                <?php foreach ($lineas as $cod => $nom): ?>
                <a href="<?php echo htmlspecialchars(dbUrl($basePath, ['l' => $cod])); ?>" class="<?php echo $linea === (string)$cod ? 'on' : ''; ?>" title="<?php echo htmlspecialchars($nom); ?>"><?php echo htmlspecialchars($cod); ?></a>
                <?php endforeach; ?>
            </nav>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!$plan): ?>
    <div class="pa-card">
        <h2>Aún no hay un plan con registros</h2>
        <p style="color:var(--pa-sub);margin:8px 0 0">Cuando un formulario FOR-DE-144 tenga indicadores en el Módulo 144, su avance aparecerá aquí.
            <a href="<?php echo $basePath; ?>/FOR-DE-144" style="color:var(--pa-blue-ink);font-weight:600">Ir a formularios</a></p>
    </div>
    <?php else: ?>

    <!-- ── Resumen ── -->
    <section class="pa-row" aria-label="Resumen">
        <div class="pa-card pa-kpi hero">
            <?php
                $C = 289.03;
                $off = $C * (1 - (($avance ?? 0) / 100));
                $offEsp = $C * (1 - $esperado / 100) + 1;
            ?>
            <svg width="112" height="112" viewBox="0 0 112 112" aria-hidden="true" style="flex:none">
                <circle cx="56" cy="56" r="46" fill="none" stroke="#EEEEF2" stroke-width="12"/>
                <circle cx="56" cy="56" r="46" fill="none" stroke="#007AFF" stroke-width="12" stroke-linecap="round" stroke-dasharray="<?php echo $C; ?>" stroke-dashoffset="<?php echo round($off, 2); ?>" transform="rotate(-90 56 56)"/>
                <circle cx="56" cy="56" r="46" fill="none" stroke="#1D1D1F" stroke-width="12" stroke-dasharray="2 <?php echo $C - 2; ?>" stroke-dashoffset="<?php echo round($offEsp, 2); ?>" transform="rotate(-90 56 56)" opacity=".55"/>
            </svg>
            <div style="display:flex;flex-direction:column;gap:2px;min-width:0">
                <span class="lbl">Avance ponderado · S<?php echo $sem; ?></span>
                <span class="val num"><?php echo $avance === null ? '—' : dbFmt($avance); ?><small> %</small></span>
                <span class="note">Esperado a la fecha: <?php echo $esperado; ?> %</span>
                <span class="pa-pill" style="margin-top:6px;background:<?php echo $TONO[$tonoAvance]['tint']; ?>;color:<?php echo $TONO[$tonoAvance]['ink']; ?>">
                    <?php echo $avance === null ? 'Sin indicadores publicados' : ($avance >= $esperado ? 'Por encima de lo esperado' : ($tonoAvance === 'bad' ? 'Muy por debajo de lo esperado' : 'Por debajo de lo esperado')); ?>
                </span>
            </div>
        </div>
        <div class="pa-card pa-kpi">
            <span class="lbl">Indicadores publicados</span>
            <span class="val num"><?php echo count($pubs); ?><small> / <?php echo count($filas); ?></small></span>
            <span class="note"><?php echo count($filas) - count($pubs); ?> en borrador</span>
        </div>
        <div class="pa-card pa-kpi">
            <span class="lbl">Con seguimiento S<?php echo $sem; ?></span>
            <span class="val num"><?php echo $conReporte; ?><small> / <?php echo count($pubs); ?></small></span>
            <span class="note"><?php echo $sinReporte; ?> sin reporte</span>
        </div>
        <div class="pa-card pa-kpi">
            <span class="lbl">En riesgo</span>
            <span class="val num" style="color:<?php echo $enRiesgo ? '#D70015' : 'inherit'; ?>"><?php echo $enRiesgo; ?></span>
            <span class="note">Menos del 60 % de la meta</span>
        </div>
        <div class="pa-card pa-kpi">
            <span class="lbl">Cierre del formulario</span>
            <span class="val num"><?php echo $cierreVal; ?><small><?php echo $cierreUnidad; ?></small></span>
            <span class="note"><?php echo htmlspecialchars($cierreNota); ?></span>
        </div>
    </section>

    <!-- ── Avance por línea + flujo ── -->
    <div class="pa-row">
        <section class="pa-card" style="flex:7 1 480px">
            <div class="pa-card-head">
                <h2>Avance por línea estratégica</h2>
                <a class="pa-link" href="<?php echo $moduloUrl; ?>">Abrir Módulo 144</a>
            </div>
            <div class="pa-lines">
                <?php foreach ($porLinea as $pl):
                    $t = $TONO[dbTono($pl['pct'])]; ?>
                <div class="pa-line">
                    <div class="nm"><span class="pa-code"><?php echo htmlspecialchars($pl['cod']); ?></span><span><?php echo htmlspecialchars($pl['nombre']); ?></span></div>
                    <div class="pa-track" role="img" aria-label="<?php echo htmlspecialchars($pl['nombre']) . ': ' . ($pl['pct'] === null ? 'sin publicados' : dbFmt($pl['pct']) . ' %'); ?>">
                        <div class="fill" style="width:<?php echo round($pl['pct'] ?? 0, 1); ?>%;background:<?php echo $t['bar']; ?>"></div>
                        <div class="goal" style="left:calc(<?php echo $esperado; ?>% - 1px)"></div>
                    </div>
                    <span class="pct num" style="color:<?php echo $t['ink']; ?>"><?php echo $pl['pct'] === null ? '—' : dbFmt($pl['pct']) . ' %'; ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (!$porLinea): ?><div class="pa-empty-box">No hay indicadores en este plan.</div><?php endif; ?>
            </div>
            <div class="pa-legend">
                <span><i style="background:#34C759"></i>90 % o más</span>
                <span><i style="background:#FF9500"></i>60 a 89 %</span>
                <span><i style="background:#FF3B30"></i>Menos de 60 %</span>
                <span><i style="width:2px;height:12px;background:#1D1D1F;opacity:.5"></i>Avance esperado hoy</span>
            </div>
        </section>

        <section class="pa-card" style="flex:5 1 340px;display:flex;flex-direction:column">
            <h2>Flujo de aprobación</h2>
            <div class="pa-flow">
                <?php
                $etapasFlujo = [
                    'C' => ['En construcción',                       '#EEEEF2',              '#6E6E73', '#AEAEB2'],
                    'G' => ['Aprobado por Gestor de Metas',          'rgba(0,122,255,.12)',  '#0066CC', '#5AC8FA'],
                    'L' => ['Aprobado por Líder de Metas',           'rgba(0,122,255,.12)',  '#0066CC', '#007AFF'],
                    'V' => ['Aprobado por Vicerrectoría',            'rgba(88,86,214,.14)',  '#4A48C8', '#5856D6'],
                    'P' => ['Oficina de Planeación · publicado',     'rgba(52,199,89,.16)',  '#248A3D', '#34C759'],
                ];
                foreach ($etapasFlujo as $k => [$nom, $tint, $ink, $bar]): ?>
                <div class="pa-stage">
                    <span class="dot" style="background:<?php echo $tint; ?>;color:<?php echo $ink; ?>"><?php echo $k; ?></span>
                    <div><span class="nm"><?php echo $nom; ?></span><div class="bar"><span style="width:<?php echo round($flujo[$k] / $flujoMax * 100); ?>%;background:<?php echo $bar; ?>"></span></div></div>
                    <span class="n num"><?php echo $flujo[$k]; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="pa-chips" style="margin-top:auto;padding-top:16px">
                <span><b class="num"><?php echo $esperanFirma; ?></b> esperan una firma intermedia</span>
                <?php if ($rechazados): ?>
                <span style="background:rgba(255,59,48,.10);color:#D70015"><b class="num"><?php echo $rechazados; ?></b> rechazado<?php echo $rechazados > 1 ? 's' : ''; ?> para corregir</span>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- ── Indicadores que necesitan atención ── -->
    <section class="pa-card pa-table-card">
        <div class="pa-card-head">
            <h2>Indicadores que necesitan atención</h2>
            <span class="pa-note">Menor avance frente a la meta de S<?php echo $sem; ?><?php echo $sinReporte ? ' · ' . $sinReporte . ' publicados sin reporte' : ''; ?></span>
        </div>
        <?php if (!$riesgo): ?>
        <div class="pa-empty">Todavía no hay indicadores publicados con seguimiento del semestre <?php echo $sem; ?>.</div>
        <?php else: ?>
        <div class="pa-scroll">
            <div class="pa-grid">
                <div class="pa-tr th"><span>Fórmula del indicador</span><span>L · M · P</span><span class="r">Meta</span><span class="r">Ejecutado</span><span class="r">Avance</span><span>Estado</span><span>Aprobación</span></div>
                <?php foreach ($riesgo as $r):
                    $t = $TONO[dbTono($r['pct'])]; ?>
                <div class="pa-tr">
                    <a class="f" href="<?php echo $moduloUrl; ?>"><?php echo htmlspecialchars($r['texto']); ?>
                        <small><?php echo htmlspecialchars($r['linea_estrategica'] ?: 'Sin línea'); ?> · ponderación <?php echo dbFmtNum($r['peso']); ?></small></a>
                    <span class="pa-lmp"><?php echo htmlspecialchars($r['lmp']); ?></span>
                    <span class="r num"><?php echo dbFmtNum($r['meta']); ?></span>
                    <span class="r num"><?php echo dbFmtNum($r['ejec']); ?></span>
                    <span class="r num" style="font-weight:700;color:<?php echo $t['ink']; ?>"><?php echo dbFmt($r['pct'], 0); ?> %</span>
                    <span class="pa-pill" style="background:<?php echo $t['tint']; ?>;color:<?php echo $t['ink']; ?>"><i class="dot" style="background:<?php echo $t['bar']; ?>"></i><?php echo $t['label']; ?></span>
                    <span class="pa-steps" aria-label="Aprobado en <?php echo $r['etapa']; ?> de 4 etapas">
                        <?php foreach (['G', 'L', 'V', 'P'] as $i => $k): ?>
                        <span class="<?php echo $r['etapa'] > $i ? 'on' : ''; ?>" title="<?php echo $etapaNombre[$i + 1]; ?>"><?php echo $k; ?></span>
                        <?php endforeach; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <?php endif; ?>

    <!-- ── Bandeja, ponderación, actividad, novedades ── -->
    <div class="pa-row">
        <?php if ($plan): ?>
        <section class="pa-card pa-col">
            <div class="pa-card-head">
                <h2>Mi bandeja<?php echo $bandejaTotal ? ' <span class="pa-pill num" style="background:#FF3B30;color:#fff;vertical-align:3px">' . $bandejaTotal . '</span>' : ''; ?></h2>
                <span class="pa-note"><?php echo htmlspecialchars($rolEtiqueta); ?></span>
            </div>
            <div class="pa-inbox">
                <?php foreach ($bandeja as $b):
                    $tarde = $b['dias'] >= 7; ?>
                <a href="<?php echo $moduloUrl; ?>">
                    <span><span class="t"><?php echo htmlspecialchars($b['texto']); ?></span><span class="s"><?php echo htmlspecialchars($b['lmp'] . ' · ' . $b['tipo']); ?></span></span>
                    <span class="pa-pill num" style="background:<?php echo $tarde ? 'rgba(255,59,48,.10)' : '#E9E9EE'; ?>;color:<?php echo $tarde ? '#D70015' : '#3A3A3C'; ?>">
                        <?php echo $b['dias'] === 0 ? 'Hoy' : ($b['dias'] === 1 ? '1 día' : $b['dias'] . ' días'); ?>
                    </span>
                </a>
                <?php endforeach; ?>
                <?php if (!$bandeja): ?><div class="pa-empty-box">Nada pendiente de tu revisión.</div><?php endif; ?>
            </div>
        </section>

        <section class="pa-card pa-col">
            <div class="pa-card-head">
                <h2>Ponderación por proyecto</h2>
                <span class="pa-note"><?php echo $pondProblemas ? $pondProblemas . ' por ajustar' : 'Todas suman 100'; ?></span>
            </div>
            <div class="pa-weights">
                <?php foreach ($pondLista as $p):
                    $t = $TONO[$p['estado']]; ?>
                <div class="pa-w">
                    <div class="top"><span class="k"><?php echo htmlspecialchars($p['k']); ?></span><span class="v num" style="color:<?php echo $t['ink']; ?>"><?php echo dbFmtNum($p['v']); ?></span></div>
                    <div class="bar"><span style="width:<?php echo min(100, round($p['v'])); ?>%;background:<?php echo $t['bar']; ?>"></span></div>
                    <span class="lbl" style="color:<?php echo $t['ink']; ?>"><?php echo $p['estado'] === 'ok' ? 'Completo' : ($p['v'] > 100 ? 'Excede por ' . dbFmtNum($p['v'] - 100) : 'Faltan ' . dbFmtNum(100 - $p['v'])); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (!$pondLista): ?><div class="pa-empty-box">Sin proyectos registrados.</div><?php endif; ?>
        </section>

        <section class="pa-card pa-col">
            <h2>Actividad reciente</h2>
            <?php if (!$actividad): ?>
            <div class="pa-empty-box">Aún no hay aprobaciones ni rechazos en este plan.</div>
            <?php else: ?>
            <ul class="pa-feed">
                <?php foreach ($actividad as $a):
                    $rech = $a['accion'] === 'rechazado'; ?>
                <li>
                    <span class="dot" style="background:<?php echo $rech ? '#FF3B30' : ((int)$a['etapa'] >= 4 ? '#34C759' : '#007AFF'); ?>"></span>
                    <span>
                        <span class="t"><b style="font-weight:600"><?php echo htmlspecialchars($a['usuario_nombre']); ?></b>
                            <?php echo $rech ? 'rechazó' : 'aprobó'; ?> como <?php echo $etapaNombre[(int)$a['etapa']] ?? 'revisor'; ?><?php echo $rech && $a['motivo'] ? ': ' . htmlspecialchars(mb_strimwidth($a['motivo'], 0, 70, '…')) : ''; ?></span>
                        <span class="s"><?php echo relTime($a['fecha_creacion']) . ' · ' . htmlspecialchars($a['lmp']); ?></span>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <?php if (!empty($novedades)): ?>
        <section class="pa-card pa-col" style="padding-bottom:12px">
            <div class="pa-card-head">
                <h2>Novedades</h2>
                <?php if ($esAdmin): ?><a class="pa-link" href="<?php echo $basePath; ?>/novedades">Gestionar</a><?php endif; ?>
            </div>
            <div class="nov-wrap">
                <div class="nov-track" id="novTrack">
                    <?php foreach ($novedades as $nov): ?>
                    <div class="nov-slide">
                        <div class="nov-slide-card">
                            <div class="nov-slide-titulo"><?php echo htmlspecialchars($nov['titulo']); ?></div>
                            <div class="nov-slide-cuerpo"><?php echo htmlspecialchars($nov['contenido']); ?></div>
                            <button class="nov-ver-mas"
                                    onclick="novVerMas(<?php echo htmlspecialchars(json_encode($nov['titulo'])); ?>, <?php echo htmlspecialchars(json_encode($nov['contenido'])); ?>)">
                                Ver más
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($novedades) > 1): ?>
                <button class="nov-nav nov-prev" onclick="novSlide(-1)" aria-label="Novedad anterior"><i class="fas fa-chevron-left"></i></button>
                <button class="nov-nav nov-next" onclick="novSlide(1)" aria-label="Novedad siguiente"><i class="fas fa-chevron-right"></i></button>
                <div class="nov-dots" id="novDots">
                    <?php foreach ($novedades as $i => $nov): ?>
                    <div class="nov-dot <?php echo $i === 0 ? 'active' : ''; ?>" onclick="novGoTo(<?php echo $i; ?>)"></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>

</div>

<!-- Popup Ver más novedades -->
<div class="nov-popup-ov" id="novPopupOv" onclick="if(event.target===this)novCerrarPopup()">
    <div class="nov-popup">
        <div class="nov-popup-head">
            <h4 id="novPopupTitulo"></h4>
            <button class="nov-popup-close" onclick="novCerrarPopup()" aria-label="Cerrar"><i class="fas fa-times"></i></button>
        </div>
        <div class="nov-popup-body" id="novPopupBody"></div>
    </div>
</div>

<script>
// Carrusel de novedades
(function() {
    var track = document.getElementById('novTrack');
    if (!track) return;
    var slides = track.querySelectorAll('.nov-slide');
    if (slides.length < 2) return;
    var total = slides.length, current = 0, timer;

    function goTo(idx) {
        var prev = document.querySelector('#novDots .nov-dot.active');
        if (prev) prev.classList.remove('active');
        current = ((idx % total) + total) % total;
        track.style.transform = 'translateX(-' + (current * 100) + '%)';
        var dots = document.querySelectorAll('#novDots .nov-dot');
        if (dots[current]) dots[current].classList.add('active');
        clearInterval(timer);
        timer = setInterval(function() { goTo(current + 1); }, 8000);
    }

    window.novSlide = function(dir) { goTo(current + dir); };
    window.novGoTo  = function(idx) { goTo(idx); };

    timer = setInterval(function() { goTo(current + 1); }, 8000);
})();

function novVerMas(titulo, contenido) {
    document.getElementById('novPopupTitulo').textContent = titulo;
    document.getElementById('novPopupBody').textContent   = contenido;
    document.getElementById('novPopupOv').classList.add('open');
}
function novCerrarPopup() {
    document.getElementById('novPopupOv').classList.remove('open');
}

// Apertura automática de novedades marcadas "auto_abrir" — una sola vez por sesión de login
(function() {
    var novedadesAutoAbrir = <?php echo json_encode(array_values(array_filter($novedades, fn($n) => !empty($n['auto_abrir']))), JSON_UNESCAPED_UNICODE); ?>;
    if (!novedadesAutoAbrir.length) return;
    var SESSION_KEY = 'nov_autoabierta_sesion';
    if (sessionStorage.getItem(SESSION_KEY)) return;
    sessionStorage.setItem(SESSION_KEY, '1');
    var nov = novedadesAutoAbrir[0];
    setTimeout(function() { novVerMas(nov.titulo, nov.contenido); }, 500);
})();
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
