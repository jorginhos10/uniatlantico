<?php
require_once 'modelo/MensajesModel.php';

class MensajesController {
    private MensajesModel $model;

    public function __construct() {
        $this->model = new MensajesModel();
    }

    private function uid(): int   { return (int)($_SESSION['usuario_id']  ?? 0); }
    private function urol(): string { return $_SESSION['usuario_rol'] ?? ''; }

    // ── VISTA PRINCIPAL ────────────────────────────────────────────────────
    public function index(): void {
        $uid  = $this->uid();
        $urol = $this->urol();

        $recibidos    = $this->model->getRecibidos($uid, $urol);
        $enviados     = $this->model->getEnviados($uid);
        $usuarios     = $this->model->getUsuarios($uid);
        $dependencias = $this->model->getDependencias();
        $roles        = $this->model->getRoles();
        $noLeidos     = $this->model->contarNoLeidos($uid, $urol);

        require_once 'vista/mensajes/index.php';
    }

    // ── LISTAR (AJAX) ──────────────────────────────────────────────────────
    public function listar(): void {
        header('Content-Type: application/json');
        $uid  = $this->uid();
        $urol = $this->urol();
        $tab  = $_GET['tab'] ?? 'recibidos';

        $mensajes = ($tab === 'enviados')
            ? $this->model->getEnviados($uid)
            : $this->model->getRecibidos($uid, $urol);

        echo json_encode(['success' => true, 'mensajes' => $mensajes]);
    }

    // ── CREAR (POST) ───────────────────────────────────────────────────────
    public function crear(): void {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $asunto = trim($_POST['asunto'] ?? '');
        $cuerpo = trim($_POST['cuerpo'] ?? '');
        $tipo   = $_POST['tipo_destinatario'] ?? '';
        $dest   = trim($_POST['destinatario_id'] ?? '');

        if (empty($asunto) || empty($tipo) || empty($dest)) {
            echo json_encode(['success' => false, 'message' => 'Asunto y destinatario son requeridos']);
            return;
        }

        $validos = ['usuario', 'dependencia', 'rol'];
        if (!in_array($tipo, $validos)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de destinatario inválido']);
            return;
        }

        $result = $this->model->crear($asunto, $cuerpo, $this->uid(), $tipo, $dest);
        echo json_encode($result);
    }

    // ── VER MENSAJE (AJAX) ─────────────────────────────────────────────────
    public function ver(): void {
        header('Content-Type: application/json');
        $id  = (int)($_GET['id'] ?? 0);
        $uid = $this->uid();

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $mensaje = $this->model->getById($id);
        if (!$mensaje) {
            echo json_encode(['success' => false, 'message' => 'Mensaje no encontrado']);
            return;
        }

        // Marcar leído si no es propio
        if ((int)$mensaje['remitente_id'] !== $uid) {
            $this->model->marcarLeido($id, $uid);
        }

        echo json_encode(['success' => true, 'mensaje' => $mensaje]);
    }

    // ── ELIMINAR (POST) ────────────────────────────────────────────────────
    public function eliminar(): void {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id    = (int)($input['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $result = $this->model->eliminar($id, $this->uid());
        echo json_encode($result);
    }

    // ── CONTAR NO LEÍDOS (campanita) ───────────────────────────────────────
    public function contarNoLeidos(): void {
        header('Content-Type: application/json');
        $total = $this->model->contarNoLeidos($this->uid(), $this->urol());
        echo json_encode(['success' => true, 'total' => $total]);
    }

    // ── RECIENTES NO LEÍDOS (dropdown campanita) ──────────────────────────
    public function recientesNoLeidos(): void {
        header('Content-Type: application/json');
        $recientes = $this->model->getRecientesNoLeidos($this->uid(), $this->urol(), 5);
        echo json_encode(['success' => true, 'mensajes' => $recientes]);
    }
}
