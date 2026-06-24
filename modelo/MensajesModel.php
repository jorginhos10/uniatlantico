<?php
require_once 'config/config.php';

class MensajesModel {
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
            error_log("Error de conexión en MensajesModel: " . $e->getMessage());
        }
    }

    // ── RECIBIDOS ──────────────────────────────────────────────────────────
    // Un mensaje llega al usuario si:
    //   tipo=usuario  → destinatario_id = usuario.id
    //   tipo=rol      → destinatario_id = usuario.rol
    //   tipo=dependencia → destinatario_id = usuario.cargo_id  (columna opcional)
    public function getRecibidos(int $userId, string $userRol): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT m.*,
                    u.nombre  AS remitente_nombre,
                    u.avatar  AS remitente_avatar,
                    u.rol     AS remitente_rol,
                    CASE WHEN ml.usuario_id IS NOT NULL THEN 1 ELSE 0 END AS leido
                 FROM mensajes m
                 JOIN usuarios u ON u.id = m.remitente_id
                 LEFT JOIN mensajes_leidos ml
                        ON ml.mensaje_id = m.id AND ml.usuario_id = :uid_l
                 WHERE m.remitente_id != :uid
                   AND (
                     (m.tipo_destinatario = 'usuario'      AND CAST(m.destinatario_id AS UNSIGNED) = :uid_u)
                  OR (m.tipo_destinatario = 'rol'          AND m.destinatario_id = :rol)
                  OR (m.tipo_destinatario = 'dependencia'  AND CAST(m.destinatario_id AS UNSIGNED) =
                         (SELECT cargo_id FROM usuarios WHERE id = :uid_c))
                   )
                 ORDER BY m.fecha_envio DESC"
            );
            $stmt->execute([
                ':uid'   => $userId,
                ':uid_l' => $userId,
                ':uid_u' => $userId,
                ':uid_c' => $userId,
                ':rol'   => $userRol,
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Si cargo_id no existe como columna, re-intentar sin esa condición
            return $this->getRecibidosSinCargo($userId, $userRol);
        }
    }

    private function getRecibidosSinCargo(int $userId, string $userRol): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT m.*,
                    u.nombre  AS remitente_nombre,
                    u.avatar  AS remitente_avatar,
                    u.rol     AS remitente_rol,
                    CASE WHEN ml.usuario_id IS NOT NULL THEN 1 ELSE 0 END AS leido
                 FROM mensajes m
                 JOIN usuarios u ON u.id = m.remitente_id
                 LEFT JOIN mensajes_leidos ml
                        ON ml.mensaje_id = m.id AND ml.usuario_id = :uid_l
                 WHERE m.remitente_id != :uid
                   AND (
                     (m.tipo_destinatario = 'usuario' AND CAST(m.destinatario_id AS UNSIGNED) = :uid_u)
                  OR (m.tipo_destinatario = 'rol'     AND m.destinatario_id = :rol)
                   )
                 ORDER BY m.fecha_envio DESC"
            );
            $stmt->execute([
                ':uid'   => $userId,
                ':uid_l' => $userId,
                ':uid_u' => $userId,
                ':rol'   => $userRol,
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getRecibidos: " . $e->getMessage());
            return [];
        }
    }

    // ── ENVIADOS ───────────────────────────────────────────────────────────
    public function getEnviados(int $userId): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT m.*,
                    CASE m.tipo_destinatario
                        WHEN 'usuario'      THEN u2.nombre
                        WHEN 'dependencia'  THEN CONCAT('Dependencia: ', COALESCE(c.nombre, m.destinatario_id))
                        WHEN 'rol'          THEN CONCAT('Rol: ', m.destinatario_id)
                        ELSE m.destinatario_id
                    END AS destinatario_nombre,
                    u2.avatar AS destinatario_avatar,
                    1 AS leido
                 FROM mensajes m
                 LEFT JOIN usuarios u2
                        ON u2.id = CAST(m.destinatario_id AS UNSIGNED)
                       AND m.tipo_destinatario = 'usuario'
                 LEFT JOIN cargos c
                        ON c.id = CAST(m.destinatario_id AS UNSIGNED)
                       AND m.tipo_destinatario = 'dependencia'
                 WHERE m.remitente_id = :uid
                 ORDER BY m.fecha_envio DESC"
            );
            $stmt->execute([':uid' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getEnviados: " . $e->getMessage());
            return [];
        }
    }

    // ── CREAR ──────────────────────────────────────────────────────────────
    public function crear(string $asunto, string $cuerpo, int $remitenteId, string $tipo, string $destinatarioId): array {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO mensajes (asunto, cuerpo, remitente_id, tipo_destinatario, destinatario_id, fecha_envio)
                 VALUES (:asunto, :cuerpo, :remitente, :tipo, :dest, NOW())"
            );
            $stmt->execute([
                ':asunto'   => $asunto,
                ':cuerpo'   => $cuerpo,
                ':remitente' => $remitenteId,
                ':tipo'     => $tipo,
                ':dest'     => $destinatarioId,
            ]);
            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error crear mensaje: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al enviar el mensaje'];
        }
    }

    // ── VER ────────────────────────────────────────────────────────────────
    public function getById(int $id): ?array {
        try {
            $stmt = $this->db->prepare(
                "SELECT m.*, u.nombre AS remitente_nombre, u.avatar AS remitente_avatar, u.rol AS remitente_rol
                 FROM mensajes m
                 JOIN usuarios u ON u.id = m.remitente_id
                 WHERE m.id = :id"
            );
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            error_log("Error getById mensaje: " . $e->getMessage());
            return null;
        }
    }

    // ── MARCAR LEÍDO ───────────────────────────────────────────────────────
    public function marcarLeido(int $mensajeId, int $userId): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO mensajes_leidos (mensaje_id, usuario_id, fecha_lectura)
                 VALUES (:mid, :uid, NOW())"
            );
            $stmt->execute([':mid' => $mensajeId, ':uid' => $userId]);
        } catch (PDOException $e) {
            error_log("Error marcarLeido: " . $e->getMessage());
        }
    }

    // ── ELIMINAR ───────────────────────────────────────────────────────────
    public function eliminar(int $id, int $userId): array {
        try {
            // Solo puede eliminar quien lo envió
            $stmt = $this->db->prepare(
                "DELETE FROM mensajes WHERE id = :id AND remitente_id = :uid"
            );
            $stmt->execute([':id' => $id, ':uid' => $userId]);
            if ($stmt->rowCount() > 0) {
                $this->db->prepare("DELETE FROM mensajes_leidos WHERE mensaje_id = :id")->execute([':id' => $id]);
                return ['success' => true];
            }
            return ['success' => false, 'message' => 'No autorizado o mensaje no encontrado'];
        } catch (PDOException $e) {
            error_log("Error eliminar mensaje: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar'];
        }
    }

    // ── CONTAR NO LEÍDOS (para campanita) ─────────────────────────────────
    public function contarNoLeidos(int $userId, string $userRol): int {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM mensajes m
                 LEFT JOIN mensajes_leidos ml ON ml.mensaje_id = m.id AND ml.usuario_id = :uid_l
                 WHERE ml.usuario_id IS NULL
                   AND m.remitente_id != :uid
                   AND (
                     (m.tipo_destinatario = 'usuario'     AND CAST(m.destinatario_id AS UNSIGNED) = :uid_u)
                  OR (m.tipo_destinatario = 'rol'         AND m.destinatario_id = :rol)
                  OR (m.tipo_destinatario = 'dependencia' AND CAST(m.destinatario_id AS UNSIGNED) =
                         (SELECT cargo_id FROM usuarios WHERE id = :uid_c))
                   )"
            );
            $stmt->execute([
                ':uid'   => $userId,
                ':uid_l' => $userId,
                ':uid_u' => $userId,
                ':uid_c' => $userId,
                ':rol'   => $userRol,
            ]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return $this->contarNoLeidosSinCargo($userId, $userRol);
        }
    }

    private function contarNoLeidosSinCargo(int $userId, string $userRol): int {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM mensajes m
                 LEFT JOIN mensajes_leidos ml ON ml.mensaje_id = m.id AND ml.usuario_id = :uid_l
                 WHERE ml.usuario_id IS NULL
                   AND m.remitente_id != :uid
                   AND (
                     (m.tipo_destinatario = 'usuario' AND CAST(m.destinatario_id AS UNSIGNED) = :uid_u)
                  OR (m.tipo_destinatario = 'rol'     AND m.destinatario_id = :rol)
                   )"
            );
            $stmt->execute([
                ':uid'   => $userId,
                ':uid_l' => $userId,
                ':uid_u' => $userId,
                ':rol'   => $userRol,
            ]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error contarNoLeidos: " . $e->getMessage());
            return 0;
        }
    }

    // ── RECIENTES NO LEÍDOS (para dropdown de campanita) ──────────────────
    public function getRecientesNoLeidos(int $userId, string $userRol, int $limit = 5): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT m.id, m.asunto, m.fecha_envio, u.nombre AS remitente_nombre, u.avatar AS remitente_avatar
                 FROM mensajes m
                 JOIN usuarios u ON u.id = m.remitente_id
                 LEFT JOIN mensajes_leidos ml ON ml.mensaje_id = m.id AND ml.usuario_id = :uid_l
                 WHERE ml.usuario_id IS NULL
                   AND m.remitente_id != :uid
                   AND (
                     (m.tipo_destinatario = 'usuario' AND CAST(m.destinatario_id AS UNSIGNED) = :uid_u)
                  OR (m.tipo_destinatario = 'rol'     AND m.destinatario_id = :rol)
                   )
                 ORDER BY m.fecha_envio DESC
                 LIMIT :lim"
            );
            $stmt->bindValue(':uid',   $userId, PDO::PARAM_INT);
            $stmt->bindValue(':uid_l', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':uid_u', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':rol',   $userRol, PDO::PARAM_STR);
            $stmt->bindValue(':lim',   $limit,  PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getRecientesNoLeidos: " . $e->getMessage());
            return [];
        }
    }

    // ── SELECTORES PARA EL FORMULARIO ─────────────────────────────────────
    public function getUsuarios(int $excluirId = 0): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, nombre, rol, avatar FROM usuarios WHERE activo = 1 AND id != :exc ORDER BY nombre ASC"
            );
            $stmt->execute([':exc' => $excluirId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getDependencias(): array {
        try {
            $stmt = $this->db->query("SELECT id, nombre FROM cargos WHERE activo = 1 ORDER BY nombre ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getRoles(): array {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'rol'");
            $row  = $stmt->fetch();
            if (!$row) return [];
            preg_match_all("/'([^']+)'/", $row['Type'], $m);
            return array_map(fn($r) => ['slug' => $r, 'nombre' => ucfirst($r)], $m[1]);
        } catch (PDOException $e) {
            return [];
        }
    }
}
