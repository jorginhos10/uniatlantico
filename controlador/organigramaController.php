<?php
// controlador/organigramaController.php
require_once 'modelo/usuarioModel.php';

class OrganigramaController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function index() {
        $todosUsuarios = $this->usuarioModel->obtenerTodosUsuarios();

        // Agrupa usuarios activos por rol (el valor de "rol" ya es el nombre legible, ej. "Administrador")
        $usuariosPorRol = [];
        foreach ($todosUsuarios as $u) {
            if ((int)$u['activo'] !== 1) continue;
            $usuariosPorRol[$u['rol']][] = $u;
        }

        require_once 'vista/organigrama/index.php';
    }
}
