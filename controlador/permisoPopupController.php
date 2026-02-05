<?php
// controlador/permisoPopupController.php - VERSIÓN COMPLETA CON DEBUGGING

require_once 'modelo/permisoModel.php';

class PermisoPopupController {
    private $permisoModel;

    public function __construct() {
        error_log("🔄 Inicializando PermisoPopupController...");
        $this->permisoModel = new PermisoModel();
    }

    /**
     * Obtener permisos de un usuario para mostrar en popup
     */
    public function getPermisosPopup($usuario_id) {
        header('Content-Type: application/json');
        
        error_log("📥 GET Permisos Popup - Usuario ID: $usuario_id");
        error_log("📥 SESSION usuario_id: " . ($_SESSION['usuario_id'] ?? 'NO SET'));
        error_log("📥 SESSION usuario_rol: " . ($_SESSION['usuario_rol'] ?? 'NO SET'));
        
        // Validar que el usuario esté logueado
        if (!isset($_SESSION['usuario_id'])) {
            error_log("❌ No autenticado - SESSION vacía");
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }
        
        // Validar que sea administrador
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            error_log("❌ No es admin - Rol: " . ($_SESSION['usuario_rol'] ?? 'NO SET'));
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para gestionar permisos']);
            exit;
        }
        
        if (empty($usuario_id) || !is_numeric($usuario_id)) {
            error_log("❌ ID de usuario no válido: $usuario_id");
            echo json_encode(['success' => false, 'message' => 'ID de usuario no válido']);
            exit;
        }
        
        // Validar seguridad - nadie puede ver/modificar permisos del superadmin (ID 1)
        if ($usuario_id == 1) {
            error_log("❌ Intento de acceder a permisos del superadmin");
            echo json_encode(['success' => false, 'message' => 'No se pueden ver/modificar los permisos del administrador principal']);
            exit;
        }
        
        // El usuario no puede ver/modificar sus propios permisos desde aquí
        if ($usuario_id == $_SESSION['usuario_id']) {
            error_log("❌ Usuario intentando ver sus propios permisos: $usuario_id");
            echo json_encode(['success' => false, 'message' => 'No puedes ver/modificar tus propios permisos desde aquí']);
            exit;
        }
        
