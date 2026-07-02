<?php
// modelo/usuarioModel.php

require_once 'config/config.php';

class UsuarioModel {
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
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function verificarUsuario($username, $password) {
        $sql = "SELECT id, username, nombre, email, password, rol, avatar FROM usuarios WHERE username = :username AND activo = 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && password_verify($password, $usuario['password'])) {
                $this->actualizarUltimoLogin($usuario['id']);
                return $usuario;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en verificarUsuario: " . $e->getMessage());
            return false;
        }
    }

    private function actualizarUltimoLogin($id) {
        $sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando último login: " . $e->getMessage());
        }
    }

    public function obtenerRoles() {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'rol'");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            preg_match_all("/'([^']+)'/", $row['Type'], $matches);
            return $matches[1] ?? ['admin'];
        } catch (PDOException $e) {
            error_log("Error obteniendo roles: " . $e->getMessage());
            return ['admin'];
        }
    }

    public function obtenerCargos() {
        $sql = "SELECT id, nombre FROM cargos WHERE activo = 1 ORDER BY nombre";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo cargos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPerfilCompleto($id) {
        $sql = "SELECT u.id, u.username, u.nombre, u.email, u.rol, u.avatar,
                       u.activo, u.cargo_id, u.fecha_creacion, u.ultimo_login,
                       c.nombre AS cargo_nombre
                FROM usuarios u
                LEFT JOIN cargos c ON u.cargo_id = c.id
                WHERE u.id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerPerfilCompleto: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerUsuarioPorId($id) {
        $sql = "SELECT id, username, nombre, email, rol, avatar, activo, cargo_id, fecha_creacion, ultimo_login
                FROM usuarios WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerUsuarioPorId: " . $e->getMessage());
            return false;
        }
    }

    public function crearUsuario($datos) {
        $sql = "INSERT INTO usuarios (username, password, nombre, email, rol, avatar, activo, cargo_id, registro_publico)
                VALUES (:username, :password, :nombre, :email, :rol, :avatar, :activo, :cargo_id, :registro_publico)";

        try {
            $stmt = $this->db->prepare($sql);
            $hashedPassword = password_hash($datos['password'], PASSWORD_DEFAULT);

            $stmt->bindParam(':username', $datos['username']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':rol', $datos['rol']);
            $stmt->bindParam(':avatar', $datos['avatar']);
            $stmt->bindParam(':activo', $datos['activo'], PDO::PARAM_INT);
            $cargoId = !empty($datos['cargo_id']) ? (int)$datos['cargo_id'] : null;
            $stmt->bindParam(':cargo_id', $cargoId, PDO::PARAM_INT);
            $registroPublico = !empty($datos['registro_publico']) ? 1 : 0;
            $stmt->bindParam(':registro_publico', $registroPublico, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creando usuario: " . $e->getMessage());
            return false;
        }
    }

    public function aceptarRegistro($id) {
        $sql = "UPDATE usuarios SET activo = 1 WHERE id = :id AND registro_publico = 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error aceptando registro: " . $e->getMessage());
            return false;
        }
    }

    public function rechazarRegistro($id) {
        $sql = "DELETE FROM usuarios WHERE id = :id AND registro_publico = 1 AND activo = 0";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error rechazando registro: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPendientesRegistro() {
        $sql = "SELECT u.id, u.username, u.nombre, u.email, u.fecha_creacion,
                       c.nombre AS cargo_nombre
                FROM usuarios u
                LEFT JOIN cargos c ON c.id = u.cargo_id
                WHERE u.registro_publico = 1 AND u.activo = 0
                ORDER BY u.fecha_creacion DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obtenerPendientesRegistro: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerTodosUsuarios() {
        $sql = "SELECT u.id, u.username, u.nombre, u.email, u.rol, u.avatar, u.activo,
                       u.cargo_id, u.fecha_creacion, u.ultimo_login, u.registro_publico,
                       c.nombre AS cargo_nombre
                FROM usuarios u
                LEFT JOIN cargos c ON u.cargo_id = c.id
                ORDER BY u.fecha_creacion DESC";
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuarios: " . $e->getMessage());
            return [];
        }
    }

    public function eliminarUsuario($id) {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error eliminando usuario: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarUsuario($id, $datos) {
        $sql = "UPDATE usuarios SET
                nombre = :nombre,
                email = :email,
                rol = :rol,
                activo = :activo,
                cargo_id = :cargo_id
                WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':rol', $datos['rol']);
            $stmt->bindParam(':activo', $datos['activo'], PDO::PARAM_INT);
            $cargoId = !empty($datos['cargo_id']) ? (int)$datos['cargo_id'] : null;
            $stmt->bindParam(':cargo_id', $cargoId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando usuario: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarEstado($id, $activo) {
        $sql = "UPDATE usuarios SET activo = :activo WHERE id = :id";
        
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

    public function existeUsername($username) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE username = :username";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error verificando username: " . $e->getMessage());
            return false;
        }
    }

    public function existeEmail($email) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error verificando email: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerUsuarioPorUsername($username) {
        $sql = "SELECT id, username, nombre, email, rol, avatar, activo FROM usuarios WHERE username = :username";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerUsuarioPorUsername: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerUsuarioPorEmail($email) {
        $sql = "SELECT id, username, nombre, email, rol, avatar, activo FROM usuarios WHERE email = :email";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerUsuarioPorEmail: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarContrasena($id, $nuevaContrasena) {
        $sql = "UPDATE usuarios SET password = :password WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $hashedPassword = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando contraseña: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarAvatar($id, $avatar) {
        $sql = "UPDATE usuarios SET avatar = :avatar WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':avatar', $avatar);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando avatar: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerEstadisticas() {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as administradores,
                SUM(CASE WHEN rol = 'cocina' THEN 1 ELSE 0 END) as cocina,
                SUM(CASE WHEN rol = 'inventario' THEN 1 ELSE 0 END) as inventario
                FROM usuarios";
        
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return [
                'total' => 0,
                'activos' => 0,
                'administradores' => 0,
                'cocina' => 0,
                'inventario' => 0
            ];
        }
    }

    public function buscarUsuarios($termino) {
        $sql = "SELECT id, username, nombre, email, rol, avatar, activo 
                FROM usuarios 
                WHERE username LIKE :termino 
                OR nombre LIKE :termino 
                OR email LIKE :termino 
                ORDER BY nombre";
        
        try {
            $stmt = $this->db->prepare($sql);
            $termino = '%' . $termino . '%';
            $stmt->bindParam(':termino', $termino);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error buscando usuarios: " . $e->getMessage());
            return [];
        }
    }

    public function contarUsuariosPorRol($rol) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE rol = :rol AND activo = 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':rol', $rol);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error contando usuarios por rol: " . $e->getMessage());
            return 0;
        }
    }

    public function obtenerUsuariosRecientes($limite = 5) {
        $sql = "SELECT id, username, nombre, email, rol, avatar, fecha_creacion 
                FROM usuarios 
                ORDER BY fecha_creacion DESC 
                LIMIT :limite";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuarios recientes: " . $e->getMessage());
            return [];
        }
    }

    public function verificarContrasena($id, $contrasena) {
        $sql = "SELECT password FROM usuarios WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && password_verify($contrasena, $usuario['password'])) {
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error verificando contraseña: " . $e->getMessage());
            return false;
        }
    }
}
?>