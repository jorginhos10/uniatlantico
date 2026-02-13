<?php
// index.php - ARCHIVO PRINCIPAL PARA PRODUCCIÓN

require_once 'config/config.php';

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

    case 'proveedores':
        require_once 'config/security.php';
        require_once 'controlador/proveedoresController.php';
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
        
    // ====================================================
    // SECCIÓN PARA FOR-DE-144 (Formularios base)
    // ====================================================
    case 'FOR-DE-144':
        require_once 'config/security.php';
        require_once 'controlador/FORDE144Controller.php';
        $forde144Controller = new FORDE144Controller();
        
        if (isset($_GET['action'])) {
            $actionParam = $_GET['action'];
        } else {
            $actionParam = isset($urlParts[1]) ? $urlParts[1] : 'index';
        }
        
        switch ($actionParam) {
            case 'crearFormulario':
                $forde144Controller->crearFormulario();
                break;
            case 'obtenerFormularios':
                $forde144Controller->obtenerFormularios();
                break;
            case 'eliminar':
                $forde144Controller->eliminarFormulario();
                break;
            case 'editar':
                $forde144Controller->editarFormulario();
                break;
            case 'getFormulario':
                $id = $_GET['id'] ?? ($urlParts[2] ?? 0);
                $forde144Controller->getFormulario($id);
                break;
            default:
                $forde144Controller->index();
                break;
        }
        break;
    
    // ====================================================
    // SECCIÓN PARA FORMULACIÓN 144 (Borradores y Productivos)
    // ====================================================
    case 'formulacion144':
        require_once 'config/security.php';
        require_once 'controlador/Formulacion144Controller.php';
        $formulacion144Controller = new Formulacion144Controller();
        
        if (isset($_GET['action'])) {
            $actionParam = $_GET['action'];
        } else {
            $actionParam = isset($urlParts[1]) ? $urlParts[1] : 'index';
        }
        
        switch ($actionParam) {
            case 'crearBorrador':
                $formulacion144Controller->crearBorrador();
                break;
            case 'getBorrador':
                $formulacion144Controller->getBorrador();
                break;
            case 'guardar':
                $formulacion144Controller->guardar();
                break;
            case 'cambiarEstado':
                $formulacion144Controller->cambiarEstado();
                break;
            case 'eliminar':
                $formulacion144Controller->eliminar();
                break;
            case 'duplicar':
                $formulacion144Controller->duplicar();
                break;
            case 'test':
                $formulacion144Controller->test();
                break;
            case 'index':
            default:
                $formulacion144Controller->index();
                break;
        }
        break;
    // ====================================================




// ====================================================
// SECCIÓN PARA MÓDULO 144 (MÚLTIPLES MÓDULOS EN ACORDEÓN)
// ====================================================
case 'modulo144':
    require_once 'config/security.php';
    require_once 'controlador/Modulo144Controller.php';
    $modulo144Controller = new Modulo144Controller();
    
    if (isset($_GET['action'])) {
        $actionParam = $_GET['action'];
    } else {
        $actionParam = isset($urlParts[1]) ? $urlParts[1] : 'index';
    }
    
    switch ($actionParam) {
        case 'crearBorrador':
            $modulo144Controller->crearBorrador();
            break;
        case 'getBorrador':
            $modulo144Controller->getBorrador();
            break;
        case 'guardar':
            $modulo144Controller->guardar();
            break;
        case 'cambiarEstado':
            $modulo144Controller->cambiarEstado();
            break;
        case 'eliminar':
            $modulo144Controller->eliminar();
            break;
        case 'duplicar':
            $modulo144Controller->duplicar();
            break;
        case 'test':
            $modulo144Controller->test();
            break;
        case 'index':
        default:
            $modulo144Controller->index();
            break;
    }
    break;
// ====================================================
        
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