        try {
            // Obtener permisos del usuario (solo los activos)
            $permisos = $this->permisoModel->obtenerPermisosUsuario($usuario_id);
            
            error_log("📋 Permisos obtenidos: " . count($permisos));
            
            // Verificar si hay permisos activos en el sistema
            if (empty($permisos)) {
                error_log("⚠️ No hay permisos activos para usuario $usuario_id");
                echo json_encode([
                    'success' => false,
                    'message' => 'No hay permisos activos configurados en el sistema'
                ]);
                exit;
            }
            
            // Obtener información del usuario
            $infoUsuario = $this->permisoModel->obtenerInfoUsuarioParaPermisos($usuario_id);
            
            if (!$infoUsuario) {
                error_log("❌ Usuario $usuario_id no encontrado en BD");
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
                exit;
            }
            
            // Obtener estadísticas
            $estadisticas = $this->permisoModel->obtenerEstadisticasPermisos($usuario_id);
            
            $response = [
                'success' => true,
                'usuario' => $infoUsuario,
                'permisos' => $permisos,
                'estadisticas' => $estadisticas,
                'total_permisos' => count($permisos),
                'debug' => [
                    'session_usuario_id' => $_SESSION['usuario_id'],
                    'session_usuario_rol' => $_SESSION['usuario_rol'],
                    'requested_user_id' => $usuario_id
                ]
            ];
            
            error_log("✅ Enviando respuesta exitosa");
            echo json_encode($response);
            
        } catch (Exception $e) {
            error_log("❌ Error en getPermisosPopup: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error del servidor al cargar permisos: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Alternar permiso desde popup
     */
    public function togglePermisoPopup() {
        header('Content-Type: application/json');
        
        error_log("🔄 TOGGLE Permiso Popup - Iniciando");
        error_log("📥 Método HTTP: " . $_SERVER['REQUEST_METHOD']);
        error_log("📥 Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'NO SET'));
        
        // Log raw input
        $rawInput = file_get_contents('php://input');
        error_log("📥 Raw input: " . $rawInput);
        
        // Validar que el usuario esté logueado
        if (!isset($_SESSION['usuario_id'])) {
            error_log("❌ No autenticado en toggle");
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }
        
        // Validar que sea administrador
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            error_log("❌ No es admin en toggle - Rol: " . ($_SESSION['usuario_rol'] ?? 'NO SET'));
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para gestionar permisos']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("❌ Método incorrecto: " . $_SERVER['REQUEST_METHOD']);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        // Obtener datos
        $input = json_decode($rawInput, true);
        
        if (!$input && !empty($rawInput)) {
            // Intentar parsear como form data
            parse_str($rawInput, $input);
        }
        
        if (!$input) {
            $input = $_POST; // Fallback a POST
        }
        
        $usuario_id = $input['usuario_id'] ?? 0;
        $permiso_id = $input['permiso_id'] ?? 0;
        $nuevo_estado = $input['nuevo_estado'] ?? 0;
        
        error_log("📥 Datos recibidos - Usuario: $usuario_id, Permiso: $permiso_id, Estado: $nuevo_estado");
        error_log("📥 Input completo: " . json_encode($input));
        
        // Validaciones
        if (empty($usuario_id) || empty($permiso_id)) {
            error_log("❌ Datos incompletos");
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        // Validar seguridad - nadie puede modificar permisos del superadmin (ID 1)
        if ($usuario_id == 1) {
            error_log("❌ Intento de modificar permisos del superadmin");
            echo json_encode(['success' => false, 'message' => 'No se pueden modificar los permisos del administrador principal']);
            exit;
        }
        
        // El usuario no puede modificar sus propios permisos
        if ($usuario_id == $_SESSION['usuario_id']) {
            error_log("❌ Usuario intentando modificar sus propios permisos: $usuario_id");
            echo json_encode(['success' => false, 'message' => 'No puedes modificar tus propios permisos']);
            exit;
        }
        
        // Verificar que el permiso esté activo en lista_permisos
        try {
            $permisoActivo = $this->permisoModel->permisoEstaActivo($permiso_id);
            
            if (!$permisoActivo) {
                error_log("❌ Permiso $permiso_id no está activo");
                echo json_encode([
                    'success' => false,
                    'message' => 'Este permiso no está disponible para asignación'
                ]);
                exit;
            }
            
            // Alternar permiso
            error_log("🔄 Ejecutando togglePermisoUsuario...");
            $resultado = $this->permisoModel->togglePermisoUsuario($usuario_id, $permiso_id, $nuevo_estado);
            
            if ($resultado) {
                // Obtener estadísticas actualizadas
                $estadisticas = $this->permisoModel->obtenerEstadisticasPermisos($usuario_id);
                
                $response = [
                    'success' => true,
                    'message' => $nuevo_estado ? '✅ Permiso asignado correctamente' : '❌ Permiso removido correctamente',
                    'nuevo_estado' => $nuevo_estado,
                    'estadisticas' => $estadisticas,
                    'debug' => [
                        'usuario_id' => $usuario_id,
                        'permiso_id' => $permiso_id,
                        'nuevo_estado' => $nuevo_estado,
                        'resultado' => $resultado
                    ]
                ];
                
                error_log("✅ Toggle exitoso");
                echo json_encode($response);
            } else {
                error_log("❌ Toggle falló en el modelo");
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar el permiso en la base de datos'
                ]);
            }
        } catch (Exception $e) {
            error_log("❌ Error en togglePermisoPopup: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Obtener todos los permisos disponibles (para debugging)
     */
    public function getAllPermisos() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }
        
        try {
            $permisos = $this->permisoModel->obtenerTodosPermisos();
            
            echo json_encode([
                'success' => true,
                'permisos' => $permisos,
                'total' => count($permisos)
            ]);
        } catch (Exception $e) {
            error_log("Error en getAllPermisos: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error del servidor'
            ]);
        }
        exit;
    }
}
?>