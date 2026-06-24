<?php
require_once 'modelo/NovedadesModel.php';

class NovedadesController {
    private $model;

    public function __construct() {
        $this->model = new NovedadesModel();
    }

    public function index() {
        $novedades = $this->model->getAll();
        require_once 'vista/novedades/index.php';
    }

    public function listar() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'novedades' => $this->model->getAll()]);
    }

    public function get() {
        header('Content-Type: application/json');
        $id = intval($_GET['id'] ?? 0);
        $nov = $this->model->getById($id);
        if ($nov) {
            echo json_encode(['success' => true, 'novedad' => $nov]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Novedad no encontrada']);
        }
    }

    public function crear() {
        header('Content-Type: application/json');
        $input     = json_decode(file_get_contents('php://input'), true) ?: [];
        $titulo    = trim($input['titulo']    ?? '');
        $contenido = trim($input['contenido'] ?? '');
        if (!$titulo || !$contenido) {
            echo json_encode(['success' => false, 'message' => 'Título y contenido son requeridos']);
            return;
        }
        $ok = $this->model->crear([
            'titulo'    => $titulo,
            'contenido' => $contenido,
            'activo'    => intval($input['activo'] ?? 1),
        ]);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Novedad creada' : 'Error al crear']);
    }

    public function actualizar() {
        header('Content-Type: application/json');
        $input     = json_decode(file_get_contents('php://input'), true) ?: [];
        $id        = intval($input['id']       ?? 0);
        $titulo    = trim($input['titulo']     ?? '');
        $contenido = trim($input['contenido']  ?? '');
        if (!$id || !$titulo || !$contenido) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        $ok = $this->model->actualizar($id, [
            'titulo'    => $titulo,
            'contenido' => $contenido,
            'activo'    => intval($input['activo'] ?? 1),
        ]);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Novedad actualizada' : 'Error al actualizar']);
    }

    public function eliminar() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $id    = intval($input['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        $ok = $this->model->eliminar($id);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Novedad eliminada' : 'Error al eliminar']);
    }

    public function toggleActivo() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $id    = intval($input['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        $ok = $this->model->toggleActivo($id);
        echo json_encode(['success' => $ok]);
    }

    public function reordenar() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $ids   = $input['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'IDs inválidos']);
            return;
        }
        $ok = $this->model->reordenarLote($ids);
        echo json_encode(['success' => $ok]);
    }
}
?>
