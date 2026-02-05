<?php
// vista/complementos/sidebar.php

// Incluir config para usar la clase Config
require_once __DIR__ . '/../../config/config.php';

// Incluir el modelo de permisos
require_once __DIR__ . '/../../modelo/permisoModel.php';

$basePath = Config::getBasePath();
$baseUrl = Config::getBaseUrl();
$usuarioLogueado = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$esAdmin = isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';

// Obtener permisos del usuario logueado
$permisosUsuario = [];
if ($usuarioLogueado && isset($_SESSION['usuario_id'])) {
    try {
        $permisoModel = new PermisoModel();
        $permisosUsuario = $permisoModel->obtenerPermisosUsuario($_SESSION['usuario_id']);
        
        // Crear un array de IDs de permisos activos
        $permisosActivosIds = [];
        foreach ($permisosUsuario as $permiso) {
            if ($permiso['activo'] == 1) {
                $permisosActivosIds[] = $permiso['id'];
            }
        }
    } catch (Exception $e) {
        error_log("Error cargando permisos para sidebar: " . $e->getMessage());
    }
}

// Función para verificar si el usuario tiene un permiso específico por ID
function tienePermisoPorId($permisoId, $permisosActivosIds, $esAdmin = false) {
    // Los administradores tienen acceso a todo
    if ($esAdmin) {
        return true;
    }
    
    // Verificar si el ID del permiso está en la lista de permisos activos
    return in_array($permisoId, $permisosActivosIds);
}

// Definir los IDs de permisos (debes ajustarlos según tu base de datos)
define('PERMISO_DASHBOARD_ID', 1);    // ver_dashboard
define('PERMISO_RECETAS_ID', 2);      // gestionar_recetas
define('PERMISO_INVENTARIO_ID', 3);   // gestionar_inventario
define('PERMISO_REPORTES_ID', 4);     // ver_reportes
define('PERMISO_CONFIG_ID', 5);       // configurar_sistema
define('PERMISO_USUARIOS_ID', 6);     // gestionar_usuarios (crear/editar/eliminar)
define('PERMISO_PERMISOS_ID', 7);     // gestionar_permisos
?>
<nav class="sidebar">
    <div class="logo">
        <img class="img-sidebar" src="<?php echo $baseUrl; ?>/assets/media/src/logo.png" alt="Logo ChefControl">
    </div>
    <ul class="navMenu">
        <?php if ($usuarioLogueado): ?>
            <!-- Menú para usuarios logueados -->
            
            <!-- Dashboard - Permiso ID: 1 (ver_dashboard) -->
            <?php if (tienePermisoPorId(PERMISO_DASHBOARD_ID, $permisosActivosIds ?? [], $esAdmin)): ?>
                <li class="navItem <?php echo ($paginaActual ?? '') === 'dashboard' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>/dashboard" class="navLink" data-tooltip="Dashboard">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Usuarios - Permiso ID: 6 (gestionar_usuarios) -->
            <?php if (tienePermisoPorId(PERMISO_USUARIOS_ID, $permisosActivosIds ?? [], $esAdmin)): ?>
                <li class="navItem <?php echo ($paginaActual ?? '') === 'usuarios' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>/usuarios" class="navLink" data-tooltip="Usuarios">
                        <i class="fas fa-users"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Recetas - Permiso ID: 2 (gestionar_recetas) -->
            <?php if (tienePermisoPorId(PERMISO_RECETAS_ID, $permisosActivosIds ?? [], $esAdmin)): ?>
                <li class="navItem <?php echo ($paginaActual ?? '') === 'recetas' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>/recetas" class="navLink" data-tooltip="Recetas">
                        <i class="fas fa-book"></i>
                        <span>Formato FOR-DE-144</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Inventario - Permiso ID: 3 (gestionar_inventario) -->
            <?php if (tienePermisoPorId(PERMISO_INVENTARIO_ID, $permisosActivosIds ?? [], $esAdmin)): ?>
                <li class="navItem <?php echo ($paginaActual ?? '') === 'inventario' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>/inventario" class="navLink" data-tooltip="Inventario">
                        <i class="fas fa-boxes"></i>
                        <span>Inventario</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Reportes - Permiso ID: 4 (ver_reportes) -->
            <?php if (tienePermisoPorId(PERMISO_REPORTES_ID, $permisosActivosIds ?? [], $esAdmin)): ?>
                <li class="navItem <?php echo ($paginaActual ?? '') === 'reportes' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>/reportes" class="navLink" data-tooltip="Reportes">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reportes</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Configuración - Permiso ID: 5 (configurar_sistema) -->
            <?php if (tienePermisoPorId(PERMISO_CONFIG_ID, $permisosActivosIds ?? [], $esAdmin)): ?>
                <li class="navItem <?php echo ($paginaActual ?? '') === 'configuraciones' ? 'active' : ''; ?>">
                    <a href="<?php echo $basePath; ?>/configuracion" class="navLink" data-tooltip="Configuración">
                        <i class="fas fa-cog"></i>
                        <span>Configuraciones</span>
                    </a>
                </li>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Menú para usuarios no logueados -->
            <li class="navItem <?php echo ($paginaActual ?? '') === 'publica' ? 'active' : ''; ?>">
                <a href="<?php echo $basePath; ?>/publica" class="navLink" data-tooltip="Inicio">
                    <i class="fas fa-globe"></i>
                    <span>Inicio</span>
                </a>
            </li>
            <li class="navItem <?php echo ($paginaActual ?? '') === 'login' ? 'active' : ''; ?>">
                <a href="<?php echo $basePath; ?>/login" class="navLink" data-tooltip="Iniciar Sesión">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Iniciar Sesión</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/sidebar.css">