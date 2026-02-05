<?php
// index.php - ARCHIVO PRINCIPAL COMPLETO

require_once 'config/config.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$url = $_GET['url'] ?? 'login';
$url = rtrim($url, '/');
$urlParts = explode('/', $url);

$action = $urlParts[0] ?? 'login';
$basePath = Config::getBasePath();

switch ($action) {
    case 'login':
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            header("Location: {$basePath}/dashboard");
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'controlador/authController.php';
            $authController = new AuthController();
            $authController->login();
        } else {
            require_once 'vista/login/login.php';
        }
        break;
        
    case 'logout':
        require_once 'controlador/authController.php';
        $authController = new AuthController();
        $authController->logout();
        break;
        
    case 'dashboard':
        require_once 'config/security.php';
        require_once 'vista/dashboard/index.php';
        break;

    case '403':
        require_once 'config/security.php';
        require_once 'vista/complementos/403.php';
        break;

    case 'proveedores':  // ← CORREGIDO: AHORA USA CONTROLADOR
        require_once 'config/security.php';
        
        // Verificar que el controlador exista
        $controladorPath = 'controlador/proveedoresController.php';
        if (!file_exists($controladorPath)) {
            die("Error: No se encuentra el controlador de proveedores en: $controladorPath");
        }
        
        require_once $controladorPath;
        $proveedorController = new proveedoresController();
        
        $subaction = $urlParts[1] ?? 'index';
        
        switch ($subaction) {
            case 'categorias':
                 $proveedorController->getCategorias();
                break;
            case 'crear':
                $proveedorController->crear();
                break;
            case 'eliminar':
                $proveedorController->eliminar();
                break;
            case 'buscar':
                $proveedorController->buscar();
                break;
            default:
                $proveedorController->index();
                break;
            
        }
        break;

    case 'menu':
        require_once 'config/security.php';
        require_once 'vista/menu/index.php';
        break;

    case 'configuraciones':
        require_once 'config/security.php';
        require_once 'vista/configuraciones/index.php';
        break;
     
        
    case 'usuarios':
        require_once 'config/security.php';
        require_once 'controlador/usuarioController.php';
        $usuarioController = new UsuarioController();
        
        $subaction = $urlParts[1] ?? 'index';
        
        switch ($subaction) {
            case 'crear':
                $usuarioController->crear();
                break;
            case 'editar':
                $id = $urlParts[2] ?? 0;
                $usuarioController->editar($id);
                break;
            case 'eliminar':
                $usuarioController->eliminar();
                break;
            case 'get':
                $id = $urlParts[2] ?? 0;
                $usuarioController->get($id);
                break;
            case 'update':
                $usuarioController->update();
                break;
            case 'update-status':
                $usuarioController->updateStatus();
                break;
            case 'reset-password':
                $usuarioController->resetPassword();
                break;
            case 'get-stats':
                $usuarioController->getStats();
                break;
            case 'get-realtime-stats':
                $usuarioController->getRealTimeStats();
                break;
            default:
                $usuarioController->index();
                break;
        }
        break;
        
    case 'permisos':
        require_once 'config/security.php';
        require_once 'controlador/permisoPopupController.php';
        $permisoController = new PermisoPopupController();
        
        $subaction = $urlParts[1] ?? '';
        
        switch ($subaction) {
            case 'popup-get':
                $usuario_id = $urlParts[2] ?? 0;
                $permisoController->getPermisosPopup($usuario_id);
                break;
            case 'popup-toggle':
                $permisoController->togglePermisoPopup();
                break;
            case 'all':
                $permisoController->getAllPermisos();
                break;
            default:
                header("Location: {$basePath}/usuarios");
                break;
        }
        break;
        
    case 'publica':
        require_once 'vista/publica/index.php';
        break;
        
    case '':
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            header("Location: {$basePath}/dashboard");
        } else {
            header("Location: {$basePath}/login");
        }
        exit;
        
    default:
        require_once 'vista/complementos/404.php';
        break;
}
?>