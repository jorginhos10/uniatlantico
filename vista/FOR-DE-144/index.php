<?php
// vista/FOR-DE-144/index.php - VERSIÓN CORREGIDA

// 🔐 INCLUIR SEGURIDAD - Redirige si no hay sesión
require_once __DIR__ . '/../../config/security.php';

// Configurar variables para el header
$titulo = 'FOR-DE-144 - CHEFCONTROL';
$tituloHeader = 'Gestión de Formularios';
$subtituloHeader = 'Administra los formularios FOR-DE-144';
$paginaActual = 'FOR-DE-144';

// Incluir header
require_once __DIR__ . '/../complementos/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOR-DE-144 - Gestión de Formularios</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --color-primary: #2C3E50;
            --color-primary-light: #34495E;
            --color-primary-dark: #1A252F;
            --color-accent: #3498DB;
            --color-text: #333333;
            --color-text-light: #7F8C8D;
            --color-bg: #F8F9FA;
            --color-white: #FFFFFF;
            --color-success: #27AE60;
            --color-warning: #F39C12;
            --color-danger: #E74C3C;
            --color-info: #3498DB;
        }
        
        body {
            background-color: var(--color-bg);
            color: var(--color-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            padding: 25px 0;
        }
        
        .formulario-card {
            background: var(--color-white);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(44, 62, 80, 0.1);
            padding: 25px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            position: relative;
            border-left: 4px solid var(--color-primary);
        }
        
        .formulario-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(44, 62, 80, 0.15);
        }
        
        .formulario-card.disponible {
            border-left-color: var(--color-success);
        }
        
        .formulario-card.no-disponible {
            border-left-color: var(--color-danger);
            opacity: 0.8;
            background: #f8f9fa;
        }
        
        .formulario-card.proximamente {
            border-left-color: var(--color-warning);
        }
        
        .formulario-add {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: var(--color-white);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 2px dashed rgba(255, 255, 255, 0.3);
            min-height: 350px;
        }
        
        .formulario-add:hover {
            background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-primary) 100%);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .add-icon {
            font-size: 70px;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        .formulario-titulo {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--color-primary);
            border-bottom: 2px solid #F0F3F4;
            padding-bottom: 8px;
            padding-right: 80px;
        }
        
        .formulario-descripcion {
            color: var(--color-text-light);
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .tiempo-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-size: 0.9rem;
            border: 1px solid #e9ecef;
        }
        
        .tiempo-info i {
            width: 20px;
            color: var(--color-primary);
            margin-right: 8px;
        }
        
        .tiempo-info .badge-estado {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .badge-disponible {
            background: var(--color-success);
            color: white;
        }
        
        .badge-no-disponible {
            background: var(--color-danger);
            color: white;
        }
        
        .badge-proximamente {
            background: var(--color-warning);
            color: white;
        }
        
        .fecha-rango {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 8px;
        }
        
        .fecha-item {
            display: flex;
            align-items: center;
            color: var(--color-text);
        }
        
        .formulario-fecha {
            font-size: 0.8rem;
            color: var(--color-text-light);
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ECF0F1;
        }
        
        .btn-actions {
            margin-top: 15px;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        
        .badge {
            padding: 6px 12px;
            font-weight: 500;
            border-radius: 20px;
        }
        
        .badge.bg-success {
            background-color: var(--color-success) !important;
        }
        
        .badge.bg-warning {
            background-color: var(--color-warning) !important;
        }
        
        .badge.bg-danger {
            background-color: var(--color-danger) !important;
        }
        
        .badge.bg-info {
            background-color: var(--color-info) !important;
        }
        
        .header-section {
            background: var(--color-white);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(44, 62, 80, 0.1);
            border-left: 5px solid var(--color-primary);
        }
        
        .header-title {
            color: var(--color-primary);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header-subtitle {
            color: var(--color-text-light);
            font-size: 1.1rem;
        }
        
        .header-icon {
            font-size: 2.5rem;
            color: var(--color-primary);
            margin-bottom: 15px;
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(44, 62, 80, 0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: var(--color-white);
            border-radius: 12px 12px 0 0;
            padding: 20px 25px;
        }
        
        .modal-title {
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .btn-close-white {
            filter: invert(1) brightness(2);
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #e9ecef;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.25rem rgba(44, 62, 80, 0.25);
        }
        
        .tiempo-opciones {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .rango-fechas {
            margin-top: 15px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-primary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
        }
        
        .btn-secondary {
            background: #95A5A6;
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
        }
        
        .btn-warning {
            background: var(--color-warning);
            border: none;
            color: white;
        }
        
        .btn-danger {
            background: var(--color-danger);
            border: none;
        }
        
        .btn-success {
            background: var(--color-success);
            border: none;
        }
        
        .alert-info {
            background-color: #D6EAF8;
            border-color: #AED6F1;
            color: var(--color-primary);
        }
        
        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .formulario-card {
            animation: fadeIn 0.5s ease-out;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .card-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .header-section {
                padding: 20px;
            }
            
            .formulario-titulo {
                padding-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Encabezado -->
        <div class="header-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h1 class="header-title">FOR-DE-144 - Gestión de Formularios</h1>
                    <p class="header-subtitle">Crea, edita y gestiona tus formularios con control de tiempo</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-muted">
                        <small><i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y H:i'); ?></small>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="formulariosContainer" class="card-grid">
            <!-- Card para agregar nuevo formulario -->
            <div class="formulario-card formulario-add" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                <div>
                    <i class="fas fa-plus-circle add-icon"></i>
                    <h3 class="mt-3">Nuevo Formulario</h3>
                    <p class="mb-0">Haz clic para crear un nuevo formulario</p>
                </div>
            </div>
            
            <!-- Los formularios existentes se cargarán aquí -->
            <?php 
            if (isset($formularios) && is_array($formularios) && count($formularios) > 0): 
                foreach ($formularios as $formulario): 
                    $fecha = new DateTime($formulario['fecha_creacion']);
                    $fechaFormateada = $fecha->format('d/m/Y H:i');
                    
                    // Determinar estado de disponibilidad
                    $ahora = new DateTime();
                    $disponible = true;
                    $estadoTiempo = 'disponible';
                    $mensajeEstado = '';
                    
                    if ($formulario['tipo_tiempo'] == 'rango') {
                        $inicio = new DateTime($formulario['fecha_inicio']);
                        $fin = new DateTime($formulario['fecha_fin']);
                        
                        if ($ahora < $inicio) {
                            $disponible = false;
                            $estadoTiempo = 'proximamente';
                            $mensajeEstado = 'Disponible a partir del ' . $inicio->format('d/m/Y H:i');
                        } elseif ($ahora > $fin) {
                            $disponible = false;
                            $estadoTiempo = 'finalizado';
                            $mensajeEstado = 'Finalizado el ' . $fin->format('d/m/Y H:i');
                        }
                    }
                    
                    $cardClass = $disponible ? 'disponible' : ($estadoTiempo == 'proximamente' ? 'proximamente' : 'no-disponible');
            ?>
     
            <div class="formulario-card <?php echo $cardClass; ?>" id="formulario-<?php echo $formulario['id']; ?>">
                <div class="formulario-titulo">
                    <?php echo htmlspecialchars($formulario['titulo']); ?>
                    <?php if ($formulario['estado'] != 1): ?>
                        <span class="badge bg-secondary float-end">Inactivo</span>
                    <?php endif; ?>
                </div>
                
                <div class="formulario-descripcion">
                    <?php echo htmlspecialchars($formulario['descripcion'] ?: 'Sin descripción'); ?>
                </div>
                
                <div class="tiempo-info">
                    <?php if ($formulario['tipo_tiempo'] == 'libre'): ?>
                        <span class="badge-estado badge-disponible">
                            <i class="fas fa-infinity"></i> Tiempo libre
                        </span>
                        <div class="fecha-item">
                            <i class="fas fa-check-circle text-success"></i>
                            Siempre disponible
                        </div>
                    <?php else: ?>
                        <?php if ($estadoTiempo == 'disponible'): ?>
                            <span class="badge-estado badge-disponible">
                                <i class="fas fa-clock"></i> Disponible ahora
                            </span>
                        <?php elseif ($estadoTiempo == 'proximamente'): ?>
                            <span class="badge-estado badge-proximamente">
                                <i class="fas fa-hourglass-half"></i> Próximamente
                            </span>
                        <?php else: ?>
                            <span class="badge-estado badge-no-disponible">
                                <i class="fas fa-ban"></i> Finalizado
                            </span>
                        <?php endif; ?>
                        
                        <div class="fecha-rango">
                            <div class="fecha-item">
                                <i class="fas fa-play-circle"></i>
                                Inicio: <?php echo date('d/m/Y H:i', strtotime($formulario['fecha_inicio'])); ?>
                            </div>
                            <div class="fecha-item">
                                <i class="fas fa-stop-circle"></i>
                                Fin: <?php echo date('d/m/Y H:i', strtotime($formulario['fecha_fin'])); ?>
                            </div>
                        </div>
                        
                        <?php if ($mensajeEstado): ?>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i> <?php echo $mensajeEstado; ?>
                            </small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="formulario-fecha">
                    <i class="far fa-clock me-1"></i>Creado: <?php echo $fechaFormateada; ?>
                </div>
                
                <div class="btn-actions">
                    <button class="btn btn-sm btn-success" onclick="window.location.href='modulo144/?id=<?php echo $formulario['id']; ?>'" 
                            title="Ver formulario" <?php echo !$disponible ? 'disabled' : ''; ?>>
                        <i class="fas fa-eye me-1"></i>Ver
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editarFormulario(<?php echo $formulario['id']; ?>)" 
                            title="Editar formulario">
                        <i class="fas fa-edit me-1"></i>Editar
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarFormulario(<?php echo $formulario['id']; ?>)" 
                            title="Eliminar formulario">
                        <i class="fas fa-trash me-1"></i>Eliminar
                    </button>
                </div>
            </div>
   
            <?php 
                endforeach; 
            else: 
            ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h4>No hay formularios registrados</h4>
                    <p class="mb-0">Comienza creando tu primer formulario haciendo clic en el card de arriba</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal para agregar formulario -->
    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarLabel">
                        <i class="fas fa-plus-circle me-2"></i>Nuevo Formulario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAgregarFormulario">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del Formulario *</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required 
                                   placeholder="Ingresa un título descriptivo para el formulario">
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="3" placeholder="Describe el propósito o contenido del formulario (opcional)"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Configuración de Tiempo *</label>
                            <div class="tiempo-opciones">
                                <div class="mb-2">
                                    <input type="radio" name="tipo_tiempo" id="tipo_libre" value="libre" checked>
                                    <label for="tipo_libre" class="ms-2">
                                        <i class="fas fa-infinity text-success me-2"></i>
                                        <strong>Tiempo Libre</strong> - El formulario estará siempre disponible
                                    </label>
                                </div>
                                
                                <div>
                                    <input type="radio" name="tipo_tiempo" id="tipo_rango" value="rango">
                                    <label for="tipo_rango" class="ms-2">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                                        <strong>Rango de Tiempo</strong> - Definir fecha y hora de inicio y fin
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="rangoFechasContainer" style="display: none;" class="rango-fechas">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha y Hora de Inicio *</label>
                                    <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio">
                                    <small class="text-muted">¿Cuándo comenzará a estar disponible?</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_fin" class="form-label">Fecha y Hora de Fin *</label>
                                    <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin">
                                    <small class="text-muted">¿Hasta cuándo estará disponible?</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Los formularios con rango de tiempo solo serán accesibles dentro del período configurado.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar Formulario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal para editar formulario -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">
                        <i class="fas fa-edit me-2"></i>Editar Formulario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarFormulario">
                    <input type="hidden" id="formularioIdEditar" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tituloEditar" class="form-label">Título del Formulario *</label>
                            <input type="text" class="form-control" id="tituloEditar" name="titulo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcionEditar" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcionEditar" name="descripcion" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Configuración de Tiempo *</label>
                            <div class="tiempo-opciones">
                                <div class="mb-2">
                                    <input type="radio" name="tipo_tiempo" id="tipo_libre_editar" value="libre">
                                    <label for="tipo_libre_editar" class="ms-2">
                                        <i class="fas fa-infinity text-success me-2"></i>
                                        <strong>Tiempo Libre</strong> - El formulario estará siempre disponible
                                    </label>
                                </div>
                                
                                <div>
                                    <input type="radio" name="tipo_tiempo" id="tipo_rango_editar" value="rango">
                                    <label for="tipo_rango_editar" class="ms-2">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                                        <strong>Rango de Tiempo</strong> - Definir fecha y hora de inicio y fin
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="rangoFechasContainerEditar" style="display: none;" class="rango-fechas">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_inicio_editar" class="form-label">Fecha y Hora de Inicio *</label>
                                    <input type="datetime-local" class="form-control" id="fecha_inicio_editar" name="fecha_inicio">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_fin_editar" class="form-label">Fecha y Hora de Fin *</label>
                                    <input type="datetime-local" class="form-control" id="fecha_fin_editar" name="fecha_fin">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="estadoEditar" class="form-label">Estado del Formulario</label>
                            <select class="form-control" id="estadoEditar" name="estado">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Actualizar Formulario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Configuración base
        const basePath = '<?php echo Config::getBasePath(); ?>';
        
        // Función para mostrar/ocultar rango de fechas en modal agregar
        function mostrarRangoFechas(mostrar) {
            const container = document.getElementById('rangoFechasContainer');
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            
            if (mostrar) {
                container.style.display = 'block';
                fechaInicio.required = true;
                fechaFin.required = true;
            } else {
                container.style.display = 'none';
                fechaInicio.required = false;
                fechaFin.required = false;
            }
        }
        
        // Función para mostrar/ocultar rango de fechas en modal editar
        function mostrarRangoFechasEditar(mostrar) {
            const container = document.getElementById('rangoFechasContainerEditar');
            const fechaInicio = document.getElementById('fecha_inicio_editar');
            const fechaFin = document.getElementById('fecha_fin_editar');
            
            if (mostrar) {
                container.style.display = 'block';
                fechaInicio.required = true;
                fechaFin.required = true;
            } else {
                container.style.display = 'none';
                fechaInicio.required = false;
                fechaFin.required = false;
            }
        }
        
        $(document).ready(function() {
            // Evento para cambiar la visualización según el tipo de tiempo seleccionado
            $('input[name="tipo_tiempo"]').on('change', function() {
                if ($(this).val() === 'rango') {
                    mostrarRangoFechas(true);
                } else {
                    mostrarRangoFechas(false);
                }
            });
            
            $('input[name="tipo_tiempo_editar"]').on('change', function() {
                if ($(this).val() === 'rango') {
                    mostrarRangoFechasEditar(true);
                } else {
                    mostrarRangoFechasEditar(false);
                }
            });
            
            // Manejar envío del formulario de agregar
            $('#formAgregarFormulario').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                
                submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');
                submitBtn.prop('disabled', true);
                
                $.ajax({
                    url: basePath + '/FOR-DE-144?action=crearFormulario',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                        
                        if (response.success) {
                            $('#modalAgregar').modal('hide');
                            $('#formAgregarFormulario')[0].reset();
                            mostrarRangoFechas(false);
                            
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al conectar con el servidor'
                        });
                    }
                });
            });
            
            // Manejar envío del formulario de editar
            $('#formEditarFormulario').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                
                submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...');
                submitBtn.prop('disabled', true);
                
                $.ajax({
                    url: basePath + '/FOR-DE-144?action=editar',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                        
                        if (response.success) {
                            $('#modalEditar').modal('hide');
                            
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al conectar con el servidor'
                        });
                    }
                });
            });
        });
        
        // Función para eliminar formulario
        function eliminarFormulario(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el formulario. No podrás revertir esto.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2C3E50',
                cancelButtonColor: '#95A5A6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/FOR-DE-144?action=eliminar',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error al conectar con el servidor'
                            });
                        }
                    });
                }
            });
        }
        
        // Función para editar formulario
        function editarFormulario(id) {
            // Mostrar loading
            Swal.fire({
                title: 'Cargando...',
                text: 'Obteniendo información del formulario',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: basePath + '/FOR-DE-144?action=getFormulario&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    
                    if (response.success && response.formulario) {
                        const f = response.formulario;
                        
                        // Llenar el formulario de edición
                        $('#formularioIdEditar').val(f.id);
                        $('#tituloEditar').val(f.titulo);
                        $('#descripcionEditar').val(f.descripcion);
                        $('#estadoEditar').val(f.estado);
                        
                        // Configurar tipo de tiempo
                        if (f.tipo_tiempo === 'libre') {
                            $('#tipo_libre_editar').prop('checked', true);
                            mostrarRangoFechasEditar(false);
                        } else {
                            $('#tipo_rango_editar').prop('checked', true);
                            mostrarRangoFechasEditar(true);
                            $('#fecha_inicio_editar').val(f.fecha_inicio ? f.fecha_inicio.replace(' ', 'T') : '');
                            $('#fecha_fin_editar').val(f.fecha_fin ? f.fecha_fin.replace(' ', 'T') : '');
                        }
                        
                        // Mostrar modal
                        $('#modalEditar').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Error al cargar el formulario'
                        });
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al conectar con el servidor'
                    });
                }
            });
        }
    </script>
</body>
</html>
<?php 
// Incluir footer
require_once __DIR__ . '/../complementos/footer.php'; 
?>