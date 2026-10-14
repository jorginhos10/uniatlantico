<?php
// controlador/organigramaController.php
require_once 'modelo/usuarioModel.php';
require_once 'modelo/organigramaModel.php';
require_once 'modelo/rolesModel.php';

class OrganigramaController {
    private $usuarioModel;
    private $model;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
        $this->model = new OrganigramaModel();
    }

    public function index() {
        $todosUsuarios = $this->usuarioModel->obtenerTodosUsuarios();

        // Agrupa usuarios activos por rol (el valor de "rol" ya es el nombre legible, ej. "Administrador")
        $usuariosPorRol = [];
        foreach ($todosUsuarios as $u) {
            if ((int)$u['activo'] !== 1) continue;
            $usuariosPorRol[$u['rol']][] = $u;
        }

        $esAdminOrganigrama = $this->esAdmin();
        $nodos = $this->model->getAll();
        $nodosPlanos = $this->construirArbol($nodos);
        $nodosAnidados = $this->construirArbolAnidado($nodos);
        $roles = (new RolesModel())->getAll();

        require_once 'vista/organigrama/index.php';
    }

    public function crear() {
        if (!$this->validarPeticionAdmin()) return;

        $nombre  = trim($_POST['nombre'] ?? '');
        $padreId = intval($_POST['padre_id'] ?? 0);
        $rol     = trim($_POST['rol'] ?? '');
        $orden   = intval($_POST['orden'] ?? 0);

        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }
        if ($padreId > 0 && $this->model->getById($padreId) === null) {
            echo json_encode(['success' => false, 'message' => 'El nodo padre seleccionado no existe']);
            return;
        }

        echo json_encode($this->model->crear($nombre, $padreId > 0 ? $padreId : null, $rol !== '' ? $rol : null, $orden));
    }

    public function actualizar() {
        if (!$this->validarPeticionAdmin()) return;

        $id      = intval($_POST['id'] ?? 0);
        $nombre  = trim($_POST['nombre'] ?? '');
        $padreId = intval($_POST['padre_id'] ?? 0);
        $rol     = trim($_POST['rol'] ?? '');
        $orden   = intval($_POST['orden'] ?? 0);

        if ($id <= 0 || $this->model->getById($id) === null) {
            echo json_encode(['success' => false, 'message' => 'El nodo que intentas editar no existe']);
            return;
        }
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
            return;
        }
        if ($padreId > 0) {
            if ($this->model->getById($padreId) === null) {
                echo json_encode(['success' => false, 'message' => 'El nodo padre seleccionado no existe']);
                return;
            }
            if ($this->creaCiclo($id, $padreId)) {
                echo json_encode(['success' => false, 'message' => 'No puedes asignar como padre a un nodo que está dentro de su propia rama']);
                return;
            }
        }

        echo json_encode($this->model->actualizar($id, $nombre, $padreId > 0 ? $padreId : null, $rol !== '' ? $rol : null, $orden));
    }

    public function cambiarEstado() {
        if (!$this->validarPeticionAdmin()) return;

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        echo json_encode($this->model->cambiarEstado($id));
    }

    public function eliminar() {
        if (!$this->validarPeticionAdmin()) return;

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        if ($this->model->tieneHijos($id)) {
            echo json_encode(['success' => false, 'message' => 'No puedes eliminar este nodo porque tiene ramas debajo. Elimínalas o reasígnalas primero.']);
            return;
        }
        echo json_encode($this->model->eliminar($id));
    }

    private function esAdmin() {
        $rol = mb_strtolower(trim((string)($_SESSION['usuario_rol'] ?? '')), 'UTF-8');
        return (int)($_SESSION['usuario_id'] ?? 0) === 1 || in_array($rol, ['admin', 'administrador'], true);
    }

    // Responde el error y devuelve false si la petición no es un POST de un administrador
    private function validarPeticionAdmin() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return false;
        }
        if (!$this->esAdmin()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
            return false;
        }
        return true;
    }

    // ¿El nuevo padre es el mismo nodo o está dentro de su rama?
    private function creaCiclo($id, $nuevoPadreId) {
        $actualId = $nuevoPadreId;
        $visitados = [];
        while ($actualId > 0 && !isset($visitados[$actualId])) {
            if ($actualId === $id) return true;
            $visitados[$actualId] = true;
            $actual = $this->model->getById($actualId);
            $actualId = $actual ? (int)$actual['padre_id'] : 0;
        }
        return false;
    }

    private function agruparPorPadre($nodos) {
        $ids = array_column($nodos, 'id');
        $porPadre = [];
        foreach ($nodos as $nodo) {
            // Un nodo cuyo padre ya no existe se trata como raíz para que no desaparezca
            $padreId = in_array($nodo['padre_id'], $ids) ? (int)$nodo['padre_id'] : 0;
            $porPadre[$padreId][] = $nodo;
        }
        return $porPadre;
    }

    // Lista plana en orden de árbol, con el nivel de cada nodo (para la tabla)
    private function construirArbol($nodos) {
        $resultado = [];
        $this->agregarNivel($this->agruparPorPadre($nodos), 0, 0, $resultado);
        return $resultado;
    }

    private function agregarNivel($porPadre, $padreId, $nivel, &$resultado) {
        foreach ($porPadre[$padreId] ?? [] as $nodo) {
            $nodo['nivel'] = $nivel;
            $resultado[] = $nodo;
            $this->agregarNivel($porPadre, (int)$nodo['id'], $nivel + 1, $resultado);
        }
    }

    // Árbol anidado (cada nodo con sus "hijos"), para dibujar el mapa
    private function construirArbolAnidado($nodos) {
        return $this->anidarHijos($this->agruparPorPadre($nodos), 0);
    }

    private function anidarHijos($porPadre, $padreId) {
        $ramas = [];
        foreach ($porPadre[$padreId] ?? [] as $nodo) {
            $nodo['hijos'] = $this->anidarHijos($porPadre, (int)$nodo['id']);
            $ramas[] = $nodo;
        }
        return $ramas;
    }
}
