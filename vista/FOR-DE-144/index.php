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
        }
        
        body {
            background-color: var(--color-bg);
            color: var(--color-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            padding: 25px 0;
        }
        
        .formulario-card {
            background: var(--color-white);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(44, 62, 80, 0.1);
            padding: 25px;
            transition: all 0.3s ease;
            height: 280px;
            display: flex;
            flex-direction: column;
            position: relative;
            border-left: 4px solid var(--color-primary);
        }
        
        .formulario-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(44, 62, 80, 0.15);
            border-left-color: var(--color-accent);
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
        }
        
        .formulario-descripcion {
            color: var(--color-text-light);
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .formulario-fecha {
            font-size: 0.8rem;
            color: var(--color-text-light);
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ECF0F1;
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-title {
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
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
            font-weight: 600;
        }
        
        .btn-secondary:hover {
            background: #7F8C8D;
        }
        
        .btn-warning {
            background: var(--color-warning);
            border: none;
        }
        
        .btn-danger {
            background: var(--color-danger);
            border: none;
        }
        
        .btn-close-white {
            filter: invert(1) brightness(2);
            opacity: 0.8;
        }
        
        .btn-close-white:hover {
            opacity: 1;
        }
        
        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.25rem rgba(44, 62, 80, 0.25);
        }
        
        #formulariosContainer {
            min-height: 450px;
        }
        
        .btn-actions {
            margin-top: 15px;
        }
        
        .badge {
            padding: 6px 12px;
            font-weight: 500;
            border-radius: 20px;
        }
        
        .badge.bg-success {
            background-color: var(--color-success) !important;
        }
        
        .badge.bg-secondary {
            background-color: #95A5A6 !important;
        }
        
        .alert-info {
            background-color: #D6EAF8;
            border-color: #AED6F1;
            color: var(--color-primary);
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
        }
        
        /* Estilos para los modales */
        .modal-body {
            padding: 25px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #BDC3C7;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.15);
        }
        
        .invalid-feedback {
            color: var(--color-danger);
            font-size: 0.85rem;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px;
        }
        
        .alert-info {
            background-color: #E8F4FC;
            color: var(--color-primary);
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
                    <p class="header-subtitle">Crea, edita y gestiona tus formularios de manera eficiente</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-muted">
                        <small><i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y'); ?></small>
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
            ?>
     
            <div class="formulario-card" id="formulario-<?php echo $formulario['id']; ?>">
                <div class="formulario-titulo"><?php echo htmlspecialchars($formulario['titulo']); ?></div>
                <div class="formulario-descripcion"><?php echo htmlspecialchars($formulario['descripcion'] ?: 'Sin descripción'); ?></div>
                <div class="formulario-fecha">
                    <i class="far fa-clock me-1"></i>Creado: <?php echo $fechaFormateada; ?>
                </div>
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <span class="badge <?php echo $formulario['estado'] == 1 ? 'bg-success' : 'bg-secondary'; ?>">
                        <i class="fas fa-<?php echo $formulario['estado'] == 1 ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                        <?php echo $formulario['estado'] == 1 ? 'Activo' : 'Inactivo'; ?>
                    </span>
                    <div class="btn-actions">
                        <button class="btn btn-sm btn-success me-2" onclick="window.location.href='modulo144/?id=<?php echo $formulario['id']; ?>'" 
                                title="Ver formulario">
                            <i class="fas fa-eye me-1"></i>Ver
                        </button>
                        <button class="btn btn-sm btn-warning me-2" onclick="editarFormulario(<?php echo $formulario['id']; ?>)" 
                                title="Editar formulario">
                            <i class="fas fa-edit me-1"></i>Editar
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarFormulario(<?php echo $formulario['id']; ?>)" 
                                title="Eliminar formulario">
                            <i class="fas fa-trash me-1"></i>Eliminar
                        </button>
                    </div>
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
        <div class="modal-dialog modal-dialog-centered">
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
                            <div class="invalid-feedback">El título es obligatorio</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="4" placeholder="Describe el propósito o contenido del formulario (opcional)"></textarea>
                            <div class="form-text">Puedes agregar detalles sobre qué información contiene este formulario.</div>
                        </div>
                        
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                El formulario se registrará con la fecha y hora actual automáticamente.
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
        <div class="modal-dialog modal-dialog-centered">
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
                            <div class="invalid-feedback">El título es obligatorio</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="descripcionEditar" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcionEditar" name="descripcion" rows="4"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="estadoEditar" class="form-label">Estado del Formulario</label>
                            <select class="form-control" id="estadoEditar" name="estado">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            <div class="form-text">Los formularios inactivos no aparecerán en el listado principal.</div>
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
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            const basePath = '<?php echo Config::getBasePath(); ?>';
            
            // Manejar envío del formulario de agregar
            $('#formAgregarFormulario').on('submit', function(e) {
                e.preventDefault();
                
                if (!$(this)[0].checkValidity()) {
                    $(this).addClass('was-validated');
                    return;
                }
                
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
                            $('#formAgregarFormulario').removeClass('was-validated');
                            
                            showMessage('success', 'Formulario creado exitosamente');
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            showMessage('error', 'Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                        showMessage('error', 'Error al conectar con el servidor');
                        console.error('Error:', error);
                    }
                });
            });
            
            // Manejar envío del formulario de editar
            $('#formEditarFormulario').on('submit', function(e) {
                e.preventDefault();
                
                if (!$(this)[0].checkValidity()) {
                    $(this).addClass('was-validated');
                    return;
                }
                
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
                            showMessage('success', 'Formulario actualizado exitosamente');
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            showMessage('error', 'Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                        showMessage('error', 'Error al conectar con el servidor');
                        console.error('Error:', error);
                    }
                });
            });
        });
        
        // Función para mostrar mensajes
        function showMessage(type, text) {
            $('.alert-message').remove();
            
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            const title = type === 'success' ? 'Éxito' : 'Error';
            
            const message = `
                <div class="alert-message alert ${alertClass} alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
                    <div class="d-flex align-items-center">
                        <i class="fas ${icon} fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-1">${title}</h6>
                            <p class="mb-0">${text}</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            $('body').append(message);
            
            setTimeout(() => {
                $('.alert-message').alert('close');
            }, 5000);
        }
        
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
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const basePath = '<?php echo Config::getBasePath(); ?>';
                    
                    $.ajax({
                        url: basePath + '/FOR-DE-144?action=eliminar',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showMessage('success', 'Formulario eliminado exitosamente');
                                setTimeout(() => location.reload(), 1200);
                            } else {
                                showMessage('error', 'Error: ' + response.message);
                            }
                        },
                        error: function() {
                            showMessage('error', 'Error al conectar con el servidor');
                        }
                    });
                }
            });
        }
        
        // Función para editar formulario
        function editarFormulario(id) {
            const basePath = '<?php echo Config::getBasePath(); ?>';
            
            $('#modalEditar .modal-body').html(`
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3">Cargando información del formulario...</p>
                </div>
            `);
            $('#modalEditar').modal('show');
            
            $.ajax({
                url: basePath + '/FOR-DE-144?action=getFormulario&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.formulario) {
                        const formulario = response.formulario;
                        
                        $('#modalEditar .modal-body').html(`
                            <input type="hidden" id="formularioIdEditar" name="id">
                            <div class="mb-3">
                                <label for="tituloEditar" class="form-label">Título del Formulario *</label>
                                <input type="text" class="form-control" id="tituloEditar" name="titulo" required>
                                <div class="invalid-feedback">El título es obligatorio</div>
                            </div>
                            <div class="mb-4">
                                <label for="descripcionEditar" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcionEditar" name="descripcion" rows="4"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="estadoEditar" class="form-label">Estado del Formulario</label>
                                <select class="form-control" id="estadoEditar" name="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                <div class="form-text">Los formularios inactivos no aparecerán en el listado principal.</div>
                            </div>
                        `);
                        
                        $('#formularioIdEditar').val(formulario.id);
                        $('#tituloEditar').val(formulario.titulo);
                        $('#descripcionEditar').val(formulario.descripcion);
                        $('#estadoEditar').val(formulario.estado);
                    } else {
                        $('#modalEditar').modal('hide');
                        showMessage('error', 'Error al cargar el formulario: ' + (response.message || 'Error desconocido'));
                    }
                },
                error: function() {
                    $('#modalEditar').modal('hide');
                    showMessage('error', 'Error al conectar con el servidor');
                }
            });
        }
        
        // Agregar SweetAlert2 para mejores diálogos
        if (typeof Swal === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            script.onload = function() {
                console.log('SweetAlert2 cargado');
            };
            document.head.appendChild(script);
        }
    </script>
</body>
</html>