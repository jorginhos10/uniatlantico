<?php
// controlador/dependenciasController.php
require_once 'modelo/dependenciasModel.php';

class DependenciasController {
    private $model;

    public function __construct() {
        $this->model = new DependenciasModel();
    }

    public function index() {
        require_once 'vista/dependencias/index.php';
    }

    public function listar() {
        header('Content-Type: application/json');
        $deps = $this->model->getAll();
        echo json_encode(['success' => true, 'dependencias' => $deps]);
    }

    public function crear() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $activo = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }

        $resultado = $this->model->crear($nombre, $activo);
        echo json_encode($resultado);
    }

    public function get() {
        header('Content-Type: application/json');
        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $dep = $this->model->getById($id);
        if ($dep) {
            echo json_encode(['success' => true, 'dependencia' => $dep]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cargo no encontrado']);
        }
    }

    public function actualizar() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $id     = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $activo = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }

        $resultado = $this->model->actualizar($id, $nombre, $activo);
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
        $activo = intval($_POST['activo'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $resultado = $this->model->cambiarEstado($id, $activo);
        echo json_encode($resultado);
    }
}
