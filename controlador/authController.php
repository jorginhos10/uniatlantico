<?php
// controlador/authController.php

require_once 'modelo/usuarioModel.php';

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            if (empty($username) || empty($password)) {
                $_SESSION['error'] = 'Por favor, complete todos los campos';
                header("Location: " . Config::getBasePath() . "/login");
                exit;
            }

            $usuario = $this->usuarioModel->verificarUsuario($username, $password);

            if ($usuario) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_username'] = $usuario['username'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                $_SESSION['usuario_cargo_id'] = $usuario['cargo_id'];
                $_SESSION['usuario_avatar'] = $usuario['avatar'];
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                }

                $redirect_url = $_SESSION['redirect_url'] ?? Config::getBasePath() . '/dashboard';
                unset($_SESSION['redirect_url']);
                
                header("Location: " . $redirect_url);
                exit;
            } else {
                $_SESSION['error'] = 'Usuario o contraseña incorrectos';
                header("Location: " . Config::getBasePath() . "/login");
                exit;
            }
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
        
        header("Location: " . Config::getBasePath() . "/login");
        exit;
    }
}
?>