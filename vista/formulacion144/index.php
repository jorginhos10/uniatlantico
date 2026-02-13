<?php
// vista/formulacion144/index.php
$basePath = Config::getBasePath();
$fecha_cierre = $formulario['fecha_cierre'] ?? null;
$fecha_inicio = $formulario['fecha_inicio'] ?? null;
$fecha_actual = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORMULACIÓN 144 - <?php echo htmlspecialchars($formulario['titulo'] ?? ''); ?></title>
    
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
        
        /* ============= CONTADOR REGRESIVO ============= */
        .countdown-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .countdown-title {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.9);
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
        
        .countdown-expired {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        
        .countdown-warning {
            background: rgba(243, 156, 18, 0.2);
            border: 1px solid rgba(243, 156, 18, 0.3);
        }
        
        .fechas-info {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .fecha-item {
            display: inline-block;
            margin-right: 20px;
        }
        
        .fecha-item i {
            margin-right: 5px;
        }
        /* ============================================ */
        
        .section-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-left: 5px solid var(--color-primary);
        }
        
        .section-title {
            color: var(--color-primary);
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .item-card {
            background: #f8f9fc;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(44,62,80,0.1);
            border-left: 4px solid var(--color-primary);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--color-success) 0%, #2ECC71 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            color: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            color: white;
        }
        
        .btn-warning {
            background: var(--color-warning);
            border: none;
            color: white;
        }
        
        .btn-danger {
            background: var(--color-danger);
            border: none;
            color: white;
        }
        
        .btn-info {
            background: var(--color-info);
            border: none;
            color: white;
        }
        
        .estado-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
            display: inline-block;
        }
        
        .estado-borrador {
            background: linear-gradient(135deg, #7F8C8D 0%, #95A5A6 100%);
        }
        
        .estado-publicado {
            background: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);
        }
        
        .estado-cancelado {
            background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
        }
        
        .estado-expirado {
            background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
        }
        
        .estado-no-iniciado {
            background: linear-gradient(135deg, #F39C12 0%, #E67E22 100%);
        }
        
        .estado-vigente {
            background: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);
        }
        
        .estado-sin-fechas {
            background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
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
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #95A5A6;
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .item-card {
                flex-direction: column;
                gap: 15px;
            }
            .item-actions {
                width: 100%;
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 5px;
            }
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
        <!-- HEADER CON INFORMACIÓN DEL FORMULARIO Y CONTADOR -->
        <div class="header-info">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-clipboard-list me-3"></i>FORMULACIÓN 144
                    </h1>
                    <h4 class="mb-0"><?php echo htmlspecialchars($formulario['titulo'] ?? 'Sin título'); ?></h4>
                    <p class="mb-0 mt-2 opacity-75">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo htmlspecialchars($formulario['descripcion'] ?? 'Sin descripción'); ?>
                    </p>
                    
                    <!-- INFORMACIÓN DE FECHAS -->
                    <?php if ($fecha_inicio || $fecha_cierre): ?>
                    <div class="fechas-info mt-3">
                        <?php if ($fecha_inicio): ?>
                        <span class="fecha-item">
                            <i class="fas fa-play-circle"></i> Inicio: <?php echo date('d/m/Y H:i', strtotime($fecha_inicio)); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($fecha_cierre): ?>
                        <span class="fecha-item">
                            <i class="fas fa-stop-circle"></i> Cierre: <?php echo date('d/m/Y H:i', strtotime($fecha_cierre)); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="estado-badge estado-<?php echo $estado_fechas['clase']; ?> mb-2">
                        <i class="fas fa-<?php echo $estado_fechas['valido'] ? 'check-circle' : 'exclamation-circle'; ?> me-1"></i>
                        <?php echo $estado_fechas['mensaje']; ?>
                    </span>
                    <br>
                    <a href="<?php echo $basePath; ?>/FOR-DE-144" class="btn btn-light ms-2">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                    <a href="<?php echo $basePath; ?>/formulacion144/test?id=<?php echo $formulario['id']; ?>" class="btn btn-info ms-2" target="_blank">
                        <i class="fas fa-flask me-1"></i>Test
                    </a>
                </div>
            </div>
            
            <!-- CONTADOR REGRESIVO (SOLO SI EL FORMULARIO ESTÁ VIGENTE) -->
            <?php if ($estado_fechas['valido'] && $fecha_cierre): ?>
            <div class="countdown-container">
                <div class="countdown-title">
                    <i class="fas fa-hourglass-half me-2"></i>
                    Tiempo restante para el cierre:
                </div>
                <div class="countdown-timer" id="countdown-timer">
                    <div class="countdown-box">
                        <div class="countdown-number" id="days">00</div>
                        <div class="countdown-label">Días</div>
                    </div>
                    <div class="countdown-box">
                        <div class="countdown-number" id="hours">00</div>
                        <div class="countdown-label">Horas</div>
                    </div>
                    <div class="countdown-box">
                        <div class="countdown-number" id="minutes">00</div>
                        <div class="countdown-label">Minutos</div>
                    </div>
                    <div class="countdown-box">
                        <div class="countdown-number" id="seconds">00</div>
                        <div class="countdown-label">Segundos</div>
                    </div>
                </div>
            </div>
            <?php elseif (!$estado_fechas['valido'] && $fecha_cierre && $fecha_actual > $fecha_cierre): ?>
            <div class="countdown-container countdown-expired mt-3">
                <div class="countdown-title text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ⚠️ FORMULARIO EXPIRADO - Fecha límite: <?php echo date('d/m/Y H:i', strtotime($fecha_cierre)); ?>
                </div>
            </div>
            <?php elseif (!$estado_fechas['valido'] && $fecha_inicio && $fecha_actual < $fecha_inicio): ?>
            <div class="countdown-container countdown-warning mt-3">
                <div class="countdown-title text-center">
                    <i class="fas fa-clock me-2"></i>
                    ⏳ Formulario disponible a partir del: <?php echo date('d/m/Y H:i', strtotime($fecha_inicio)); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- SI EL FORMULARIO NO ESTÁ VIGENTE, MOSTRAR MENSAJE Y DESHABILITAR ACCIONES -->
        <?php if (!$estado_fechas['valido']): ?>
        <div class="alert alert-warning text-center py-4 mb-4">
            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
            <h3>Formulario no disponible</h3>
            <p class="lead"><?php echo $estado_fechas['mensaje']; ?></p>
            <p>No es posible crear o editar borradores en este momento.</p>
            <a href="<?php echo $basePath; ?>/FOR-DE-144" class="btn btn-primary mt-2">
                <i class="fas fa-arrow-left me-2"></i>Ver otros formularios
            </a>
        </div>
        <?php else: ?>

        <div class="row">
            <!-- COLUMNA IZQUIERDA - BORRADORES (Estado 0) -->
            <div class="col-lg-4 mb-4">
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-pen-fancy fa-2x"></i>
                        <h2 class="mb-0">Borradores</h2>
                        <span class="badge bg-secondary ms-2"><?php echo count($borradores); ?></span>
                        <button class="btn btn-success ms-auto" onclick="abrirModalNuevoBorrador()">
                            <i class="fas fa-plus me-1"></i>Nuevo
                        </button>
                    </div>

                    <?php if (count($borradores) > 0): ?>
                        <?php foreach ($borradores as $borrador): ?>
                        <div class="item-card">
                            <div style="flex: 1;">
                                <h5 class="mb-2"><?php echo htmlspecialchars($borrador['nombre_borrador']); ?></h5>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($borrador['fecha_creacion'])); ?>
                                </small>
                                <?php if (!empty($borrador['anio'])): ?>
                                <span class="badge bg-secondary ms-2">Año: <?php echo $borrador['anio']; ?></span>
                                <?php endif; ?>
                                <span class="estado-badge estado-borrador ms-2">Borrador</span>
                            </div>
                            <div class="item-actions">
                                <button class="btn btn-sm btn-warning" onclick="editarBorrador(<?php echo $borrador['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success" onclick="publicarBorrador(<?php echo $borrador['id']; ?>)">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="cancelarBorrador(<?php echo $borrador['id']; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button class="btn btn-sm btn-info" onclick="abrirModalDuplicar(<?php echo $borrador['id']; ?>, '<?php echo htmlspecialchars($borrador['nombre_borrador']); ?>')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <h5>No hay borradores</h5>
                            <p class="text-muted">Haz clic en "Nuevo" para comenzar</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- COLUMNA CENTRAL - PUBLICADOS (Estado 2) -->
            <div class="col-lg-4 mb-4">
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-check-circle fa-2x"></i>
                        <h2 class="mb-0">Publicados</h2>
                        <span class="badge bg-success ms-2"><?php echo count($publicados); ?></span>
                    </div>

                    <?php if (count($publicados) > 0): ?>
                        <?php foreach ($publicados as $publicado): ?>
                        <div class="item-card">
                            <div style="flex: 1;">
                                <h5 class="mb-2"><?php echo htmlspecialchars($publicado['nombre_borrador']); ?></h5>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($publicado['fecha_creacion'])); ?>
                                </small>
                                <?php if (!empty($publicado['anio'])): ?>
                                <span class="badge bg-secondary ms-2">Año: <?php echo $publicado['anio']; ?></span>
                                <?php endif; ?>
                                <span class="estado-badge estado-publicado ms-2">Publicado</span>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-primary" onclick="verBorrador(<?php echo $publicado['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <h5>No hay publicaciones</h5>
                            <p class="text-muted">Los borradores aprobados aparecerán aquí</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- COLUMNA DERECHA - CANCELADOS (Estado 1) -->
            <div class="col-lg-4 mb-4">
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-times-circle fa-2x"></i>
                        <h2 class="mb-0">Cancelados</h2>
                        <span class="badge bg-danger ms-2"><?php echo count($cancelados); ?></span>
                    </div>

                    <?php if (count($cancelados) > 0): ?>
                        <?php foreach ($cancelados as $cancelado): ?>
                        <div class="item-card">
                            <div style="flex: 1;">
                                <h5 class="mb-2"><?php echo htmlspecialchars($cancelado['nombre_borrador']); ?></h5>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($cancelado['fecha_creacion'])); ?>
                                </small>
                                <?php if (!empty($cancelado['anio'])): ?>
                                <span class="badge bg-secondary ms-2">Año: <?php echo $cancelado['anio']; ?></span>
                                <?php endif; ?>
                                <span class="estado-badge estado-cancelado ms-2">Cancelado</span>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-danger" onclick="eliminarBorrador(<?php echo $cancelado['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-times-circle"></i>
                            <h5>No hay cancelados</h5>
                            <p class="text-muted">Los borradores cancelados aparecerán aquí</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- MODAL NUEVO BORRADOR -->
    <div class="modal fade" id="modalNuevoBorrador" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nuevo Borrador</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevoBorrador">
                    <input type="hidden" name="formulario_id" value="<?php echo $formulario['id']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Borrador *</label>
                            <input type="text" class="form-control" name="nombre_borrador" required 
                                   placeholder="Ej: Planificación 2024 - V1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Crear Borrador
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DUPLICAR BORRADOR -->
    <div class="modal fade" id="modalDuplicarBorrador" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-copy me-2"></i>Duplicar Borrador</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formDuplicarBorrador">
                    <input type="hidden" name="id" id="duplicar_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Nuevo Borrador *</label>
                            <input type="text" class="form-control" name="nombre_duplicado" id="nombre_duplicado" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-copy me-1"></i>Duplicar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL FORMULARIO ESTRATÉGICO -->
    <div class="modal fade" id="modalFormularioEstrategico" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Formulación Estratégica - <span id="tituloBorrador"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formFormularioEstrategico">
                    <input type="hidden" id="borrador_id" name="id">
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Nombre del Borrador</label>
                                <input type="text" class="form-control" id="nombre_borrador_edit" name="nombre_borrador" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">AÑO</label>
                                <select class="form-select" name="anio" id="anio">
                                    <option value="">Seleccione año</option>
                                    <?php 
                                    $anio_actual = date('Y');
                                    for ($i = $anio_actual; $i <= $anio_actual + 5; $i++): 
                                    ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">LÍNEA ESTRATÉGICA</label>
                                <input type="text" class="form-control" name="linea_estrategica" id="linea_estrategica">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">OBJETIVO</label>
                                <textarea class="form-control" name="objetivo" id="objetivo" rows="3"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">ESTRATEGIA</label>
                                <textarea class="form-control" name="estrategia" id="estrategia" rows="3"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">MOTOR DE DESARROLLO</label>
                                <input type="text" class="form-control" name="motor_desarrollo" id="motor_desarrollo">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">META DE RESULTADO</label>
                                <textarea class="form-control" name="meta_resultado" id="meta_resultado" rows="2"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">PROYECTO</label>
                                <input type="text" class="form-control" name="proyecto" id="proyecto">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PONDERACIÓN DE LOS PROYECTOS</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="ponderacion_proyectos" id="ponderacion_proyectos" step="0.01" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">ACTIVIDAD DEL PROYECTO (205)</label>
                                <textarea class="form-control" name="actividad_proyecto" id="actividad_proyecto" rows="4"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PONDERACIÓN DE LAS ACTIVIDADES POR PROYECTO</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="ponderacion_actividades" id="ponderacion_actividades" step="0.01" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RESPONSABLE</label>
                                <input type="text" class="form-control" name="responsable" id="responsable">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL VER FORMULARIO -->
    <div class="modal fade" id="modalVerFormulario" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>Ver Formulación - <span id="ver_titulo"></span>
                    </h5>
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
        // ============= CONTADOR REGRESIVO EN TIEMPO REAL =============
        function actualizarContador() {
            const fechaCierre = new Date('<?php echo $fecha_cierre; ?>').getTime();
            const ahora = new Date().getTime();
            const distancia = fechaCierre - ahora;
            
            if (distancia < 0) {
                document.getElementById('days').innerHTML = '00';
                document.getElementById('hours').innerHTML = '00';
                document.getElementById('minutes').innerHTML = '00';
                document.getElementById('seconds').innerHTML = '00';
                
                // Recargar la página cuando expire
                setTimeout(() => location.reload(), 3000);
                return;
            }
            
            const dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
            const horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((distancia % (1000 * 60)) / 1000);
            
            document.getElementById('days').innerHTML = dias.toString().padStart(2, '0');
            document.getElementById('hours').innerHTML = horas.toString().padStart(2, '0');
            document.getElementById('minutes').innerHTML = minutos.toString().padStart(2, '0');
            document.getElementById('seconds').innerHTML = segundos.toString().padStart(2, '0');
        }
        
        // Actualizar cada segundo
        actualizarContador();
        setInterval(actualizarContador, 1000);
        <?php endif; ?>

        function abrirModalNuevoBorrador() {
            $('#modalNuevoBorrador').modal('show');
        }

        $('#formNuevoBorrador').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: basePath + '/formulacion144/crearBorrador',
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

        function abrirModalDuplicar(id, nombre) {
            $('#duplicar_id').val(id);
            $('#nombre_duplicado').val('Copia de ' + nombre);
            $('#modalDuplicarBorrador').modal('show');
        }

        $('#formDuplicarBorrador').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: basePath + '/formulacion144/duplicar',
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

        function editarBorrador(id) {
            $.ajax({
                url: basePath + '/formulacion144/getBorrador?id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const b = response.borrador;
                        $('#borrador_id').val(b.id);
                        $('#tituloBorrador').text(b.nombre_borrador);
                        $('#nombre_borrador_edit').val(b.nombre_borrador);
                        $('#anio').val(b.anio);
                        $('#linea_estrategica').val(b.linea_estrategica);
                        $('#objetivo').val(b.objetivo);
                        $('#estrategia').val(b.estrategia);
                        $('#motor_desarrollo').val(b.motor_desarrollo);
                        $('#meta_resultado').val(b.meta_resultado);
                        $('#proyecto').val(b.proyecto);
                        $('#ponderacion_proyectos').val(b.ponderacion_proyectos);
                        $('#actividad_proyecto').val(b.actividad_proyecto);
                        $('#ponderacion_actividades').val(b.ponderacion_actividades);
                        $('#responsable').val(b.responsable);
                        $('#modalFormularioEstrategico').modal('show');
                    }
                }
            });
        }

        function verBorrador(id) {
            $.ajax({
                url: basePath + '/formulacion144/getBorrador?id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const b = response.borrador;
                        $('#ver_titulo').text(b.nombre_borrador);
                        let html = '<div class="row">';
                        html += '<div class="col-md-6 mb-3"><strong>AÑO:</strong><br>' + (b.anio || 'No especificado') + '</div>';
                        html += '<div class="col-md-6 mb-3"><strong>LÍNEA ESTRATÉGICA:</strong><br>' + (b.linea_estrategica || 'No especificada') + '</div>';
                        html += '<div class="col-12 mb-3"><strong>OBJETIVO:</strong><br>' + (b.objetivo || 'No especificado') + '</div>';
                        html += '<div class="col-12 mb-3"><strong>ESTRATEGIA:</strong><br>' + (b.estrategia || 'No especificada') + '</div>';
                        html += '<div class="col-12 mb-3"><strong>MOTOR DE DESARROLLO:</strong><br>' + (b.motor_desarrollo || 'No especificado') + '</div>';
                        html += '<div class="col-12 mb-3"><strong>META DE RESULTADO:</strong><br>' + (b.meta_resultado || 'No especificada') + '</div>';
                        html += '<div class="col-12 mb-3"><strong>PROYECTO:</strong><br>' + (b.proyecto || 'No especificado') + '</div>';
                        html += '<div class="col-md-6 mb-3"><strong>PONDERACIÓN DE LOS PROYECTOS:</strong><br>' + (b.ponderacion_proyectos ? b.ponderacion_proyectos + '%' : 'No especificada') + '</div>';
                        html += '<div class="col-12 mb-3"><strong>ACTIVIDAD DEL PROYECTO (205):</strong><br>' + (b.actividad_proyecto || 'No especificada') + '</div>';
                        html += '<div class="col-md-6 mb-3"><strong>PONDERACIÓN DE LAS ACTIVIDADES POR PROYECTO:</strong><br>' + (b.ponderacion_actividades ? b.ponderacion_actividades + '%' : 'No especificada') + '</div>';
                        html += '<div class="col-md-6 mb-3"><strong>RESPONSABLE:</strong><br>' + (b.responsable || 'No especificado') + '</div>';
                        html += '</div>';
                        $('#ver_contenido').html(html);
                        $('#modalVerFormulario').modal('show');
                    }
                }
            });
        }

        $('#formFormularioEstrategico').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: basePath + '/formulacion144/guardar',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Guardado!', response.message, 'success');
                        $('#modalFormularioEstrategico').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    }
                }
            });
        });

        function publicarBorrador(id) {
            Swal.fire({
                title: '¿Publicar borrador?',
                text: 'Este borrador pasará a estado PUBLICADO',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#27AE60',
                confirmButtonText: 'Sí, publicar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/formulacion144/cambiarEstado',
                        type: 'POST',
                        data: { id: id, estado: 2 },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('¡Publicado!', response.message, 'success');
                                setTimeout(() => location.reload(), 1500);
                            }
                        }
                    });
                }
            });
        }

        function cancelarBorrador(id) {
            Swal.fire({
                title: '¿Cancelar borrador?',
                text: 'Este borrador pasará a estado CANCELADO',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E74C3C',
                confirmButtonText: 'Sí, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/formulacion144/cambiarEstado',
                        type: 'POST',
                        data: { id: id, estado: 1 },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('¡Cancelado!', response.message, 'success');
                                setTimeout(() => location.reload(), 1500);
                            }
                        }
                    });
                }
            });
        }

        function eliminarBorrador(id) {
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
                        url: basePath + '/formulacion144/eliminar',
                        type: 'POST',
                        data: { id: id },
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