<?php
// vista/modulo144/test.php
$basePath = Config::getBasePath();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TEST - Sistema 144 (ID: <?php echo $id; ?>)</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --color-primary: #2C3E50;
            --color-success: #27AE60;
            --color-danger: #E74C3C;
            --color-warning: #F39C12;
            --color-info: #3498DB;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
            min-height: 100vh;
        }
        
        .test-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            border-left: 5px solid var(--color-primary);
        }
        
        .module-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }
        
        .module-card:hover {
            transform: translateY(-5px);
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 600;
            color: white;
            display: inline-block;
        }
        
        .status-success {
            background: linear-gradient(135deg, var(--color-success) 0%, #2ECC71 100%);
        }
        
        .status-danger {
            background: linear-gradient(135deg, var(--color-danger) 0%, #C0392B 100%);
        }
        
        .status-warning {
            background: linear-gradient(135deg, var(--color-warning) 0%, #E67E22 100%);
        }
        
        .info-table {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary) 0%, #34495E 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44,62,80,0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--color-success) 0%, #2ECC71 100%);
            border: none;
        }
        
        .btn-info {
            background: linear-gradient(135deg, var(--color-info) 0%, #2980B9 100%);
            border: none;
            color: white;
        }
        
        .stat-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            color: white;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <!-- HEADER -->
        <div class="header-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2" style="color: var(--color-primary);">
                        <i class="fas fa-vial me-3"></i>SISTEMA 144 - TEST
                    </h1>
                    <h4 class="mb-0">Formulario ID: <?php echo $id; ?></h4>
                    <p class="mb-0 mt-2 text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo htmlspecialchars($formulario['titulo'] ?? 'Sin título'); ?>
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-secondary me-2">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Inicio: <?php echo !empty($formulario['fecha_inicio']) ? date('d/m/Y H:i', strtotime($formulario['fecha_inicio'])) : 'No definido'; ?>
                        </span>
                        <span class="badge bg-secondary">
                            <i class="fas fa-calendar-times me-1"></i>
                            Cierre: <?php echo !empty($formulario['fecha_cierre']) ? date('d/m/Y H:i', strtotime($formulario['fecha_cierre'])) : 'No definido'; ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="<?php echo $basePath; ?>/modulo144?id=<?php echo $id; ?>" class="btn btn-primary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                    <a href="<?php echo $basePath; ?>/FOR-DE-144" class="btn btn-secondary">
                        <i class="fas fa-folder me-1"></i>Formularios
                    </a>
                </div>
            </div>
        </div>

        <!-- RESUMEN GENERAL -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Módulos</h5>
                        <h2><?php echo count($modulos); ?></h2>
                        <small>Módulos configurados</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Tablas OK</h5>
                        <h2>
                            <?php 
                            $tablas_ok = 0;
                            foreach ($datos_modulos as $m) {
                                if ($m['tabla_existe']) $tablas_ok++;
                            }
                            echo $tablas_ok . '/' . count($modulos);
                            ?>
                        </h2>
                        <small>Tablas existentes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Borradores</h5>
                        <h2>
                            <?php 
                            $total_borradores = 0;
                            foreach ($datos_modulos as $m) {
                                $total_borradores += count($m['borradores']);
                            }
                            echo $total_borradores;
                            ?>
                        </h2>
                        <small>Total en todos los módulos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Publicados</h5>
                        <h2>
                            <?php 
                            $total_publicados = 0;
                            foreach ($datos_modulos as $m) {
                                $total_publicados += count($m['publicados']);
                            }
                            echo $total_publicados;
                            ?>
                        </h2>
                        <small>Total en todos los módulos</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESULTADOS POR MÓDULO -->
        <h3 class="text-white mb-3"><i class="fas fa-cubes me-2"></i>Resultados por Módulo</h3>
        
        <?php foreach ($resultados_tests as $test): ?>
        <div class="module-card">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h4 class="mb-1" style="color: <?php echo $test['modulo'] == 'FORMULACIÓN 144' ? '#2C3E50' : '#27AE60'; ?>;">
                        <?php echo $test['modulo']; ?>
                    </h4>
                    <small class="text-muted"><?php echo $test['tabla']; ?></small>
                </div>
                <div class="col-md-2">
                    <?php if ($test['tabla_existe']): ?>
                        <span class="status-badge status-success">
                            <i class="fas fa-check-circle me-1"></i>Tabla OK
                        </span>
                    <?php else: ?>
                        <span class="status-badge status-danger">
                            <i class="fas fa-times-circle me-1"></i>No existe
                        </span>
                    <?php endif; ?>
                </div>
                <div class="col-md-5">
                    <div class="d-flex gap-4">
                        <div class="text-center">
                            <div class="stat-circle bg-secondary mb-2"><?php echo $test['borradores']; ?></div>
                            <small>Borradores</small>
                        </div>
                        <div class="text-center">
                            <div class="stat-circle bg-success mb-2"><?php echo $test['publicados']; ?></div>
                            <small>Publicados</small>
                        </div>
                        <div class="text-center">
                            <div class="stat-circle bg-danger mb-2"><?php echo $test['cancelados']; ?></div>
                            <small>Cancelados</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    <?php if (!$test['tabla_existe']): ?>
                        <button class="btn btn-sm btn-danger" onclick="alert('Ejecutar SQL:\nCREATE TABLE <?php echo $test['tabla']; ?> (...)')">
                            <i class="fas fa-database me-1"></i>Crear tabla
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($test['borradores'] > 0 || $test['publicados'] > 0 || $test['cancelados'] > 0): ?>
            <div class="info-table mt-3">
                <h6 class="mb-2"><i class="fas fa-list me-2"></i>Últimos registros:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $modulo_key = array_search($test['modulo'], array_column($modulos, 'nombre'));
                            $modulo_data = $datos_modulos[array_keys($modulos)[$modulo_key]];
                            $todos = array_merge($modulo_data['borradores'], $modulo_data['publicados'], $modulo_data['cancelados']);
                            usort($todos, function($a, $b) {
                                return strtotime($b['fecha_creacion']) - strtotime($a['fecha_creacion']);
                            });
                            $todos = array_slice($todos, 0, 3);
                            ?>
                            <?php foreach ($todos as $item): ?>
                            <tr>
                                <td><?php echo $item['id']; ?></td>
                                <td><?php echo htmlspecialchars($item['nombre_borrador']); ?></td>
                                <td>
                                    <?php if ($item['estado'] == 0): ?>
                                        <span class="badge bg-secondary">Borrador</span>
                                    <?php elseif ($item['estado'] == 1): ?>
                                        <span class="badge bg-danger">Cancelado</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Publicado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($item['fecha_creacion'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <!-- INFORMACIÓN DEL SISTEMA -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-info-circle me-2"></i>Información del Sistema
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>PHP Version</span>
                                <strong><?php echo phpversion(); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Servidor</span>
                                <strong><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Base de Datos</span>
                                <strong><?php echo Config::DB_NAME; ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Fecha/Hora</span>
                                <strong><?php echo date('d/m/Y H:i:s'); ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-tools me-2"></i>Acciones Rápidas
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo $basePath; ?>/modulo144?id=<?php echo $id; ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Ir al Módulo 144
                            </a>
                            <a href="<?php echo $basePath; ?>/FOR-DE-144" class="btn btn-info">
                                <i class="fas fa-folder me-2"></i>Gestión de Formularios
                            </a>
                            <button class="btn btn-success" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-2"></i>Ejecutar Test Nuevamente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="text-center text-white mt-4">
            <small>Sistema 144 - Test ID: <?php echo $id; ?> | <?php echo date('Y-m-d H:i:s'); ?></small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>