<?php
// controlador/Formulacion144Controller.php
require_once 'modelo/Formulacion144Model.php';

class Formulacion144Controller {
    private $model;

    public function __construct() {
        $this->model = new Formulacion144Model();
    }

    // ============= PÁGINA PRINCIPAL =============
    public function index() {
        $id = $_GET['id'] ?? 0;
        
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            $this->mostrarError('ID de formulario no válido', $id);
            return;
        }

        $formulario = $this->model->verificarFormulario($id);
        
        if (!$formulario) {
            $this->mostrarError('El formulario solicitado NO EXISTE en la tabla formularios', $id);
            return;
        }

        // Verificar estado de fechas
        $estado_fechas = $this->model->verificarFechaHabil($formulario);
        
        $borradores = $this->model->getBorradores($id);
        $publicados = $this->model->getPublicados($id);
        $cancelados = $this->model->getCancelados($id);
        $test_resultados = $this->model->testCompleto($id);

        $vistaPath = 'vista/formulacion144/index.php';
        if (!file_exists($vistaPath)) {
            die("Error crítico: No se encuentra la vista en: " . $vistaPath);
        }

        require_once $vistaPath;
    }

    // ============= MÉTODO PARA MOSTRAR ERRORES =============
    private function mostrarError($mensaje, $id = null) {
        $formularios = $this->model->obtenerTodosLosFormularios();
        
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error - Formulación 144</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                :root {
                    --color-primary: #2C3E50;
                    --color-danger: #E74C3C;
                }
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    font-family: 'Segoe UI', sans-serif;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .error-card {
                    background: white;
                    border-radius: 20px;
                    padding: 40px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
                    max-width: 800px;
                    width: 100%;
                    border-left: 5px solid var(--color-danger);
                }
                .error-icon {
                    font-size: 70px;
                    color: var(--color-danger);
                    margin-bottom: 20px;
                }
                .btn-primary {
                    background: linear-gradient(135deg, var(--color-primary) 0%, #34495E 100%);
                    border: none;
                    padding: 12px 30px;
                    border-radius: 10px;
                    font-weight: 600;
                }
                .formulario-list {
                    max-height: 300px;
                    overflow-y: auto;
                    background: #f8f9fa;
                    border-radius: 10px;
                    padding: 15px;
                    margin-top: 20px;
                }
                .formulario-item {
                    padding: 10px;
                    border-bottom: 1px solid #dee2e6;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
            </style>
        </head>
        <body>
            <div class="error-card">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="mb-3" style="color: var(--color-primary);">Formulario No Encontrado</h2>
                <p class="lead mb-4"><?php echo htmlspecialchars($mensaje); ?></p>
                
                <div class="alert alert-info">
                    <strong>ID solicitado:</strong> <?php echo $id ?? 'No proporcionado'; ?>
                </div>
                
                <?php if (count($formularios) > 0): ?>
                <div class="formulario-list">
                    <h6><i class="fas fa-list me-2"></i>Formularios disponibles (<?php echo count($formularios); ?>):</h6>
                    <?php foreach ($formularios as $f): ?>
                    <div class="formulario-item">
                        <span>
                            <strong>ID <?php echo $f['id']; ?>:</strong> 
                            <?php echo htmlspecialchars($f['titulo']); ?>
                            <?php if ($f['fecha_inicio'] && $f['fecha_cierre']): ?>
                                <br><small class="text-muted">
                                    <i class="fas fa-calendar-alt"></i> 
                                    <?php echo date('d/m/Y', strtotime($f['fecha_inicio'])); ?> - 
                                    <?php echo date('d/m/Y', strtotime($f['fecha_cierre'])); ?>
                                </small>
                            <?php endif; ?>
                        </span>
                        <a href="<?php echo Config::getBasePath(); ?>/formulacion144?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-arrow-right"></i> Usar
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    No hay formularios activos. <a href="<?php echo Config::getBasePath(); ?>/FOR-DE-144" class="alert-link">Crear uno</a>
                </div>
                <?php endif; ?>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="<?php echo Config::getBasePath(); ?>/FOR-DE-144" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Ver todos los formularios
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    // ============= API: CREAR BORRADOR =============
    public function crearBorrador() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formulario_id = $_POST['formulario_id'] ?? 0;
            $nombre = trim($_POST['nombre_borrador'] ?? '');

            if (empty($formulario_id) || empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'Faltan datos']);
                return;
            }

            $creado_por = $_SESSION['user_id'] ?? 1;
            $resultado = $this->model->crearBorrador($formulario_id, $nombre, $creado_por);

            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Borrador creado exitosamente' : 'Error al crear borrador'
            ]);
        }
    }

    // ============= API: OBTENER BORRADOR =============
    public function getBorrador() {
        $id = $_GET['id'] ?? 0;
        $borrador = $this->model->getById($id);
        
        if ($borrador) {
            echo json_encode(['success' => true, 'borrador' => $borrador]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No encontrado']);
        }
    }

    // ============= API: GUARDAR FORMULARIO =============
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            
            $data = [
                'nombre_borrador' => $_POST['nombre_borrador'] ?? '',
                'anio' => $_POST['anio'] ?? null,
                'linea_estrategica' => $_POST['linea_estrategica'] ?? null,
                'objetivo' => $_POST['objetivo'] ?? null,
                'estrategia' => $_POST['estrategia'] ?? null,
                'motor_desarrollo' => $_POST['motor_desarrollo'] ?? null,
                'meta_resultado' => $_POST['meta_resultado'] ?? null,
                'proyecto' => $_POST['proyecto'] ?? null,
                'ponderacion_proyectos' => $_POST['ponderacion_proyectos'] ?? null,
                'actividad_proyecto' => $_POST['actividad_proyecto'] ?? null,
                'ponderacion_actividades' => $_POST['ponderacion_actividades'] ?? null,
                'responsable' => $_POST['responsable'] ?? null
            ];

            $resultado = $this->model->actualizar($id, $data);

            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Guardado exitosamente' : 'Error al guardar'
            ]);
        }
    }

    // ============= API: CAMBIAR ESTADO =============
    public function cambiarEstado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $estado = $_POST['estado'] ?? 0;
            
            $resultado = $this->model->cambiarEstado($id, $estado);
            
            $mensajes = [
                0 => 'Movido a Borrador',
                1 => 'Cancelado',
                2 => 'Publicado exitosamente'
            ];

            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? $mensajes[$estado] : 'Error al cambiar estado'
            ]);
        }
    }

    // ============= API: ELIMINAR =============
    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $resultado = $this->model->eliminar($id);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Eliminado exitosamente' : 'Error al eliminar'
            ]);
        }
    }

    // ============= API: DUPLICAR =============
    public function duplicar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $nombre = $_POST['nombre_duplicado'] ?? 'Copia de ' . date('d/m/Y H:i');
            $creado_por = $_SESSION['user_id'] ?? 1;
            
            $resultado = $this->model->duplicar($id, $nombre, $creado_por);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Duplicado exitosamente' : 'Error al duplicar'
            ]);
        }
    }
    
    // ============= PÁGINA DE TEST =============
    public function test() {
        $id = $_GET['id'] ?? 0;
        
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            echo "<h2 style='color: red;'>❌ Error: ID de formulario no válido</h2>";
            echo "<p>Debes proporcionar un ID válido: <code>?id=1</code></p>";
            echo "<a href='" . Config::getBasePath() . "/FOR-DE-144'>Volver a FOR-DE-144</a>";
            exit;
        }
        
        $formulario = $this->model->verificarFormulario($id);
        
        if (!$formulario) {
            echo "<h2 style='color: red;'>❌ El formulario con ID $id NO EXISTE</h2>";
            echo "<p>Verifica que el ID sea correcto</p>";
            echo "<a href='" . Config::getBasePath() . "/FOR-DE-144'>Volver a FOR-DE-144</a>";
            exit;
        }
        
        $resultados = $this->model->testCompleto($id);
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Test Formulación 144</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
            <style>
                :root {
                    --color-primary: #2C3E50;
                    --color-success: #27AE60;
                    --color-danger: #E74C3C;
                }
                body { 
                    padding: 30px; 
                    background: #f8f9fa; 
                    font-family: 'Segoe UI', sans-serif; 
                }
                .header { 
                    background: linear-gradient(135deg, var(--color-primary) 0%, #34495E 100%);
                    color: white; 
                    padding: 30px; 
                    border-radius: 15px; 
                    margin-bottom: 30px; 
                }
                .test-card { 
                    background: white; 
                    border-radius: 15px; 
                    padding: 25px; 
                    margin-bottom: 20px; 
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                }
                .success { color: var(--color-success); }
                .error { color: var(--color-danger); }
                .btn-primary { background: var(--color-primary); border: none; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1><i class='fas fa-vial me-3'></i>Test de Formulación 144</h1>
                    <p class='mb-0'>ID: {$id} - " . htmlspecialchars($formulario['titulo']) . "</p>
                </div>";
        
        foreach ($resultados as $resultado) {
            $border_color = $resultado['success'] ? 'var(--color-success)' : 'var(--color-danger)';
            $text_color = $resultado['success'] ? 'success' : 'error';
            $icon = $resultado['success'] ? 'fa-check-circle' : 'fa-times-circle';
            
            echo "<div class='test-card' style='border-left: 5px solid {$border_color};'>";
            echo "<h4 class='{$text_color}'>";
            echo "<i class='fas {$icon} me-2'></i>";
            echo $resultado['message'] . "</h4>";
            echo "</div>";
        }
        
        echo "<div class='text-center mt-4'>";
        echo "<a href='" . Config::getBasePath() . "/formulacion144?id={$id}' class='btn btn-primary btn-lg'>";
        echo "<i class='fas fa-arrow-left me-2'></i>Volver</a>";
        echo "</div></body></html>";
        exit;
    }
}
?>