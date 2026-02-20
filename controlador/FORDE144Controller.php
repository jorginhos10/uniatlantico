<?php 
// controlador/FORDE144Controller.php - VERSIÓN COMPLETA CON CAMPOS DE TIEMPO
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
        
        // Agregar información de disponibilidad a cada formulario
        foreach ($formularios as &$formulario) {
            $disponibilidad = $this->model->verificarDisponibilidad($formulario['id']);
            $formulario['disponible'] = $disponibilidad['disponible'] ?? 1;
            $formulario['estado_tiempo'] = $this->getEstadoTiempo($formulario);
        }
        
        require_once $vistaPath;
    }
    
    /**
     * Determina el estado de tiempo del formulario
     */
    private function getEstadoTiempo($formulario) {
        $ahora = new DateTime();
        
        if (empty($formulario['fecha_inicio']) && empty($formulario['fecha_cierre'])) {
            return 'sin_restricciones';
        }
        
        if (!empty($formulario['fecha_inicio']) && !empty($formulario['fecha_cierre'])) {
            $inicio = new DateTime($formulario['fecha_inicio']);
            $cierre = new DateTime($formulario['fecha_cierre']);
            
            if ($ahora < $inicio) {
                return 'proximamente';
            } elseif ($ahora > $cierre) {
                return 'cerrado';
            } else {
                return 'activo';
            }
        }
        
        return 'sin_restricciones';
    }
    
    /**
     * Procesa la creación de un nuevo formulario
     */
    public function crearFormulario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tipo_tiempo = $_POST['tipo_tiempo'] ?? 'sin_restricciones';
            
            if (empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'El título es obligatorio']);
                return;
            }
            
            $data = [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'estado' => 1
            ];
            
            // Asignar fechas según el tipo de tiempo seleccionado
            if ($tipo_tiempo === 'con_restricciones') {
                $data['fecha_inicio'] = $_POST['fecha_inicio'] ?? null;
                $data['fecha_cierre'] = $_POST['fecha_cierre'] ?? null;
                
                // Validar fechas
                if (empty($data['fecha_inicio']) || empty($data['fecha_cierre'])) {
                    echo json_encode(['success' => false, 'message' => 'Las fechas de inicio y cierre son obligatorias']);
                    return;
                }
                
                if (strtotime($data['fecha_inicio']) > strtotime($data['fecha_cierre'])) {
                    echo json_encode(['success' => false, 'message' => 'La fecha de inicio no puede ser mayor a la fecha de cierre']);
                    return;
                }
            } else {
                $data['fecha_inicio'] = null;
                $data['fecha_cierre'] = null;
            }
            
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
        
        // Agregar información de disponibilidad
        foreach ($formularios as &$formulario) {
            $disponibilidad = $this->model->verificarDisponibilidad($formulario['id']);
            $formulario['disponible'] = $disponibilidad['disponible'] ?? 1;
        }
        
        echo json_encode(['success' => true, 'formularios' => $formularios]);
    }
    
    /**
     * Elimina un formulario
     */
    public function eliminarFormulario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    /**
     * Edita un formulario existente
     */
    public function editarFormulario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $estado = 1;
            $tipo_tiempo = $_POST['edit_tipo_tiempo'] ?? 'sin_restricciones';
            
            if (empty($id) || empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }
            
            $data = [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'estado' => $estado
            ];
            
            // Asignar fechas según el tipo de tiempo seleccionado
            if ($tipo_tiempo === 'con_restricciones') {
                $data['fecha_inicio'] = $_POST['fecha_inicio'] ?? null;
                $data['fecha_cierre'] = $_POST['fecha_cierre'] ?? null;
                
                // Validar fechas
                if (empty($data['fecha_inicio']) || empty($data['fecha_cierre'])) {
                    echo json_encode(['success' => false, 'message' => 'Las fechas de inicio y cierre son obligatorias']);
                    return;
                }
                
                if (strtotime($data['fecha_inicio']) > strtotime($data['fecha_cierre'])) {
                    echo json_encode(['success' => false, 'message' => 'La fecha de inicio no puede ser mayor a la fecha de cierre']);
                    return;
                }
            } else {
                $data['fecha_inicio'] = null;
                $data['fecha_cierre'] = null;
            }
            
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
    
    /**
     * Verifica la disponibilidad de un formulario
     */
    public function verificarDisponibilidad() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = $_GET['id'] ?? 0;
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID no válido']);
                return;
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
                echo json_encode(['success' => false, 'message' => 'Formulario no encontrado']);
            }
        }
    }
}
?>