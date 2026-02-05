<?php
// controlador/proveedoresController.php

// Activar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Verificar que el archivo se está cargando
error_log("proveedoresController.php cargado - " . date('Y-m-d H:i:s'));

// Incluir config
require_once 'config/config.php';

// Verificar si se puede incluir el modelo
$modelPath = 'modelo/proveedorModel.php';
if (!file_exists($modelPath)) {
    error_log("ERROR: No se encuentra el archivo $modelPath");
    die(json_encode(['success' => false, 'message' => "Modelo no encontrado: $modelPath"]));
}

require_once $modelPath;

class proveedoresController {
    private $proveedorModel;

    public function __construct() {
        try {
            error_log("Creando instancia de proveedorModel");
            $this->proveedorModel = new proveedorModel();
            error_log("Modelo creado exitosamente");
        } catch (Exception $e) {
            error_log("Error creando modelo: " . $e->getMessage());
            $this->enviarError("Error creando modelo: " . $e->getMessage());
        }
    }

    public function index() {
        error_log("Método index() ejecutado");
        try {
            $proveedores = $this->proveedorModel->obtenerTodosProveedores();
            error_log("Proveedores obtenidos: " . count($proveedores));
            
            // Obtener categorías únicas para el select
            $categorias = $this->proveedorModel->obtenerCategorias();
            error_log("Categorías obtenidas: " . implode(", ", $categorias));
            
            // Calcular estadísticas
            $totalProveedores = count($proveedores);
            $proveedoresActivos = array_filter($proveedores, fn($p) => ($p['activo'] ?? 1) == 1);
            $proveedoresPorCategoria = [];
            
            // Agrupar por categoría para estadísticas
            foreach ($proveedores as $proveedor) {
                $categoria = $proveedor['categoria'] ?? 'cocina';
                if (!isset($proveedoresPorCategoria[$categoria])) {
                    $proveedoresPorCategoria[$categoria] = 0;
                }
                $proveedoresPorCategoria[$categoria]++;
            }
            
            // Pasar variables a la vista
            require_once 'vista/configuraciones/proveedores.php';
        } catch (Exception $e) {
            error_log("Error en index: " . $e->getMessage());
            die("Error cargando proveedores: " . $e->getMessage());
        }
    }

    public function crear() {
        // Establecer headers primero
        header('Content-Type: application/json');
        
        // Debug: Verificar método
        error_log("=== MÉTODO CREAR PROVEEDOR ===");
        error_log("Método: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->enviarError('Método no permitido. Se recibió: ' . $_SERVER['REQUEST_METHOD']);
        }
        
        try {
            // Obtener datos del formulario
            $datos = [
                'nombre' => $this->sanitizar($_POST['nombre'] ?? ''),
                'empresa' => $this->sanitizar($_POST['empresa'] ?? ''),
                'telefono' => $this->sanitizar($_POST['telefono'] ?? ''),
                'direccion' => $this->sanitizar($_POST['direccion'] ?? ''),
                'correo' => $this->sanitizar($_POST['correo'] ?? ''),
                'categoria' => $this->sanitizar($_POST['categoria'] ?? 'cocina'),
                'foto' => $this->manejarFoto($_FILES['foto'] ?? null),
                'observacion' => $this->sanitizar($_POST['observacion'] ?? ''),
                'nit_rut' => $this->sanitizar($_POST['nit_rut'] ?? ''),
                'activo' => isset($_POST['activo']) ? 1 : 0
            ];
            
            error_log("Datos procesados: " . print_r($datos, true));

            // Validaciones
            $errores = [];
            
            if (empty($datos['nombre'])) {
                $errores['nombre'] = ['El nombre del proveedor es obligatorio'];
            }
            
            if (empty($datos['telefono'])) {
                $errores['telefono'] = ['El teléfono es obligatorio'];
            }
            
            if (!empty($datos['correo']) && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
                $errores['correo'] = ['Email inválido'];
            }

            if (!empty($errores)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errores
                ]);
                exit;
            }

