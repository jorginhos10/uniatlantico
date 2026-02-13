<?php
// modelo/proveedorModel.php

require_once 'config/config.php';

class proveedorModel {
    private $db;

    public function __construct() {
        $this->conectarDB();
    }

    private function conectarDB() {
        try {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
            $this->db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Error de conexión a BD: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
        }
    }

    public function obtenerTodosProveedores() {
        $sql = "SELECT * FROM proveedores ORDER BY fecha_creacion DESC";
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo proveedores: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerProveedorPorId($id) {
        $sql = "SELECT * FROM proveedores WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($proveedor) {
                error_log("Proveedor encontrado en DB: ID {$id}, Nombre: {$proveedor['nombre']}");
            } else {
                error_log("Proveedor NO encontrado en DB: ID {$id}");
            }
            
            return $proveedor;
        } catch (PDOException $e) {
            error_log("Error obteniendo proveedor ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    public function crearProveedor($datos) {
        try {
            // Preparar SQL con NIT/RUT
            $sql = "INSERT INTO proveedores (
                    nombre,
                    empresa,
                    telefono,
                    direccion,
                    correo,
                    categoria,
                    foto,
                    observacion,
                    nit_rut,
                    activo,
                    fecha_creacion
                ) VALUES (
                    :nombre,
                    :empresa,
                    :telefono,
                    :direccion,
                    :correo,
                    :categoria,
                    :foto,
                    :observacion,
                    :nit_rut,
                    :activo,
                    NOW()
                )";
            
            $stmt = $this->db->prepare($sql);
            
            // Vincular parámetros
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':empresa', $datos['empresa']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':direccion', $datos['direccion']);
            $stmt->bindParam(':correo', $datos['correo']);
            $stmt->bindParam(':categoria', $datos['categoria']);
            $stmt->bindParam(':foto', $datos['foto']);
            $stmt->bindParam(':observacion', $datos['observacion']);
            $stmt->bindParam(':nit_rut', $datos['nit_rut']);
            $stmt->bindParam(':activo', $datos['activo']);
            
            // Ejecutar y retornar resultado
            $result = $stmt->execute();
            
            if ($result) {
                error_log("✅ Proveedor insertado en DB: " . $datos['nombre']);
                $lastId = $this->db->lastInsertId();
                error_log("Último ID insertado: " . $lastId);
            } else {
                error_log("❌ Error ejecutando INSERT: " . implode(", ", $stmt->errorInfo()));
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error creando proveedor: " . $e->getMessage());
            error_log("Datos recibidos: " . print_r($datos, true));
            return false;
        }
    }

    public function eliminarProveedor($id) {
        $sql = "DELETE FROM proveedores WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error eliminando proveedor: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarEstado($id, $activo) {
        $sql = "UPDATE proveedores SET activo = :activo WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando estado: " . $e->getMessage());
            return false;
        }
    }

    public function buscarProveedores($termino = '') {
        $sql = "SELECT * FROM proveedores 
                WHERE nombre LIKE :termino 
                OR empresa LIKE :termino 
                OR telefono LIKE :termino
                OR correo LIKE :termino
                OR nit_rut LIKE :termino
                ORDER BY nombre";
        
        try {
            $stmt = $this->db->prepare($sql);
            $termino = '%' . $termino . '%';
            $stmt->bindParam(':termino', $termino);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error buscando proveedores: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerCategorias() {
        $sql = "SELECT DISTINCT categoria FROM proveedores WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria";
        
        try {
            $stmt = $this->db->query($sql);
            $resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Si no hay categorías, devolver las básicas
            if (empty($resultados)) {
                $resultados = ['cocina', 'inventario', 'mesero', 'admin','calidad'];
            }
            
            error_log("Categorías obtenidas de DB: " . implode(", ", $resultados));
            return $resultados;
        } catch (PDOException $e) {
            error_log("Error obteniendo categorías: " . $e->getMessage());
            // Devolver categorías por defecto si hay error
            return ['cocina', 'inventario', 'mesero', 'admin','calidad'];
        }
    }
}
?>