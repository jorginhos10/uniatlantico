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
            --color-tab-incomplete: #6c757d;
            --color-tab-complete: #27AE60;
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
        
        .modal-header .modal-title {
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .modal-header .modal-title:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .modal-header .modal-title-input {
            background: transparent;
            border: 2px solid white;
            color: white;
            font-size: 1.25rem;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 5px;
            width: 100%;
        }
        
        .modal-header .modal-title-input:focus {
            outline: none;
            background-color: rgba(255, 255, 255, 0.1);
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
        
        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }
        
        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            margin-right: 10px;
            cursor: pointer;
        }
        
        .form-check-input:checked {
            background-color: var(--color-success);
            border-color: var(--color-success);
        }
        
        .form-check-label {
            font-weight: 500;
            color: var(--color-primary);
            cursor: pointer;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 8px;
        }
        
        .auto-save-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--color-success);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            display: none;
            z-index: 9999;
            animation: fadeInOut 2s ease;
        }
        
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(20px); }
            15% { opacity: 1; transform: translateY(0); }
            85% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
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

        /* Estilos para pestañas */
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            border-bottom-color: var(--color-primary-light);
            background-color: rgba(44, 62, 80, 0.05);
        }
        
        .nav-tabs .nav-link.active {
            border-bottom-color: var(--color-primary);
            background-color: transparent;
            color: var(--color-primary) !important;
        }
        
        /* Estilos para el estado de las pestañas */
        .nav-tabs .nav-link.tab-incomplete {
            color: var(--color-tab-incomplete) !important;
        }
        
        .nav-tabs .nav-link.tab-complete {
            color: var(--color-tab-complete) !important;
        }
        
        .nav-tabs .nav-link.tab-complete i {
            color: var(--color-tab-complete);
        }

        /* Estilos para campos de indicador */
        .indicador-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .indicador-title {
            color: var(--color-primary);
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--color-primary-light);
        }

        /* Estilos para tabla de metas */
        .meta-section {
            background-color: #f0f8ff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid var(--color-primary);
        }
        
        .meta-title {
            color: var(--color-primary);
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1.2rem;
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
                            <?php if ($key === 'formulacion'): ?>
                            <button class="btn btn-success" onclick="abrirModalNuevoBorrador('<?php echo $key; ?>')">
                                <i class="fas fa-plus me-1"></i>Nuevo Borrador
                            </button>
                            <?php endif; ?>
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
                                                    <?php if ($key === 'formulacion'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="cambiarEstadoBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 2)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="cambiarEstadoBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 1)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button class="btn btn-sm btn-success" onclick="cambiarEstadoBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 2)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="cambiarEstadoBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 1)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <?php endif; ?>
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
                                    <p class="text-muted small"><?php echo $key === 'formulacion' ? 'Haz clic en "Nuevo Borrador" para comenzar' : 'Los borradores de formulación aparecerán aquí'; ?></p>
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

    <!-- Indicador de auto-guardado -->
    <div class="auto-save-indicator" id="autoSaveIndicator">
        <i class="fas fa-check-circle me-2"></i> Guardado automático
    </div>

    <!-- MODAL PARA NUEVO BORRADOR -->
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

    <!-- MODAL PARA DUPLICAR BORRADOR -->
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

    <!-- MODAL PARA FORMULACIÓN CON PESTAÑAS -->
    <div class="modal fade" id="modalFormulacion" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #2C3E50 0%, #34495E 100%);">
                    <h5 class="modal-title" id="tituloFormulacion" ondblclick="editarTituloModal('formulacion')">
                        <i class="fas fa-clipboard-list me-2"></i>FORMULACIÓN 144 - <span id="tituloFormulacionSpan"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formFormulacion">
                    <input type="hidden" name="modulo" value="formulacion">
                    <input type="hidden" id="formulacion_id" name="id">
                    
                    <!-- PESTAÑAS (SOLO 3: FORMULACIÓN, INDICADOR DE RESULTADO, PLANES INSTITUCIONALES) -->
                    <ul class="nav nav-tabs px-3 pt-3" id="formulacionTabs" role="tablist" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active tab-incomplete" id="tab-formulacion" data-bs-toggle="tab" data-bs-target="#formulacion" type="button" role="tab" aria-controls="formulacion" aria-selected="true" style="font-weight: 600;">
                                <i class="fas fa-clipboard-list me-2"></i>FORMULACIÓN
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link tab-incomplete" id="tab-indicador" data-bs-toggle="tab" data-bs-target="#indicador" type="button" role="tab" aria-controls="indicador" aria-selected="false" style="font-weight: 600;">
                                <i class="fas fa-chart-line me-2"></i>INDICADOR DE RESULTADO
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link tab-incomplete" id="tab-planes" data-bs-toggle="tab" data-bs-target="#planes" type="button" role="tab" aria-controls="planes" aria-selected="false" style="font-weight: 600;">
                                <i class="fas fa-file-alt me-2"></i>PLANES INSTITUCIONALES DECRETO 612 DE 2018
                            </button>
                        </li>
                    </ul>
                    
                    <!-- CONTENIDO DE LAS PESTAÑAS -->
                    <div class="tab-content" style="max-height: 60vh; overflow-y: auto; padding: 20px;">
                        <!-- PESTAÑA 1: FORMULACIÓN (campos 1-13) -->
                        <div class="tab-pane fade show active" id="formulacion" role="tabpanel" aria-labelledby="tab-formulacion">
                            <div class="row">
                                <!-- 1. AÑO -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">1. AÑO</label>
                                    <select class="form-select" name="anio" id="formulacion_anio" onchange="autoGuardarFormulacion(); validarPestanas()">
                                        <option value="">Seleccione año</option>
                                        <?php for ($i = date('Y'); $i <= date('Y') + 5; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <!-- 2. LÍNEA ESTRATÉGICA -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">2. LÍNEA ESTRATÉGICA</label>
                                    <select class="form-select" name="linea_estrategica" id="formulacion_linea" onchange="cargarObjetivoYestrategias(); cargarMotoresPorLinea(); validarPestanas()">
                                        <option value="">Seleccione línea estratégica</option>
                                        <?php foreach ($lineas_estrategicas as $linea): ?>
                                        <option value="<?php echo htmlspecialchars($linea['nombre']); ?>" 
                                                data-id="<?php echo $linea['id']; ?>" 
                                                data-objetivo="<?php echo htmlspecialchars($linea['objetivo']); ?>">
                                            <?php echo htmlspecialchars($linea['codigo'] . ' - ' . $linea['nombre']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- 3. OBJETIVO -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">3. OBJETIVO</label>
                                    <textarea class="form-control" name="objetivo" id="formulacion_objetivo" rows="3" readonly></textarea>
                                </div>

                                <!-- 4. ESTRATEGIA -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">4. ESTRATEGIA</label>
                                    <select class="form-select" name="estrategia" id="formulacion_estrategia" onchange="autoGuardarFormulacion(); validarPestanas()">
                                        <option value="">Seleccione una estrategia</option>
                                    </select>
                                </div>

                                <!-- 5. MOTOR DE DESARROLLO -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">5. MOTOR DE DESARROLLO</label>
                                    <select class="form-select" name="motor_desarrollo" id="formulacion_motor" onchange="cargarProyectosPorMotor(); autoGuardarFormulacion(); validarPestanas()">
                                        <option value="">Seleccione un motor de desarrollo</option>
                                    </select>
                                </div>

                                <!-- 6. PROYECTO -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">6. PROYECTO</label>
                                    <select class="form-select" name="proyecto" id="formulacion_proyecto" onchange="autoGuardarFormulacion(); validarPestanas()">
                                        <option value="">Seleccione un proyecto</option>
                                    </select>
                                </div>

                                <!-- 7. META DE RESULTADO -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">7. META DE RESULTADO</label>
                                    <textarea class="form-control" name="meta_resultado" id="formulacion_meta" rows="2" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa la meta de resultado..."></textarea>
                                </div>

                                <!-- 8. PONDERACIÓN DE LOS PROYECTOS -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">8. PONDERACIÓN DE LOS PROYECTOS</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="ponderacion_proyectos" id="formulacion_ponderacion_proyectos" step="0.01" min="0" max="100" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="0.00">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <!-- 9. ACTIVIDAD DEL PROYECTO (205) -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">9. ACTIVIDAD DEL PROYECTO (205)</label>
                                    <textarea class="form-control" name="actividad_proyecto" id="formulacion_actividad" rows="4" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa la actividad del proyecto..."></textarea>
                                </div>

                                <!-- 10. PONDERACIÓN DE LAS ACTIVIDADES POR PROYECTO -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">10. PONDERACIÓN DE LAS ACTIVIDADES POR PROYECTO</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="ponderacion_actividades" id="formulacion_ponderacion_actividades" step="0.01" min="0" max="100" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="0.00">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <!-- 11. RESPONSABLE (select de cargos) -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">11. RESPONSABLE</label>
                                    <select class="form-select" name="responsable_formulacion" id="formulacion_responsable" onchange="autoGuardarFormulacion(); validarPestanas()">
                                        <option value="">Seleccione un cargo</option>
                                        <?php foreach ($cargos as $cargo): ?>
                                        <option value="<?php echo htmlspecialchars($cargo['nombre']); ?>">
                                            <?php echo htmlspecialchars($cargo['nombre']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- 12. ID INDICADOR -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">12. ID INDICADOR</label>
                                    <input type="text" class="form-control" name="id_indicador" id="formulacion_id_indicador" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Ingrese el ID del indicador">
                                </div>

                                <!-- 13. GESTIONADO EN FACULTADES -->
                                <div class="col-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="gestionado_facultades" id="formulacion_gestionado_facultades" value="1" onchange="autoGuardarFormulacion(); validarPestanas()">
                                        <label class="form-check-label" for="formulacion_gestionado_facultades">
                                            <strong>13. MARQUE: ✓ SI EL INDICADOR SERÁ GESTIONADO DESDE LAS FACULTADES</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- PESTAÑA 2: INDICADOR DE RESULTADO (con METAS PROPUESTAS al final) -->
                        <div class="tab-pane fade" id="indicador" role="tabpanel" aria-labelledby="tab-indicador">
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <h5 class="indicador-title">INFORMACIÓN DEL INDICADOR</h5>
                                </div>
                                
                                <!-- 16.1 NOMBRE DEL INDICADOR -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">16.1 NOMBRE DEL INDICADOR</label>
                                    <input type="text" class="form-control" name="nombre_indicador" id="formulacion_nombre_indicador" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Ingrese el nombre del indicador">
                                </div>
                                
                                <!-- 16.2 FÓRMULA DE LA MEDICIÓN -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">16.2 FÓRMULA DE LA MEDICIÓN</label>
                                    <textarea class="form-control" name="formula_medicion" id="formulacion_formula_medicion" rows="3" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Ej: (Número de estudiantes graduados / Total de estudiantes matriculados) * 100"></textarea>
                                </div>
                                
                                <div class="row">
                                    <!-- 16.3 FRECUENCIA DE MEDICIÓN -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">16.3 FRECUENCIA DE MEDICIÓN</label>
                                        <select class="form-select" name="frecuencia_medicion" id="formulacion_frecuencia_medicion" onchange="autoGuardarFormulacion(); validarPestanas()">
                                            <option value="">Seleccione frecuencia</option>
                                            <option value="Mensual">Mensual</option>
                                            <option value="Bimestral">Bimestral</option>
                                            <option value="Trimestral">Trimestral</option>
                                            <option value="Semestral">Semestral</option>
                                            <option value="Anual">Anual</option>
                                        </select>
                                    </div>
                                    
                                    <!-- 16.4 UNIDAD DE MEDIDA - AHORA ES SELECT -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">16.4 UNIDAD DE MEDIDA</label>
                                        <select class="form-select" name="unidad_medida" id="formulacion_unidad_medida" onchange="autoGuardarFormulacion(); validarPestanas()">
                                            <option value="">Seleccione unidad</option>
                                            <option value="Unidad">Unidad</option>
                                            <option value="Porcentaje">Porcentaje</option>
                                        </select>
                                    </div>
                                    
                                    <!-- 16.5 TIPO DE MEDICIÓN -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">16.5 TIPO DE MEDICIÓN</label>
                                        <select class="form-select" name="tipo_medicion" id="formulacion_tipo_medicion" onchange="autoGuardarFormulacion(); validarPestanas()">
                                            <option value="">Seleccione tipo</option>
                                            <option value="Cuantitativo">Cuantitativo</option>
                                            <option value="Cualitativo">Cualitativo</option>
                                            <option value="Mixto">Mixto</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- DESCRIPCIÓN DEL INDICADOR -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">DESCRIPCIÓN DEL INDICADOR</label>
                                    <textarea class="form-control" name="descripcion_indicador" id="formulacion_descripcion_indicador" rows="4" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa detalladamente el indicador, su propósito y qué mide..."></textarea>
                                </div>
                                
                                <!-- SECCIÓN DE METAS PROPUESTAS (ahora aquí) -->
                                <div class="col-12 mt-4">
                                    <div class="meta-section">
                                        <h5 class="meta-title">METAS PROPUESTAS</h5>
                                        
                                        <div class="row">
                                            <!-- 17.1 LÍNEA BASE -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">17.1 LÍNEA BASE</label>
                                                <input type="text" class="form-control" name="linea_base_meta" id="formulacion_linea_base_meta" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Valor de línea base">
                                            </div>
                                            
                                            <!-- 17.4 AÑO -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">17.4 AÑO</label>
                                                <select class="form-select" name="anio_base_meta" id="formulacion_anio_base_meta" onchange="autoGuardarFormulacion(); validarPestanas()">
                                                    <option value="">Seleccione año</option>
                                                    <?php for ($i = date('Y') - 2; $i <= date('Y') + 5; $i++): ?>
                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="col-12">
                                                <h6 class="mb-3" style="color: var(--color-primary);">METAS ANUALES</h6>
                                            </div>
                                            
                                            <!-- 17.2 SEMESTRE 1 -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">17.2 SEMESTRE 1</label>
                                                <input type="text" class="form-control" name="meta_s1" id="formulacion_meta_s1" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Meta Semestre 1">
                                            </div>
                                            
                                            <!-- 17.3 SEMESTRE 2 -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">17.3 SEMESTRE 2</label>
                                                <input type="text" class="form-control" name="meta_s2" id="formulacion_meta_s2" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Meta Semestre 2">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- PESTAÑA 3: PLANES INSTITUCIONALES DECRETO 612 DE 2018 -->
                        <div class="tab-pane fade" id="planes" role="tabpanel" aria-labelledby="tab-planes">
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <h5 class="indicador-title">PLANES INSTITUCIONALES</h5>
                                </div>
                                
                                <!-- Selector con buscador (columna izquierda) -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SELECCIONAR PLAN</label>
                                    <select class="form-select" id="selectPlanInstitucional" style="width: 100%;">
                                        <option value="">-- Buscar y seleccionar plan --</option>
                                        <?php foreach ($planes_institucionales as $plan): ?>
                                        <option value="<?php echo htmlspecialchars($plan['nombre']); ?>">
                                            <?php echo htmlspecialchars($plan['nombre']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-primary mt-2" onclick="agregarPlan()">
                                        <i class="fas fa-plus me-2"></i>Agregar Plan
                                    </button>
                                </div>
                                
                                <!-- Contenedor de planes seleccionados (columna derecha) -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">PLANES SELECCIONADOS</label>
                                    <div id="contenedorPlanes" style="max-height: 300px; overflow-y: auto; border: 1px solid #ced4da; border-radius: 8px; padding: 10px; background-color: #f8f9fa;">
                                        <!-- Los planes seleccionados se cargarán aquí dinámicamente -->
                                        <p class="text-muted text-center mb-0" id="mensajeVacioPlanes">No hay planes seleccionados</p>
                                    </div>
                                </div>
                                
                                <!-- Campo oculto para guardar los planes en la base de datos -->
                                <input type="hidden" name="planes_institucionales" id="formulacion_planes_institucionales" value="">
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA SEGUIMIENTO -->
    <div class="modal fade" id="modalSeguimiento" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);">
                    <h5 class="modal-title" id="tituloSeguimiento" ondblclick="editarTituloModal('seguimiento')">
                        <i class="fas fa-chart-line me-2"></i>SEGUIMIENTO 144 - <span id="tituloSeguimientoSpan"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSeguimiento">
                    <input type="hidden" name="modulo" value="seguimiento">
                    <input type="hidden" id="seguimiento_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">INDICADOR</label>
                                <input type="text" class="form-control" name="indicador" id="seguimiento_indicador" placeholder="Ej: % de cumplimiento" oninput="autoGuardarSeguimiento()">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">FECHA DE SEGUIMIENTO</label>
                                <input type="date" class="form-control" name="fecha_seguimiento" id="seguimiento_fecha" onchange="autoGuardarSeguimiento()">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">META PROGRAMADA</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="meta_programada" id="seguimiento_meta_programada" step="0.01" oninput="autoGuardarSeguimiento()">
                                    <span class="input-group-text">$</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">META EJECUTADA</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="meta_ejecutada" id="seguimiento_meta_ejecutada" step="0.01" oninput="autoGuardarSeguimiento()">
                                    <span class="input-group-text">$</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">% AVANCE</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="porcentaje_avance" id="seguimiento_porcentaje" step="0.01" min="0" max="100" oninput="autoGuardarSeguimiento()">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RESPONSABLE</label>
                                <input type="text" class="form-control" name="responsable_seguimiento" id="seguimiento_responsable" oninput="autoGuardarSeguimiento()">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">OBSERVACIONES</label>
                                <textarea class="form-control" name="observaciones" id="seguimiento_observaciones" rows="4" oninput="autoGuardarSeguimiento()"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA VER FORMULARIO -->
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
        
        // Variables para auto-guardado
        let timeoutId = null;
        let currentModule = null;
        
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

        // Variable para controlar la edición del título
        let editandoTitulo = false;

        // Array para almacenar los planes seleccionados
        let planesSeleccionados = [];

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

        // Función para validar el estado de las pestañas
        function validarPestanas() {
            // Validar Pestaña 1: FORMULACIÓN
            const camposFormulacion = [
                $('#formulacion_anio').val(),
                $('#formulacion_linea').val(),
                $('#formulacion_estrategia').val(),
                $('#formulacion_motor').val(),
                $('#formulacion_proyecto').val(),
                $('#formulacion_meta').val(),
                $('#formulacion_ponderacion_proyectos').val(),
                $('#formulacion_actividad').val(),
                $('#formulacion_ponderacion_actividades').val(),
                $('#formulacion_responsable').val(),
                $('#formulacion_id_indicador').val()
            ];
            
            // El checkbox no es obligatorio
            const formulacionCompleta = camposFormulacion.every(valor => valor && valor.trim() !== '');
            
            // Validar Pestaña 2: INDICADOR DE RESULTADO (incluyendo METAS PROPUESTAS)
            const camposIndicador = [
                $('#formulacion_nombre_indicador').val(),
                $('#formulacion_formula_medicion').val(),
                $('#formulacion_frecuencia_medicion').val(),
                $('#formulacion_unidad_medida').val(),
                $('#formulacion_tipo_medicion').val(),
                $('#formulacion_descripcion_indicador').val(),
                $('#formulacion_linea_base_meta').val(),
                $('#formulacion_anio_base_meta').val(),
                $('#formulacion_meta_s1').val(),
                $('#formulacion_meta_s2').val()
            ];
            
            const indicadorCompleto = camposIndicador.every(valor => valor && valor.trim() !== '');
            
            // Pestaña 3 es opcional
            const planesCompleto = true;
            
            // Actualizar clases de las pestañas
            actualizarClasePestana('#tab-formulacion', formulacionCompleta);
            actualizarClasePestana('#tab-indicador', indicadorCompleto);
            actualizarClasePestana('#tab-planes', planesCompleto);
        }
        
        function actualizarClasePestana(selector, completo) {
            const pestana = $(selector);
            if (completo) {
                pestana.removeClass('tab-incomplete').addClass('tab-complete');
            } else {
                pestana.removeClass('tab-complete').addClass('tab-incomplete');
            }
        }

        // Función para cargar estrategias
        function cargarObjetivoYestrategias() {
            const selectLinea = document.getElementById('formulacion_linea');
            const selectedOption = selectLinea.options[selectLinea.selectedIndex];
            
            if (!selectedOption || !selectedOption.value) {
                document.getElementById('formulacion_objetivo').value = '';
                document.getElementById('formulacion_estrategia').innerHTML = '<option value="">Seleccione una estrategia</option>';
                validarPestanas();
                return;
            }
            
            // Cargar objetivo
            const objetivo = selectedOption.getAttribute('data-objetivo') || '';
            document.getElementById('formulacion_objetivo').value = objetivo;
            
            // Cargar estrategias
            const lineaId = selectedOption.getAttribute('data-id');
            
            if (lineaId) {
                document.getElementById('formulacion_estrategia').innerHTML = '<option value="">Cargando estrategias...</option>';
                
                $.ajax({
                    url: basePath + '/modulo144/getEstrategiasPorLinea',
                    type: 'GET',
                    data: { linea_id: lineaId },
                    dataType: 'json',
                    success: function(response) {
                        const selectEstrategia = document.getElementById('formulacion_estrategia');
                        selectEstrategia.innerHTML = '<option value="">Seleccione una estrategia</option>';
                        
                        if (response.success && response.estrategias && response.estrategias.length > 0) {
                            response.estrategias.forEach(function(estrategia) {
                                const option = document.createElement('option');
                                option.value = estrategia.descripcion;
                                option.textContent = estrategia.descripcion;
                                selectEstrategia.appendChild(option);
                            });
                        } else {
                            selectEstrategia.innerHTML = '<option value="">No hay estrategias disponibles</option>';
                        }
                        
                        autoGuardarFormulacion();
                        validarPestanas();
                    },
                    error: function() {
                        document.getElementById('formulacion_estrategia').innerHTML = '<option value="">Error al cargar estrategias</option>';
                        validarPestanas();
                    }
                });
            }
        }

        // Función para cargar motores
        function cargarMotoresPorLinea() {
            const selectLinea = document.getElementById('formulacion_linea');
            const selectedOption = selectLinea.options[selectLinea.selectedIndex];
            
            if (!selectedOption || !selectedOption.value) {
                document.getElementById('formulacion_motor').innerHTML = '<option value="">Seleccione un motor de desarrollo</option>';
                document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Primero seleccione un motor</option>';
                validarPestanas();
                return;
            }
            
            const lineaId = selectedOption.getAttribute('data-id');
            
            if (lineaId) {
                document.getElementById('formulacion_motor').innerHTML = '<option value="">Cargando motores...</option>';
                document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Seleccione un motor primero</option>';
                
                $.ajax({
                    url: basePath + '/modulo144/getMotoresPorLinea',
                    type: 'GET',
                    data: { linea_id: lineaId },
                    dataType: 'json',
                    success: function(response) {
                        const selectMotor = document.getElementById('formulacion_motor');
                        selectMotor.innerHTML = '<option value="">Seleccione un motor de desarrollo</option>';
                        
                        if (response.success && response.motores && response.motores.length > 0) {
                            response.motores.forEach(function(motor) {
                                const option = document.createElement('option');
                                option.value = motor.nombre;
                                option.setAttribute('data-motor-id', motor.id);
                                option.textContent = motor.nombre;
                                selectMotor.appendChild(option);
                            });
                        } else {
                            selectMotor.innerHTML = '<option value="">No hay motores disponibles</option>';
                        }
                        
                        autoGuardarFormulacion();
                        validarPestanas();
                    },
                    error: function() {
                        document.getElementById('formulacion_motor').innerHTML = '<option value="">Error al cargar motores</option>';
                        validarPestanas();
                    }
                });
            }
        }

        // Función para cargar proyectos según el motor seleccionado
        function cargarProyectosPorMotor() {
            const selectLinea = document.getElementById('formulacion_linea');
            const selectMotor = document.getElementById('formulacion_motor');
            
            const lineaOption = selectLinea.options[selectLinea.selectedIndex];
            const motorOption = selectMotor.options[selectMotor.selectedIndex];
            
            if (!lineaOption || !lineaOption.value || !motorOption || !motorOption.value) {
                document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Seleccione un motor primero</option>';
                validarPestanas();
                return;
            }
            
            const lineaId = lineaOption.getAttribute('data-id');
            const motorId = motorOption.getAttribute('data-motor-id');
            
            if (lineaId && motorId) {
                document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Cargando proyectos...</option>';
                
                $.ajax({
                    url: basePath + '/modulo144/getProyectosPorLineaYMotor',
                    type: 'GET',
                    data: { 
                        linea_id: lineaId,
                        motor_id: motorId
                    },
                    dataType: 'json',
                    success: function(response) {
                        const selectProyecto = document.getElementById('formulacion_proyecto');
                        selectProyecto.innerHTML = '<option value="">Seleccione un proyecto</option>';
                        
                        if (response.success && response.proyectos && response.proyectos.length > 0) {
                            response.proyectos.forEach(function(proyecto) {
                                const option = document.createElement('option');
                                option.value = proyecto.nombre;
                                option.textContent = proyecto.codigo + ' - ' + proyecto.nombre;
                                selectProyecto.appendChild(option);
                            });
                        } else {
                            selectProyecto.innerHTML = '<option value="">No hay proyectos disponibles</option>';
                        }
                        
                        autoGuardarFormulacion();
                        validarPestanas();
                    },
                    error: function() {
                        document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Error al cargar proyectos</option>';
                        validarPestanas();
                    }
                });
            }
        }

        // Funciones para manejar planes institucionales
        function agregarPlan() {
            const select = document.getElementById('selectPlanInstitucional');
            const planSeleccionado = select.value;
            
            if (!planSeleccionado) {
                Swal.fire('Atención', 'Por favor seleccione un plan', 'warning');
                return;
            }
            
            // Verificar si ya fue seleccionado
            if (planesSeleccionados.includes(planSeleccionado)) {
                Swal.fire('Atención', 'Este plan ya ha sido agregado', 'info');
                return;
            }
            
            // Agregar al array
            planesSeleccionados.push(planSeleccionado);
            
            // Actualizar la vista
            actualizarContenedorPlanes();
            
            // Limpiar el select
            select.value = '';
            
            // Actualizar campo oculto y guardar
            actualizarCampoOculto();
            
            // Forzar guardado inmediato
            guardarPlanesInmediatamente();
        }

        function eliminarPlan(plan) {
            // Filtrar el array para quitar el plan
            planesSeleccionados = planesSeleccionados.filter(p => p !== plan);
            
            // Actualizar la vista
            actualizarContenedorPlanes();
            
            // Actualizar campo oculto y guardar
            actualizarCampoOculto();
            
            // Forzar guardado inmediato
            guardarPlanesInmediatamente();
        }

        function guardarPlanesInmediatamente() {
            const id = $('#formulacion_id').val();
            if (!id) return;
            
            const data = {
                modulo: 'formulacion',
                id: id,
                planes_institucionales: $('#formulacion_planes_institucionales').val()
            };
            
            $.ajax({
                url: basePath + '/modulo144/guardar',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        console.log('Planes guardados correctamente');
                        mostrarAutoSaveIndicator();
                    }
                }
            });
        }

        function actualizarContenedorPlanes() {
            const contenedor = document.getElementById('contenedorPlanes');
            
            if (planesSeleccionados.length === 0) {
                contenedor.innerHTML = '<p class="text-muted text-center mb-0" id="mensajeVacioPlanes">No hay planes seleccionados</p>';
                return;
            }
            
            let html = '';
            planesSeleccionados.forEach((plan, index) => {
                // Asignar colores según el índice
                let colorClass = '';
                let colorText = '';
                
                if (index % 3 === 0) {
                    colorClass = 'bg-primary';
                    colorText = 'Primary';
                } else if (index % 3 === 1) {
                    colorClass = 'bg-success';
                    colorText = 'Primary';
                } else {
                    colorClass = 'bg-info';
                    colorText = 'Primary';
                }
                
                // Escapar comillas para el onclick
                const planEscaped = plan.replace(/'/g, "\\'");
                
                html += `
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 ${colorClass} text-white rounded" style="opacity: 0.9;">
                        <span><strong>${colorText} ${index + 1}:</strong> ${plan}</span>
                        <button type="button" class="btn btn-sm btn-light" onclick="eliminarPlan('${planEscaped}')">
                            <i class="fas fa-times text-danger"></i>
                        </button>
                    </div>
                `;
            });
            
            contenedor.innerHTML = html;
        }

        function actualizarCampoOculto() {
            const valorJSON = JSON.stringify(planesSeleccionados);
            document.getElementById('formulacion_planes_institucionales').value = valorJSON;
            console.log('Campo oculto actualizado:', valorJSON);
        }

        function cargarPlanesDesdeBD(planesJSON) {
            console.log('Cargando planes desde BD:', planesJSON);
            if (planesJSON && planesJSON !== '[]' && planesJSON !== '') {
                try {
                    planesSeleccionados = JSON.parse(planesJSON);
                } catch (e) {
                    console.error('Error al parsear planes:', e);
                    planesSeleccionados = [];
                }
            } else {
                planesSeleccionados = [];
            }
            actualizarContenedorPlanes();
            actualizarCampoOculto();
        }

        function editarTituloModal(modulo) {
            if (editandoTitulo) return;
            
            const modalHeader = modulo === 'formulacion' ? 
                $('#modalFormulacion .modal-header h5') : 
                $('#modalSeguimiento .modal-header h5');
            
            const tituloActual = modulo === 'formulacion' ? 
                $('#tituloFormulacionSpan').text() : 
                $('#tituloSeguimientoSpan').text();
            
            const inputHtml = `<input type="text" class="modal-title-input" id="editTituloInput" value="${tituloActual}" />`;
            modalHeader.html(inputHtml);
            
            $('#editTituloInput').focus();
            editandoTitulo = true;
            
            $('#editTituloInput').on('blur', function() {
                guardarTituloModal(modulo, $(this).val());
            }).on('keypress', function(e) {
                if (e.which === 13) {
                    guardarTituloModal(modulo, $(this).val());
                }
            });
        }

        function guardarTituloModal(modulo, nuevoTitulo) {
            if (!nuevoTitulo.trim()) {
                restaurarTituloModal(modulo);
                return;
            }

            const id = modulo === 'formulacion' ? $('#formulacion_id').val() : $('#seguimiento_id').val();
            
            $.ajax({
                url: basePath + '/modulo144/guardar',
                type: 'POST',
                data: {
                    modulo: modulo,
                    id: id,
                    nombre_borrador: nuevoTitulo
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (modulo === 'formulacion') {
                            $('#tituloFormulacionSpan').text(nuevoTitulo);
                            $('#modalFormulacion .modal-header h5').html(`<i class="fas fa-clipboard-list me-2"></i>FORMULACIÓN 144 - <span id="tituloFormulacionSpan">${nuevoTitulo}</span>`);
                        } else {
                            $('#tituloSeguimientoSpan').text(nuevoTitulo);
                            $('#modalSeguimiento .modal-header h5').html(`<i class="fas fa-chart-line me-2"></i>SEGUIMIENTO 144 - <span id="tituloSeguimientoSpan">${nuevoTitulo}</span>`);
                        }
                        editandoTitulo = false;
                        
                        // Mostrar indicador de guardado
                        mostrarAutoSaveIndicator();
                    } else {
                        restaurarTituloModal(modulo);
                    }
                },
                error: function() {
                    restaurarTituloModal(modulo);
                }
            });
        }

        function restaurarTituloModal(modulo) {
            const tituloActual = modulo === 'formulacion' ? 
                $('#tituloFormulacionSpan').text() : 
                $('#tituloSeguimientoSpan').text();
            
            if (modulo === 'formulacion') {
                $('#modalFormulacion .modal-header h5').html(`<i class="fas fa-clipboard-list me-2"></i>FORMULACIÓN 144 - <span id="tituloFormulacionSpan">${tituloActual}</span>`);
            } else {
                $('#modalSeguimiento .modal-header h5').html(`<i class="fas fa-chart-line me-2"></i>SEGUIMIENTO 144 - <span id="tituloSeguimientoSpan">${tituloActual}</span>`);
            }
            editandoTitulo = false;
        }

        function autoGuardarFormulacion() {
            const id = $('#formulacion_id').val();
            if (!id) return;
            
            currentModule = 'formulacion';
            
            // Limpiar timeout anterior
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            
            // Programar nuevo guardado
            timeoutId = setTimeout(function() {
                // Para el checkbox, enviar 1 si está marcado, 0 si no
                const gestionado = $('#formulacion_gestionado_facultades').is(':checked') ? 1 : 0;
                
                const data = {
                    modulo: 'formulacion',
                    id: id,
                    anio: $('#formulacion_anio').val(),
                    linea_estrategica: $('#formulacion_linea').val(),
                    objetivo: $('#formulacion_objetivo').val(),
                    estrategia: $('#formulacion_estrategia').val(),
                    motor_desarrollo: $('#formulacion_motor').val(),
                    proyecto: $('#formulacion_proyecto').val(),
                    meta_resultado: $('#formulacion_meta').val(),
                    ponderacion_proyectos: $('#formulacion_ponderacion_proyectos').val(),
                    actividad_proyecto: $('#formulacion_actividad').val(),
                    ponderacion_actividades: $('#formulacion_ponderacion_actividades').val(),
                    responsable_formulacion: $('#formulacion_responsable').val(),
                    id_indicador: $('#formulacion_id_indicador').val(),
                    gestionado_facultades: gestionado,
                    
                    // Campos de la pestaña INDICADOR DE RESULTADO
                    nombre_indicador: $('#formulacion_nombre_indicador').val(),
                    formula_medicion: $('#formulacion_formula_medicion').val(),
                    frecuencia_medicion: $('#formulacion_frecuencia_medicion').val(),
                    unidad_medida: $('#formulacion_unidad_medida').val(),
                    tipo_medicion: $('#formulacion_tipo_medicion').val(),
                    descripcion_indicador: $('#formulacion_descripcion_indicador').val(),
                    
                    // Campos de METAS PROPUESTAS (ahora en la misma pestaña)
                    linea_base_meta: $('#formulacion_linea_base_meta').val(),
                    anio_base_meta: $('#formulacion_anio_base_meta').val(),
                    meta_s1: $('#formulacion_meta_s1').val(),
                    meta_s2: $('#formulacion_meta_s2').val(),
                    
                    // Campos de la pestaña PLANES INSTITUCIONALES
                    planes_institucionales: $('#formulacion_planes_institucionales').val()
                };
                
                console.log('Enviando datos:', data); // Para depuración
                
                $.ajax({
                    url: basePath + '/modulo144/guardar',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            console.log('Guardado exitoso');
                            mostrarAutoSaveIndicator();
                            validarPestanas();
                        } else {
                            console.error('Error en guardado:', response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la petición:', error);
                    }
                });
            }, 500);
        }

        function autoGuardarSeguimiento() {
            const id = $('#seguimiento_id').val();
            if (!id) return;
            
            currentModule = 'seguimiento';
            
            // Limpiar timeout anterior
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            
            // Programar nuevo guardado
            timeoutId = setTimeout(function() {
                const data = {
                    modulo: 'seguimiento',
                    id: id,
                    indicador: $('#seguimiento_indicador').val(),
                    fecha_seguimiento: $('#seguimiento_fecha').val(),
                    meta_programada: $('#seguimiento_meta_programada').val(),
                    meta_ejecutada: $('#seguimiento_meta_ejecutada').val(),
                    porcentaje_avance: $('#seguimiento_porcentaje').val(),
                    responsable_seguimiento: $('#seguimiento_responsable').val(),
                    observaciones: $('#seguimiento_observaciones').val()
                };
                
                $.ajax({
                    url: basePath + '/modulo144/guardar',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mostrarAutoSaveIndicator();
                        }
                    }
                });
            }, 500);
        }

        function mostrarAutoSaveIndicator() {
            const indicator = document.getElementById('autoSaveIndicator');
            indicator.style.display = 'block';
            indicator.style.animation = 'none';
            indicator.offsetHeight;
            indicator.style.animation = 'fadeInOut 2s ease';
            
            setTimeout(function() {
                indicator.style.display = 'none';
            }, 2000);
        }

        function editarBorrador(modulo, id) {
            $.ajax({
                url: basePath + '/modulo144/getBorrador?modulo=' + modulo + '&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const b = response.borrador;
                        console.log('Borrador cargado:', b); // Para depuración
                        
                        if (modulo === 'formulacion') {
                            $('#formulacion_id').val(b.id);
                            $('#tituloFormulacionSpan').text(b.nombre_borrador);
                            $('#formulacion_anio').val(b.anio);
                            $('#formulacion_linea').val(b.linea_estrategica);
                            $('#formulacion_objetivo').val(b.objetivo);
                            
                            // Obtener el ID de la línea seleccionada
                            const selectLinea = document.getElementById('formulacion_linea');
                            const lineaOption = selectLinea.options[selectLinea.selectedIndex];
                            const lineaId = lineaOption ? lineaOption.getAttribute('data-id') : null;
                            
                            // Función para cargar estrategias
                            function cargarEstrategias(lineaId, valorEstrategia) {
                                if (lineaId) {
                                    document.getElementById('formulacion_estrategia').innerHTML = '<option value="">Cargando estrategias...</option>';
                                    
                                    $.ajax({
                                        url: basePath + '/modulo144/getEstrategiasPorLinea',
                                        type: 'GET',
                                        data: { linea_id: lineaId },
                                        dataType: 'json',
                                        success: function(res) {
                                            const selectEstrategia = document.getElementById('formulacion_estrategia');
                                            selectEstrategia.innerHTML = '<option value="">Seleccione una estrategia</option>';
                                            
                                            if (res.success && res.estrategias && res.estrategias.length > 0) {
                                                res.estrategias.forEach(function(estrategia) {
                                                    const option = document.createElement('option');
                                                    option.value = estrategia.descripcion;
                                                    option.textContent = estrategia.descripcion;
                                                    selectEstrategia.appendChild(option);
                                                });
                                                
                                                if (valorEstrategia) {
                                                    $('#formulacion_estrategia').val(valorEstrategia);
                                                }
                                            } else {
                                                selectEstrategia.innerHTML = '<option value="">No hay estrategias disponibles</option>';
                                            }
                                            validarPestanas();
                                        }
                                    });
                                } else {
                                    document.getElementById('formulacion_estrategia').innerHTML = '<option value="">Seleccione una estrategia</option>';
                                    validarPestanas();
                                }
                            }
                            
                            // Función para cargar motores
                            function cargarMotores(lineaId, valorMotor) {
                                if (lineaId) {
                                    document.getElementById('formulacion_motor').innerHTML = '<option value="">Cargando motores...</option>';
                                    
                                    $.ajax({
                                        url: basePath + '/modulo144/getMotoresPorLinea',
                                        type: 'GET',
                                        data: { linea_id: lineaId },
                                        dataType: 'json',
                                        success: function(res) {
                                            const selectMotor = document.getElementById('formulacion_motor');
                                            selectMotor.innerHTML = '<option value="">Seleccione un motor de desarrollo</option>';
                                            
                                            if (res.success && res.motores && res.motores.length > 0) {
                                                res.motores.forEach(function(motor) {
                                                    const option = document.createElement('option');
                                                    option.value = motor.nombre;
                                                    option.setAttribute('data-motor-id', motor.id);
                                                    option.textContent = motor.nombre;
                                                    selectMotor.appendChild(option);
                                                });
                                                
                                                if (valorMotor) {
                                                    $('#formulacion_motor').val(valorMotor);
                                                    
                                                    // Después de seleccionar el motor, cargar proyectos
                                                    setTimeout(function() {
                                                        cargarProyectosPorMotorConValor(b.proyecto);
                                                    }, 300);
                                                }
                                            } else {
                                                selectMotor.innerHTML = '<option value="">No hay motores disponibles</option>';
                                            }
                                            validarPestanas();
                                        }
                                    });
                                } else {
                                    document.getElementById('formulacion_motor').innerHTML = '<option value="">Seleccione un motor de desarrollo</option>';
                                    validarPestanas();
                                }
                            }
                            
                            // Función para cargar proyectos después de seleccionar motor
                            function cargarProyectosPorMotorConValor(valorProyecto) {
                                const selectMotor = document.getElementById('formulacion_motor');
                                const motorOption = selectMotor.options[selectMotor.selectedIndex];
                                const motorId = motorOption ? motorOption.getAttribute('data-motor-id') : null;
                                
                                if (lineaId && motorId) {
                                    document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Cargando proyectos...</option>';
                                    
                                    $.ajax({
                                        url: basePath + '/modulo144/getProyectosPorLineaYMotor',
                                        type: 'GET',
                                        data: { 
                                            linea_id: lineaId,
                                            motor_id: motorId
                                        },
                                        dataType: 'json',
                                        success: function(res) {
                                            const selectProyecto = document.getElementById('formulacion_proyecto');
                                            selectProyecto.innerHTML = '<option value="">Seleccione un proyecto</option>';
                                            
                                            if (res.success && res.proyectos && res.proyectos.length > 0) {
                                                res.proyectos.forEach(function(proyecto) {
                                                    const option = document.createElement('option');
                                                    option.value = proyecto.nombre;
                                                    option.textContent = proyecto.codigo + ' - ' + proyecto.nombre;
                                                    selectProyecto.appendChild(option);
                                                });
                                                
                                                if (valorProyecto) {
                                                    $('#formulacion_proyecto').val(valorProyecto);
                                                }
                                            } else {
                                                selectProyecto.innerHTML = '<option value="">No hay proyectos disponibles</option>';
                                            }
                                            validarPestanas();
                                        }
                                    });
                                }
                            }
                            
                            // Cargar estrategias, motores y luego proyectos
                            cargarEstrategias(lineaId, b.estrategia);
                            cargarMotores(lineaId, b.motor_desarrollo);
                            
                            $('#formulacion_meta').val(b.meta_resultado);
                            $('#formulacion_ponderacion_proyectos').val(b.ponderacion_proyectos);
                            $('#formulacion_actividad').val(b.actividad_proyecto);
                            $('#formulacion_ponderacion_actividades').val(b.ponderacion_actividades);
                            $('#formulacion_responsable').val(b.responsable_formulacion);
                            $('#formulacion_id_indicador').val(b.id_indicador);
                            
                            // Checkbox: marcar si gestionado_facultades es 1
                            if (b.gestionado_facultades == 1) {
                                $('#formulacion_gestionado_facultades').prop('checked', true);
                            } else {
                                $('#formulacion_gestionado_facultades').prop('checked', false);
                            }
                            
                            // Campos de la pestaña INDICADOR DE RESULTADO
                            $('#formulacion_nombre_indicador').val(b.nombre_indicador);
                            $('#formulacion_formula_medicion').val(b.formula_medicion);
                            $('#formulacion_frecuencia_medicion').val(b.frecuencia_medicion);
                            $('#formulacion_unidad_medida').val(b.unidad_medida);
                            $('#formulacion_tipo_medicion').val(b.tipo_medicion);
                            $('#formulacion_descripcion_indicador').val(b.descripcion_indicador);
                            
                            // Campos de METAS PROPUESTAS
                            $('#formulacion_linea_base_meta').val(b.linea_base_meta);
                            $('#formulacion_anio_base_meta').val(b.anio_base_meta);
                            $('#formulacion_meta_s1').val(b.meta_s1);
                            $('#formulacion_meta_s2').val(b.meta_s2);
                            
                            // Cargar planes institucionales
                            if (b.planes_institucionales) {
                                cargarPlanesDesdeBD(b.planes_institucionales);
                            } else {
                                planesSeleccionados = [];
                                actualizarContenedorPlanes();
                                actualizarCampoOculto();
                            }
                            
                            $('#modalFormulacion').modal('show');
                            
                            // Validar pestañas después de cargar
                            setTimeout(validarPestanas, 500);
                        } else {
                            $('#seguimiento_id').val(b.id);
                            $('#tituloSeguimientoSpan').text(b.nombre_borrador);
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

        // Test automático al cargar la página
        $(document).ready(function() {
            console.log('=== SISTEMA CARGADO CORRECTAMENTE CON 3 PESTAÑAS (METAS PROPUESTAS EN INDICADOR) ===');
            
            // Inicializar validación de pestañas cuando se abre el modal
            $('#modalFormulacion').on('shown.bs.modal', function() {
                validarPestanas();
            });
        });
    </script>
</body>
</html>