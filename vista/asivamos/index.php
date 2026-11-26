<?php
// vista/asivamos/index.php
// Landing "Así vamos".
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Así vamos';
$paginaActual = 'asivamos';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

ob_start();
?>
<style>
    .asi-vamos {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .asi-vamos h1 {
        font-size: clamp(2.5rem, 8vw, 5rem);
        font-weight: 800;
        margin: 0;
        color: var(--color-texto, #1d1d1f);
        letter-spacing: -0.02em;
    }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<section class="asi-vamos">
    <h1>Así vamos</h1>
</section>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
