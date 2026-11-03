<?php
// config/security.php

require_once __DIR__ . '/roles_helper.php';

// Verificar sesión activa
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . Config::getBasePath() . '/login');
    exit();
}

// ── CSRF: una petición que modifica datos debe venir de este mismo sitio ──
// Además de la cookie SameSite, si el navegador informa el origen (Origin o Referer) debe coincidir con el host de la app.
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
    $__origen = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
    if ($__origen !== '') {
        $__hostOrigen = strtolower((string)parse_url($__origen, PHP_URL_HOST));
        $__propios = [];
        foreach ([$_SERVER['HTTP_HOST'] ?? '', $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '', $_SERVER['SERVER_NAME'] ?? ''] as $__h) {
            $__h = strtolower(trim(explode(',', (string)$__h)[0]));
            if ($__h !== '') $__propios[] = preg_replace('/:\d+$/', '', $__h);
        }
        if ($__hostOrigen !== '' && !in_array($__hostOrigen, $__propios, true)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            exit(json_encode(['success' => false, 'message' => 'Petición no permitida desde otro sitio']));
        }
    }
    unset($__origen, $__hostOrigen, $__propios, $__h);
}

// ── La sesión no puede ir por delante de la base de datos ──
// Cada minuto se relee el usuario: si lo desactivaron o lo borraron se cierra su sesión, y un cambio de rol o de
// dependencia rige de inmediato, sin esperar a que vuelva a iniciar sesión.
if (empty($_SESSION['impersonador']) && (time() - (int)($_SESSION['rol_revisado'] ?? 0)) > 60) {
    try {
        $__dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME . ';charset=' . Config::DB_CHARSET;
        $__pdo = new PDO($__dsn, Config::DB_USER, Config::DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $__q = $__pdo->prepare('SELECT rol, activo, cargo_id FROM usuarios WHERE id = :id');
        $__q->execute([':id' => (int)($_SESSION['usuario_id'] ?? 0)]);
        $__u = $__q->fetch(PDO::FETCH_ASSOC);
        if (!$__u || (int)$__u['activo'] !== 1) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['error'] = 'Tu cuenta ya no está activa.';
            header('Location: ' . Config::getBasePath() . '/login');
            exit();
        }
        $_SESSION['usuario_rol'] = $__u['rol'];
        $_SESSION['usuario_cargo_id'] = $__u['cargo_id'];
        $_SESSION['rol_revisado'] = time();
    } catch (PDOException $e) {
        error_log('security.php: no se pudo revisar el usuario: ' . $e->getMessage()); // sin base no se cierra la sesión
    }
    unset($__dsn, $__pdo, $__q, $__u);
}

// La sección se decide por la ruta que atiende el router (primer segmento), no por buscar texto en la URL:
// «?url=usuarios» o «?x=/usuarios» evadían o disparaban la comprobación anterior.
$__seccion = $action ?? strtolower(explode('/', trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/'))[0]);
$__uid = Roles::usuarioId();

// Secciones solo para el administrador del sistema
$__adminSections = ['usuarios', 'configuraciones', 'config-formularios', 'config144', 'snapshots', 'dependencias',
                    'roles', 'facultades', 'novedades', 'almacenamiento', 'catalogos144', 'proveedores'];
if (in_array($__seccion, $__adminSections, true) && !Roles::esAdminSistema()) {
    $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
    header('Location: ' . Config::getBasePath() . '/403');
    exit();
}

// Rutas que requieren permisos específicos (no-admin)
// ID 1 = ver_dashboard | ID 2 = for_de_144
$__permChecks = [
    'dashboard'   => 1,
    'FOR-DE-144'  => 2,
    'modulo144'   => 2,
];

if (isset($__permChecks[$__seccion]) && !Roles::esAdminSistema()) {
    $__permisoId = $__permChecks[$__seccion];
    if (!class_exists('PermisoModel')) {
        require_once __DIR__ . '/../modelo/permisoModel.php';
    }
    $__pm    = new PermisoModel();
    $__perms = $__pm->obtenerPermisosUsuario($__uid);
    $__ok = false;
    foreach ($__perms as $__p) {
        if ((int)$__p['id'] === $__permisoId && (int)$__p['activo'] === 1) { $__ok = true; break; }
    }
    if (!$__ok) {
        $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
        header('Location: ' . Config::getBasePath() . '/403');
        exit();
    }
}

unset($__seccion, $__uid, $__adminSections, $__permChecks, $__permisoId, $__pm, $__perms, $__ok, $__p);
?>
