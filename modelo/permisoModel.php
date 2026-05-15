<?php
// modelo/permisoModel.php - VERSIÓN COMPLETA Y CORREGIDA

require_once 'config/config.php';

class PermisoModel {
    private $db;

    public function __construct() {
        $this->conectarDB();
    }

    private function conectarDB() {
        try {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
            $this->db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ Error de conexión a la base de datos: " . $e->getMessage());
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Obtener permisos de un usuario específico
     * Solo muestra permisos con estado = 1 en lista_permisos
     * activo = 1 si existe en detalle_permiso, 0 si no existe
     */
    public function obtenerPermisosUsuario($usuario_id) {
        error_log("🔍 Obteniendo permisos para usuario ID: $usuario_id");
        
        $sql = "SELECT 
                    lp.id,
                    lp.nombre,
                    CASE 
                        WHEN dp.id IS NOT NULL THEN 1 
                        ELSE 0 
                    END as activo,
                    dp.fecha_asignacion
                FROM lista_permisos lp
                LEFT JOIN detalle_permiso dp ON lp.id = dp.id_permiso 
                    AND dp.id_usuario = :usuario_id
                WHERE lp.estado = 1  -- Solo permisos activos
                ORDER BY lp.nombre ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            $permisos = $stmt->fetchAll();
            
            error_log("📊 Permisos encontrados: " . count($permisos));
            
            // Formatear nombres para mostrar mejor
            foreach ($permisos as &$permiso) {
                $permiso['nombre_formateado'] = $this->formatearNombrePermiso($permiso['nombre']);
                $permiso['descripcion'] = $this->getDescripcionPermiso($permiso['nombre']);
            }
            
            return $permisos;
        } catch (PDOException $e) {
            error_log("❌ Error obteniendo permisos de usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Formatear nombre del permiso para mostrar
     */
    private function formatearNombrePermiso($nombre) {
        $nombres = [
            'ver_dashboard'       => 'Dashboard',
            'gestionar_recetas'   => 'Formato FOR-DE-144',
            'for_de_144'          => 'Formato FOR-DE-144',
            'configurar_sistema'  => 'Configuraciones',
            'gestionar_usuarios'  => 'Gestionar Usuarios',
            'gestionar_permisos'  => 'Gestionar Permisos',
        ];

        return $nombres[$nombre] ?? ucwords(str_replace('_', ' ', $nombre));
    }

    private function getDescripcionPermiso($nombre) {
        $descripciones = [
            'ver_dashboard'       => 'Permite acceder al panel principal',
            'gestionar_recetas'   => 'Permite acceder y gestionar el Formato FOR-DE-144',
            'for_de_144'          => 'Permite acceder y gestionar el Formato FOR-DE-144',
            'configurar_sistema'  => 'Permite acceder a las configuraciones del sistema',
            'gestionar_usuarios'  => 'Permite crear, editar y eliminar usuarios',
            'gestionar_permisos'  => 'Permite gestionar los permisos de otros usuarios',
        ];

        return $descripciones[$nombre] ?? 'Permiso del sistema';
    }

    /**
     * Asignar permiso a usuario (insertar en detalle_permiso)
     */
    public function asignarPermisoUsuario($usuario_id, $permiso_id) {
        error_log("➕ Asignando permiso $permiso_id a usuario $usuario_id");
        
        try {
            // Verificar que el permiso esté activo en lista_permisos
            $sqlCheckPermiso = "SELECT id FROM lista_permisos WHERE id = :permiso_id AND estado = 1";
            $stmtCheckPermiso = $this->db->prepare($sqlCheckPermiso);
            $stmtCheckPermiso->bindParam(':permiso_id', $permiso_id, PDO::PARAM_INT);
            $stmtCheckPermiso->execute();
            
            $permisoActivo = $stmtCheckPermiso->fetch();
            
            if (!$permisoActivo) {
                error_log("❌ Permiso $permiso_id no está activo o no existe");
                return false;
            }
            
            // Verificar si ya existe en detalle_permiso
            $sqlCheck = "SELECT id FROM detalle_permiso 
                        WHERE id_usuario = :usuario_id AND id_permiso = :permiso_id";
            
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmtCheck->bindParam(':permiso_id', $permiso_id, PDO::PARAM_INT);
            $stmtCheck->execute();
            
            $existe = $stmtCheck->fetch();
            
            if (!$existe) {
                // Insertar nuevo registro
                $sqlInsert = "INSERT INTO detalle_permiso (id_usuario, id_permiso, fecha_asignacion) 
                            VALUES (:usuario_id, :permiso_id, NOW())";
                $stmtInsert = $this->db->prepare($sqlInsert);
                $stmtInsert->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                $stmtInsert->bindParam(':permiso_id', $permiso_id, PDO::PARAM_INT);
                
                $resultado = $stmtInsert->execute();
                
                if ($resultado) {
                    error_log("✅ Permiso asignado: Usuario $usuario_id, Permiso $permiso_id");
                } else {
                    error_log("❌ Error al insertar permiso");
                }
                
                return $resultado;
            }
            
            error_log("ℹ️ Permiso ya estaba asignado");
            return true; // Ya existe
            
        } catch (PDOException $e) {
            error_log("❌ Error asignando permiso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Quitar permiso de usuario (eliminar de detalle_permiso)
     */
    public function quitarPermisoUsuario($usuario_id, $permiso_id) {
        error_log("➖ Quitando permiso $permiso_id de usuario $usuario_id");
        
        try {
            $sql = "DELETE FROM detalle_permiso 
                    WHERE id_usuario = :usuario_id AND id_permiso = :permiso_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(':permiso_id', $permiso_id, PDO::PARAM_INT);
            
            $resultado = $stmt->execute();
            $filasAfectadas = $stmt->rowCount();
            
            if ($resultado && $filasAfectadas > 0) {
                error_log("✅ Permiso removido: Usuario $usuario_id, Permiso $permiso_id ($filasAfectadas filas)");
            } else {
                error_log("ℹ️ No se encontró permiso para remover");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            error_log("❌ Error quitando permiso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Alternar permiso (asignar/quitar según existencia en detalle_permiso)
     */
    public function togglePermisoUsuario($usuario_id, $permiso_id, $nuevo_estado) {
        error_log("🔄 Toggle permiso $permiso_id para usuario $usuario_id -> estado: $nuevo_estado");
        
        // $nuevo_estado = 1: insertar en detalle_permiso
        // $nuevo_estado = 0: eliminar de detalle_permiso
        if ($nuevo_estado == 1) {
            return $this->asignarPermisoUsuario($usuario_id, $permiso_id);
        } else {
            return $this->quitarPermisoUsuario($usuario_id, $permiso_id);
        }
    }

    /**
     * Obtener estadísticas de permisos de un usuario
     */
    public function obtenerEstadisticasPermisos($usuario_id) {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM detalle_permiso dp
                     JOIN lista_permisos lp ON dp.id_permiso = lp.id
                     WHERE dp.id_usuario = :usuario_id AND lp.estado = 1) as permisos_asignados,
                    (SELECT COUNT(*) FROM lista_permisos WHERE estado = 1) as total_permisos_disponibles";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            $estadisticas = $stmt->fetch();
            
            // Asegurar valores numéricos
            $estadisticas['permisos_asignados'] = (int)$estadisticas['permisos_asignados'];
            $estadisticas['total_permisos_disponibles'] = (int)$estadisticas['total_permisos_disponibles'];
            
            error_log("📊 Estadísticas usuario $usuario_id: " . 
                     json_encode($estadisticas));
            
            return $estadisticas;
        } catch (PDOException $e) {
            error_log("❌ Error obteniendo estadísticas: " . $e->getMessage());
            return ['permisos_asignados' => 0, 'total_permisos_disponibles' => 0];
        }
    }

    /**
     * Obtener información de usuario
     */
    public function obtenerInfoUsuarioParaPermisos($usuario_id) {
        $sql = "SELECT id, username, nombre, email, rol, avatar FROM usuarios WHERE id = :usuario_id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Formatear rol para mostrar
                $roles = [
                    'admin'        => 'Administrador',
                    'director'     => 'Director',
                    'coordinador'  => 'Coordinador',
                    'jefe'         => 'Jefe de Área',
                    'analista'     => 'Analista',
                    'secretario'   => 'Secretario(a)',
                    'auxiliar'     => 'Auxiliar',
                    'tecnico'      => 'Técnico',
                    'asesor'       => 'Asesor',
                    'pasante'      => 'Pasante',
                ];
                $usuario['rol_formateado'] = $roles[$usuario['rol']] ?? ucfirst($usuario['rol']);
                
                error_log("👤 Info usuario $usuario_id: " . $usuario['username']);
            } else {
                error_log("❌ Usuario $usuario_id no encontrado");
            }
            
            return $usuario;
        } catch (PDOException $e) {
            error_log("❌ Error obteniendo info de usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si un permiso está activo en lista_permisos
     */
    public function permisoEstaActivo($permiso_id) {
        $sql = "SELECT estado FROM lista_permisos WHERE id = :permiso_id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':permiso_id', $permiso_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch();
            $estaActivo = $result && $result['estado'] == 1;
            
            error_log("🔍 Permiso $permiso_id activo: " . ($estaActivo ? 'Sí' : 'No'));
            
            return $estaActivo;
            
        } catch (PDOException $e) {
            error_log("❌ Error verificando estado del permiso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener el total de permisos activos
     */
    public function obtenerTotalPermisosActivos() {
        $sql = "SELECT COUNT(*) as total FROM lista_permisos WHERE estado = 1";
        
        try {
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            $total = (int)$result['total'];
            
            error_log("📋 Total permisos activos: $total");
            
            return $total;
        } catch (PDOException $e) {
            error_log("❌ Error obteniendo total de permisos activos: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener todos los permisos (para debugging)
     */
    public function obtenerTodosPermisos() {
        $sql = "SELECT id, nombre, estado, fecha_creacion FROM lista_permisos ORDER BY nombre";
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("❌ Error obteniendo todos los permisos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener conexión a DB (para debugging)
     */
    public function getDB() {
        return $this->db;
    }
}
?>