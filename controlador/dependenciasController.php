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

    // Exporta el listado a Excel; respeta el texto del buscador (?q=)
    public function exportar() {
        require_once 'config/ExcelExport.php';
        $q = mb_strtolower(trim($_GET['q'] ?? ''), 'UTF-8');

        $filas = [];
        foreach ($this->model->getAll() as $d) {
            if ($q !== '' && !ExcelExport::contiene($d['nombre'] ?? '', $q)) continue;
            $filas[] = [
                count($filas) + 1,
                (string)($d['nombre'] ?? ''),
                (int)($d['activo'] ?? 0) === 1 ? 'Activo' : 'Inactivo',
                !empty($d['fecha_creacion']) ? date('d/m/Y', strtotime($d['fecha_creacion'])) : ''
            ];
        }

        ExcelExport::descargar('dependencias_' . date('Y-m-d'), 'Dependencias', ['#', 'Nombre', 'Estado', 'Creado'], $filas, [6, 60, 14, 14]);
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

    // ============= FLUJO DE INFORMACIÓN =============

    public function listarFlujos() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'flujos' => $this->model->getFlujos()]);
    }

    public function crearFlujo() {
        header('Content-Type: application/json');
        $datos = $this->validarFlujo();
        if ($datos === null) return;
        echo json_encode($this->model->crearFlujo($datos['origen'], $datos['destino'], $datos['aplica_a'], $datos['observacion']));
    }

    public function actualizarFlujo() {
        header('Content-Type: application/json');
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        $datos = $this->validarFlujo($id);
        if ($datos === null) return;
        echo json_encode($this->model->actualizarFlujo($id, $datos['origen'], $datos['destino'], $datos['aplica_a'], $datos['observacion']));
    }

    public function eliminarFlujo() {
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
        echo json_encode($this->model->eliminarFlujo($id));
    }

    // Devuelve los datos limpios, o responde el error y devuelve null
    private function validarFlujo($exceptoId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return null;
        }
        $origen      = intval($_POST['origen_id'] ?? 0);
        $destino     = intval($_POST['destino_id'] ?? 0);
        $aplicaA     = $_POST['aplica_a'] ?? 'ambos';
        $observacion = trim($_POST['observacion'] ?? '');

        if ($origen <= 0 || $destino <= 0) {
            echo json_encode(['success' => false, 'message' => 'Selecciona la dependencia que envía y la que aprueba']);
            return null;
        }
        if ($origen === $destino) {
            echo json_encode(['success' => false, 'message' => 'Una dependencia no puede enviarse a sí misma']);
            return null;
        }
        if (!in_array($aplicaA, ['ambos', 'formulacion', 'seguimiento'], true)) {
            echo json_encode(['success' => false, 'message' => 'Valor de "Aplica a" no válido']);
            return null;
        }
        if (!$this->model->getById($origen) || !$this->model->getById($destino)) {
            echo json_encode(['success' => false, 'message' => 'Una de las dependencias seleccionadas no existe']);
            return null;
        }
        if ($this->model->existeFlujo($origen, $destino, $aplicaA, $exceptoId)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe esa relación entre estas dependencias']);
            return null;
        }
        return [
            'origen' => $origen, 'destino' => $destino, 'aplica_a' => $aplicaA,
            'observacion' => $observacion !== '' ? mb_substr($observacion, 0, 255) : null,
        ];
    }
}
