<?php
// controlador/config144Controller.php
require_once 'modelo/config144Model.php';

class config144Controller {
    private $model;

    public function __construct() {
        $this->model = new config144Model();
    }

    public function index() {
        $vistaPath = 'vista/configuraciones/config144.php';
        if (!file_exists($vistaPath)) {
            die("Error crítico: No se encuentra la vista en: " . $vistaPath);
        }

        require_once $vistaPath;
    }

    public function listar() {
        header('Content-Type: application/json');
        $anos = $this->model->getAnos();
        echo json_encode(['success' => true, 'anos' => $anos]);
    }

    public function activos() {
        header('Content-Type: application/json');
        $anos = $this->model->getAnosActivos();
        echo json_encode(['success' => true, 'anos' => $anos]);
    }

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

    public function duplicar() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $id   = isset($_POST['id'])   ? intval($_POST['id'])   : 0;
        $anio = isset($_POST['anio']) ? intval($_POST['anio']) : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            return;
        }
        if ($anio < 2000 || $anio > 2100) {
            echo json_encode(['success' => false, 'message' => 'El año debe estar entre 2000 y 2100']);
            return;
        }
        echo json_encode($this->model->duplicarAno($id, $anio));
    }

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

    public function getLineasEstrategicas() {
        header('Content-Type: application/json');
        
        $lineas = $this->model->getLineasEstrategicas();
        
        echo json_encode(['success' => true, 'lineas' => $lineas]);
    }

    public function getMotoresPorLinea() {
        header('Content-Type: application/json');
        
        $linea_id = isset($_GET['linea_id']) ? intval($_GET['linea_id']) : 0;
        
        if ($linea_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de línea no válido']);
            return;
        }
        
        $motores = $this->model->getMotoresPorLinea($linea_id);
        echo json_encode(['success' => true, 'motores' => $motores]);
    }

    public function getProyectosPorMotor() {
        header('Content-Type: application/json');
        
        $motor_id = isset($_GET['motor_id']) ? intval($_GET['motor_id']) : 0;
        
        if ($motor_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de motor no válido']);
            return;
        }
        
        $proyectos = $this->model->getProyectosPorMotor($motor_id);
        echo json_encode(['success' => true, 'proyectos' => $proyectos]);
    }

    public function getDataMotores() {
        header('Content-Type: application/json');
        
        $anio = isset($_GET['anio']) ? intval($_GET['anio']) : 0;
        $linea_id = isset($_GET['linea_id']) ? intval($_GET['linea_id']) : 0;
        
        if ($anio <= 0 || $linea_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Año o línea no válidos']);
            return;
        }
        
        $datos = $this->model->getDataMotores($anio, $linea_id);
        echo json_encode(['success' => true, 'datos' => ['motor' => $datos]]);
    }

    public function getDataProyectos() {
        header('Content-Type: application/json');
        
        $anio = isset($_GET['anio']) ? intval($_GET['anio']) : 0;
        $motor_id = isset($_GET['motor_id']) ? intval($_GET['motor_id']) : 0;
        
        if ($anio <= 0 || $motor_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Año o motor no válidos']);
            return;
        }
        
        $datos = $this->model->getDataProyectos($anio, $motor_id);
        echo json_encode(['success' => true, 'datos' => ['proyecto' => $datos]]);
    }

    public function guardarDataMotores() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $datos = isset($_POST['datos']) ? json_decode($_POST['datos'], true) : [];
        
        if (empty($datos)) {
            echo json_encode(['success' => false, 'message' => 'No hay datos para guardar']);
            return;
        }
        
        $total = 0;
        $anio = $datos[0]['anio'];
        $linea_id = $datos[0]['linea_id'];
        
        foreach ($datos as $item) {
            $total += floatval($item['porcentaje']);
        }
        
        if (abs($total - 100) > 0.01 && count($datos) > 1) {
            echo json_encode(['success' => false, 'message' => 'La suma de porcentajes debe ser 100%']);
            return;
        }
        
        $resultado = $this->model->guardarDataMotores($anio, $linea_id, $datos);
        echo json_encode($resultado);
    }

    public function guardarDataProyectos() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $datos = isset($_POST['datos']) ? json_decode($_POST['datos'], true) : [];
        
        if (empty($datos)) {
            echo json_encode(['success' => false, 'message' => 'No hay datos para guardar']);
            return;
        }
        
        $total = 0;
        $anio = $datos[0]['anio'];
        $motor_id = $datos[0]['motor_id'];
        
        foreach ($datos as $item) {
            $total += floatval($item['porcentaje']);
        }
        
        if (abs($total - 100) > 0.01 && count($datos) > 1) {
            echo json_encode(['success' => false, 'message' => 'La suma de porcentajes debe ser 100%']);
            return;
        }
        
        $resultado = $this->model->guardarDataProyectos($anio, $motor_id, $datos);
        echo json_encode($resultado);
    }

    public function getDataDistribucionPorAnio() {
        header('Content-Type: application/json');
        
        $anio = isset($_GET['anio']) ? intval($_GET['anio']) : 0;
        
        if ($anio <= 0) {
            echo json_encode(['success' => false, 'message' => 'Año no válido']);
            return;
        }
        
        $datos = $this->model->getDataLineasEstrategicasPorAnio($anio);
        
        if ($datos === false || $datos === null) {
            $datos = [];
        }
        
        echo json_encode(['success' => true, 'distribucion' => $datos]);
    }

    public function guardarDataDistribucion() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $distribucion = isset($_POST['distribucion']) ? json_decode($_POST['distribucion'], true) : [];
        
        if (empty($distribucion)) {
            echo json_encode(['success' => false, 'message' => 'No hay datos para guardar']);
            return;
        }
        
        $total = 0;
        $anio = $distribucion[0]['anio'];
        
        foreach ($distribucion as $item) {
            $total += floatval($item['porcentaje']);
        }
        
        if (abs($total - 100) > 0.01) {
            echo json_encode(['success' => false, 'message' => 'La suma de porcentajes debe ser 100%']);
            return;
        }
        
        $resultado = $this->model->guardarDataDistribucion($anio, $distribucion);
        echo json_encode($resultado);
    }

    public function verificarDatos() {
        header('Content-Type: application/json');
        
        $anio = isset($_GET['anio']) ? intval($_GET['anio']) : 0;
        
        if ($anio <= 0) {
            echo json_encode(['success' => false, 'message' => 'Año no válido']);
            return;
        }
        
        $tieneDatos = $this->model->verificarDatosPorAnio($anio);
        echo json_encode(['success' => true, 'tiene_datos' => $tieneDatos]);
    }
}
?>