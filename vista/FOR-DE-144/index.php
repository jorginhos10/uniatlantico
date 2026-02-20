<?php
// vista/FOR-DE-144/index.php - VERSIÓN COMPLETA CON TARJETAS CLIQUEABLES
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formatos FOR-DE-144</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/formatos_for_de_144.css">
</head>
<body>
    <div class="formatos-container">
        
        <!-- Mensajes de sesión -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje-exito">
                <i class="fas fa-check-circle"></i>
                <?= $_SESSION['mensaje'] ?>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mensaje-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Cabecera -->
        <div class="formatos-header">
            <h2>
                <i class="fas fa-file-alt"></i>
                Formatos FOR-DE-144
            </h2>

        </div>

        <!-- Grid de tarjetas -->
        <div class="cards-grid">
            <!-- Tarjeta para agregar nuevo -->
            <div class="card card-agregar" onclick="FormatoForDe144.abrirPopupCrear()">
                <div class="card-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h3>Crear Nuevo</h3>
                <p>Agrega un nuevo formato FOR-DE-144</p>
            </div>

            <!-- Tarjetas de formatos existentes -->
            <?php if (!empty($formularios)): ?>
                <?php foreach ($formularios as $formato): ?>
                    <div class="card <?= $formato['disponible'] ? 'clickable' : 'disabled' ?>" 
                         onclick="window.location.href='yodecido/?id=<?= $formato['id'] ?>'">
                        
                        <div class="card-header">
                            <span class="badge">
                                <i class="fas fa-hashtag"></i> ID: <?= $formato['id'] ?>
                            </span>
                            <div class="acciones-tarjeta">
                                <button class="btn-editar" onclick="event.stopPropagation(); FormatoForDe144.abrirPopupEditar(<?= htmlspecialchars(json_encode($formato)) ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-eliminar" onclick="event.stopPropagation(); FormatoForDe144.confirmarEliminar(<?= $formato['id'] ?>, '<?= htmlspecialchars($formato['titulo']) ?>')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <h4><?= htmlspecialchars($formato['titulo']) ?></h4>
                            <p class="descripcion"><?= htmlspecialchars($formato['descripcion'] ?? 'Sin descripción') ?></p>
                            
                            <!-- Información de tiempo -->
                            <div class="info-tiempo">
                                <?php if (empty($formato['fecha_inicio']) && empty($formato['fecha_cierre'])): ?>
                                    <span class="badge-tiempo badge-sin-restricciones">
                                        <i class="fas fa-infinity"></i> Sin restricciones
                                    </span>
                                <?php else: ?>
                                    <?php if ($formato['estado_tiempo'] === 'activo'): ?>
                                        <span class="badge-tiempo badge-activo">
                                            <i class="fas fa-check-circle"></i> Activo
                                        </span>
                                    <?php elseif ($formato['estado_tiempo'] === 'proximamente'): ?>
                                        <span class="badge-tiempo badge-proximamente">
                                            <i class="fas fa-clock"></i> Próximamente
                                        </span>
                                    <?php elseif ($formato['estado_tiempo'] === 'cerrado'): ?>
                                        <span class="badge-tiempo badge-cerrado">
                                            <i class="fas fa-lock"></i> Cerrado
                                        </span>
                                    <?php endif; ?>
                                    
                                    <div class="fecha-texto">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= date('d/m/Y', strtotime($formato['fecha_inicio'])) ?> - 
                                        <?= date('d/m/Y', strtotime($formato['fecha_cierre'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <i class="fas fa-clock"></i>
                            Creado: <?= date('d/m/Y H:i', strtotime($formato['fecha_creacion'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-formatos">
                    <i class="fas fa-folder-open"></i>
                    <p>No hay formatos creados todavía</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- POPUP: Crear Nuevo Formato -->
    <div id="popupCrear" class="popup-overlay">
        <div class="popup">
            <div class="popup-header">
                <h3>
                    <i class="fas fa-plus-circle" style="color: #4CAF50;"></i>
                    Nuevo Formato
                </h3>
                <button class="cerrar" onclick="FormatoForDe144.cerrarPopup()">&times;</button>
            </div>
            
            <form id="formNuevoFormato" action="index.php?controller=FORDE144&action=crear" method="POST">
                <div class="popup-body">
                    <div class="form-group">
                        <label for="titulo">Título del Formato *</label>
                        <input type="text" id="titulo" name="titulo" placeholder="Ej: Formato de evaluación mensual" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" placeholder="Describe el propósito de este formato..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Configuración de Tiempo</label>
                        <div class="opciones-tiempo">
                            <div class="opcion-radio">
                                <input type="radio" name="tipo_tiempo" id="sin_restricciones" value="sin_restricciones" checked>
                                <label for="sin_restricciones">
                                    <i class="fas fa-infinity"></i> Sin restricciones de tiempo
                                </label>
                                <small>El formato estará disponible permanentemente</small>
                            </div>
                            
                            <div class="opcion-radio">
                                <input type="radio" name="tipo_tiempo" id="con_restricciones" value="con_restricciones">
                                <label for="con_restricciones">
                                    <i class="fas fa-calendar-alt"></i> Con período definido
                                </label>
                                <small>El formato solo estará disponible en las fechas seleccionadas</small>
                            </div>

                            <div id="camposFecha" class="campos-fecha" style="display: none;">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha de Apertura</label>
                                    <input type="datetime-local" id="fecha_inicio" name="fecha_inicio">
                                </div>
                                
                                <div class="form-group">
                                    <label for="fecha_cierre">Fecha de Cierre</label>
                                    <input type="datetime-local" id="fecha_cierre" name="fecha_cierre">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="popup-footer">
                    <button type="button" class="btn-cancelar" onclick="FormatoForDe144.cerrarPopup()">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn-guardar">
                        <i class="fas fa-save"></i>
                        Guardar Formato
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- POPUP: Editar Formato -->
    <div id="popupEditar" class="popup-overlay">
        <div class="popup">
            <div class="popup-header">
                <h3>
                    <i class="fas fa-edit" style="color: #3498db;"></i>
                    Editar Formato
                </h3>
                <button class="cerrar" onclick="FormatoForDe144.cerrarPopupEditar()">&times;</button>
            </div>
            
            <form id="formEditarFormato" action="index.php?controller=FORDE144&action=editar" method="POST">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="popup-body">
                    <div class="form-group">
                        <label for="edit_titulo">Título del Formato *</label>
                        <input type="text" id="edit_titulo" name="titulo" placeholder="Ej: Formato de evaluación mensual" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_descripcion">Descripción</label>
                        <textarea id="edit_descripcion" name="descripcion" placeholder="Describe el propósito de este formato..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Configuración de Tiempo</label>
                        <div class="opciones-tiempo">
                            <div class="opcion-radio">
                                <input type="radio" name="edit_tipo_tiempo" id="edit_sin_restricciones" value="sin_restricciones" checked>
                                <label for="edit_sin_restricciones">
                                    <i class="fas fa-infinity"></i> Sin restricciones de tiempo
                                </label>
                                <small>El formato estará disponible permanentemente</small>
                            </div>
                            
                            <div class="opcion-radio">
                                <input type="radio" name="edit_tipo_tiempo" id="edit_con_restricciones" value="con_restricciones">
                                <label for="edit_con_restricciones">
                                    <i class="fas fa-calendar-alt"></i> Con período definido
                                </label>
                                <small>El formato solo estará disponible en las fechas seleccionadas</small>
                            </div>

                            <div id="edit_camposFecha" class="campos-fecha" style="display: none;">
                                <div class="form-group">
                                    <label for="edit_fecha_inicio">Fecha de Apertura</label>
                                    <input type="datetime-local" id="edit_fecha_inicio" name="fecha_inicio">
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_fecha_cierre">Fecha de Cierre</label>
                                    <input type="datetime-local" id="edit_fecha_cierre" name="fecha_cierre">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="popup-footer">
                    <button type="button" class="btn-cancelar" onclick="FormatoForDe144.cerrarPopupEditar()">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn-guardar" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                        <i class="fas fa-save"></i>
                        Actualizar Formato
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- POPUP: Confirmar Eliminación -->
    <div id="popupConfirmar" class="popup-overlay">
        <div class="popup popup-small">
            <div class="popup-header">
                <h3>
                    <i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i>
                    Confirmar Eliminación
                </h3>
                <button class="cerrar" onclick="FormatoForDe144.cerrarConfirmar()">&times;</button>
            </div>
            
            <div class="popup-body">
                <p id="mensajeConfirmacion" style="font-size: 16px; line-height: 1.6; color: #2c3e50;">
                    ¿Estás seguro de eliminar este formato?
                </p>
                <p style="color: #e74c3c; font-size: 14px; margin-top: 10px;">
                    <i class="fas fa-info-circle"></i> Esta acción no se puede deshacer.
                </p>
            </div>
            
            <div class="popup-footer">
                <button class="btn-cancelar" onclick="FormatoForDe144.cerrarConfirmar()">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button id="btnEliminarConfirmar" class="btn-eliminar-popup">
                    <i class="fas fa-trash-alt"></i>
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </div>

    <meta name="base-path" content="<?= dirname($_SERVER['SCRIPT_NAME']) ?>">
    
    <script src="assets/js/formatos_for_de_144.js"></script>
    <script>
        // Configurar el popup de creación
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle campos de fecha en popup de creación
            const sinRestricciones = document.getElementById('sin_restricciones');
            const conRestricciones = document.getElementById('con_restricciones');
            const camposFecha = document.getElementById('camposFecha');
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaCierre = document.getElementById('fecha_cierre');

            function toggleCamposFecha() {
                if (conRestricciones.checked) {
                    camposFecha.style.display = 'block';
                    fechaInicio.required = true;
                    fechaCierre.required = true;
                    
                    // Establecer fecha mínima por defecto (hoy)
                    const hoy = new Date().toISOString().slice(0, 16);
                    fechaInicio.min = hoy;
                    fechaCierre.min = hoy;
                } else {
                    camposFecha.style.display = 'none';
                    fechaInicio.required = false;
                    fechaCierre.required = false;
                    fechaInicio.value = '';
                    fechaCierre.value = '';
                }
            }

            sinRestricciones.addEventListener('change', toggleCamposFecha);
            conRestricciones.addEventListener('change', toggleCamposFecha);

            // Validar que fecha cierre sea mayor a fecha inicio
            fechaInicio.addEventListener('change', function() {
                fechaCierre.min = this.value;
                if (fechaCierre.value && fechaCierre.value < this.value) {
                    fechaCierre.value = this.value;
                }
            });

            // Toggle campos de fecha en popup de edición
            const editSinRestricciones = document.getElementById('edit_sin_restricciones');
            const editConRestricciones = document.getElementById('edit_con_restricciones');
            const editCamposFecha = document.getElementById('edit_camposFecha');
            const editFechaInicio = document.getElementById('edit_fecha_inicio');
            const editFechaCierre = document.getElementById('edit_fecha_cierre');

            function toggleEditCamposFecha() {
                if (editConRestricciones && editConRestricciones.checked) {
                    editCamposFecha.style.display = 'block';
                    editFechaInicio.required = true;
                    editFechaCierre.required = true;
                    
                    // Establecer fecha mínima por defecto (hoy)
                    const hoy = new Date().toISOString().slice(0, 16);
                    editFechaInicio.min = hoy;
                    editFechaCierre.min = hoy;
                } else if (editSinRestricciones && editSinRestricciones.checked) {
                    editCamposFecha.style.display = 'none';
                    editFechaInicio.required = false;
                    editFechaCierre.required = false;
                }
            }

            if (editSinRestricciones && editConRestricciones) {
                editSinRestricciones.addEventListener('change', toggleEditCamposFecha);
                editConRestricciones.addEventListener('change', toggleEditCamposFecha);
            }

            // Validar que fecha cierre sea mayor a fecha inicio en popup de edición
            if (editFechaInicio && editFechaCierre) {
                editFechaInicio.addEventListener('change', function() {
                    editFechaCierre.min = this.value;
                    if (editFechaCierre.value && editFechaCierre.value < this.value) {
                        editFechaCierre.value = this.value;
                    }
                });
            }

            // Validar formulario de edición
            const formEditar = document.getElementById('formEditarFormato');
            if (formEditar) {
                formEditar.addEventListener('submit', function(e) {
                    const titulo = document.getElementById('edit_titulo').value.trim();
                    
                    if (!titulo) {
                        e.preventDefault();
                        alert('Por favor, ingresa un título para el formato');
                        document.getElementById('edit_titulo').focus();
                        return false;
                    }
                    
                    // Validar fechas si se seleccionó con restricciones
                    if (editConRestricciones && editConRestricciones.checked) {
                        if (!editFechaInicio.value || !editFechaCierre.value) {
                            e.preventDefault();
                            alert('Por favor, completa las fechas de inicio y cierre');
                            return false;
                        }
                        
                        if (new Date(editFechaInicio.value) > new Date(editFechaCierre.value)) {
                            e.preventDefault();
                            alert('La fecha de inicio no puede ser mayor a la fecha de cierre');
                            return false;
                        }
                    }
                    
                    // Mostrar indicador de carga
                    const btnGuardar = this.querySelector('.btn-guardar');
                    if (btnGuardar) {
                        const originalText = btnGuardar.innerHTML;
                        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
                        btnGuardar.disabled = true;
                    }
                });
            }
        });
    </script>
</body>
</html>