<?php
// controlador/Modulo144Controller.php
require_once 'modelo/Modulo144Model.php';

class Modulo144Controller {
    private $model;

    public function __construct() {
        $this->model = new Modulo144Model();
    }

    public function index() {
        $id = $_GET['id'] ?? 0;
        
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            $this->mostrarError('ID de formulario no válido', $id);
            return;
        }

        $formulario = $this->model->verificarFormulario($id);
        
        if (!$formulario) {
            $this->mostrarError('El formulario solicitado NO EXISTE', $id);
            return;
        }

        $estado_fechas = $this->model->verificarFechaHabil($formulario);
        
        $anos = $this->model->getAnos();
        $lineas_estrategicas = $this->model->getLineasEstrategicas();
        $cargos = $this->model->getCargos();
        $planes_institucionales = $this->model->getPlanesInstitucionales();
        $facultades = $this->model->getFacultades();
        
        $modulos = $this->model->getModulos();
        $datos_modulos = [];
        
        foreach ($modulos as $key => $modulo) {
            $datos_modulos[$key] = [
                'config' => $modulo,
                'borradores' => $this->model->getBorradores($key, $id),
                'publicados' => $this->model->getPublicados($key, $id),
                'cancelados' => $this->model->getCancelados($key, $id)
            ];
        }

        // Cargar preferencias de filtro del usuario actual
        $filter_preferences = [];
        $uid = $_SESSION['usuario_id'] ?? 0;
        if ($uid) {
            foreach ($modulos as $key => $modulo) {
                $filter_preferences[$key] = $this->model->getFilterPreference($uid, $id, $key);
            }
        }

        $esAdminFormulario = $uid ? $this->model->esAdministradorFormulario($uid, $id) : false;

        $vistaPath = 'vista/modulo144/index.php';
        if (!file_exists($vistaPath)) {
            die("Error crítico: No se encuentra la vista en: " . $vistaPath);
        }

