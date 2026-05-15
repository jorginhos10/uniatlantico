<?php
// config/security.php

// Verificar sesión activa
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . Config::getBasePath() . '/login');
    exit();
}

$__uri = $_SERVER['REQUEST_URI'];
$__rol = $_SESSION['usuario_rol'] ?? '';
$__uid = $_SESSION['usuario_id'] ?? 0;

// Secciones solo para admin
$__adminSections = ['/usuarios', '/configuraciones', '/config-formularios', '/config144'];
foreach ($__adminSections as $__section) {
    if (strpos($__uri, $__section) !== false && $__rol !== 'admin') {
        $_SESSION['error'] = 'No tienes permisos para acceder a esta sección';
        header('Location: ' . Config::getBasePath() . '/403');
        exit();
    }
}

// Rutas que requieren permisos específicos (no-admin)
// ID 1 = ver_dashboard | ID 2 = for_de_144
$__permChecks = [
    '/dashboard'  => 1,
    '/FOR-DE-144' => 2,
    '/modulo144'  => 2,
];

foreach ($__permChecks as $__route => $__permisoId) {
    if ($__rol !== 'admin' && strpos($__uri, $__route) !== false) {
        if (!class_exists('PermisoModel')) {
            require_once __DIR__ . '/../modelo/permisoModel.php';
        }
        if (!isset($__pm)) {
            $__pm    = new PermisoModel();
            $__perms = $__pm->obtenerPermisosUsuario($__uid);
        }
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
}

unset($__uri, $__rol, $__uid, $__adminSections, $__section, $__permChecks, $__route,
      $__permisoId, $__pm, $__perms, $__ok, $__p);
?>