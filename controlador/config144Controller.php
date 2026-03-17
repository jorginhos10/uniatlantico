<?php
// controlador/config144Controller.php
require_once 'modelo/config144Model.php';

class config144Controller {
    private $model;

    public function __construct() {
        $this->model = new config144Model();
    }

    /**
     * Página principal de gestión de años
     */
    public function index() {
        $vistaPath = 'vista/configuraciones/config144.php';
        if (!file_exists($vistaPath)) {
            die("Error crítico: No se encuentra la vista en: " . $vistaPath);
        }

        require_once $vistaPath;
    }

    /**
     * Obtener todos los años (API)
     */
    public function listar() {
        header('Content-Type: application/json');
        $anos = $this->model->getAnos();
        echo json_encode(['success' => true, 'anos' => $anos]);
    }

    /**
     * Obtener años activos (API)
     */
    public function activos() {
        header('Content-Type: application/json');
        $anos = $this->model->getAnosActivos();
        echo json_encode(['success' => true, 'anos' => $anos]);
    }

    /**
     * Crear un nuevo año
     */
    public function crear() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $anio = isset($_POST['anio']) ? intval($_POST['anio']) : 0;
        $activo = isset($_POST['activo']) ? intval($_POST['activo']) : 1;
        $orden = isset($_POST['orden']) && $_POST['orden'] !== '' ? intval($_POST['orden']) : null;

        if ($anio <= 0) {
            echo json_encode(['success' => false, 'message' => 'El año debe ser un número válido']);
            return;
        }

        if ($anio < 2000 || $anio > 2100) {
            echo json_encode(['success' => false, 'message' => 'El año debe estar entre 2000 y 2100']);
            return;
        }

        $resultado = $this->model->crearAno($anio, $activo, $orden);
        echo json_encode($resultado);
    }

    /**
     * Obtener un año por ID
     */
    public function get() {
        header('Content-Type: application/json');
        
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }

        $ano = $this->model->getAnoById($id);
        
        if ($ano) {
            echo json_encode(['success' => true, 'ano' => $ano]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Año no encontrado']);
        }
    }

    /**
     * Actualizar un año
     */
    public function actualizar() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $anio = isset($_POST['anio']) ? intval($_POST['anio']) : 0;
        $activo = isset($_POST['activo']) ? intval($_POST['activo']) : null;
        $orden = isset($_POST['orden']) && $_POST['orden'] !== '' ? intval($_POST['orden']) : null;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }

        if ($anio <= 0) {
            echo json_encode(['success' => false, 'message' => 'El año debe ser un número válido']);
            return;
        }

        if ($anio < 2000 || $anio > 2100) {
            echo json_encode(['success' => false, 'message' => 'El año debe estar entre 2000 y 2100']);
            return;
        }

        $resultado = $this->model->actualizarAno($id, $anio, $activo, $orden);
        echo json_encode($resultado);
    }

    /**
     * Cambiar estado (activar/desactivar)
     */
    public function cambiarEstado() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $activo = isset($_POST['activo']) ? intval($_POST['activo']) : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }

        $resultado = $this->model->cambiarEstado($id, $activo);
        echo json_encode($resultado);
    }

    /**
     * Eliminar un año
     */
    public function eliminar() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }

        $resultado = $this->model->eliminarAno($id);
        echo json_encode($resultado);
    }

    /**
     * Actualizar orden de los años (para drag & drop)
     */
    public function actualizarOrden() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $ordenes = isset($_POST['ordenes']) ? json_decode($_POST['ordenes'], true) : [];

        if (empty($ordenes)) {
            echo json_encode(['success' => false, 'message' => 'No hay datos para actualizar']);
            return;
        }

        $resultado = $this->model->reordenarAnos($ordenes);
        echo json_encode($resultado);
    }
}
?>