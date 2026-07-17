<?php
// vista/organigrama/index.php
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Organigrama';
$paginaActual = 'organigrama';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

ob_start();
?>
<style>
    :root {
        --ios-blue:    #007AFF;
        --ios-bg:      #F2F2F7;
        --ios-surface: #FFFFFF;
        --ios-label:   #000000;
        --ios-label2:  rgba(60,60,67,.6);
        --ios-sep:     rgba(60,60,67,.12);
    }
    body { font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif; }

    .org-wrap { padding: 0 4px; }

    .org-header {
        background: var(--ios-surface);
        border-radius: 20px;
        padding: 24px 28px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 .5px var(--ios-sep);
    }

    .org-header-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #007AFF 0%, #5856D6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 22px;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(0,122,255,.32);
    }

    .org-header h1 {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 2px;
        color: var(--ios-label);
    }

    .org-header p {
        font-size: 13px;
        color: var(--ios-label2);
        margin: 0;
    }

    .org-empty {
        background: var(--ios-surface);
        border-radius: 20px;
        padding: 70px 24px;
        text-align: center;
        box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 .5px var(--ios-sep);
    }

    .org-empty i {
        font-size: 46px;
        color: rgba(60,60,67,.25);
        margin-bottom: 16px;
    }

    .org-empty h5 {
        font-size: 17px;
        font-weight: 600;
        color: var(--ios-label);
        margin-bottom: 6px;
    }

    .org-empty p {
        font-size: 14px;
        color: var(--ios-label2);
        margin: 0;
    }

    /* ═══ NIVELES DEL ORGANIGRAMA ═══ */
    .org-chart {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 30px 10px;
    }

    .org-node {
        background: var(--ios-surface);
        border-radius: 16px;
        padding: 18px 26px;
        min-width: 260px;
        max-width: 420px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,.08), 0 0 0 .5px var(--ios-sep);
        border-top: 4px solid var(--ios-blue);
    }

    .org-node-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--ios-label);
        margin-bottom: 8px;
    }

    .org-node-users {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: center;
    }

    .org-node-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: var(--ios-bg);
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 12.5px;
        color: var(--ios-label);
    }

    .org-node-chip i { color: var(--ios-blue); font-size: 11px; }

    .org-node-empty {
        font-size: 13px;
        color: var(--ios-label2);
        font-style: italic;
    }

    .org-connector {
        width: 2px;
        height: 34px;
        background: var(--ios-sep);
    }

    /* ═══ RAMA CON VARIOS HIJOS EN EL MISMO NIVEL ═══ */
    .org-branch-row {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        gap: 50px;
        flex-wrap: wrap;
    }

    .org-branch-item {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .org-branch-connector {
        width: 2px;
        height: 20px;
        background: var(--ios-sep);
    }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<div class="org-wrap">
    <div class="org-header">
        <div class="org-header-icon">
            <i class="fas fa-sitemap"></i>
        </div>
        <div>
            <h1>Organigrama</h1>
            <p>Estructura organizacional de la Universidad del Atlántico</p>
        </div>
    </div>

    <?php
    // Busca la clave real de $usuariosPorRol cuyo nombre de rol coincida (sin acentos/mayúsculas) con el patrón buscado
    function org_buscarUsuariosPorRol($usuariosPorRol, $patron) {
        $normaliza = function($s) {
            $s = mb_strtolower(trim($s), 'UTF-8');
            return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
        };
        foreach ($usuariosPorRol as $rol => $usuarios) {
            if ($normaliza($rol) === $normaliza($patron)) {
                return $usuarios;
            }
        }
        return [];
    }

    function org_renderChips($usuarios) {
        if (empty($usuarios)) {
            echo '<span class="org-node-empty">Sin usuarios asignados</span>';
            return;
        }
        foreach ($usuarios as $u) {
            echo '<span class="org-node-chip"><i class="fas fa-user"></i>' . htmlspecialchars($u['nombre']) . '</span>';
        }
    }

    $usuariosAdmin         = org_buscarUsuariosPorRol($usuariosPorRol ?? [], 'Administrador');
    $usuariosSubAdmin      = org_buscarUsuariosPorRol($usuariosPorRol ?? [], 'Sub administrador');
    $usuariosGestorSubAdmin = org_buscarUsuariosPorRol($usuariosPorRol ?? [], 'Gestor de metas de sub-admin');
    $usuariosRespLinea     = org_buscarUsuariosPorRol($usuariosPorRol ?? [], 'Responsable de línea');
    ?>

    <div class="org-chart">
        <div class="org-node">
            <div class="org-node-title">Administrador</div>
            <div class="org-node-users"><?php org_renderChips($usuariosAdmin); ?></div>
        </div>

        <div class="org-connector"></div>

        <div class="org-node">
            <div class="org-node-title">Sub Administrador</div>
            <div class="org-node-users"><?php org_renderChips($usuariosSubAdmin); ?></div>
        </div>

        <div class="org-connector"></div>

        <div class="org-branch-row">
            <div class="org-branch-item">
                <div class="org-branch-connector"></div>
                <div class="org-node">
                    <div class="org-node-title">Gestor de Metas de Sub-Administrador</div>
                    <div class="org-node-users"><?php org_renderChips($usuariosGestorSubAdmin); ?></div>
                </div>
            </div>
            <div class="org-branch-item">
                <div class="org-branch-connector"></div>
                <div class="org-node">
                    <div class="org-node-title">Responsable de Línea</div>
                    <div class="org-node-users"><?php org_renderChips($usuariosRespLinea); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
