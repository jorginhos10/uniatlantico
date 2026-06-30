<?php
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Almacenamiento';
$paginaActual = 'almacenamiento';
$baseUrl      = Config::getBaseUrl();
$basePath     = Config::getBasePath();

$cssExtra = '<link rel="stylesheet" href="' . $baseUrl . '/assets/css/configuraciones.css">';
require_once __DIR__ . '/../complementos/header.php';
?>

<style>
    :root {
        --ios-blue:    #007AFF;
        --ios-green:   #34C759;
        --ios-red:     #FF3B30;
        --ios-orange:  #FF9500;
        --ios-purple:  #9C27B0;
        --ios-gray:    #8E8E93;
        --ios-bg:      #F2F2F7;
        --ios-surface: #FFFFFF;
        --ios-label:   #000000;
        --ios-label2:  rgba(60,60,67,.6);
        --ios-sep:     rgba(60,60,67,.12);
        --font: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
    }
    body { font-family: var(--font); background: var(--ios-bg); }

    .alm-wrap { max-width: 1100px; margin: 0 auto; padding: 28px 20px; }

    .alm-header {
        background: linear-gradient(135deg, #5856D6 0%, #8e44ad 100%);
        color: white;
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 6px 24px rgba(88,86,214,.35);
    }
    .alm-header-left { display: flex; align-items: center; gap: 18px; }
    .alm-header-icon {
        width: 56px; height: 56px;
        background: rgba(255,255,255,.2);
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .alm-header h1 { font-size: 24px; font-weight: 700; margin: 0 0 4px; letter-spacing: -.3px; }
    .alm-header p  { font-size: 14px; opacity: .85; margin: 0; }

    .btn-volver {
        background: rgba(255,255,255,.2);
        color: white;
        border: 1.5px solid rgba(255,255,255,.4);
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        display: flex; align-items: center; gap: 7px;
        transition: background .2s;
        white-space: nowrap;
    }
    .btn-volver:hover { background: rgba(255,255,255,.32); color: white; }

    .section-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--ios-label2);
        margin: 0 0 14px 4px;
    }

    .alm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .alm-card {
        background: var(--ios-surface);
        border-radius: 18px;
        padding: 24px 22px;
        text-decoration: none;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,.07);
        transition: transform .18s, box-shadow .18s;
        cursor: pointer;
        border: 1.5px solid transparent;
    }
    .alm-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,.13);
        border-color: var(--ios-sep);
        text-decoration: none;
    }
    .alm-card-icon {
        width: 50px; height: 50px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        color: white;
        flex-shrink: 0;
    }
    .alm-card-body h3 {
        font-size: 15px;
        font-weight: 700;
        color: var(--ios-label);
        margin: 0 0 5px;
    }
    .alm-card-body p {
        font-size: 13px;
        color: var(--ios-label2);
        margin: 0;
        line-height: 1.45;
    }
    .alm-card-badge {
        margin-top: 10px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .badge-disponible { background: rgba(52,199,89,.12); color: #1a7a3a; }
    .badge-pronto     { background: rgba(255,149,0,.12);  color: #b36b00; }

    @media (max-width: 600px) {
        .alm-grid { grid-template-columns: 1fr; }
        .alm-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="alm-wrap">

    <div class="alm-header">
        <div class="alm-header-left">
            <div class="alm-header-icon"><i class="fas fa-database"></i></div>
            <div>
                <h1>Almacenamiento</h1>
                <p>Gestiona el espacio, copias de seguridad y sincronización del sistema</p>
            </div>
        </div>
        <a href="<?php echo $basePath; ?>/configuraciones" class="btn-volver">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <p class="section-title">Herramientas disponibles</p>

    <div class="alm-grid">

        <!-- Backup -->
        <a href="<?php echo $basePath; ?>/almacenamiento/backup" class="alm-card">
            <div class="alm-card-icon" style="background: linear-gradient(135deg,#34C759,#2ecc71);">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="alm-card-body">
                <h3>Backup</h3>
                <p>Genera y descarga copias de seguridad de la base de datos y archivos del sistema</p>
                <span class="alm-card-badge badge-disponible"><i class="fas fa-check-circle"></i> Disponible</span>
            </div>
        </a>

        <!-- Espacio -->
        <div class="alm-card" style="cursor:default;">
            <div class="alm-card-icon" style="background: linear-gradient(135deg,#007AFF,#5856D6);">
                <i class="fas fa-hdd"></i>
            </div>
            <div class="alm-card-body">
                <h3>Espacio en disco</h3>
                <p>Monitorea el uso del almacenamiento y libera espacio de archivos temporales</p>
                <span class="alm-card-badge badge-pronto"><i class="fas fa-clock"></i> Próximamente</span>
            </div>
        </div>

        <!-- Sincronización -->
        <div class="alm-card" style="cursor:default;">
            <div class="alm-card-icon" style="background: linear-gradient(135deg,#FF9500,#e67e22);">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="alm-card-body">
                <h3>Sincronización</h3>
                <p>Configura la sincronización automática con servicios externos de almacenamiento</p>
                <span class="alm-card-badge badge-pronto"><i class="fas fa-clock"></i> Próximamente</span>
            </div>
        </div>

        <!-- Logs -->
        <div class="alm-card" style="cursor:default;">
            <div class="alm-card-icon" style="background: linear-gradient(135deg,#FF3B30,#c0392b);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="alm-card-body">
                <h3>Registros del sistema</h3>
                <p>Consulta y descarga los logs de actividad y errores del sistema</p>
                <span class="alm-card-badge badge-pronto"><i class="fas fa-clock"></i> Próximamente</span>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
