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
        $sql = "SELECT id, username, nombre, email, password, rol, avatar, cargo_id FROM usuarios WHERE (username = :username OR email = :email) AND activo = 1 ORDER BY (username = :username2) DESC LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            // Se puede iniciar sesión con el usuario o con el correo (si coinciden ambos, gana el username)
            $stmt->execute([':username' => $username, ':email' => $username, ':username2' => $username]);

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

            if (!$stmt->execute()) {
                return false;
            }

            $this->ultimoIdCreado = (int)$this->db->lastInsertId();
            $this->asignarPermisosPorDefecto($this->ultimoIdCreado);
            return true;
        } catch (PDOException $e) {
            error_log("Error creando usuario: " . $e->getMessage());
            return false;
        }
    }

    // Todo usuario nuevo arranca con Chat, Dashboard y FOR-DE-144 (con su sub-permiso "ver").
    // Si falla no se revierte la creación del usuario: los permisos se pueden asignar a mano.
    private function asignarPermisosPorDefecto($usuarioId) {
        if ($usuarioId <= 0) return;

        try {
            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO detalle_permiso (id_usuario, id_permiso, fecha_asignacion)
                 SELECT :uid, lp.id, NOW()
                 FROM lista_permisos lp
                 WHERE lp.estado = 1
                   AND (lp.nombre IN ('ver_dashboard', 'for_de_144', 'gestionar_recetas') OR lp.nombre LIKE '%chat%')"
            );
            $stmt->execute([':uid' => $usuarioId]);

            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO detalle_subpermiso (usuario_id, subpermiso_id, fecha_asignacion)
                 SELECT dp.id_usuario, s.id, NOW()
                 FROM detalle_permiso dp
                 JOIN subpermisos s ON s.permiso_id = dp.id_permiso AND s.nombre = 'ver' AND s.estado = 1
                 WHERE dp.id_usuario = :uid"
            );
            $stmt->execute([':uid' => $usuarioId]);
        } catch (PDOException $e) {
            error_log("Error asignando permisos por defecto: " . $e->getMessage());
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

    // Id del último usuario creado con crearUsuario()
    public $ultimoIdCreado = 0;

    // ── Dependencias de las que el usuario es responsable (además de su dependencia principal) ──

    public function obtenerDependenciasUsuario($usuarioId) {
        try {
            $stmt = $this->db->prepare("SELECT cargo_id FROM usuario_dependencias WHERE usuario_id = :id");
            $stmt->execute([':id' => (int)$usuarioId]);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            // Sin la migración todavía: el usuario no tiene dependencias adicionales
            return [];
        }
    }

    public function guardarDependenciasUsuario($usuarioId, array $cargoIds) {
        $cargoIds = array_values(array_unique(array_filter(array_map('intval', $cargoIds))));
        try {
            $this->db->beginTransaction();
            $this->db->prepare("DELETE FROM usuario_dependencias WHERE usuario_id = :id")->execute([':id' => (int)$usuarioId]);
            $ins = $this->db->prepare("INSERT INTO usuario_dependencias (usuario_id, cargo_id) VALUES (:u, :c)");
            foreach ($cargoIds as $c) $ins->execute([':u' => (int)$usuarioId, ':c' => $c]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Error guardarDependenciasUsuario: " . $e->getMessage());
            return false;
        }
    }

    // ── Organigrama: a quién le reporta cada usuario (usuarios.reporta_a = id del superior) ──

    private function asegurarColumnaReportaA() {
        if ($this->db->query("SHOW COLUMNS FROM usuarios LIKE 'reporta_a'")->rowCount() === 0) {
            $this->db->exec("ALTER TABLE usuarios ADD COLUMN reporta_a INT NULL DEFAULT NULL");
        }
    }

    public function obtenerReportaA($usuarioId) {
        try {
            $this->asegurarColumnaReportaA();
            $stmt = $this->db->prepare("SELECT reporta_a FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => (int)$usuarioId]);
            $v = $stmt->fetchColumn();
            return $v ? (int)$v : null;
        } catch (PDOException $e) {
            error_log("Error obtenerReportaA: " . $e->getMessage());
            return null;
        }
    }

    // El superior debe existir y no puede ser el mismo usuario ni alguien que ya le reporta (evita ciclos)
    public function guardarReportaA($usuarioId, $jefeId) {
        $usuarioId = (int)$usuarioId;
        $jefeId = $jefeId ? (int)$jefeId : null;
        try {
            $this->asegurarColumnaReportaA();
            if ($jefeId !== null) {
                if ($jefeId === $usuarioId) return false;
                $cur = $jefeId;
                for ($i = 0; $cur && $i < 50; $i++) {
                    if ($cur === $usuarioId) return false;
                    $q = $this->db->prepare("SELECT reporta_a FROM usuarios WHERE id = :id");
                    $q->execute([':id' => $cur]);
                    $cur = (int)$q->fetchColumn();
                }
            }
            $stmt = $this->db->prepare("UPDATE usuarios SET reporta_a = :j WHERE id = :id");
            $stmt->bindValue(':j', $jefeId, $jefeId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':id', $usuarioId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error guardarReportaA: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarUsuario($id) {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            if (!$stmt->execute()) return false;
            try {
                $this->db->prepare("DELETE FROM usuario_dependencias WHERE usuario_id = :id")->execute([':id' => (int)$id]);
            } catch (PDOException $e) { /* sin la migración no hay nada que borrar */ }
            return true;
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

    // ¿Otro usuario (distinto de $id) ya usa este correo?
    public function emailUsadoPorOtro($email, $id) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email AND id <> :id");
            $stmt->execute([':email' => $email, ':id' => $id]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en emailUsadoPorOtro: " . $e->getMessage());
            return true; // ante la duda, no permitir el cambio
        }
    }

    // Datos que el propio usuario puede cambiar desde su perfil
    public function actualizarDatosPerfil($id, $nombre, $email) {
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET nombre = :nombre, email = :email WHERE id = :id");
            return $stmt->execute([':nombre' => $nombre, ':email' => $email, ':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error en actualizarDatosPerfil: " . $e->getMessage());
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