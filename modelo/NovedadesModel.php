<?php
require_once 'config/config.php';

class NovedadesModel {
    private $db;

    public function __construct() {
        $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
        $this->db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getAll(): array {
        return $this->db->query(
            "SELECT * FROM novedades ORDER BY orden ASC, fecha_creacion DESC"
        )->fetchAll();
    }

    public function getActivas(): array {
        return $this->db->query(
            "SELECT titulo, contenido FROM novedades WHERE activo = 1 ORDER BY orden ASC, fecha_creacion DESC LIMIT 10"
        )->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM novedades WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return $r ?: null;
    }

    public function crear(array $data): bool {
        // La nueva novedad siempre va al inicio (orden = 0), las demás se desplazan
        $this->db->exec("UPDATE novedades SET orden = orden + 1");
        $stmt = $this->db->prepare(
            "INSERT INTO novedades (titulo, contenido, activo, orden) VALUES (:titulo, :contenido, :activo, 0)"
        );
        return $stmt->execute([
            ':titulo'    => $data['titulo'],
            ':contenido' => $data['contenido'],
            ':activo'    => $data['activo'] ?? 1,
        ]);
    }

    public function actualizar(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE novedades SET titulo=:titulo, contenido=:contenido, activo=:activo WHERE id=:id"
        );
        return $stmt->execute([
            ':titulo'    => $data['titulo'],
            ':contenido' => $data['contenido'],
            ':activo'    => $data['activo'] ?? 1,
            ':id'        => $id,
        ]);
    }

    public function reordenarLote(array $ids): bool {
        $stmt = $this->db->prepare("UPDATE novedades SET orden = :orden WHERE id = :id");
        foreach ($ids as $posicion => $id) {
            $stmt->execute([':orden' => $posicion, ':id' => (int)$id]);
        }
        return true;
    }

    public function eliminar(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM novedades WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function toggleActivo(int $id): bool {
        $stmt = $this->db->prepare(
            "UPDATE novedades SET activo = IF(activo=1,0,1) WHERE id=:id"
        );
        return $stmt->execute([':id' => $id]);
    }
}
?>
