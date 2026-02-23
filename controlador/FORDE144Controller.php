<<<<<<< Updated upstream
<?php 
// controlador/FORDE144Controller.php - VERSIÓN PRODUCCIÓN
require_once 'modelo/FORDE144Model.php';
=======
<?php
// controlador/FORDE144Controller.php
require_once __DIR__ . '/../modelo/FORDE144Model.php';

// Desactivar muestra de errores en producción
ini_set('display_errors', 0);
error_reporting(E_ALL);
>>>>>>> Stashed changes

class FORDE144Controller {
    private $model;
    
    public function __construct() {
        $this->model = new FORDE144Model();
    }
    
    public function index() {
        $vistaPath = __DIR__ . '/../vista/FOR-DE-144/index.php';
        if (!file_exists($vistaPath)) {
            die("Error: No se encuentra la vista");
        }
        
<<<<<<< Updated upstream
        $formularios = $this->model->getAllAdmin(); // Usamos getAllAdmin para ver todos en administración
=======
        $formularios = $this->model->getAll();
        
        foreach ($formularios as &$formulario) {
            $disponibilidad = $this->model->verificarDisponibilidad($formulario['id']);
            $formulario['disponible'] = $disponibilidad['disponible'] ?? 1;
            $formulario['estado_tiempo'] = $this->getEstadoTiempo($formulario);
        }
>>>>>>> Stashed changes
        
        require_once $vistaPath;
    }
    
<<<<<<< Updated upstream
    /**
     * Procesa la creación de un nuevo formulario
     */
    public function crearFormulario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
=======
    private function getEstadoTiempo($formulario) {
        $ahora = new DateTime();
        
        if (empty($formulario['fecha_inicio']) && empty($formulario['fecha_cierre'])) {
            return 'sin_restricciones';
        }
        
        if (!empty($formulario['fecha_inicio']) && !empty($formulario['fecha_cierre'])) {
            $inicio = new DateTime($formulario['fecha_inicio']);
            $cierre = new DateTime($formulario['fecha_cierre']);
            
            if ($ahora < $inicio) return 'proximamente';
            if ($ahora > $cierre) return 'cerrado';
            return 'activo';
        }
        
        return 'sin_restricciones';
    }
    
    public function crear() {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
>>>>>>> Stashed changes
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tipo_tiempo = $_POST['tipo_tiempo'] ?? 'libre';
            $fecha_inicio = $_POST['fecha_inicio'] ?? null;
            $fecha_fin = $_POST['fecha_fin'] ?? null;
            
            if (empty($titulo)) {
                throw new Exception('El título es obligatorio');
            }
            
            // Validar fechas si es tipo rango
            if ($tipo_tiempo === 'rango') {
                if (empty($fecha_inicio) || empty($fecha_fin)) {
                    echo json_encode(['success' => false, 'message' => 'Las fechas de inicio y fin son obligatorias para tiempo con rango']);
                    return;
                }
                
                if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
                    echo json_encode(['success' => false, 'message' => 'La fecha de inicio debe ser menor a la fecha de fin']);
                    return;
                }
            }
            
