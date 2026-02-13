<?php
// vista/modulo144/index.php
$basePath = Config::getBasePath();
$fecha_cierre = $formulario['fecha_cierre'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA 144 - <?php echo htmlspecialchars($formulario['titulo'] ?? ''); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --color-primary: #2C3E50;
            --color-primary-light: #34495E;
            --color-success: #27AE60;
            --color-warning: #F39C12;
            --color-danger: #E74C3C;
            --color-info: #3498DB;
            --color-bg: #F8F9FA;
            --color-white: #FFFFFF;
        }
        
        body {
            background-color: var(--color-bg);
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        
        .header-info {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 20px rgba(44,62,80,0.2);
        }
        
        .countdown-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .countdown-timer {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .countdown-box {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 15px;
            min-width: 100px;
            text-align: center;
        }
        
        .countdown-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 5px;
            color: white;
        }
        
        .countdown-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .accordion-item {
            border: none;
            margin-bottom: 20px;
            border-radius: 15px !important;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .accordion-button {
            padding: 20px 25px;
            font-size: 1.3rem;
            font-weight: 700;
            color: white !important;
            border-radius: 15px !important;
        }
        
        .accordion-button:not(.collapsed) {
            box-shadow: none;
        }
        
        .accordion-button::after {
            filter: brightness(0) invert(1);
        }
        
        .item-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            border-left: 4px solid var(--color-primary);
        }
        
        .item-actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .estado-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            white-space: nowrap;
        }
        
        .estado-borrador { background: linear-gradient(135deg, #7F8C8D 0%, #95A5A6 100%); }
        .estado-publicado { background: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%); }
        .estado-cancelado { background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%); }
        .estado-expirado { background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%); }
        .estado-vigente { background: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%); }
        .estado-sin-fechas { background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%); }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            background: #f8f9fc;
            border-radius: 12px;
            border: 1px dashed #dee2e6;
        }
        
        .empty-state i {
            color: #adb5bd;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }
        
        .btn-close-white {
            filter: invert(1) brightness(2);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 12px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.25rem rgba(44,62,80,0.15);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 8px;
        }
        
        @media (max-width: 768px) {
            .countdown-box {
                min-width: 70px;
                padding: 10px;
            }
            .countdown-number {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="header-info">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-cubes me-3"></i>SISTEMA 144
                    </h1>
                    <h4 class="mb-0"><?php echo htmlspecialchars($formulario['titulo'] ?? 'Sin título'); ?></h4>
                    <p class="mb-0 mt-2 opacity-75">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo htmlspecialchars($formulario['descripcion'] ?? 'Sin descripción'); ?>
                    </p>
                    <div class="fechas-info mt-3">
                        <?php if (!empty($formulario['fecha_inicio'])): ?>
                        <span class="me-3"><i class="fas fa-play-circle"></i> Inicio: <?php echo date('d/m/Y H:i', strtotime($formulario['fecha_inicio'])); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($formulario['fecha_cierre'])): ?>
                        <span class="me-3"><i class="fas fa-stop-circle"></i> Cierre: <?php echo date('d/m/Y H:i', strtotime($formulario['fecha_cierre'])); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="estado-badge estado-<?php echo $estado_fechas['clase']; ?> mb-2">
                        <i class="fas fa-<?php echo $estado_fechas['valido'] ? 'check-circle' : 'exclamation-circle'; ?> me-1"></i>
                        <?php echo $estado_fechas['mensaje']; ?>
                    </span>
                    <br>
                    <a href="<?php echo $basePath; ?>/FOR-DE-144" class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
            
            <?php if ($estado_fechas['valido'] && $fecha_cierre): ?>
            <div class="countdown-container">
                <div class="countdown-title text-center mb-3">
                    <i class="fas fa-hourglass-half me-2"></i>Tiempo restante para el cierre:
                </div>
                <div class="countdown-timer" id="countdown-timer">
                    <div class="countdown-box"><div class="countdown-number" id="days">00</div><div class="countdown-label">Días</div></div>
                    <div class="countdown-box"><div class="countdown-number" id="hours">00</div><div class="countdown-label">Horas</div></div>
                    <div class="countdown-box"><div class="countdown-number" id="minutes">00</div><div class="countdown-label">Minutos</div></div>
                    <div class="countdown-box"><div class="countdown-number" id="seconds">00</div><div class="countdown-label">Segundos</div></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!$estado_fechas['valido']): ?>
        <div class="alert alert-warning text-center py-4 mb-4">
            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
            <h3>Formulario no disponible</h3>
            <p class="lead"><?php echo $estado_fechas['mensaje']; ?></p>
            <a href="<?php echo $basePath; ?>/FOR-DE-144" class="btn btn-primary mt-2">
                <i class="fas fa-arrow-left me-2"></i>Ver otros formularios
            </a>
        </div>
        <?php else: ?>

        <div class="accordion" id="accordionModulos">
            <?php $primer_modulo = false; ?>
            <?php foreach ($datos_modulos as $key => $modulo): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?php echo $key; ?>">
                    <button class="accordion-button <?php echo $primer_modulo ? '' : 'collapsed'; ?>" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapse<?php echo $key; ?>" 
                            style="background: <?php echo $modulo['config']['color_header']; ?>; color: white !important;">
                        <i class="fas <?php echo $modulo['config']['icono']; ?> me-3 fa-2x"></i>
                        <div>
                            <span style="font-size: 1.3rem;"><?php echo $modulo['config']['nombre']; ?></span>
                            <br>
                            <small style="font-size: 0.85rem; opacity: 0.9;"><?php echo $modulo['config']['descripcion']; ?></small>
                        </div>
                        <span class="badge bg-light text-dark ms-3">
                            B: <?php echo count($modulo['borradores']); ?> | 
                            P: <?php echo count($modulo['publicados']); ?> | 
                            C: <?php echo count($modulo['cancelados']); ?>
                        </span>
                    </button>
                </h2>
                <div id="collapse<?php echo $key; ?>" 
                     class="accordion-collapse collapse <?php echo $primer_modulo ? 'show' : ''; ?>" 
                     data-bs-parent="#accordionModulos">
                    <div class="accordion-body p-4">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0" style="color: <?php echo $modulo['config']['color']; ?>;">
                                <i class="fas <?php echo $modulo['config']['icono']; ?> me-2"></i>
                                Gestión de <?php echo $modulo['config']['nombre']; ?>
                            </h4>
                            <button class="btn btn-success" onclick="abrirModalNuevoBorrador('<?php echo $key; ?>')">
                                <i class="fas fa-plus me-1"></i>Nuevo Borrador
                            </button>
                        </div>
                        
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-secondary bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="fas fa-pen-fancy" style="color: #7F8C8D;"></i>
                                </div>
                                <h5 class="mb-0">Borradores</h5>
                                <span class="badge bg-secondary ms-2"><?php echo count($modulo['borradores']); ?></span>
                            </div>
                            
                            <?php if (count($modulo['borradores']) > 0): ?>
                                <div class="row g-3">
                                    <?php foreach ($modulo['borradores'] as $borrador): ?>
                                    <div class="col-xl-4 col-lg-6 col-md-6">
                                        <div class="item-card h-100">
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h5 class="mb-0"><?php echo htmlspecialchars($borrador['nombre_borrador']); ?></h5>
                                                    <span class="estado-badge estado-borrador ms-2">Borrador</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="far fa-calendar-alt me-1"></i>
                                                        <?php echo date('d/m/Y H:i', strtotime($borrador['fecha_creacion'])); ?>
                                                    </small>
                                                </div>
                                                <?php if (!empty($borrador['anio'])): ?>
                                                <span class="badge bg-secondary mb-2">Año: <?php echo $borrador['anio']; ?></span>
                                                <?php endif; ?>
                                                <div class="item-actions mt-3">
                                                    <button class="btn btn-sm btn-warning" onclick="editarBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-success" onclick="cambiarEstadoBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 2)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="cambiarEstadoBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 1)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-info" onclick="abrirModalDuplicar('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, '<?php echo htmlspecialchars($borrador['nombre_borrador']); ?>')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state p-4">
                                    <i class="fas fa-file-alt fa-3x mb-3"></i>
                                    <h6>No hay borradores</h6>
                                    <p class="text-muted small">Haz clic en "Nuevo Borrador" para comenzar</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="fas fa-check-circle" style="color: #27AE60;"></i>
                                </div>
                                <h5 class="mb-0">Publicados</h5>
                                <span class="badge bg-success ms-2"><?php echo count($modulo['publicados']); ?></span>
                            </div>
                            
                            <?php if (count($modulo['publicados']) > 0): ?>
                                <div class="row g-3">
                                    <?php foreach ($modulo['publicados'] as $publicado): ?>
                                    <div class="col-xl-4 col-lg-6 col-md-6">
                                        <div class="item-card h-100">
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h5 class="mb-0"><?php echo htmlspecialchars($publicado['nombre_borrador']); ?></h5>
                                                    <span class="estado-badge estado-publicado ms-2">Publicado</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="far fa-calendar-alt me-1"></i>
                                                        <?php echo date('d/m/Y H:i', strtotime($publicado['fecha_creacion'])); ?>
                                                    </small>
                                                </div>
                                                <?php if (!empty($publicado['anio'])): ?>
                                                <span class="badge bg-secondary mb-2">Año: <?php echo $publicado['anio']; ?></span>
                                                <?php endif; ?>
                                                <div class="mt-3">
                                                    <button class="btn btn-sm btn-primary" onclick="verBorrador('<?php echo $key; ?>', <?php echo $publicado['id']; ?>)">
                                                        <i class="fas fa-eye me-1"></i>Ver detalles
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state p-4">
                                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                                    <h6>No hay publicaciones</h6>
                                    <p class="text-muted small">Los borradores aprobados aparecerán aquí</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-danger bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="fas fa-times-circle" style="color: #E74C3C;"></i>
                                </div>
                                <h5 class="mb-0">Cancelados</h5>
                                <span class="badge bg-danger ms-2"><?php echo count($modulo['cancelados']); ?></span>
                            </div>
                            
                            <?php if (count($modulo['cancelados']) > 0): ?>
                                <div class="row g-3">
                                    <?php foreach ($modulo['cancelados'] as $cancelado): ?>
                                    <div class="col-xl-4 col-lg-6 col-md-6">
                                        <div class="item-card h-100">
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h5 class="mb-0"><?php echo htmlspecialchars($cancelado['nombre_borrador']); ?></h5>
                                                    <span class="estado-badge estado-cancelado ms-2">Cancelado</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="far fa-calendar-alt me-1"></i>
                                                        <?php echo date('d/m/Y H:i', strtotime($cancelado['fecha_creacion'])); ?>
                                                    </small>
                                                </div>
                                                <?php if (!empty($cancelado['anio'])): ?>
                                                <span class="badge bg-secondary mb-2">Año: <?php echo $cancelado['anio']; ?></span>
                                                <?php endif; ?>
                                                <div class="mt-3">
                                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarBorrador('<?php echo $key; ?>', <?php echo $cancelado['id']; ?>)">
                                                        <i class="fas fa-trash me-1"></i>Eliminar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state p-4">
                                    <i class="fas fa-times-circle fa-3x mb-3"></i>
                                    <h6>No hay cancelados</h6>
                                    <p class="text-muted small">Los borradores cancelados aparecerán aquí</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php $primer_modulo = false; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- MODALES -->
    <div class="modal fade" id="modalNuevoBorrador" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #2C3E50 0%, #34495E 100%);">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nuevo Borrador</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevoBorrador">
                    <input type="hidden" name="modulo" id="nuevo_modulo">
                    <input type="hidden" name="formulario_id" value="<?php echo $formulario['id']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Borrador *</label>
                            <input type="text" class="form-control" name="nombre_borrador" id="nuevo_nombre" required placeholder="Ej: Versión 1.0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Crear Borrador</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDuplicarBorrador" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);">
                    <h5 class="modal-title"><i class="fas fa-copy me-2"></i>Duplicar Borrador</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formDuplicarBorrador">
                    <input type="hidden" name="modulo" id="duplicar_modulo">
                    <input type="hidden" name="id" id="duplicar_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Nuevo Borrador *</label>
                            <input type="text" class="form-control" name="nombre_duplicado" id="duplicar_nombre" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info"><i class="fas fa-copy me-1"></i>Duplicar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFormulacion" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #2C3E50 0%, #34495E 100%);">
                    <h5 class="modal-title"><i class="fas fa-clipboard-list me-2"></i>FORMULACIÓN 144 - <span id="tituloFormulacion"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formFormulacion">
                    <input type="hidden" name="modulo" value="formulacion">
                    <input type="hidden" id="formulacion_id" name="id">
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Nombre del Borrador</label>
                                <input type="text" class="form-control" id="formulacion_nombre" name="nombre_borrador" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">AÑO</label>
                                <select class="form-select" name="anio" id="formulacion_anio">
                                    <option value="">Seleccione año</option>
                                    <?php for ($i = date('Y'); $i <= date('Y') + 5; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">LÍNEA ESTRATÉGICA</label>
                                <input type="text" class="form-control" name="linea_estrategica" id="formulacion_linea">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">OBJETIVO</label>
                                <textarea class="form-control" name="objetivo" id="formulacion_objetivo" rows="3"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">ESTRATEGIA</label>
                                <textarea class="form-control" name="estrategia" id="formulacion_estrategia" rows="3"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">MOTOR DE DESARROLLO</label>
                                <input type="text" class="form-control" name="motor_desarrollo" id="formulacion_motor">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">META DE RESULTADO</label>
                                <textarea class="form-control" name="meta_resultado" id="formulacion_meta" rows="2"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">PROYECTO</label>
                                <input type="text" class="form-control" name="proyecto" id="formulacion_proyecto">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PONDERACIÓN DE LOS PROYECTOS</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="ponderacion_proyectos" id="formulacion_ponderacion_proyectos" step="0.01" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">ACTIVIDAD DEL PROYECTO (205)</label>
                                <textarea class="form-control" name="actividad_proyecto" id="formulacion_actividad" rows="4"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PONDERACIÓN DE LAS ACTIVIDADES POR PROYECTO</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="ponderacion_actividades" id="formulacion_ponderacion_actividades" step="0.01" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RESPONSABLE</label>
                                <input type="text" class="form-control" name="responsable" id="formulacion_responsable">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSeguimiento" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);">
                    <h5 class="modal-title"><i class="fas fa-chart-line me-2"></i>SEGUIMIENTO 144 - <span id="tituloSeguimiento"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSeguimiento">
                    <input type="hidden" name="modulo" value="seguimiento">
                    <input type="hidden" id="seguimiento_id" name="id">
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Nombre del Borrador</label>
                                <input type="text" class="form-control" id="seguimiento_nombre" name="nombre_borrador" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">INDICADOR</label>
                                <input type="text" class="form-control" name="indicador" id="seguimiento_indicador" placeholder="Ej: % de cumplimiento">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">FECHA DE SEGUIMIENTO</label>
                                <input type="date" class="form-control" name="fecha_seguimiento" id="seguimiento_fecha">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">META PROGRAMADA</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="meta_programada" id="seguimiento_meta_programada" step="0.01">
                                    <span class="input-group-text">$</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">META EJECUTADA</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="meta_ejecutada" id="seguimiento_meta_ejecutada" step="0.01">
                                    <span class="input-group-text">$</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">% AVANCE</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="porcentaje_avance" id="seguimiento_porcentaje" step="0.01" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RESPONSABLE</label>
                                <input type="text" class="form-control" name="responsable_seguimiento" id="seguimiento_responsable">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">OBSERVACIONES</label>
                                <textarea class="form-control" name="observaciones" id="seguimiento_observaciones" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalVerFormulario" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Ver Formulación - <span id="ver_titulo"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="ver_contenido"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const basePath = '<?php echo $basePath; ?>';
        const formularioId = <?php echo $formulario['id']; ?>;
        
        <?php if ($estado_fechas['valido'] && $fecha_cierre): ?>
        function actualizarContador() {
            const fechaCierre = new Date('<?php echo $fecha_cierre; ?>').getTime();
            const ahora = new Date().getTime();
            const distancia = fechaCierre - ahora;
            
            if (distancia < 0) {
                document.getElementById('days').innerHTML = '00';
                document.getElementById('hours').innerHTML = '00';
                document.getElementById('minutes').innerHTML = '00';
                document.getElementById('seconds').innerHTML = '00';
                setTimeout(() => location.reload(), 3000);
                return;
            }
            
            document.getElementById('days').innerHTML = Math.floor(distancia / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
            document.getElementById('hours').innerHTML = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
            document.getElementById('minutes').innerHTML = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
            document.getElementById('seconds').innerHTML = Math.floor((distancia % (1000 * 60)) / 1000).toString().padStart(2, '0');
        }
        actualizarContador();
        setInterval(actualizarContador, 1000);
        <?php endif; ?>

        function abrirModalNuevoBorrador(modulo) {
            $('#nuevo_modulo').val(modulo);
            $('#nuevo_nombre').val('');
            $('#modalNuevoBorrador').modal('show');
        }

        $('#formNuevoBorrador').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: basePath + '/modulo144/crearBorrador',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Creado!', response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        });

        function abrirModalDuplicar(modulo, id, nombre) {
            $('#duplicar_modulo').val(modulo);
            $('#duplicar_id').val(id);
            $('#duplicar_nombre').val('Copia de ' + nombre);
            $('#modalDuplicarBorrador').modal('show');
        }

        $('#formDuplicarBorrador').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: basePath + '/modulo144/duplicar',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Duplicado!', response.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    }
                }
            });
        });

        function editarBorrador(modulo, id) {
            $.ajax({
                url: basePath + '/modulo144/getBorrador?modulo=' + modulo + '&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const b = response.borrador;
                        if (modulo === 'formulacion') {
                            $('#formulacion_id').val(b.id);
                            $('#tituloFormulacion').text(b.nombre_borrador);
                            $('#formulacion_nombre').val(b.nombre_borrador);
                            $('#formulacion_anio').val(b.anio);
                            $('#formulacion_linea').val(b.linea_estrategica);
                            $('#formulacion_objetivo').val(b.objetivo);
                            $('#formulacion_estrategia').val(b.estrategia);
                            $('#formulacion_motor').val(b.motor_desarrollo);
                            $('#formulacion_meta').val(b.meta_resultado);
                            $('#formulacion_proyecto').val(b.proyecto);
                            $('#formulacion_ponderacion_proyectos').val(b.ponderacion_proyectos);
                            $('#formulacion_actividad').val(b.actividad_proyecto);
                            $('#formulacion_ponderacion_actividades').val(b.ponderacion_actividades);
                            $('#formulacion_responsable').val(b.responsable);
                            $('#modalFormulacion').modal('show');
                        } else {
                            $('#seguimiento_id').val(b.id);
                            $('#tituloSeguimiento').text(b.nombre_borrador);
                            $('#seguimiento_nombre').val(b.nombre_borrador);
                            $('#seguimiento_indicador').val(b.indicador);
                            $('#seguimiento_fecha').val(b.fecha_seguimiento);
                            $('#seguimiento_meta_programada').val(b.meta_programada);
                            $('#seguimiento_meta_ejecutada').val(b.meta_ejecutada);
                            $('#seguimiento_porcentaje').val(b.porcentaje_avance);
                            $('#seguimiento_responsable').val(b.responsable_seguimiento);
                            $('#seguimiento_observaciones').val(b.observaciones);
                            $('#modalSeguimiento').modal('show');
                        }
                    }
                }
            });
        }

        function verBorrador(modulo, id) {
            $.ajax({
                url: basePath + '/modulo144/getBorrador?modulo=' + modulo + '&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const b = response.borrador;
                        $('#ver_titulo').text(b.nombre_borrador);
                        let html = '<div class="row">';
                        <?php foreach ($datos_modulos as $key => $modulo_config): ?>
                        if (modulo === '<?php echo $key; ?>') {
                            <?php foreach ($modulo_config['config']['campos_vista'] as $label => $campo): ?>
                            html += '<div class="col-md-6 mb-3"><strong><?php echo $label; ?>:</strong><br>' + (b.<?php echo $campo; ?> || 'No especificado') + '</div>';
                            <?php endforeach; ?>
                        }
                        <?php endforeach; ?>
                        html += '</div>';
                        $('#ver_contenido').html(html);
                        $('#modalVerFormulario').modal('show');
                    }
                }
            });
        }

        $('#formFormulacion, #formSeguimiento').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: basePath + '/modulo144/guardar',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Guardado!', response.message, 'success');
                        $('.modal').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    }
                }
            });
        });

        function cambiarEstadoBorrador(modulo, id, estado) {
            Swal.fire({
                title: estado === 2 ? '¿Publicar borrador?' : '¿Cancelar borrador?',
                text: estado === 2 ? 'Este borrador pasará a estado PUBLICADO' : 'Este borrador pasará a estado CANCELADO',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: estado === 2 ? '#27AE60' : '#E74C3C',
                confirmButtonText: 'Sí, continuar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/modulo144/cambiarEstado',
                        type: 'POST',
                        data: { modulo: modulo, id: id, estado: estado },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('¡Completado!', response.message, 'success');
                                setTimeout(() => location.reload(), 1500);
                            }
                        }
                    });
                }
            });
        }

        function eliminarBorrador(modulo, id) {
            Swal.fire({
                title: '¿Eliminar borrador?',
                text: 'Esta acción NO se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E74C3C',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/modulo144/eliminar',
                        type: 'POST',
                        data: { modulo: modulo, id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('¡Eliminado!', response.message, 'success');
                                setTimeout(() => location.reload(), 1500);
                            }
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>