<?php
// vista/complementos/header.php

// Incluir config para usar la clase Config
require_once __DIR__ . '/../../config/config.php';

$basePath = Config::getBasePath();
$baseUrl = Config::getBaseUrl();
$usuarioLogueado = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Obtener avatar del usuario o usar default
$avatar = $usuarioLogueado ? ($_SESSION['usuario_avatar'] ?? 'default.png') : 'default.png';
$avatarUrl = $baseUrl . '/assets/media/users/' . $avatar;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'CHEFCONTROL'; ?></title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --apple-blue: #0071e3;
            --apple-green: #34c759;
            --apple-red: #ff3b30;
            --apple-orange: #ff9f0a;
            --apple-purple: #af52de;
            --apple-text: #1d1d1f;
            --apple-text-secondary: #6e6e73;
            --apple-text-tertiary: #86868b;
            --apple-bg: #f5f5f7;
            --apple-card: #ffffff;
            --apple-border: rgba(0,0,0,0.06);
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
    <?php echo $cssExtra ?? ''; ?>
</head>
<body>
    <div class="dashboardContainer">
        <!-- Incluir Sidebar -->
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="mainContent">
            <!-- Nuevo Header -->
            <header class="mainHeader">
                <!-- Logo a la izquierda -->
                <div class="headerLeft">
                    <div class="logoHeader">
                        <i class="fas fa-university"></i>
                        <span>UNIVERSIDAD DEL ATLANTICO</span>
                    </div>
                </div>

                <!-- Información de usuario a la derecha -->
                <div class="headerRight">
                    <?php if ($usuarioLogueado): ?>
                        <div class="userDropdown">
                            <button class="userDropdownBtn">
                                <div class="userAvatar">
                                    <img src="<?php echo $avatarUrl; ?>" 
                                         alt="<?php echo $_SESSION['usuario_nombre']; ?>"
                                         onerror="this.src='<?php echo $baseUrl; ?>/assets/media/users/default.png'">
                                </div>
                                <div class="userInfo">
                                    <span class="userName"><?php echo $_SESSION['usuario_nombre']; ?></span>
                                    <span class="userRole"><?php echo ucfirst($_SESSION['usuario_rol']); ?></span>
                                </div>
                                <i class="fas fa-chevron-down dropdownArrow"></i>
                            </button>
                            
                            <div class="dropdownMenu">
                                <a href="<?php echo $basePath; ?>/perfil" class="dropdownItem">
                                    <i class="fas fa-user"></i>
                                    <span>Mi Perfil</span>
                                </a>
                                <a href="<?php echo $basePath; ?>/configuracion" class="dropdownItem">
                                    <i class="fas fa-cog"></i>
                                    <span>Configuración</span>
                                </a>
                                <div class="dropdownDivider"></div>
                                <a href="<?php echo $basePath; ?>/logout" class="dropdownItem">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Cerrar Sesión</span>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="guestActions">
                            <a href="<?php echo $basePath; ?>/login" class="loginBtn">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Iniciar Sesión</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Contenido principal de la página -->
            <div class="contentWrapper">