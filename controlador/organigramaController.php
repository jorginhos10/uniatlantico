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

        // Usuarios activos agrupados por su rol (normalizado: sin acentos ni mayúsculas)
        $usuariosPorRol = [];
        foreach ($this->usuarioModel->obtenerTodosUsuarios() as $u) {
            if ((int)$u['activo'] !== 1) continue;
            $usuariosPorRol[$this->normalizar($u['rol'])][] = $u;
        }

        // El organigrama se arma con los ROLES: cada nodo es un rol del catálogo y muestra sus usuarios
        $roles = (new RolesModel())->getAll();
        $nodos = $this->model->getAll();
        $nombrePorId = [];
        foreach ($nodos as &$nodo) {
            $rolNodo = $this->decodificarRoles($nodo['rol'])[0] ?? '';
            $rolCatalogo = $this->buscarRol($roles, $rolNodo);
            $nodo['rol_slug'] = $rolCatalogo['slug'] ?? $rolNodo;
            $nodo['nombre']   = $rolCatalogo['nombre'] ?? ($rolNodo !== '' ? $rolNodo : $nodo['nombre']);
            $nodo['usuarios'] = $this->usuariosDelRol($usuariosPorRol, $rolCatalogo, $rolNodo);
            $nombrePorId[(int)$nodo['id']] = $nodo['nombre'];
        }
        unset($nodo);
        foreach ($nodos as &$nodo) {
            $nodo['nombre_padre'] = $nodo['padre_id'] !== null ? ($nombrePorId[(int)$nodo['padre_id']] ?? null) : null;
        }
        unset($nodo);

        // Roles que ya están en el organigrama (cada rol aparece una sola vez)
        $rolesUsados = [];
        foreach ($nodos as $n) $rolesUsados[$this->normalizar($n['rol_slug'])] = (int)$n['id'];

        $padresDisponibles = array_values(array_filter($nodos, function ($n) { return (int)$n['activo'] === 1; }));
        usort($padresDisponibles, function ($a, $b) { return strcasecmp($a['nombre'], $b['nombre']); });
        $nodosPlanos = $this->construirArbol($nodos);
        $nodosAnidados = $this->construirArbolAnidado($nodos);

        require_once 'vista/organigrama/index.php';
    }

    // Cada acción devuelve [error, éxito], como en new-ua

    private function guardar() {
        $padreId = intval($_POST['padre_id'] ?? 0);
        $orden   = intval($_POST['orden'] ?? 0);

        [$rol, $error] = $this->rolEnviado();
        if ($error !== '') return [$error, ''];
        if ($padreId > 0 && $this->model->getById($padreId) === null) {
            return ['El rol del que depende no existe.', ''];
        }

        $ok = $this->model->crear($rol['nombre'], $padreId > 0 ? $padreId : null, json_encode([$rol['slug']], JSON_UNESCAPED_UNICODE), $orden);
        return $ok ? ['', 'Rol "' . $rol['nombre'] . '" agregado al organigrama.'] : ['No se pudo agregar el rol.', ''];
    }

    private function actualizar() {
        $id      = intval($_POST['id'] ?? 0);
        $padreId = intval($_POST['padre_id'] ?? 0);
        $orden   = intval($_POST['orden'] ?? 0);

        if ($id <= 0 || $this->model->getById($id) === null) {
            return ['El rol que intentas editar no existe en el organigrama.', ''];
        }
        [$rol, $error] = $this->rolEnviado($id);
        if ($error !== '') return [$error, ''];
        if ($padreId > 0) {
            if ($this->model->getById($padreId) === null) {
                return ['El rol del que depende no existe.', ''];
            }
            if ($this->creaCiclo($id, $padreId)) {
                return ['No puedes hacer depender un rol de otro que está dentro de su propia rama.', ''];
            }
        }

        $ok = $this->model->actualizar($id, $rol['nombre'], $padreId > 0 ? $padreId : null, json_encode([$rol['slug']], JSON_UNESCAPED_UNICODE), $orden);
        return $ok ? ['', 'Rol "' . $rol['nombre'] . '" actualizado.'] : ['No se pudo actualizar el rol.', ''];
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
            return ['No puedes quitar este rol porque tiene otros roles debajo. Quítalos o reasígnalos primero.', ''];
        }
        return $this->model->eliminar($id) ? ['', 'Rol quitado del organigrama.'] : ['No se pudo quitar el rol.', ''];
    }

    private function esAdmin() {
        $rol = mb_strtolower(trim((string)($_SESSION['usuario_rol'] ?? '')), 'UTF-8');
        return (int)($_SESSION['usuario_id'] ?? 0) === 1 || in_array($rol, ['admin', 'administrador'], true);
    }

    // Rol elegido en el formulario: debe existir en el catálogo y no estar ya en el organigrama
    // (salvo en el mismo nodo que se edita). Devuelve [rolDelCatálogo, error].
    private function rolEnviado($exceptoId = 0) {
        $valor = trim($_POST['rol'] ?? '');
        if ($valor === '') return [null, 'Selecciona un rol.'];
        $rol = $this->buscarRol((new RolesModel())->getAll(), $valor);
        if ($rol === null) return [null, 'El rol seleccionado no existe.'];

        foreach ($this->model->getAll() as $n) {
            if ((int)$n['id'] === (int)$exceptoId) continue;
            $rolNodo = $this->decodificarRoles($n['rol'])[0] ?? '';
            $usado = $this->normalizar($rolNodo);
            if ($usado !== '' && ($usado === $this->normalizar($rol['slug']) || $usado === $this->normalizar($rol['nombre']))) {
                return [null, 'El rol "' . $rol['nombre'] . '" ya está en el organigrama.'];
            }
        }
        return [$rol, ''];
    }

    private function normalizar($s) {
        $s = mb_strtolower(trim((string)$s), 'UTF-8');
        return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
    }

    // Busca un rol del catálogo por su valor guardado o por su nombre legible
    private function buscarRol(array $roles, $valor) {
        $v = $this->normalizar($valor);
        if ($v === '') return null;
        foreach ($roles as $r) {
            if ($this->normalizar($r['slug']) === $v || $this->normalizar($r['nombre']) === $v) return $r;
        }
        return null;
    }

    // Usuarios activos del rol (se buscan por el valor guardado y por el nombre legible)
    private function usuariosDelRol(array $usuariosPorRol, $rolCatalogo, $rolNodo) {
        $claves = array_unique(array_filter([
            $this->normalizar($rolCatalogo['slug'] ?? ''),
            $this->normalizar($rolCatalogo['nombre'] ?? ''),
            $this->normalizar($rolNodo),
        ], 'strlen'));
        $usuarios = [];
        foreach ($claves as $k) {
            foreach ($usuariosPorRol[$k] ?? [] as $u) $usuarios[(int)$u['id']] = $u;
        }
        usort($usuarios, function ($a, $b) { return strcasecmp($a['nombre'], $b['nombre']); });
        return array_values($usuarios);
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
