<?php
// vista/configuraciones/formularios.php

require_once __DIR__ . '/../../config/security.php';

$titulo = 'Gestión de Formularios - CHEFCONTROL';
$paginaActual = 'configuraciones/formularios';

$baseUrl = Config::getBaseUrl();
$basePath = Config::getBasePath();

$cssExtra = '<link rel="stylesheet" href="' . $baseUrl . '/assets/css/configuraciones.css">';
$jsExtra = '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
$jsExtra .= '<script src="' . $baseUrl . '/assets/js/formularios.js"></script>';

require_once __DIR__ . '/../complementos/header.php';
?>


<div class="mosaic-container">
  <div class="mosaic-title">
    <h1><i class="fas fa-wpforms"></i> Gestión de Formularios</h1>
    <p class="subtitle">Administra todos los formularios del sistema</p>
  </div>
  
  <div class="mosaic-grid">
    <!-- Fila 1 -->

    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #4a6ee0;">
        <i class="fas fa-file-alt"></i>
      </div>
      <h3>FOR - DE - 144</h3>
      <p>Formulario principal de declaración de existencias</p>
    </div>

    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #95a5a6;">
        <i class="fas fa-clock"></i>
      </div>
      <h3>Espacio Reservado</h3>
      <p>Espacio reservado para futuros formularios</p>
    </div>
    
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #95a5a6;">
        <i class="fas fa-clock"></i>
      </div>
      <h3>Espacio Reservado</h3>
      <p>Espacio reservado para futuros formularios</p>
    </div>
    
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #95a5a6;">
        <i class="fas fa-clock"></i>
      </div>
      <h3>Espacio Reservado</h3>
      <p>Espacio reservado para futuros formularios</p>
    </div>
    
    <!-- Fila 2 -->
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #95a5a6;">
        <i class="fas fa-clock"></i>
      </div>
      <h3>Espacio Reservado</h3>
      <p>Espacio reservado para futuros formularios</p>
    </div>
    
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #95a5a6;">
        <i class="fas fa-clock"></i>
      </div>
      <h3>Espacio Reservado</h3>
      <p>Espacio reservado para futuros formularios</p>
    </div>
    
    
    <!-- Fila 3 -->
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #95a5a6;">
        <i class="fas fa-clock"></i>
      </div>
      <h3>Espacio Reservado</h3>
      <p>Espacio reservado para futuros formularios</p>
    </div>
    
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #95a5a6;">
        <i class="fas fa-clock"></i>
      </div>
      <h3>Espacio Reservado</h3>
      <p>Espacio reservado para futuros formularios</p>
    </div>
    
    <div class="mosaic-item">
      <div class="item-icon" style="background-color: #95a5a6;">
        <i class="fas fa-clock"></i>
      </div>
      <h3>Espacio Reservado</h3>
      <p>Espacio reservado para futuros formularios</p>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>