<?php
// index.php - ARCHIVO PRINCIPAL PARA PRODUCCIÓN (VERSIÓN COMPLETA)

require_once 'config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$basePath = Config::getBasePath();

// Support both Apache mod_rewrite (?url=X) and direct-server setups (PHP built-in, nginx)
if (isset($_GET['url']) && $_GET['url'] !== '') {
    $url = $_GET['url'];
} else {
    // Parse from REQUEST_URI — strip base path and query string
    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $reqPath = str_replace('\\', '/', $reqPath ?? '/');
    if ($basePath !== '' && strpos($reqPath, $basePath) === 0) {
        $reqPath = substr($reqPath, strlen($basePath));
    }
    $url = ltrim($reqPath, '/') ?: 'login';
    // Strip index.php from path if present
    if ($url === 'index.php') $url = 'login';
}

$url = rtrim($url, '/');
$urlParts = explode('/', $url);

$action = $urlParts[0] ?? 'login';

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

    case 'perfil':
        require_once 'config/security.php';
        require_once 'controlador/perfilController.php';
        $perfilController = new PerfilController();
        $subaction = $urlParts[1] ?? 'index';
        switch ($subaction) {
            case 'update-avatar':
                $perfilController->updateAvatar();
                break;
            case 'update-password':
                $perfilController->updatePassword();
                break;
            default:
                $perfilController->index();
                break;
        }
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
        
        $subaction = $urlParts[1] ?? 'index';
        
        switch ($subaction) {
            case 'config-formularios':
                require_once 'vista/configuraciones/config-formularios.php';
                break;
            case 'proveedores':
                require_once 'vista/configuraciones/proveedores.php';
                break;
            default:
                require_once 'vista/configuraciones/index.php';
                break;
        }
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
            case 'subpermiso-toggle':
                $permisoController->toggleSubpermisoPopup();
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
    // SECCIÓN PARA FORMULARIOS (Página principal de formularios)
    // ====================================================
    case 'config-formularios':
        require_once 'config/security.php';
        require_once 'vista/configuraciones/formularios.php';
        break;
    
    // ====================================================
    // SECCIÓN PARA FOR-DE-144 (Formularios base)
    // ====================================================
    case 'FOR-DE-144':
        require_once 'config/security.php';
        require_once 'controlador/FORDE144Controller.php';
        $forde144Controller = new FORDE144Controller();
        
        // Obtener la acción
        if (isset($_GET['action'])) {
            $actionParam = $_GET['action'];
        } else {
            $actionParam = isset($urlParts[1]) ? $urlParts[1] : 'index';
        }
        
        // Manejar las diferentes acciones
        switch ($actionParam) {
            case 'crear':
                $forde144Controller->crear();
                break;
                
            case 'obtenerFormularios':
                $forde144Controller->obtenerFormularios();
                break;
                
            case 'eliminar':
                $forde144Controller->eliminar();
                break;
                
            case 'editar':
                $forde144Controller->editar();
                break;
                
            case 'getFormulario':
                $id = $_GET['id'] ?? ($urlParts[2] ?? 0);
                $forde144Controller->getFormulario($id);
                break;

            case 'informe':
                $id = $_GET['id'] ?? ($urlParts[2] ?? 0);
                $forde144Controller->informe($id);
                break;

            case 'informePage':
                $id = $_GET['id'] ?? ($urlParts[2] ?? 0);
                $forde144Controller->informePage($id);
                break;

            case 'verificarDisponibilidad':
                $forde144Controller->verificarDisponibilidad();
                break;
                
            case 'index':
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
            case 'getEstrategiasPorLinea':
                $modulo144Controller->getEstrategiasPorLinea();
                break;
            case 'getMotoresPorLinea':
                $modulo144Controller->getMotoresPorLinea();
                break;
            case 'getProyectosPorLineaYMotor':
                $modulo144Controller->getProyectosPorLineaYMotor();
                break;
            case 'getFilterPreference':
                $modulo144Controller->getFilterPreference();
                break;
            case 'saveFilterPreference':
                $modulo144Controller->saveFilterPreference();
                break;
            case 'getPonderaciones':
                $modulo144Controller->getPonderaciones();
                break;
            case 'contarRegistros':
                $modulo144Controller->contarRegistros();
                break;
            case 'index':
            default:
                $modulo144Controller->index();
                break;
        }
        break;
    
    // ====================================================
    // SECCIÓN PARA CONFIG-144 (Configuración de años) - VERSIÓN COMPLETA CON NUEVAS TABLAS
    // ====================================================
    case 'config144':
        require_once 'config/security.php';
        require_once 'controlador/config144Controller.php';
        $config144Controller = new config144Controller();
        
        if (isset($_GET['action'])) {
            $actionParam = $_GET['action'];
        } else {
            $actionParam = isset($urlParts[1]) ? $urlParts[1] : 'index';
        }
        
        switch ($actionParam) {
            // Endpoints básicos para años
            case 'listar':
                $config144Controller->listar();
                break;
            case 'activos':
                $config144Controller->activos();
                break;
            case 'crear':
                $config144Controller->crear();
                break;
            case 'get':
                $config144Controller->get();
                break;
            case 'actualizar':
                $config144Controller->actualizar();
                break;
            case 'cambiarEstado':
                $config144Controller->cambiarEstado();
                break;
            case 'eliminar':
                $config144Controller->eliminar();
                break;
            case 'actualizarOrden':
                $config144Controller->actualizarOrden();
                break;
            
            // Endpoints para obtener datos de líneas, motores y proyectos
            case 'getLineasEstrategicas':
                $config144Controller->getLineasEstrategicas();
                break;
            case 'getMotoresPorLinea':
                $config144Controller->getMotoresPorLinea();
                break;
            case 'getProyectosPorMotor':
                $config144Controller->getProyectosPorMotor();
                break;
            
            // NUEVOS ENDPOINTS para las tablas data_*
            case 'getDataMotores':
                $config144Controller->getDataMotores();
                break;
            case 'getDataProyectos':
                $config144Controller->getDataProyectos();
                break;
            case 'guardarDataMotores':
                $config144Controller->guardarDataMotores();
                break;
            case 'guardarDataProyectos':
                $config144Controller->guardarDataProyectos();
                break;
            case 'getDataDistribucionPorAnio':
                $config144Controller->getDataDistribucionPorAnio();
                break;
            case 'guardarDataDistribucion':
                $config144Controller->guardarDataDistribucion();
                break;
            case 'verificarDatos':
                $config144Controller->verificarDatos();
                break;
            
            // ENDPOINTS ANTIGUOS (mantenidos por compatibilidad, pero redirigidos a los nuevos)
            case 'getPorcentajesMotores':
                // Redirigir al nuevo endpoint
                $config144Controller->getDataMotores();
                break;
            case 'getPorcentajesProyectos':
                // Redirigir al nuevo endpoint
                $config144Controller->getDataProyectos();
                break;
            case 'guardarPorcentajesMotores':
                // Redirigir al nuevo endpoint
                $config144Controller->guardarDataMotores();
                break;
            case 'guardarPorcentajesProyectos':
                // Redirigir al nuevo endpoint
                $config144Controller->guardarDataProyectos();
                break;
            case 'getDistribucionPorAnio':
                // Redirigir al nuevo endpoint
                $config144Controller->getDataDistribucionPorAnio();
                break;
            case 'guardarDistribucion':
                // Redirigir al nuevo endpoint
                $config144Controller->guardarDataDistribucion();
                break;
            
            case 'index':
            default:
                $config144Controller->index();
                break;
        }
        break;
    // ====================================================
    // SECCIÓN CATALOGOS 144: LÍNEAS ESTRATÉGICAS, MOTORES, PROYECTOS (CRUD)
    // ====================================================
    case 'catalogos144':
        require_once 'config/security.php';
        require_once 'controlador/catalogos144Controller.php';
        $catalogos144Controller = new Catalogos144Controller();

        $actionParam = isset($_GET['action']) ? $_GET['action'] : (isset($urlParts[1]) ? $urlParts[1] : 'index');

        switch ($actionParam) {
            // Líneas estratégicas
            case 'listarLineas':
                $catalogos144Controller->listarLineas();
                break;
            case 'getLinea':
                $catalogos144Controller->getLinea();
                break;
            case 'crearLinea':
                $catalogos144Controller->crearLinea();
                break;
            case 'actualizarLinea':
                $catalogos144Controller->actualizarLinea();
                break;
            case 'cambiarEstadoLinea':
                $catalogos144Controller->cambiarEstadoLinea();
                break;
            case 'eliminarLinea':
                $catalogos144Controller->eliminarLinea();
                break;

            // Estrategias
            case 'listarEstrategias':
                $catalogos144Controller->listarEstrategias();
                break;
            case 'getEstrategia':
                $catalogos144Controller->getEstrategia();
                break;
            case 'crearEstrategia':
                $catalogos144Controller->crearEstrategia();
                break;
            case 'actualizarEstrategia':
                $catalogos144Controller->actualizarEstrategia();
                break;
            case 'cambiarEstadoEstrategia':
                $catalogos144Controller->cambiarEstadoEstrategia();
                break;
            case 'eliminarEstrategia':
                $catalogos144Controller->eliminarEstrategia();
                break;

            // Motores
            case 'listarMotores':
                $catalogos144Controller->listarMotores();
                break;
            case 'getMotor':
                $catalogos144Controller->getMotor();
                break;
            case 'crearMotor':
                $catalogos144Controller->crearMotor();
                break;
            case 'actualizarMotor':
                $catalogos144Controller->actualizarMotor();
                break;
            case 'cambiarEstadoMotor':
                $catalogos144Controller->cambiarEstadoMotor();
                break;
            case 'eliminarMotor':
                $catalogos144Controller->eliminarMotor();
                break;

            // Proyectos
            case 'listarProyectos':
                $catalogos144Controller->listarProyectos();
                break;
            case 'getProyecto':
                $catalogos144Controller->getProyecto();
                break;
            case 'crearProyecto':
                $catalogos144Controller->crearProyecto();
                break;
            case 'actualizarProyecto':
                $catalogos144Controller->actualizarProyecto();
                break;
            case 'cambiarEstadoProyecto':
                $catalogos144Controller->cambiarEstadoProyecto();
                break;
            case 'eliminarProyecto':
                $catalogos144Controller->eliminarProyecto();
                break;

            // Auxiliares
            case 'lineasActivas':
                $catalogos144Controller->lineasActivas();
                break;
            case 'motoresPorLinea':
                $catalogos144Controller->motoresPorLinea();
                break;

            case 'index':
            default:
                $catalogos144Controller->index();
                break;
        }
        break;
    // ====================================================
    // SECCIÓN PARA ROLES / CARGOS (CRUD)
    // ====================================================
    case 'roles':
        require_once 'config/security.php';
        require_once 'controlador/rolesController.php';
        $rolesController = new RolesController();

        $actionParam = isset($urlParts[1]) ? $urlParts[1] : 'index';

        switch ($actionParam) {
            case 'listar':
                $rolesController->listar();
                break;
            case 'activos':
                $rolesController->activos();
                break;
            case 'actualizarNombre':
                $rolesController->actualizarNombre();
                break;
            case 'index':
            default:
                $rolesController->index();
                break;
        }
        break;

    // ====================================================
    // SECCIÓN PARA DEPENDENCIAS (CRUD)
    // ====================================================
    case 'dependencias':
        require_once 'config/security.php';
        require_once 'controlador/dependenciasController.php';
        $dependenciasController = new DependenciasController();

        $actionParam = isset($urlParts[1]) ? $urlParts[1] : 'index';

        switch ($actionParam) {
            case 'listar':
                $dependenciasController->listar();
                break;
            case 'crear':
                $dependenciasController->crear();
                break;
            case 'get':
                $dependenciasController->get();
                break;
            case 'actualizar':
                $dependenciasController->actualizar();
                break;
            case 'eliminar':
                $dependenciasController->eliminar();
                break;
            case 'cambiarEstado':
                $dependenciasController->cambiarEstado();
                break;
            case 'index':
            default:
                $dependenciasController->index();
                break;
        }
        break;

    // ====================================================

    // ====================================================
    // SECCIÓN PARA MENSAJES
    // ====================================================
    case 'mensajes':
        require_once 'config/security.php';
        require_once 'controlador/MensajesController.php';
        $mensajesController = new MensajesController();

        $actionParam = $_GET['action'] ?? ($urlParts[1] ?? 'index');

        switch ($actionParam) {
            case 'listar':
                $mensajesController->listar();
                break;
            case 'crear':
                $mensajesController->crear();
                break;
            case 'ver':
                $mensajesController->ver();
                break;
            case 'eliminar':
                $mensajesController->eliminar();
                break;
            case 'contarNoLeidos':
                $mensajesController->contarNoLeidos();
                break;
            case 'recientesNoLeidos':
                $mensajesController->recientesNoLeidos();
                break;
            case 'index':
            default:
                $mensajesController->index();
                break;
        }
        break;

    case 'novedades':
        require_once 'config/security.php';
        require_once 'controlador/NovedadesController.php';
        $novedadesController = new NovedadesController();
        $actionParam = $urlParts[1] ?? 'index';
        switch ($actionParam) {
            case 'listar':    $novedadesController->listar();      break;
            case 'get':       $novedadesController->get();         break;
            case 'crear':     $novedadesController->crear();       break;
            case 'actualizar':$novedadesController->actualizar();  break;
            case 'eliminar':  $novedadesController->eliminar();    break;
            case 'toggle':    $novedadesController->toggleActivo();  break;
            case 'reordenar': $novedadesController->reordenar();    break;
            default:          $novedadesController->index();       break;
        }
        break;

    case 'almacenamiento':
        require_once 'config/security.php';
        $actionParam = $urlParts[1] ?? 'index';
        switch ($actionParam) {
            default:
                require_once 'vista/almacenamiento/index.php';
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
        // Verificar si es una ruta de API o archivo estático
        if (strpos($action, 'assets/') === 0) {
            // Servir archivos estáticos
            $filePath = $action;
            if (file_exists($filePath)) {
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $mimeTypes = [
                    'css' => 'text/css',
                    'js' => 'application/javascript',
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'ico' => 'image/x-icon',
                    'svg' => 'image/svg+xml'
                ];
                
                if (isset($mimeTypes[$extension])) {
                    header('Content-Type: ' . $mimeTypes[$extension]);
                }
                
                readfile($filePath);
                exit;
            }
        }
        
        // Si no es un archivo estático, mostrar 404
        require_once 'vista/complementos/404.php';
        break;
}

// Función de ayuda para depuración (opcional)
function debug_log($message, $data = null) {
    $logFile = 'debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($data !== null) {
        $logMessage .= " - " . print_r($data, true);
    }
    
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}
?>