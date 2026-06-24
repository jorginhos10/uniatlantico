<?php
// vista/formulacion144/index.php
require_once __DIR__ . '/../../config/security.php';

$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();
$fecha_cierre = $formulario['fecha_cierre'] ?? null;
$fecha_inicio = $formulario['fecha_inicio'] ?? null;
$fecha_actual = date('Y-m-d H:i:s');

$es_seguimiento = isset($_GET['tipo']) && $_GET['tipo'] == 'seguimiento';
$titulo_pagina  = $es_seguimiento ? 'SEGUIMIENTO 144' : 'FORMULACIÓN 144';
$icono_pagina   = $es_seguimiento ? 'fa-chart-line' : 'fa-clipboard-list';
$color_pagina   = '#007AFF';

$titulo       = $titulo_pagina . ' — ' . htmlspecialchars($formulario['titulo'] ?? '');
$paginaActual = 'formulacion144';

ob_start();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
        :root {
            --color-primary: #007AFF;
            --color-primary-light: #0A84FF;
            --color-success: #34C759;
            --color-warning: #FF9500;
            --color-danger: #FF3B30;
            --color-info: #007AFF;
            --color-bg: #F2F2F7;
            --color-white: #FFFFFF;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
        }
        
        .header-info {
            background: linear-gradient(135deg, <?php echo $color_pagina; ?> 0%, <?php echo $es_seguimiento ? '#2980B9' : '#34495E'; ?> 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 6px 24px rgba(0,122,255,0.25);
        }

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
        
        .section-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            border-left: 5px solid <?php echo $color_pagina; ?>;
        }
        
        .section-title {
            color: <?php echo $color_pagina; ?>;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .item-card {
            background: #F2F2F7;
            border: 1px solid rgba(60,60,67,.12);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: transform .2s, box-shadow .2s;
        }

        .item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,.1);
            border-left: 4px solid <?php echo $color_pagina; ?>;
        }
        
        .btn-success {
            background: var(--color-success);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            color: white;
        }

        .btn-primary {
            background: var(--color-primary);
            border: none;
            border-radius: 10px;
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
        
        .estado-borrador  { background: #8E8E93; }
        .estado-publicado { background: #34C759; }
        .estado-cancelado { background: #FF3B30; }
        
        .modal-content {
            border-radius: 16px;
            border: none;
            overflow: hidden;
        }

        .modal-header {
            background: #FFFFFF;
            color: #000000;
            border-radius: 16px 16px 0 0;
            border-bottom: 1px solid rgba(60,60,67,.12);
            padding: 18px 22px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 12px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #007AFF;
            box-shadow: 0 0 0 3px rgba(0,122,255,.15);
        }

        .form-label {
            font-weight: 600;
            color: #000000;
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
        
        .readonly-field {
            background-color: #e9ecef;
            opacity: 0.9;
            cursor: not-allowed;
        }
        
        .seguimiento-badge {
            background: <?php echo $color_pagina; ?>;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            margin-left: 10px;
        }
        
        .item-actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .item-card {
                flex-direction: column;
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
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>
    <div class="container-fluid">
        <!-- HEADER CON INFORMACIÓN DEL FORMULARIO Y CONTADOR -->
        <div class="header-info">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas <?php echo $icono_pagina; ?> me-3"></i><?php echo $titulo_pagina; ?>
                    </h1>
                    <h4 class="mb-0"><?php echo htmlspecialchars($formulario['titulo'] ?? 'Sin título'); ?></h4>
                    <p class="mb-0 mt-2 opacity-75">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo htmlspecialchars($formulario['descripcion'] ?? 'Sin descripción'); ?>
                    </p>
                    
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
                    
                    <!-- BOTONES DE NAVEGACIÓN -->
                    <?php if ($es_seguimiento): ?>
                        <a href="<?php echo $basePath; ?>/formulacion144/index?id=<?php echo $formulario['id']; ?>" class="btn btn-primary ms-2">
                            <i class="fas fa-clipboard-list me-1"></i>Formulación
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $basePath; ?>/formulacion144/index?id=<?php echo $formulario['id']; ?>&tipo=seguimiento" class="btn btn-info ms-2">
                            <i class="fas fa-chart-line me-1"></i>Seguimiento
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo $basePath; ?>/formulacion144/test?id=<?php echo $formulario['id']; ?>" class="btn btn-secondary ms-2" target="_blank">
                        <i class="fas fa-flask me-1"></i>Test
                    </a>
                </div>
            </div>
            
            <!-- CONTADOR REGRESIVO -->
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

        <div class="row">
            <!-- COLUMNA IZQUIERDA - BORRADORES (Estado 0) -->
            <div class="col-lg-4 mb-4">
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-pen-fancy fa-2x"></i>
                        <h2 class="mb-0">Borradores</h2>
                        <span class="badge bg-secondary ms-2"><?php echo count($borradores); ?></span>
                        
                        <?php if (!$es_seguimiento): ?>
                        <button class="btn btn-success ms-auto" onclick="abrirModalNuevoBorrador()">
                            <i class="fas fa-plus me-1"></i>Nuevo
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php if (count($borradores) > 0): ?>
                        <?php foreach ($borradores as $item): ?>
                        <div class="item-card">
                            <div>
                                <h5 class="mb-2">
                                    <?php echo htmlspecialchars($item['nombre_borrador'] ?? $item['nombre_seguimiento']); ?>
                                </h5>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($item['fecha_creacion'])); ?>
                                </small>
                                <?php if (!empty($item['anio'])): ?>
                                <span class="badge bg-secondary ms-2">Año: <?php echo $item['anio']; ?></span>
                                <?php endif; ?>
                                <span class="estado-badge estado-borrador ms-2">Borrador</span>
                            </div>
                            <div class="item-actions">
                                <?php if ($es_seguimiento): ?>
                                    <!-- SOLO PARA SEGUIMIENTO -->
                                    <button class="btn btn-sm btn-info" onclick="editarSeguimiento(<?php echo $item['formulacion_id']; ?>)">
                                        <i class="fas fa-chart-line"></i> Avances
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="publicarItem(<?php echo $item['formulacion_id']; ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="cancelarItem(<?php echo $item['formulacion_id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php else: ?>
                                    <!-- SOLO PARA FORMULACIÓN -->
                                    <button class="btn btn-sm btn-warning" onclick="editarBorrador(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="publicarItem(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="cancelarItem(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info" onclick="abrirModalDuplicar(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['nombre_borrador']); ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <h5>No hay borradores</h5>
                            <?php if (!$es_seguimiento): ?>
                            <p class="text-muted">Haz clic en "Nuevo" para comenzar</p>
                            <?php endif; ?>
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
                        <?php foreach ($publicados as $item): ?>
                        <div class="item-card">
                            <div>
                                <h5 class="mb-2">
                                    <?php echo htmlspecialchars($item['nombre_borrador'] ?? $item['nombre_seguimiento']); ?>
                                </h5>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($item['fecha_creacion'])); ?>
                                </small>
                                <?php if (!empty($item['anio'])): ?>
                                <span class="badge bg-secondary ms-2">Año: <?php echo $item['anio']; ?></span>
                                <?php endif; ?>
                                <span class="estado-badge estado-publicado ms-2">Publicado</span>
                            </div>
                            <div class="item-actions">
                                <?php if ($es_seguimiento): ?>
                                    <button class="btn btn-sm btn-info" onclick="verDetalle(<?php echo $item['formulacion_id']; ?>)">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-primary" onclick="verBorrador(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <h5>No hay publicaciones</h5>
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
                        <?php foreach ($cancelados as $item): ?>
                        <div class="item-card">
                            <div>
                                <h5 class="mb-2">
                                    <?php echo htmlspecialchars($item['nombre_borrador'] ?? $item['nombre_seguimiento']); ?>
                                </h5>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($item['fecha_creacion'])); ?>
                                </small>
                                <?php if (!empty($item['anio'])): ?>
                                <span class="badge bg-secondary ms-2">Año: <?php echo $item['anio']; ?></span>
                                <?php endif; ?>
                                <span class="estado-badge estado-cancelado ms-2">Cancelado</span>
                            </div>
                            <div class="item-actions">
                                <?php if ($es_seguimiento): ?>
                                    <button class="btn btn-sm btn-danger" onclick="eliminarItem(<?php echo $item['formulacion_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-danger" onclick="eliminarItem(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-times-circle"></i>
                            <h5>No hay cancelados</h5>
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
                            <small class="text-muted">Este nombre también se usará para el seguimiento</small>
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

    <!-- MODAL FORMULARIO ESTRATÉGICO (PARA FORMULACIÓN) -->
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

    <!-- MODAL SEGUIMIENTO (PARA REGISTRAR AVANCES) -->
    <div class="modal fade" id="modalSeguimiento" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-chart-line me-2"></i>Registrar Avances - <span id="tituloSeguimiento"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSeguimiento">
                    <input type="hidden" id="seguimiento_borrador_id" name="id">
                    <div class="modal-body">
                        <!-- DATOS DE FORMULACIÓN (SOLO LECTURA) -->
                        <div class="card mb-4 bg-light">
                            <div class="card-header bg-secondary text-white">
                                <i class="fas fa-clipboard-list me-2"></i>DATOS DE FORMULACIÓN (No editables)
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">AÑO</label>
                                        <input type="text" class="form-control readonly-field" id="ver_anio" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">LÍNEA ESTRATÉGICA</label>
                                        <input type="text" class="form-control readonly-field" id="ver_linea" readonly>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">OBJETIVO</label>
                                        <textarea class="form-control readonly-field" id="ver_objetivo" rows="2" readonly></textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">ESTRATEGIA</label>
                                        <textarea class="form-control readonly-field" id="ver_estrategia" rows="2" readonly></textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">MOTOR DE DESARROLLO</label>
                                        <input type="text" class="form-control readonly-field" id="ver_motor" readonly>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">META DE RESULTADO</label>
                                        <textarea class="form-control readonly-field" id="ver_meta" rows="2" readonly></textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">PROYECTO</label>
                                        <input type="text" class="form-control readonly-field" id="ver_proyecto" readonly>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">ACTIVIDAD DEL PROYECTO (205)</label>
                                        <textarea class="form-control readonly-field" id="ver_actividad" rows="3" readonly></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">RESPONSABLE</label>
                                        <input type="text" class="form-control readonly-field" id="ver_responsable" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DATOS DE SEGUIMIENTO (EDITABLES) -->
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <i class="fas fa-chart-line me-2"></i>REGISTRO DE AVANCES
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">AVANCE FÍSICO (%)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="avance_fisico" id="avance_fisico" step="0.01" min="0" max="100">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">AVANCE FINANCIERO (%)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="avance_financiero" id="avance_financiero" step="0.01" min="0" max="100">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">OBSERVACIONES</label>
                                        <textarea class="form-control" name="observaciones" id="observaciones" rows="4" placeholder="Ingrese observaciones del seguimiento..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save me-1"></i>Guardar Avances
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL VER DETALLE -->
    <div class="modal fade" id="modalVerFormulario" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>Ver Detalle - <span id="ver_titulo"></span>
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
        const esSeguimiento = <?php echo $es_seguimiento ? 'true' : 'false'; ?>;
        
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
            
            const dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
            const horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((distancia % (1000 * 60)) / 1000);
            
            document.getElementById('days').innerHTML = dias.toString().padStart(2, '0');
            document.getElementById('hours').innerHTML = horas.toString().padStart(2, '0');
            document.getElementById('minutes').innerHTML = minutos.toString().padStart(2, '0');
            document.getElementById('seconds').innerHTML = segundos.toString().padStart(2, '0');
        }
        
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

        function editarSeguimiento(id) {
            $.ajax({
                url: basePath + '/formulacion144/getBorrador?id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const b = response.borrador;
                        
                        $('#ver_anio').val(b.anio || '');
                        $('#ver_linea').val(b.linea_estrategica || '');
                        $('#ver_objetivo').val(b.objetivo || '');
                        $('#ver_estrategia').val(b.estrategia || '');
                        $('#ver_motor').val(b.motor_desarrollo || '');
                        $('#ver_meta').val(b.meta_resultado || '');
                        $('#ver_proyecto').val(b.proyecto || '');
                        $('#ver_actividad').val(b.actividad_proyecto || '');
                        $('#ver_responsable').val(b.responsable || '');
                        
                        $('#seguimiento_borrador_id').val(b.id);
                        $('#tituloSeguimiento').text(b.nombre_borrador);
                        
                        $.ajax({
                            url: basePath + '/formulacion144/getSeguimiento?id=' + id,
                            type: 'GET',
                            dataType: 'json',
                            success: function(resp) {
                                if (resp.success) {
                                    $('#avance_fisico').val(resp.seguimiento.avance_fisico || '');
                                    $('#avance_financiero').val(resp.seguimiento.avance_financiero || '');
                                    $('#observaciones').val(resp.seguimiento.observaciones || '');
                                } else {
                                    $('#avance_fisico').val('');
                                    $('#avance_financiero').val('');
                                    $('#observaciones').val('');
                                }
                            }
                        });
                        
                        $('#modalSeguimiento').modal('show');
                    }
                }
            });
        }

        $('#formSeguimiento').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: basePath + '/formulacion144/guardarSeguimiento',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Guardado!', response.message, 'success');
                        $('#modalSeguimiento').modal('hide');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        });

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

        function publicarItem(id) {
            Swal.fire({
                title: '¿Publicar?',
                text: 'Este registro pasará a estado PUBLICADO',
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

        function cancelarItem(id) {
            Swal.fire({
                title: '¿Cancelar?',
                text: 'Este registro pasará a estado CANCELADO',
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

        function eliminarItem(id) {
            Swal.fire({
                title: '¿Eliminar?',
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

        function verDetalle(id) {
            $.ajax({
                url: basePath + '/formulacion144/getBorrador?id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const b = response.borrador;
                        $('#ver_titulo').text(b.nombre_borrador);
                        
                        $.ajax({
                            url: basePath + '/formulacion144/getSeguimiento?id=' + id,
                            type: 'GET',
                            dataType: 'json',
                            success: function(resp) {
                                let html = '<div class="row">';
                                
                                html += '<div class="col-12"><h5 class="text-primary">DATOS DE FORMULACIÓN</h5></div>';
                                html += '<div class="col-md-6 mb-3"><strong>AÑO:</strong><br>' + (b.anio || 'No especificado') + '</div>';
                                html += '<div class="col-md-6 mb-3"><strong>LÍNEA ESTRATÉGICA:</strong><br>' + (b.linea_estrategica || 'No especificada') + '</div>';
                                html += '<div class="col-12 mb-3"><strong>OBJETIVO:</strong><br>' + (b.objetivo || 'No especificado') + '</div>';
                                html += '<div class="col-12 mb-3"><strong>ESTRATEGIA:</strong><br>' + (b.estrategia || 'No especificada') + '</div>';
                                html += '<div class="col-12 mb-3"><strong>PROYECTO:</strong><br>' + (b.proyecto || 'No especificado') + '</div>';
                                html += '<div class="col-12 mb-3"><strong>ACTIVIDAD DEL PROYECTO:</strong><br>' + (b.actividad_proyecto || 'No especificada') + '</div>';
                                html += '<div class="col-md-6 mb-3"><strong>RESPONSABLE:</strong><br>' + (b.responsable || 'No especificado') + '</div>';
                                
                                if (resp.success) {
                                    html += '<div class="col-12 mt-4"><h5 class="text-info">DATOS DE SEGUIMIENTO</h5></div>';
                                    html += '<div class="col-md-6 mb-3"><strong>AVANCE FÍSICO:</strong><br>' + (resp.seguimiento.avance_fisico ? resp.seguimiento.avance_fisico + '%' : '0%') + '</div>';
                                    html += '<div class="col-md-6 mb-3"><strong>AVANCE FINANCIERO:</strong><br>' + (resp.seguimiento.avance_financiero ? resp.seguimiento.avance_financiero + '%' : '0%') + '</div>';
                                    html += '<div class="col-12 mb-3"><strong>OBSERVACIONES:</strong><br>' + (resp.seguimiento.observaciones || 'Sin observaciones') + '</div>';
                                }
                                
                                html += '</div>';
                                $('#ver_contenido').html(html);
                                $('#modalVerFormulario').modal('show');
                            },
                            error: function() {
                                let html = '<div class="row">';
                                html += '<div class="col-md-6 mb-3"><strong>AÑO:</strong><br>' + (b.anio || 'No especificado') + '</div>';
                                html += '<div class="col-md-6 mb-3"><strong>LÍNEA ESTRATÉGICA:</strong><br>' + (b.linea_estrategica || 'No especificada') + '</div>';
                                html += '<div class="col-12 mb-3"><strong>OBJETIVO:</strong><br>' + (b.objetivo || 'No especificado') + '</div>';
                                html += '<div class="col-12 mb-3"><strong>ESTRATEGIA:</strong><br>' + (b.estrategia || 'No especificada') + '</div>';
                                html += '<div class="col-12 mb-3"><strong>PROYECTO:</strong><br>' + (b.proyecto || 'No especificado') + '</div>';
                                html += '<div class="col-12 mb-3"><strong>ACTIVIDAD DEL PROYECTO:</strong><br>' + (b.actividad_proyecto || 'No especificada') + '</div>';
                                html += '<div class="col-md-6 mb-3"><strong>RESPONSABLE:</strong><br>' + (b.responsable || 'No especificado') + '</div>';
                                html += '</div>';
                                $('#ver_contenido').html(html);
                                $('#modalVerFormulario').modal('show');
                            }
                        });
                    }
                }
            });
        }
    </script>
<?php require_once __DIR__ . '/../complementos/footer.php'; ?>