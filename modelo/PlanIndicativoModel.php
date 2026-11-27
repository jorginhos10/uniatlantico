<?php
// modelo/PlanIndicativoModel.php
// Plan Indicativo PDI 2022–2031: indicadores de resultado con línea base, metas por trienio y meta PDI.
// Los datos se cargan con database/plan_indicativo.sql.
require_once __DIR__ . '/../config/config.php';

class PlanIndicativoModel {
    private $db;

    // columna => [título, primer año, último año]
    const PERIODOS = [
        't2022_2024' => ['2022 - 2024', 2022, 2024],
        't2025_2027' => ['2025 - 2027', 2025, 2027],
        't2028_2030' => ['2028 - 2030', 2028, 2030],
        'a2031'      => ['2031', 2031, 2031],
    ];

    public function __construct() {
        $this->db = new PDO("mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET, Config::DB_USER, Config::DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    // Todos los indicadores; arreglo vacío si todavía no se ha ejecutado la carga
    public function listar() {
        try {
            return $this->db->query("SELECT * FROM plan_indicativo_144 ORDER BY numero, id")->fetchAll();
        } catch (PDOException $e) {
            error_log('Plan indicativo: ' . $e->getMessage());
            return [];
        }
    }

    // Número para mostrar: miles con punto, sin decimales sobrantes y con % si el indicador es porcentual
    public static function formato($v, $esPorcentaje) {
        if ($v === null || $v === '') return '—';
        $n = (float)$v * ($esPorcentaje ? 100 : 1);
        $s = number_format($n, 2, ',', '.');
        $s = rtrim(rtrim($s, '0'), ',');
        return $s . ($esPorcentaje ? '%' : '');
    }
}