            $data = [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'tipo_tiempo' => $tipo_tiempo,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'estado' => 1
            ];
            
<<<<<<< Updated upstream
=======
            if ($tipo_tiempo === 'con_restricciones') {
                $data['fecha_inicio'] = $_POST['fecha_inicio'] ?? null;
                $data['fecha_cierre'] = $_POST['fecha_cierre'] ?? null;
                
                if (empty($data['fecha_inicio']) || empty($data['fecha_cierre'])) {
                    throw new Exception('Las fechas son obligatorias');
                }
                
                if (strtotime($data['fecha_inicio']) > strtotime($data['fecha_cierre'])) {
                    throw new Exception('La fecha de inicio no puede ser mayor a la fecha de cierre');
                }
            } else {
                $data['fecha_inicio'] = null;
                $data['fecha_cierre'] = null;
            }
            
>>>>>>> Stashed changes
            $resultado = $this->model->create($data);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Formulario creado exitosamente'
                ]);
            } else {
                throw new Exception('Error al guardar en la base de datos');
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    
<<<<<<< Updated upstream
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
            // Formatear fechas para el input datetime-local
            if ($formulario['fecha_inicio']) {
                $formulario['fecha_inicio'] = date('Y-m-d\TH:i', strtotime($formulario['fecha_inicio']));
            }
            if ($formulario['fecha_fin']) {
                $formulario['fecha_fin'] = date('Y-m-d\TH:i', strtotime($formulario['fecha_fin']));
            }
            
            echo json_encode(['success' => true, 'formulario' => $formulario]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Formulario no encontrado']);
        }
    }
    
    /**
     * Obtiene todos los formularios para AJAX
     */
    public function obtenerFormularios() {
        $formularios = $this->model->getAllAdmin();
        echo json_encode(['success' => true, 'formularios' => $formularios]);
=======
    public function obtenerFormularios() {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $formularios = $this->model->getAll();
            
            foreach ($formularios as &$formulario) {
                $disponibilidad = $this->model->verificarDisponibilidad($formulario['id']);
                $formulario['disponible'] = $disponibilidad['disponible'] ?? 1;
            }
            
            echo json_encode(['success' => true, 'formularios' => $formularios]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
>>>>>>> Stashed changes
    }
    
    public function eliminar() {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                throw new Exception('ID no válido');
            }
            
            $resultado = $this->model->delete($id);
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Formulario eliminado exitosamente']);
            } else {
                throw new Exception('Error al eliminar el formulario');
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    public function editar() {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = $_POST['id'] ?? 0;
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
<<<<<<< Updated upstream
            $tipo_tiempo = $_POST['tipo_tiempo'] ?? 'libre';
            $fecha_inicio = $_POST['fecha_inicio'] ?? null;
            $fecha_fin = $_POST['fecha_fin'] ?? null;
            $estado = $_POST['estado'] ?? 1;
=======
            $tipo_tiempo = $_POST['edit_tipo_tiempo'] ?? 'sin_restricciones';
>>>>>>> Stashed changes
            
            if (empty($id) || empty($titulo)) {
                throw new Exception('Datos incompletos');
            }
            
            // Validar fechas si es tipo rango
            if ($tipo_tiempo === 'rango') {
                if (empty($fecha_inicio) || empty($fecha_fin)) {
                    echo json_encode(['success' => false, 'message' => 'Las fechas de inicio y fin son obligatorias para tiempo con rango']);
                    return;
                }
                
                if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
                    echo json_encode(['success' => false, 'message' => 'La fecha de inicio debe ser menor a la fecha de fin']);
                    return;
                }
            }
            
            $data = [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
<<<<<<< Updated upstream
                'tipo_tiempo' => $tipo_tiempo,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'estado' => $estado
            ];
            
=======
                'estado' => 1
            ];
            
            if ($tipo_tiempo === 'con_restricciones') {
                $data['fecha_inicio'] = $_POST['fecha_inicio'] ?? null;
                $data['fecha_cierre'] = $_POST['fecha_cierre'] ?? null;
                
                if (empty($data['fecha_inicio']) || empty($data['fecha_cierre'])) {
                    throw new Exception('Las fechas son obligatorias');
                }
                
                if (strtotime($data['fecha_inicio']) > strtotime($data['fecha_cierre'])) {
                    throw new Exception('La fecha de inicio no puede ser mayor a la fecha de cierre');
                }
            } else {
                $data['fecha_inicio'] = null;
                $data['fecha_cierre'] = null;
            }
            
>>>>>>> Stashed changes
            $resultado = $this->model->update($id, $data);
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Formulario actualizado exitosamente']);
            } else {
                throw new Exception('Error al actualizar el formulario');
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
<<<<<<< Updated upstream
=======
    
    public function verificarDisponibilidad() {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $id = $_GET['id'] ?? 0;
            
            if (empty($id)) {
                throw new Exception('ID no válido');
            }
            
            $disponibilidad = $this->model->verificarDisponibilidad($id);
            
            if ($disponibilidad) {
                echo json_encode([
                    'success' => true, 
                    'disponible' => $disponibilidad['disponible'],
                    'fecha_inicio' => $disponibilidad['fecha_inicio'],
                    'fecha_cierre' => $disponibilidad['fecha_cierre']
                ]);
            } else {
                throw new Exception('Formulario no encontrado');
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
>>>>>>> Stashed changes
}
?>