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
        
        require_once $vistaPath;
    }
    
    /**
     * Procesa la creación de un nuevo formulario
     */
    public function crearFormulario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            
            if (empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'El título es obligatorio']);
                return;
            }
            
            $data = [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'estado' => 1
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
            $estado = $_POST['estado'] ?? 1;
            
            if (empty($id) || empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }
            
            $data = [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'estado' => $estado
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