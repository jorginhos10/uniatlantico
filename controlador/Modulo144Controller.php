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
        $catalogo_proyectos = $this->model->getCatalogoProyectos();
        $id_indicadores = $this->model->getIdIndicadores();
        $viewer_cargo_nombre = $this->model->getCargoNombre($_SESSION['usuario_cargo_id'] ?? 0);
        // Todas las dependencias de las que el usuario es responsable (principal + "Responsable de")
        $viewer_dependencias = $this->model->getNombresDependenciasUsuario($_SESSION['usuario_id'] ?? 0, $_SESSION['usuario_cargo_id'] ?? 0);
        // Si la dependencia del usuario es una facultad, ve en Seguimiento los indicadores donde su facultad tiene distribución
        // Facultades del usuario (por su dependencia principal o por cualquiera de las asociadas en "Responsable de")
        $viewer_facultades = $this->model->getFacultadesDelUsuario($_SESSION['usuario_id'] ?? 0, $_SESSION['usuario_cargo_id'] ?? 0);
        $facultades_por_item = $this->model->getFacultadesConAcceso((int)$formulario['id']);
        $resumen_concertaciones = $this->model->getResumenConcertaciones((int)$formulario['id']);
        $matriz_concertacion = $this->model->getMatrizConcertacion((int)$formulario['id']);
        $facultades = $this->model->getFacultades();

        // Resumen del informe (cumplimiento ponderado por líneas, motores y proyectos) para "Evaluación líneas"
        require_once 'modelo/FORDE144Model.php';
        $informe_resumen = (new FORDE144Model())->getInformeCompleto((int)$formulario['id']);

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

            if ($this->bloquearSeguimientoNoPublicado($modulo, $id)) return;

            // Quien ve el indicador solo por la distribución de su facultad registra únicamente su casilla de
            // "Seguimiento por facultades" (guardarSeguimientoFacultad), no el indicador general
            $itemActual = $this->model->getById($modulo, $id);
            if ($itemActual && $this->facultadesRestringidas($itemActual)) {
                echo json_encode(['success' => false, 'message' => 'Tu facultad solo registra su seguimiento en la tabla "Seguimiento por facultades"']);
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

            // Semestres bloqueados desde Editar formulario → Campos: sus casillas de seguimiento no se modifican
            if ($modulo === 'seguimiento' && $itemActual) {
                $bl = $this->semestresBloqueados($itemActual);
                if ($bl['sem1']) unset($_POST['semestre1_seguimiento'], $_POST['descripcion_sem1'],
                    $_POST['logros'], $_POST['limites'], $_POST['observacion_estado'], $_POST['acciones_fortalecimiento']);
                if ($bl['sem2']) unset($_POST['semestre2_seguimiento'], $_POST['descripcion_sem2'],
                    $_POST['logros_sem2'], $_POST['limites_sem2'], $_POST['observacion_estado_sem2'], $_POST['acciones_fortalecimiento_sem2']);
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
            
            if ((int)$estado === 2 && $this->bloquearSeguimientoNoPublicado($modulo, (int)$id)) return;

            // Devolver un publicado a borrador: solo el superadmin (id=1)
            if ((int)$estado === 0 && (int)($_SESSION['usuario_id'] ?? 0) !== 1) {
                echo json_encode(['success' => false, 'message' => 'Solo el superadministrador puede devolver a borrador']);
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

    // Devuelve a borrador todos los publicados de un módulo (formulación o seguimiento) del formulario.
    // Igual que devolver uno solo: exclusivo del superadministrador (id=1).
    // Publica en bloque los borradores de formulación (cargados por plantilla): solo administradores.
    // Con "ids[]" publica solo esos; sin ellos, todos los borradores completos del formulario.
    public function publicarBorradores() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $uid = (int)($_SESSION['usuario_id'] ?? 0);
        $rol = $this->normalizarRol($_SESSION['usuario_rol'] ?? '');
        if ($uid !== 1 && !in_array($rol, ['admin', 'administrador'], true)) {
            echo json_encode(['success' => false, 'message' => 'Solo un administrador puede publicar los borradores']);
            return;
        }
        $formularioId = intval($_POST['formulario_id'] ?? 0);
        if ($formularioId <= 0 || !$this->model->verificarFormulario($formularioId)) {
            echo json_encode(['success' => false, 'message' => 'Formulario no válido']);
            return;
        }
        $ids = isset($_POST['ids']) ? (array)$_POST['ids'] : null;

        $resultado = $this->model->publicarBorradores($formularioId, $ids, $uid, $_SESSION['usuario_nombre'] ?? '—', $_SESSION['usuario_rol'] ?? null);
        if ($resultado === false) {
            echo json_encode(['success' => false, 'message' => 'Error al publicar los borradores']);
            return;
        }
        $n = count($resultado['publicados']);
        echo json_encode([
            'success'    => true,
            'message'    => $n === 0 ? 'No se publicó ningún borrador' : ($n === 1 ? '1 indicador publicado' : "$n indicadores publicados"),
            'publicados' => $resultado['publicados'],
            'omitidos'   => $resultado['omitidos'],
        ]);
    }

    public function devolverTodosABorrador() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        if ((int)($_SESSION['usuario_id'] ?? 0) !== 1) {
            echo json_encode(['success' => false, 'message' => 'Solo el superadministrador puede devolver a borrador']);
            return;
        }
        $modulo = $_POST['modulo'] ?? '';
        $formularioId = intval($_POST['formulario_id'] ?? 0);
        if (!in_array($modulo, ['formulacion', 'seguimiento'], true) || $formularioId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            return;
        }

        $movidos = $this->model->devolverTodosABorrador($modulo, $formularioId);
        if ($movidos === false) {
            echo json_encode(['success' => false, 'message' => 'Error al devolver a borrador']);
            return;
        }
        echo json_encode([
            'success' => true,
            'message' => $movidos === 0 ? 'No había publicados para devolver' : ($movidos === 1 ? '1 registro devuelto a borrador' : "$movidos registros devueltos a borrador")
        ]);
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

        if ($solicitud_estado === 1 && $this->bloquearSeguimientoNoPublicado($modulo, $id)) return;

        // Al solicitar aprobación (1), el nivel del creador "pasa" automáticamente su propia
        // etapa del semáforo (para que la cascada de visibilidad la vea el siguiente rol).
        $nivelCreador = null;
        if ($solicitud_estado === 1) {
            $rolActual = $this->normalizarRol($_SESSION['usuario_rol'] ?? '');
            $nivelCreador = $this->semaforoRolNivel[$rolActual] ?? 0;
        }

        $resultado = $this->model->actualizarSolicitudEstado($modulo, $id, $solicitud_estado, $nivelCreador);
        $mensajes = [0 => 'Solicitud reiniciada', 1 => 'Solicitud de aprobación enviada', 2 => 'Solicitud rechazada'];
        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? ($mensajes[$solicitud_estado] ?? 'Actualizado') : 'Error al actualizar'
        ]);
    }

    // Orden de aprobación secuencial del semáforo. La clave es la etapa (1-4).
    private $semaforoRoles = [
        1 => 'gestor de metas',
        2 => 'lider de meta',
        3 => 'responsable de linea',
        4 => 'sub administrador',
    ];

    private $semaforoRolNivel = [
        'gestor de metas' => 1,
        'gestor de metas de responsable de linea' => 1,
        'lider de meta' => 2,
        'gestor de metas de sub-admin' => 2,
        'responsable de linea' => 3,
        'sub administrador' => 4,
    ];

    // El seguimiento solo aplica a formulaciones publicadas (estado_formulacion = 2).
    // Devuelve true (y responde el error) si la acción debe bloquearse.
    private function bloquearSeguimientoNoPublicado($modulo, $id) {
        if ($modulo !== 'seguimiento') return false;
        $registro = $this->model->getById('formulacion', $id);
        if ($registro && (int)($registro['estado_formulacion'] ?? 0) === 2) return false;
        echo json_encode(['success' => false, 'message' => 'Solo se puede hacer seguimiento a formulaciones publicadas']);
        return true;
    }

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

        if ($this->bloquearSeguimientoNoPublicado($modulo, $id)) return;

        $etapaNueva = $etapaActual + 1;
        $rolEsperado = $this->semaforoRoles[$etapaNueva] ?? null;
        $rolUsuario  = $this->normalizarRol($_SESSION['usuario_rol'] ?? '');
        $esSuperAdmin = (int)($_SESSION['usuario_id'] ?? 0) === 1;
        // Administrador / Sub Administrador solo pueden actuar sobre la etapa final (4)
        $esAdminEtapaFinal = $etapaNueva === 4 && in_array($rolUsuario, ['administrador', 'sub administrador'], true);

        if (!$esSuperAdmin && !$esAdminEtapaFinal && (!$rolEsperado || $this->normalizarRol($rolEsperado) !== $rolUsuario)) {
            echo json_encode(['success' => false, 'message' => 'Tu rol no puede aprobar esta etapa']);
            return;
        }

        $resultado = $this->model->avanzarSemaforo($modulo, $id, $etapaNueva, $etapaActual);

        if ($resultado) {
            $this->model->registrarHistorialSemaforo(
                $modulo, $id, $etapaNueva, 'aprobado',
                (int)($_SESSION['usuario_id'] ?? 0),
                $_SESSION['usuario_nombre'] ?? '—',
                $rolEsperado ?? ($_SESSION['usuario_rol'] ?? null)
            );
        }

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
        $motivo = trim($_POST['motivo'] ?? '');

        if (empty($modulo) || $id <= 0 || $etapaActual < 0 || $etapaActual > 3) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            return;
        }

        if ($motivo === '') {
            echo json_encode(['success' => false, 'message' => 'Debes ingresar el motivo del rechazo']);
            return;
        }

        // Quien puede aprobar la siguiente etapa también puede rechazarla
        $etapaSiguiente = $etapaActual + 1;
        $rolEsperado = $this->semaforoRoles[$etapaSiguiente] ?? null;
        $rolUsuario  = $this->normalizarRol($_SESSION['usuario_rol'] ?? '');
        $esSuperAdmin = (int)($_SESSION['usuario_id'] ?? 0) === 1;
        // Administrador / Sub Administrador solo pueden actuar sobre la etapa final (4)
        $esAdminEtapaFinal = $etapaSiguiente === 4 && in_array($rolUsuario, ['administrador', 'sub administrador'], true);

        if (!$esSuperAdmin && !$esAdminEtapaFinal && (!$rolEsperado || $this->normalizarRol($rolEsperado) !== $rolUsuario)) {
            echo json_encode(['success' => false, 'message' => 'Tu rol no puede rechazar esta etapa']);
            return;
        }

        $resultado = $this->model->rechazarSemaforo($modulo, $id, $etapaActual, $etapaSiguiente);

        if ($resultado) {
            $this->model->registrarHistorialSemaforo(
                $modulo, $id, $etapaSiguiente, 'rechazado',
                (int)($_SESSION['usuario_id'] ?? 0),
                $_SESSION['usuario_nombre'] ?? '—',
                $rolEsperado ?? ($_SESSION['usuario_rol'] ?? null),
                $motivo
            );
            $this->enviarMensajeRechazo($modulo, $id, $motivo);
        }

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Rechazado: vuelve al creador para su corrección' : 'No se pudo actualizar (puede que ya haya avanzado)'
        ]);
    }

    public function getDistribucionFacultades() {
        header('Content-Type: application/json');
        $formulacionId = intval($_GET['formulacion_id'] ?? 0);
        if ($formulacionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }
        $distribucion = $this->model->getDistribucionFacultades($formulacionId);

        // Un usuario de facultad que no es administrador, ni creó el indicador, ni pertenece a la
        // dependencia responsable, solo recibe las filas de su(s) propia(s) facultad(es)
        $facultadesPropias = $this->facultadesRestringidas($this->model->getById('formulacion', $formulacionId) ?: []);
        if ($facultadesPropias) {
            $distribucion = array_values(array_filter($distribucion, function ($d) use ($facultadesPropias) {
                return in_array((int)$d['facultad_id'], $facultadesPropias, true);
            }));
        }
        echo json_encode(['success' => true, 'distribucion' => $distribucion, 'solo_facultades' => $facultadesPropias]);
    }

    // Ids de las facultades del usuario si solo debe ver/registrar su propia fila; [] si puede ver todas
    private function facultadesRestringidas(array $item) {
        $uid = (int)($_SESSION['usuario_id'] ?? 0);
        $rol = $this->normalizarRol($_SESSION['usuario_rol'] ?? '');
        if ($uid === 1 || in_array($rol, ['admin', 'administrador', 'sub administrador'], true)) return [];
        if ($uid > 0 && $uid === (int)($item['creado_por'] ?? 0)) return [];

        $propias = $this->model->getFacultadesDelUsuario($uid, $_SESSION['usuario_cargo_id'] ?? 0);
        if (!$propias) return [];

        if ($this->esResponsableDelItem($item)) return [];
        return $propias;
    }

    // ¿Alguna de las dependencias del usuario (principal o "Responsable de") es responsable del indicador?
    private function esResponsableDelItem(array $item) {
        $lista = ', ' . mb_strtolower(trim((string)($item['responsable_formulacion'] ?? '')), 'UTF-8') . ', ';
        foreach ($this->model->getNombresDependenciasUsuario($_SESSION['usuario_id'] ?? 0, $_SESSION['usuario_cargo_id'] ?? 0) as $dep) {
            if (mb_strpos($lista, ', ' . mb_strtolower(trim($dep), 'UTF-8') . ', ', 0, 'UTF-8') !== false) return true;
        }
        return false;
    }

    public function guardarDistribucionFacultad() {
        header('Content-Type: application/json');
        $formulacionId = intval($_POST['formulacion_id'] ?? 0);
        $facultadId = intval($_POST['facultad_id'] ?? 0);
        $observacion = trim($_POST['observacion'] ?? '');
        $distribucion = floatval($_POST['distribucion'] ?? 0);

        if ($formulacionId <= 0 || $facultadId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            return;
        }

        $ok = $this->model->guardarDistribucionFacultad($formulacionId, $facultadId, $observacion, $distribucion);
        echo json_encode(['success' => $ok]);
    }

    // Seguimiento S1/S2 de una facultad sobre su parte de la distribución.
    // Lo registran la propia facultad (usuarios cuya dependencia es esa facultad), quien creó el
    // indicador, los usuarios de la dependencia responsable y los administradores.
    public function guardarSeguimientoFacultad() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $formulacionId = intval($_POST['formulacion_id'] ?? 0);
        $facultadId    = intval($_POST['facultad_id'] ?? 0);
        $num = function ($v) {
            $v = trim((string)$v);
            return $v === '' ? null : (float)str_replace(',', '.', $v);
        };
        $sem1 = $num($_POST['seguimiento_sem1'] ?? '');
        $sem2 = $num($_POST['seguimiento_sem2'] ?? '');
        $descripcion = trim((string)($_POST['seguimiento_descripcion'] ?? ''));
        $descripcion = $descripcion === '' ? null : $descripcion;
        $descripcion2 = trim((string)($_POST['seguimiento_descripcion2'] ?? ''));
        $descripcion2 = $descripcion2 === '' ? null : $descripcion2;

        $item = $formulacionId > 0 ? $this->model->getById('seguimiento', $formulacionId) : null;
        if (!$item || $facultadId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            return;
        }
        if (!$this->puedeRegistrarSeguimientoFacultad($item, $facultadId)) {
            echo json_encode(['success' => false, 'message' => 'No puedes registrar el seguimiento de esta facultad']);
            return;
        }

        // Semestres bloqueados: se conserva el valor que ya tenía la facultad
        $bl = $this->semestresBloqueados($item);
        if ($bl['sem1'] || $bl['sem2']) {
            $actualFac = null;
            foreach ($this->model->getDistribucionFacultades($formulacionId) as $d) {
                if ((int)$d['facultad_id'] === $facultadId) { $actualFac = $d; break; }
            }
            if ($bl['sem1']) {
                $sem1 = isset($actualFac['seguimiento_sem1']) && $actualFac['seguimiento_sem1'] !== '' ? $actualFac['seguimiento_sem1'] : null;
                $descripcion = $actualFac['seguimiento_descripcion'] ?? null;
            }
            if ($bl['sem2']) {
                $sem2 = isset($actualFac['seguimiento_sem2']) && $actualFac['seguimiento_sem2'] !== '' ? $actualFac['seguimiento_sem2'] : null;
                $descripcion2 = $actualFac['seguimiento_descripcion2'] ?? null;
            }
        }

        $ok = $this->model->guardarSeguimientoFacultad($formulacionId, $facultadId, $sem1, $sem2, $descripcion, $descripcion2);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Seguimiento guardado' : 'No se pudo guardar']);
    }

    private function puedeRegistrarSeguimientoFacultad(array $item, $facultadId) {
        $uid = (int)($_SESSION['usuario_id'] ?? 0);
        $rol = $this->normalizarRol($_SESSION['usuario_rol'] ?? '');
        if ($uid === 1 || in_array($rol, ['admin', 'administrador', 'sub administrador'], true)) return true;
        if ($uid > 0 && $uid === (int)($item['creado_por'] ?? 0)) return true;

        // La facultad es una de las del usuario (dependencia principal o asociada en "Responsable de")
        if (in_array((int)$facultadId, $this->model->getFacultadesDelUsuario($uid, $_SESSION['usuario_cargo_id'] ?? 0), true)) return true;

        // Dependencia responsable (principal o asociada en "Responsable de")
        return $this->esResponsableDelItem($item);
    }

    public function getHistorialSemaforo() {
        header('Content-Type: application/json');
        $modulo = $_GET['modulo'] ?? '';
        $id = intval($_GET['id'] ?? 0);

        if (empty($modulo) || $id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            return;
        }

        $historial = $this->model->getHistorialSemaforo($modulo, $id);
        echo json_encode(['success' => true, 'historial' => $historial]);
    }

    /**
     * Envía un mensaje directo al creador del borrador explicando el motivo del rechazo,
     * incluyendo línea, motor, proyecto, indicador y fórmula del indicador.
     */
    private function enviarMensajeRechazo($modulo, $id, $motivo) {
        try {
            $borrador = $this->model->getById($modulo, $id);
            if (!$borrador || empty($borrador['creado_por'])) return;

            $remitenteId = (int)($_SESSION['usuario_id'] ?? 0);
            $destinatarioId = (int)$borrador['creado_por'];
            if ($remitenteId <= 0 || $destinatarioId <= 0) return;

            $nombreBorrador = $borrador['nombre_borrador'] ?? 'Sin nombre';
            $asunto = 'Rechazo de: ' . $nombreBorrador;

            $cuerpo = "Se rechazó el ítem \"{$nombreBorrador}\".\n\n"
                    . "Línea: " . ($borrador['linea_estrategica'] ?? '—') . "\n"
                    . "Motor de Desarrollo: " . ($borrador['motor_desarrollo'] ?? '—') . "\n"
                    . "Proyecto: " . ($borrador['proyecto'] ?? '—') . "\n"
                    . "Nombre del Indicador: " . ($borrador['nombre_indicador'] ?? '—') . "\n"
                    . "Fórmula del Indicador: " . ($borrador['formula_medicion'] ?? '—') . "\n\n"
                    . "Motivo del rechazo:\n" . $motivo;

            require_once 'modelo/MensajesModel.php';
            $mensajesModel = new MensajesModel();
            $mensajesModel->crear($asunto, $cuerpo, $remitenteId, 'usuario', (string)$destinatarioId);
        } catch (Exception $e) {
            error_log("Error enviarMensajeRechazo: " . $e->getMessage());
        }
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

    // ───────────────────────── Evidencias (archivos del seguimiento) ─────────────────────────
    // Se guardan en evidencias/AÑO/ID_INDICADOR[/FACULTAD]/ en la raíz del proyecto. La carpeta no es pública
    // (.htaccess); los archivos se consultan por verEvidencia, que comprueba permisos.
    const EVIDENCIAS_MAX = 5;
    const EVIDENCIAS_MAX_BYTES = 10485760; // 10 MB
    private static $evidenciasTipos = [
        'pdf' => 'application/pdf', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif', 'webp' => 'image/webp', 'txt' => 'text/plain', 'csv' => 'text/csv',
        'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    private function esAdminSeguimiento() {
        $uid = (int)($_SESSION['usuario_id'] ?? 0);
        $rol = $this->normalizarRol($_SESSION['usuario_rol'] ?? '');
        return $uid === 1 || in_array($rol, ['admin', 'administrador', 'sub administrador'], true);
    }

    // Quién puede cargar/quitar: evidencias del indicador → administradores, creador y dependencia responsable;
    // evidencias de una facultad → quien puede registrar el seguimiento de esa facultad.
    private function puedeEditarEvidencias(array $item, $facultadId, $semestre = 1) {
        // Un semestre bloqueado (Editar formulario → Campos) tampoco admite cambios en sus evidencias
        $bl = $this->semestresBloqueados($item);
        if (!empty($bl[(int)$semestre === 2 ? 'sem2' : 'sem1'])) return false;
        if ($facultadId > 0) return $this->puedeRegistrarSeguimientoFacultad($item, $facultadId);
        $uid = (int)($_SESSION['usuario_id'] ?? 0);
        return $this->esAdminSeguimiento() || ($uid > 0 && $uid === (int)($item['creado_por'] ?? 0)) || $this->esResponsableDelItem($item);
    }

    // Quién puede consultar: cualquiera con acceso al indicador; las evidencias de otra facultad solo su facultad
    private function puedeVerEvidencias(array $item, $facultadId) {
        if ($facultadId <= 0) return true;
        $propias = $this->facultadesRestringidas($item);
        return !$propias || in_array((int)$facultadId, $propias, true);
    }

    private function slugEvidencia($texto) {
        $texto = strtr((string)$texto, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N','ü'=>'u','Ü'=>'U']);
        $texto = strtoupper(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $texto), '-'));
        return $texto !== '' ? substr($texto, 0, 60) : 'SIN-NOMBRE';
    }

    // Carpeta relativa a la raíz: evidencias/AÑO/ID[/FACULTAD]/semestre-N
    private function carpetaEvidencias(array $item, $facultadId, $semestre = 1) {
        $anio = preg_replace('/\D/', '', (string)($item['anio'] ?? ''));
        $ruta = 'evidencias/' . ($anio !== '' ? $anio : 'sin-anio') . '/' . (int)$item['id'];
        if ($facultadId > 0) {
            $ruta .= '/' . $this->slugEvidencia($this->model->getNombreFacultad($facultadId) ?: ('facultad-' . (int)$facultadId));
        }
        return $ruta . '/semestre-' . ((int)$semestre === 2 ? 2 : 1);
    }

    private function semestreEvidencias($origen) {
        return (int)($origen['semestre'] ?? 1) === 2 ? 2 : 1;
    }

    private function cargarItemEvidencias(&$formulacionId, &$facultadId, $origen) {
        $formulacionId = (int)($origen['formulacion_id'] ?? 0);
        $facultadId = (int)($origen['facultad_id'] ?? 0);
        return $formulacionId > 0 ? $this->model->getById('seguimiento', $formulacionId) : null;
    }

    public function resumenEvidencias() {
        header('Content-Type: application/json');
        $fid = $fac = 0;
        $item = $this->cargarItemEvidencias($fid, $fac, $_GET);
        if (!$item) { echo json_encode(['success' => false, 'message' => 'Indicador no válido']); return; }
        $res = $this->model->getResumenEvidencias($fid);
        $propias = $this->facultadesRestringidas($item);
        if ($propias) {
            $res['facultades'] = array_intersect_key($res['facultades'], array_flip($propias));
        }
        echo json_encode(['success' => true, 'general' => $res['general'], 'facultades' => (object)$res['facultades']]);
    }

    public function listarEvidencias() {
        header('Content-Type: application/json');
        $fid = $fac = 0;
        $item = $this->cargarItemEvidencias($fid, $fac, $_GET);
        if (!$item) { echo json_encode(['success' => false, 'message' => 'Indicador no válido']); return; }
        if (!$this->puedeVerEvidencias($item, $fac)) { echo json_encode(['success' => false, 'message' => 'No tienes acceso a estas evidencias']); return; }
        $sem = $this->semestreEvidencias($_GET);
        $archivos = array_map(function ($e) {
            return ['id' => (int)$e['id'], 'nombre' => $e['nombre_original'], 'tamano' => (int)$e['tamano'], 'fecha' => $e['fecha_creacion']];
        }, $this->model->getEvidencias($fid, $fac, $sem));
        echo json_encode([
            'success' => true,
            'archivos' => $archivos,
            'maximo' => self::EVIDENCIAS_MAX,
            'semestre' => $sem,
            'puede_editar' => $this->puedeEditarEvidencias($item, $fac, $sem),
            'carpeta' => $this->carpetaEvidencias($item, $fac, $sem) . '/',
        ]);
    }

    public function subirEvidencia() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success' => false, 'message' => 'Método no permitido']); return; }
        $fid = $fac = 0;
        $item = $this->cargarItemEvidencias($fid, $fac, $_POST);
        if (!$item) { echo json_encode(['success' => false, 'message' => 'Indicador no válido']); return; }
        $sem = $this->semestreEvidencias($_POST);
        if (!$this->puedeEditarEvidencias($item, $fac, $sem)) { echo json_encode(['success' => false, 'message' => 'No puedes cargar evidencias aquí (¿el semestre está bloqueado?)']); return; }

        $f = $_FILES['archivo'] ?? null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
            $msg = ($f && in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true))
                ? 'El archivo supera el tamaño permitido' : 'No se pudo recibir el archivo';
            echo json_encode(['success' => false, 'message' => $msg]);
            return;
        }
        if ($f['size'] > self::EVIDENCIAS_MAX_BYTES) { echo json_encode(['success' => false, 'message' => 'Cada archivo puede pesar máximo 10 MB']); return; }
        $nombre = trim(basename(str_replace('\\', '/', (string)$f['name'])));
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        if (!isset(self::$evidenciasTipos[$ext])) {
            echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido (PDF, imágenes, Word, Excel, PowerPoint, TXT o CSV)']);
            return;
        }
        if ($this->model->contarEvidencias($fid, $fac, $sem) >= self::EVIDENCIAS_MAX) {
            echo json_encode(['success' => false, 'message' => 'Solo puedes cargar ' . self::EVIDENCIAS_MAX . ' evidencias. Quita una para agregar otra']);
            return;
        }

        $raiz = dirname(__DIR__);
        $carpeta = $this->carpetaEvidencias($item, $fac, $sem);
        $dir = $raiz . '/' . $carpeta;
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) { echo json_encode(['success' => false, 'message' => 'No se pudo crear la carpeta de evidencias']); return; }
        // La carpeta no se sirve directamente: todo pasa por verEvidencia
        $ht = $raiz . '/evidencias/.htaccess';
        if (!is_file($ht)) {
            @file_put_contents($ht, "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
        }

        $guardado = bin2hex(random_bytes(8)) . '.' . $ext;
        if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $guardado)) { echo json_encode(['success' => false, 'message' => 'No se pudo guardar el archivo']); return; }
        $id = $this->model->guardarEvidencia($fid, $fac, mb_substr($nombre, 0, 255, 'UTF-8'), $carpeta . '/' . $guardado, $f['size'], $_SESSION['usuario_id'] ?? 0, $sem);
        if ($id <= 0) { @unlink($dir . '/' . $guardado); echo json_encode(['success' => false, 'message' => 'No se pudo registrar el archivo']); return; }
        echo json_encode(['success' => true, 'id' => $id]);
    }

    public function eliminarEvidencia() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success' => false, 'message' => 'Método no permitido']); return; }
        $ev = $this->model->getEvidencia((int)($_POST['id'] ?? 0));
        $item = $ev ? $this->model->getById('seguimiento', (int)$ev['formulacion_id']) : null;
        if (!$ev || !$item) { echo json_encode(['success' => false, 'message' => 'Evidencia no encontrada']); return; }
        if (!$this->puedeEditarEvidencias($item, (int)$ev['facultad_id'], (int)($ev['semestre'] ?? 1))) { echo json_encode(['success' => false, 'message' => 'No puedes quitar esta evidencia (¿el semestre está bloqueado?)']); return; }
        $ok = $this->model->eliminarEvidencia((int)$ev['id']);
        if ($ok) @unlink(dirname(__DIR__) . '/' . $ev['ruta']);
        echo json_encode(['success' => $ok]);
    }

    // Entrega el archivo (enlace de la ventana de evidencias) tras comprobar el acceso
    public function verEvidencia() {
        $ev = $this->model->getEvidencia((int)($_GET['id'] ?? 0));
        $item = $ev ? $this->model->getById('seguimiento', (int)$ev['formulacion_id']) : null;
        $ruta = $ev ? dirname(__DIR__) . '/' . $ev['ruta'] : '';
        if (!$ev || !$item || !$this->puedeVerEvidencias($item, (int)$ev['facultad_id']) || !is_file($ruta)) {
            http_response_code(404);
            echo 'Archivo no disponible';
            return;
        }
        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        $mime = self::$evidenciasTipos[$ext] ?? 'application/octet-stream';
        $enLinea = in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'txt'], true);
        header('Content-Type: ' . $mime);
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: ' . filesize($ruta));
        header('Content-Disposition: ' . ($enLinea ? 'inline' : 'attachment') . "; filename*=UTF-8''" . rawurlencode($ev['nombre_original']));
        readfile($ruta);
    }

    // Semestres cuyas casillas de seguimiento están bloqueadas (aplica a todos; se desbloquean en Editar formulario → Campos)
    private function semestresBloqueados(array $item) {
        $b = $this->model->getBloqueosSemestres((int)($item['formulario_id'] ?? 0));
        if ($b['excepto_admin'] && $this->esAdminSeguimiento()) return ['sem1' => false, 'sem2' => false, 'excepto_admin' => true];
        return $b;
    }

    // ───────── Concertaciones por facultad ─────────
    // Concertan: administradores, quien creó el indicador y la dependencia responsable (la facultad no concierta lo suyo)
    private function puedeConcertar(array $item) {
        $uid = (int)($_SESSION['usuario_id'] ?? 0);
        return $this->esAdminSeguimiento() || ($uid > 0 && $uid === (int)($item['creado_por'] ?? 0)) || $this->esResponsableDelItem($item);
    }

    // Tabla del indicador con todas sus facultades: meta propuesta, meta hecha por semestre y su concertación
    public function getConcertacion() {
        header('Content-Type: application/json');
        $id = intval($_GET['formulacion_id'] ?? 0);
        $item = $id > 0 ? $this->model->getById('seguimiento', $id) : null;
        if (!$item) { echo json_encode(['success' => false, 'message' => 'Indicador no válido']); return; }

        $propias = $this->facultadesRestringidas($item);
        $nombres = [];
        foreach ($this->model->getFacultades() as $f) $nombres[(int)$f['id']] = $f['nombre'];
        $conc = $this->model->getConcertaciones($id);
        $esLimite = ($item['tipo_medicion'] ?? '') === 'Límite';

        // Lo que reportan todas las facultades (antes de limitar la vista a la facultad del usuario)
        $todas = $this->model->getDistribucionFacultades($id);
        $sumaFac = [1 => null, 2 => null];
        foreach ($todas as $d) {
            foreach ([1, 2] as $sem) {
                $v = $d['seguimiento_sem' . $sem] ?? null;
                if ($v !== null && $v !== '') $sumaFac[$sem] = ($sumaFac[$sem] ?? 0) + (float)$v;
            }
        }
        $numDep = function ($v) { return ($v === null || $v === '') ? null : (float)$v; };

        $filas = [];
        foreach ($todas as $d) {
            $fid = (int)$d['facultad_id'];
            $reporto = (isset($d['seguimiento_sem1']) && $d['seguimiento_sem1'] !== null && $d['seguimiento_sem1'] !== '')
                    || (isset($d['seguimiento_sem2']) && $d['seguimiento_sem2'] !== null && $d['seguimiento_sem2'] !== '');
            if ((float)$d['distribucion'] <= 0 && !$reporto) continue; // sin meta y sin reporte: nada que concertar
            if ($propias && !in_array($fid, $propias, true)) continue;
            $num = function ($v) { return ($v === null || $v === '') ? null : (float)$v; };
            $fila = [
                'facultad_id' => $fid,
                'puede' => $this->puedeRegistrarSeguimientoFacultad($item, $fid), // la facultad concierta lo suyo (también administradores y responsables)
                'nombre' => $nombres[$fid] ?? ('Facultad ' . $fid),
                'propuesta' => (float)$d['distribucion'],
                'hecha' => [1 => $esLimite ? null : $num($d['seguimiento_sem1'] ?? null), 2 => $num($d['seguimiento_sem2'] ?? null)],
                'descripcion' => [1 => $d['seguimiento_descripcion'] ?? '', 2 => $d['seguimiento_descripcion2'] ?? ''],
            ];
            foreach ([1, 2] as $sem) {
                $c = $conc[$fid][$sem] ?? null;
                $fila['c' . $sem] = $c ? [
                    'concertado' => (int)$c['concertado'] === 1,
                    'valor' => $c['valor_concertado'] !== null ? (float)$c['valor_concertado'] : null,
                    'usuario' => $c['usuario'] ?? '', 'fecha' => $c['fecha_actualizacion'] ?? '',
                ] : ['concertado' => false, 'valor' => null, 'usuario' => '', 'fecha' => ''];
            }
            $filas[] = $fila;
        }
        echo json_encode([
            'success' => true,
            'indicador' => [
                'id' => $id,
                'nombre' => trim((string)($item['nombre_indicador'] ?? '')) !== '' ? $item['nombre_indicador'] : ($item['formula_medicion'] ?: $item['nombre_borrador']),
                'unidad' => $item['unidad_medida'] ?? '', 'tipo' => $item['tipo_medicion'] ?? '',
                'lmp' => implode(' · ', array_filter([$item['linea_codigo'] ?? '', $item['motor_codigo'] ?? '', $item['proyecto_codigo'] ?? ''])),
                'meta' => $item['anio_base_meta'] ?? null,
                'bloqueo' => $this->semestresBloqueados($item),
            ],
            'completo' => empty($propias), // false cuando el usuario solo ve su facultad
            'puede_concertar' => count(array_filter($filas, function ($f) { return $f['puede']; })) > 0,
            'dependencia' => [
                'nombre' => (string)($item['responsable_formulacion'] ?? ''),
                's1' => $esLimite ? null : $numDep($item['semestre1_seguimiento'] ?? null),
                's2' => $numDep($item['semestre2_seguimiento'] ?? null),
                'suma1' => $esLimite ? null : $sumaFac[1], 'suma2' => $sumaFac[2],
            ],
            'facultades' => $filas,
        ]);
    }

    public function guardarConcertacion() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success' => false, 'message' => 'Método no permitido']); return; }
        $id = intval($_POST['formulacion_id'] ?? 0);
        $fac = intval($_POST['facultad_id'] ?? 0);
        $sem = intval($_POST['semestre'] ?? 0);
        $concertado = !empty($_POST['concertado']) && $_POST['concertado'] !== '0';
        $item = $id > 0 ? $this->model->getById('seguimiento', $id) : null;
        if (!$item || $fac <= 0 || !in_array($sem, [1, 2], true)) { echo json_encode(['success' => false, 'message' => 'Datos no válidos']); return; }
        // La facultad concierta con la dependencia responsable: cada una confirma lo suyo (y los administradores o responsables pueden hacerlo por ella)
        if (!$this->puedeRegistrarSeguimientoFacultad($item, $fac)) { echo json_encode(['success' => false, 'message' => 'Solo la propia facultad puede concertar su seguimiento']); return; }

        $actual = null;
        foreach ($this->model->getDistribucionFacultades($id) as $d) {
            if ((int)$d['facultad_id'] === $fac) { $actual = $d; break; }
        }
        if (!$actual) { echo json_encode(['success' => false, 'message' => 'La facultad no tiene seguimiento registrado en este indicador']); return; }

        $hecha = $actual['seguimiento_sem' . $sem] ?? null;
        $valor = null;
        if ($concertado) {
            if ($hecha === null || $hecha === '') { echo json_encode(['success' => false, 'message' => 'La facultad aún no reporta este semestre']); return; }
            $v = trim((string)($_POST['valor'] ?? ''));
            $valor = $v === '' ? (float)$hecha : (float)str_replace(',', '.', $v);
        }
        $ok = $this->model->guardarConcertacion($id, $fac, $sem, $concertado, $valor, trim((string)($_POST['observacion'] ?? '')) ?: null, $_SESSION['usuario_id'] ?? 0);
        echo json_encode(['success' => $ok, 'message' => $ok ? ($concertado ? 'Concertado' : 'Concertación deshecha') : 'No se pudo guardar']);
    }
}
?>