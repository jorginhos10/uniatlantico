<?php
// vista/configuraciones/proveedores.php

require_once __DIR__ . '/../../config/security.php';

$titulo = 'Gestión de Proveedores - CHEFCONTROL';
$paginaActual = 'configuraciones/proveedores';

$baseUrl = Config::getBaseUrl();
$basePath = Config::getBasePath();

$cssExtra = '<link rel="stylesheet" href="' . $baseUrl . '/assets/css/configuraciones.css">';
$jsExtra = '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
$jsExtra .= '<script src="' . $baseUrl . '/assets/js/proveedores.js"></script>';

require_once __DIR__ . '/../complementos/header.php';
?>


<div class="mosaic-container">
  <div class="mosaic-title">
    <h1><i class="fas fa-cogs"></i> Configuraciones</h1>
    <p class="subtitle">Gestiona todas las opciones de tu aplicación desde este panel</p>
  </div>
  
  <div class="mosaic-grid">
    <!-- Fila 1 -->

    <a style="text-decoration:none;" href="<?php echo $basePath; ?>/config144">
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #4a6ee0;">
        <i class="fas fa-wpforms"></i>
      </div>
      <h3>Formularios</h3>
      <p>Configura campos, validaciones y plantillas de formularios</p>
    </div>
    </a>
    
    <a style="text-decoration:none;" href="<?php echo $basePath; ?>/novedades">
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #FF9500;">
        <i class="fas fa-bullhorn"></i>
      </div>
      <h3>Novedades</h3>
      <p>Publica y gestiona novedades que aparecen en el dashboard</p>
    </div>
    </a>
    
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #ea4335;">
        <i class="fas fa-palette"></i>
      </div>
      <h3>Apariencia</h3>
      <p>Personaliza temas, colores y disposición de elementos</p>
    </div>
    
    <!-- Fila 2 -->
    <a style="text-decoration:none;" href="<?php echo $basePath; ?>/roles">
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #fbbc05;">
        <i class="fas fa-user-tie"></i>
      </div>
      <h3>Dependencias</h3>
      <p>Gestiona la creación, edición y eliminación de dependencias</p>
    </div>
    </a>
    
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #8e44ad;">
        <i class="fas fa-database"></i>
      </div>
      <h3>Almacenamiento</h3>
      <p>Gestiona espacio, copias de seguridad y sincronización</p>
    </div>
    
    
    <!-- Fila 3 -->
    <a style="text-decoration:none;" href="<?php echo $basePath; ?>/roles">
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #8e44ad;">
        <i class="fas fa-shield-alt"></i>
      </div>
      <h3>Roles</h3>
      <p>Define los roles disponibles para los usuarios del sistema</p>
    </div>
    </a>

    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #e74c3c;">
        <i class="fas fa-file-export"></i>
      </div>
      <h3>Exportación</h3>
      <p>Configura formatos, rutas y opciones de exportación</p>
    </div>
    
    <a style="text-decoration:none;" href="<?php echo $basePath; ?>/usuarios">
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #3498db;">
        <i class="fas fa-users-cog"></i>
      </div>
      <h3>Usuarios</h3>
      <p>Administra perfiles, roles y permisos de usuarios</p>
    </div>
    </a>
    
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #2ecc71;">
        <i class="fas fa-sliders-h"></i>
      </div>
      <h3>Avanzado</h3>
      <p>Configuraciones técnicas y opciones para desarrolladores</p>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>