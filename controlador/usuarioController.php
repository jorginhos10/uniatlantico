<?php
// controlador/usuarioController.php

require_once 'modelo/usuarioModel.php';

class UsuarioController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function index() {
        $usuarios = $this->usuarioModel->obtenerTodosUsuarios();
        $cargos   = $this->usuarioModel->obtenerCargos();
        $roles    = $this->usuarioModel->obtenerRoles();
        require_once 'vista/usuarios/index.php';
    }

    public function paginaRegistro() {
        require_once 'vista/registro/index.php';
    }

    public function registroPublico() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) $_POST = $input;

        $datos = [
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'nombre'   => trim($_POST['nombre'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'rol'      => 'pasante',
            'avatar'   => 'default.png',
            'activo'   => 0,
            'cargo_id' => null,
        ];

        $errores = [];
        if (empty($datos['username'])) $errores['username'] = ['El nombre de usuario es obligatorio'];
        if (empty($datos['password'])) {
            $errores['password'] = ['La contraseña es obligatoria'];
        } elseif (strlen($datos['password']) < 6) {
            $errores['password'] = ['La contraseña debe tener al menos 6 caracteres'];
        }
        if (empty($datos['nombre'])) $errores['nombre'] = ['El nombre completo es obligatorio'];
        if (empty($datos['email'])) {
            $errores['email'] = ['El correo es obligatorio'];
        } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = ['Correo inválido'];
        }
        if (isset($_POST['password_confirmation']) && $datos['password'] !== $_POST['password_confirmation']) {
            $errores['password_confirmation'] = ['Las contraseñas no coinciden'];
        }
        if (!empty($datos['username']) && $this->usuarioModel->existeUsername($datos['username'])) {
            $errores['username'] = ['El nombre de usuario ya está registrado'];
        }
        if (!empty($datos['email']) && $this->usuarioModel->existeEmail($datos['email'])) {
            $errores['email'] = ['El correo ya está registrado'];
        }

        if (!empty($errores)) {
            echo json_encode(['success' => false, 'message' => 'Errores de validación', 'errors' => $errores]);
            exit;
        }

        if ($this->usuarioModel->crearUsuario($datos)) {
            echo json_encode(['success' => true, 'message' => 'Cuenta creada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear la cuenta']);
        }
        exit;
    }

    public function crear() {
        // Siempre devolver JSON para AJAX
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        // Decodificar JSON si viene como application/json
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $_POST = $input;
        }
        
        // Obtener datos
        $datos = [
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'nombre'   => trim($_POST['nombre'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'rol'      => $_POST['rol'] ?? 'auxiliar',
            'avatar'   => 'default.png',
            'activo'   => isset($_POST['activo']) ? (int)$_POST['activo'] : 1,
            'cargo_id' => !empty($_POST['cargo_id']) ? (int)$_POST['cargo_id'] : null,
        ];

        // Validaciones
        $errores = [];
        
        if (empty($datos['username'])) {
            $errores['username'] = ['El nombre de usuario es obligatorio'];
        }
        
        if (empty($datos['password'])) {
            $errores['password'] = ['La contraseña es obligatoria'];
        } elseif (strlen($datos['password']) < 6) {
            $errores['password'] = ['La contraseña debe tener al menos 6 caracteres'];
        }
        
        if (empty($datos['nombre'])) {
            $errores['nombre'] = ['El nombre completo es obligatorio'];
        }
        
        if (empty($datos['email'])) {
            $errores['email'] = ['El email es obligatorio'];
        } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = ['Email inválido'];
        }
        
        if (isset($_POST['password_confirmation']) && $datos['password'] !== $_POST['password_confirmation']) {
            $errores['password_confirmation'] = ['Las contraseñas no coinciden'];
        }
        
        // Verificar si username ya existe
        if (!empty($datos['username']) && $this->usuarioModel->existeUsername($datos['username'])) {
            $errores['username'] = ['El nombre de usuario ya está registrado'];
        }
        
        // Verificar si email ya existe
        if (!empty($datos['email']) && $this->usuarioModel->existeEmail($datos['email'])) {
            $errores['email'] = ['El email ya está registrado'];
        }

        if (!empty($errores)) {
            echo json_encode([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $errores
            ]);
            exit;
        }

        // Crear usuario
        if ($this->usuarioModel->crearUsuario($datos)) {
            echo json_encode([
                'success' => true,
                'message' => 'Usuario creado exitosamente'
            ]);
            exit;
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear el usuario en la base de datos'
            ]);
            exit;
        }
    }

    // Método para obtener datos de usuario por AJAX
    public function get($id) {
        header('Content-Type: application/json');
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            exit;
        }
        
        $usuario = $this->usuarioModel->obtenerUsuarioPorId($id);
        
        if ($usuario) {
            echo json_encode([
                'success' => true,
                'data' => $usuario
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }
        exit;
    }

    // Método para actualizar estado por AJAX
    public function updateStatus() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        // Decodificar JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? 0;
        $activo = $input['activo'] ?? 0;
        
        // Validar ID
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario no válido']);
            exit;
        }
        
        // Validaciones de seguridad - NADIE puede modificar el superadmin (ID 1)
        if ($id == 1) {
            echo json_encode(['success' => false, 'message' => 'No se puede modificar el usuario administrador principal']);
            exit;
        }
        
        // El usuario no puede cambiar su propio estado
        if ($id == ($_SESSION['usuario_id'] ?? 0)) {
            echo json_encode(['success' => false, 'message' => 'No puedes cambiar tu propio estado']);
            exit;
        }
        
        // Actualizar estado
        if ($this->usuarioModel->actualizarEstado($id, $activo)) {
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
        }
        exit;
    }

    // Método para actualizar usuario (editar)
    public function update() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        // Decodificar JSON
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) {
            $input = $_POST;
        }
        
        $id = $input['id'] ?? 0;
        $datos = [
            'nombre'   => trim($input['nombre'] ?? ''),
            'email'    => trim($input['email'] ?? ''),
            'rol'      => $input['rol'] ?? 'cocina',
            'activo'   => isset($input['activo']) ? (int)$input['activo'] : 0,
            'cargo_id' => !empty($input['cargo_id']) ? (int)$input['cargo_id'] : null,
        ];

        // Validaciones
        $errores = [];
        
        if (empty($datos['nombre'])) {
            $errores['nombre'] = ['El nombre es obligatorio'];
        }
        
        if (empty($datos['email'])) {
            $errores['email'] = ['El email es obligatorio'];
        } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = ['Email inválido'];
        }

        if (!empty($errores)) {
            echo json_encode([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $errores
            ]);
            exit;
        }

        // Verificar si es el superadmin (ID 1) - NADIE puede modificarlo
        if ($id == 1) {
            echo json_encode([
                'success' => false,
                'message' => 'No se puede modificar el usuario administrador principal'
            ]);
            exit;
        }

        // Si el usuario está editándose a sí mismo, no puede cambiar su rol
        if ($id == ($_SESSION['usuario_id'] ?? 0)) {
            // Obtener el rol actual del usuario
            $usuarioActual = $this->usuarioModel->obtenerUsuarioPorId($id);
            if ($usuarioActual && $usuarioActual['rol'] != $datos['rol']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No puedes cambiar tu propio rol'
                ]);
                exit;
            }
        }

        // Actualizar usuario
        if ($this->usuarioModel->actualizarUsuario($id, $datos)) {
            echo json_encode([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el usuario'
            ]);
        }
        exit;
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            
            // Validaciones de seguridad - NADIE puede eliminar al superadmin (ID 1)
            if ($id == 1) {
                $_SESSION['error'] = 'No se puede eliminar al administrador principal';
                header("Location: " . Config::getBasePath() . "/usuarios");
                exit;
            }
            
            // El usuario no puede eliminarse a sí mismo
            if ($id == ($_SESSION['usuario_id'] ?? 0)) {
                $_SESSION['error'] = 'No puedes eliminar tu propio usuario';
                header("Location: " . Config::getBasePath() . "/usuarios");
                exit;
            }

            if ($this->usuarioModel->eliminarUsuario($id)) {
                $_SESSION['success'] = 'Usuario eliminado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar el usuario';
            }
            
            header("Location: " . Config::getBasePath() . "/usuarios");
            exit;
        }
        
        header("Location: " . Config::getBasePath() . "/usuarios");
        exit;
    }

    public function editar($id) {
        // Método tradicional para compatibilidad
        $usuario = $this->usuarioModel->obtenerUsuarioPorId($id);
        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado';
            header("Location: " . Config::getBasePath() . "/usuarios");
            exit;
        }
        require_once 'vista/usuarios/editar.php';
    }

    // Método para restablecer contraseña por AJAX
    public function resetPassword() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        // Decodificar JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? 0;
        $password = $input['password'] ?? '';
        
        // Validar ID
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario no válido']);
            exit;
        }
        
        // Validar contraseña
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
            exit;
        }
        
        // Validaciones de seguridad - NADIE puede cambiar contraseña del superadmin
        if ($id == 1) {
            echo json_encode(['success' => false, 'message' => 'No se puede cambiar la contraseña del administrador principal']);
            exit;
        }
        
        // El usuario no puede cambiar su propia contraseña desde aquí
        if ($id == ($_SESSION['usuario_id'] ?? 0)) {
            echo json_encode(['success' => false, 'message' => 'No puedes cambiar tu propia contraseña aquí']);
            exit;
        }
        
        // Actualizar contraseña usando el modelo
        if ($this->usuarioModel->actualizarContrasena($id, $password)) {
            echo json_encode(['success' => true, 'message' => 'Contraseña restablecida correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al restablecer la contraseña']);
        }
        exit;
    }

    // Método para obtener estadísticas en tiempo real
    public function getStats() {
        header('Content-Type: application/json');
        
        $stats = $this->usuarioModel->obtenerEstadisticas();
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        exit;
    }

    /**
     * Método NUEVO: Obtener estadísticas EN TIEMPO REAL de usuarios activos
     * Para actualización en vivo del contador
     */
    public function getRealTimeStats() {
        header('Content-Type: application/json');
        
        // Solo administradores pueden ver estadísticas
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
            exit;
        }
        
        // Obtener estadísticas básicas
        $stats = $this->usuarioModel->obtenerEstadisticas();
        
        // Calcular logins de hoy
        $hoy = date('Y-m-d');
        $usuarios = $this->usuarioModel->obtenerTodosUsuarios();
        $loginsHoy = 0;
        
        foreach ($usuarios as $usuario) {
            if ($usuario['ultimo_login'] && date('Y-m-d', strtotime($usuario['ultimo_login'])) == $hoy) {
                $loginsHoy++;
            }
        }
        
        // Calcular usuarios por rol específicos
        $cocinaCount = 0;
        $inventarioCount = 0;
        $meserosCount = 0;
        
        foreach ($usuarios as $usuario) {
            if ($usuario['activo'] == 1) {
                switch ($usuario['rol']) {
                    case 'cocina':
                        $cocinaCount++;
                        break;
                    case 'inventario':
                        $inventarioCount++;
                        break;
                    case 'mesero':
                        $meserosCount++;
                        break;
                }
            }
        }
        
        // Agregar datos adicionales
        $stats['logins_hoy'] = $loginsHoy;
        $stats['total'] = count($usuarios);
        $stats['cocina'] = $cocinaCount;
        $stats['inventario'] = $inventarioCount;
        $stats['meseros'] = $meserosCount;
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
}
?>