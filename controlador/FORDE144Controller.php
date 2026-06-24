<?php 
// controlador/FORDE144Controller.php - VERSIÓN PRODUCCIÓN
require_once 'modelo/FORDE144Model.php';

class FORDE144Controller {
    private $model;
    
    public function __construct() {
        $this->model = new FORDE144Model();
    }
    
    /**
     * Muestra la lista de formularios
     */
    public function index() {
        $vistaPath = 'vista/FOR-DE-144/index.php';
        if (!file_exists($vistaPath)) {
            die("Error: No se encuentra la vista en: $vistaPath");
        }

        $formularios = $this->model->getAll();
        $anios       = $this->model->getAnios();

        // Sub-permisos FOR-DE-144: solo el superadmin (id=1) tiene todo por defecto;
        // el resto (incluyendo admins) usa lo que se asignó en detalle_subpermiso
        $currentUserId = (int)($_SESSION['usuario_id'] ?? 0);
        $perms_f144 = ['crear' => true, 'editar' => true, 'informe' => true, 'eliminar' => true, 'ver' => true, 'configurar' => true];
        if ($currentUserId !== 1) {
            require_once 'modelo/permisoModel.php';
            $pm        = new PermisoModel();
            $subPerms  = $pm->getSubpermisosActivosUsuario($currentUserId);
            $perms_f144 = array_fill_keys(['crear', 'editar', 'informe', 'eliminar', 'ver', 'configurar'], false);
            foreach ($subPerms as $s) {
                if (array_key_exists($s['nombre'], $perms_f144)) {
                    $perms_f144[$s['nombre']] = true;
                }
            }
        }

        require_once $vistaPath;
    }
    
    /**
     * Verifica si el usuario actual tiene un sub-permiso específico de FOR-DE-144.
     * El superadmin (id=1) siempre puede; los demás consultan detalle_subpermiso.
     */
    private function puedeHacer(string $accion): bool {
        $uid = (int)($_SESSION['usuario_id'] ?? 0);
        if ($uid === 1) return true;
        require_once 'modelo/permisoModel.php';
        $pm = new PermisoModel();
        $subs = $pm->getSubpermisosActivosUsuario($uid);
        foreach ($subs as $s) {
            if ($s['nombre'] === $accion) return true;
        }
        return false;
    }

    /**
     * Procesa la creación de un nuevo formulario
     */
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->puedeHacer('crear')) {
                echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear formularios']);
                return;
            }
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tipo_tiempo = $_POST['tipo_tiempo'] ?? 'libre';
            $fecha_inicio = $_POST['fecha_inicio'] ?? null;
            $fecha_fin = $_POST['fecha_fin'] ?? null;
            $anio = $_POST['anio'] ?? null;
            
            if (empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'El título es obligatorio']);
                return;
            }

            if (empty($anio) || !is_numeric($anio) || strlen($anio) !== 4) {
                echo json_encode(['success' => false, 'message' => 'El año es obligatorio y debe tener 4 dígitos']);
                return;
            }
            
            $data = [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'tipo_tiempo' => $tipo_tiempo,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'estado' => 1,
                'anio' => intval($anio)
            ];
            
            $resultado = $this->model->create($data);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Formulario creado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al guardar en la base de datos'
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        }
    }
    
    /**
     * Obtiene un formulario específico por ID
     */
    public function getFormulario($id) {
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }
        
        $formulario = $this->model->getById($id);
        
        if ($formulario) {
            echo json_encode(['success' => true, 'formulario' => $formulario]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Formulario no encontrado']);
        }
    }
    
    /**
     * Obtiene todos los formularios para AJAX
     */
    public function obtenerFormularios() {
        $formularios = $this->model->getAll();
        echo json_encode(['success' => true, 'formularios' => $formularios]);
    }
    
    /**
     * Elimina un formulario
     */
    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->puedeHacer('eliminar')) {
                echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar formularios']);
                return;
            }

            $id = $_POST['id'] ?? 0;

            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
            }
            
            $resultado = $this->model->delete($id);
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Formulario eliminado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el formulario']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        }
    }
    
    public function informePage($id) {
        if (!$this->puedeHacer('informe')) {
            header('Location: ' . Config::getBasePath() . '/FOR-DE-144');
            exit;
        }
        if (empty($id)) {
            header('Location: ' . Config::getBasePath() . '/FOR-DE-144');
            exit;
        }
        $formulario = $this->model->getById($id);
        if (!$formulario) {
            header('Location: ' . Config::getBasePath() . '/FOR-DE-144');
            exit;
        }
        $informe = $this->model->getInformeCompleto($id);
        require_once 'vista/FOR-DE-144/informe.php';
    }

    public function informe($id) {
        if (!$this->puedeHacer('informe')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para ver el informe']);
            return;
        }
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }
        $formulario = $this->model->getById($id);
        if (!$formulario) {
            echo json_encode(['success' => false, 'message' => 'Formulario no encontrado']);
            return;
        }
        $data = $this->model->getInforme($id);
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Error al generar el informe']);
            return;
        }
        echo json_encode(['success' => true, 'formulario' => $formulario, 'data' => $data]);
    }

    /**
     * Edita un formulario existente
     */
    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->puedeHacer('editar')) {
                echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar formularios']);
                return;
            }

            $id = $_POST['id'] ?? 0;
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tipo_tiempo = $_POST['tipo_tiempo'] ?? 'libre';
            $fecha_inicio = $_POST['fecha_inicio'] ?? null;
            $fecha_fin = $_POST['fecha_fin'] ?? null;
            $estado = $_POST['estado'] ?? 1;
            $anio = $_POST['anio'] ?? null;
            
            if (empty($id) || empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }

            if (empty($anio) || !is_numeric($anio) || strlen((string)$anio) !== 4) {
                echo json_encode(['success' => false, 'message' => 'El año es obligatorio y debe tener 4 dígitos']);
                return;
            }
            
            $data = [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'tipo_tiempo' => $tipo_tiempo,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'estado' => $estado,
                'anio' => intval($anio)
            ];
            
            $resultado = $this->model->update($id, $data);
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Formulario actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el formulario']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        }
    }
}
?>