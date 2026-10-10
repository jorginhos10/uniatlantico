<?php
// controlador/rolesController.php
require_once 'modelo/rolesModel.php';

class RolesController {
    private $model;

    public function __construct() {
        $this->model = new RolesModel();
    }

    public function index() {
        require_once 'vista/roles/index.php';
    }

    public function listar() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'roles' => $this->model->getAll()]);
    }

    // Exporta el listado a Excel; respeta el texto del buscador (?q=, por identificador o nombre)
    public function exportar() {
        require_once 'config/ExcelExport.php';
        $q = mb_strtolower(trim($_GET['q'] ?? ''), 'UTF-8');

        $filas = [];
        foreach ($this->model->getAll() as $r) {
            if ($q !== '' && !ExcelExport::contiene($r['slug'] ?? '', $q) && !ExcelExport::contiene($r['nombre'] ?? '', $q)) continue;
            $filas[] = [(string)($r['slug'] ?? ''), (string)($r['nombre'] ?? ''), (int)($r['usuarios'] ?? 0)];
        }

        ExcelExport::descargar('roles_' . date('Y-m-d'), 'Roles', ['Identificador (slug)', 'Nombre visible', 'Usuarios'], $filas, [40, 45, 12]);
    }

    public function activos() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'roles' => $this->model->getActivos()]);
    }

    public function actualizarNombre() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); return;
        }
        $slug   = trim($_POST['slug']   ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        if ($slug === '' || $nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']); return;
        }
        echo json_encode($this->model->actualizarNombre($slug, $nombre));
    }
}
