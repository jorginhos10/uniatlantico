<?php
// controlador/Seguimiento144Controller.php
require_once 'modelo/Formulacion144Model.php';
require_once 'config/config.php';

class Seguimiento144Controller {
    
    private $model;
    
    public function __construct() {
        $this->model = new Formulacion144Model();
    }
    
    /**
     * Página principal de seguimientos
     */
    public function index() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                header('Location: ' . Config::getBasePath() . '/FOR-DE-144');
                exit;
            }
            
            // Verificar formulario padre
            $formulario = $this->model->verificarFormulario($id);
            if (!$formulario) {
                header('Location: ' . Config::getBasePath() . '/FOR-DE-144');
                exit;
            }
            
            // Verificar estado de fechas
            $estado_fechas = $this->model->verificarFechaHabil($formulario);
            
            // Obtener seguimientos
            $seguimientos = $this->model->getSeguimientos($id);
            
            // Cargar vista
            include 'vista/seguimiento144/index.php';
            
        } catch (Exception $e) {
            error_log("Error en index de seguimiento: " . $e->getMessage());
            header('Location: ' . Config::getBasePath() . '/FOR-DE-144');
        }
    }
    
    /**
     * Obtener un seguimiento específico (AJAX)
     */
    public function getSeguimiento() {
        header('Content-Type: application/json');
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                return;
            }
            
            $stmt = $this->model->conectarDB()->prepare("
                SELECT s.*, f.* 
                FROM seguimiento_144 s
                INNER JOIN formulacion_144 f ON s.formulacion_id = f.id
                WHERE s.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $seguimiento = $stmt->fetch();
            
            if ($seguimiento) {
                echo json_encode(['success' => true, 'seguimiento' => $seguimiento]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Seguimiento no encontrado']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Guardar avances del seguimiento (AJAX)
     */
    public function guardarAvances() {
        header('Content-Type: application/json');
        try {
            $id = $_POST['id'] ?? null;
            $formulacion_id = $_POST['formulacion_id'] ?? null;
            $avance_fisico = $_POST['avance_fisico'] ?? null;
            $avance_financiero = $_POST['avance_financiero'] ?? null;
            $observaciones = $_POST['observaciones'] ?? null;
            
            if (!$id || !$formulacion_id) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }
            
            $result = $this->model->actualizarSeguimiento($formulacion_id, [
                'avance_fisico' => $avance_fisico,
                'avance_financiero' => $avance_financiero,
                'observaciones' => $observaciones
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Avances guardados correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar avances']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Cambiar estado del seguimiento (AJAX)
     */
    public function cambiarEstado() {
        header('Content-Type: application/json');
        try {
            $id = $_POST['id'] ?? null;
            $estado = $_POST['estado'] ?? null;
            
            if (!$id || $estado === null) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }
            
            // Obtener formulacion_id asociado
            $db = $this->model->conectarDB();
            $stmt = $db->prepare("SELECT formulacion_id FROM seguimiento_144 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $seg = $stmt->fetch();
            
            if (!$seg) {
                echo json_encode(['success' => false, 'message' => 'Seguimiento no encontrado']);
                return;
            }
            
            // Cambiar estado en ambas tablas
            $result = $this->model->cambiarEstado($seg['formulacion_id'], $estado);
            
            if ($result) {
                $mensaje = $estado == 2 ? 'publicado' : ($estado == 1 ? 'cancelado' : 'actualizado');
                echo json_encode(['success' => true, 'message' => 'Seguimiento ' . $mensaje . ' correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al cambiar estado']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
?>