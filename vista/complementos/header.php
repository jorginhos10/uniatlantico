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

                    <!-- ── Campanita de notificaciones ── -->
                    <div class="ntf-wrap" id="ntfWrap">
                        <button class="ntf-btn" id="ntfBtn" title="Notificaciones">
                            <i class="fas fa-bell ntf-icon"></i>
                            <span class="ntf-badge" id="ntfBadge">0</span>
                        </button>
                        <div class="ntf-panel" id="ntfPanel">
                            <div class="ntf-panel-head">
                                <span class="ntf-panel-title"><i class="fas fa-bell"></i> Notificaciones</span>
                                <a href="<?php echo $basePath; ?>/mensajes" class="ntf-link-all">Ver todos</a>
                            </div>
                            <div class="ntf-panel-list" id="ntfList">
                                <div class="ntf-empty">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Sin mensajes nuevos</span>
                                </div>
                            </div>
                            <a href="<?php echo $basePath; ?>/mensajes" class="ntf-panel-footer">
                                Abrir bandeja de entrada &rarr;
                            </a>
                        </div>
                    </div>

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
                                <button class="dropdownItem" onclick="abrirAcercaDe()" type="button">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Acerca de nosotros</span>
                                </button>
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

            <!-- ════ MODAL ACERCA DE NOSOTROS ════ -->
            <div id="modalAcercaDe" style="
                display:none;position:fixed;inset:0;z-index:99999;
                background:rgba(0,0,0,0.45);backdrop-filter:blur(6px);
                align-items:center;justify-content:center;padding:20px">
                <div style="
                    background:#fff;border-radius:22px;width:100%;max-width:500px;
                    box-shadow:0 32px 80px rgba(0,0,0,0.22);
                    font-family:-apple-system,BlinkMacSystemFont,'Helvetica Neue',sans-serif;
                    overflow:hidden;position:relative">

                    <!-- Botón cerrar -->
                    <button onclick="cerrarAcercaDe()" style="
                        position:absolute;top:14px;right:14px;
                        width:28px;height:28px;border-radius:50%;border:none;
                        background:rgba(0,0,0,0.07);color:#6e6e73;cursor:pointer;
                        display:flex;align-items:center;justify-content:center;
                        font-size:13px;transition:background .15s">
                        <i class="fas fa-times"></i>
                    </button>

                    <!-- Cuerpo -->
                    <div style="padding:30px 32px 24px">
                        <!-- Ícono app -->
                        <div style="
                            width:58px;height:58px;border-radius:14px;
                            background:linear-gradient(135deg,#4facfe,#007AFF);
                            display:flex;align-items:center;justify-content:center;
                            margin-bottom:18px;box-shadow:0 4px 16px rgba(0,122,255,.35)">
                            <i class="fas fa-cloud" style="color:#fff;font-size:26px"></i>
                        </div>

                        <h2 style="font-size:20px;font-weight:700;color:#1d1d1f;margin:0 0 14px;letter-spacing:-0.3px">
                            Acerca de nosotros
                        </h2>

                        <p style="font-size:14.5px;line-height:1.7;color:#3a3a3c;margin:0 0 18px">
                            Este software fue diseñado y desarrollado por
                            <strong>Cloud Control Technology</strong>, por
                            <strong>Jorge Albeiro Valencia Bolívar</strong>, quien otorga
                            uso, adquisición y entrega total de propiedad intelectual a la
                            <strong>Universidad del Atlántico</strong> y todas sus asociaciones,
                            dependencias y organismos afiliados. La Universidad del
                            Atlántico es titular y propietaria de todos los derechos sobre
                            esta plataforma y sus desarrollos derivados.
                        </p>

                        <!-- Agradecimiento -->
                        <div style="
                            border-left:3px solid #007AFF;
                            background:#f0f6ff;
                            border-radius:0 10px 10px 0;
                            padding:14px 16px;
                            margin-bottom:4px">
                            <p style="font-size:13.5px;line-height:1.7;color:#3a3a3c;margin:0">
                                Agradecimiento especial a <strong>Gabriel Romero</strong>,
                                <strong>Shirlene Buelvas</strong> y <strong>Graciela Angulo</strong>
                                por ser fuente de apoyo e inspiración para las funciones y experiencia
                                de usuario en los desarrollos que se han venido realizando en la
                                Oficina de Planeación de la Universidad del Atlántico sede Norte.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="
                        display:flex;align-items:center;gap:10px;
                        padding:13px 32px;
                        border-top:1px solid rgba(0,0,0,0.07);
                        background:#fafafa">
                        <div style="
                            width:30px;height:30px;border-radius:8px;
                            background:#1d1d1f;display:flex;align-items:center;
                            justify-content:center;flex-shrink:0">
                            <i class="fas fa-university" style="color:#fff;font-size:13px"></i>
                        </div>
                        <span style="font-size:12px;color:#86868b">
                            &copy; 2026 Cloud Control Technology &middot; Universidad del Atlántico
                        </span>
                    </div>
                </div>
            </div>

            <script>
            function abrirAcercaDe() {
                var m = document.getElementById('modalAcercaDe');
                m.style.display = 'flex';
                document.querySelector('.dropdownMenu')?.classList.remove('show');
                document.addEventListener('keydown', _acercaEsc);
            }
            function cerrarAcercaDe() {
                document.getElementById('modalAcercaDe').style.display = 'none';
                document.removeEventListener('keydown', _acercaEsc);
            }
            function _acercaEsc(e) { if (e.key === 'Escape') cerrarAcercaDe(); }
            document.getElementById('modalAcercaDe').addEventListener('click', function(e) {
                if (e.target === this) cerrarAcercaDe();
            });
            </script>
            <!-- ════════════════════════════════════ -->

            <!-- Contenido principal de la página -->
            <div class="contentWrapper">