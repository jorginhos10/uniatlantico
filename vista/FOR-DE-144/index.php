<?php
// vista/FOR-DE-144/index.php

require_once __DIR__ . '/../../config/security.php';

$titulo      = 'FOR-DE-144 — Formularios';
$paginaActual = 'FOR-DE-144';

ob_start();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --ios-blue:       #007AFF;
        --ios-green:      #34C759;
        --ios-red:        #FF3B30;
        --ios-orange:     #FF9500;
        --ios-purple:     #AF52DE;
        --ios-bg:         #F2F2F7;
        --ios-surface:    #FFFFFF;
        --ios-label:      #000000;
        --ios-label2:     rgba(60,60,67,.6);
        --ios-label3:     rgba(60,60,67,.3);
        --ios-sep:        rgba(60,60,67,.12);
        --ios-fill:       rgba(120,120,128,.12);
        --ios-fill2:      rgba(120,120,128,.06);
        --r-sm:  8px;
        --r:    12px;
        --r-lg: 16px;
        --r-xl: 20px;
    }

    /* ── Body ── */
    body {
        background: var(--ios-bg);
        font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        color: var(--ios-label);
    }

    /* ── Page wrapper ── */
    .f144-wrap {
        padding: 0 20px 56px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* ── Page header ── */
    .f144-header {
        background: var(--ios-surface);
        border-radius: var(--r-lg);
        padding: 24px 28px;
        margin-bottom: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 .5px var(--ios-sep);
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .f144-header-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--r);
        background: linear-gradient(135deg, var(--ios-blue) 0%, #5856d6 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0,122,255,.3);
    }

    .f144-header-text h1 {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -.4px;
        margin: 0 0 2px;
        color: var(--ios-label);
    }

    .f144-header-text p {
        font-size: 14px;
        color: var(--ios-label2);
        margin: 0;
    }

    .f144-header-date {
        margin-left: auto;
        font-size: 13px;
        color: var(--ios-label3);
        white-space: nowrap;
    }

    /* ── List ── */
    .card-grid {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    /* ── Add row ── */
    .formulario-add {
        background: var(--ios-blue);
        border-radius: var(--r-lg);
        padding: 16px 22px;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 16px;
        cursor: pointer;
        border: none;
        color: white;
        box-shadow: 0 4px 16px rgba(0,122,255,.32);
        transition: transform .2s, box-shadow .2s;
    }

    .formulario-add:hover {
        background: #0066E0;
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(0,122,255,.42);
        color: white;
    }

    .add-icon { font-size: 26px; flex-shrink: 0; }

    .formulario-add h3 {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
        color: white;
    }

    .formulario-add p {
        font-size: 13px;
        opacity: .75;
        margin: 0;
        color: white;
    }

    /* ── Formulario row ── */
    .formulario-card {
        background: var(--ios-surface);
        border-radius: var(--r-lg);
        padding: 14px 20px;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 18px;
        box-shadow: 0 1px 4px rgba(0,0,0,.07), 0 0 0 .5px var(--ios-sep);
        border-left: 4px solid var(--ios-sep);
        transition: transform .15s, box-shadow .15s;
    }

    .formulario-card:hover {
        transform: translateX(2px);
        box-shadow: 0 4px 14px rgba(0,0,0,.1), 0 0 0 .5px var(--ios-sep);
    }

    .formulario-card.disponible    { border-left-color: var(--ios-green); }
    .formulario-card.proximamente  { border-left-color: var(--ios-orange); }
    .formulario-card.no-disponible { border-left-color: var(--ios-red); opacity: .85; }

    /* ── Row main (title + description) ── */
    .formulario-main {
        flex: 1 1 auto;
        min-width: 0;
    }

    /* ── Card title ── */
    .formulario-titulo {
        font-size: 15px;
        font-weight: 600;
        color: var(--ios-label);
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 2px;
        padding-right: 0;
        line-height: 1.35;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Card description ── */
    .formulario-descripcion {
        font-size: 13px;
        color: var(--ios-label2);
        line-height: 1.4;
        flex-grow: 0;
        margin-bottom: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Row meta (estado + fechas) ── */
    .formulario-meta {
        flex: 0 0 auto;
        min-width: 220px;
        max-width: 260px;
    }

    /* ── Time info ── */
    .tiempo-info {
        background: none;
        border-radius: 0;
        padding: 0;
        margin: 0;
        font-size: 12px;
    }

    /* ── Status pill ── */
    .badge-estado {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 7px;
    }

    .badge-disponible    { background: rgba(52,199,89,.15);  color: #1A7A35; }
    .badge-no-disponible { background: rgba(255,59,48,.12);  color: #C0392B; }
    .badge-proximamente  { background: rgba(255,149,0,.15);  color: #8B5E00; }

    /* ── Date row ── */
    .fecha-rango { display: flex; flex-direction: column; gap: 2px; margin-top: 3px; }

    .fecha-item {
        display: flex;
        align-items: center;
        gap: 6px;
        color: var(--ios-label2);
        font-size: 12px;
    }

    .fecha-item i { width: 15px; color: var(--ios-blue); font-size: 11px; }

    /* ── Card footer date ── */
    .formulario-fecha {
        font-size: 12px;
        color: var(--ios-label3);
        margin: 3px 0 0;
        padding-top: 0;
        border-top: none;
    }

    /* ── Action buttons ── */
    .btn-actions {
        margin-top: 0;
        flex: 0 0 auto;
        display: flex;
        gap: 7px;
        justify-content: flex-end;
    }

    .btn-sm {
        padding: 7px 13px;
        font-size: 13px;
        font-weight: 600;
        border-radius: var(--r-sm);
        border: none;
        cursor: pointer;
        transition: opacity .15s, transform .15s;
    }

    .btn-sm:hover:not(:disabled) { opacity: .85; transform: translateY(-1px); }

    .btn-success          { background: var(--ios-green);  color: #fff; }
    .btn-warning          { background: var(--ios-orange); color: #fff; }
    .btn-danger           { background: var(--ios-red);    color: #fff; }
    .btn-success:disabled { background: rgba(52,199,89,.35); color: rgba(255,255,255,.7); cursor: not-allowed; }
    .btn-info             { background: var(--ios-blue);    color: #fff; }

    /* Bootstrap badges override */
    .badge { padding: 4px 9px; font-weight: 600; border-radius: 20px; font-size: 11px; }
    .badge.bg-success   { background: var(--ios-green)  !important; }
    .badge.bg-warning   { background: var(--ios-orange) !important; color: #fff !important; }
    .badge.bg-danger    { background: var(--ios-red)    !important; }
    .badge.bg-secondary { background: var(--ios-label2) !important; }

    /* ── Empty state ── */
    .empty-state {
        grid-column: 1 / -1;
        background: var(--ios-surface);
        border-radius: var(--r-lg);
        padding: 48px 24px;
        text-align: center;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }

    .empty-state i     { font-size: 40px; color: var(--ios-label3); margin-bottom: 14px; }
    .empty-state h5    { font-size: 17px; font-weight: 600; margin-bottom: 6px; }
    .empty-state p     { font-size: 14px; color: var(--ios-label2); margin: 0; }

    /* ── Modal ── */
    .modal-content {
        border-radius: var(--r-xl);
        border: none;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,.22);
    }

    .modal-header {
        background: var(--ios-surface);
        border-bottom: .5px solid var(--ios-sep);
        padding: 18px 20px;
    }

    .modal-title { font-size: 17px; font-weight: 600; color: var(--ios-label); }

    .modal-body {
        background: var(--ios-bg);
        padding: 20px;
    }

    .modal-footer {
        background: var(--ios-surface);
        border-top: .5px solid var(--ios-sep);
        padding: 14px 20px;
    }

    /* ── Form ── */
    .form-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--ios-label2);
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 5px;
    }

    .form-control, .form-select {
        border-radius: var(--r-sm);
        border: .5px solid rgba(60,60,67,.2);
        padding: 11px 14px;
        font-size: 15px;
        background: var(--ios-surface);
        color: var(--ios-label);
        transition: border-color .15s, box-shadow .15s;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--ios-blue);
        box-shadow: 0 0 0 3px rgba(0,122,255,.15);
        outline: none;
        background: var(--ios-surface);
    }

    /* ── Tiempo opciones (iOS list) ── */
    .tiempo-opciones {
        background: var(--ios-surface);
        border-radius: var(--r);
        border: .5px solid var(--ios-sep);
        overflow: hidden;
        padding: 0;
    }

    .tiempo-opciones > div {
        padding: 13px 16px;
        border-bottom: .5px solid var(--ios-sep);
        display: flex;
        align-items: flex-start;
        gap: 10px;
        cursor: pointer;
    }

    .tiempo-opciones > div:last-child { border-bottom: none; }

    .tiempo-opciones input[type="radio"] { margin-top: 3px; accent-color: var(--ios-blue); }

    .tiempo-opciones label { cursor: pointer; line-height: 1.4; }

    .tiempo-opt-sub { font-size: 12px; color: var(--ios-label2); display: block; margin-top: 2px; font-weight: 400; }

    /* ── Rango fechas ── */
    .rango-fechas {
        background: var(--ios-surface);
        border-radius: var(--r);
        border: .5px solid var(--ios-sep);
        padding: 16px;
        margin-top: 12px;
    }

    /* ── Alert ── */
    .alert-info {
        background: rgba(0,122,255,.08);
        border: none;
        border-radius: var(--r-sm);
        color: var(--ios-blue);
        font-size: 13px;
    }

    /* ── Modal buttons ── */
    .btn-primary {
        background: var(--ios-blue);
        border: none;
        padding: 11px 22px;
        border-radius: var(--r-sm);
        font-weight: 600;
        font-size: 15px;
        color: white;
        transition: background .15s;
    }

    .btn-primary:hover { background: #0066E0; color: white; transform: none; box-shadow: none; }

    .btn-secondary {
        background: var(--ios-fill);
        border: none;
        padding: 11px 22px;
        border-radius: var(--r-sm);
        font-weight: 600;
        font-size: 15px;
        color: var(--ios-label);
    }

    .btn-secondary:hover { background: rgba(120,120,128,.2); color: var(--ios-label); }

    /* ── Fade in ── */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .formulario-card, .formulario-add { animation: fadeInUp .25s ease-out; }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .f144-wrap      { padding: 0 12px 40px; }
        .f144-header    { flex-wrap: wrap; gap: 12px; }
        .f144-header-date { margin-left: 0; }
        .formulario-titulo { padding-right: 0; }
        .formulario-card, .formulario-add {
            flex-direction: column;
            align-items: flex-start;
        }
        .formulario-main, .formulario-meta { max-width: 100%; width: 100%; }
        .formulario-titulo, .formulario-descripcion { white-space: normal; }
        .btn-actions { width: 100%; justify-content: flex-start; flex-wrap: wrap; }
    }

    /* ══════════════ Listado (diseño claro estilo Apple) ══════════════ */
    .fl { --fl-text: #1D1D1F; --fl-text2: #3A3A3C; --fl-sub: #6E6E73; --fl-fill: #F2F2F7; --fl-seg: #E3E3E8; --fl-sep: #F0F0F3;
          --fl-shadow: 0 1px 2px rgba(0,0,0,.04), 0 6px 20px rgba(0,0,0,.04);
          display: flex; flex-direction: column; gap: 20px; max-width: 1100px; color: var(--fl-text); }
    .fl-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; }
    .fl-eyebrow { font-size: 13px; font-weight: 600; color: var(--fl-sub); text-transform: uppercase; letter-spacing: .04em; }
    .fl-head h1 { margin: 4px 0 0; font-size: 32px; font-weight: 700; letter-spacing: -.02em; line-height: 1.15; }
    .fl-btn { display: inline-flex; align-items: center; gap: 8px; border: 0; cursor: pointer; min-height: 44px; padding: 0 20px;
              border-radius: 12px; font-size: 15px; font-weight: 600; text-decoration: none; white-space: nowrap; }
    .fl-btn-sm { min-height: 38px; padding: 0 15px; border-radius: 10px; font-size: 14px; }
    .fl-btn-primary { background: #007AFF; color: #fff; }
    .fl-btn-primary:hover { background: #0068D9; color: #fff; }
    .fl-btn-tinted { background: rgba(0,122,255,.12); color: #0066CC; }
    .fl-btn-tinted:hover { background: rgba(0,122,255,.18); color: #0066CC; }
    .fl-btn:focus-visible, .fl-more:focus-visible, .fl-seg button:focus-visible, .fl-search input:focus-visible { outline: 3px solid rgba(0,122,255,.45); outline-offset: 2px; }

    .fl-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .fl-stat { background: #fff; border-radius: 16px; padding: 14px 18px; box-shadow: var(--fl-shadow); display: flex; align-items: center; gap: 12px; }
    .fl-stat b { display: block; font-size: 24px; font-weight: 700; line-height: 1.1; }
    .fl-stat small { font-size: 13px; color: var(--fl-sub); }
    .fl-num { font-variant-numeric: tabular-nums; }
    .fl-dot { width: 10px; height: 10px; border-radius: 50%; flex: none; }
    .fl-dot-abierto { background: #007AFF; } .fl-dot-libre { background: #34C759; }
    .fl-dot-programado { background: #5856D6; } .fl-dot-finalizado { background: #AEAEB2; }

    .fl-tools { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
    .fl-seg { display: inline-flex; flex-wrap: wrap; padding: 2px; border-radius: 9px; background: var(--fl-seg); gap: 2px; }
    .fl-seg button { border: 0; background: transparent; cursor: pointer; padding: 6px 14px; border-radius: 7px; font-size: 13px; font-weight: 600; color: var(--fl-text2); }
    .fl-seg button.on { background: #fff; color: var(--fl-text); box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 1px rgba(0,0,0,.04); }
    .fl-search { display: flex; align-items: center; gap: 8px; flex: 0 1 300px; min-width: 200px; margin: 0; background: var(--fl-seg); border-radius: 10px; padding: 0 12px; color: var(--fl-sub); }
    .fl-search input { flex: 1; min-width: 0; border: 0; background: transparent; padding: 9px 0; font-size: 14px; color: var(--fl-text); outline: none; }

    .fl-list { background: #fff; border-radius: 18px; box-shadow: var(--fl-shadow); }
    .fl-item { display: flex; flex-wrap: wrap; align-items: center; gap: 16px 24px; padding: 20px 24px; border-bottom: 1px solid var(--fl-sep); }
    .fl-item:last-of-type { border-bottom: 0; }
    .fl-main { flex: 1 1 300px; min-width: 0; display: flex; gap: 14px; align-items: flex-start; }
    .fl-icon { flex: none; width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; font-size: 18px; }
    .fl-icon-abierto { background: rgba(0,122,255,.12); color: #0066CC; }
    .fl-icon-libre { background: rgba(52,199,89,.14); color: #248A3D; }
    .fl-icon-programado { background: rgba(88,86,214,.14); color: #4A48C8; }
    .fl-icon-finalizado, .fl-icon-inactivo { background: var(--fl-fill); color: #8E8E93; }
    .fl-text { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
    .fl-title-row { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .fl-title-row h2 { margin: 0; font-size: 17px; font-weight: 600; letter-spacing: -.01em; }
    .fl-desc { font-size: 14px; color: var(--fl-text2); }
    .fl-desc.vacia { color: #8E8E93; }
    .fl-meta { font-size: 12px; color: var(--fl-sub); }
    .fl-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 2px 9px; border-radius: 999px; }
    .fl-pill i { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .fl-pill-abierto { background: rgba(0,122,255,.12); color: #0066CC; }
    .fl-pill-libre { background: rgba(52,199,89,.14); color: #248A3D; }
    .fl-pill-programado { background: rgba(88,86,214,.14); color: #4A48C8; }
    .fl-pill-finalizado, .fl-pill-inactivo { background: var(--fl-fill); color: var(--fl-sub); }

    .fl-time { flex: 1 1 240px; min-width: 0; display: flex; flex-direction: column; gap: 6px; }
    .fl-time-top { display: flex; justify-content: space-between; gap: 8px; font-size: 13px; color: var(--fl-text2); }
    .fl-time-top b { font-weight: 600; white-space: nowrap; }
    .fl-ink-abierto { color: #0066CC; } .fl-ink-libre { color: #248A3D; } .fl-ink-programado { color: #4A48C8; }
    .fl-ink-finalizado, .fl-ink-inactivo { color: var(--fl-sub); }
    .fl-bar { height: 6px; border-radius: 999px; background: #EEEEF2; overflow: hidden; }
    .fl-bar span { display: block; height: 100%; border-radius: 999px; }
    .fl-fill-abierto { background: #007AFF; } .fl-fill-programado { background: #5856D6; } .fl-fill-finalizado { background: #AEAEB2; }
    .fl-bar-libre { background: repeating-linear-gradient(90deg, rgba(52,199,89,.35) 0 8px, transparent 8px 14px); }

    .fl-actions { flex: 0 0 auto; display: flex; align-items: center; gap: 8px; }
    .fl-more { border: 0; cursor: pointer; width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; background: var(--fl-fill); color: var(--fl-text2); }
    .fl-more:hover, .fl-more[aria-expanded="true"] { background: var(--fl-seg); }
    .fl-menu { min-width: 220px; padding: 6px; border: 0; border-radius: 14px; box-shadow: 0 0 0 1px rgba(0,0,0,.06), 0 12px 32px rgba(0,0,0,.14); }
    .fl-menu .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 8px; font-size: 14px; color: var(--fl-text); }
    .fl-menu .dropdown-item i { width: 16px; color: var(--fl-sub); }
    .fl-menu .fl-danger, .fl-menu .fl-danger i { color: #D70015; }
    .fl-menu .dropdown-item:active { background: rgba(0,122,255,.12); }

    .fl-empty { padding: 44px 24px; text-align: center; color: var(--fl-sub); }
    .fl-empty i { font-size: 32px; color: #AEAEB2; margin-bottom: 10px; }
    .fl-empty h5 { font-size: 17px; font-weight: 600; color: var(--fl-text); }
    .fl-empty p { margin: 0; font-size: 14px; }

    @media (max-width: 720px) {
        .fl-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .fl-head h1 { font-size: 28px; }
    }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<?php
// ── Estado de cada formulario (abierto, tiempo libre, programado, finalizado, inactivo) ──
$fl_items  = [];
$fl_counts = ['abierto' => 0, 'libre' => 0, 'programado' => 0, 'finalizado' => 0, 'inactivo' => 0];
$fl_ahora  = time();
foreach (($formularios ?? []) as $formulario) {
    $it = ['f' => $formulario, 'progreso' => null, 'rango' => '', 'resta' => ''];
    if ($formulario['estado'] != 1) {
        $it['estado'] = 'inactivo';
        $it['resta']  = 'No disponible';
    } elseif ($formulario['tipo_tiempo'] == 'libre') {
        $it['estado'] = 'libre';
        $it['rango']  = 'Siempre disponible';
        $it['resta']  = 'Sin cierre';
    } else {
        $ini = strtotime($formulario['fecha_inicio']);
        $fin = strtotime($formulario['fecha_fin']);
        $it['rango'] = date('d/m/Y', $ini) . ' – ' . date('d/m/Y H:i', $fin);
        if ($fl_ahora < $ini) {
            $it['estado']   = 'programado';
            $dias           = (int)ceil(($ini - $fl_ahora) / 86400);
            $it['resta']    = 'Abre en ' . $dias . ($dias === 1 ? ' día' : ' días');
            $it['progreso'] = 0;
        } elseif ($fl_ahora > $fin) {
            $it['estado']   = 'finalizado';
            $it['resta']    = 'Cerró el ' . date('d/m/Y', $fin);
            $it['progreso'] = 100;
        } else {
            $it['estado']   = 'abierto';
            $dias           = (int)ceil(($fin - $fl_ahora) / 86400);
            $it['resta']    = $dias <= 1 ? 'Cierra hoy' : 'Quedan ' . $dias . ' días';
            $it['progreso'] = $fin > $ini ? (int)round(($fl_ahora - $ini) / ($fin - $ini) * 100) : 100;
        }
    }
    $fl_counts[$it['estado']]++;
    $fl_items[] = $it;
}
$fl_etiquetas = [
    'abierto'    => 'Abierto',
    'libre'      => 'Tiempo libre',
    'programado' => 'Programado',
    'finalizado' => 'Finalizado',
    'inactivo'   => 'Inactivo',
];
?>
<div class="f144-wrap fl">

    <!-- Encabezado -->
    <header class="fl-head">
        <div>
            <div class="fl-eyebrow">Formularios con control de tiempo</div>
            <h1>FOR-DE-144</h1>
        </div>
        <?php if ($perms_f144['crear']): ?>
        <button type="button" class="fl-btn fl-btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            <i class="fas fa-plus"></i> Nuevo formulario
        </button>
        <?php endif; ?>
    </header>

    <?php if (!empty($fl_items)): ?>
    <!-- Resumen -->
    <section class="fl-stats" aria-label="Resumen">
        <?php foreach (['abierto' => 'Abiertos', 'libre' => 'Tiempo libre', 'programado' => 'Programados', 'finalizado' => 'Finalizados'] as $k => $lbl): ?>
        <div class="fl-stat">
            <span class="fl-dot fl-dot-<?php echo $k; ?>"></span>
            <span><b class="fl-num"><?php echo $fl_counts[$k]; ?></b><small><?php echo $lbl; ?></small></span>
        </div>
        <?php endforeach; ?>
    </section>

    <!-- Filtros -->
    <div class="fl-tools">
        <div class="fl-seg" role="group" aria-label="Filtrar por estado" id="flFiltros">
            <button type="button" class="on" data-filtro="todos" aria-pressed="true">Todos</button>
            <button type="button" data-filtro="abierto" aria-pressed="false">Abiertos</button>
            <button type="button" data-filtro="libre" aria-pressed="false">Tiempo libre</button>
            <button type="button" data-filtro="programado" aria-pressed="false">Programados</button>
            <button type="button" data-filtro="finalizado" aria-pressed="false">Finalizados</button>
            <?php if ($fl_counts['inactivo']): ?>
            <button type="button" data-filtro="inactivo" aria-pressed="false">Inactivos</button>
            <?php endif; ?>
        </div>
        <label class="fl-search" for="flBuscar">
            <i class="fas fa-search"></i>
            <input type="search" id="flBuscar" placeholder="Buscar formulario" aria-label="Buscar formulario">
        </label>
    </div>
    <?php endif; ?>

    <!-- Lista -->
    <section id="formulariosContainer" class="fl-list" aria-label="Formularios">
        <?php foreach ($fl_items as $it):
            $formulario = $it['f'];
            $est = $it['estado'];
            $puedeConstruir = in_array($est, ['abierto', 'libre'], true);
            $tieneMenu = $perms_f144['editar'] || $perms_f144['eliminar'];
        ?>
        <article class="fl-item" id="formulario-<?php echo $formulario['id']; ?>"
                 data-estado="<?php echo $est; ?>"
                 data-buscar="<?php echo htmlspecialchars(mb_strtolower(($formulario['titulo'] ?? '') . ' ' . ($formulario['descripcion'] ?? ''), 'UTF-8')); ?>">

            <div class="fl-main">
                <span class="fl-icon fl-icon-<?php echo $est; ?>"><i class="fas fa-file-alt"></i></span>
                <div class="fl-text">
                    <div class="fl-title-row">
                        <h2><?php echo htmlspecialchars($formulario['titulo']); ?></h2>
                        <span class="fl-pill fl-pill-<?php echo $est; ?>"><i></i><?php echo $fl_etiquetas[$est]; ?></span>
                    </div>
                    <div class="fl-desc <?php echo $formulario['descripcion'] ? '' : 'vacia'; ?>"><?php echo htmlspecialchars($formulario['descripcion'] ?: 'Sin descripción'); ?></div>
                    <div class="fl-meta">
                        <?php if (!empty($formulario['anio'])): ?>Vigencia <?php echo htmlspecialchars($formulario['anio']); ?> · <?php endif; ?>
                        creado el <?php echo date('d/m/Y H:i', strtotime($formulario['fecha_creacion'])); ?>
                    </div>
                </div>
            </div>

            <div class="fl-time">
                <div class="fl-time-top">
                    <span><?php echo htmlspecialchars($it['rango']); ?></span>
                    <b class="fl-ink-<?php echo $est; ?>"><?php echo htmlspecialchars($it['resta']); ?></b>
                </div>
                <?php if ($est === 'libre'): ?>
                <div class="fl-bar fl-bar-libre"></div>
                <?php elseif ($it['progreso'] !== null): ?>
                <div class="fl-bar"><span class="fl-fill-<?php echo $est; ?>" style="width:<?php echo max(0, min(100, $it['progreso'])); ?>%"></span></div>
                <?php endif; ?>
            </div>

            <div class="fl-actions">
                <?php if ($perms_f144['ver'] && $puedeConstruir): ?>
                <a class="fl-btn fl-btn-primary fl-btn-sm" href="<?php echo Config::getBasePath(); ?>/modulo144?id=<?php echo $formulario['id']; ?>">Construir</a>
                <?php endif; ?>
                <?php if ($perms_f144['informe']): ?>
                <a class="fl-btn fl-btn-tinted fl-btn-sm" target="_blank"
                   href="<?php echo Config::getBasePath(); ?>/FOR-DE-144?action=informePage&id=<?php echo $formulario['id']; ?>">Informe</a>
                <?php endif; ?>
                <?php if ($tieneMenu): ?>
                <div class="dropdown">
                    <button type="button" class="fl-more" data-bs-toggle="dropdown" aria-expanded="false"
                            aria-label="Más acciones para <?php echo htmlspecialchars($formulario['titulo']); ?>">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end fl-menu">
                        <?php if ($perms_f144['editar']): ?>
                        <li><button type="button" class="dropdown-item" onclick="editarFormulario(<?php echo $formulario['id']; ?>)"><i class="fas fa-pen"></i> Editar datos y fechas</button></li>
                        <?php endif; ?>
                        <?php if ($perms_f144['editar'] && $perms_f144['eliminar']): ?>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <?php if ($perms_f144['eliminar']): ?>
                        <li><button type="button" class="dropdown-item fl-danger" onclick="eliminarFormulario(<?php echo $formulario['id']; ?>)"><i class="fas fa-trash"></i> Eliminar formulario</button></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>

        <div class="fl-empty" id="flVacio" <?php echo empty($fl_items) ? '' : 'hidden'; ?>>
            <i class="fas fa-inbox"></i>
            <?php if (empty($fl_items)): ?>
            <h5>Sin formularios</h5>
            <p>Crea el primero con el botón “Nuevo formulario”.</p>
            <?php else: ?>
            <p>No hay formularios que coincidan con el filtro.</p>
            <?php endif; ?>
        </div>
    </section>
</div><!-- /f144-wrap -->

<script>
// Filtro por estado y búsqueda del listado
(function () {
    var seg = document.getElementById('flFiltros');
    var buscar = document.getElementById('flBuscar');
    if (!seg) return;
    var filtro = 'todos';
    function aplicar() {
        var q = (buscar.value || '').trim().toLowerCase();
        var visibles = 0;
        document.querySelectorAll('.fl-item').forEach(function (el) {
            var ok = (filtro === 'todos' || el.dataset.estado === filtro) && (!q || el.dataset.buscar.indexOf(q) !== -1);
            el.hidden = !ok;
            if (ok) visibles++;
        });
        document.getElementById('flVacio').hidden = visibles > 0;
    }
    seg.addEventListener('click', function (e) {
        var b = e.target.closest('button');
        if (!b) return;
        filtro = b.dataset.filtro;
        seg.querySelectorAll('button').forEach(function (x) {
            x.classList.toggle('on', x === b);
            x.setAttribute('aria-pressed', x === b ? 'true' : 'false');
        });
        aplicar();
    });
    buscar.addEventListener('input', aplicar);
})();
</script>


<!-- ══════════════ MODAL AGREGAR ══════════════ -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarLabel">
                    <i class="fas fa-plus-circle me-2" style="color:var(--ios-blue,#007AFF);"></i>Nuevo Formulario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAgregarFormulario">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required
                               placeholder="Ingresa un título descriptivo">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion"
                                  rows="3" placeholder="Describe el propósito (opcional)"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="anio" class="form-label">Año de Vigencia</label>
                        <select class="form-control" id="anio" name="anio" required>
                            <option value="">— Seleccione un año —</option>
                            <?php if (!empty($anios)): ?>
                                <?php foreach ($anios as $a): ?>
                                    <option value="<?php echo htmlspecialchars($a['anio']); ?>"
                                        <?php echo ($a['anio'] == date('Y')) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($a['anio']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No hay años disponibles</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Configuración de Tiempo</label>
                        <div class="tiempo-opciones">
                            <div>
                                <input type="radio" name="tipo_tiempo" id="tipo_libre" value="libre" checked>
                                <label for="tipo_libre">
                                    <strong><i class="fas fa-infinity me-1" style="color:var(--ios-green);"></i> Tiempo Libre</strong>
                                    <span class="tiempo-opt-sub">El formulario estará siempre disponible</span>
                                </label>
                            </div>
                            <div>
                                <input type="radio" name="tipo_tiempo" id="tipo_rango" value="rango">
                                <label for="tipo_rango">
                                    <strong><i class="fas fa-calendar-alt me-1" style="color:var(--ios-blue);"></i> Rango de Tiempo</strong>
                                    <span class="tiempo-opt-sub">Definir fecha y hora de inicio y fin</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="rangoFechasContainer" style="display:none;" class="rango-fechas">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <small><i class="fas fa-info-circle me-1"></i>
                            Los formularios con rango de tiempo solo estarán disponibles dentro del período configurado.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ══════════════ MODAL EDITAR ══════════════ -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarLabel">
                    <i class="fas fa-edit me-2" style="color:var(--ios-blue,#007AFF);"></i>Editar Formulario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <ul class="nav nav-tabs px-3 pt-2" id="editarFormularioTabs" role="tablist" style="border-bottom:.5px solid var(--ios-sep);">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabEditarGeneral" type="button" role="tab">General</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabEditarAvanzado" type="button" role="tab">Avanzado</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tabEditarGeneral" role="tabpanel">
                    <form id="formEditarFormulario">
                        <input type="hidden" id="formularioIdEditar" name="id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="tituloEditar" class="form-label">Título</label>
                                <input type="text" class="form-control" id="tituloEditar" name="titulo" required>
                            </div>

                            <div class="mb-3">
                                <label for="descripcionEditar" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcionEditar" name="descripcion" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="anioEditar" class="form-label">Año de Vigencia</label>
                                <select class="form-control" id="anioEditar" name="anio" required>
                                    <option value="">— Seleccione un año —</option>
                                    <?php if (!empty($anios)): ?>
                                        <?php foreach ($anios as $a): ?>
                                            <option value="<?php echo htmlspecialchars($a['anio']); ?>">
                                                <?php echo htmlspecialchars($a['anio']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No hay años disponibles</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Configuración de Tiempo</label>
                                <div class="tiempo-opciones">
                                    <div>
                                        <input type="radio" name="tipo_tiempo" id="tipo_libre_editar" value="libre">
                                        <label for="tipo_libre_editar">
                                            <strong><i class="fas fa-infinity me-1" style="color:var(--ios-green);"></i> Tiempo Libre</strong>
                                        </label>
                                    </div>
                                    <div>
                                        <input type="radio" name="tipo_tiempo" id="tipo_rango_editar" value="rango">
                                        <label for="tipo_rango_editar">
                                            <strong><i class="fas fa-calendar-alt me-1" style="color:var(--ios-blue);"></i> Rango de Tiempo</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="rangoFechasContainerEditar" style="display:none;" class="rango-fechas">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_inicio_editar" class="form-label">Fecha de Inicio</label>
                                        <input type="datetime-local" class="form-control" id="fecha_inicio_editar" name="fecha_inicio">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_fin_editar" class="form-label">Fecha de Fin</label>
                                        <input type="datetime-local" class="form-control" id="fecha_fin_editar" name="fecha_fin">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="estadoEditar" class="form-label">Estado</label>
                                <select class="form-control" id="estadoEditar" name="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Actualizar</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="tabEditarAvanzado" role="tabpanel">
                    <div class="modal-body">
                        <label class="form-label">Administradores</label>
                        <p style="font-size:13px;color:var(--ios-label2);margin-top:-4px;">
                            Los usuarios agregados aquí tendrán acceso de administrador sobre este formulario.
                        </p>

                        <div class="filtro-input-wrap" style="position:relative;">
                            <input type="text" class="form-control" id="buscarAdminInput" autocomplete="off"
                                   placeholder="Buscar usuario por nombre o correo...">
                            <div id="buscarAdminSugerencias"
                                 style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid var(--ios-sep);border-radius:var(--r-sm);box-shadow:0 6px 20px rgba(0,0,0,.12);z-index:20;max-height:220px;overflow-y:auto;"></div>
                        </div>

                        <div id="listaAdministradores" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════ MODAL INFORME ══════════════ -->
<div class="modal fade" id="modalInforme" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#007AFF,#5856D6);border-bottom:none;padding:20px 24px;">
                <div>
                    <h5 class="modal-title" style="color:#fff;font-size:18px;font-weight:700;letter-spacing:-.3px;margin:0 0 2px;">
                        <i class="fas fa-chart-bar me-2"></i><span id="infTitulo">Informe</span>
                    </h5>
                    <small style="color:rgba(255,255,255,.75);font-size:13px;">Cumplimiento · Líneas Estratégicas · Dependencias</small>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="background:#F2F2F7;padding:20px;">

                <!-- Loading state -->
                <div id="infLoading" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x" style="color:#007AFF;"></i>
                    <p class="mt-3" style="color:#6e6e73;font-size:14px;">Generando informe…</p>
                </div>

                <!-- Content (hidden until loaded) -->
                <div id="infContent" style="display:none;">

                    <!-- Global stats row -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div style="background:#fff;border-radius:16px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.07);text-align:center;">
                                <div style="font-size:13px;color:#6e6e73;font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Cumplimiento Global</div>
                                <div id="infPct" style="font-size:48px;font-weight:800;letter-spacing:-2px;line-height:1;color:#007AFF;">—</div>
                                <div style="font-size:13px;color:#aeaeb2;margin-top:4px;">promedio</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background:#fff;border-radius:16px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.07);text-align:center;">
                                <div style="font-size:13px;color:#6e6e73;font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Total Indicadores</div>
                                <div id="infTotal" style="font-size:48px;font-weight:800;letter-spacing:-2px;line-height:1;color:#1d1d1f;">—</div>
                                <div style="font-size:13px;color:#aeaeb2;margin-top:4px;">registros</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background:#fff;border-radius:16px;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.07);text-align:center;">
                                <div style="font-size:13px;color:#6e6e73;font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Líneas Estratégicas</div>
                                <div id="infLineas" style="font-size:48px;font-weight:800;letter-spacing:-2px;line-height:1;color:#AF52DE;">—</div>
                                <div style="font-size:13px;color:#aeaeb2;margin-top:4px;">con registros</div>
                            </div>
                        </div>
                    </div>

                    <!-- Líneas estratégicas grid -->
                    <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:20px;">
                        <div style="font-size:15px;font-weight:700;color:#1d1d1f;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-layer-group" style="color:#007AFF;"></i>Líneas Estratégicas
                        </div>
                        <div id="infLineasGrid" class="row g-3"></div>
                    </div>

                    <!-- Chart -->
                    <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:20px;">
                        <div style="font-size:15px;font-weight:700;color:#1d1d1f;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-chart-bar" style="color:#34C759;"></i>Cumplimiento por Línea
                        </div>
                        <div style="position:relative;height:220px;">
                            <canvas id="infChart"></canvas>
                        </div>
                    </div>

                    <!-- Dependencias table -->
                    <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
                        <div style="font-size:15px;font-weight:700;color:#1d1d1f;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-university" style="color:#FF9500;"></i>Estadísticas por Dependencia
                        </div>
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:separate;border-spacing:0;font-size:14px;">
                                <thead>
                                    <tr style="background:#F2F2F7;">
                                        <th style="padding:10px 14px;font-weight:600;color:#6e6e73;font-size:12px;text-transform:uppercase;letter-spacing:.4px;border-radius:8px 0 0 8px;">Dependencia</th>
                                        <th style="padding:10px 14px;font-weight:600;color:#6e6e73;font-size:12px;text-transform:uppercase;letter-spacing:.4px;text-align:center;">Total Indicadores</th>
                                        <th style="padding:10px 14px;font-weight:600;color:#6e6e73;font-size:12px;text-transform:uppercase;letter-spacing:.4px;text-align:center;">Indicadores ≥80%</th>
                                        <th style="padding:10px 14px;font-weight:600;color:#6e6e73;font-size:12px;text-transform:uppercase;letter-spacing:.4px;text-align:center;border-radius:0 8px 8px 0;">Cumplimiento</th>
                                    </tr>
                                </thead>
                                <tbody id="infDepBody"></tbody>
                            </table>
                        </div>
                    </div>

                </div><!-- /infContent -->
            </div>
            <div class="modal-footer" style="background:#fff;border-top:.5px solid rgba(60,60,67,.1);padding:14px 20px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- ══════════════════════════════════════════ -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    const basePath = '<?php echo Config::getBasePath(); ?>';

    function mostrarRangoFechas(mostrar, sufijo) {
        sufijo = sufijo || '';
        const c  = document.getElementById('rangoFechasContainer' + sufijo);
        const fi = document.getElementById('fecha_inicio' + (sufijo ? '_' + sufijo.replace('Editar','editar') : ''));
        const ff = document.getElementById('fecha_fin'    + (sufijo ? '_' + sufijo.replace('Editar','editar') : ''));
        if (!c) return;
        c.style.display   = mostrar ? 'block' : 'none';
        if (fi) fi.required = mostrar;
        if (ff) ff.required = mostrar;
    }

    $(document).ready(function () {

        /* Radio buttons — modal agregar */
        $('input[name="tipo_tiempo"]:not(#modalEditar input)').on('change', function () {
            mostrarRangoFechas($(this).val() === 'rango', '');
        });

        /* Radio buttons — modal editar */
        $('#modalEditar input[name="tipo_tiempo"]').on('change', function () {
            mostrarRangoFechas($(this).val() === 'rango', 'Editar');
        });

        /* ── Agregar ── */
        $('#formAgregarFormulario').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            const orig = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...').prop('disabled', true);

            $.ajax({
                url: basePath + '/FOR-DE-144?action=crear',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (r) {
                    btn.html(orig).prop('disabled', false);
                    if (r.success) {
                        $('#modalAgregar').modal('hide');
                        $('#formAgregarFormulario')[0].reset();
                        mostrarRangoFechas(false, '');
                        Swal.fire({ icon: 'success', title: '¡Guardado!', text: r.message, timer: 1400, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: r.message });
                    }
                },
                error: function () {
                    btn.html(orig).prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
                }
            });
        });

        /* ── Editar ── */
        $('#formEditarFormulario').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            const orig = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...').prop('disabled', true);

            $.ajax({
                url: basePath + '/FOR-DE-144?action=editar',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (r) {
                    btn.html(orig).prop('disabled', false);
                    if (r.success) {
                        $('#modalEditar').modal('hide');
                        Swal.fire({ icon: 'success', title: '¡Actualizado!', text: r.message, timer: 1400, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: r.message });
                    }
                },
                error: function () {
                    btn.html(orig).prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
                }
            });
        });
    });

    function eliminarFormulario(id) {
        Swal.fire({
            title: '¿Eliminar formulario?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#FF3B30',
            cancelButtonColor:  '#8E8E93',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText:  'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: basePath + '/FOR-DE-144?action=eliminar',
                type: 'POST',
                data: { id },
                dataType: 'json',
                success: function (r) {
                    if (r.success) {
                        Swal.fire({ icon: 'success', title: 'Eliminado', text: r.message, timer: 1400, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: r.message });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
                }
            });
        });
    }

    var infChart = null;

    function verInforme(id, titulo) {
        document.getElementById('infTitulo').textContent = titulo;
        document.getElementById('infLoading').style.display = '';
        document.getElementById('infContent').style.display = 'none';

        var modal = new bootstrap.Modal(document.getElementById('modalInforme'));
        modal.show();

        $.ajax({
            url: basePath + '/FOR-DE-144?action=informe&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
                if (!r.success) {
                    document.getElementById('infLoading').innerHTML =
                        '<i class="fas fa-exclamation-circle fa-2x" style="color:#FF3B30;"></i>'
                        + '<p class="mt-3" style="color:#6e6e73;">No se pudo cargar el informe.</p>';
                    return;
                }
                renderInforme(r.data);
            },
            error: function() {
                document.getElementById('infLoading').innerHTML =
                    '<i class="fas fa-wifi fa-2x" style="color:#FF9500;"></i>'
                    + '<p class="mt-3" style="color:#6e6e73;">Error de conexión.</p>';
            }
        });
    }

    function badgeColor(pct) {
        if (pct >= 80) return { bg:'rgba(52,199,89,.15)', color:'#1A7A35' };
        if (pct >= 60) return { bg:'rgba(255,149,0,.15)',  color:'#7A4500' };
        return              { bg:'rgba(255,59,48,.12)',  color:'#C0392B' };
    }

    function renderInforme(data) {
        var g      = data.global     || {};
        var lineas = data.lineas     || [];
        var deps   = data.dependencias || [];

        var pct    = parseFloat(g.cumplimiento_global || 0);
        var total  = parseInt(g.total || 0);

        // Global numbers
        document.getElementById('infPct').textContent   = pct.toFixed(1) + '%';
        document.getElementById('infTotal').textContent = total;
        document.getElementById('infLineas').textContent = lineas.length;

        // Color of global percentage
        var bc = badgeColor(pct);
        document.getElementById('infPct').style.color = bc.color;

        // Líneas grid
        var grid = document.getElementById('infLineasGrid');
        grid.innerHTML = '';
        if (lineas.length === 0) {
            grid.innerHTML = '<div class="col-12"><p style="color:#aeaeb2;text-align:center;font-size:14px;">Sin datos de líneas estratégicas.</p></div>';
        }
        lineas.forEach(function(l) {
            var p = parseFloat(l.cumplimiento || 0);
            var c = badgeColor(p);
            var barColor = p >= 80 ? '#34C759' : (p >= 60 ? '#FF9500' : '#FF3B30');
            grid.innerHTML +=
                '<div class="col-md-6 col-lg-4">'
                + '<div style="background:#F2F2F7;border-radius:12px;padding:14px 16px;">'
                + '<div style="font-size:11px;font-weight:700;color:#007AFF;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">' + esc(l.codigo) + '</div>'
                + '<div style="font-size:13px;font-weight:600;color:#1d1d1f;margin-bottom:10px;line-height:1.3;">' + esc(l.linea) + '</div>'
                + '<div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">'
                + '<div style="flex:1;background:rgba(0,0,0,.06);border-radius:6px;height:8px;overflow:hidden;">'
                + '<div style="width:' + Math.min(p,100) + '%;height:100%;background:' + barColor + ';border-radius:6px;transition:width .6s;"></div>'
                + '</div>'
                + '<span style="font-size:13px;font-weight:700;color:' + c.color + ';white-space:nowrap;">' + p.toFixed(1) + '%</span>'
                + '</div>'
                + '<div style="font-size:11px;color:#aeaeb2;">' + l.total + ' indicador' + (l.total != 1 ? 'es' : '') + '</div>'
                + '</div></div>';
        });

        // Chart
        if (infChart) { infChart.destroy(); infChart = null; }
        if (lineas.length > 0) {
            var ctx = document.getElementById('infChart').getContext('2d');
            var labels  = lineas.map(function(l){ return l.codigo !== '—' ? l.codigo : l.linea.substring(0,20); });
            var valores = lineas.map(function(l){ return parseFloat(l.cumplimiento || 0).toFixed(1); });
            var colors  = lineas.map(function(l){
                var p = parseFloat(l.cumplimiento || 0);
                return p >= 80 ? 'rgba(52,199,89,.85)' : (p >= 60 ? 'rgba(255,149,0,.85)' : 'rgba(255,59,48,.85)');
            });
            infChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cumplimiento (%)',
                        data: valores,
                        backgroundColor: colors,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function(ctx){ return ctx.parsed.y + '%'; } } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true, max: 100,
                            ticks: { callback: function(v){ return v + '%'; }, font: { size: 11 } },
                            grid: { color: 'rgba(0,0,0,.05)' }
                        },
                        x: {
                            ticks: { font: { size: 11 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // Dependencias table
        var tbody = document.getElementById('infDepBody');
        tbody.innerHTML = '';
        if (deps.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="padding:20px;text-align:center;color:#aeaeb2;font-size:14px;">Sin datos de dependencias.</td></tr>';
        }
        deps.forEach(function(d, i) {
            var p  = parseFloat(d.cumplimiento || 0);
            var bc = badgeColor(p);
            var bg = i % 2 === 0 ? '#fff' : '#fafafa';
            tbody.innerHTML +=
                '<tr style="background:' + bg + ';">'
                + '<td style="padding:12px 14px;font-weight:500;color:#1d1d1f;">' + esc(d.dependencia) + '</td>'
                + '<td style="padding:12px 14px;text-align:center;color:#3a3a3c;">' + d.total_indicadores + '</td>'
                + '<td style="padding:12px 14px;text-align:center;"><span style="background:rgba(52,199,89,.15);color:#1A7A35;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;">' + (d.indicadores_alto || 0) + '</span></td>'
                + '<td style="padding:12px 14px;text-align:center;"><span style="background:' + bc.bg + ';color:' + bc.color + ';padding:3px 12px;border-radius:20px;font-size:13px;font-weight:700;">' + p.toFixed(1) + '%</span></td>'
                + '</tr>';
        });

        document.getElementById('infLoading').style.display = 'none';
        document.getElementById('infContent').style.display = '';
    }

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Destroy chart when modal closes to avoid canvas reuse issues
    document.getElementById('modalInforme').addEventListener('hidden.bs.modal', function() {
        if (infChart) { infChart.destroy(); infChart = null; }
    });

    function editarFormulario(id) {
        Swal.fire({ title: 'Cargando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: basePath + '/FOR-DE-144?action=getFormulario&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function (r) {
                Swal.close();
                if (r.success && r.formulario) {
                    const f = r.formulario;
                    $('#formularioIdEditar').val(f.id);
                    $('#tituloEditar').val(f.titulo);
                    $('#descripcionEditar').val(f.descripcion);
                    $('#estadoEditar').val(f.estado);
                    $('#anioEditar').val(f.anio || '').trigger('change');

                    if (f.tipo_tiempo === 'libre') {
                        $('#tipo_libre_editar').prop('checked', true);
                        mostrarRangoFechas(false, 'Editar');
                    } else {
                        $('#tipo_rango_editar').prop('checked', true);
                        mostrarRangoFechas(true, 'Editar');
                        $('#fecha_inicio_editar').val(f.fecha_inicio ? f.fecha_inicio.replace(' ', 'T') : '');
                        $('#fecha_fin_editar').val(f.fecha_fin     ? f.fecha_fin.replace(' ', 'T')     : '');
                    }

                    cargarAdministradores(f.id);
                    $('#modalEditar').modal('show');
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: r.message || 'Error al cargar el formulario' });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
            }
        });
    }

    /* ══════════════ ADMINISTRADORES (pestaña Avanzado) ══════════════ */

    function escAdmin(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function cargarAdministradores(formularioId) {
        $('#listaAdministradores').html('<p style="font-size:13px;color:var(--ios-label3);">Cargando...</p>');
        $.ajax({
            url: basePath + '/FOR-DE-144?action=getAdministradores&formulario_id=' + formularioId,
            type: 'GET',
            dataType: 'json',
            success: function (r) {
                renderAdministradores(r.success ? (r.administradores || []) : []);
            },
            error: function () {
                $('#listaAdministradores').html('<p style="font-size:13px;color:var(--ios-red);">No se pudo cargar la lista.</p>');
            }
        });
    }

    function renderAdministradores(lista) {
        const cont = $('#listaAdministradores');
        if (!lista.length) {
            cont.html('<p style="font-size:13px;color:var(--ios-label3);">Sin administradores adicionales todavía.</p>');
            return;
        }
        let html = '';
        lista.forEach(function (a) {
            html += '<div style="display:flex;align-items:center;justify-content:space-between;background:var(--ios-fill2);border-radius:var(--r-sm);padding:9px 12px;">'
                + '<div><div style="font-size:14px;font-weight:600;color:var(--ios-label);">' + escAdmin(a.nombre) + '</div>'
                + '<div style="font-size:12px;color:var(--ios-label2);">' + escAdmin(a.email) + '</div></div>'
                + '<button type="button" class="btn btn-sm btn-danger" onclick="quitarAdministrador(' + a.id + ', ' + $('#formularioIdEditar').val() + ')">'
                + '<i class="fas fa-trash"></i></button>'
                + '</div>';
        });
        cont.html(html);
    }

    let _buscarAdminTimeout = null;
    $(document).on('input', '#buscarAdminInput', function () {
        const termino = $(this).val().trim();
        clearTimeout(_buscarAdminTimeout);
        if (termino.length < 2) {
            $('#buscarAdminSugerencias').hide().empty();
            return;
        }
        _buscarAdminTimeout = setTimeout(function () {
            $.ajax({
                url: basePath + '/FOR-DE-144?action=buscarUsuarios&q=' + encodeURIComponent(termino),
                type: 'GET',
                dataType: 'json',
                success: function (r) {
                    const sug = $('#buscarAdminSugerencias');
                    const usuarios = (r.success ? r.usuarios : []) || [];
                    if (!usuarios.length) {
                        sug.html('<div style="padding:10px 14px;font-size:13px;color:var(--ios-label3);">Sin resultados</div>').show();
                        return;
                    }
                    let html = '';
                    usuarios.forEach(function (u) {
                        html += '<div class="sug-admin-item" data-id="' + u.id + '" data-nombre="' + escAdmin(u.nombre) + '"'
                            + ' style="padding:9px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid var(--ios-sep);">'
                            + '<strong>' + escAdmin(u.nombre) + '</strong><br><span style="color:var(--ios-label2);">' + escAdmin(u.email) + '</span>'
                            + '</div>';
                    });
                    sug.html(html).show();
                },
                error: function () { $('#buscarAdminSugerencias').hide().empty(); }
            });
        }, 300);
    });

    $(document).on('click', '.sug-admin-item', function () {
        const usuarioId = $(this).data('id');
        const formularioId = $('#formularioIdEditar').val();
        $.ajax({
            url: basePath + '/FOR-DE-144?action=agregarAdministrador',
            type: 'POST',
            data: { formulario_id: formularioId, usuario_id: usuarioId },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    $('#buscarAdminInput').val('');
                    $('#buscarAdminSugerencias').hide().empty();
                    cargarAdministradores(formularioId);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: r.message || 'No se pudo agregar' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
            }
        });
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#buscarAdminInput, #buscarAdminSugerencias').length) {
            $('#buscarAdminSugerencias').hide();
        }
    });

    function quitarAdministrador(id, formularioId) {
        $.ajax({
            url: basePath + '/FOR-DE-144?action=eliminarAdministrador',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (r) {
                if (r.success) {
                    cargarAdministradores(formularioId);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: r.message || 'No se pudo quitar' });
                }
            },
            error: function () {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor' });
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