        require_once $vistaPath;
    }

    public function getEstrategiasPorLinea() {
        header('Content-Type: application/json');
        
        $linea_id = isset($_GET['linea_id']) ? intval($_GET['linea_id']) : 0;
        
        if ($linea_id <= 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'ID de línea no válido'
            ]);
            return;
        }
        
        $estrategias = $this->model->getEstrategiasPorLinea($linea_id);
        
        echo json_encode([
            'success' => true, 
            'estrategias' => $estrategias
        ]);
    }

    public function getMotoresPorLinea() {
        header('Content-Type: application/json');
        
        $linea_id = isset($_GET['linea_id']) ? intval($_GET['linea_id']) : 0;
        
        if ($linea_id <= 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'ID de línea no válido'
            ]);
            return;
        }
        
        $motores = $this->model->getMotoresPorLinea($linea_id);
        
        echo json_encode([
            'success' => true, 
            'motores' => $motores
        ]);
    }

    public function getProyectosPorLineaYMotor() {
        header('Content-Type: application/json');
        
        $linea_id = isset($_GET['linea_id']) ? intval($_GET['linea_id']) : 0;
        $motor_id = isset($_GET['motor_id']) ? intval($_GET['motor_id']) : 0;
        
        if ($linea_id <= 0 || $motor_id <= 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'ID de línea o motor no válido'
            ]);
            return;
        }
        
        $proyectos = $this->model->getProyectosPorLineaYMotor($linea_id, $motor_id);
        
        echo json_encode([
            'success' => true, 
            'proyectos' => $proyectos
        ]);
    }

    public function getPonderacionProyecto() {
        header('Content-Type: application/json');

        $proyecto_id = isset($_GET['proyecto_id']) ? intval($_GET['proyecto_id']) : 0;
        $anio        = isset($_GET['anio'])         ? intval($_GET['anio'])        : 0;

        if ($proyecto_id <= 0 || $anio <= 0) {
            echo json_encode(['success' => false, 'porcentaje' => null, 'message' => 'Parámetros inválidos']);
            return;
        }

        $porcentaje = $this->model->getPonderacionProyecto($proyecto_id, $anio);

        echo json_encode([
            'success'    => true,
            'porcentaje' => $porcentaje
        ]);
    }

    public function getBorradoresPorFacultad() {
        header('Content-Type: application/json');
        
        $facultad_id = isset($_GET['facultad_id']) ? intval($_GET['facultad_id']) : 0;
        $formulario_id = isset($_GET['formulario_id']) ? intval($_GET['formulario_id']) : 0;
        
        if ($facultad_id <= 0 || $formulario_id <= 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'ID de facultad o formulario no válido',
                'borradores' => []
            ]);
            return;
        }
        
        $borradores = $this->model->getBorradoresPorFacultad($facultad_id, $formulario_id);
        
        echo json_encode([
            'success' => true, 
            'borradores' => $borradores
        ]);
    }

    public function test() {
        $id = $_GET['id'] ?? 0;
        
        if (empty($id) || !is_numeric($id) || $id <= 0) {
            $this->mostrarErrorTest('ID de formulario no válido para test', $id);
            return;
        }

        $formulario = $this->model->verificarFormulario($id);
        
        if (!$formulario) {
            $this->mostrarErrorTest('El formulario con ID ' . $id . ' NO EXISTE', $id);
            return;
        }

        $modulos = $this->model->getModulos();
        $datos_modulos = [];
        $resultados_tests = [];
        
        foreach ($modulos as $key => $modulo) {
            $tabla_existe = $this->model->verificarTabla($key);
            $datos_modulos[$key] = [
                'config' => $modulo,
                'tabla_existe' => $tabla_existe,
                'borradores' => $this->model->getBorradores($key, $id),
                'publicados' => $this->model->getPublicados($key, $id),
                'cancelados' => $this->model->getCancelados($key, $id)
            ];
            
            $resultados_tests[] = [
                'modulo' => $modulo['nombre'],
                'tabla' => $modulo['tabla'],
                'tabla_existe' => $tabla_existe,
                'borradores' => count($datos_modulos[$key]['borradores']),
                'publicados' => count($datos_modulos[$key]['publicados']),
                'cancelados' => count($datos_modulos[$key]['cancelados'])
            ];
        }

        $vistaTestPath = 'vista/modulo144/test.php';
        if (!file_exists($vistaTestPath)) {
            die("Error crítico: No se encuentra la vista de test en: " . $vistaTestPath);
        }

        require_once $vistaTestPath;
    }

    private function mostrarErrorTest($mensaje, $id = null) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error - Test Sistema 144</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
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
                    max-width: 600px;
                    width: 100%;
                    border-left: 5px solid #E74C3C;
                }
                .btn-primary { background: #2C3E50; border: none; }
            </style>
        </head>
        <body>
            <div class="error-card">
                <div class="text-center mb-4">
                    <i class="fas fa-exclamation-triangle fa-4x" style="color: #E74C3C;"></i>
                </div>
                <h2 class="text-center mb-3">Error en Test</h2>
                <p class="lead text-center mb-4"><?php echo htmlspecialchars($mensaje); ?></p>
                <div class="alert alert-info">
                    <strong>ID solicitado:</strong> <?php echo $id ?? 'No proporcionado'; ?>
                </div>
                <div class="text-center mt-4">
                    <a href="<?php echo Config::getBasePath(); ?>/modulo144?id=<?php echo $id; ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Volver al módulo
                    </a>
                    <a href="<?php echo Config::getBasePath(); ?>/FOR-DE-144" class="btn btn-secondary ms-2">
                        <i class="fas fa-folder me-2"></i>Ver formularios
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    private function mostrarError($mensaje, $id = null) {
        $formularios = $this->model->obtenerTodosLosFormularios();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error - Sistema 144</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                :root { --color-primary: #2C3E50; --color-danger: #E74C3C; }
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
                .btn-primary { background: var(--color-primary); border: none; }
                .formulario-list { max-height: 300px; overflow-y: auto; background: #f8f9fa; border-radius: 10px; padding: 15px; margin-top: 20px; }
                .formulario-item { padding: 10px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; }
            </style>
        </head>
        <body>
            <div class="error-card">
                <div class="text-center mb-4">
                    <i class="fas fa-exclamation-triangle fa-4x" style="color: var(--color-danger);"></i>
                </div>
                <h2 class="text-center mb-3">Formulario No Encontrado</h2>
                <p class="lead text-center mb-4"><?php echo htmlspecialchars($mensaje); ?></p>
                <div class="alert alert-info">
                    <strong>ID solicitado:</strong> <?php echo $id ?? 'No proporcionado'; ?>
                </div>
                <?php if (count($formularios) > 0): ?>
                <div class="formulario-list">
                    <h6><i class="fas fa-list me-2"></i>Formularios disponibles:</h6>
                    <?php foreach ($formularios as $f): ?>
                    <div class="formulario-item">
                        <span><strong>ID <?php echo $f['id']; ?>:</strong> <?php echo htmlspecialchars($f['titulo']); ?></span>
                        <a href="?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-arrow-right"></i> Usar
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="text-center mt-4">
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

    public function crearBorrador() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modulo = $_POST['modulo'] ?? '';
            $formulario_id = $_POST['formulario_id'] ?? 0;
            $nombre = trim($_POST['nombre_borrador'] ?? '');
            $facultad_id = isset($_POST['facultad_id']) ? intval($_POST['facultad_id']) : null;

            if (empty($modulo)) {
                echo json_encode(['success' => false, 'message' => 'Módulo no especificado']);
                return;
            }

            if (empty($formulario_id) || $formulario_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de formulario no válido']);
                return;
            }

            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre del borrador es obligatorio']);
                return;
            }

            $creado_por = $_SESSION['usuario_id'] ?? 1;
            $resultado = $this->model->crearBorrador($modulo, $formulario_id, $nombre, $creado_por, $facultad_id);

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Borrador creado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear borrador. Verifique que la tabla exista.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        }
    }

    public function getBorrador() {
        header('Content-Type: application/json');
        $modulo = $_GET['modulo'] ?? '';
        $id = $_GET['id'] ?? 0;
        
        if (empty($modulo) || empty($id) || $id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos']);
            return;
        }
        
        $borrador = $this->model->getById($modulo, $id);
        echo json_encode($borrador ? ['success' => true, 'borrador' => $borrador] : ['success' => false, 'message' => 'No encontrado']);
    }

    public function guardar() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modulo = $_POST['modulo'] ?? '';
            $id     = intval($_POST['id'] ?? 0);

            if (empty($modulo) || $id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Faltan datos']);
                return;
            }

            // Validación server-side: ponderación acumulada no puede superar 100%
            if ($modulo === 'formulacion' && isset($_POST['ponderacion_actividades']) && $_POST['ponderacion_actividades'] !== '') {
                $proyecto      = $_POST['proyecto']              ?? '';
                $formulario_id = intval($_POST['formulario_id']  ?? 0);
                $nueva         = (float)$_POST['ponderacion_actividades'];

                if ($formulario_id > 0 && $proyecto !== '') {
                    $acumulado = $this->model->getAcumuladoProyecto($formulario_id, $proyecto, $id);
                    if (($acumulado + $nueva) > 100.005) {
                        $disponible = max(0, 100 - $acumulado);
                        echo json_encode([
                            'success'   => false,
                            'message'   => "La ponderación supera el 100% para este proyecto. Disponible: {$disponible}%",
                            'acumulado' => $acumulado,
                            'disponible'=> $disponible
                        ]);
                        return;
                    }
                }
            }

            $resultado = $this->model->actualizar($modulo, $id, $_POST);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Guardado exitosamente' : 'Error al guardar'
            ]);
        }
    }

    // Devuelve id, proyecto, ponderacion_actividades de todas las formulaciones del formulario
    public function getPonderaciones() {
        header('Content-Type: application/json');
        $formulario_id = intval($_GET['formulario_id'] ?? 0);
        if ($formulario_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        $data = $this->model->getPonderacionesPorFormulario($formulario_id);
        echo json_encode(['success' => true, 'formulaciones' => $data]);
    }

    // Devuelve el conteo de registros del formulario (para polling de cambios en la lista)
    public function contarRegistros() {
        header('Content-Type: application/json');
        $formulario_id = intval($_GET['formulario_id'] ?? 0);
        if ($formulario_id <= 0) {
            echo json_encode(['success' => false]);
            return;
        }
        $total = $this->model->contarRegistros($formulario_id);
        echo json_encode(['success' => true, 'total' => $total]);
    }
    
    public function guardarGestionSemestral() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            
            if (empty($id) || $id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }
            
            $data = [
                'gestion_sem1' => $_POST['gestion_sem1'] ?? '',
                'gestion_sem2' => $_POST['gestion_sem2'] ?? '',
                'vigencia' => $_POST['vigencia'] ?? '',
                'descripcion_gestion' => $_POST['descripcion_gestion'] ?? '',
                'tabla_fila1_sem1' => $_POST['tabla_fila1_sem1'] ?? '',
                'tabla_fila1_sem2' => $_POST['tabla_fila1_sem2'] ?? '',
                'tabla_fila2_sem1' => $_POST['tabla_fila2_sem1'] ?? '',
                'tabla_fila2_sem2' => $_POST['tabla_fila2_sem2'] ?? '',
                'tabla_fila3_sem1' => $_POST['tabla_fila3_sem1'] ?? '',
                'tabla_fila3_sem2' => $_POST['tabla_fila3_sem2'] ?? ''
            ];
            
            $resultado = $this->model->actualizarGestionSemestral($id, $data);
            
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Gestión semestral guardada exitosamente' : 'Error al guardar la gestión semestral'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        }
    }

    public function getGestionFacultad144() {
        header('Content-Type: application/json');
        $formulacion_id = intval($_GET['formulacion_id'] ?? 0);
        $facultad_id = intval($_GET['facultad_id'] ?? 0);

        if ($formulacion_id <= 0 || $facultad_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            return;
        }

        $gestion = $this->model->getGestionFacultad($formulacion_id, $facultad_id);
        echo json_encode(['success' => true, 'gestion' => $gestion ?: null]);
    }

    public function guardarGestionFacultad144() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $formulacion_id = intval($_POST['formulacion_id'] ?? 0);
        $facultad_id = intval($_POST['facultad_id'] ?? 0);

        if ($formulacion_id <= 0 || $facultad_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            return;
        }

        $data = [
            'sem1' => $_POST['sem1'] ?? '',
            'sem2' => $_POST['sem2'] ?? '',
            'vigencia' => $_POST['vigencia'] ?? '',
            'seguimiento_sem1' => $_POST['seguimiento_sem1'] ?? '',
            'seguimiento_sem2' => $_POST['seguimiento_sem2'] ?? '',
            'descripcion_gestion' => $_POST['descripcion_gestion'] ?? ''
        ];

        $resultado = $this->model->guardarGestionFacultad($formulacion_id, $facultad_id, $data);

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Gestión de facultad guardada' : 'Error al guardar'
        ]);
    }

    public function cambiarEstado() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modulo = $_POST['modulo'] ?? '';
            $id = $_POST['id'] ?? 0;
            $estado = $_POST['estado'] ?? 0;
            
            if (empty($modulo) || empty($id) || $id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Faltan datos']);
                return;
            }
            
            $resultado = $this->model->cambiarEstado($modulo, $id, $estado);
            $mensajes = [0 => 'Movido a Borrador', 1 => 'Cancelado', 2 => 'Publicado exitosamente'];
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? $mensajes[$estado] : 'Error al cambiar estado'
            ]);
        }
    }

    /**
     * Cambia el estado de solicitud de un borrador (0=Construcción, 1=Solicitado, 2=Rechazado)
     */
    public function cambiarSolicitudEstado() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $modulo = $_POST['modulo'] ?? '';
        $id = intval($_POST['id'] ?? 0);
        $solicitud_estado = intval($_POST['solicitud_estado'] ?? 0);

        if (empty($modulo) || $id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos']);
            return;
        }

        $resultado = $this->model->actualizarSolicitudEstado($modulo, $id, $solicitud_estado);
        $mensajes = [0 => 'Solicitud reiniciada', 1 => 'Solicitud de aprobación enviada', 2 => 'Solicitud rechazada'];
        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? ($mensajes[$solicitud_estado] ?? 'Actualizado') : 'Error al actualizar'
        ]);
    }

    // Orden de aprobación secuencial del semáforo. La clave es la etapa (1-4).
    private $semaforoRoles = [
        1 => 'gestor de metas',
        2 => 'lider de metas',
        3 => 'responsable de linea',
        4 => 'sub administrador',
    ];

    private function normalizarRol($s) {
        $s = mb_strtolower(trim((string)$s), 'UTF-8');
        return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
    }

    public function avanzarSemaforo() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $modulo = $_POST['modulo'] ?? '';
        $id = intval($_POST['id'] ?? 0);
        $etapaActual = intval($_POST['etapa_actual'] ?? 0);

        if (empty($modulo) || $id <= 0 || $etapaActual < 0 || $etapaActual > 3) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            return;
        }

        $etapaNueva = $etapaActual + 1;
        $rolEsperado = $this->semaforoRoles[$etapaNueva] ?? null;
        $rolUsuario  = $this->normalizarRol($_SESSION['usuario_rol'] ?? '');
        $esSuperAdmin = (int)($_SESSION['usuario_id'] ?? 0) === 1;

        if (!$esSuperAdmin && (!$rolEsperado || $this->normalizarRol($rolEsperado) !== $rolUsuario)) {
            echo json_encode(['success' => false, 'message' => 'Tu rol no puede aprobar esta etapa']);
            return;
        }

        $resultado = $this->model->avanzarSemaforo($modulo, $id, $etapaNueva, $etapaActual);

        if ($resultado && $etapaNueva === 4) {
            // Sub Administrador es la última etapa: aprobar = publicar
            $this->model->cambiarEstado($modulo, $id, 2);
        }

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado
                ? ($etapaNueva === 4 ? 'Etapa aprobada: publicado exitosamente' : 'Etapa aprobada')
                : 'No se pudo actualizar (puede que ya haya avanzado)'
        ]);
    }

    public function rechazarSemaforo() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $modulo = $_POST['modulo'] ?? '';
        $id = intval($_POST['id'] ?? 0);
        $etapaActual = intval($_POST['etapa_actual'] ?? 0);

        if (empty($modulo) || $id <= 0 || $etapaActual < 0 || $etapaActual > 3) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            return;
        }

        // Quien puede aprobar la siguiente etapa también puede rechazarla
        $etapaSiguiente = $etapaActual + 1;
        $rolEsperado = $this->semaforoRoles[$etapaSiguiente] ?? null;
        $rolUsuario  = $this->normalizarRol($_SESSION['usuario_rol'] ?? '');
        $esSuperAdmin = (int)($_SESSION['usuario_id'] ?? 0) === 1;

        if (!$esSuperAdmin && (!$rolEsperado || $this->normalizarRol($rolEsperado) !== $rolUsuario)) {
            echo json_encode(['success' => false, 'message' => 'Tu rol no puede rechazar esta etapa']);
            return;
        }

        $resultado = $this->model->rechazarSemaforo($modulo, $id, $etapaActual);
        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Rechazado: vuelve al creador para su corrección' : 'No se pudo actualizar (puede que ya haya avanzado)'
        ]);
    }

    public function eliminar() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modulo = $_POST['modulo'] ?? '';
            $id = $_POST['id'] ?? 0;
            
            if (empty($modulo) || empty($id) || $id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Faltan datos']);
                return;
            }
            
            $resultado = $this->model->eliminar($modulo, $id);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Eliminado exitosamente' : 'Error al eliminar'
            ]);
        }
    }

    public function duplicar() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modulo = $_POST['modulo'] ?? '';
            $id = $_POST['id'] ?? 0;
            $nombre = $_POST['nombre_duplicado'] ?? 'Copia de ' . date('d/m/Y H:i');
            $creado_por = $_SESSION['usuario_id'] ?? 1;

            if (empty($modulo) || empty($id) || $id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Faltan datos']);
                return;
            }
            
            $resultado = $this->model->duplicar($modulo, $id, $nombre, $creado_por);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? 'Duplicado exitosamente' : 'Error al duplicar'
            ]);
        }
    }

    public function getFilterPreference() {
        header('Content-Type: application/json');
        $uid = $_SESSION['usuario_id'] ?? 0;
        $formulario_id = intval($_GET['formulario_id'] ?? 0);
        $modulo = $_GET['modulo'] ?? '';
        if (!$uid || !$formulario_id || !$modulo) {
            echo json_encode(['success' => false, 'tipo_filtro' => 'todos', 'valor_filtro' => null]);
            return;
        }
        $pref = $this->model->getFilterPreference($uid, $formulario_id, $modulo);
        echo json_encode(['success' => true] + $pref);
    }

    public function saveFilterPreference() {
        header('Content-Type: application/json');
        $uid = $_SESSION['usuario_id'] ?? 0;
        if (!$uid) { echo json_encode(['success' => false]); return; }
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $result = $this->model->saveFilterPreference(
            $uid,
            intval($input['formulario_id'] ?? 0),
            $input['modulo'] ?? '',
            $input['tipo_filtro'] ?? 'todos',
            $input['valor_filtro'] ?? null
        );
        echo json_encode(['success' => $result]);
    }
}
?>