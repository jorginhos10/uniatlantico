<?php
// vista/dashboard/index.php

// 🔐 INCLUIR SEGURIDAD - Redirige si no hay sesión
require_once __DIR__ . '/../../config/security.php';

// Configurar variables para el header
$titulo = 'Dashboard - CHEFCONTROL';
$tituloHeader = 'Bienvenido, ' . $_SESSION['usuario_nombre'] . '!';
$subtituloHeader = 'Panel de control principal';
$paginaActual = 'dashboard';

// Incluir header
require_once __DIR__ . '/../complementos/header.php';
?>

<!-- Contenido específico del dashboard -->
<div class="statsGrid">
    <div class="statCard">
        <div class="statIcon" style="color: #3498db;">
            <i class="fas fa-utensils"></i>
        </div>
        <div class="statValue">45</div>
        <div class="statLabel">Recetas Activas</div>
    </div>
    <div class="statCard">
        <div class="statIcon" style="color: #2ecc71;">
            <i class="fas fa-shopping-basket"></i>
        </div>
        <div class="statValue">128</div>
        <div class="statLabel">Ingredientes</div>
    </div>
    <div class="statCard">
        <div class="statIcon" style="color: #e74c3c;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="statValue">8</div>
        <div class="statLabel">Stock Bajo</div>
    </div>
</div>

<div class="recentActivity">
    <h2 class="sectionTitle">Actividad Reciente</h2>
    <ul class="activityList">
        <li class="activityItem">
            <strong>Nueva receta agregada</strong>
            <p>Pasta Carbonara ha sido agregada al sistema</p>
            <div class="activityTime">Hace 15 minutos</div>
        </li>
        <li class="activityItem">
            <strong>Stock actualizado</strong>
            <p>Inventario de tomates ha sido modificado</p>
            <div class="activityTime">Hace 1 hora</div>
        </li>
    </ul>
</div>

<?php
// Incluir footer
require_once __DIR__ . '/../complementos/footer.php';
?>