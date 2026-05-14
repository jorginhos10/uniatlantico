<?php
// vista/modulo144/index.php
$basePath = Config::getBasePath();
// Solo asignar fecha_cierre si es formulario de rango con fecha válida
$fecha_cierre = null;
if (($formulario['tipo_tiempo'] ?? '') === 'rango' && !empty($formulario['fecha_cierre'])) {
    $fecha_cierre = $formulario['fecha_cierre'];
}
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 4px 8px;
            min-height: 48px;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.25rem rgba(44,62,80,0.15);
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #1D71B8;
            border: none;
            border-radius: 20px;
            color: white;
            padding: 2px 10px;
            font-size: 0.82rem;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice:first-of-type {
            background-color: #D85819;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255,255,255,0.8);
            margin-right: 5px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: white;
            background: transparent;
        }
        .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--color-primary);
        }
        .select2-search--dropdown .select2-search__field {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 6px 10px;
        }
        .select2-container { width: 100% !important; }
        .select2-max-reached {
            padding: 6px 12px;
            color: #D85819;
            font-size: 0.82rem;
            font-weight: 600;
        }
    </style>
    
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
        
        .facultad-color-0 { border-left-color: #9C27B0; }
        .facultad-color-1 { border-left-color: #FF9800; }
        .facultad-color-2 { border-left-color: #2196F3; }
        .facultad-color-3 { border-left-color: #4CAF50; }
        .facultad-color-4 { border-left-color: #F44336; }
        .facultad-color-5 { border-left-color: #673AB7; }
        .facultad-color-6 { border-left-color: #FF5722; }
        .facultad-color-7 { border-left-color: #009688; }
        .facultad-color-8 { border-left-color: #3F51B5; }
        .facultad-color-9 { border-left-color: #E91E63; }
        
        .badge-facultad-0 { background-color: #9C27B0; }
        .badge-facultad-1 { background-color: #FF9800; }
        .badge-facultad-2 { background-color: #2196F3; }
        .badge-facultad-3 { background-color: #4CAF50; }
        .badge-facultad-4 { background-color: #F44336; }
        .badge-facultad-5 { background-color: #673AB7; }
        .badge-facultad-6 { background-color: #FF5722; }
        .badge-facultad-7 { background-color: #009688; }
        .badge-facultad-8 { background-color: #3F51B5; }
        .badge-facultad-9 { background-color: #E91E63; }
        
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
        
        .lista-container {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .lista-header {
            background: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: var(--color-primary);
        }
        
        .lista-item {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .lista-item:hover {
            background-color: #f8f9fa;
        }
        
        .lista-item:last-child {
            border-bottom: none;
        }
        
        .lista-item-info {
            flex: 1;
        }
        
        .lista-item-titulo {
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 5px;
        }
        
        .lista-item-detalles {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .lista-item-actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
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
        
        .bg-light-view {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px;
            min-height: 46px;
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
            .lista-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .lista-item-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }

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
        
        .nav-tabs .nav-link.tab-incomplete {
            color: var(--color-tab-incomplete) !important;
        }
        
        .nav-tabs .nav-link.tab-complete {
            color: var(--color-tab-complete) !important;
        }
        
        .nav-tabs .nav-link.tab-complete i {
            color: var(--color-tab-complete);
        }

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

        .modal-body-scroll {
            max-height: 70vh;
            overflow-y: auto;
            padding: 20px;
        }

        .desarrollo-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 30px;
            margin-top: 20px;
            color: white;
            text-align: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .desarrollo-icon {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        
        .desarrollo-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .desarrollo-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .desarrollo-badge {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 30px;
            padding: 8px 20px;
            display: inline-block;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .facultad-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        
        .facultad-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }
        
        .facultad-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            padding: 10px 0;
        }
        
        .facultad-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .facultad-header i {
            transition: transform 0.3s ease;
        }
        
        .facultad-header.collapsed i {
            transform: rotate(-90deg);
        }
        
        .facultad-content {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .badge-facultad {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
        }

        .facultad-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 3px solid var(--color-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .facultad-item:hover {
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .facultad-item-info h6 {
            margin: 0 0 5px 0;
            color: var(--color-primary);
        }
        
        .facultad-item-info small {
            color: #6c757d;
        }
        
        .facultad-item-actions {
            display: flex;
            gap: 5px;
        }

        .gestionado-indicador {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            background-color: #27AE60;
            color: white;
            margin-left: 8px;
        }
        
        .gestionado-indicador i {
            font-size: 0.6rem;
            margin-right: 3px;
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
                    <h4 class="mb-0"><?php echo htmlspecialchars($formulario['titulo'] ?? ''); ?></h4>
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

        <!-- Acordeón 1: Módulos existentes -->
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
                        
                        <!-- BORRADORES -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-secondary bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="fas fa-pen-fancy" style="color: #7F8C8D;"></i>
                                </div>
                                <h5 class="mb-0">Borradores</h5>
                                <span class="badge bg-secondary ms-2"><?php echo count($modulo['borradores']); ?></span>
                            </div>
                            
                            <?php if (count($modulo['borradores']) > 0): ?>
                                <div class="lista-container">
                                    <div class="lista-header">
                                        <div class="row">
                                            <div class="col-md-6">Nombre</div>
                                            <div class="col-md-3">Fecha de creación</div>
                                            <div class="col-md-3">Acciones</div>
                                        </div>
                                    </div>
                                    <?php foreach ($modulo['borradores'] as $borrador): ?>
                                    <div class="lista-item">
                                        <div class="lista-item-info">
                                            <div class="lista-item-titulo">
                                                <?php echo htmlspecialchars($borrador['nombre_borrador']); ?>
                                                <?php if ($borrador['gestionado_facultades'] == 1): ?>
                                                <span class="gestionado-indicador"><i class="fas fa-check-circle"></i> Gestionado</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="lista-item-detalles">
                                                <span><i class="far fa-calendar-alt me-1"></i> <?php echo date('d/m/Y H:i', strtotime($borrador['fecha_creacion'])); ?></span>
                                                <?php if (!empty($borrador['anio'])): ?>
                                                <span><i class="fas fa-calendar me-1"></i> Año: <?php echo $borrador['anio']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="lista-item-actions">
                                            <?php if ($key === 'formulacion'): ?>
                                            <button class="btn btn-sm btn-warning" onclick="editarBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-sm btn-info" onclick="verSeguimiento('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($key === 'formulacion'): ?>
                                            <button class="btn btn-sm btn-success" onclick="cambiarEstadoBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 2)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="cambiarEstadoBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 1)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-sm btn-info" onclick="abrirModalDuplicar('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, '<?php echo htmlspecialchars($borrador['nombre_borrador']); ?>')">
                                                <i class="fas fa-copy"></i>
                                            </button>
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

                        <!-- PUBLICADOS -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="fas fa-check-circle" style="color: #27AE60;"></i>
                                </div>
                                <h5 class="mb-0">Publicados</h5>
                                <span class="badge bg-success ms-2"><?php echo count($modulo['publicados']); ?></span>
                            </div>
                            
                            <?php if (count($modulo['publicados']) > 0): ?>
                                <div class="lista-container">
                                    <div class="lista-header">
                                        <div class="row">
                                            <div class="col-md-6">Nombre</div>
                                            <div class="col-md-3">Fecha de publicación</div>
                                            <div class="col-md-3">Acciones</div>
                                        </div>
                                    </div>
                                    <?php foreach ($modulo['publicados'] as $publicado): ?>
                                    <div class="lista-item">
                                        <div class="lista-item-info">
                                            <div class="lista-item-titulo">
                                                <?php echo htmlspecialchars($publicado['nombre_borrador']); ?>
                                                <?php if ($publicado['gestionado_facultades'] == 1): ?>
                                                <span class="gestionado-indicador"><i class="fas fa-check-circle"></i> Gestionado</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="lista-item-detalles">
                                                <span><i class="far fa-calendar-alt me-1"></i> <?php echo date('d/m/Y H:i', strtotime($publicado['fecha_creacion'])); ?></span>
                                                <?php if (!empty($publicado['anio'])): ?>
                                                <span><i class="fas fa-calendar me-1"></i> Año: <?php echo $publicado['anio']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="lista-item-actions">
                                            <?php if ($key === 'formulacion'): ?>
                                            <button class="btn btn-sm btn-primary" onclick="verBorrador('<?php echo $key; ?>', <?php echo $publicado['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>Ver
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-sm btn-primary" onclick="verSeguimiento('<?php echo $key; ?>', <?php echo $publicado['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i>Ver
                                            </button>
                                            <?php endif; ?>
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

                        <!-- CANCELADOS -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-danger bg-opacity-10 p-2 rounded-circle me-2">
                                    <i class="fas fa-times-circle" style="color: #E74C3C;"></i>
                                </div>
                                <h5 class="mb-0">Cancelados</h5>
                                <span class="badge bg-danger ms-2"><?php echo count($modulo['cancelados']); ?></span>
                            </div>
                            
                            <?php if (count($modulo['cancelados']) > 0): ?>
                                <div class="lista-container">
                                    <div class="lista-header">
                                        <div class="row">
                                            <div class="col-md-6">Nombre</div>
                                            <div class="col-md-3">Fecha de cancelación</div>
                                            <div class="col-md-3">Acciones</div>
                                        </div>
                                    </div>
                                    <?php foreach ($modulo['cancelados'] as $cancelado): ?>
                                    <div class="lista-item">
                                        <div class="lista-item-info">
                                            <div class="lista-item-titulo">
                                                <?php echo htmlspecialchars($cancelado['nombre_borrador']); ?>
                                            </div>
                                            <div class="lista-item-detalles">
                                                <span><i class="far fa-calendar-alt me-1"></i> <?php echo date('d/m/Y H:i', strtotime($cancelado['fecha_creacion'])); ?></span>
                                                <?php if (!empty($cancelado['anio'])): ?>
                                                <span><i class="fas fa-calendar me-1"></i> Año: <?php echo $cancelado['anio']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="lista-item-actions">
                                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarBorrador('<?php echo $key; ?>', <?php echo $cancelado['id']; ?>)">
                                                <i class="fas fa-trash me-1"></i>Eliminar
                                            </button>
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

        <!-- Acordeón 2: Formulación y Seguimiento por Facultades -->
        <div class="accordion mt-4" id="accordionFacultades">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFacultades">
                    <button class="accordion-button collapsed" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapseFacultades" 
                            style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); color: white !important;">
                        <i class="fas fa-university me-3 fa-2x"></i>
                        <div>
                            <span style="font-size: 1.3rem;">FORMULACIÓN Y SEGUIMIENTO POR FACULTADES</span>
                            <br>
                            <small style="font-size: 0.85rem; opacity: 0.9;">Solo formulaciones con gestión desde facultades activada</small>
                        </div>
                        <span class="badge bg-light text-dark ms-3">
                            <i class="fas fa-building me-1"></i><?php echo count($facultades ?? []); ?> facultades
                        </span>
                    </button>
                </h2>
                <div id="collapseFacultades" 
                     class="accordion-collapse collapse" 
                     data-bs-parent="#accordionFacultades">
                    <div class="accordion-body p-4">
                        
                        <?php if (isset($facultades) && count($facultades) > 0): ?>
                            
                            <?php foreach ($facultades as $index => $facultad): ?>
                                <?php 
                                if ($facultad['estado'] != 1) continue; 
                                $colorIndex = $index % 10;
                                $facultadId = $facultad['id'];
                                
                                $formulaciones_con_check = [];
                                
                                if (isset($datos_modulos['formulacion']['borradores'])) {
                                    foreach ($datos_modulos['formulacion']['borradores'] as $borrador) {
                                        if (isset($borrador['gestionado_facultades']) && $borrador['gestionado_facultades'] == 1) {
                                            $formulaciones_con_check[] = $borrador;
                                        }
                                    }
                                }
                                
                                if (isset($datos_modulos['formulacion']['publicados'])) {
                                    foreach ($datos_modulos['formulacion']['publicados'] as $publicado) {
                                        if (isset($publicado['gestionado_facultades']) && $publicado['gestionado_facultades'] == 1) {
                                            $formulaciones_con_check[] = $publicado;
                                        }
                                    }
                                }
                                
                                usort($formulaciones_con_check, function($a, $b) {
                                    return strtotime($b['fecha_creacion']) - strtotime($a['fecha_creacion']);
                                });
                                ?>
                                
                                <div class="facultad-card facultad-color-<?php echo $colorIndex; ?>" id="facultad-card-<?php echo $facultadId; ?>">
                                    <div class="facultad-header" data-bs-toggle="collapse" data-bs-target="#facultad<?php echo $facultadId; ?>" aria-expanded="false">
                                        <h5 class="mb-0">
                                            <span class="badge-facultad badge-facultad-<?php echo $colorIndex; ?> me-2">
                                                <?php echo htmlspecialchars($facultad['codigo'] ?? 'FAC'); ?>
                                            </span>
                                            <?php echo htmlspecialchars($facultad['nombre']); ?>
                                        </h5>
                                        <span class="badge bg-primary" id="facultad-count-<?php echo $facultadId; ?>">
                                            <?php echo count($formulaciones_con_check); ?> formulaciones con check
                                        </span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div id="facultad<?php echo $facultadId; ?>" class="collapse facultad-content">
                                        <div id="facultad-contenido-<?php echo $facultadId; ?>">
                                            <?php if (count($formulaciones_con_check) > 0): ?>
                                                <div class="mb-3">
                                                    <h6 class="text-muted mb-3"><i class="fas fa-clipboard-list me-2"></i>Formulaciones con gestión desde facultades activada:</h6>
                                                    <?php foreach ($formulaciones_con_check as $borrador): ?>
                                                        <?php
                                                        $estadoClass = 'bg-secondary';
                                                        $estadoText = 'Borrador';
                                                        
                                                        if ($borrador['estado_formulacion'] == 2) {
                                                            $estadoClass = 'bg-success';
                                                            $estadoText = 'Publicado';
                                                        } else if ($borrador['estado_formulacion'] == 1) {
                                                            $estadoClass = 'bg-danger';
                                                            $estadoText = 'Cancelado';
                                                        }
                                                        
                                                        $fecha = isset($borrador['fecha_creacion']) ? date('d/m/Y H:i', strtotime($borrador['fecha_creacion'])) : '-';
                                                        ?>
                                                        <div class="facultad-item" id="formulacion-item-<?php echo $borrador['id']; ?>" data-facultad-id="<?php echo $facultadId; ?>">
                                                            <div class="facultad-item-info">
                                                                <h6>
                                                                    <?php echo htmlspecialchars($borrador['nombre_borrador'] ?? 'Sin nombre'); ?>
                                                                    <span class="gestionado-indicador"><i class="fas fa-check-circle"></i> Gestionado</span>
                                                                </h6>
                                                                <small>
                                                                    <i class="far fa-calendar-alt me-1"></i> <?php echo $fecha; ?>
                                                                    <?php if (!empty($borrador['anio'])): ?>
                                                                        | <i class="fas fa-calendar me-1"></i> Año: <?php echo $borrador['anio']; ?>
                                                                    <?php endif; ?>
                                                                </small>
                                                                <div class="mt-1">
                                                                    <span class="badge <?php echo $estadoClass; ?>"><?php echo $estadoText; ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="facultad-item-actions">
                                                                <button class="btn btn-sm btn-warning" onclick="editarBorrador('formulacion', <?php echo $borrador['id']; ?>)" title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm" style="background-color: #FF9800; color: white;" onclick="abrirGestionSemestral(<?php echo $borrador['id']; ?>, '<?php echo htmlspecialchars($borrador['nombre_borrador']); ?>')" title="Gestión Semestral">
                                                                    <i class="fas fa-calendar-alt"></i>
                                                                </button>
                                                                <?php if ($borrador['estado_formulacion'] == 0): ?>
                                                                    <button class="btn btn-sm btn-success" onclick="cambiarEstadoBorrador('formulacion', <?php echo $borrador['id']; ?>, 2)" title="Publicar">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                                <?php if ($borrador['estado_formulacion'] == 2): ?>
                                                                    <button class="btn btn-sm btn-info" onclick="verBorrador('formulacion', <?php echo $borrador['id']; ?>)" title="Ver">
                                                                        <i class="fas fa-eye"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                                <?php if ($borrador['estado_formulacion'] == 1): ?>
                                                                    <button class="btn btn-sm btn-danger" onclick="eliminarBorrador('formulacion', <?php echo $borrador['id']; ?>)" title="Eliminar">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                                <button class="btn btn-sm btn-info" onclick="abrirModalDuplicar('formulacion', <?php echo $borrador['id']; ?>, '<?php echo htmlspecialchars($borrador['nombre_borrador']); ?>')" title="Duplicar">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="empty-state p-3" id="empty-state-<?php echo $facultadId; ?>">
                                                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                                                    <p class="text-muted mb-0">No hay formulaciones con gestión desde facultades activada</p>
                                                    <p class="text-muted small mb-0 mt-2">
                                                        Para que aparezcan aquí, marca la opción:
                                                        <br>
                                                        <strong>"12. MARQUE: ✓ SI EL INDICADOR SERÁ GESTIONADO DESDE LAS FACULTADES"</strong>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="mt-3 text-end">
                                            <button class="btn btn-sm btn-success" onclick="abrirModalNuevoBorradorFacultad('<?php echo $facultadId; ?>', '<?php echo htmlspecialchars($facultad['nombre']); ?>')">
                                                <i class="fas fa-plus me-1"></i>Nueva Formulación
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <div class="desarrollo-section">
                                <div class="desarrollo-icon">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="desarrollo-title">NO HAY FACULTADES REGISTRADAS</div>
                                <div class="desarrollo-subtitle">
                                    No se encontraron facultades en la base de datos
                                </div>
                                <div class="desarrollo-badge">
                                    <i class="fas fa-info-circle me-2"></i>Contacte al administrador
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- Acordeón 3: Evaluación Líneas -->
        <div class="accordion mt-4" id="accordionEvaluacion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingEvaluacion">
                    <button class="accordion-button collapsed" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapseEvaluacion" 
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important;">
                        <i class="fas fa-chart-pie me-3 fa-2x"></i>
                        <div>
                            <span style="font-size: 1.3rem;">EVALUACIÓN LÍNEAS</span>
                            <br>
                            <small style="font-size: 0.85rem; opacity: 0.9;">Evaluación de líneas estratégicas - Módulo en desarrollo</small>
                        </div>
                    </button>
                </h2>
                <div id="collapseEvaluacion" 
                     class="accordion-collapse collapse" 
                     data-bs-parent="#accordionEvaluacion">
                    <div class="accordion-body p-4">
                        <div class="desarrollo-section">
                            <div class="desarrollo-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="desarrollo-title">EVALUACIÓN LÍNEAS</div>
                            <div class="desarrollo-subtitle">
                                Módulo en desarrollo - Próximamente disponible
                            </div>
                            <div class="desarrollo-badge">
                                <i class="fas fa-clock me-2"></i>Reservado para desarrollo
                            </div>
                            <div class="mt-4">
                                <p class="mb-0" style="opacity: 0.8; font-size: 0.9rem;">
                                    Este módulo permitirá la evaluación detallada de líneas estratégicas y su impacto en los diferentes proyectos institucionales.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <div class="auto-save-indicator" id="autoSaveIndicator">
        <i class="fas fa-check-circle me-2"></i> Guardado automático
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

    <div class="modal fade" id="modalNuevoBorradorFacultad" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Formulación - <span id="facultadNombreModal"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevoBorradorFacultad">
                    <input type="hidden" name="modulo" value="formulacion">
                    <input type="hidden" name="formulario_id" value="<?php echo $formulario['id']; ?>">
                    <input type="hidden" name="facultad_id" id="facultad_id_modal">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Formulación *</label>
                            <input type="text" class="form-control" name="nombre_borrador" id="nuevo_nombre_facultad" required placeholder="Ej: Plan Facultad 2025">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Crear Formulación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDuplicarBorrador" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);">
                    <h5 class="modal-title"><i class="fas fa-copy me-2"></i>Duplicar Formulación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formDuplicarBorrador">
                    <input type="hidden" name="modulo" id="duplicar_modulo">
                    <input type="hidden" name="id" id="duplicar_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Nueva Formulación *</label>
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

    <div class="modal fade" id="modalGestionSemestral" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-alt me-2"></i>GESTIÓN SEMESTRAL - <span id="gestionTituloSpan"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formGestionSemestral">
                    <input type="hidden" id="gestion_id" name="id">
                    
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-muted mb-3" style="border-bottom: 2px solid #FF9800; padding-bottom: 10px;">
                                    <i class="fas fa-archway me-2"></i>ARQUITECTURA
                                </h6>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">SEM. 1</label>
                                <input type="text" class="form-control" name="gestion_sem1" id="gestion_sem1" placeholder="Ingrese gestión semestre 1">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">SEM. 2</label>
                                <input type="text" class="form-control" name="gestion_sem2" id="gestion_sem2" placeholder="Ingrese gestión semestre 2">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">VIGENCIA</label>
                                <select class="form-select" name="vigencia" id="gestion_vigencia">
                                    <option value="">Seleccione vigencia</option>
                                    <?php for ($i = date('Y'); $i <= date('Y') + 5; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-white">
                                        <i class="fas fa-chart-line me-2"></i>SEGUIMIENTO (0/0)
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">DESCRIPCIÓN DE LA GESTIÓN</label>
                                            <textarea class="form-control" name="descripcion_gestion" id="gestion_descripcion" rows="4" placeholder="Describa la gestión realizada..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Guardar Gestión Semestral
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA FORMULACIÓN -->
    <div class="modal fade" id="modalFormulacion" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
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
                    
                    <div class="modal-body-scroll">
                        <div class="tab-content">
                            <!-- PESTAÑA 1: FORMULACIÓN -->
                            <div class="tab-pane fade show active" id="formulacion" role="tabpanel" aria-labelledby="tab-formulacion">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">1. LÍNEA ESTRATÉGICA</label>
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

                                    <div class="col-12 mb-3">
                                        <label class="form-label">2. OBJETIVO</label>
                                        <textarea class="form-control" name="objetivo" id="formulacion_objetivo" rows="3" readonly></textarea>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">3. ESTRATEGIA</label>
                                        <select class="form-select" name="estrategia" id="formulacion_estrategia" onchange="autoGuardarFormulacion(); validarPestanas()">
                                            <option value="">Seleccione una estrategia</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">4. MOTOR DE DESARROLLO</label>
                                        <select class="form-select" name="motor_desarrollo" id="formulacion_motor" onchange="cargarProyectosPorMotor(); autoGuardarFormulacion(); validarPestanas()">
                                            <option value="">Seleccione un motor de desarrollo</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">5. PROYECTO</label>
                                        <select class="form-select" name="proyecto" id="formulacion_proyecto" onchange="calcularAcumuladoActividades(); cargarPonderacionProyecto(); autoGuardarFormulacion(); validarPestanas()">
                                            <option value="">Seleccione un proyecto</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">6. META DE RESULTADO</label>
                                        <textarea class="form-control" name="meta_resultado" id="formulacion_meta" rows="2" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa la meta de resultado..."></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">7. PONDERACIÓN DE LOS PROYECTOS</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="ponderacion_proyectos" id="formulacion_ponderacion_proyectos" step="0.01" min="0" max="100" readonly placeholder="0.00" style="background-color: #e9ecef; cursor: not-allowed;">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">8. ACTIVIDAD DEL PROYECTO (205)</label>
                                        <textarea class="form-control" name="actividad_proyecto" id="formulacion_actividad" rows="4" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa la actividad del proyecto..."></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">9. PONDERACIÓN DE LAS ACTIVIDADES POR PROYECTO</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="ponderacion_actividades" id="formulacion_ponderacion_actividades" step="0.01" min="0" max="100" oninput="calcularAcumuladoActividades(); autoGuardarFormulacion(); validarPestanas()" placeholder="0.00">
                                            <span class="input-group-text">%</span>
                                            <span class="input-group-text px-3" id="acumulado_actividades_badge" 
                                                  title="Total acumulado para este proyecto (incluyendo este registro)"
                                                  style="font-size:0.82rem; font-weight:600; min-width:110px; justify-content:center; background:#f8f9fa; color:#6c757d; border-left: 2px solid #dee2e6;">
                                                Acum: — / 100%
                                            </span>
                                        </div>
                                        <div id="acumulado_actividades_msg" class="mt-1" style="font-size:0.8rem; display:none;"></div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">10. RESPONSABLE</label>
                                        <select class="form-select" name="responsable_formulacion_multi[]" id="formulacion_responsable" multiple="multiple">
                                            <?php foreach ($cargos as $cargo): ?>
                                            <option value="<?php echo htmlspecialchars($cargo['nombre']); ?>">
                                                <?php echo htmlspecialchars($cargo['nombre']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="responsable_formulacion" id="formulacion_responsable_hidden">
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">11. ID INDICADOR</label>
                                        <input type="text" class="form-control" name="id_indicador" id="formulacion_id_indicador" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Ingrese el ID del indicador">
                                    </div>

                                    <div class="col-12 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="gestionado_facultades" id="formulacion_gestionado_facultades" value="1" onchange="gestionarCheckboxFacultades(this)">
                                            <label class="form-check-label" for="formulacion_gestionado_facultades">
                                                <strong>12. MARQUE: ✓ SI EL INDICADOR SERÁ GESTIONADO DESDE LAS FACULTADES</strong>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PESTAÑA 2: INDICADOR DE RESULTADO -->
                            <div class="tab-pane fade" id="indicador" role="tabpanel" aria-labelledby="tab-indicador">
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <h5 class="indicador-title">INFORMACIÓN DEL INDICADOR</h5>
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label class="form-label">13.1 NOMBRE DEL INDICADOR</label>
                                        <input type="text" class="form-control" name="nombre_indicador" id="formulacion_nombre_indicador" oninput="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Ingrese el nombre del indicador">
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label class="form-label">13.2 FÓRMULA DE LA MEDICIÓN</label>
                                        <textarea class="form-control" name="formula_medicion" id="formulacion_formula_medicion" rows="3" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Ej: (Número de estudiantes graduados / Total de estudiantes matriculados) * 100"></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">13.3 FRECUENCIA DE MEDICIÓN</label>
                                            <select class="form-select" name="frecuencia_medicion" id="formulacion_frecuencia_medicion" onchange="autoGuardarFormulacion(); validarPestanas()">
                                                <option value="">Seleccione frecuencia</option>
                                                <option value="Mensual">Mensual</option>
                                                <option value="Bimestral">Bimestral</option>
                                                <option value="Trimestral">Trimestral</option>
                                                <option value="Semestral">Semestral</option>
                                                <option value="Anual">Anual</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">13.4 UNIDAD DE MEDIDA</label>
                                            <select class="form-select" name="unidad_medida" id="formulacion_unidad_medida" onchange="autoGuardarFormulacion(); validarPestanas()">
                                                <option value="">Seleccione unidad</option>
                                                <option value="Unidad">Unidad</option>
                                                <option value="Porcentaje">Porcentaje</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">13.5 AÑO</label>
                                            <select class="form-select" name="tipo_medicion" id="formulacion_tipo_medicion" onchange="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()">
                                                <option value="">Seleccione tipo</option>
                                                <option value="Acumulado">Acumulado</option>
                                                <option value="Nuevo gestionado durante la vigencia">Nuevo gestionado durante la vigencia</option>
                                                <option value="Promedio">Promedio</option>
                                                <option value="Último valor reportado">Último valor reportado</option>
                                                <option value="Límite">Límite</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label class="form-label">DESCRIPCIÓN DEL INDICADOR</label>
                                        <textarea class="form-control" name="descripcion_indicador" id="formulacion_descripcion_indicador" rows="4" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa detalladamente el indicador..."></textarea>
                                    </div>
                                    
                                    <div class="col-12 mt-4">
                                        <div class="meta-section">
                                            <h5 class="meta-title">METAS PROPUESTAS</h5>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">14.1 LÍNEA BASE</label>
                                                    <input type="text" class="form-control" name="linea_base_meta" id="formulacion_linea_base_meta" oninput="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Valor de línea base">
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">14.4 VALOR AÑO</label>
                                                    <input type="text" class="form-control" name="anio_base_meta" id="formulacion_anio_base_meta" readonly placeholder="Valor anual" style="background-color: #e8f5e9; font-weight: bold; color: #2e7d32;">
                                                </div>
                                                
                                                <div class="col-12">
                                                    <h6 class="mb-3" style="color: var(--color-primary);">METAS ANUALES</h6>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">14.2 SEMESTRE 1</label>
                                                    <input type="text" class="form-control" name="meta_s1" id="formulacion_meta_s1" oninput="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Meta Semestre 1">
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">14.3 SEMESTRE 2</label>
                                                    <input type="text" class="form-control" name="meta_s2" id="formulacion_meta_s2" oninput="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Meta Semestre 2">
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                            
                            <!-- PESTAÑA 3: PLANES INSTITUCIONALES -->
                            <div class="tab-pane fade" id="planes" role="tabpanel" aria-labelledby="tab-planes">
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <h5 class="indicador-title">PLANES INSTITUCIONALES</h5>
                                    </div>
                                    
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
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">PLANES SELECCIONADOS</label>
                                        <div id="contenedorPlanes" style="max-height: 300px; overflow-y: auto; border: 1px solid #ced4da; border-radius: 8px; padding: 10px; background-color: #f8f9fa;">
                                            <p class="text-muted text-center mb-0">No hay planes seleccionados</p>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="planes_institucionales" id="formulacion_planes_institucionales" value="">
                                </div>
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
        <div class="modal-dialog modal-xl">
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
                    
                    <div class="modal-body-scroll">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-success">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Datos de Formulación:</strong> Esta información proviene del formulario de formulación y no es editable.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-2 mb-3"><label class="form-label text-muted">AÑO</label><div class="bg-light-view" id="seguimiento_anio_view">-</div></div>
                            <div class="col-md-5 mb-3"><label class="form-label text-muted">LÍNEA ESTRATÉGICA</label><div class="bg-light-view" id="seguimiento_linea_view">-</div></div>
                            <div class="col-md-5 mb-3"><label class="form-label text-muted">OBJETIVO</label><div class="bg-light-view" id="seguimiento_objetivo_view">-</div></div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3"><label class="form-label text-muted">ESTRATEGIA</label><div class="bg-light-view" id="seguimiento_estrategia_view">-</div></div>
                            <div class="col-md-6 mb-3"><label class="form-label text-muted">MOTOR DE DESARROLLO</label><div class="bg-light-view" id="seguimiento_motor_view">-</div></div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3"><label class="form-label text-muted">META DE RESULTADO</label><div class="bg-light-view" id="seguimiento_meta_resultado_view">-</div></div>
                            <div class="col-md-6 mb-3"><label class="form-label text-muted">PROYECTO</label><div class="bg-light-view" id="seguimiento_proyecto_view">-</div></div>
                            <div class="col-md-3 mb-3"><label class="form-label text-muted">PONDERACIÓN PROYECTOS</label><div class="bg-light-view" id="seguimiento_ponderacion_proyectos_view">-</div></div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3"><label class="form-label text-muted">ACTIVIDAD DEL PROYECTO</label><div class="bg-light-view" id="seguimiento_actividad_view">-</div></div>
                            <div class="col-md-3 mb-3"><label class="form-label text-muted">PONDERACIÓN ACTIVIDADES</label><div class="bg-light-view" id="seguimiento_ponderacion_actividades_view">-</div></div>
                            <div class="col-md-3 mb-3"><label class="form-label text-muted">RESPONSABLE</label><div class="bg-light-view" id="seguimiento_responsable_view">-</div></div>
                        </div>
                        

                        
                        <div class="row mb-4">
                            <div class="col-12"><hr><h6 class="text-success"><i class="fas fa-chart-line me-2"></i>DATOS DE SEGUIMIENTO</h6></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">INDICADOR</label><input type="text" class="form-control" name="indicador" id="seguimiento_indicador" oninput="autoGuardarSeguimiento()"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">FECHA DE SEGUIMIENTO</label><input type="date" class="form-control" name="fecha_seguimiento" id="seguimiento_fecha" onchange="autoGuardarSeguimiento()"></div>
                            
                            <div class="col-md-4 mb-3"><label class="form-label">META PROGRAMADA</label><div class="input-group"><input type="number" class="form-control" name="meta_programada" id="seguimiento_meta_programada" step="0.01" oninput="autoGuardarSeguimiento()"><span class="input-group-text">$</span></div></div>
                            <div class="col-md-4 mb-3"><label class="form-label">META EJECUTADA</label><div class="input-group"><input type="number" class="form-control" name="meta_ejecutada" id="seguimiento_meta_ejecutada" step="0.01" oninput="autoGuardarSeguimiento()"><span class="input-group-text">$</span></div></div>
                            <div class="col-md-4 mb-3"><label class="form-label">% AVANCE</label><div class="input-group"><input type="number" class="form-control" name="porcentaje_avance" id="seguimiento_porcentaje" step="0.01" min="0" max="100" oninput="autoGuardarSeguimiento()"><span class="input-group-text">%</span></div></div>
                            
                            <div class="col-md-6 mb-3"><label class="form-label">SEMESTRE 1</label><input type="text" class="form-control" name="semestre1_seguimiento" id="seguimiento_semestre1" oninput="autoGuardarSeguimiento()"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">SEMESTRE 2</label><input type="text" class="form-control" name="semestre2_seguimiento" id="seguimiento_semestre2" oninput="autoGuardarSeguimiento()"></div>
                            
                            <div class="col-md-6 mb-3"><label class="form-label">RESPONSABLE DEL SEGUIMIENTO</label><input type="text" class="form-control" name="responsable_seguimiento" id="seguimiento_responsable" oninput="autoGuardarSeguimiento()"></div>
                            <div class="col-12 mb-3"><label class="form-label">OBSERVACIONES</label><textarea class="form-control" name="observaciones" id="seguimiento_observaciones" rows="4" oninput="autoGuardarSeguimiento()"></textarea></div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const basePath = '<?php echo $basePath; ?>';
        const formularioId = <?php echo $formulario['id']; ?>;
        const formularioAnio = <?php echo intval($formulario['anio'] ?? 0); ?>; // Año heredado del formulario padre
        
        // Datos de formulaciones existentes para calcular ponderación acumulada por proyecto
        const formulacionesExistentes = <?php
            $todas = [];
            $modulos_check = ['formulacion'];
            foreach ($modulos_check as $mk) {
                if (isset($datos_modulos[$mk])) {
                    foreach (['borradores', 'publicados'] as $estado) {
                        if (isset($datos_modulos[$mk][$estado])) {
                            foreach ($datos_modulos[$mk][$estado] as $b) {
                                if (!empty($b['proyecto']) && isset($b['ponderacion_actividades'])) {
                                    $todas[] = [
                                        'id' => $b['id'],
                                        'proyecto' => $b['proyecto'],
                                        'ponderacion_actividades' => floatval($b['ponderacion_actividades'])
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            echo json_encode($todas);
        ?>;

        let timeoutId = null;
        let currentModule = null;
        let editandoTitulo = false;
        let planesSeleccionados = [];
        
        // FUNCIÓN PARA OBTENER EL ÚLTIMO VALOR REPORTADO (Puesto 1: Línea Base, Puesto 2: Semestre 1, Puesto 3: Semestre 2)
        function obtenerUltimoValorReportado(lineaBase, metaS1, metaS2) {
            // Prioridad: Semestre 2 (puesto 3) -> Semestre 1 (puesto 2) -> Línea Base (puesto 1)
            if (metaS2 !== null && metaS2 !== undefined && metaS2 !== '' && !isNaN(metaS2)) {
                return parseFloat(metaS2);
            }
            if (metaS1 !== null && metaS1 !== undefined && metaS1 !== '' && !isNaN(metaS1)) {
                return parseFloat(metaS1);
            }
            if (lineaBase !== null && lineaBase !== undefined && lineaBase !== '' && !isNaN(lineaBase)) {
                return parseFloat(lineaBase);
            }
            return null;
        }
        
        // FUNCIÓN PARA CALCULAR EL VALOR ANUAL SEGÚN EL TIPO DE MEDICIÓN
        function calcularValorAnual() {
            const tipoMedicion = $('#formulacion_tipo_medicion').val();
            const lineaBaseRaw = $('#formulacion_linea_base_meta').val();
            const metaS1Raw = $('#formulacion_meta_s1').val();
            const metaS2Raw = $('#formulacion_meta_s2').val();
            
            const lineaBase = (lineaBaseRaw && lineaBaseRaw !== '') ? parseFloat(lineaBaseRaw) : null;
            const metaS1 = (metaS1Raw && metaS1Raw !== '') ? parseFloat(metaS1Raw) : null;
            const metaS2 = (metaS2Raw && metaS2Raw !== '') ? parseFloat(metaS2Raw) : null;
            
            let valorAnual = '';
            
            if (tipoMedicion === 'Acumulado') {
                // Acumulado = Línea Base + Semestre 1 + Semestre 2
                let suma = 0;
                if (lineaBase !== null) suma += lineaBase;
                if (metaS1 !== null) suma += metaS1;
                if (metaS2 !== null) suma += metaS2;
                valorAnual = suma.toFixed(2);
            } 
            else if (tipoMedicion === 'Nuevo gestionado durante la vigencia') {
                // Nuevo gestionado durante la vigencia = Semestre 1 + Semestre 2
                let suma = 0;
                if (metaS1 !== null) suma += metaS1;
                if (metaS2 !== null) suma += metaS2;
                valorAnual = suma.toFixed(2);
            }
            else if (tipoMedicion === 'Promedio') {
                // Promedio = (Semestre 1 + Semestre 2) / 2
                let suma = 0;
                let contador = 0;
                if (metaS1 !== null) { suma += metaS1; contador++; }
                if (metaS2 !== null) { suma += metaS2; contador++; }
                valorAnual = contador > 0 ? (suma / contador).toFixed(2) : '0.00';
            }
            else if (tipoMedicion === 'Último valor reportado') {
                // Último valor reportado = prioridad: Semestre 2 -> Semestre 1 -> Línea Base
                const ultimoValor = obtenerUltimoValorReportado(lineaBase, metaS1, metaS2);
                valorAnual = ultimoValor !== null ? ultimoValor.toFixed(2) : '';
            }
            else if (tipoMedicion === 'Límite') {
                // Límite = valor de Semestre 2
                valorAnual = metaS2 !== null ? metaS2.toFixed(2) : '';
            }
            else {
                valorAnual = '';
            }
            
            $('#formulacion_anio_base_meta').val(valorAnual);

            // Bloquear/desbloquear Línea Base y Semestre 1 según tipo Límite
            if (tipoMedicion === 'Límite') {
                $('#formulacion_linea_base_meta')
                    .prop('readonly', true)
                    .val('')
                    .css({ 'background-color': '#e9ecef', 'opacity': '0.6', 'cursor': 'not-allowed' });
                $('#formulacion_meta_s1')
                    .prop('readonly', true)
                    .val('')
                    .css({ 'background-color': '#e9ecef', 'opacity': '0.6', 'cursor': 'not-allowed' });
            } else {
                $('#formulacion_linea_base_meta')
                    .prop('readonly', false)
                    .css({ 'background-color': '', 'opacity': '', 'cursor': '' });
                $('#formulacion_meta_s1')
                    .prop('readonly', false)
                    .css({ 'background-color': '', 'opacity': '', 'cursor': '' });
            }
        }
        
        // ── ACUMULADO DE PONDERACIÓN DE ACTIVIDADES POR PROYECTO ────────────
        // Calcula cuánto % ya está usado por el proyecto seleccionado
        // en TODOS los registros (excluyendo el borrador que se está editando)
        function calcularAcumuladoActividades() {
            const proyectoSeleccionado = $('#formulacion_proyecto').val();
            const idActual = $('#formulacion_id').val() ? parseInt($('#formulacion_id').val()) : null;
            const valorActual = parseFloat($('#formulacion_ponderacion_actividades').val()) || 0;
            const badge = $('#acumulado_actividades_badge');
            const msg   = $('#acumulado_actividades_msg');
            const input = $('#formulacion_ponderacion_actividades');

            if (!proyectoSeleccionado) {
                badge.text('Acum: — / 100%').css({ color: '#6c757d', background: '#f8f9fa', borderColor: '#dee2e6' });
                msg.hide();
                return;
            }

            // Suma de otros registros con el mismo proyecto (excluye el actual)
            let acumuladoOtros = 0;
            formulacionesExistentes.forEach(function(f) {
                if (f.proyecto === proyectoSeleccionado && f.id !== idActual) {
                    acumuladoOtros += f.ponderacion_actividades;
                }
            });

            const totalConActual = acumuladoOtros + valorActual;
            const disponible     = 100 - acumuladoOtros;

            // Actualizar badge
            badge.text('Acum: ' + totalConActual.toFixed(2) + ' / 100%');

            if (totalConActual > 100) {
                // Excede el 100%
                badge.css({ color: '#fff', background: '#E74C3C', borderColor: '#E74C3C' });
                msg.html('<i class="fas fa-exclamation-triangle me-1"></i>Supera el 100%. Disponible: <strong>' + disponible.toFixed(2) + '%</strong>')
                   .css('color', '#E74C3C').show();
                input.css({ borderColor: '#E74C3C', boxShadow: '0 0 0 0.2rem rgba(231,76,60,0.25)' });
            } else if (totalConActual === 100) {
                // Completo justo
                badge.css({ color: '#fff', background: '#27AE60', borderColor: '#27AE60' });
                msg.html('<i class="fas fa-check-circle me-1"></i>Completo al 100%')
                   .css('color', '#27AE60').show();
                input.css({ borderColor: '#27AE60', boxShadow: '0 0 0 0.2rem rgba(39,174,96,0.25)' });
            } else if (totalConActual > 80) {
                // Advertencia: casi lleno
                badge.css({ color: '#fff', background: '#F39C12', borderColor: '#F39C12' });
                msg.html('<i class="fas fa-info-circle me-1"></i>Disponible: <strong>' + disponible.toFixed(2) + '%</strong>')
                   .css('color', '#F39C12').show();
                input.css({ borderColor: '#F39C12', boxShadow: '0 0 0 0.2rem rgba(243,156,18,0.25)' });
            } else {
                // Normal
                badge.css({ color: '#2C3E50', background: '#f0f8ff', borderColor: '#3498DB' });
                msg.html('<i class="fas fa-info-circle me-1"></i>Disponible: <strong>' + disponible.toFixed(2) + '%</strong>')
                   .css('color', '#3498DB').show();
                input.css({ borderColor: '#ced4da', boxShadow: '' });
            }
        }
        // ────────────────────────────────────────────────────────────────────
        
        // ── CARGA AUTOMÁTICA DE PONDERACIÓN DE PROYECTOS DESDE data_proyectos ──
        function cargarPonderacionProyecto() {
            const selectProyecto = document.getElementById('formulacion_proyecto');
            const proyectoOption = selectProyecto ? selectProyecto.options[selectProyecto.selectedIndex] : null;
            const proyectoId = proyectoOption ? proyectoOption.getAttribute('data-proyecto-id') : null;

            const selectMotor = document.getElementById('formulacion_motor');
            const motorOption = selectMotor ? selectMotor.options[selectMotor.selectedIndex] : null;
            const motorId = motorOption ? motorOption.getAttribute('data-motor-id') : null;

            // El año viene del formulario padre (formularios.anio)
            const anio = formularioAnio;

            const input = $('#formulacion_ponderacion_proyectos');

            if (!proyectoId || !motorId || !anio) {
                input.val('');
                return;
            }

            $.ajax({
                url: basePath + '/modulo144/getPonderacionProyecto',
                type: 'GET',
                data: { proyecto_id: proyectoId, motor_id: motorId, anio: anio },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.porcentaje !== null) {
                        input.val(parseFloat(response.porcentaje).toFixed(2));
                    } else {
                        input.val('');
                    }
                    autoGuardarFormulacion();
                    validarPestanas();
                },
                error: function() {
                    input.val('');
                }
            });
        }
        // ────────────────────────────────────────────────────────────────────

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

        function gestionarCheckboxFacultades(checkbox) {
            const id = $('#formulacion_id').val();
            const estadoActual = checkbox.checked;
            
            Swal.fire({
                title: estadoActual ? '¿Activar gestión desde facultades?' : '¿Desactivar gestión desde facultades?',
                html: estadoActual ? 
                    'Se creará un formulario de SEGUIMIENTO para cada facultad basado en esta formulación.' :
                    'Se ELIMINARÁN todos los formularios de seguimiento asociados a esta formulación en las facultades. Esta acción no se puede deshacer.',
                icon: estadoActual ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: estadoActual ? '#27AE60' : '#E74C3C',
                confirmButtonText: estadoActual ? 'Sí, activar' : 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: estadoActual ? 'Activando...' : 'Desactivando...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    
                    const data = {
                        modulo: 'formulacion',
                        id: id,
                        gestionado_facultades: estadoActual ? 1 : 0
                    };
                    
                    $.ajax({
                        url: basePath + '/modulo144/guardar',
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: estadoActual ? '¡Activado!' : '¡Desactivado!',
                                    text: estadoActual ? 'Se ha activado la gestión desde facultades.' : 'Se ha desactivado la gestión desde facultades.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                checkbox.checked = !estadoActual;
                                Swal.fire('Error', response.message || 'No se pudo guardar el cambio', 'error');
                            }
                        },
                        error: function() {
                            checkbox.checked = !estadoActual;
                            Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
                        }
                    });
                } else {
                    checkbox.checked = !estadoActual;
                }
            });
        }

        function abrirModalNuevoBorrador(modulo) {
            $('#nuevo_modulo').val(modulo);
            $('#nuevo_nombre').val('');
            $('#modalNuevoBorrador').modal('show');
        }

        function abrirModalNuevoBorradorFacultad(facultadId, facultadNombre) {
            $('#facultad_id_modal').val(facultadId);
            $('#facultadNombreModal').text(facultadNombre);
            $('#nuevo_nombre_facultad').val('Formulación ' + facultadNombre);
            $('#modalNuevoBorradorFacultad').modal('show');
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

        $('#formNuevoBorradorFacultad').on('submit', function(e) {
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

        function abrirGestionSemestral(id, nombre) {
            $('#gestion_id').val(id);
            $('#gestionTituloSpan').text(nombre);
            
            $.ajax({
                url: basePath + '/modulo144/getBorrador?modulo=formulacion&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const b = response.borrador;
                        $('#gestion_sem1').val(b.gestion_sem1 || '');
                        $('#gestion_sem2').val(b.gestion_sem2 || '');
                        $('#gestion_vigencia').val(b.vigencia || '');
                        $('#gestion_descripcion').val(b.descripcion_gestion || '');
                    }
                }
            });
            
            $('#modalGestionSemestral').modal('show');
        }

        $('#formGestionSemestral').on('submit', function(e) {
            e.preventDefault();
            
            const data = {
                id: $('#gestion_id').val(),
                gestion_sem1: $('#gestion_sem1').val(),
                gestion_sem2: $('#gestion_sem2').val(),
                vigencia: $('#gestion_vigencia').val(),
                descripcion_gestion: $('#gestion_descripcion').val()
            };
            
            $.ajax({
                url: basePath + '/modulo144/guardarGestionSemestral',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Guardado!', response.message, 'success');
                        $('#modalGestionSemestral').modal('hide');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
                }
            });
        });

        function validarPestanas() {
            const camposFormulacion = [
                $('#formulacion_linea').val(),
                $('#formulacion_estrategia').val(),
                $('#formulacion_motor').val(),
                $('#formulacion_proyecto').val(),
                $('#formulacion_meta').val(),
                $('#formulacion_ponderacion_proyectos').val(),
                $('#formulacion_actividad').val(),
                $('#formulacion_ponderacion_actividades').val(),
                $('#formulacion_responsable_hidden').val(),
                $('#formulacion_id_indicador').val()
            ];
            
            const formulacionCompleta = camposFormulacion.every(valor => valor && valor.trim() !== '');
            
            const camposIndicador = [
                $('#formulacion_nombre_indicador').val(),
                $('#formulacion_formula_medicion').val(),
                $('#formulacion_frecuencia_medicion').val(),
                $('#formulacion_unidad_medida').val(),
                $('#formulacion_tipo_medicion').val(),
                $('#formulacion_descripcion_indicador').val(),
                $('#formulacion_linea_base_meta').val(),
                $('#formulacion_meta_s1').val(),
                $('#formulacion_meta_s2').val()
            ];
            
            const indicadorCompleto = camposIndicador.every(valor => valor && valor.trim() !== '');
            const planesCompleto = true;
            
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

        function cargarObjetivoYestrategias() {
            const selectLinea = document.getElementById('formulacion_linea');
            const selectedOption = selectLinea.options[selectLinea.selectedIndex];
            
            if (!selectedOption || !selectedOption.value) {
                document.getElementById('formulacion_objetivo').value = '';
                document.getElementById('formulacion_estrategia').innerHTML = '<option value="">Seleccione una estrategia</option>';
                validarPestanas();
                return;
            }
            
            const objetivo = selectedOption.getAttribute('data-objetivo') || '';
            document.getElementById('formulacion_objetivo').value = objetivo;
            
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
                    data: { linea_id: lineaId, motor_id: motorId },
                    dataType: 'json',
                    success: function(response) {
                        const selectProyecto = document.getElementById('formulacion_proyecto');
                        selectProyecto.innerHTML = '<option value="">Seleccione un proyecto</option>';
                        
                        if (response.success && response.proyectos && response.proyectos.length > 0) {
                            response.proyectos.forEach(function(proyecto) {
                                const option = document.createElement('option');
                                option.value = proyecto.nombre;
                                option.setAttribute('data-proyecto-id', proyecto.id);
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

        function agregarPlan() {
            const select = document.getElementById('selectPlanInstitucional');
            const planSeleccionado = select.value;
            
            if (!planSeleccionado) {
                Swal.fire('Atención', 'Por favor seleccione un plan', 'warning');
                return;
            }
            
            if (planesSeleccionados.includes(planSeleccionado)) {
                Swal.fire('Atención', 'Este plan ya ha sido agregado', 'info');
                return;
            }
            
            planesSeleccionados.push(planSeleccionado);
            actualizarContenedorPlanes();
            select.value = '';
            actualizarCampoOculto();
            guardarPlanesInmediatamente();
        }

        function eliminarPlan(plan) {
            planesSeleccionados = planesSeleccionados.filter(p => p !== plan);
            actualizarContenedorPlanes();
            actualizarCampoOculto();
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
                        mostrarAutoSaveIndicator();
                    }
                }
            });
        }

        function actualizarContenedorPlanes() {
            const contenedor = document.getElementById('contenedorPlanes');
            
            if (planesSeleccionados.length === 0) {
                contenedor.innerHTML = '<p class="text-muted text-center mb-0">No hay planes seleccionados</p>';
                return;
            }
            
            let html = '';
            planesSeleccionados.forEach((plan, index) => {
                let colorClass = index % 3 === 0 ? 'bg-primary' : (index % 3 === 1 ? 'bg-success' : 'bg-info');
                const planEscaped = plan.replace(/'/g, "\\'");
                html += `<div class="d-flex justify-content-between align-items-center mb-2 p-2 ${colorClass} text-white rounded" style="opacity: 0.9;">
                            <span><strong>${index + 1}:</strong> ${plan}</span>
                            <button type="button" class="btn btn-sm btn-light" onclick="eliminarPlan('${planEscaped}')">
                                <i class="fas fa-times text-danger"></i>
                            </button>
                        </div>`;
            });
            contenedor.innerHTML = html;
        }

        function actualizarCampoOculto() {
            document.getElementById('formulacion_planes_institucionales').value = JSON.stringify(planesSeleccionados);
        }

        function cargarPlanesDesdeBD(planesJSON) {
            if (planesJSON && planesJSON !== '[]' && planesJSON !== '') {
                try {
                    planesSeleccionados = JSON.parse(planesJSON);
                } catch (e) {
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
            
            const modalHeader = modulo === 'formulacion' ? $('#modalFormulacion .modal-header h5') : $('#modalSeguimiento .modal-header h5');
            const tituloActual = modulo === 'formulacion' ? $('#tituloFormulacionSpan').text() : $('#tituloSeguimientoSpan').text();
            
            modalHeader.html(`<input type="text" class="modal-title-input" id="editTituloInput" value="${tituloActual}" />`);
            $('#editTituloInput').focus();
            editandoTitulo = true;
            
            $('#editTituloInput').on('blur', function() { guardarTituloModal(modulo, $(this).val()); })
                .on('keypress', function(e) { if (e.which === 13) guardarTituloModal(modulo, $(this).val()); });
        }

        function guardarTituloModal(modulo, nuevoTitulo) {
            if (!nuevoTitulo.trim()) { restaurarTituloModal(modulo); return; }

            const id = modulo === 'formulacion' ? $('#formulacion_id').val() : $('#seguimiento_id').val();
            
            $.ajax({
                url: basePath + '/modulo144/guardar',
                type: 'POST',
                data: { modulo: modulo, id: id, nombre_borrador: nuevoTitulo },
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
                        mostrarAutoSaveIndicator();
                    } else { restaurarTituloModal(modulo); }
                },
                error: function() { restaurarTituloModal(modulo); }
            });
        }

        function restaurarTituloModal(modulo) {
            const tituloActual = modulo === 'formulacion' ? $('#tituloFormulacionSpan').text() : $('#tituloSeguimientoSpan').text();
            if (modulo === 'formulacion') {
                $('#modalFormulacion .modal-header h5').html(`<i class="fas fa-clipboard-list me-2"></i>FORMULACIÓN 144 - <span id="tituloFormulacionSpan">${tituloActual}</span>`);
            } else {
                $('#modalSeguimiento .modal-header h5').html(`<i class="fas fa-chart-line me-2"></i>SEGUIMIENTO 144 - <span id="tituloSeguimientoSpan">${tituloActual}</span>`);
            }
            editandoTitulo = false;
        }

        function cargarDatosFormulacionEnSeguimiento(b) {
            let valorAnual = '-';
            const lineaBaseRaw = b.linea_base_meta;
            const metaS1Raw = b.meta_s1;
            const metaS2Raw = b.meta_s2;
            
            const lineaBase = (lineaBaseRaw && lineaBaseRaw !== '') ? parseFloat(lineaBaseRaw) : null;
            const metaS1 = (metaS1Raw && metaS1Raw !== '') ? parseFloat(metaS1Raw) : null;
            const metaS2 = (metaS2Raw && metaS2Raw !== '') ? parseFloat(metaS2Raw) : null;
            
            if (b.tipo_medicion === 'Acumulado') {
                let suma = 0;
                if (lineaBase !== null) suma += lineaBase;
                if (metaS1 !== null) suma += metaS1;
                if (metaS2 !== null) suma += metaS2;
                valorAnual = suma.toFixed(2);
            } 
            else if (b.tipo_medicion === 'Nuevo gestionado durante la vigencia') {
                let suma = 0;
                if (metaS1 !== null) suma += metaS1;
                if (metaS2 !== null) suma += metaS2;
                valorAnual = suma.toFixed(2);
            }
            else if (b.tipo_medicion === 'Promedio') {
                let suma = 0;
                let contador = 0;
                if (metaS1 !== null) { suma += metaS1; contador++; }
                if (metaS2 !== null) { suma += metaS2; contador++; }
                valorAnual = contador > 0 ? (suma / contador).toFixed(2) : '0.00';
            }
            else if (b.tipo_medicion === 'Último valor reportado') {
                let ultimoValor = null;
                if (metaS2 !== null && metaS2 !== undefined && metaS2 !== '' && !isNaN(metaS2)) {
                    ultimoValor = metaS2;
                } else if (metaS1 !== null && metaS1 !== undefined && metaS1 !== '' && !isNaN(metaS1)) {
                    ultimoValor = metaS1;
                } else if (lineaBase !== null && lineaBase !== undefined && lineaBase !== '' && !isNaN(lineaBase)) {
                    ultimoValor = lineaBase;
                }
                valorAnual = ultimoValor !== null ? ultimoValor.toFixed(2) : '';
            }
            
            $('#seguimiento_anio_view').text(valorAnual);
            $('#seguimiento_linea_view').text(b.linea_estrategica || '-');
            $('#seguimiento_objetivo_view').text(b.objetivo || '-');
            $('#seguimiento_estrategia_view').text(b.estrategia || '-');
            $('#seguimiento_motor_view').text(b.motor_desarrollo || '-');
            $('#seguimiento_meta_resultado_view').text(b.meta_resultado || '-');
            $('#seguimiento_proyecto_view').text(b.proyecto || '-');
            $('#seguimiento_ponderacion_proyectos_view').text(b.ponderacion_proyectos ? b.ponderacion_proyectos + '%' : '-');
            $('#seguimiento_actividad_view').text(b.actividad_proyecto || '-');
            $('#seguimiento_ponderacion_actividades_view').text(b.ponderacion_actividades ? b.ponderacion_actividades + '%' : '-');
            $('#seguimiento_responsable_view').text(b.responsable_formulacion || '-');
        }

        function verSeguimiento(modulo, id) { editarBorrador(modulo, id); }

        function autoGuardarFormulacion() {
            const id = $('#formulacion_id').val();
            if (!id) return;
            
            if (timeoutId) clearTimeout(timeoutId);
            
            timeoutId = setTimeout(function() {
                const gestionado = $('#formulacion_gestionado_facultades').is(':checked') ? 1 : 0;
                
                const data = {
                    modulo: 'formulacion', id: id,
                    anio: formularioAnio,
                    linea_estrategica: $('#formulacion_linea').val(),
                    objetivo: $('#formulacion_objetivo').val(),
                    estrategia: $('#formulacion_estrategia').val(),
                    motor_desarrollo: $('#formulacion_motor').val(),
                    proyecto: $('#formulacion_proyecto').val(),
                    meta_resultado: $('#formulacion_meta').val(),
                    ponderacion_proyectos: $('#formulacion_ponderacion_proyectos').val(),
                    actividad_proyecto: $('#formulacion_actividad').val(),
                    ponderacion_actividades: $('#formulacion_ponderacion_actividades').val(),
                    responsable_formulacion: $('#formulacion_responsable_hidden').val(),
                    id_indicador: $('#formulacion_id_indicador').val(),
                    gestionado_facultades: gestionado,
                    nombre_indicador: $('#formulacion_nombre_indicador').val(),
                    formula_medicion: $('#formulacion_formula_medicion').val(),
                    frecuencia_medicion: $('#formulacion_frecuencia_medicion').val(),
                    unidad_medida: $('#formulacion_unidad_medida').val(),
                    tipo_medicion: $('#formulacion_tipo_medicion').val(),
                    descripcion_indicador: $('#formulacion_descripcion_indicador').val(),
                    linea_base_meta: $('#formulacion_linea_base_meta').val(),
                    anio_base_meta: $('#formulacion_anio_base_meta').val(),
                    meta_s1: $('#formulacion_meta_s1').val(),
                    meta_s2: $('#formulacion_meta_s2').val(),
                    planes_institucionales: $('#formulacion_planes_institucionales').val()
                };
                
                $.ajax({
                    url: basePath + '/modulo144/guardar',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mostrarAutoSaveIndicator();
                            validarPestanas();
                        }
                    }
                });
            }, 500);
        }

        function autoGuardarSeguimiento() {
            const id = $('#seguimiento_id').val();
            if (!id) return;
            
            if (timeoutId) clearTimeout(timeoutId);
            
            timeoutId = setTimeout(function() {
                const data = {
                    modulo: 'seguimiento', id: id,
                    indicador: $('#seguimiento_indicador').val(),
                    fecha_seguimiento: $('#seguimiento_fecha').val(),
                    semestre1_seguimiento: $('#seguimiento_semestre1').val(),
                    semestre2_seguimiento: $('#seguimiento_semestre2').val(),
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
                        if (response.success) mostrarAutoSaveIndicator();
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
            setTimeout(function() { indicator.style.display = 'none'; }, 2000);
        }

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
                            $('#tituloFormulacionSpan').text(b.nombre_borrador);
                            $('#formulacion_linea').val(b.linea_estrategica);
                            $('#formulacion_objetivo').val(b.objetivo);
                            $('#formulacion_tipo_medicion').val(b.tipo_medicion);
                            
                            const selectLinea = document.getElementById('formulacion_linea');
                            const lineaOption = selectLinea.options[selectLinea.selectedIndex];
                            const lineaId = lineaOption ? lineaOption.getAttribute('data-id') : null;
                            
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
                                                if (valorEstrategia) $('#formulacion_estrategia').val(valorEstrategia);
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
                                                    setTimeout(function() { cargarProyectosPorMotorConValor(b.proyecto); }, 300);
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
                            
                            function cargarProyectosPorMotorConValor(valorProyecto) {
                                const selectMotor = document.getElementById('formulacion_motor');
                                const motorOption = selectMotor.options[selectMotor.selectedIndex];
                                const motorId = motorOption ? motorOption.getAttribute('data-motor-id') : null;
                                
                                if (lineaId && motorId) {
                                    document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Cargando proyectos...</option>';
                                    $.ajax({
                                        url: basePath + '/modulo144/getProyectosPorLineaYMotor',
                                        type: 'GET',
                                        data: { linea_id: lineaId, motor_id: motorId },
                                        dataType: 'json',
                                        success: function(res) {
                                            const selectProyecto = document.getElementById('formulacion_proyecto');
                                            selectProyecto.innerHTML = '<option value="">Seleccione un proyecto</option>';
                                            if (res.success && res.proyectos && res.proyectos.length > 0) {
                                                res.proyectos.forEach(function(proyecto) {
                                                    const option = document.createElement('option');
                                                    option.value = proyecto.nombre;
                                                    option.setAttribute('data-proyecto-id', proyecto.id);
                                                    option.textContent = proyecto.codigo + ' - ' + proyecto.nombre;
                                                    selectProyecto.appendChild(option);
                                                });
                                                if (valorProyecto) {
                                                    $('#formulacion_proyecto').val(valorProyecto);
                                                    setTimeout(cargarPonderacionProyecto, 100);
                                                }
                                            } else {
                                                selectProyecto.innerHTML = '<option value="">No hay proyectos disponibles</option>';
                                            }
                                            validarPestanas();
                                        }
                                    });
                                }
                            }
                            
                            cargarEstrategias(lineaId, b.estrategia);
                            cargarMotores(lineaId, b.motor_desarrollo);
                            
                            $('#formulacion_meta').val(b.meta_resultado);
                            $('#formulacion_ponderacion_proyectos').val(b.ponderacion_proyectos);
                            $('#formulacion_actividad').val(b.actividad_proyecto);
                            $('#formulacion_ponderacion_actividades').val(b.ponderacion_actividades);
                            const responsableGuardado = b.responsable_formulacion || '';
                            $('#formulacion_responsable_hidden').val(responsableGuardado);
                            const responsablesArray = responsableGuardado
                                ? responsableGuardado.split(',').map(s => s.trim()).filter(s => s !== '')
                                : [];
                            $('#formulacion_responsable').val(responsablesArray).trigger('change.select2');
                            $('#formulacion_id_indicador').val(b.id_indicador);
                            $('#formulacion_gestionado_facultades').prop('checked', b.gestionado_facultades == 1);
                            $('#formulacion_nombre_indicador').val(b.nombre_indicador);
                            $('#formulacion_formula_medicion').val(b.formula_medicion);
                            $('#formulacion_frecuencia_medicion').val(b.frecuencia_medicion);
                            $('#formulacion_unidad_medida').val(b.unidad_medida);
                            $('#formulacion_descripcion_indicador').val(b.descripcion_indicador);
                            $('#formulacion_linea_base_meta').val(b.linea_base_meta);
                            $('#formulacion_meta_s1').val(b.meta_s1);
                            $('#formulacion_meta_s2').val(b.meta_s2);
                            
                            // Calcular valor anual después de cargar valores
                            calcularValorAnual();
                            setTimeout(calcularAcumuladoActividades, 400);
                            
                            if (b.planes_institucionales) {
                                cargarPlanesDesdeBD(b.planes_institucionales);
                            } else {
                                planesSeleccionados = [];
                                actualizarContenedorPlanes();
                                actualizarCampoOculto();
                            }
                            
                            $('#modalFormulacion').modal('show');
                            setTimeout(validarPestanas, 500);
                        } else {
                            $('#seguimiento_id').val(b.id);
                            $('#tituloSeguimientoSpan').text(b.nombre_borrador);
                            $('#seguimiento_indicador').val(b.indicador);
                            $('#seguimiento_fecha').val(b.fecha_seguimiento);
                            $('#seguimiento_semestre1').val(b.semestre1_seguimiento);
                            $('#seguimiento_semestre2').val(b.semestre2_seguimiento);
                            $('#seguimiento_meta_programada').val(b.meta_programada);
                            $('#seguimiento_meta_ejecutada').val(b.meta_ejecutada);
                            $('#seguimiento_porcentaje').val(b.porcentaje_avance);
                            $('#seguimiento_responsable').val(b.responsable_seguimiento);
                            $('#seguimiento_observaciones').val(b.observaciones);
                            cargarDatosFormulacionEnSeguimiento(b);
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
                title: estado === 2 ? '¿Publicar formulación?' : '¿Cancelar formulación?',
                text: estado === 2 ? 'Esta formulación pasará a estado PUBLICADO' : 'Esta formulación pasará a estado CANCELADO',
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
                title: '¿Eliminar formulación?',
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

        $(document).ready(function() {
            console.log('=== SISTEMA CARGADO CORRECTAMENTE ===');

            // ── SELECT2: Responsable múltiple con buscador (máx. 3) ─────────
            $('#formulacion_responsable').select2({
                placeholder: 'Seleccione uno o más responsables',
                allowClear: true,
                maximumSelectionLength: 3,
                language: {
                    noResults: function() { return 'No se encontraron resultados'; },
                    searching:  function() { return 'Buscando...'; },
                    maximumSelected: function() {
                        return '<span class="select2-max-reached">Máximo 3 responsables permitidos</span>';
                    }
                },
                escapeMarkup: function(m) { return m; },
                dropdownParent: $('#modalFormulacion')
            });

            $('#formulacion_responsable').on('change', function() {
                const seleccionados = $(this).val() || [];
                $('#formulacion_responsable_hidden').val(seleccionados.join(', '));
                autoGuardarFormulacion();
                validarPestanas();
            });
            // ────────────────────────────────────────────────────────────────

            $('#modalFormulacion').on('shown.bs.modal', function() {
                validarPestanas();
                calcularValorAnual();
            });
            $('#formulacion_nombre_indicador, #formulacion_formula_medicion, #formulacion_frecuencia_medicion, #formulacion_unidad_medida, #formulacion_tipo_medicion, #formulacion_descripcion_indicador, #formulacion_linea_base_meta, #formulacion_meta_s1, #formulacion_meta_s2, #formulacion_gestionado_facultades').on('input change', function() {
                calcularValorAnual();
            });
        });
    </script>
</body>
</html>