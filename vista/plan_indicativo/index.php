<?php
// vista/plan_indicativo/index.php
// Landing "Plan indicativo" (el contenido se define más adelante).
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/config.php';

$titulo       = 'Plan indicativo';
$paginaActual = 'plan-indicativo';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

ob_start();
?>
<style>
    .plan-ind { min-height: 60vh; display: flex; align-items: center; justify-content: center; text-align: center; }
    .plan-ind h1 { font-size: clamp(2.5rem, 8vw, 5rem); font-weight: 800; margin: 0; color: #1d1d1f; letter-spacing: -.02em; }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<section class="plan-ind">
    <h1>Plan indicativo</h1>
</section>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
