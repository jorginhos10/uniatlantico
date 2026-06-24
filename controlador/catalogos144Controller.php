<?php
// controlador/catalogos144Controller.php
require_once 'modelo/catalogos144Model.php';

class Catalogos144Controller {
    private $model;

    public function __construct() {
        $this->model = new Catalogos144Model();
    }

    public function index() {
        require_once 'vista/catalogos144/index.php';
    }

    // ============= LINEAS ESTRATEGICAS =============

    public function listarLineas() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'lineas' => $this->model->getAllLineas()]);
    }

    public function getLinea() {
        header('Content-Type: application/json');
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        $linea = $this->model->getLineaById($id);
        if ($linea) {
            echo json_encode(['success' => true, 'linea' => $linea]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Linea no encontrada']);
        }
    }

    public function crearLinea() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }

        $codigo   = trim($_POST['codigo'] ?? '');
        $nombre   = trim($_POST['nombre'] ?? '');
        $objetivo = trim($_POST['objetivo'] ?? '');
        $activo   = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

        if ($codigo === '' || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El codigo y el nombre son obligatorios']);
            return;
        }

        echo json_encode($this->model->crearLinea($codigo, $nombre, $objetivo, $activo));
    }

    public function actualizarLinea() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }

        $id       = intval($_POST['id'] ?? 0);
        $codigo   = trim($_POST['codigo'] ?? '');
        $nombre   = trim($_POST['nombre'] ?? '');
        $objetivo = trim($_POST['objetivo'] ?? '');
        $activo   = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

        if ($id <= 0 || $codigo === '' || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Datos invalidos']);
            return;
        }

        echo json_encode($this->model->actualizarLinea($id, $codigo, $nombre, $objetivo, $activo));
    }

    public function cambiarEstadoLinea() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }
        $id     = intval($_POST['id'] ?? 0);
        $activo = intval($_POST['activo'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        echo json_encode($this->model->cambiarEstadoLinea($id, $activo));
    }

    public function eliminarLinea() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        echo json_encode($this->model->eliminarLinea($id));
    }

    // ============= ESTRATEGIAS =============

    public function listarEstrategias() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'estrategias' => $this->model->getAllEstrategias()]);
    }

    public function getEstrategia() {
        header('Content-Type: application/json');
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        $estrategia = $this->model->getEstrategiaById($id);
        if ($estrategia) {
            echo json_encode(['success' => true, 'estrategia' => $estrategia]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Estrategia no encontrada']);
        }
    }

    public function crearEstrategia() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }

        $linea_id    = intval($_POST['linea_id'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $activo      = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

        if ($linea_id <= 0 || $descripcion === '') {
            echo json_encode(['success' => false, 'message' => 'La linea estrategica y la descripcion son obligatorias']);
            return;
        }

        echo json_encode($this->model->crearEstrategia($linea_id, $descripcion, $activo));
    }

    public function actualizarEstrategia() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }

        $id          = intval($_POST['id'] ?? 0);
        $linea_id    = intval($_POST['linea_id'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $activo      = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

        if ($id <= 0 || $linea_id <= 0 || $descripcion === '') {
            echo json_encode(['success' => false, 'message' => 'Datos invalidos']);
            return;
        }

        echo json_encode($this->model->actualizarEstrategia($id, $linea_id, $descripcion, $activo));
    }

    public function cambiarEstadoEstrategia() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }
        $id     = intval($_POST['id'] ?? 0);
        $activo = intval($_POST['activo'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        echo json_encode($this->model->cambiarEstadoEstrategia($id, $activo));
    }

    public function eliminarEstrategia() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        echo json_encode($this->model->eliminarEstrategia($id));
    }

    // ============= MOTORES =============

    public function listarMotores() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'motores' => $this->model->getAllMotores()]);
    }

    public function getMotor() {
        header('Content-Type: application/json');
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        $motor = $this->model->getMotorById($id);
        if ($motor) {
            echo json_encode(['success' => true, 'motor' => $motor]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Motor no encontrado']);
        }
    }

    public function crearMotor() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }

        $linea_id    = intval($_POST['linea_id'] ?? 0);
        $nombre      = trim($_POST['nombre'] ?? '');
        $ponderacion = ($_POST['ponderacion'] ?? '') !== '' ? floatval($_POST['ponderacion']) : null;
        $activo      = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

        if ($linea_id <= 0 || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'La linea estrategica y el nombre son obligatorios']);
            return;
        }

        echo json_encode($this->model->crearMotor($linea_id, $nombre, $ponderacion, $activo));
    }

    public function actualizarMotor() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }

        $id          = intval($_POST['id'] ?? 0);
        $linea_id    = intval($_POST['linea_id'] ?? 0);
        $nombre      = trim($_POST['nombre'] ?? '');
        $ponderacion = ($_POST['ponderacion'] ?? '') !== '' ? floatval($_POST['ponderacion']) : null;
        $activo      = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

        if ($id <= 0 || $linea_id <= 0 || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Datos invalidos']);
            return;
        }

        echo json_encode($this->model->actualizarMotor($id, $linea_id, $nombre, $ponderacion, $activo));
    }

    public function cambiarEstadoMotor() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }
        $id     = intval($_POST['id'] ?? 0);
        $activo = intval($_POST['activo'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        echo json_encode($this->model->cambiarEstadoMotor($id, $activo));
    }

    public function eliminarMotor() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        echo json_encode($this->model->eliminarMotor($id));
    }

    // ============= PROYECTOS =============

    public function listarProyectos() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'proyectos' => $this->model->getAllProyectos()]);
    }

    public function getProyecto() {
        header('Content-Type: application/json');
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        $proyecto = $this->model->getProyectoById($id);
        if ($proyecto) {
            echo json_encode(['success' => true, 'proyecto' => $proyecto]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Proyecto no encontrado']);
        }
    }

    public function crearProyecto() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }

        $linea_id = intval($_POST['linea_id'] ?? 0);
        $motor_id = intval($_POST['motor_id'] ?? 0);
        $codigo   = trim($_POST['codigo'] ?? '');
        $nombre   = trim($_POST['nombre'] ?? '');
        $activo   = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

        if ($linea_id <= 0 || $motor_id <= 0 || $codigo === '' || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'La linea, el motor, el codigo y el nombre son obligatorios']);
            return;
        }

        echo json_encode($this->model->crearProyecto($linea_id, $motor_id, $codigo, $nombre, $activo));
    }

    public function actualizarProyecto() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }

        $id       = intval($_POST['id'] ?? 0);
        $linea_id = intval($_POST['linea_id'] ?? 0);
        $motor_id = intval($_POST['motor_id'] ?? 0);
        $codigo   = trim($_POST['codigo'] ?? '');
        $nombre   = trim($_POST['nombre'] ?? '');
        $activo   = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

        if ($id <= 0 || $linea_id <= 0 || $motor_id <= 0 || $codigo === '' || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Datos invalidos']);
            return;
        }

        echo json_encode($this->model->actualizarProyecto($id, $linea_id, $motor_id, $codigo, $nombre, $activo));
    }

    public function cambiarEstadoProyecto() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }
        $id     = intval($_POST['id'] ?? 0);
        $activo = intval($_POST['activo'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        echo json_encode($this->model->cambiarEstadoProyecto($id, $activo));
    }

    public function eliminarProyecto() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
            return;
        }
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID invalido']);
            return;
        }
        echo json_encode($this->model->eliminarProyecto($id));
    }

    // ============= AUXILIARES =============

    public function lineasActivas() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'lineas' => $this->model->getLineasActivas()]);
    }

    public function motoresPorLinea() {
        header('Content-Type: application/json');
        $linea_id = intval($_GET['linea_id'] ?? 0);
        if ($linea_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de linea invalido']);
            return;
        }
        echo json_encode(['success' => true, 'motores' => $this->model->getMotoresPorLinea($linea_id)]);
    }
}
?>
