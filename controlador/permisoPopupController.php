<?php
// controlador/permisoPopupController.php

require_once 'modelo/permisoModel.php';

class PermisoPopupController {
    private $permisoModel;

    public function __construct() {
        $this->permisoModel = new PermisoModel();
    }

    public function getPermisosPopup($usuario_id) {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
            return;
        }

        $usuario_id = intval($usuario_id);

        if ($usuario_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario no válido']);
            return;
        }

        if ($usuario_id == 1) {
            echo json_encode(['success' => false, 'message' => 'No se pueden modificar los permisos del administrador principal']);
            return;
        }

        $permisos     = $this->permisoModel->obtenerPermisosUsuario($usuario_id);
        $usuario      = $this->permisoModel->obtenerInfoUsuarioParaPermisos($usuario_id);
        $estadisticas = $this->permisoModel->obtenerEstadisticasPermisos($usuario_id);

        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            return;
        }

        echo json_encode([
            'success'      => true,
            'permisos'     => $permisos,
            'usuario'      => $usuario,
            'estadisticas' => $estadisticas
        ]);
    }

    public function togglePermisoPopup() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
            return;
        }

        $input       = json_decode(file_get_contents('php://input'), true) ?: [];
        $usuario_id  = intval($input['usuario_id']  ?? 0);
        $permiso_id  = intval($input['permiso_id']  ?? 0);
        $nuevo_estado = intval($input['nuevo_estado'] ?? 0);

        if ($usuario_id <= 0 || $permiso_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            return;
        }

        if ($usuario_id == 1) {
            echo json_encode(['success' => false, 'message' => 'No se pueden modificar los permisos del administrador principal']);
            return;
        }

        $resultado = $this->permisoModel->togglePermisoUsuario($usuario_id, $permiso_id, $nuevo_estado);

        if ($resultado) {
            $estadisticas = $this->permisoModel->obtenerEstadisticasPermisos($usuario_id);
            $accion = $nuevo_estado ? 'asignado' : 'removido';
            echo json_encode([
                'success'      => true,
                'message'      => "Permiso $accion correctamente",
                'estadisticas' => $estadisticas
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el permiso']);
        }
    }

    public function getAllPermisos() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos']);
            return;
        }

        $permisos = $this->permisoModel->obtenerTodosPermisos();
        echo json_encode(['success' => true, 'permisos' => $permisos]);
    }
}
?>
