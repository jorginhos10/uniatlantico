<?php
// controlador/perfilController.php

require_once 'modelo/usuarioModel.php';

class PerfilController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function index() {
        $id      = $_SESSION['usuario_id'] ?? 0;
        $usuario = $this->usuarioModel->obtenerPerfilCompleto($id);

        if (!$usuario) {
            header('Location: ' . Config::getBasePath() . '/dashboard');
            exit;
        }

        $titulo       = 'Mi Perfil';
        $paginaActual = 'perfil';
        $basePath     = Config::getBasePath();
        $baseUrl      = Config::getBaseUrl();

        require_once 'vista/perfil/index.php';
    }

    // Mientras el superadministrador ve la cuenta de otro usuario no puede cambiarle la foto, los datos ni la contraseña
    private function bloqueadoPorImpersonacion() {
        if (!empty($_SESSION['impersonador'])) {
            echo json_encode(['success' => false, 'message' => 'No disponible mientras ves la cuenta de otro usuario']);
            return true;
        }
        return false;
    }

    public function updateAvatar() {
        header('Content-Type: application/json');

        $id = $_SESSION['usuario_id'] ?? 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        if ($this->bloqueadoPorImpersonacion()) return;

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ninguna imagen']);
            return;
        }

        $file    = $_FILES['avatar'];
        $maxSize = 2 * 1024 * 1024;

        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'La imagen no puede superar 2 MB']);
            return;
        }

        // El tipo y la extensión salen del CONTENIDO del archivo, no de lo que declara el navegador: un .php con
        // «Content-Type: image/png» ya no pasa
        $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!isset($tipos[$mime]) || @getimagesize($file['tmp_name']) === false) {
            echo json_encode(['success' => false, 'message' => 'Formato no permitido. Use JPG, PNG, GIF o WebP']);
            return;
        }

        $ext       = $tipos[$mime];
        $filename  = 'avatar_' . (int)$id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $uploadDir = 'assets/media/users/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen']);
            return;
        }

        if ($this->usuarioModel->actualizarAvatar($id, $filename)) {
            $_SESSION['usuario_avatar'] = $filename;
            echo json_encode([
                'success'    => true,
                'message'    => 'Foto actualizada correctamente',
                'avatar_url' => Config::getBaseUrl() . '/assets/media/users/' . $filename
            ]);
        } else {
            @unlink($uploadDir . $filename);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos']);
        }
    }

    // Cambiar nombre y correo del propio usuario
    public function updateDatos() {
        header('Content-Type: application/json');

        $id = $_SESSION['usuario_id'] ?? 0;
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        if ($this->bloqueadoPorImpersonacion()) return;

        $input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $nombre = trim($input['nombre'] ?? '');
        $email  = trim($input['email'] ?? '');

        if ($nombre === '' || $email === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre y el correo son obligatorios']);
            return;
        }

        if (mb_strlen($nombre, 'UTF-8') > 150 || mb_strlen($email, 'UTF-8') > 150) {
            echo json_encode(['success' => false, 'message' => 'El nombre y el correo no pueden superar 150 caracteres']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido']);
            return;
        }

        // El correo también sirve para iniciar sesión, así que no puede repetirse
        if ($this->usuarioModel->emailUsadoPorOtro($email, $id)) {
            echo json_encode(['success' => false, 'message' => 'Ese correo ya está registrado por otro usuario']);
            return;
        }

        if ($this->usuarioModel->actualizarDatosPerfil($id, $nombre, $email)) {
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_email']  = $email;
            echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar los datos']);
        }
    }

    public function updatePassword() {
        header('Content-Type: application/json');

        $id = $_SESSION['usuario_id'] ?? 0;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        if ($this->bloqueadoPorImpersonacion()) return;

        $input     = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $actual   = $input['password_actual']    ?? '';
        $nueva     = $input['password_nueva']     ?? '';
        $confirmar = $input['password_confirmar'] ?? '';

        if (empty($actual) || empty($nueva) || empty($confirmar)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
            return;
        }

        if (strlen($nueva) < 8) {
            echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres']);
            return;
        }

        if ($nueva !== $confirmar) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden']);
            return;
        }

        if (!$this->usuarioModel->verificarContrasena($id, $actual)) {
            echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
            return;
        }

        if ($this->usuarioModel->actualizarContrasena($id, $nueva)) {
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
        }
    }
}
?>
