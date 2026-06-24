<?php
// modelo/rolesModel.php
// Lee el ENUM de usuarios.rol desde la BD. El JSON guarda los nombres legibles.
require_once 'config/config.php';

class RolesModel {
    private $db;
    private $jsonFile;

    public function __construct() {
        $this->jsonFile = __DIR__ . '/../config/roles.json';
        try {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
            $this->db = new PDO($dsn, Config::DB_USER, Config::DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error conexión RolesModel: " . $e->getMessage());
        }
    }

    // Obtiene los valores del ENUM desde la base de datos
    private function getEnumValues(): array {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'rol'");
            $row  = $stmt->fetch();
            preg_match_all("/'([^']+)'/", $row['Type'], $m);
            return $m[1] ?? [];
        } catch (PDOException $e) {
            error_log("Error leyendo ENUM: " . $e->getMessage());
            return [];
        }
    }

    // Lee el mapa slug→nombre del JSON
    private function leerNombres(): array {
        if (!file_exists($this->jsonFile)) return [];
        return json_decode(file_get_contents($this->jsonFile), true) ?? [];
    }

    private function guardarNombres(array $nombres): void {
        file_put_contents($this->jsonFile, json_encode($nombres, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // Cuenta usuarios por rol
    private function contarUsuarios(): array {
        try {
            $stmt = $this->db->query("SELECT rol, COUNT(*) as total FROM usuarios GROUP BY rol");
            $rows = $stmt->fetchAll();
            $map  = [];
            foreach ($rows as $r) $map[$r['rol']] = (int)$r['total'];
            return $map;
        } catch (PDOException $e) {
            return [];
        }
    }

    // Devuelve todos los roles del ENUM con su nombre legible y conteo
    public function getAll(): array {
        $slugs   = $this->getEnumValues();
        $nombres = $this->leerNombres();
        $conteo  = $this->contarUsuarios();

        $result = [];
        foreach ($slugs as $slug) {
            $result[] = [
                'slug'    => $slug,
                'nombre'  => $nombres[$slug] ?? ucfirst($slug),
                'usuarios'=> $conteo[$slug]  ?? 0,
            ];
        }
        return $result;
    }

    // Solo activos (todos los del ENUM se consideran activos)
    public function getActivos(): array {
        $slugs   = $this->getEnumValues();
        $nombres = $this->leerNombres();
        $result  = [];
        foreach ($slugs as $slug) {
            $result[] = ['slug' => $slug, 'nombre' => $nombres[$slug] ?? ucfirst($slug)];
        }
        return $result;
    }

    // Actualiza solo el nombre visible de un rol
    public function actualizarNombre(string $slug, string $nombre): array {
        $slugs = $this->getEnumValues();
        if (!in_array($slug, $slugs)) {
            return ['success' => false, 'message' => 'Rol no encontrado en la base de datos'];
        }
        $nombres        = $this->leerNombres();
        $nombres[$slug] = $nombre;
        $this->guardarNombres($nombres);
        return ['success' => true, 'message' => 'Nombre actualizado exitosamente'];
    }
}