            // Crear proveedor
            error_log("Llamando a crearProveedor en el modelo");
            $resultado = $this->proveedorModel->crearProveedor($datos);
            
            if ($resultado) {
                error_log("✅ Proveedor creado exitosamente");
                echo json_encode([
                    'success' => true,
                    'message' => 'Proveedor creado exitosamente'
                ]);
            } else {
                $this->enviarError('Error al crear el proveedor en la base de datos. Modelo retornó false.');
            }
            
        } catch (Exception $e) {
            $this->enviarError('Excepción en crear: ' . $e->getMessage());
        }
        exit;
    }

    public function get($id) {
        header('Content-Type: application/json');
        
        error_log("=== MÉTODO GET PROVEEDOR ===");
        error_log("ID solicitado: " . $id);
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            exit;
        }
        
        try {
            $proveedor = $this->proveedorModel->obtenerProveedorPorId($id);
            
            if ($proveedor) {
                error_log("Proveedor encontrado: " . $proveedor['nombre']);
                echo json_encode([
                    'success' => true,
                    'data' => $proveedor
                ]);
            } else {
                error_log("Proveedor NO encontrado para ID: " . $id);
                echo json_encode([
                    'success' => false,
                    'message' => 'Proveedor no encontrado'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error en get: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function getCategorias() {
        header('Content-Type: application/json');
        
        try {
            $categorias = $this->proveedorModel->obtenerCategorias();
            
            echo json_encode([
                'success' => true,
                'categorias' => $categorias
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function updateStatus() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? 0;
            $activo = $input['activo'] ?? 0;
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID de proveedor no válido']);
                exit;
            }
            
            if ($this->proveedorModel->actualizarEstado($id, $activo)) {
                echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public function eliminar() {
        error_log("=== MÉTODO ELIMINAR PROVEEDOR ===");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            error_log("ID a eliminar: " . $id);

            if ($this->proveedorModel->eliminarProveedor($id)) {
                $_SESSION['success'] = 'Proveedor eliminado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar el proveedor';
            }
            
            header("Location: " . Config::getBasePath() . "/proveedores");
            exit;
        }
        
        header("Location: " . Config::getBasePath() . "/proveedores");
        exit;
    }

    public function buscar() {
        header('Content-Type: application/json');
        
        $termino = $_GET['q'] ?? '';
        
        try {
            $proveedores = $this->proveedorModel->buscarProveedores($termino);
            
            echo json_encode([
                'success' => true,
                'data' => $proveedores,
                'total' => count($proveedores)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error en búsqueda: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    private function sanitizar($input) {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }

    private function manejarFoto($file) {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return 'default.png';
        }
        
        // Validar tipo de archivo
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensionesPermitidas)) {
            return 'default.png';
        }
        
        // Validar tamaño (2MB máximo)
        if ($file['size'] > 2 * 1024 * 1024) {
            return 'default.png';
        }
        
        $nombreArchivo = 'proveedor_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
        $destino = __DIR__ . '/../../assets/media/proveedores/' . $nombreArchivo;
        
        // Crear directorio si no existe
        $directorio = dirname($destino);
        if (!is_dir($directorio)) {
            if (!mkdir($directorio, 0777, true)) {
                error_log("No se pudo crear el directorio: $directorio");
                return 'default.png';
            }
        }
        
        if (move_uploaded_file($file['tmp_name'], $destino)) {
            return $nombreArchivo;
        }
        
        error_log("Error moviendo archivo: " . $file['tmp_name'] . " a " . $destino);
        return 'default.png';
    }

    private function enviarError($mensaje) {
        error_log("ERROR proveedoresController: " . $mensaje);
        echo json_encode([
            'success' => false,
            'message' => $mensaje
        ]);
        exit;
    }
}
?>