<?php
// controlador/facultadesController.php
require_once 'modelo/facultadesModel.php';

class FacultadesController {
    private $model;

    public function __construct() {
        $this->model = new FacultadesModel();
    }

    public function index() {
        require_once 'vista/facultades/index.php';
    }

    public function listar() {
        header('Content-Type: application/json');
        $facultades = $this->model->getAll();
        echo json_encode(['success' => true, 'facultades' => $facultades]);
    }

    public function crear() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }

        $resultado = $this->model->crear($codigo, $nombre, $estado);
        echo json_encode($resultado);
    }

    public function get() {
        header('Content-Type: application/json');
        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $facultad = $this->model->getById($id);
        if ($facultad) {
            echo json_encode(['success' => true, 'facultad' => $facultad]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Facultad no encontrada']);
        }
    }

    public function actualizar() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $id     = intval($_POST['id'] ?? 0);
        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }

        $resultado = $this->model->actualizar($id, $codigo, $nombre, $estado);
        echo json_encode($resultado);
    }

    public function eliminar() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $resultado = $this->model->eliminar($id);
        echo json_encode($resultado);
    }

    public function cambiarEstado() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $id     = intval($_POST['id'] ?? 0);
        $estado = intval($_POST['estado'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $resultado = $this->model->cambiarEstado($id, $estado);
        echo json_encode($resultado);
    }
}
