<?php
// controlador/snapshotController.php
require_once 'modelo/snapshotModel.php';

class SnapshotController {
    private $model;

    public function __construct() {
        $this->model = new SnapshotModel();
    }

    public function index() {
        $this->requerirAdmin();

        // Los formularios envían un POST a esta misma página y se redirige después,
        // para que recargar no vuelva a crear o eliminar un snapshot
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? 'crear';
            $_SESSION['snapshots_flash'] = $accion === 'eliminar' ? $this->eliminar() : ($accion === 'asignar' ? $this->asignar() : $this->crear());
            header('Location: ' . Config::getBasePath() . '/snapshots');
            exit;
        }

        [$error, $exito] = $_SESSION['snapshots_flash'] ?? ['', ''];
        unset($_SESSION['snapshots_flash']);

        $snapshots = $this->model->getAll();
        $formularios = $this->model->getFormularios();
        require_once 'vista/snapshots/index.php';
    }

    // Descarga el snapshot completo como archivo JSON
    public function descargar($id) {
        $this->requerirAdmin();
        $snapshot = $this->model->getById((int)$id);
        if (!$snapshot) {
            $_SESSION['snapshots_flash'] = ['El snapshot que intentas descargar no existe.', ''];
            header('Location: ' . Config::getBasePath() . '/snapshots');
            exit;
        }

        $nombreArchivo = 'snapshot_' . (int)$snapshot['id'] . '_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $snapshot['nombre']) . '.json';
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        echo json_encode([
            'app'      => 'uniatlantico',
            'snapshot' => $snapshot,
            'tablas'   => $this->model->getDatos((int)$snapshot['id']),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    private function crear() {
        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            $nombre = 'Snapshot ' . date('Y-m-d H:i:s');
        }
        $nombre = mb_substr($nombre, 0, 150);

        try {
            @set_time_limit(300);
            $trimestre = (int)($_POST['trimestre'] ?? 0);
            $formularioId = (int)($_POST['formulario_id'] ?? 0);
            $this->model->crear($nombre, (int)($_SESSION['usuario_id'] ?? 0), $formularioId ?: null, ($trimestre >= 1 && $trimestre <= 4) ? $trimestre : null);
        } catch (Throwable $e) {
            error_log("Error crear snapshot: " . $e->getMessage());
            return ['No se pudo crear el snapshot: ' . $e->getMessage(), ''];
        }
        return ['', 'Snapshot "' . $nombre . '" creado correctamente.'];
    }

    // Cambia el trimestre al que representa un snapshot (0 = sin trimestre)
    private function asignar() {
        $id = intval($_POST['id'] ?? 0);
        $trimestre = intval($_POST['trimestre'] ?? 0);
        $formularioId = intval($_POST['formulario_id'] ?? 0);
        if ($id <= 0 || $this->model->getById($id) === null) {
            return ['El snapshot no existe.', ''];
        }
        if ($trimestre < 0 || $trimestre > 4 || ($trimestre > 0 && $formularioId <= 0)) {
            return ['Elige el plan y un trimestre del 1 al 4.', ''];
        }
        if (!$this->model->asignarTrimestre($id, $formularioId, $trimestre)) {
            return ['No se pudo asignar el trimestre.', ''];
        }
        return ['', $trimestre > 0 ? 'Snapshot asignado al trimestre ' . $trimestre . '.' : 'Se quitó el trimestre del snapshot.'];
    }

    private function eliminar() {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->getById($id) === null) {
            return ['El snapshot que intentas eliminar no existe.', ''];
        }
        return $this->model->eliminar($id) ? ['', 'Snapshot eliminado correctamente.'] : ['No se pudo eliminar el snapshot.', ''];
    }

    // Solo administradores (y el superadministrador)
    private function requerirAdmin() {
        $rol = mb_strtolower(trim((string)($_SESSION['usuario_rol'] ?? '')), 'UTF-8');
        if ((int)($_SESSION['usuario_id'] ?? 0) === 1 || in_array($rol, ['admin', 'administrador'], true)) return;
        header('Location: ' . Config::getBasePath() . '/dashboard');
        exit;
    }
}
