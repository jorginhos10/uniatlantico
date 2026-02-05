<?php
// vista/login/login.php

require_once __DIR__ . '/../../config/config.php';

$basePath = Config::getBasePath();
$baseUrl = Config::getBaseUrl();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CHEFCONTROL</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- ⭐ Capa de fondo borroso -->
    <div class="background-blur"></div>

    <div class="loginContainer">
        <div class="containerDivIdentidad">
            <img src="assets/media/src/logo.png">
        </div>
        
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alertError">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']); 
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alertSuccess">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']); 
                ?>
            </div>
        <?php endif; ?>

        <!-- ✅ Agregar ID al formulario -->
        <form id="loginForm" method="POST" action="<?php echo $basePath; ?>/login">
            <div class="formGroup">
                <input type="text" id="username" name="username" required 
                       placeholder="Usuario" value="<?php echo $_POST['username'] ?? ''; ?>">
            </div>
            
            <div class="formGroup">
                <input type="password" id="password" name="password" required 
                       placeholder="Contraseña">
            </div>
            
            <div class="checkboxGroup">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Recordarme</label>
            </div>
                        
            <hr class="divider">
            
            <button type="submit" class="loginButton">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="rememberInfo">
            <i class="fas fa-info-circle"></i>
            <span>Al marcar "Recordarme", tu sesión se mantendrá por 30 días</span>
        </div>
        
        <div class="sessionInfo">
            <i class="fas fa-clock"></i>
            <span>La sesión expira después de 30 minutos de inactividad</span>
        </div>
    </div>

    <!-- ✅ Incluir scripts -->
    <script src="<?php echo $baseUrl; ?>/assets/js/rememberMe.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/login.js"></script>
</body>
</html>