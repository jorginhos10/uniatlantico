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
        $esAdminOrganigrama = $this->esAdmin();

        // Igual que en Jerarquías de new-ua: los formularios envían un POST a esta misma página.
        // Se redirige después para que recargar no reenvíe el formulario.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($esAdminOrganigrama) {
                $accion = $_POST['accion'] ?? 'crear';
                if ($accion === 'cambiar_estado') {
                    $resultado = $this->cambiarEstado();
                } elseif ($accion === 'actualizar') {
                    $resultado = $this->actualizar();
                } elseif ($accion === 'eliminar') {
                    $resultado = $this->eliminar();
                } else {
                    $resultado = $this->guardar();
                }
            } else {
                $resultado = ['No tienes permisos para esta acción.', ''];
            }
            $_SESSION['organigrama_flash'] = $resultado;
            header('Location: ' . Config::getBasePath() . '/organigrama');
            exit;
        }

        [$error, $exito] = $_SESSION['organigrama_flash'] ?? ['', ''];
        unset($_SESSION['organigrama_flash']);

        // Agrupa usuarios activos por rol (el valor de "rol" ya es el nombre legible, ej. "Administrador")
        $usuariosPorRol = [];
        foreach ($this->usuarioModel->obtenerTodosUsuarios() as $u) {
            if ((int)$u['activo'] !== 1) continue;
            $usuariosPorRol[$u['rol']][] = $u;
        }

        $nodos = $this->model->getAll();
        foreach ($nodos as &$nodo) {
            $nodo['roles'] = $this->decodificarRoles($nodo['rol']);
        }
        unset($nodo);

        $padresDisponibles = array_values(array_filter($nodos, function ($n) { return (int)$n['activo'] === 1; }));
        usort($padresDisponibles, function ($a, $b) { return strcasecmp($a['nombre'], $b['nombre']); });
        $nodosPlanos = $this->construirArbol($nodos);
        $nodosAnidados = $this->construirArbolAnidado($nodos);
        $roles = (new RolesModel())->getAll();

        require_once 'vista/organigrama/index.php';
    }

    // Cada acción devuelve [error, éxito], como en new-ua

    private function guardar() {
        $nombre  = trim($_POST['nombre'] ?? '');
        $padreId = intval($_POST['padre_id'] ?? 0);
        $orden   = intval($_POST['orden'] ?? 0);

        if ($nombre === '') {
            return ['El nombre es obligatorio.', ''];
        }
        if ($padreId > 0 && $this->model->getById($padreId) === null) {
            return ['El nodo padre seleccionado no existe.', ''];
        }

        $ok = $this->model->crear($nombre, $padreId > 0 ? $padreId : null, $this->rolesEnviados(), $orden);
        return $ok ? ['', 'Nodo agregado correctamente.'] : ['No se pudo agregar el nodo.', ''];
    }

    private function actualizar() {
        $id      = intval($_POST['id'] ?? 0);
        $nombre  = trim($_POST['nombre'] ?? '');
        $padreId = intval($_POST['padre_id'] ?? 0);
        $orden   = intval($_POST['orden'] ?? 0);

        if ($id <= 0 || $this->model->getById($id) === null) {
            return ['El nodo que intentas editar no existe.', ''];
        }
        if ($nombre === '') {
            return ['El nombre es obligatorio.', ''];
        }
        if ($padreId > 0) {
            if ($this->model->getById($padreId) === null) {
                return ['El nodo padre seleccionado no existe.', ''];
            }
            if ($this->creaCiclo($id, $padreId)) {
                return ['No puedes asignar como padre a un nodo que está dentro de su propia rama.', ''];
            }
        }

        $ok = $this->model->actualizar($id, $nombre, $padreId > 0 ? $padreId : null, $this->rolesEnviados(), $orden);
        return $ok ? ['', 'Nodo actualizado correctamente.'] : ['No se pudo actualizar el nodo.', ''];
    }

    private function cambiarEstado() {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->cambiarEstado($id);
        }
        return ['', ''];
    }

    private function eliminar() {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            return ['Nodo no válido.', ''];
        }
        if ($this->model->tieneHijos($id)) {
            return ['No puedes eliminar este nodo porque tiene ramas debajo de él. Elimínalas o reasígnalas primero.', ''];
        }
        return $this->model->eliminar($id) ? ['', 'Nodo eliminado correctamente.'] : ['No se pudo eliminar el nodo.', ''];
    }

    private function esAdmin() {
        $rol = mb_strtolower(trim((string)($_SESSION['usuario_rol'] ?? '')), 'UTF-8');
        return (int)($_SESSION['usuario_id'] ?? 0) === 1 || in_array($rol, ['admin', 'administrador'], true);
    }

    // Roles marcados en el formulario, como JSON para la columna `rol` (null si no hay ninguno)
    private function rolesEnviados() {
        $roles = array_values(array_unique(array_filter(array_map('trim', (array)($_POST['roles'] ?? [])), 'strlen')));
        return $roles ? json_encode($roles, JSON_UNESCAPED_UNICODE) : null;
    }

    // La columna `rol` guarda una lista JSON; un texto simple se toma como un solo rol
    private function decodificarRoles($valor) {
        $valor = trim((string)$valor);
        if ($valor === '') return [];
        $lista = json_decode($valor, true);
        return is_array($lista) ? array_values(array_filter(array_map('strval', $lista), 'strlen')) : [$valor];
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
