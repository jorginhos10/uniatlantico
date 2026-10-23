<?php
// vista/modulo144/index.php
require_once __DIR__ . '/../../config/security.php';

$basePath = Config::getBasePath();
$baseUrl  = Config::getBaseUrl();

$fecha_cierre = null;
if (($formulario['tipo_tiempo'] ?? '') === 'rango' && !empty($formulario['fecha_cierre'])) {
    $fecha_cierre = $formulario['fecha_cierre'];
}

$titulo       = 'Plan de Acción — ' . htmlspecialchars($formulario['titulo'] ?? '');
$paginaActual = 'modulo144';

$eval_colores = ['#9C27B0', '#FF9500', '#1D71B8', '#34C759', '#FF3B30', '#673AB7', '#FF6230', '#32ADE6', '#3F51B5', '#FF2D55'];
function eval_darken($hex, $percent) {
    $hex = ltrim($hex, '#');
    $r = max(0, min(255, hexdec(substr($hex, 0, 2)) * (1 - $percent)));
    $g = max(0, min(255, hexdec(substr($hex, 2, 2)) * (1 - $percent)));
    $b = max(0, min(255, hexdec(substr($hex, 4, 2)) * (1 - $percent)));
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

ob_start();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 4px 8px;
            min-height: 48px;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(29,113,184,.15);
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #1D71B8;
            border: none;
            border-radius: 20px;
            color: white;
            padding: 2px 10px;
            font-size: 0.82rem;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice:first-of-type {
            background-color: #D85819;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255,255,255,0.8);
            margin-right: 5px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: white;
            background: transparent;
        }
        .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--color-primary);
        }
        .select2-search--dropdown .select2-search__field {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 6px 10px;
        }
        .select2-container { width: 100% !important; }
        .select2-max-reached {
            padding: 6px 12px;
            color: #D85819;
            font-size: 0.82rem;
            font-weight: 600;
        }
    </style>
    
    <style>
        :root {
            --color-primary: #1D71B8;
            --color-primary-light: #0A84FF;
            --color-success: #34C759;
            --color-warning: #FF9500;
            --color-danger: #FF3B30;
            --color-info: #1D71B8;
            --color-bg: #F2F2F7;
            --color-white: #FFFFFF;
            --color-tab-incomplete: #8E8E93;
            --color-tab-complete: #34C759;
        }

        .facultad-color-0 { border-left-color: #9C27B0; }
        .facultad-color-1 { border-left-color: #FF9500; }
        .facultad-color-2 { border-left-color: #1D71B8; }
        .facultad-color-3 { border-left-color: #34C759; }
        .facultad-color-4 { border-left-color: #FF3B30; }
        .facultad-color-5 { border-left-color: #673AB7; }
        .facultad-color-6 { border-left-color: #FF6230; }
        .facultad-color-7 { border-left-color: #32ADE6; }
        .facultad-color-8 { border-left-color: #3F51B5; }
        .facultad-color-9 { border-left-color: #FF2D55; }

        .badge-facultad-0 { background-color: #9C27B0; }
        .badge-facultad-1 { background-color: #FF9500; }
        .badge-facultad-2 { background-color: #1D71B8; }
        .badge-facultad-3 { background-color: #34C759; }
        .badge-facultad-4 { background-color: #FF3B30; }
        .badge-facultad-5 { background-color: #673AB7; }
        .badge-facultad-6 { background-color: #FF6230; }
        .badge-facultad-7 { background-color: #32ADE6; }
        .badge-facultad-8 { background-color: #3F51B5; }
        .badge-facultad-9 { background-color: #FF2D55; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
        }
        
        /* ═══ HEADER ═══ */
        .header-info {
            background: #fff;
            color: #1d1d1f;
            padding: 18px 22px;
            border-radius: 16px;
            margin-bottom: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,.07), 0 0 0 .5px rgba(60,60,67,.1);
        }

        .header-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #1D71B8 0%, #5856D6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(29,113,184,.32);
            color: white;
            font-size: 22px;
        }

        .header-eyebrow {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #1D71B8;
            margin-bottom: 2px;
        }

        .header-title {
            font-size: 20px;
            font-weight: 800;
            color: #1d1d1f;
            margin: 0 0 3px;
            letter-spacing: -.3px;
            line-height: 1.2;
        }

        .header-desc {
            font-size: 13px;
            color: #6e6e73;
            margin: 0;
        }

        .header-meta {
            margin-top: 8px;
            font-size: 12px;
            color: #aeaeb2;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* ═══ COUNTDOWN ═══ */
        .countdown-container {
            background: rgba(29,113,184,.04);
            border-radius: 12px;
            padding: 14px 18px;
            margin-top: 14px;
            border: 1px solid rgba(29,113,184,.12);
        }

        .countdown-timer {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .countdown-box {
            background: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            min-width: 72px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.07);
        }

        .countdown-number {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
            color: #1D71B8;
        }

        .countdown-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #aeaeb2;
            font-weight: 600;
        }

        /* ═══ ACCORDION ═══ */
        .accordion-item {
            border: none;
            margin-bottom: 10px;
            border-radius: 14px !important;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,.08), 0 0 0 .5px rgba(0,0,0,.06);
        }

        .accordion-button {
            padding: 14px 20px !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            color: white !important;
            border-radius: 14px !important;
            min-height: 60px;
        }

        .accordion-button .fa-2x {
            font-size: 1.15rem !important;
        }

        .accordion-button:not(.collapsed) {
            box-shadow: none;
        }

        .accordion-button::after {
            filter: brightness(0) invert(1);
        }

        .accordion-button .badge.bg-light {
            background: rgba(255,255,255,.18) !important;
            color: white !important;
            border: 1px solid rgba(255,255,255,.25) !important;
            border-radius: 20px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            padding: 4px 12px !important;
            letter-spacing: .2px !important;
        }
        
        .lista-container {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .lista-header {
            background: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: var(--color-primary);
        }
        
        .lista-item {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            transition: background 0.2s ease;
        }

        .lista-item:hover {
            background-color: #f8f9fa;
        }

        .lista-item:last-child {
            border-bottom: none;
        }

        .lista-item-titulo {
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 3px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .lista-item-sub {
            font-size: 0.78rem;
            color: #8a95a0;
            margin-top: 2px;
        }

        .lista-item-fecha {
            font-size: 0.82rem;
            color: #6c757d;
            line-height: 1.6;
        }

        .lista-item-autor {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.82rem;
            color: var(--apple-text, #1d1d1f);
            font-weight: 500;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .lista-item-autor.sin-datos {
            color: #b0b0b5;
            font-style: italic;
        }
        .lista-item-autor i {
            font-size: 0.72rem;
            color: var(--apple-blue, #0071e3);
            flex-shrink: 0;
        }

        .lista-item-actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            align-items: center;
        }

        .lmp-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #4338ca;
            white-space: nowrap;
            letter-spacing: 0.3px;
        }

        .lmp-badge i {
            font-size: 0.72rem;
            opacity: 0.65;
        }

        .lmp-badge.sin-datos {
            background: #f5f5f7;
            border-color: #e0e0e0;
            color: #b0b0b5;
            font-weight: 500;
            font-style: italic;
        }

        /* Línea exactamente al 100% → verde */
        .lmp-badge.lmp-completo {
            background: rgba(52, 199, 89, 0.12);
            border-color: rgba(52, 199, 89, 0.4);
            color: #248a3d;
            cursor: help;
        }
        .lmp-badge.lmp-completo i { opacity: 0.8; }
        .lmp-badge.lmp-completo .lmp-suma {
            background: rgba(52,199,89,0.2);
            opacity: 1;
        }

        /* Línea excedida (>100%) → rojo */
        .lmp-badge.lmp-excedido {
            background: rgba(255, 59, 48, 0.1);
            border-color: rgba(255, 59, 48, 0.4);
            color: #d70015;
            cursor: help;
            animation: pulseRed 2s infinite;
        }
        .lmp-badge.lmp-excedido i { opacity: 0.8; }
        .lmp-badge.lmp-excedido .lmp-suma {
            background: rgba(255,59,48,0.18);
            opacity: 1;
        }
        @keyframes pulseRed {
            0%, 100% { border-color: rgba(255,59,48,0.4); }
            50%       { border-color: rgba(255,59,48,0.9); }
        }

        /* Ítem sin ponderación propia dentro de la línea → número en naranja */
        .lmp-badge .lmp-suma.sin-aporte {
            background: rgba(255, 149, 0, 0.18);
            color: #ff9500;
            opacity: 1;
        }

        .cumplido-meta {
            display: flex;
            align-items: baseline;
            gap: 4px;
            font-size: 0.82rem;
            color: #6c757d;
            font-variant-numeric: tabular-nums;
        }
        .cumplido-medicion {
            font-size: 0.74rem;
            color: #6c757d;
            line-height: 1.3;
            margin-top: 2px;
        }
        .cumplido-meta i { font-size: 0.7rem; color: var(--apple-blue, #0071e3); }
        .cumplido-meta strong { color: var(--apple-text, #1d1d1f); font-weight: 600; }
        .lmp-suma {
            font-size: 0.68rem;
            font-weight: 600;
            opacity: 0.75;
            background: rgba(0,0,0,0.06);
            border-radius: 10px;
            padding: 1px 5px;
            margin-left: 2px;
        }

        /* Nombre verde cuando linea completa (exactamente 100) */
        .titulo-linea-completa {
            color: #248a3d !important;
        }
        .titulo-linea-completa::after {
            content: ' ✓';
            font-size: 0.75rem;
            color: #34c759;
        }
        /* Nombre rojo cuando excedido */
        .titulo-linea-excedida {
            color: #d70015 !important;
        }
        .titulo-linea-excedida::after {
            content: ' ⚠';
            font-size: 0.75rem;
            color: #ff3b30;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            background: #F2F2F7;
            border-radius: 14px;
            border: 1px dashed rgba(60,60,67,.2);
        }
        
        .empty-state i {
            color: #adb5bd;
        }
        
        .estado-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 13px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .estado-borrador   { background: rgba(142,142,147,.12); color: #6e6e73; }
        .estado-publicado  { background: rgba(52,199,89,.12);   color: #1A7A35; }
        .estado-cancelado  { background: rgba(255,59,48,.1);    color: #C0392B; }
        .estado-expirado   { background: rgba(255,59,48,.1);    color: #C0392B; }
        .estado-vigente    { background: rgba(52,199,89,.12);   color: #1A7A35; }
        .estado-sin-fechas { background: rgba(29,113,184,.1);    color: #1D71B8; }
        .estado-no-iniciado { background: rgba(255,149,0,.12);  color: #8B5E00; }
        
        .modal-content {
            border-radius: 16px;
            border: none;
            overflow: hidden;
        }

        .modal-header {
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 18px 22px;
        }

        .modal-header .modal-title {
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
            transition: background-color .2s;
        }

        .modal-header .modal-title:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .modal-header .modal-title-input {
            background: rgba(255,255,255,.15);
            border: 1.5px solid rgba(255,255,255,.5);
            color: white;
            font-size: 1.15rem;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 8px;
            width: 100%;
        }

        .modal-header .modal-title-input:focus {
            outline: none;
            background-color: rgba(255, 255, 255, 0.22);
        }

        .btn-close-white {
            filter: invert(1) brightness(2);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 12px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(29,113,184,.15);
        }
        
        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }
        
        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            margin-right: 10px;
            cursor: pointer;
        }
        
        .form-check-input:checked {
            background-color: var(--color-success);
            border-color: var(--color-success);
        }
        
        .form-check-label {
            font-weight: 500;
            color: var(--color-primary);
            cursor: pointer;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 8px;
        }
        
        .bg-light-view {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px;
            min-height: 46px;
        }
        
        .auto-save-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--color-success);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            display: none;
            z-index: 9999;
            animation: fadeInOut 2s ease;
        }
        
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(20px); }
            15% { opacity: 1; transform: translateY(0); }
            85% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        
        @media (max-width: 768px) {
            .countdown-box {
                min-width: 70px;
                padding: 10px;
            }
            .countdown-number {
                font-size: 1.8rem;
            }
            .lista-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .lista-item-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            border-bottom-color: var(--color-primary-light);
            background-color: rgba(29,113,184, 0.05);
        }
        
        .nav-tabs .nav-link.active {
            border-bottom-color: var(--color-primary);
            background-color: transparent;
            color: var(--color-primary) !important;
        }
        
        .nav-tabs .nav-link.tab-incomplete {
            color: var(--color-tab-incomplete) !important;
        }
        
        .nav-tabs .nav-link.tab-complete {
            color: var(--color-tab-complete) !important;
        }
        
        .nav-tabs .nav-link.tab-complete i {
            color: var(--color-tab-complete);
        }

        .lista-tabs {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #fff;
            padding-top: 6px;
        }

        /* ═══ PESTAÑAS PRINCIPALES (antes acordeones) ═══ */
        .modulo-tabs-top {
            border-bottom: 2px solid #e9ecef;
            flex-wrap: wrap;
        }

        .modulo-tabs-top .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            padding: 12px 18px;
            font-weight: 700;
            font-size: 0.92rem;
            color: #6e6e73;
        }

        .modulo-tabs-top .nav-link:hover {
            border-bottom-color: rgba(29,113,184,.4);
            background-color: rgba(29,113,184,.05);
        }

        .modulo-tabs-top .nav-link.active {
            color: var(--color-primary) !important;
            border-bottom-color: var(--color-primary);
            background-color: transparent;
        }

        .modulo-panel-header {
            display: flex;
            align-items: center;
            padding: 18px 22px;
            border-radius: 14px;
            color: white;
            margin-bottom: 4px;
        }

        .modulo-panel-title {
            font-size: 1.2rem;
            font-weight: 700;
        }

        .modulo-panel-header small {
            font-size: 0.82rem;
            opacity: 0.9;
        }

        .indicador-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .indicador-title {
            color: var(--color-primary);
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--color-primary-light);
        }

        /* Alinea el inicio de inputs/textareas aunque el label ocupe 1 o 2 líneas */
        .field-group .form-label {
            min-height: 34px;
            display: flex;
            align-items: flex-end;
        }

        .cumplimiento-ring-wrap {
            position: relative;
            width: 84px;
            height: 84px;
            margin: 0 auto;
        }

        .cumplimiento-ring-wrap svg circle {
            transition: stroke-dashoffset .4s ease, stroke .4s ease;
        }

        .cumplimiento-ring-value {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.05rem;
            color: #1d1d1f;
        }

        .meta-section {
            background-color: #f0f8ff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid var(--color-primary);
        }
        
        .meta-title {
            color: var(--color-primary);
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .modal-body-scroll {
            max-height: 70vh;
            overflow-y: auto;
            padding: 20px;
        }

        /* ═══ MODAL DE FORMULACIÓN: pantalla completa, barra azul y botón compactos ═══ */
        #modalFormulacion .modal-dialog { margin: 0 !important; width: 100vw; max-width: none; height: 100%; }
        #modalFormulacion .modal-content { height: 100%; max-height: none !important; border: 0; border-radius: 0 !important; }
        #modalFormulacion .modal-content > form { display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; }
        #modalFormulacion .modal-body-scroll { flex: 1 1 auto; max-height: none; min-height: 0; padding: 16px 28px 24px; }
        #modalFormulacion .modal-header { padding: 0.4rem 1rem; min-height: 0; border: 0; }
        #modalFormulacion .modal-title { font-size: 0.95rem; font-weight: 600; line-height: 1.3; }
        #modalFormulacion .btn-close { padding: 0.3rem; margin: 0; background-size: 0.7rem; }
        #modalFormulacion #formulacionTabs { padding-top: 0.35rem !important; }
        #modalFormulacion #formulacionTabs .nav-link { padding: 0.4rem 1rem; font-size: 0.82rem; }
        #modalFormulacion .modal-footer { padding: 0.35rem 1rem; }
        #modalFormulacion .modal-footer .btn { padding: 0.2rem 0.8rem; font-size: 0.8rem; }
        #modalFormulacion .form-label { font-size: 0.78rem; margin-bottom: 0.25rem; }
        #modalFormulacion .form-check-label, #modalFormulacion .form-check-label strong { font-size: 0.8rem; }

        /* MODAL DE SEGUIMIENTO: pantalla completa y compacto, como Formulación */
        #modalSeguimiento .modal-dialog { margin: 0 !important; width: 100vw; max-width: none; height: 100%; }
        #modalSeguimiento .modal-content { height: 100%; max-height: none !important; border: 0; border-radius: 0 !important; }
        #modalSeguimiento .modal-content > form { display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; }
        #modalSeguimiento .modal-body-scroll { flex: 1 1 auto; max-height: none; min-height: 0; padding: 16px 28px 24px; }
        #modalSeguimiento .modal-header { padding: 0.4rem 1rem; min-height: 0; border: 0; }
        #modalSeguimiento .modal-title { font-size: 0.95rem; font-weight: 600; line-height: 1.3; }
        #modalSeguimiento .btn-close { padding: 0.3rem; margin: 0; background-size: 0.7rem; }
        #modalSeguimiento .modal-footer { padding: 0.35rem 1rem; }
        #modalSeguimiento .modal-footer .btn { padding: 0.2rem 0.8rem; font-size: 0.8rem; }
        #modalSeguimiento .form-label { font-size: 0.78rem; margin-bottom: 0.25rem; }
        .seg-ctx { background: #F5F5F7; border: 1px solid #E5E5EA; border-radius: 10px; margin-bottom: 16px; }
        .seg-ctx > summary { cursor: pointer; padding: 9px 14px; font-size: 12px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #6E6E73; }
        .seg-ctx > summary small { text-transform: none; letter-spacing: 0; font-weight: 400; }
        .seg-card { display: grid; grid-template-columns: minmax(0, 1fr) 150px; gap: 16px; align-items: center; border: 1px solid #E5E5EA; border-radius: 12px; padding: 14px; background: #FAFAFC; }
        .seg-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
        .seg-tile { background: #fff; border: 1px solid #E5E5EA; border-radius: 10px; padding: 10px 12px; min-width: 0; }
        .seg-tile .form-label { margin-bottom: 2px; }
        .seg-num { font-size: 24px; font-weight: 800; min-height: 38px; display: flex; align-items: center; font-variant-numeric: tabular-nums; }
        .seg-input { font-size: 22px; font-weight: 800; min-height: 38px; padding: 2px 8px; font-variant-numeric: tabular-nums; }
        .seg-tile-total { background: #E4EEF8; border-color: transparent; }
        .seg-tile-total .seg-num { color: #143163; }
        /* Estado de proyectos: una tarjeta por semestre, con aire entre tarjetas y entre campos */
        .seg-estado-sem { display: grid; grid-template-columns: 110px minmax(0, 1fr); gap: 20px; align-items: stretch;
                          background: #fff; border: 1px solid #E5E5EA; border-radius: 14px; padding: 16px; margin-bottom: 20px; }
        .seg-estado-sem:last-child { margin-bottom: 0; }
        .seg-estado-label { background: #E4EEF8; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; color: #143163; min-height: 96px; }
        .seg-estado-label span { font-size: 11px; font-weight: 700; letter-spacing: .08em; }
        .seg-estado-label b { font-size: 36px; font-weight: 800; line-height: 1; }
        .seg-estado-campos { display: grid !important; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin: 0 !important; }
        .seg-estado-campos > [class*="col-"] { width: auto; max-width: none; padding: 0; margin: 0; }
        .seg-estado-campos .form-label { margin-bottom: 6px; }
        @media (max-width: 1200px) { .seg-estado-campos { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 800px) {
            .seg-estado-sem { grid-template-columns: minmax(0, 1fr); gap: 14px; padding: 14px; }
            .seg-estado-label { flex-direction: row; justify-content: flex-start; gap: 10px; padding: 8px 14px; min-height: 0; }
            .seg-estado-label b { font-size: 22px; }
            .seg-estado-campos { grid-template-columns: minmax(0, 1fr); gap: 12px; }
        }
        .seg-sems { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 12px; }
        .seg-sem { background: #fff; border: 1px solid #E5E5EA; border-radius: 12px; padding: 12px 14px; display: grid; gap: 8px; min-width: 0; align-content: start; }
        .seg-sem-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; }
        #tablaSeguimientoFacultades thead th { vertical-align: middle; font-size: 12px; }
        @media (max-width: 800px) { .seg-sems { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 700px) { .seg-card { grid-template-columns: minmax(0, 1fr); } #modalSeguimiento .modal-body-scroll { padding: 12px 14px 20px; } }
        .seg-ev-label { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #6E6E73; }
        .seg-ev-btn { display: inline-flex; align-items: center; gap: 8px; border: 2px dashed #1D71B8; background: #E4EEF8; color: #1D71B8; border-radius: 99px; padding: 3px 6px 3px 12px; cursor: pointer; }
        .seg-ev-btn:hover { border-style: solid; }
        .seg-ev-btn .ev-n { min-width: 24px; height: 24px; padding: 0 7px; border-radius: 99px; background: #1D71B8; color: #fff; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
        #modalEvidencias .ev-lista li { display: flex; align-items: center; gap: 10px; border: 1px solid #E5E5EA; border-radius: 8px; padding: 7px 10px; margin-bottom: 6px; background: #FAFAFC; min-width: 0; }
        #modalEvidencias .ev-ext { flex: none; width: 36px; height: 36px; border-radius: 8px; background: #E6F0FA; color: #1D71B8; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; text-transform: uppercase; text-decoration: none; }
        #modalEvidencias .ev-nombre { min-width: 0; flex: 1; }
        #modalEvidencias .ev-nombre a { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 700; color: #1D71B8; text-decoration: underline; }
        #modalEvidencias .ev-nombre small { color: #6E6E73; }
        #modalEvidencias .ev-quitar { flex: none; border: 0; background: none; color: #6E6E73; font-size: 18px; width: 28px; height: 28px; border-radius: 6px; }
        #modalEvidencias .ev-quitar:hover { background: #FFF4E0; color: #B45309; }
        #modalEvidencias .ev-vacio { border: 2px dashed #E5E5EA; border-radius: 10px; padding: 18px; }
        #modalEvidencias .ev-ruta { font-family: ui-monospace, Consolas, monospace; font-size: 12px; background: #F2F4F8; border: 1px solid #E5E5EA; border-radius: 6px; padding: 6px 9px; word-break: break-all; color: #6E6E73; }

        /* Buscador de proyectos (código L-M-P o nombre) */
        .m144-buscar { position: relative; }
        .m144-buscar > .fa-search { position: absolute; left: 13px; top: 21px; transform: translateY(-50%); color: #86868b; pointer-events: none; }
        .m144-buscar > input { height: 42px; padding-left: 38px; padding-right: 36px; font-size: 0.95rem; border: 2px solid #0071e3 !important; }
        .m144-buscar-limpiar { position: absolute; right: 8px; top: 7px; width: 28px; height: 28px; border: 0; border-radius: 50%; background: transparent; color: #86868b; font-size: 1.3rem; line-height: 1; cursor: pointer; }
        .m144-buscar-limpiar:hover { background: #f0f0f2; color: #1d1d1f; }
        .m144-buscar-lista { position: absolute; z-index: 40; left: 0; right: 0; top: calc(100% + 4px); max-height: 360px; overflow-y: auto; background: #fff; border: 1px solid #d2d2d7; border-radius: 10px; box-shadow: 0 14px 34px rgba(0, 0, 0, 0.18); }
        .m144-opcion { display: flex; gap: 12px; align-items: flex-start; padding: 8px 14px; cursor: pointer; border-bottom: 1px solid #f0f0f3; }
        .m144-opcion:last-child { border-bottom: 0; }
        .m144-opcion.activa, .m144-opcion:hover { background: #e8f2ff; }
        .m144-opcion-codigo { flex: none; min-width: 92px; text-align: center; font-weight: 700; font-size: 0.76rem; color: #0058b0; background: #e8f2ff; border-radius: 6px; padding: 3px 8px; white-space: nowrap; }
        .m144-opcion.activa .m144-opcion-codigo, .m144-opcion:hover .m144-opcion-codigo { background: #fff; }
        .m144-opcion-nombre { font-weight: 600; font-size: 0.88rem; color: #1d1d1f; }
        .m144-opcion-sub { font-size: 0.75rem; color: #6e6e73; margin-top: 1px; }
        .m144-buscar-vacio { padding: 14px; font-size: 0.85rem; color: #6e6e73; text-align: center; }
        .m144-buscar-nota { padding: 6px 14px; font-size: 0.74rem; color: #86868b; background: #fafafa; text-align: center; }


        .desarrollo-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 30px;
            margin-top: 20px;
            color: white;
            text-align: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .desarrollo-icon {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        
        .desarrollo-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .desarrollo-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .desarrollo-badge {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 30px;
            padding: 8px 20px;
            display: inline-block;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .facultad-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        
        .facultad-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }
        
        .facultad-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            padding: 10px 0;
        }
        
        .facultad-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .facultad-header i {
            transition: transform 0.3s ease;
        }
        
        .facultad-header.collapsed i {
            transform: rotate(-90deg);
        }
        
        .facultad-content {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .badge-facultad {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
        }

        .facultad-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 3px solid var(--color-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .facultad-item:hover {
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .facultad-item-info h6 {
            margin: 0 0 5px 0;
            color: var(--color-primary);
        }
        
        .facultad-item-info small {
            color: #6c757d;
        }
        
        .facultad-item-actions {
            display: flex;
            gap: 5px;
        }

        /* ═══ EVALUACIÓN LÍNEAS — RESUMEN DEL INFORME ═══ */
        .ev-resumen { display: grid; grid-template-columns: minmax(0, 300px) minmax(0, 1fr); gap: 20px; align-items: start; }
        @media (max-width: 900px) { .ev-resumen { grid-template-columns: 1fr; } }
        .ev-total {
            background: #fff; border-radius: 16px; padding: 24px; text-align: center;
            box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 6px 20px rgba(0,0,0,.05); position: sticky; top: 90px;
        }
        .ev-total-label { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #6E6E73; }
        .ev-donut { position: relative; width: 168px; height: 168px; margin: 16px auto 10px; }
        .ev-donut svg { width: 100%; height: 100%; transform: rotate(-90deg); }
        .ev-donut-num { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .ev-donut-num strong { font-size: 2.1rem; font-weight: 800; letter-spacing: -.03em; line-height: 1; }
        .ev-donut-num span { font-size: .75rem; color: #6E6E73; margin-top: 4px; }
        .ev-total-sub { font-size: .82rem; color: #6E6E73; margin-bottom: 16px; }
        .ev-total-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; }
        .ev-total-stats div { background: #F5F5F7; border-radius: 10px; padding: 10px 6px; }
        .ev-total-stats b { display: block; font-size: 1.15rem; }
        .ev-total-stats small { font-size: .72rem; color: #6E6E73; }
        .ev-btn-informe {
            display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 10px;
            background: #1D71B8; color: #fff !important; font-weight: 600; font-size: .88rem; text-decoration: none;
        }
        .ev-btn-informe:hover { background: #0066CC; }
        .ev-lineas { display: flex; flex-direction: column; gap: 12px; }
        .ev-linea { background: #fff; border-radius: 14px; box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 6px 20px rgba(0,0,0,.05); overflow: hidden; border-left: 5px solid var(--ev-color); }
        .ev-linea > summary { list-style: none; cursor: pointer; padding: 16px 18px; display: grid; grid-template-columns: minmax(0, 1fr) minmax(120px, 220px) 74px 18px; gap: 16px; align-items: center; }
        .ev-linea > summary::-webkit-details-marker { display: none; }
        @media (max-width: 700px) { .ev-linea > summary { grid-template-columns: minmax(0, 1fr) 64px 16px; } .ev-linea > summary .ev-bar { grid-column: 1 / -1; grid-row: 2; } }
        .ev-linea-titulo { font-weight: 700; font-size: .95rem; line-height: 1.3; }
        .ev-linea-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
        .ev-chip { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: .72rem; font-weight: 600; background: #F2F2F7; color: #3A3A3C; }
        .ev-chip.codigo { background: var(--ev-color); color: #fff; }
        .ev-chip.peso { background: rgba(29,113,184,.1); color: #0058b0; }
        .ev-bar { height: 8px; border-radius: 999px; background: #EEEEF2; overflow: hidden; }
        .ev-bar span { display: block; height: 100%; border-radius: 999px; }
        .ev-pct { text-align: right; font-weight: 800; font-size: 1.05rem; font-variant-numeric: tabular-nums; }
        .ev-chevron { color: #AEAEB2; transition: transform .2s; }
        .ev-linea[open] .ev-chevron { transform: rotate(180deg); }
        .ev-motores { border-top: 1px solid #F0F0F3; padding: 6px 18px 14px; }
        .ev-motor { display: grid; grid-template-columns: minmax(0, 1fr) minmax(100px, 200px) 64px; gap: 14px; align-items: center; padding: 9px 0; border-bottom: 1px solid #F5F5F7; font-size: .86rem; }
        .ev-motor:last-child { border-bottom: 0; }
        .ev-motor .ev-bar { height: 6px; }
        .ev-motor .ev-pct { font-size: .9rem; font-weight: 700; }
        .ev-motor small { display: block; color: #6E6E73; font-size: .74rem; margin-top: 2px; }
        .ev-vacio { color: #6E6E73; font-size: .85rem; padding: 10px 0; }
        .ev-subtabs { gap: 6px; }
        .ev-subtabs .nav-link { border-radius: 999px; font-weight: 600; font-size: .88rem; color: #143163; background: #E7ECF5; padding: .45rem 1.1rem; }
        .ev-subtabs .nav-link.active { background: #143163; color: #fff; }
        .cn-intro { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap; margin-bottom: 16px; }
        .cn-titulo { font-size: 1.15rem; font-weight: 700; color: #143163; }
        .cn-sub { font-size: .85rem; color: #6E6E73; margin-top: 2px; }
        .cn-filtros { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .cn-filtros input, .cn-filtros select { border: 1px solid #D2D2D7; border-radius: 9px; padding: 8px 12px; min-height: 38px; font-size: .88rem; background: #fff; min-width: 0; }
        .cn-filtros input { width: 260px; max-width: 100%; }
        .cn-contador { font-size: .8rem; font-weight: 700; color: #143163; background: #E7ECF5; border-radius: 99px; padding: 5px 12px; }
        .cn-tabla-wrap { overflow-x: auto; border: 1px solid #E5E5EA; border-radius: 12px; background: #fff; }
        .cn-tabla { border-collapse: collapse; width: 100%; min-width: 980px; font-size: .88rem; }
        .cn-tabla th, .cn-tabla td { padding: 10px 12px; border-bottom: 1px solid #EEEEF2; text-align: left; vertical-align: middle; }
        .cn-tabla thead th { background: #E7ECF5; color: #5b6479; font-size: .72rem; letter-spacing: .05em; text-transform: uppercase; position: sticky; top: 0; }
        .cn-tabla tbody tr:last-child td { border-bottom: 0; }
        .cn-tabla .cn-num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .cn-nombre { font-weight: 600; max-width: 380px; }
        .cn-anio { font-weight: 800; color: #143163; }
        .cn-lmp { display: inline-block; background: #E4EEF8; color: #1D71B8; font-weight: 700; font-size: .75rem; border-radius: 99px; padding: 3px 10px; white-space: nowrap; }
        .cn-estado { font-size: .75rem; font-weight: 700; border-radius: 99px; padding: 3px 10px; background: #FDF1D6; color: #8A5A00; }
        .cn-estado.ok { background: #E3F4EA; color: #1F8A4C; }
        /* Concertaciones: vista simple */
        .cnx-barra { display: flex; flex-direction: column; gap: 12px; margin-bottom: 14px; }
        .cnx-chips { display: flex; gap: 6px; flex-wrap: wrap; }
        .cnx-chip { border: 1px solid #E1E4EC; background: #fff; color: #424245; border-radius: 999px; padding: 6px 14px; font-size: .84rem; font-weight: 600; cursor: pointer; display: inline-flex; gap: 8px; align-items: center; }
        .cnx-chip b { background: #EEF0F5; color: #5b6479; border-radius: 99px; padding: 0 8px; font-size: .75rem; }
        .cnx-chip:hover { border-color: #143163; }
        .cnx-chip.on { background: #143163; border-color: #143163; color: #fff; }
        .cnx-chip.on b { background: rgba(255,255,255,.2); color: #fff; }
        .cnx-tools { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .cnx-tools input, .cnx-tools select { border: 1px solid #E1E4EC; border-radius: 10px; padding: 8px 12px; min-height: 38px; font-size: .88rem; background: #fff; min-width: 0; }
        .cnx-tools input { flex: 1 1 260px; max-width: 420px; }
        .cnx-vista { border: 1px solid #E1E4EC; background: #fff; color: #143163; border-radius: 10px; padding: 8px 12px; min-height: 38px; font-size: .84rem; font-weight: 600; cursor: pointer; margin-left: auto; }
        .cnx-vista:hover { border-color: #143163; }
        .cnx-lista { display: flex; flex-direction: column; gap: 8px; }
        .cnx-item { display: grid; grid-template-columns: minmax(0, 1fr) 118px 118px 124px 18px; gap: 18px; align-items: center; background: #fff; border: 1px solid #E8EAF0; border-radius: 12px; padding: 12px 16px; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
        .cnx-item:hover, .cnx-item:focus-visible { border-color: #143163; box-shadow: 0 2px 10px rgba(20,49,99,.08); outline: none; }
        .cnx-nombre { font-weight: 600; color: #1d1d1f; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .cnx-sub { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 4px; font-size: .8rem; color: #6E6E73; }
        .cnx-prog { display: grid; grid-template-columns: 22px minmax(0, 1fr) 34px; gap: 6px; align-items: center; font-size: .78rem; color: #6E6E73; }
        .cnx-prog b { text-align: right; color: #424245; font-variant-numeric: tabular-nums; font-weight: 600; }
        .cnx-barra-p { height: 6px; background: #EEF0F5; border-radius: 99px; overflow: hidden; }
        .cnx-barra-p i { display: block; height: 100%; background: #1D71B8; border-radius: 99px; transition: width .25s; }
        .cnx-pill { font-size: .74rem; font-weight: 700; border-radius: 99px; padding: 4px 10px; text-align: center; white-space: nowrap; }
        .cnx-pill.si { background: #E3F4EA; color: #1F8A4C; }
        .cnx-pill.no { background: #FDF1D6; color: #8A5A00; }
        .cnx-pill.nd { background: #EEF0F5; color: #6E6E73; }
        .cnx-flecha { color: #B0B5C3; font-size: .8rem; }
        .cnx-item:hover .cnx-flecha { color: #143163; }
        @media (max-width: 900px) {
            .cnx-item { grid-template-columns: minmax(0, 1fr) auto; gap: 8px 12px; }
            .cnx-main { grid-column: 1 / -1; }
            .cnx-flecha { display: none; }
            .cnx-vista { margin-left: 0; }
        }
        /* Concertaciones: ventana de un indicador */
        .cnx-modal .modal-header { background: #143163; color: #fff; padding: .6rem 1rem; gap: 14px; align-items: center; }
        .cnx-modal .modal-title { flex: 1; font-size: 1rem; font-weight: 600; }
        .cnx-nav { display: flex; align-items: center; gap: 6px; font-size: .8rem; opacity: .95; }
        .cnx-flechas { border: 0; width: 30px; height: 30px; border-radius: 8px; background: rgba(255,255,255,.14); color: #fff; cursor: pointer; }
        .cnx-flechas:hover:not(:disabled) { background: rgba(255,255,255,.28); }
        .cnx-flechas:disabled { opacity: .35; cursor: default; }
        .cnx-ind { font-size: 1.05rem; font-weight: 700; color: #1d1d1f; line-height: 1.35; }
        .cnx-metas { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin: 8px 0 14px; }
        .cnx-dato { font-size: .8rem; color: #5b6479; background: #F2F4F8; border-radius: 99px; padding: 3px 10px; }
        .cnx-comp { display: flex; flex-wrap: wrap; gap: 10px 28px; align-items: center; background: #F7F8FB; border-radius: 12px; padding: 12px 16px; margin-bottom: 14px; }
        .cnx-c { display: grid; gap: 2px; font-size: .88rem; }
        .cnx-c small { color: #6E6E73; font-size: .74rem; letter-spacing: .03em; }
        .cnx-comp .cnx-pill { margin-left: auto; font-size: .8rem; padding: 6px 14px; }
        .cnx-ayuda { color: #6E6E73; font-size: .84rem; margin: 0 0 10px; }
        .cnx-tabla { width: 100%; border-collapse: collapse; }
        .cnx-tabla th { font-size: .72rem; letter-spacing: .05em; text-transform: uppercase; color: #8a90a2; font-weight: 700; padding: 8px 12px; border-bottom: 1px solid #E8EAF0; text-align: left; }
        .cnx-tabla td { padding: 12px; border-bottom: 1px solid #F0F1F5; vertical-align: middle; }
        .cnx-tabla .cnx-n { text-align: right; width: 90px; font-variant-numeric: tabular-nums; }
        .cnx-fac { font-weight: 600; }
        .cnx-celda { display: flex; align-items: center; gap: 8px 10px; flex-wrap: wrap; min-width: 0; }
        .cnx-valor { font-weight: 700; font-variant-numeric: tabular-nums; min-width: 42px; }
        .cnx-input { width: 96px; border: 1px solid #C9CEDB; border-radius: 8px; padding: 5px 8px; font-size: .9rem; font-weight: 700; }
        .cnx-estado { font-size: .78rem; color: #8A5A00; background: #FDF1D6; border-radius: 99px; padding: 3px 10px; font-weight: 600; }
        .cnx-estado.ok { color: #1F8A4C; background: #E3F4EA; }
        .cnx-btn { border: 0; border-radius: 8px; padding: 6px 14px; font-size: .8rem; font-weight: 700; cursor: pointer; background: #143163; color: #fff; }
        .cnx-btn.sec { background: #E7ECF5; color: #143163; }
        .cnx-btn:hover:not(:disabled) { filter: brightness(1.15); }
        .cnx-btn:disabled { opacity: .5; cursor: wait; }
        .cnx-link { border: 0; background: none; color: #6E6E73; font-size: .78rem; text-decoration: underline; cursor: pointer; padding: 2px 4px; }
        .cnx-link:hover { color: #143163; }
        .cnx-nd { color: #9aa1b2; font-size: .82rem; }
        .cnx-det { border: 0; background: none; color: #9aa1b2; cursor: pointer; width: 30px; height: 30px; border-radius: 8px; }
        .cnx-det:hover { background: #F2F4F8; color: #143163; }
        .cnx-desc td { background: #F9FAFC; color: #424245; font-size: .86rem; line-height: 1.45; padding: 10px 16px 14px; }
        .cnx-modal .modal-footer { justify-content: space-between; }
        .cnx-modal .table-responsive { overflow-x: visible; }
        .cnx-tabla tr[hidden] { display: none; }
        @media (max-width: 820px) {
            .cnx-modal .modal-body { padding: 14px 12px; }
            .cnx-tabla, .cnx-tabla tbody, .cnx-tabla tfoot, .cnx-tabla tr, .cnx-tabla td { display: block; width: 100%; box-sizing: border-box; }
            .cnx-tabla thead { display: none; }
            .cnx-tabla tr { border: 1px solid #E8EAF0; border-radius: 12px; padding: 8px 14px; margin-bottom: 10px; background: #fff; position: relative; }
            .cnx-tabla tr[hidden] { display: none; }
            .cnx-tabla td { border: 0; padding: 7px 0; display: flex; justify-content: space-between; align-items: center; gap: 12px; text-align: right; }
            .cnx-tabla td[data-label]::before { content: attr(data-label); flex: none; min-width: 86px; text-align: left; font-size: .7rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #8a90a2; }
            .cnx-tabla td.cnx-fac { display: block; text-align: left; font-weight: 700; padding: 4px 36px 8px 0; }
            .cnx-tabla td.cnx-n { font-variant-numeric: tabular-nums; }
            .cnx-tabla .cnx-celda { justify-content: flex-end; }
            .cnx-tabla td.cnx-det-td { position: absolute; top: 6px; right: 6px; width: auto; padding: 0; }
            .cnx-tabla td.cnx-det-td:empty { display: none; }
            .cnx-tabla tr.cnx-desc { background: #F9FAFC; }
            .cnx-tabla tr.cnx-desc td { display: block; text-align: left; padding: 6px 0; }
            .cnx-tabla tfoot tr td.cnx-fac { padding-right: 0; }
            .cnx-comp { gap: 8px 16px; }
            .cnx-comp .cnx-pill { margin-left: 0; }
            .cnx-modal .modal-footer { flex-direction: column; align-items: stretch; }
            .cnx-masivo { flex-direction: column; }
        }
        /* Debate de la meta real: facultad | vicerrectoría */
        .cnx-sem-cab { display: flex; align-items: center; gap: 4px 10px; flex-wrap: wrap; margin-bottom: 6px; }
        .cnx-sem-cab h6 { margin: 0; font-size: 1.05rem; font-weight: 800; color: #143163; }
        .cnx-sem-cuenta { font-size: .78rem; color: #6E6E73; }
        .cnx-sem-cab .cnx-btn { margin-left: auto; }
        .cnx-sem-vacio { padding: 14px 4px; }
        .cnx-modal .modal-body { padding: 0; background: #eef1f7; }
        .cnx-modal .cnx-ind, .cnx-modal .cnx-metas, .cnx-modal .cnx-ayuda, .cnx-modal #cnError { margin-left: 24px; margin-right: 24px; }
        .cnx-modal .cnx-ind { margin-top: 16px; }
        .cnv-body { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 0; border-top: 1px solid #dbe0ec; margin-top: 8px; min-height: 60vh; background: #fff; }
        .cnv-body.solo { grid-template-columns: minmax(0, 1fr); }
        .cnv-rail { border-right: 1px solid #dbe0ec; background: #f4f6fb; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
        .cnv-rail h3 { margin: 2px 4px; font-size: .72rem; letter-spacing: .07em; text-transform: uppercase; color: #5d667c; }
        .cnv-fi { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 4px 8px; text-align: left; border: 1px solid #dbe0ec; background: #fff; border-radius: 10px; padding: 9px 11px; cursor: pointer; }
        .cnv-fi:hover { border-color: #1D71B8; }
        .cnv-fi[aria-current="true"] { border-color: #143163; box-shadow: inset 3px 0 0 #D85819; }
        .cnv-fi .n { font-weight: 700; font-size: .85rem; line-height: 1.25; }
        .cnv-fi small { color: #5d667c; }
        .cnv-dots { display: flex; gap: 4px; align-items: center; }
        .cnv-dot { font-size: .68rem; font-weight: 700; padding: 2px 7px; border-radius: 999px; background: #dbe0ec; color: #5d667c; }
        .cnv-dot.listo, .cnv-pill.listo { background: #e4f4ea; color: #237a4b; }
        .cnv-dot.pendiente, .cnv-pill.pendiente { background: #fdf1d6; color: #8a5a00; }
        .cnv-dot.enviado, .cnv-pill.enviado { background: #e1eefa; color: #144f86; }
        .cnv-dot.aprobado, .cnv-pill.aprobado { background: #237a4b; color: #fff; }
        .cnv-det { padding: 18px 22px; display: flex; flex-direction: column; gap: 14px; min-width: 0; }
        .cnv-dh { display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap; }
        .cnv-dh h3 { margin: 0; font-size: 1.25rem; font-weight: 800; color: #143163; text-transform: uppercase; }
        .cnv-dh span { color: #5d667c; }
        .cnv-sems { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .cnv-sem { border: 1px solid #dbe0ec; border-radius: 12px; background: #fff; display: flex; flex-direction: column; min-width: 0; overflow: hidden; }
        .cnv-sem > header { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 11px 14px; background: #f4f6fb; border-bottom: 1px solid #dbe0ec; }
        .cnv-sem > header h4 { margin: 0; font-size: 1rem; font-weight: 800; color: #143163; }
        .cnv-pill { font-size: .75rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; background: #dbe0ec; color: #5d667c; white-space: nowrap; }
        .cnv-duelo { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
        .cnv-lado { padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; min-width: 0; }
        .cnv-lado + .cnv-lado { border-left: 1px dashed #dbe0ec; }
        .cnv-t { font-size: .72rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: #5d667c; }
        .cnv-lado .cnx-in { font-size: 1.4rem; font-weight: 800; padding: 8px 12px; border-radius: 10px; }
        .cnv-lado .cnx-valor { font-size: 1.4rem; font-weight: 800; text-align: center; }
        .cnv-sem .cnx-res { border-top: 1px solid #dbe0ec; padding: 12px 14px; display: flex; flex-wrap: wrap; gap: 8px 10px; align-items: center; justify-content: center; min-height: 62px; }
        .cnv-na { padding: 18px; text-align: center; }
        @media (max-width: 1000px) { .cnv-sems { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 760px) {
            .cnv-body { grid-template-columns: minmax(0, 1fr); }
            .cnv-rail { border-right: 0; border-bottom: 1px solid #dbe0ec; flex-direction: row; overflow-x: auto; }
            .cnv-rail h3 { display: none; }
            .cnv-fi { min-width: 200px; }
            .cnv-duelo { grid-template-columns: minmax(0, 1fr); }
            .cnv-lado + .cnv-lado { border-left: 0; border-top: 1px dashed #dbe0ec; }
        }
        .cnx-unica { border: 2px solid #1d1d1f; border-radius: 12px; overflow: hidden; background: #fff; }
        .cnx-uh, .cnx-ur { display: grid; grid-template-columns: minmax(110px, .6fr) minmax(0, 1.6fr) minmax(0, 1.6fr); }
        .cnx-uh { background: #F7F8FB; border-bottom: 2px solid #1d1d1f; }
        .cnx-uh-nombre { padding: 10px 12px; font-weight: 800; align-self: end; }
        .cnx-uh-sem { padding: 10px 12px 0; border-left: 2px solid #1d1d1f; display: flex; flex-direction: column; justify-content: flex-end; }
        .cnx-uh-sub { display: grid; grid-template-columns: 1fr 1fr; margin: 0 -12px; border-top: 1px solid #D5D9E4; }
        .cnx-uh-sub span { padding: 8px 12px; text-align: center; font-weight: 800; font-size: .92rem; }
        .cnx-uh-sub span:nth-child(2) { border-left: 2px solid #1d1d1f; }
        .cnx-ur { border-bottom: 1px solid #E8EAF0; align-items: stretch; }
        .cnx-ur:last-child { border-bottom: 0; }
        .cnx-us { display: grid; grid-template-columns: 1fr 1fr; border-left: 2px solid #1d1d1f; }
        .cnx-us .cnx-gf { border-left: 0; }
        .cnx-us .cnx-gv { border-left: 2px solid #1d1d1f; }
        .cnx-us .cnx-res { grid-column: 1 / -1; border-left: 0; border-top: 1px dashed #E8EAF0; flex-direction: row; flex-wrap: wrap; align-items: center; padding: 8px 12px; }
        .cnx-na { grid-column: 1 / -1; display: flex; align-items: center; justify-content: center; padding: 14px; }
        .cnx-gn { padding: 10px 12px; display: flex; flex-direction: column; gap: 2px; position: relative; justify-content: center; }
        .cnx-gnombre { font-weight: 700; line-height: 1.25; padding-right: 26px; }
        .cnx-gn small { color: #6E6E73; font-size: .76rem; }
        .cnx-gn .cnx-det { position: absolute; top: 6px; right: 6px; }
        .cnx-gl { padding: 10px 10px; display: flex; flex-direction: column; align-items: stretch; justify-content: flex-start; gap: 8px; min-width: 0; }
        .cnx-gl .cnx-valor, .cnx-gl > .cnx-nd { text-align: center; }
        .cnx-txt { width: 100%; box-sizing: border-box; min-height: 56px; resize: vertical; border: 1px solid #C9CEDB; border-radius: 8px; padding: 6px 8px; font-size: .84rem; line-height: 1.3; font-family: inherit; }
        .cnx-txt:focus { outline: 2px solid #1D71B8; outline-offset: 1px; border-color: #1D71B8; }
        .cnx-desc-ro { font-size: .82rem; color: #424245; line-height: 1.35; white-space: pre-line; word-break: break-word; }
        .cnx-in { width: 100%; max-width: none; box-sizing: border-box; text-align: center; border: 1px solid #C9CEDB; border-radius: 8px; padding: 7px 8px; font-size: 1rem; font-weight: 700; font-variant-numeric: tabular-nums; }
        .cnx-in:focus { outline: 2px solid #1D71B8; outline-offset: 1px; border-color: #1D71B8; }
        .cnx-gl .cnx-valor { font-size: 1.05rem; }
        .cnx-res { padding: 10px 12px; display: flex; flex-direction: column; justify-content: center; align-items: flex-start; gap: 6px; border-left: 1px solid #E8EAF0; }
        .cnx-estado.deb { color: #8A5A00; background: #FDF1D6; }
        .cnx-estado.env { color: #1D71B8; background: #E4EEF8; }
        .cnx-pill.env { background: #E4EEF8; color: #1D71B8; }
        .cnx-btn.verde { background: #1F8A4C; color: #fff; }
        .cnx-hint { font-size: .76rem; color: #6E6E73; }
        .cnx-acciones { display: flex; flex-direction: column; gap: 4px; align-items: flex-start; }
        .cnx-estado.fin { color: #fff; background: #143163; }
        .cnx-estado.fin b, .cnx-estado.ok b { margin-left: 2px; }
        .cnx-elige { display: flex; flex-direction: column; gap: 4px; align-items: flex-start; }
        .cnx-gdesc { grid-column: 1 / -1; background: #F9FAFC; border-top: 1px dashed #E1E4EC; color: #424245; font-size: .86rem; line-height: 1.45; padding: 10px 14px; }
        .cnx-gdesc[hidden] { display: none; }
        @media (max-width: 860px) {
            .cnx-uh, .cnx-ur { grid-template-columns: 1fr; }
            .cnx-uh-nombre { display: none; }
            .cnx-uh-sem { border-left: 0; border-bottom: 1px solid #D5D9E4; padding-bottom: 0; }
            .cnx-ur { border-bottom: 3px solid #EEF0F5; }
            .cnx-us { border-left: 0; border-top: 1px solid #E8EAF0; }
            .cnx-us::before { content: 'Semestre ' attr(data-sem); grid-column: 1 / -1; padding: 6px 12px 0; font-size: .72rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #8a90a2; }
            .cnx-sem-cab .cnx-btn { margin-left: 0; width: 100%; }
        }
        .cnx-fila-dep td { background: #F7F8FB; }
        .cnx-fila-dep .cnx-fac small { display: block; font-weight: 500; font-size: .74rem; color: #6E6E73; margin-top: 2px; }
        .cnx-aprob td { background: #fff; border-top: 2px solid #E8EAF0; border-bottom: 0; padding-top: 14px; }
        .cnx-aprob .cnx-fac { color: #143163; }
        .cnx-prog.apr .cnx-barra-p i { background: #1F8A4C; }
        .cnx-prog.apr b { color: #1F8A4C; }
        .cnx-masivo { display: flex; gap: 8px; flex-wrap: wrap; }
        .cn-matriz { min-width: max-content; }
        .cn-matriz th, .cn-matriz td { white-space: nowrap; }
        .cn-matriz .cn-grupos th { text-align: center; font-size: .78rem; letter-spacing: .04em; padding: 8px 10px; background: transparent; color: #143163; position: static; border-bottom: 0; }
        .cn-matriz .cn-g-dep { background: #D85819 !important; color: #fff !important; border-radius: 8px 8px 0 0; }
        .cn-matriz .cn-g-fac { background: #F4C7AE !important; color: #5a2a10 !important; border-radius: 8px 8px 0 0; border-left: 4px solid #fff; }
        .cn-matriz thead tr:not(.cn-grupos) th { background: #143163; color: #fff; text-align: center; }
        .cn-matriz thead .cn-th-coinciden { background: #1D71B8; }
        .cn-matriz td.cn-fg:first-of-type { border-left: 0; }
        .cn-matriz .cn-meta { border-left: 4px solid #fff; background: #FBEDE4; font-weight: 700; }
        .cn-matriz .cn-fg[data-fac] { background-clip: padding-box; }
        .cn-matriz .cn-sticky { position: sticky; left: 0; z-index: 2; background: #fff; box-shadow: 1px 0 0 #E5E5EA; }
        .cn-matriz thead .cn-sticky { z-index: 3; background: #143163; text-align: left; }
        .cn-matriz .cn-nombre { min-width: 260px; max-width: 320px; white-space: normal; }
        .cn-matriz .cn-formula, .cn-matriz .cn-desc { white-space: normal; min-width: 200px; max-width: 280px; font-size: .8rem; color: #424245; }
        .cn-matriz .cn-tipo { white-space: normal; min-width: 130px; text-align: center; }
        .cn-matriz .cn-resp { min-width: 190px; white-space: normal; }
        .cn-matriz .cn-num { text-align: center; }
        .cn-matriz .cn-tot { font-weight: 800; color: #143163; }
        .cn-coinciden { font-weight: 800; text-align: center; letter-spacing: .04em; }
        td.cn-coinciden.si { background: #D8F0DF; color: #1F8A4C; }
        td.cn-coinciden.no { background: #FDF1D6; color: #8A5A00; }
        td.cn-coinciden.nd { color: #6E6E73; }
        span.cn-coinciden { border-radius: 99px; padding: 4px 12px; font-size: .8rem; display: inline-block; }
        span.cn-coinciden.si { background: #D8F0DF; color: #1F8A4C; }
        span.cn-coinciden.no { background: #FDF1D6; color: #8A5A00; }
        span.cn-coinciden.nd { background: #EEF0F5; color: #6E6E73; }
        .cn-concerta { white-space: nowrap; }
        .cn-concerta .cn-prog { margin-right: 4px; }
        .cn-dep-resumen { display: flex; flex-wrap: wrap; gap: 8px 22px; align-items: center; background: #F2F5FA; border: 1px solid #E5E5EA; border-radius: 10px; padding: 10px 14px; margin-bottom: 14px; font-size: .88rem; }
        .cn-fila { cursor: pointer; }
        .cn-fila:hover td { background: #F5F8FC; }
        .cn-prog { display: inline-block; min-width: 54px; text-align: center; font-weight: 700; font-size: .8rem; border-radius: 99px; padding: 3px 10px; background: #FDF1D6; color: #8A5A00; }
        .cn-prog.ok { background: #E3F4EA; color: #1F8A4C; }
        .cn-abrir { border: 0; background: #E7ECF5; color: #143163; font-weight: 700; font-size: .8rem; border-radius: 8px; padding: 6px 12px; cursor: pointer; white-space: nowrap; }
        .cn-abrir:hover { background: #143163; color: #fff; }
        .cn-modal-tabla thead th { background: #E7ECF5; color: #5b6479; font-size: .72rem; letter-spacing: .05em; text-transform: uppercase; vertical-align: middle; }
        .cn-modal-tabla td { vertical-align: middle; }
        .cn-hecha { font-weight: 700; font-variant-numeric: tabular-nums; }
        .cn-hecha small { display: block; font-weight: 400; color: #6E6E73; max-width: 220px; white-space: normal; line-height: 1.25; }
        .cn-celda { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-width: 190px; }
        .cn-celda input { width: 90px; border: 1px solid #D2D2D7; border-radius: 8px; padding: 5px 8px; font-size: .85rem; }
        .cn-btn { border: 0; border-radius: 8px; padding: 6px 12px; font-size: .8rem; font-weight: 700; cursor: pointer; background: #143163; color: #fff; }
        .cn-btn.sec { background: #E7ECF5; color: #143163; }
        .cn-btn:disabled { opacity: .5; cursor: wait; }
        .cn-ok { display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: .8rem; color: #1F8A4C; background: #E3F4EA; border-radius: 99px; padding: 4px 10px; }
        .cn-pend { font-size: .8rem; color: #8A5A00; background: #FDF1D6; border-radius: 99px; padding: 4px 10px; font-weight: 600; }
        .cn-sin { color: #6E6E73; font-size: .82rem; }
        .cn-vacio { text-align: center; color: #6E6E73; padding: 24px; }
        .ev-nota { font-size: .76rem; color: #6E6E73; margin-top: 14px; line-height: 1.5; }

        .gestionado-indicador {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            background-color: #34C759;
            color: white;
            margin-left: 8px;
        }
        
        .gestionado-indicador i {
            font-size: 0.6rem;
            margin-right: 3px;
        }

        .seguimiento-indicador {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            background-color: #1D71B8;
            color: white;
            margin-left: 8px;
        }

        .seguimiento-indicador i {
            font-size: 0.6rem;
            margin-right: 3px;
        }

        /* ═══ ESTADO DE SOLICITUD (badge cuadrado con letra) ═══ */
        .estado-letra-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: #F2F2F7;
            border: 1px solid rgba(0,0,0,.08);
            font-weight: 800;
            font-size: 14px;
            cursor: help;
        }

        .estado-letra-c { color: #8E8E93; }
        .estado-letra-s { color: #E0A800; }
        .estado-letra-r { color: #FF3B30; }

        .semaforo-row {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .semaforo-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 800;
            color: white;
            background: #C7C7CC;
            cursor: default;
        }

        .semaforo-circle.activo {
            background: #34C759;
        }

        .semaforo-circle.clickable {
            cursor: pointer;
            box-shadow: 0 0 0 2px rgba(29,113,184,.45);
        }

        .semaforo-circle.clickable:hover {
            filter: brightness(1.08);
        }

        .semaforo-dependencia {
            margin-top: 5px;
            font-size: 11px;
            color: #8a95a0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140px;
        }

        .semaforo-circle.no-aplica {
            background: #FFCC00;
            color: #6b4e00;
        }

        .semaforo-circle.rechazado {
            background: #FF3B30;
        }

        /* ═══ ACCORDION INNER TEXT OVERRIDES ═══ */
        /* !important beats inline style on span/small inside accordion button */
        .accordion-button > div > span:first-child {
            font-size: 15px !important;
            font-weight: 700 !important;
        }
        .accordion-button > div > small {
            font-size: 12px !important;
            opacity: .82;
        }


        /* Container padding reduction */
        .container-fluid {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /* Accordion body padding */
        .accordion-body {
            padding: 20px !important;
        }

        /* Section sub-headers inside accordion */
        .mb-5 > .d-flex > h5 {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #1d1d1f !important;
        }

        /* === FILTRO DE LISTA === */
        .filtro-lista-bar {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            background: rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 12px;
            padding: 10px 16px;
            margin-bottom: 18px;
        }
        .filtro-lista-bar .filtro-label {
            font-size: 0.82rem; font-weight: 600; color: #6c757d; white-space: nowrap;
        }
        .filtro-tipo-select {
            border: 1px solid rgba(0,0,0,0.12); border-radius: 8px;
            padding: 5px 10px; font-size: 0.83rem; background: white; color: #1d1d1f; cursor: pointer;
        }
        .filtro-input-wrap { position: relative; min-width: 200px; max-width: 300px; }
        .filtro-texto-input {
            width: 100%; border: 1px solid rgba(0,0,0,0.12); border-radius: 8px;
            padding: 5px 10px; font-size: 0.83rem;
        }
        .filtro-sugerencias {
            display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
            background: white; border: 1px solid rgba(0,0,0,0.1); border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12); z-index: 9999; max-height: 220px; overflow-y: auto;
        }
        .filtro-sugerencias .sug-item {
            padding: 8px 14px; cursor: pointer; font-size: 0.83rem;
            border-bottom: 1px solid rgba(0,0,0,0.04);
        }
        .filtro-sugerencias .sug-item:last-child { border-bottom: none; }
        .filtro-sugerencias .sug-item:hover { background: rgba(29,113,184,0.08); }
        .filtro-resultado-badge {
            font-size: 0.75rem; padding: 2px 9px; border-radius: 10px;
            background: rgba(29,113,184,0.09); color: #1D71B8;
            border: 1px solid rgba(29,113,184,0.18);
        }

        /* ══════════════ Rediseño claro estilo Apple ══════════════ */
        .m144-head { padding: 22px 26px !important; }
        .m144-head-row { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 16px; }
        .m144-head-text { flex: 1 1 400px; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
        .m144-crumb { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #6E6E73; }
        .m144-crumb a { color: #0066CC; font-weight: 500; text-decoration: none; }
        .m144-crumb i { font-size: 9px; }
        .m144-title-row { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        .m144-head .header-title { margin: 0; font-size: 30px; letter-spacing: -.02em; }
        .m144-head .header-desc { margin: 0; }
        .m144-btn { display: inline-flex; align-items: center; gap: 8px; border: 0; cursor: pointer; min-height: 44px; padding: 0 18px;
                    border-radius: 12px; font-size: 15px; font-weight: 600; text-decoration: none; white-space: nowrap; }
        .m144-btn i { font-size: 13px; }
        .m144-btn-gray { background: #E9E9EE; color: #1D1D1F; }
        .m144-btn-gray:hover { background: #E0E0E5; color: #1D1D1F; }
        .m144-btn-primary { background: #1D71B8; color: #fff; }
        .m144-btn-primary:hover { background: #0068D9; color: #fff; }
        .m144-btn:focus-visible, .m144-more:focus-visible, .m144-estado:focus-visible { outline: 3px solid rgba(29,113,184,.45); outline-offset: 2px; }

        /* Pestañas principales: una fila, subrayado azul y conteo */
        .modulo-tabs-top { border-bottom: 1px solid #E0E0E5 !important; gap: 4px; flex-wrap: wrap; }
        .modulo-tabs-top .nav-link { border: 0 !important; border-bottom: 2px solid transparent !important; background: transparent !important;
                                     color: #3A3A3C !important; font-weight: 600; text-transform: none; padding: 12px 14px; margin-bottom: -1px; }
        .modulo-tabs-top .nav-link.active { color: #0066CC !important; border-bottom-color: #1D71B8 !important; }
        .modulo-tabs-top .nav-link:hover { color: #1D1D1F !important; }
        .m144-tab-count { display: inline-block; margin-left: 8px; font-size: 12px; font-weight: 600; padding: 1px 8px; border-radius: 999px; background: #E9E9EE; color: #6E6E73; }
        .modulo-tabs-top .nav-link.active .m144-tab-count { background: rgba(29,113,184,.12); color: #0066CC; }

        /* Panel sin banner oscuro */
        .m144-panel { padding: 4px 0 0; }
        .m144-panel-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .m144-panel-acciones { display: flex; flex-wrap: wrap; gap: 8px; }
        .m144-menu-acciones { border: 0; border-radius: 12px; padding: 6px; min-width: 240px; box-shadow: 0 8px 28px rgba(0,0,0,.16); }
        .m144-menu-acciones .dropdown-item { border-radius: 8px; font-size: 13px; font-weight: 600; padding: 8px 12px; display: flex; align-items: center; }
        .m144-menu-acciones .dropdown-header { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; }
        /* Plazo en una franja compacta */
        .countdown-container { display: flex !important; flex-wrap: wrap; align-items: center; justify-content: center; gap: 8px 16px; padding: 10px 14px !important; margin-top: 12px !important; }
        .countdown-container > div:first-child { margin: 0 !important; }
        .countdown-container .countdown-timer { gap: 8px !important; margin: 0 !important; }
        .countdown-container .countdown-box { padding: 4px 12px !important; min-width: 0 !important; display: flex; flex-direction: row; align-items: baseline; gap: 4px; }
        .countdown-container .countdown-number { font-size: 18px !important; }
        .countdown-container .countdown-label { font-size: 10px !important; }
        .m144-btn-tinted { background: rgba(29,113,184,.12) !important; color: #0066CC !important; text-decoration: none; }
        .m144-btn-tinted:hover { background: rgba(29,113,184,.2) !important; color: #0066CC !important; }
        .imp-resumen { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 8px; margin: 4px 0 12px; }
        .imp-resumen div { background: #F2F2F7; border-radius: 10px; padding: 10px; text-align: center; }
        .imp-resumen b { display: block; font-size: 1.3rem; }
        .imp-resumen small { color: #6E6E73; font-size: .75rem; }
        .imp-errores { max-height: 240px; overflow-y: auto; text-align: left; font-size: .82rem; background: #FFF4F2; border-radius: 10px; padding: 10px 12px; margin: 0; }
        .imp-errores li { margin: 3px 0 3px 14px; }
        .m144-panel-desc { margin: 0; font-size: 15px; color: #6E6E73; }

        /* Filtros */
        .filtro-lista-bar { background: transparent !important; border: 0 !important; padding: 0 !important; box-shadow: none !important; gap: 10px; }
        .filtro-lista-bar .filtro-tipo-select, .filtro-lista-bar .filtro-texto-input { background: #E3E3E8 !important; border: 0 !important; border-radius: 9px !important; font-size: 13px; font-weight: 600; color: #1D1D1F; }

        /* Borradores / Publicados / Cancelados como control segmentado */
        .lista-tabs { display: inline-flex !important; flex-wrap: wrap; gap: 2px; padding: 2px; border: 0 !important; border-radius: 9px; background: #E3E3E8; }
        .lista-tabs .nav-link { border: 0 !important; border-radius: 7px !important; background: transparent !important; color: #3A3A3C !important;
                                font-size: 13px; font-weight: 600; padding: 6px 14px; margin: 0 !important; }
        .lista-tabs .nav-link.active { background: #fff !important; color: #1D1D1F !important; box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 1px rgba(0,0,0,.04); }
        .lista-tabs .nav-link i { display: none; }
        .lista-tabs .badge { background: transparent !important; color: #6E6E73 !important; font-weight: 600; padding: 0 0 0 2px; }

        /* Lista como una sola tarjeta con separadores finos */
        .lista-container { background: #fff !important; border: 0 !important; border-radius: 18px !important; overflow: visible !important;
                           box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 6px 20px rgba(0,0,0,.04) !important; }
        .lista-header { background: #FAFAFC !important; border-bottom: 1px solid #F0F0F3 !important; border-radius: 18px 18px 0 0;
                        color: #6E6E73 !important; font-size: 12px !important; font-weight: 600 !important; text-transform: none !important; letter-spacing: 0 !important; }
        .lista-item { border-bottom: 1px solid #F0F0F3 !important; padding-top: 16px !important; padding-bottom: 16px !important; }
        .lista-item:hover { background: #FAFAFC !important; }
        .lista-item-titulo { color: #1D1D1F !important; font-size: 15px !important; font-weight: 600 !important; line-height: 1.35; }
        .lista-item-titulo.titulo-linea-excedida { color: #D70015 !important; }

        /* Estado en palabras */
        .m144-estado { display: inline-flex; align-items: center; gap: 6px; border: 0; cursor: pointer; font-size: 12px; font-weight: 600;
                       padding: 3px 10px; border-radius: 999px; white-space: nowrap; background: #F2F2F7; color: #6E6E73; }
        .m144-estado i { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .m144-estado.estado-letra-s { background: rgba(255,149,0,.14); color: #B25000; }
        .m144-estado.estado-letra-r { background: rgba(255,59,48,.10); color: #D70015; }

        /* Semáforo como pasos conectados */
        .semaforo-row { gap: 0 !important; }
        .semaforo-row .semaforo-circle { width: 24px; height: 24px; font-size: 11px; font-weight: 700; background: #F2F2F7; color: #8E8E93; position: relative; }
        .semaforo-row .semaforo-circle + .semaforo-circle { margin-left: 10px; }
        .semaforo-row .semaforo-circle + .semaforo-circle::before { content: ""; position: absolute; right: 100%; top: 50%; width: 10px; height: 2px; background: #E3E3E8; }
        .semaforo-row .semaforo-circle.activo { background: #34C759; color: #fff; }
        .semaforo-row .semaforo-circle.activo + .semaforo-circle::before { background: #34C759; }
        .semaforo-row .semaforo-circle.rechazado { background: #FF3B30; color: #fff; }
        .semaforo-row .semaforo-circle.no-aplica { background: #FFF4D6; color: #8A6100; }
        .semaforo-row .semaforo-circle.clickable { background: #fff; color: #B25000; box-shadow: inset 0 0 0 2px #FF9500; }
        .semaforo-dependencia { color: #6E6E73 !important; }

        /* Acciones: botones con tinte suave y menú "…" */
        .lista-item-actions { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .lista-item-actions .btn { width: 38px; height: 38px; padding: 0; border: 0 !important; border-radius: 10px !important;
                                   display: inline-grid; place-items: center; box-shadow: none !important; }
        .lista-item-actions .btn-warning, .lista-item-actions .btn-info { background: rgba(29,113,184,.12) !important; color: #0066CC !important; }
        .lista-item-actions .btn-success { background: rgba(52,199,89,.16) !important; color: #248A3D !important; }
        .lista-item-actions .btn-danger { background: rgba(255,59,48,.10) !important; color: #D70015 !important; }
        .lista-item-actions .btn:hover { filter: brightness(.96); }
        .lista-item-actions .badge.bg-warning { background: rgba(255,149,0,.14) !important; color: #B25000 !important; font-weight: 600; border-radius: 999px; padding: 5px 10px; }
        .m144-more { border: 0; cursor: pointer; width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; background: #F2F2F7; color: #3A3A3C; }
        .m144-more:hover, .m144-more[aria-expanded="true"] { background: #E3E3E8; }
        .m144-menu { min-width: 230px; padding: 6px; border: 0; border-radius: 14px; box-shadow: 0 0 0 1px rgba(0,0,0,.06), 0 12px 32px rgba(0,0,0,.14); }
        .m144-menu .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 8px; font-size: 14px; color: #1D1D1F; }
        .m144-menu .dropdown-item i { width: 16px; color: #6E6E73; }
        .m144-menu .m144-danger, .m144-menu .m144-danger i { color: #D70015; }
        .m144-menu .m144-ok, .m144-menu .m144-ok i { color: #248A3D; }
        .m144-menu .dropdown-header { font-size: 12px; color: #6E6E73; padding: 6px 10px 4px; }
        .m144-pendiente { display: inline-flex; align-items: center; gap: 6px; border: 0; cursor: pointer; font-size: 13px; font-weight: 600;
                          padding: 5px 12px; border-radius: 999px; background: rgba(255,149,0,.14); color: #B25000; }
        .m144-pendiente i { font-size: 10px; }
        .m144-pendiente:hover, .m144-pendiente[aria-expanded="true"] { background: rgba(255,149,0,.24); }
        .m144-pendiente:focus-visible { outline: 3px solid rgba(29,113,184,.45); outline-offset: 2px; }

        /* ── Celular ── */
        @media (max-width: 768px) {
            .m144-head { padding: 18px 16px !important; }
            .m144-head .header-title { font-size: 22px; }
            /* Pestañas largas: se deslizan de lado en vez de partirse en varias líneas */
            .modulo-tabs-top { flex-wrap: nowrap !important; overflow-x: auto; overflow-y: hidden; scrollbar-width: none; }
            .modulo-tabs-top::-webkit-scrollbar { display: none; }
            .modulo-tabs-top .nav-link { white-space: nowrap; font-size: 13px; padding: 10px 12px !important; }
            .lista-tabs { display: flex !important; flex-wrap: nowrap; overflow-x: auto; max-width: 100%; }
            .lista-tabs .nav-link { white-space: nowrap; }
            .filtro-lista-bar { flex-wrap: wrap; }
            .filtro-input-wrap { min-width: 0; max-width: none; flex: 1 1 100%; }
            .filtro-tipo-select { flex: 1 1 auto; }
            /* Los títulos de columna no aplican cuando cada fila se apila */
            .lista-header { display: none !important; }
            .lista-item { padding-left: 14px !important; padding-right: 14px !important; }
            .lista-item .row > [class*="col-"] { margin-bottom: 6px; }
            .ev-total { position: static; }
            .ev-motor { grid-template-columns: minmax(0, 1fr) 64px; }
            .ev-motor .ev-bar { grid-column: 1 / -1; grid-row: 2; }
            .modal-dialog { margin: 8px; }
        }
    </style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>
    <div class="container-fluid">
        <?php
        // Fechas vacías de MySQL (0000-00-00) se ven como "30/11/-0001": solo se muestran fechas reales
        $m144_fechaReal = function ($d) { return !empty($d) && (int)date('Y', strtotime($d)) > 1; };
        $m144_desc = trim((string)($formulario['descripcion'] ?? ''));
        ?>
        <div class="header-info m144-head">
            <div class="m144-head-row">
                <div class="m144-head-text">
                    <nav class="m144-crumb" aria-label="Ruta">
                        <a href="<?php echo $basePath; ?>/FOR-DE-144">FOR-DE-144</a>
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        <span>Plan de acción</span>
                    </nav>
                    <div class="m144-title-row">
                        <h1 class="header-title"><?php echo htmlspecialchars($formulario['titulo'] ?? ''); ?></h1>
                        <span class="estado-badge estado-<?php echo $estado_fechas['clase']; ?>">
                            <i class="fas fa-<?php echo $estado_fechas['valido'] ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                            <?php echo $estado_fechas['mensaje']; ?>
                        </span>
                    </div>
                    <?php if ($m144_desc !== ''): ?>
                    <p class="header-desc"><?php echo htmlspecialchars($m144_desc); ?></p>
                    <?php endif; ?>
                    <?php if ($m144_fechaReal($formulario['fecha_inicio'] ?? null) || $m144_fechaReal($formulario['fecha_cierre'] ?? null)): ?>
                    <div class="header-meta">
                        <?php if ($m144_fechaReal($formulario['fecha_inicio'] ?? null)): ?>
                        <span><i class="fas fa-play-circle me-1" style="color:#34C759;"></i>Inicio: <?php echo date('d/m/Y H:i', strtotime($formulario['fecha_inicio'])); ?></span>
                        <?php endif; ?>
                        <?php if ($m144_fechaReal($formulario['fecha_cierre'] ?? null)): ?>
                        <span><i class="fas fa-stop-circle me-1" style="color:#FF3B30;"></i>Cierre: <?php echo date('d/m/Y H:i', strtotime($formulario['fecha_cierre'])); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <a href="<?php echo $basePath; ?>/FOR-DE-144" class="m144-btn m144-btn-gray">
                    <i class="fas fa-chevron-left"></i>Volver
                </a>
            </div>

            <?php if ($estado_fechas['valido'] && $fecha_cierre): ?>
            <div class="countdown-container">
                <div style="text-align:center;font-size:12px;font-weight:600;color:#6e6e73;margin-bottom:10px;text-transform:uppercase;letter-spacing:.4px;">
                    <i class="fas fa-hourglass-half me-1" style="color:#1D71B8;"></i>Tiempo restante
                </div>
                <div class="countdown-timer" id="countdown-timer">
                    <div class="countdown-box"><div class="countdown-number" id="days">00</div><div class="countdown-label">Días</div></div>
                    <div class="countdown-box"><div class="countdown-number" id="hours">00</div><div class="countdown-label">Horas</div></div>
                    <div class="countdown-box"><div class="countdown-number" id="minutes">00</div><div class="countdown-label">Minutos</div></div>
                    <div class="countdown-box"><div class="countdown-number" id="seconds">00</div><div class="countdown-label">Segundos</div></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!$estado_fechas['valido']): ?>
        <div class="alert alert-warning text-center py-4 mb-4">
            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
            <h3>Formulario no disponible</h3>
            <p class="lead"><?php echo $estado_fechas['mensaje']; ?></p>
            <a href="<?php echo $basePath; ?>/FOR-DE-144" class="btn btn-primary mt-2">
                <i class="fas fa-arrow-left me-2"></i>Ver otros formularios
            </a>
        </div>
        <?php else: ?>

        <?php
        // ── Precalcular cuántos borradores son realmente visibles para el usuario actual ──
        // (la cascada de visibilidad por dependencia se aplica fila por fila más abajo; aquí se
        // replica la misma regla para que los números de las pestañas coincidan con lo que se ve en pantalla).
        if (!function_exists('m144_normalizarRol')) {
            function m144_normalizarRol($s) {
                $s = mb_strtolower(trim((string)$s), 'UTF-8');
                return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
            }
        }
        // Cumplido / meta del año de la fila, con las mismas reglas por tipo de medición que el modal de seguimiento
        if (!function_exists('m144_cumplidoMetaHtml')) {
            function m144_cumplidoMetaHtml($row) {
                // Igual que parseFloat en el modal: toma el número inicial aunque traiga texto detrás ("20.00%" → 20)
                $num = function ($v) {
                    return preg_match('/^\s*([-+]?(?:\d+\.?\d*|\.\d+))/', (string)$v, $m) ? (float)$m[1] : null;
                };
                $fmt = function ($n) { return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.'); };
                $tipo = $row['tipo_medicion'] ?? '';
                $lb = $num($row['linea_base_meta'] ?? null);
                $m1 = $num($row['meta_s1'] ?? null);
                $m2 = $num($row['meta_s2'] ?? null);
                $s1 = $num($row['semestre1_seguimiento'] ?? null);
                $s2 = $num($row['semestre2_seguimiento'] ?? null);

                $meta = null; $cumplido = 0.0;
                if ($tipo === 'Acumulado') {
                    $meta = ($lb ?? 0) + ($m1 ?? 0) + ($m2 ?? 0);
                    // Sin ningún semestre reportado, la línea base sola no cuenta como avance
                    $cumplido = ($s1 === null && $s2 === null) ? 0.0 : ($lb ?? 0) + ($s1 ?? 0) + ($s2 ?? 0);
                } elseif ($tipo === 'Nuevo gestionado durante la vigencia') {
                    $meta = ($m1 ?? 0) + ($m2 ?? 0);
                    $cumplido = ($s1 ?? 0) + ($s2 ?? 0);
                } elseif ($tipo === 'Promedio') {
                    $metas = array_filter([$m1, $m2], function ($v) { return $v !== null; });
                    $segs  = array_filter([$s1, $s2], function ($v) { return $v !== null; });
                    $meta = $metas ? array_sum($metas) / count($metas) : 0.0;
                    $cumplido = $segs ? array_sum($segs) / count($segs) : 0.0;
                } elseif ($tipo === 'Último valor reportado') {
                    $meta = $m2 ?? $m1 ?? $lb;
                    $cumplido = $s2 ?? $s1 ?? 0.0;
                } elseif ($tipo === 'Límite') {
                    $meta = $m2 ?? $m1 ?? $lb;
                    $cumplido = $s2 ?? 0.0;
                }
                $html = '';
                if ($meta !== null) {
                    $html .= '<div class="cumplido-meta" title="Cumplido ' . $fmt($cumplido) . ' de una meta anual de ' . $fmt($meta) . '">'
                           . '<i class="fas fa-bullseye"></i><strong>' . $fmt($cumplido) . '</strong><span>/ ' . $fmt($meta) . '</span></div>';
                }
                if (trim($tipo) !== '') {
                    $html .= '<div class="cumplido-medicion" title="Tipo de medición: ' . htmlspecialchars($tipo) . '">' . htmlspecialchars($tipo) . '</div>';
                }
                return $html !== '' ? $html : '<span class="lista-item-autor sin-datos">—</span>';
            }
        }
        $semaforoRolNivel = [
            'gestor de metas' => 1,
            'gestor de metas de responsable de linea' => 1,
            'lider de meta' => 2,
            'gestor de metas de sub-admin' => 2,
            'responsable de linea' => 3,
            'sub administrador' => 4,
        ];
        $miRolNormalizado = m144_normalizarRol($_SESSION['usuario_rol'] ?? '');
        $esSuperAdminSem  = (int)($_SESSION['usuario_id'] ?? 0) === 1;
        $viewerId         = (int)($_SESSION['usuario_id'] ?? 0);
        // Dependencias del usuario: la principal y las de "Responsable de" (en Usuarios)
        $viewerCargoNombre = $viewer_dependencias ?? ($viewer_cargo_nombre ?? '');
        // Solo un administrador publica en bloque los borradores cargados por plantilla
        $esAdminPlan = (int)($_SESSION['usuario_id'] ?? 0) === 1 || in_array(m144_normalizarRol($_SESSION['usuario_rol'] ?? ''), ['admin', 'administrador'], true);
        $puedeVerTodoElSemaforo = $esSuperAdminSem || in_array($miRolNormalizado, ['administrador', 'sub administrador'], true);

        // ¿La dependencia del usuario es una de las responsables del indicador?
        // "responsable_formulacion" guarda los nombres de dependencia separados por ", ".
        if (!function_exists('m144_esDependenciaResponsable')) {
            // $cargoNombre puede ser un nombre o una lista de nombres (todas las dependencias del usuario)
            function m144_esDependenciaResponsable($responsables, $cargoNombre) {
                $lista = ', ' . mb_strtolower(trim((string)$responsables), 'UTF-8') . ', ';
                foreach ((array)$cargoNombre as $nombre) {
                    $nombre = mb_strtolower(trim((string)$nombre), 'UTF-8');
                    if ($nombre !== '' && mb_strpos($lista, ', ' . $nombre . ', ', 0, 'UTF-8') !== false) return true;
                }
                return false;
            }
        }

        // Ven el indicador: admin / sub administrador, quien lo creó y todos los usuarios
        // de las dependencias responsables (sin importar quién lo haya creado).
        if (!function_exists('m144_filtrarBorradoresVisibles')) {
            function m144_filtrarBorradoresVisibles($borradores, $viewerId, $viewerCargoNombre, $puedeVerTodoElSemaforo, $facultadesPorItem = [], $viewerFacultades = []) {
                $visibles = [];
                foreach ($borradores as $b) {
                    $esCreador = $viewerId > 0 && $viewerId === (int)($b['creado_por'] ?? 0);
                    // Alguna facultad del usuario tiene valor (distribución > 0) en este indicador
                    $esFacultadConValor = $viewerFacultades && array_intersect($viewerFacultades, $facultadesPorItem[(int)$b['id']] ?? []);
                    if ($puedeVerTodoElSemaforo || $esCreador || $esFacultadConValor
                        || m144_esDependenciaResponsable($b['responsable_formulacion'] ?? '', $viewerCargoNombre)) {
                        $visibles[] = $b;
                    }
                }
                return $visibles;
            }
        }
        // Ojo: se guarda aparte (no se pisa $datos_modulos[...]['borradores']) porque ese arreglo
        // completo también lo usan, sin filtrar, las pestañas de Facultades y Evaluación Líneas.
        // Publicados y cancelados siguen la misma regla: cada usuario ve su formulación y su seguimiento.
        $borradoresVisiblesPorModulo = [];
        $publicadosVisiblesPorModulo = [];
        $canceladosVisiblesPorModulo = [];
        foreach ($datos_modulos as $_dmKey => $_dmModulo) {
            // Solo en Seguimiento las facultades nombradas en la distribución ven el indicador
            $_facPorItem = $_dmKey === 'seguimiento' ? ($facultades_por_item ?? []) : [];
            $_viewerFac  = $_dmKey === 'seguimiento' ? ($viewer_facultades ?? []) : [];
            $borradoresVisiblesPorModulo[$_dmKey] = m144_filtrarBorradoresVisibles(
                $_dmModulo['borradores'], $viewerId, $viewerCargoNombre, $puedeVerTodoElSemaforo, $_facPorItem, $_viewerFac
            );
            $publicadosVisiblesPorModulo[$_dmKey] = m144_filtrarBorradoresVisibles(
                $_dmModulo['publicados'], $viewerId, $viewerCargoNombre, $puedeVerTodoElSemaforo, $_facPorItem, $_viewerFac
            );
            $canceladosVisiblesPorModulo[$_dmKey] = m144_filtrarBorradoresVisibles(
                $_dmModulo['cancelados'], $viewerId, $viewerCargoNombre, $puedeVerTodoElSemaforo
            );
        }
        ?>

        <!-- Pestañas principales -->
        <ul class="nav nav-tabs modulo-tabs-top mb-4" id="moduloTabsTop" role="tablist">
            <?php $primer_modulo_tab = true; ?>
            <?php foreach ($datos_modulos as $key => $modulo): ?>
            <?php $modulo['publicados'] = $publicadosVisiblesPorModulo[$key]; $modulo['cancelados'] = $canceladosVisiblesPorModulo[$key]; ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $primer_modulo_tab ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#panelModulo-<?php echo $key; ?>" type="button" role="tab">
                    <i class="fas <?php echo $modulo['config']['icono']; ?> me-2"></i><?php echo $modulo['config']['nombre']; ?>
                    <span class="m144-tab-count" title="Borradores: <?php echo count($borradoresVisiblesPorModulo[$key]); ?> · Aprobados: <?php echo count($modulo['publicados']); ?> · Cancelados: <?php echo count($modulo['cancelados']); ?>"><?php echo count($borradoresVisiblesPorModulo[$key]) + count($modulo['publicados']); ?></span>
                </button>
            </li>
            <?php $primer_modulo_tab = false; ?>
            <?php endforeach; ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#panelConcertacion" type="button" role="tab">
                    <i class="fas fa-handshake me-2"></i>CONCERTACIONES
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#panelEvaluacion" type="button" role="tab">
                    <i class="fas fa-chart-pie me-2"></i>EVALUACIÓN LÍNEAS
                </button>
            </li>
        </ul>

        <div class="tab-content" id="moduloTabsTopContent">
            <?php $primer_modulo = true; ?>
            <?php foreach ($datos_modulos as $key => $modulo): ?>
            <?php $modulo['publicados'] = $publicadosVisiblesPorModulo[$key]; $modulo['cancelados'] = $canceladosVisiblesPorModulo[$key]; ?>
            <div class="tab-pane fade <?php echo $primer_modulo ? 'show active' : ''; ?>" id="panelModulo-<?php echo $key; ?>" role="tabpanel">
                <div class="m144-panel">

                        <div class="m144-panel-head">
                            <p class="m144-panel-desc"><?php echo $modulo['config']['descripcion']; ?></p>
                            <div class="m144-panel-acciones">
                                <?php $puedePublicarLote = $key === 'formulacion' && $esAdminPlan && count($borradoresVisiblesPorModulo[$key]) > 0; ?>
                                <?php $puedeDevolverTodos = (int)($_SESSION['usuario_id'] ?? 0) === 1 && count($modulo['publicados']) > 0; ?>
                                <div class="dropdown">
                                    <button type="button" class="m144-btn m144-btn-tinted dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h"></i>Más acciones
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end m144-menu-acciones">
                                        <li><a class="dropdown-item" href="<?php echo $basePath; ?>/modulo144/exportarPlantilla?id=<?php echo (int)$formulario['id']; ?>"
                                               title="Descarga la plantilla de Excel (hojas Formulación y Seguimiento) para llenarla y subirla">
                                            <i class="fas fa-file-download me-2"></i>Exportar plantilla</a></li>
                                        <li><button type="button" class="dropdown-item" onclick="abrirModalImportarPlantilla()" title="Sube la plantilla de Excel llena">
                                            <i class="fas fa-file-upload me-2"></i>Importar plantilla</button></li>
                                        <?php if ($puedePublicarLote || $puedeDevolverTodos): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header">Solo administrador</h6></li>
                                        <?php endif; ?>
                                        <?php if ($puedePublicarLote): ?>
                                        <li><button type="button" class="dropdown-item text-success" onclick="publicarBorradores(<?php echo count($borradoresVisiblesPorModulo[$key]); ?>)" title="Publica todos los borradores completos sin pasar por el semáforo">
                                            <i class="fas fa-bullhorn me-2"></i>Publicar borradores</button></li>
                                        <?php endif; ?>
                                        <?php if ($puedeDevolverTodos): ?>
                                        <li><button type="button" class="dropdown-item text-danger" onclick="devolverTodosABorrador('<?php echo $key; ?>', <?php echo count($modulo['publicados']); ?>)" title="Devuelve todos los publicados de este módulo a borrador">
                                            <i class="fas fa-undo me-2"></i>Devolver todos a borrador</button></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <?php if ($key === 'formulacion'): ?>
                                <button type="button" class="m144-btn m144-btn-primary" onclick="abrirModalNuevoBorrador('<?php echo $key; ?>')">
                                    <i class="fas fa-plus"></i>Nuevo indicador
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- FILTRO DE LISTA -->
                        <?php $pref_key = $filter_preferences[$key] ?? ['tipo_filtro' => 'todos', 'valor_filtro' => null]; ?>
                        <div class="filtro-lista-bar" id="filtroBar-<?php echo $key; ?>">
                            <span class="filtro-label"><i class="fas fa-filter me-1"></i>Ver:</span>
                            <select class="filtro-tipo-select" id="filtroTipo-<?php echo $key; ?>"
                                    onchange="onFiltroTipoChange('<?php echo $key; ?>')">
                                <option value="todos"       <?php echo $pref_key['tipo_filtro']==='todos'       ? 'selected':''; ?>>Todos</option>
                                <option value="mio"         <?php echo $pref_key['tipo_filtro']==='mio'         ? 'selected':''; ?>>Mi usuario</option>
                                <option value="dependencia" <?php echo $pref_key['tipo_filtro']==='dependencia' ? 'selected':''; ?>>Por dependencia</option>
                                <option value="persona"     <?php echo $pref_key['tipo_filtro']==='persona'     ? 'selected':''; ?>>Por persona</option>
                                <option value="nombre"      <?php echo $pref_key['tipo_filtro']==='nombre'      ? 'selected':''; ?>>Por indicador</option>
                                <option value="con_seguimiento" <?php echo $pref_key['tipo_filtro']==='con_seguimiento' ? 'selected':''; ?>>Con seguimiento</option>
                                <option value="sin_seguimiento" <?php echo $pref_key['tipo_filtro']==='sin_seguimiento' ? 'selected':''; ?>>Sin seguimiento</option>
                            </select>
                            <select class="filtro-tipo-select" id="filtroLinea-<?php echo $key; ?>"
                                    onchange="onFiltroLineaChange('<?php echo $key; ?>')" style="display:none;">
                                <option value="">Línea: Todas</option>
                            </select>
                            <select class="filtro-tipo-select" id="filtroMotor-<?php echo $key; ?>"
                                    onchange="onFiltroMotorChange('<?php echo $key; ?>')" style="display:none;">
                                <option value="">Motor: Todos</option>
                            </select>
                            <select class="filtro-tipo-select" id="filtroProyecto-<?php echo $key; ?>"
                                    onchange="onFiltroProyectoChange('<?php echo $key; ?>')" style="display:none;">
                                <option value="">Proyecto: Todos</option>
                            </select>
                            <div class="filtro-input-wrap" id="filtroInputWrap-<?php echo $key; ?>"
                                 style="display:<?php echo in_array($pref_key['tipo_filtro'],['dependencia','persona','nombre']) ? 'block':'none'; ?>">
                                <input type="text" class="filtro-texto-input"
                                       id="filtroTexto-<?php echo $key; ?>"
                                       autocomplete="off"
                                       placeholder="<?php echo $pref_key['tipo_filtro']==='persona' ? 'Buscar persona...' : ($pref_key['tipo_filtro']==='nombre' ? 'Buscar por indicador...' : 'Buscar dependencia...'); ?>"
                                       value="<?php echo htmlspecialchars($pref_key['valor_filtro'] ?? ''); ?>"
                                       oninput="onFiltroTextoInput('<?php echo $key; ?>')"
                                       onfocus="mostrarSugerencias('<?php echo $key; ?>')"
                                       onblur="setTimeout(function(){ ocultarSugerencias('<?php echo $key; ?>') }, 200)">
                                <div class="filtro-sugerencias" id="filtroSugerencias-<?php echo $key; ?>"></div>
                            </div>
                            <span class="filtro-resultado-badge" id="filtroResultado-<?php echo $key; ?>" style="display:none;"></span>
                        </div>

                        <ul class="nav nav-tabs lista-tabs mb-4" id="listaTabs-<?php echo $key; ?>" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabBorradores-<?php echo $key; ?>" type="button" role="tab">
                                    <i class="fas fa-pen-fancy me-1"></i>Borradores <span class="badge bg-secondary ms-1"><?php echo count($borradoresVisiblesPorModulo[$key]); ?></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPublicados-<?php echo $key; ?>" type="button" role="tab">
                                    <i class="fas fa-check-circle me-1"></i>Aprobados <span class="badge bg-success ms-1"><?php echo count($modulo['publicados']); ?></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCancelados-<?php echo $key; ?>" type="button" role="tab">
                                    <i class="fas fa-times-circle me-1"></i>Cancelados <span class="badge bg-danger ms-1"><?php echo count($modulo['cancelados']); ?></span>
                                </button>
                            </li>
</ul>

                        <div class="tab-content">
                        <!-- BORRADORES -->
                        <div class="tab-pane fade show active" id="tabBorradores-<?php echo $key; ?>" role="tabpanel">
                            <?php if (count($borradoresVisiblesPorModulo[$key]) > 0): ?>
                                <div class="lista-container">
                                    <div class="lista-header">
                                        <div class="row">
                                            <div class="col-md-3">Nombre</div>
                                            <div class="col-md-2">L - M - P</div>
                                            <div class="col-md-2">Meta / Medición</div>
                                            <div class="col-md-1">Estado</div>
                                            <div class="col-md-2">Semáforo</div>
                                            <div class="col-md-2">Acciones</div>
                                        </div>
                                    </div>
                                    <?php
                                    // Pre-calcular suma de ponderacion_actividades por L-M-P
                                    $pond_por_linea_b = [];
                                    foreach ($borradoresVisiblesPorModulo[$key] as $_b) {
                                        $lk = ($_b['linea_codigo'] ?? '__') . '|' . ($_b['motor_id_num'] ?? '__') . '|' . ($_b['proyecto_codigo'] ?? '__');
                                        $pond_por_linea_b[$lk] = ($pond_por_linea_b[$lk] ?? 0) + (float)($_b['ponderacion_actividades'] ?? 0);
                                    }
                                    $solEstadoInfo = [
                                        0 => ['letra' => 'C', 'clase' => 'estado-letra-c', 'titulo' => 'Construcción'],
                                        1 => ['letra' => 'S', 'clase' => 'estado-letra-s', 'titulo' => 'Solicitado'],
                                        2 => ['letra' => 'R', 'clase' => 'estado-letra-r', 'titulo' => 'Rechazado'],
                                    ];
                                    $semaforoEtapas = [
                                        1 => ['letra' => 'G', 'rol' => 'gestor de metas',       'titulo' => 'Gestor de Metas'],
                                        2 => ['letra' => 'L', 'rol' => 'lider de meta',          'titulo' => 'Líder de Metas'],
                                        3 => ['letra' => 'V', 'rol' => 'responsable de linea',   'titulo' => 'Vicerrectoría'],
                                        4 => ['letra' => 'P', 'rol' => 'sub administrador',      'titulo' => 'Oficina de Planeación'],
                                    ];
                                    $semaforoRolNivel = [
                                        'gestor de metas' => 1,
                                        'gestor de metas de responsable de linea' => 1,
                                        'lider de meta' => 2,
                                        'gestor de metas de sub-admin' => 2,
                                        'responsable de linea' => 3,
                                        'sub administrador' => 4,
                                    ];
                                    if (!function_exists('m144_normalizarRol')) {
                                        function m144_normalizarRol($s) {
                                            $s = mb_strtolower(trim((string)$s), 'UTF-8');
                                            return strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
                                        }
                                    }
                                    $miRolNormalizado = m144_normalizarRol($_SESSION['usuario_rol'] ?? '');
                                    $esSuperAdminSem  = (int)($_SESSION['usuario_id'] ?? 0) === 1;
                                    $campoSemaforo    = $modulo['config']['campo_semaforo'];
                                    $campoSemaforoRechazo = $modulo['config']['campo_semaforo_rechazo'];
                                    $viewerId         = (int)($_SESSION['usuario_id'] ?? 0);
                                    // Administrador / Sub Administrador / Súper Admin ven todo el recorrido, en cualquier dependencia
                                    $puedeVerTodoElSemaforo = $esSuperAdminSem || in_array($miRolNormalizado, ['administrador', 'sub administrador'], true);
                                    ?>
                                    <?php foreach ($borradoresVisiblesPorModulo[$key] as $borrador):
                                        $l   = !empty($borrador['linea_codigo'])    ? $borrador['linea_codigo']    : null;
                                        $m   = !empty($borrador['motor_codigo'])    ? $borrador['motor_codigo']    : null;
                                        $p   = !empty($borrador['proyecto_codigo']) ? $borrador['proyecto_codigo'] : null;
                                        $lmp = array_filter([$l, $m, $p]);
                                        $lmp_key_b = ($l ?? '__') . '|' . ($borrador['motor_id_num'] ?? '__') . '|' . ($borrador['proyecto_codigo'] ?? '__');
                                        $suma_linea = $pond_por_linea_b[$lmp_key_b] ?? 0;
                                        $linea_completa = ($suma_linea >= 99.99 && $suma_linea <= 100.01);
                                        $linea_excedida = $suma_linea > 100.01;
                                        $tiene_seg_b = !empty($borrador['fecha_seguimiento']) || !empty($borrador['porcentaje_avance']) || !empty($borrador['indicador']);
                                        $campoSolicitud = $modulo['config']['campo_solicitud'];
                                        $solEstado = (int)($borrador[$campoSolicitud] ?? 0);
                                        $solInfo = $solEstadoInfo[$solEstado] ?? $solEstadoInfo[0];
                                        $rechazoEtapa = (int)($borrador[$campoSemaforoRechazo] ?? 0);
                                        $creadorRolNorm = m144_normalizarRol($borrador['creado_por_rol'] ?? '');
                                        $creadorNivel = $semaforoRolNivel[$creadorRolNorm] ?? 0;
                                        // Si ya fue solicitado, la etapa "visible" nunca puede ser menor al nivel del creador
                                        // (autocorrige registros cuyo avance quedó desincronizado en la base de datos)
                                        $etapaActual = (int)($borrador[$campoSemaforo] ?? 0);
                                        if ($solEstado === 1 && $etapaActual < $creadorNivel) {
                                            $etapaActual = $creadorNivel;
                                        }

                                        // La visibilidad ya viene filtrada (m144_filtrarBorradoresVisibles): por dependencia responsable
                                        $esCreadorFila = $viewerId > 0 && $viewerId === (int)($borrador['creado_por'] ?? 0);
                                        // Editan y solicitan aprobación: quien lo creó y los gestores de la dependencia responsable.
                                        // Líder de meta y responsable de línea solo ven y aprueban/rechazan.
                                        $puedeGestionarFila = $esCreadorFila || (
                                            ($semaforoRolNivel[$miRolNormalizado] ?? 0) <= 1
                                            && m144_esDependenciaResponsable($borrador['responsable_formulacion'] ?? '', $viewerCargoNombre)
                                        );
                                        // Visible solo porque su facultad tiene distribución: registra su seguimiento, pero no aprueba etapas
                                        $soloPorFacultadFila = !$puedeVerTodoElSemaforo && !$esCreadorFila
                                            && !m144_esDependenciaResponsable($borrador['responsable_formulacion'] ?? '', $viewerCargoNombre);
                                    ?>
                                    <div class="lista-item" data-item-id="<?php echo $borrador['id']; ?>" data-ponderacion="<?php echo (float)($borrador['ponderacion_actividades'] ?? 0); ?>" data-linea-item="<?php echo htmlspecialchars($l ?? ''); ?>" data-motor-item="<?php echo htmlspecialchars($borrador['motor_id_num'] ?? ''); ?>" data-proyecto-item="<?php echo htmlspecialchars($p ?? ''); ?>" data-modulo="<?php echo $key; ?>" data-creado-por="<?php echo (int)($borrador['creado_por'] ?? 0); ?>" data-creado-por-nombre="<?php echo htmlspecialchars($borrador['creado_por_nombre'] ?? ''); ?>" data-cargo-id="<?php echo (int)($borrador['creado_por_cargo_id'] ?? 0); ?>" data-cargo-nombre="<?php echo htmlspecialchars($borrador['creado_por_cargo_nombre'] ?? ''); ?>" data-responsable="<?php echo htmlspecialchars($borrador['responsable_formulacion'] ?? ''); ?>" data-linea-filtro="<?php echo htmlspecialchars($borrador['linea_estrategica'] ?? ''); ?>" data-motor-filtro="<?php echo htmlspecialchars($borrador['motor_desarrollo'] ?? ''); ?>" data-proyecto-filtro="<?php echo htmlspecialchars($borrador['proyecto'] ?? ''); ?>" data-linea-codigo="<?php echo htmlspecialchars($l ?? ''); ?>" data-motor-codigo="<?php echo htmlspecialchars($m ?? ''); ?>" data-proyecto-codigo="<?php echo htmlspecialchars($p ?? ''); ?>" data-nombre-borrador="<?php echo htmlspecialchars(strtolower($borrador['nombre_borrador'] ?? '')); ?>" data-tiene-seguimiento="<?php echo $tiene_seg_b ? '1' : '0'; ?>">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-3">
                                                <div class="lista-item-titulo <?php echo $linea_completa ? 'titulo-linea-completa' : ($linea_excedida ? 'titulo-linea-excedida' : ''); ?>">
                                                    <?php echo htmlspecialchars(!empty(trim($borrador['formula_medicion'] ?? '')) ? $borrador['formula_medicion'] : $borrador['nombre_borrador']); ?>
                                                    <?php if ($borrador['gestionado_facultades'] == 1): ?>
                                                    <span class="gestionado-indicador"><i class="fas fa-check-circle"></i> Gestionado</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($borrador['fecha_seguimiento']) || !empty($borrador['porcentaje_avance']) || !empty($borrador['indicador'])): ?>
                                                    <span class="seguimiento-indicador"><i class="fas fa-check-circle"></i> Seguimiento</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="lista-item-sub">
                                                    <?php if (!empty($borrador['anio'])): ?>
                                                    <i class="fas fa-calendar me-1"></i> Año: <?php echo $borrador['anio']; ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($borrador['fecha_creacion'])): ?>
                                                    <span class="ms-2"><i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y H:i', strtotime($borrador['fecha_creacion'])); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <?php if (!empty($lmp)): ?>
                                                <span class="lmp-badge <?php echo $linea_completa ? 'lmp-completo' : ($linea_excedida ? 'lmp-excedido' : ''); ?>"
                                                      data-linea="<?php echo htmlspecialchars($l ?? ''); ?>"
                                                      title="Ponderación línea: <?php echo number_format($suma_linea,2); ?> / 100<?php echo $linea_excedida ? ' ⚠ Excede el 100%' : ''; ?>">
                                                    <i class="fas fa-sitemap"></i><?php echo implode(' - ', $lmp); ?>
                                                    <small class="lmp-suma<?php echo ((float)($borrador['ponderacion_actividades'] ?? 0)) <= 0 ? ' sin-aporte' : ''; ?>"><?php echo number_format($suma_linea,1); ?></small>
                                                </span>
                                                <?php else: ?>
                                                <span class="lmp-badge sin-datos">—</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2">
                                                <?php echo m144_cumplidoMetaHtml($borrador); ?>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="m144-estado <?php echo $solInfo['clase']; ?>" title="<?php echo $solInfo['titulo']; ?> — clic para ver historial" onclick="verHistorialEstado('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)"><i></i><?php echo $solInfo['titulo']; ?></button>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="semaforo-row">
                                                    <?php
                                                    $puedeRechazarEtapa = false;
                                                    foreach ($semaforoEtapas as $etapaNum => $etapaInfo):
                                                        $noAplica = $etapaNum <= $creadorNivel;
                                                        $rechazadaAqui = $etapaNum === $rechazoEtapa;
                                                        $activo = $etapaNum <= $etapaActual;
                                                        $esSiguiente = $etapaNum === $etapaActual + 1;
                                                        $puedeAprobar = !$noAplica && !$soloPorFacultadFila && $esSiguiente && (
                                                            m144_normalizarRol($etapaInfo['rol']) === $miRolNormalizado
                                                            || (($esSuperAdminSem || in_array($miRolNormalizado, ['administrador', 'sub administrador'], true)) && $etapaNum === 4)
                                                            // El superadministrador puede aprobar cualquier etapa en nombre del rol que corresponde,
                                                            // una vez el indicador fue enviado a aprobación
                                                            || ($esSuperAdminSem && $solEstado === 1)
                                                        );
                                                        if ($puedeAprobar) $puedeRechazarEtapa = true;
                                                        $clases = 'semaforo-circle';
                                                        if ($rechazadaAqui) $clases .= ' rechazado';
                                                        elseif ($noAplica) $clases .= ' no-aplica';
                                                        elseif ($activo) $clases .= ' activo';
                                                        if ($puedeAprobar) $clases .= ' clickable';
                                                        $tituloCirculo = $etapaInfo['titulo'];
                                                        if ($rechazadaAqui) $tituloCirculo .= ' — Rechazó';
                                                        elseif ($noAplica) $tituloCirculo .= ' — No aplica (creado por un rol superior)';
                                                        elseif ($activo) $tituloCirculo .= ' — Aprobado';
                                                    ?>
                                                    <span class="<?php echo $clases; ?>"
                                                          title="<?php echo htmlspecialchars($tituloCirculo); ?>"
                                                          <?php if ($puedeAprobar): ?>
                                                          onclick="avanzarSemaforo('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, <?php echo $etapaActual; ?>)"
                                                          <?php endif; ?>><?php echo $etapaInfo['letra']; ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php $dependenciaFila = trim($borrador['responsable_formulacion'] ?? '') ?: ($borrador['creado_por_cargo_nombre'] ?? ''); ?>
                                                <?php if ($dependenciaFila !== ''): ?>
                                                <div class="semaforo-dependencia"><?php echo htmlspecialchars($dependenciaFila); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="lista-item-actions">
                                                    <?php if (!$puedeGestionarFila): ?>
                                                    <button class="btn btn-sm btn-info" onclick="verBorradorSoloLectura('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)" title="Ver (solo lectura)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php
                                                    // Enviado al Líder de Metas (siguiente etapa = L): los líderes pueden editarlo además de aprobar o rechazar
                                                    $puedeEditarComoLider = $solEstado === 1 && $puedeRechazarEtapa && ($etapaActual + 1) === 2
                                                        && $miRolNormalizado === m144_normalizarRol($semaforoEtapas[2]['rol']);
                                                    if ($puedeEditarComoLider): ?>
                                                    <button class="btn btn-sm btn-warning" onclick="editarBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)" title="Editar (enviado al Líder de Metas)" aria-label="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if ($puedeRechazarEtapa):
                                                        $esEtapaFinalAdmin = ($etapaActual + 1) === 4;
                                                    ?>
                                                    <button class="btn btn-sm btn-success" onclick="avanzarSemaforo('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, <?php echo $etapaActual; ?>)" title="<?php echo $esEtapaFinalAdmin ? 'Publicar' : 'Aprobar'; ?>">
                                                        <i class="fas fa-<?php echo $esEtapaFinalAdmin ? 'globe' : 'check'; ?>"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="rechazarSemaforo('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, <?php echo $etapaActual; ?>)" title="Rechazar y devolver al creador">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php elseif ($solEstado === 1 && $puedeRechazarEtapa):
                                                        // El creador es el superadministrador: "Pendiente" abre las opciones para aprobar en nombre de la etapa
                                                        $esEtapaFinalAdmin = ($etapaActual + 1) === 4;
                                                        $etapaPendiente = $semaforoEtapas[$etapaActual + 1]['titulo'] ?? '';
                                                    ?>
                                                    <div class="dropdown">
                                                        <button type="button" class="m144-pendiente" data-bs-toggle="dropdown" aria-expanded="false"
                                                                title="Pendiente de <?php echo htmlspecialchars($etapaPendiente); ?>">
                                                            Pendiente <i class="fas fa-chevron-down"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end m144-menu">
                                                            <li><h6 class="dropdown-header">Espera a <?php echo htmlspecialchars($etapaPendiente); ?></h6></li>
                                                            <li><button type="button" class="dropdown-item" onclick="verBorradorSoloLectura('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)"><i class="fas fa-eye"></i>Ver</button></li>
                                                            <li><button type="button" class="dropdown-item m144-ok" onclick="avanzarSemaforo('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, <?php echo $etapaActual; ?>)"><i class="fas fa-<?php echo $esEtapaFinalAdmin ? 'globe' : 'check'; ?>"></i><?php echo $esEtapaFinalAdmin ? 'Aceptar y publicar' : 'Aceptar'; ?></button></li>
                                                            <?php if ($key === 'formulacion' && !empty($esAdminPlan)): ?>
                                                            <li><button type="button" class="dropdown-item m144-ok" onclick="publicarIndicador(<?php echo (int)$borrador['id']; ?>)" title="Publica este indicador sin pasar por las etapas de aprobación"><i class="fas fa-globe"></i>Publicar</button></li>
                                                            <?php endif; ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item m144-danger" onclick="rechazarSemaforo('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, <?php echo $etapaActual; ?>)"><i class="fas fa-times"></i>Rechazar</button></li>
                                                        </ul>
                                                    </div>
                                                    <?php elseif ($solEstado === 1): ?>
                                                    <span class="badge bg-warning text-dark align-self-center">Pendiente</span>
                                                    <?php else: ?>
                                                    <button class="btn btn-sm btn-warning" onclick="editarBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)" title="Editar" aria-label="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-success" onclick="cambiarSolicitudEstado('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 1)" title="Solicitar aprobación" aria-label="Solicitar aprobación">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                    <?php if ($key === 'formulacion'): ?>
                                                    <div class="dropdown">
                                                        <button type="button" class="m144-more" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Más acciones">
                                                            <i class="fas fa-ellipsis-h"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end m144-menu">
                                                            <li><button type="button" class="dropdown-item" onclick="abrirModalDuplicar('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, '<?php echo htmlspecialchars($borrador['nombre_borrador']); ?>')"><i class="fas fa-copy"></i>Duplicar</button></li>
                                                            <li><button type="button" class="dropdown-item" onclick="verHistorialEstado('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)"><i class="fas fa-history"></i>Ver historial de aprobación</button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item m144-danger" onclick="cambiarEstadoBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 1)"><i class="fas fa-ban"></i>Cancelar indicador</button></li>
                                                        </ul>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state p-4">
                                    <i class="fas fa-file-alt fa-3x mb-3"></i>
                                    <h6>No hay borradores</h6>
                                    <p class="text-muted small"><?php echo $key === 'formulacion' ? 'Haz clic en "Nuevo Indicador" para comenzar' : 'Los borradores de formulación aparecerán aquí'; ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- PUBLICADOS -->
                        <div class="tab-pane fade" id="tabPublicados-<?php echo $key; ?>" role="tabpanel">
                            <?php if (count($modulo['publicados']) > 0): ?>
                                <div class="lista-container">
                                    <div class="lista-header">
                                        <div class="row">
                                            <div class="col-md-4">Nombre</div>
                                            <div class="col-md-2">L - M - P</div>
                                            <div class="col-md-2">Meta / Medición</div>
                                            <div class="col-md-2">Fecha de publicación</div>
                                            <div class="col-md-2">Acciones</div>
                                        </div>
                                    </div>
                                    <?php
                                    $pond_por_linea_p = [];
                                    foreach ($modulo['publicados'] as $_p) {
                                        $lk = ($_p['linea_codigo'] ?? '__') . '|' . ($_p['motor_id_num'] ?? '__') . '|' . ($_p['proyecto_codigo'] ?? '__');
                                        $pond_por_linea_p[$lk] = ($pond_por_linea_p[$lk] ?? 0) + (float)($_p['ponderacion_actividades'] ?? 0);
                                    }
                                    ?>
                                    <?php foreach ($modulo['publicados'] as $publicado):
                                        $l   = !empty($publicado['linea_codigo'])    ? $publicado['linea_codigo']    : null;
                                        $m   = !empty($publicado['motor_codigo'])    ? $publicado['motor_codigo']    : null;
                                        $p   = !empty($publicado['proyecto_codigo']) ? $publicado['proyecto_codigo'] : null;
                                        $lmp = array_filter([$l, $m, $p]);
                                        $lmp_key_p = ($l ?? '__') . '|' . ($publicado['motor_id_num'] ?? '__') . '|' . ($publicado['proyecto_codigo'] ?? '__');
                                        $suma_linea = $pond_por_linea_p[$lmp_key_p] ?? 0;
                                        $linea_completa = ($suma_linea >= 99.99 && $suma_linea <= 100.01);
                                        $linea_excedida = $suma_linea > 100.01;
                                        $tiene_seg_p = !empty($publicado['fecha_seguimiento']) || !empty($publicado['porcentaje_avance']) || !empty($publicado['indicador']);
                                    ?>
                                    <div class="lista-item" data-item-id="<?php echo $publicado['id']; ?>" data-ponderacion="<?php echo (float)($publicado['ponderacion_actividades'] ?? 0); ?>" data-linea-item="<?php echo htmlspecialchars($l ?? ''); ?>" data-motor-item="<?php echo htmlspecialchars($publicado['motor_id_num'] ?? ''); ?>" data-proyecto-item="<?php echo htmlspecialchars($p ?? ''); ?>" data-modulo="<?php echo $key; ?>" data-creado-por="<?php echo (int)($publicado['creado_por'] ?? 0); ?>" data-creado-por-nombre="<?php echo htmlspecialchars($publicado['creado_por_nombre'] ?? ''); ?>" data-cargo-id="<?php echo (int)($publicado['creado_por_cargo_id'] ?? 0); ?>" data-cargo-nombre="<?php echo htmlspecialchars($publicado['creado_por_cargo_nombre'] ?? ''); ?>" data-responsable="<?php echo htmlspecialchars($publicado['responsable_formulacion'] ?? ''); ?>" data-linea-filtro="<?php echo htmlspecialchars($publicado['linea_estrategica'] ?? ''); ?>" data-motor-filtro="<?php echo htmlspecialchars($publicado['motor_desarrollo'] ?? ''); ?>" data-proyecto-filtro="<?php echo htmlspecialchars($publicado['proyecto'] ?? ''); ?>" data-linea-codigo="<?php echo htmlspecialchars($l ?? ''); ?>" data-motor-codigo="<?php echo htmlspecialchars($m ?? ''); ?>" data-proyecto-codigo="<?php echo htmlspecialchars($p ?? ''); ?>" data-nombre-borrador="<?php echo htmlspecialchars(strtolower($publicado['nombre_borrador'] ?? '')); ?>" data-tiene-seguimiento="<?php echo $tiene_seg_p ? '1' : '0'; ?>">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-4">
                                                <div class="lista-item-titulo <?php echo $linea_completa ? 'titulo-linea-completa' : ($linea_excedida ? 'titulo-linea-excedida' : ''); ?>">
                                                    <?php echo htmlspecialchars(!empty(trim($publicado['formula_medicion'] ?? '')) ? $publicado['formula_medicion'] : $publicado['nombre_borrador']); ?>
                                                    <?php if ($publicado['gestionado_facultades'] == 1): ?>
                                                    <span class="gestionado-indicador"><i class="fas fa-check-circle"></i> Gestionado</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($publicado['fecha_seguimiento']) || !empty($publicado['porcentaje_avance']) || !empty($publicado['indicador'])): ?>
                                                    <span class="seguimiento-indicador"><i class="fas fa-check-circle"></i> Seguimiento</span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($publicado['anio'])): ?>
                                                <div class="lista-item-sub"><i class="fas fa-calendar me-1"></i> Año: <?php echo $publicado['anio']; ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2">
                                                <?php if (!empty($lmp)): ?>
                                                <span class="lmp-badge <?php echo $linea_completa ? 'lmp-completo' : ($linea_excedida ? 'lmp-excedido' : ''); ?>"
                                                      data-linea="<?php echo htmlspecialchars($l ?? ''); ?>"
                                                      title="Ponderación línea: <?php echo number_format($suma_linea,2); ?> / 100<?php echo $linea_excedida ? ' ⚠ Excede el 100%' : ''; ?>">
                                                    <i class="fas fa-sitemap"></i><?php echo implode(' - ', $lmp); ?>
                                                    <small class="lmp-suma<?php echo ((float)($publicado['ponderacion_actividades'] ?? 0)) <= 0 ? ' sin-aporte' : ''; ?>"><?php echo number_format($suma_linea,1); ?></small>
                                                </span>
                                                <?php else: ?>
                                                <span class="lmp-badge sin-datos">—</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2">
                                                <?php echo m144_cumplidoMetaHtml($publicado); ?>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="lista-item-fecha">
                                                    <i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y H:i', strtotime($publicado['fecha_creacion'])); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="lista-item-actions">
                                                    <?php if ($key === 'formulacion'): ?>
                                                    <button class="btn btn-sm btn-primary" onclick="verBorrador('<?php echo $key; ?>', <?php echo $publicado['id']; ?>)">
                                                        <i class="fas fa-eye me-1"></i>Ver
                                                    </button>
                                                    <?php else: ?>
                                                    <button class="btn btn-sm btn-primary" onclick="verSeguimiento('<?php echo $key; ?>', <?php echo $publicado['id']; ?>)">
                                                        <i class="fas fa-eye me-1"></i>Ver
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if ((int)($_SESSION['usuario_id'] ?? 0) === 1): ?>
                                                    <button class="btn btn-sm btn-warning" onclick="devolverABorrador('<?php echo $key; ?>', <?php echo $publicado['id']; ?>)" title="Devolver a borradores" aria-label="Devolver a borradores">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state p-4">
                                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                                    <h6>No hay publicaciones</h6>
                                    <p class="text-muted small">Los borradores aprobados aparecerán aquí</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- CANCELADOS -->
                        <div class="tab-pane fade" id="tabCancelados-<?php echo $key; ?>" role="tabpanel">
                            <?php if (count($modulo['cancelados']) > 0): ?>
                                <div class="lista-container">
                                    <div class="lista-header">
                                        <div class="row">
                                            <div class="col-md-4">Nombre</div>
                                            <div class="col-md-2">L - M - P</div>
                                            <div class="col-md-2">Meta / Medición</div>
                                            <div class="col-md-2">Fecha de cancelación</div>
                                            <div class="col-md-2">Acciones</div>
                                        </div>
                                    </div>
                                    <?php
                                    $pond_por_linea_c = [];
                                    foreach ($modulo['cancelados'] as $_c) {
                                        $lk = ($_c['linea_codigo'] ?? '__') . '|' . ($_c['motor_id_num'] ?? '__') . '|' . ($_c['proyecto_codigo'] ?? '__');
                                        $pond_por_linea_c[$lk] = ($pond_por_linea_c[$lk] ?? 0) + (float)($_c['ponderacion_actividades'] ?? 0);
                                    }
                                    ?>
                                    <?php foreach ($modulo['cancelados'] as $cancelado):
                                        $l   = !empty($cancelado['linea_codigo'])    ? $cancelado['linea_codigo']    : null;
                                        $m   = !empty($cancelado['motor_codigo'])    ? $cancelado['motor_codigo']    : null;
                                        $p   = !empty($cancelado['proyecto_codigo']) ? $cancelado['proyecto_codigo'] : null;
                                        $lmp = array_filter([$l, $m, $p]);
                                        $lmp_key_c = ($l ?? '__') . '|' . ($cancelado['motor_id_num'] ?? '__') . '|' . ($cancelado['proyecto_codigo'] ?? '__');
                                        $suma_linea = $pond_por_linea_c[$lmp_key_c] ?? 0;
                                        $linea_completa = ($suma_linea >= 99.99 && $suma_linea <= 100.01);
                                        $linea_excedida = $suma_linea > 100.01;
                                    ?>
                                    <div class="lista-item" data-item-id="<?php echo $cancelado['id']; ?>" data-ponderacion="<?php echo (float)($cancelado['ponderacion_actividades'] ?? 0); ?>" data-linea-item="<?php echo htmlspecialchars($l ?? ''); ?>" data-motor-item="<?php echo htmlspecialchars($cancelado['motor_id_num'] ?? ''); ?>" data-proyecto-item="<?php echo htmlspecialchars($p ?? ''); ?>" data-modulo="<?php echo $key; ?>" data-creado-por="<?php echo (int)($cancelado['creado_por'] ?? 0); ?>" data-creado-por-nombre="<?php echo htmlspecialchars($cancelado['creado_por_nombre'] ?? ''); ?>" data-cargo-id="<?php echo (int)($cancelado['creado_por_cargo_id'] ?? 0); ?>" data-cargo-nombre="<?php echo htmlspecialchars($cancelado['creado_por_cargo_nombre'] ?? ''); ?>" data-responsable="<?php echo htmlspecialchars($cancelado['responsable_formulacion'] ?? ''); ?>" data-nombre-borrador="<?php echo htmlspecialchars(strtolower($cancelado['nombre_borrador'] ?? '')); ?>">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-4">
                                                <div class="lista-item-titulo <?php echo $linea_completa ? 'titulo-linea-completa' : ($linea_excedida ? 'titulo-linea-excedida' : ''); ?>">
                                                    <?php echo htmlspecialchars(!empty(trim($cancelado['formula_medicion'] ?? '')) ? $cancelado['formula_medicion'] : $cancelado['nombre_borrador']); ?>
                                                </div>
                                                <?php if (!empty($cancelado['anio'])): ?>
                                                <div class="lista-item-sub"><i class="fas fa-calendar me-1"></i> Año: <?php echo $cancelado['anio']; ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2">
                                                <?php if (!empty($lmp)): ?>
                                                <span class="lmp-badge <?php echo $linea_completa ? 'lmp-completo' : ($linea_excedida ? 'lmp-excedido' : ''); ?>"
                                                      data-linea="<?php echo htmlspecialchars($l ?? ''); ?>"
                                                      title="Ponderación línea: <?php echo number_format($suma_linea,2); ?> / 100<?php echo $linea_excedida ? ' ⚠ Excede el 100%' : ''; ?>">
                                                    <i class="fas fa-sitemap"></i><?php echo implode(' - ', $lmp); ?>
                                                    <small class="lmp-suma<?php echo ((float)($cancelado['ponderacion_actividades'] ?? 0)) <= 0 ? ' sin-aporte' : ''; ?>"><?php echo number_format($suma_linea,1); ?></small>
                                                </span>
                                                <?php else: ?>
                                                <span class="lmp-badge sin-datos">—</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2">
                                                <?php echo m144_cumplidoMetaHtml($cancelado); ?>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="lista-item-fecha">
                                                    <i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y H:i', strtotime($cancelado['fecha_creacion'])); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="lista-item-actions">
                                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarBorrador('<?php echo $key; ?>', <?php echo $cancelado['id']; ?>)">
                                                        <i class="fas fa-trash me-1"></i>Eliminar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state p-4">
                                    <i class="fas fa-times-circle fa-3x mb-3"></i>
                                    <h6>No hay cancelados</h6>
                                    <p class="text-muted small">Los borradores cancelados aparecerán aquí</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        </div>
                    </div>
                </div>
            <?php $primer_modulo = false; ?>
            <?php endforeach; ?>

            <!-- EVALUACIÓN LÍNEAS -->
            <div class="tab-pane fade" id="panelEvaluacion" role="tabpanel">
                <div class="modulo-panel-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-chart-pie fa-2x me-3"></i>
                    <div>
                        <span class="modulo-panel-title">EVALUACIÓN LÍNEAS</span><br>
                        <small>Resumen del informe: cumplimiento ponderado por línea y motor</small>
                    </div>
                </div>
                <div class="p-4">
                    <?php
                    $evInforme = $informe_resumen ?? ['global_cumplimiento' => 0, 'lineas' => []];
                    $evLineas  = $evInforme['lineas'] ?? [];
                    $evGlobal  = (float)($evInforme['global_cumplimiento'] ?? 0);
                    // Verde 80 o más, naranja 60 a 79, rojo menos de 60 (mismos cortes del informe)
                    $evTono = function ($p) { return $p >= 80 ? '#34C759' : ($p >= 60 ? '#FF9500' : '#FF3B30'); };
                    $evMotores = 0; $evProyectos = 0; $evProyConReporte = 0;
                    foreach ($evLineas as $evL) {
                        $evMotores += count($evL['motores']);
                        foreach ($evL['motores'] as $evM) {
                            foreach ($evM['proyectos'] as $evP) {
                                $evProyectos++;
                                if ((int)$evP['total'] > 0) $evProyConReporte++;
                            }
                        }
                    }
                    $evCirc = 2 * M_PI * 70;
                    ?>
                    <?php if (empty($evLineas)): ?>
                    <div class="desarrollo-section">
                        <div class="desarrollo-icon"><i class="fas fa-chart-pie"></i></div>
                        <div class="desarrollo-title">SIN DATOS PARA EVALUAR</div>
                        <div class="desarrollo-subtitle">Este formulario aún no tiene indicadores ni distribución de líneas configurada.</div>
                    </div>
                    <?php else: ?>
                    <div class="ev-resumen">
                        <aside class="ev-total">
                            <div class="ev-total-label">Cumplimiento integral del plan</div>
                            <div class="ev-donut" role="img" aria-label="Cumplimiento integral <?php echo number_format($evGlobal, 2); ?> %">
                                <svg viewBox="0 0 160 160">
                                    <circle cx="80" cy="80" r="70" fill="none" stroke="#EEEEF2" stroke-width="14"/>
                                    <circle cx="80" cy="80" r="70" fill="none" stroke="<?php echo $evTono($evGlobal); ?>" stroke-width="14" stroke-linecap="round"
                                            stroke-dasharray="<?php echo round($evCirc, 2); ?>" stroke-dashoffset="<?php echo round($evCirc * (1 - min($evGlobal, 100) / 100), 2); ?>"/>
                                </svg>
                                <div class="ev-donut-num"><strong><?php echo number_format($evGlobal, 2); ?>%</strong><span>ponderado</span></div>
                            </div>
                            <div class="ev-total-sub"><?php echo htmlspecialchars($formulario['titulo'] ?? ''); ?><?php echo !empty($formulario['anio']) ? ' · ' . (int)$formulario['anio'] : ''; ?></div>
                            <div class="ev-total-stats">
                                <div><b><?php echo count($evLineas); ?></b><small>Líneas</small></div>
                                <div><b><?php echo $evMotores; ?></b><small>Motores</small></div>
                                <div><b><?php echo $evProyectos; ?></b><small>Proyectos</small></div>
                                <div><b><?php echo $evProyConReporte; ?>/<?php echo $evProyectos; ?></b><small>Con indicadores</small></div>
                            </div>
                            <a class="ev-btn-informe" href="<?php echo Config::getBasePath(); ?>/FOR-DE-144?action=informePage&id=<?php echo (int)$formulario['id']; ?>">
                                <i class="fas fa-chart-bar"></i> Ver informe completo
                            </a>
                        </aside>

                        <div>
                            <div class="ev-lineas">
                                <?php foreach ($evLineas as $li => $evL):
                                    $evColor = $eval_colores[$li % count($eval_colores)];
                                    $evPct = (float)$evL['cumplimiento']; ?>
                                <details class="ev-linea" style="--ev-color: <?php echo $evColor; ?>">
                                    <summary>
                                        <div>
                                            <div class="ev-linea-titulo"><?php echo htmlspecialchars($evL['nombre'] ?? $evL['titulo']); ?></div>
                                            <div class="ev-linea-meta">
                                                <span class="ev-chip codigo"><?php echo htmlspecialchars($evL['codigo']); ?></span>
                                                <?php if ($evL['peso'] !== null): ?><span class="ev-chip peso">Peso <?php echo number_format((float)$evL['peso'], 2); ?>%</span><?php endif; ?>
                                                <span class="ev-chip"><?php echo count($evL['motores']); ?> motor<?php echo count($evL['motores']) === 1 ? '' : 'es'; ?></span>
                                            </div>
                                        </div>
                                        <div class="ev-bar"><span style="width: <?php echo min($evPct, 100); ?>%; background: <?php echo $evTono($evPct); ?>"></span></div>
                                        <div class="ev-pct" style="color: <?php echo $evTono($evPct); ?>"><?php echo number_format($evPct, 2); ?>%</div>
                                        <i class="fas fa-chevron-down ev-chevron"></i>
                                    </summary>
                                    <div class="ev-motores">
                                        <?php foreach ($evL['motores'] as $evM):
                                            $evMp = (float)$evM['cumplimiento'];
                                            $evConReporte = count(array_filter($evM['proyectos'], function ($p) { return (int)$p['total'] > 0; })); ?>
                                        <div class="ev-motor">
                                            <div>
                                                <?php echo htmlspecialchars($evM['nombre']); ?>
                                                <small>
                                                    <?php echo $evM['peso'] !== null ? 'Peso ' . number_format((float)$evM['peso'], 2) . '% · ' : ''; ?>
                                                    <?php echo $evConReporte; ?>/<?php echo count($evM['proyectos']); ?> proyectos con indicadores
                                                </small>
                                            </div>
                                            <div class="ev-bar"><span style="width: <?php echo min($evMp, 100); ?>%; background: <?php echo $evTono($evMp); ?>"></span></div>
                                            <div class="ev-pct" style="color: <?php echo $evTono($evMp); ?>"><?php echo number_format($evMp, 2); ?>%</div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php if (empty($evL['motores'])): ?><div class="ev-vacio">Sin motores con indicadores en esta línea.</div><?php endif; ?>
                                    </div>
                                </details>
                                <?php endforeach; ?>
                            </div>
                            <div class="ev-nota">
                                Clic en una línea para ver sus motores. El cumplimiento se pondera con la distribución de Configuraciones del año
                                (líneas, motores y proyectos); un elemento con peso pero sin indicadores cuenta como 0 %.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                </div>

            <!-- CONCERTACIONES -->
            <div class="tab-pane fade" id="panelConcertacion" role="tabpanel">
                <div class="modulo-panel-header" style="background: linear-gradient(135deg, #143163 0%, #1D71B8 100%);">
                    <i class="fas fa-handshake fa-2x me-3"></i>
                    <div>
                        <span class="modulo-panel-title">CONCERTACIONES</span><br>
                        <small>Lo que reportan las facultades se concierta aquí contra su meta propuesta</small>
                    </div>
                </div>
                <div class="p-4">
                    <?php
                    // Indicadores aprobados que tienen meta por facultad: lo que cada facultad reporta se concierta aquí
                    $cnFilas = array_values(array_filter($datos_modulos['formulacion']['publicados'] ?? [], function ($r) use ($resumen_concertaciones) {
                        return !empty(($resumen_concertaciones ?? [])[(int)$r['id']]['facultades']);
                    }));
                    usort($cnFilas, function ($a, $b) {
                        return strcmp(($a['linea_codigo'] ?? '~') . ($a['motor_codigo'] ?? '~') . ($a['proyecto_codigo'] ?? '~'), ($b['linea_codigo'] ?? '~') . ($b['motor_codigo'] ?? '~') . ($b['proyecto_codigo'] ?? '~'));
                    });
                    $cnResumen = $resumen_concertaciones ?? [];
                    $cnLineas = [];
                    foreach ($cnFilas as $cnR) { if (!empty($cnR['linea_codigo'])) $cnLineas[$cnR['linea_codigo']] = true; }
                    ksort($cnLineas);
                    ?>
                    <?php
                    $cnMat = $matriz_concertacion ?? [];
                    $cnTodas = $concertaciones_todas ?? [];
                    // pendiente (falta un valor) | listo (los dos valores, falta concertar) | enviado (espera al superadministrador) | aprobado
                    $cnEst = function ($vf, $vv, $apr, $env) {
                        if ($apr) return 'aprobado';
                        if ($vf === null && $vv === null) return 'vacio';
                        if ($vf === null || $vv === null) return 'pendiente';
                        return $env ? 'enviado' : 'listo';
                    };
                    $cnInfo = [];
                    $cnConteo = ['' => count($cnFilas), 'pendiente' => 0, 'listo' => 0, 'enviado' => 0, 'aprobado' => 0, 'sinreporte' => 0];
                    foreach ($cnFilas as $cnR) {
                        $id = (int)$cnR['id'];
                        $lim = ($cnR['tipo_medicion'] ?? '') === 'Límite';
                        $tot = [1 => 0, 2 => 0]; $ap = [1 => 0, 2 => 0];
                        $hayEnv = false; $hayListo = false; $hayPend = false;
                        foreach (($cnMat[$id] ?? []) as $cnFid => $cnD) {
                            foreach ([1, 2] as $sem) {
                                if ($sem === 1 && $lim) continue;
                                $h = (($cnD['seguimiento_sem' . $sem] ?? '') !== '' && $cnD['seguimiento_sem' . $sem] !== null) ? (float)$cnD['seguimiento_sem' . $sem] : null;
                                $t = $cnTodas[$id][$cnFid][$sem] ?? null;
                                $vf = ($t && $t['vf'] !== null) ? $t['vf'] : $h;
                                $vv = $t ? $t['vv'] : null;
                                $e = $cnEst($vf, $vv, $t && $t['aprobado'], $t && $t['enviado']);
                                if ($e === 'vacio') continue;
                                $tot[$sem]++;
                                if ($e === 'aprobado') $ap[$sem]++;
                                if ($e === 'enviado') $hayEnv = true;
                                if ($e === 'listo') $hayListo = true;
                                if ($e === 'pendiente') $hayPend = true;
                            }
                        }
                        if ($tot[1] + $tot[2] === 0) $estado = 'sinreporte';
                        else $estado = $hayEnv ? 'enviado' : ($hayListo ? 'listo' : ($hayPend ? 'pendiente' : 'aprobado'));
                        $cnInfo[$id] = ['coin' => $estado === 'aprobado' ? 'si' : 'nd', 'estado' => $estado, 't1' => $tot[1], 't2' => $tot[2], 'd1' => $ap[1], 'd2' => $ap[2],
                                        'a1' => $tot[1] > 0 && $ap[1] >= $tot[1], 'a2' => $tot[2] > 0 && $ap[2] >= $tot[2]];
                        $cnConteo[$estado]++;
                    }
                    ?>
                    <div class="cnx-barra">
                        <div class="cnx-chips" id="cnChips" role="group" aria-label="Filtrar por estado">
                            <button type="button" class="cnx-chip on" data-f="">Todos <b><?php echo $cnConteo['']; ?></b></button>
                            <button type="button" class="cnx-chip" data-f="pendiente">Falta un valor <b><?php echo $cnConteo['pendiente']; ?></b></button>
                            <button type="button" class="cnx-chip" data-f="listo">Para concertar <b><?php echo $cnConteo['listo']; ?></b></button>
                            <button type="button" class="cnx-chip" data-f="enviado">Por aprobar <b><?php echo $cnConteo['enviado']; ?></b></button>
                            <button type="button" class="cnx-chip" data-f="aprobado">Aprobados <b><?php echo $cnConteo['aprobado']; ?></b></button>
                        </div>
                        <div class="cnx-tools">
                            <input type="search" id="cnBuscar" placeholder="Buscar indicador o dependencia…" aria-label="Buscar en concertaciones">
                            <select id="cnLinea" aria-label="Línea">
                                <option value="">Línea: todas</option>
                                <?php foreach (array_keys($cnLineas) as $cnCod): ?><option value="<?php echo htmlspecialchars($cnCod); ?>"><?php echo htmlspecialchars($cnCod); ?></option><?php endforeach; ?>
                            </select>
                            <select id="cnFacultad" aria-label="Facultad" hidden><option value="">Facultad: todas</option></select>
                            <button type="button" id="cnVista" class="cnx-vista" title="Cambiar entre la vista simple y el formato Excel"><i class="fas fa-table me-1"></i>Formato Excel</button>
                            <span class="cn-contador" id="cnContador"><?php echo count($cnFilas); ?> indicadores</span>
                        </div>
                    </div>
                    <?php if (empty($cnFilas)): ?>
                    <div class="desarrollo-section">
                        <div class="desarrollo-icon"><i class="fas fa-handshake"></i></div>
                        <div class="desarrollo-title">SIN INDICADORES POR FACULTAD</div>
                        <div class="desarrollo-subtitle">Aquí aparecen los indicadores aprobados que tienen meta asignada a las facultades.</div>
                    </div>
                    <?php else: ?>
                    <div class="cnx-lista" id="cnVistaLista">
                        <?php foreach ($cnFilas as $cnR):
                            $id = (int)$cnR['id']; $inf = $cnInfo[$id];
                            $nombre = trim((string)($cnR['nombre_indicador'] ?? '')) !== '' ? $cnR['nombre_indicador'] : ($cnR['formula_medicion'] ?: $cnR['nombre_borrador']);
                            $lmp = implode(' · ', array_filter([$cnR['linea_codigo'] ?? '', $cnR['motor_codigo'] ?? '', $cnR['proyecto_codigo'] ?? '']));
                        ?>
                        <div class="cnx-item" role="button" tabindex="0" data-id="<?php echo $id; ?>" data-linea="<?php echo htmlspecialchars($cnR['linea_codigo'] ?? ''); ?>"
                             data-estado="<?php echo $inf['estado']; ?>" data-coinciden="<?php echo $inf['coin']; ?>"
                             data-buscar="<?php echo htmlspecialchars(mb_strtolower($nombre . ' ' . ($cnR['responsable_formulacion'] ?? '') . ' ' . $lmp, 'UTF-8')); ?>"
                             onclick="abrirConcertacion(<?php echo $id; ?>)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();abrirConcertacion(<?php echo $id; ?>);}">
                            <div class="cnx-main">
                                <div class="cnx-nombre"><?php echo htmlspecialchars($nombre); ?></div>
                                <div class="cnx-sub"><span class="cn-lmp"><?php echo htmlspecialchars($lmp ?: '—'); ?></span><span><?php echo htmlspecialchars($cnR['responsable_formulacion'] ?: '—'); ?></span></div>
                            </div>
                            <?php foreach ([1, 2] as $sem): $rep = $inf['t' . $sem]; $con = $inf['d' . $sem]; ?>
                            <div class="cnx-prog<?php echo $inf['a' . $sem] ? ' apr' : ''; ?>" data-sem="<?php echo $sem; ?>" title="Semestre <?php echo $sem; ?>: concertado por la dependencia y las facultades<?php echo $inf['a' . $sem] ? ' · aprobado' : ''; ?>">
                                <span class="cnx-sem">S<?php echo $sem; ?></span>
                                <span class="cnx-barra-p"><i style="width:<?php echo $rep ? round(min($con, $rep) / $rep * 100) : 0; ?>%"></i></span>
                                <b><?php echo $rep ? $con . '/' . $rep : '—'; ?></b>
                            </div>
                            <?php endforeach; ?>
                            <span class="cnx-pill <?php echo ['pendiente' => 'nd', 'listo' => 'no', 'enviado' => 'env', 'aprobado' => 'si', 'sinreporte' => 'nd'][$inf['estado']]; ?>"><?php echo ['pendiente' => 'Falta un valor', 'listo' => 'Para concertar', 'enviado' => 'Por aprobar', 'aprobado' => 'Aprobado', 'sinreporte' => 'Sin datos'][$inf['estado']]; ?></span>
                            <i class="fas fa-chevron-right cnx-flecha"></i>
                        </div>
                        <?php endforeach; ?>
                        <div class="cn-vacio" id="cnxVacio" hidden>Ningún indicador coincide con los filtros.</div>
                    </div>
                    <div id="cnVistaMatriz" hidden>
                    <?php
                    // Facultades que tienen meta o reporte en alguno de los indicadores listados
                    $cnFacs = [];
                    foreach ($facultades as $cnF) {
                        foreach ($cnFilas as $cnR0) { if (isset($cnMat[(int)$cnR0['id']][(int)$cnF['id']])) { $cnFacs[] = $cnF; break; } }
                    }
                    $cnCorto = function ($n) { return mb_convert_case(preg_replace('/^\s*facultad\s+de\s+/iu', '', (string)$n), MB_CASE_TITLE, 'UTF-8'); };
                    $cnV = function ($v, $u) {
                        if ($v === null || $v === '') return '';
                        $t = rtrim(rtrim(number_format((float)$v, 2, ',', '.'), '0'), ',');
                        return $t . ($u === 'Porcentaje' ? '%' : '');
                    };
                    $cnTot = function ($tipo, $s1, $s2) {
                        $v = array_values(array_filter([$s1, $s2], function ($x) { return $x !== null && $x !== ''; }));
                        if (!$v) return null;
                        $v = array_map('floatval', $v);
                        if ($tipo === 'Promedio') return array_sum($v) / count($v);
                        if ($tipo === 'Último valor reportado' || $tipo === 'Límite') return ($s2 !== null && $s2 !== '') ? (float)$s2 : (float)$s1;
                        return array_sum($v);
                    };
                    $cnTexto = function ($t, $max = 140) { $t = trim((string)$t); return mb_strlen($t, 'UTF-8') > $max ? mb_substr($t, 0, $max, 'UTF-8') . '…' : $t; };
                    ?>
                    <div class="cn-tabla-wrap">
                        <table class="cn-tabla cn-matriz" id="cnTabla">
                            <thead>
                                <tr class="cn-grupos">
                                    <th colspan="6"></th>
                                    <th colspan="4" class="cn-g-dep">Dependencia responsable</th>
                                    <?php foreach ($cnFacs as $cnF): ?>
                                    <th colspan="5" class="cn-g-fac cn-fg" data-fac="<?php echo (int)$cnF['id']; ?>"><?php echo htmlspecialchars($cnCorto($cnF['nombre'])); ?></th>
                                    <?php endforeach; ?>
                                    <th colspan="2"></th>
                                </tr>
                                <tr>
                                    <th class="cn-sticky">Indicador</th><th>Línea estratégica</th><th>Fórmula del indicador</th><th>Tipo de medición</th><th class="cn-num">Meta general</th><th>Responsable</th>
                                    <th class="cn-num">Sem 1</th><th class="cn-num">Sem 2</th><th class="cn-num">Total</th><th>Descripción</th>
                                    <?php foreach ($cnFacs as $cnF): ?>
                                    <th class="cn-num cn-fg" data-fac="<?php echo (int)$cnF['id']; ?>">Meta</th><th class="cn-num cn-fg" data-fac="<?php echo (int)$cnF['id']; ?>">Sem 1</th><th class="cn-num cn-fg" data-fac="<?php echo (int)$cnF['id']; ?>">Sem 2</th><th class="cn-num cn-fg" data-fac="<?php echo (int)$cnF['id']; ?>">Total</th><th class="cn-fg" data-fac="<?php echo (int)$cnF['id']; ?>">Descripción</th>
                                    <?php endforeach; ?>
                                    <th class="cn-th-coinciden">Aprobado</th><th>Concertación</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($cnFilas as $cnR):
                                $cnId = (int)$cnR['id'];
                                $cnU = $cnR['unidad_medida'] ?? '';
                                $cnTipo = $cnR['tipo_medicion'] ?? '';
                                $cnLimite = $cnTipo === 'Límite';
                                $cnNombre = trim((string)($cnR['nombre_indicador'] ?? '')) !== '' ? $cnR['nombre_indicador'] : ($cnR['formula_medicion'] ?: $cnR['nombre_borrador']);
                                $cnLmp = implode(' · ', array_filter([$cnR['linea_codigo'] ?? '', $cnR['motor_codigo'] ?? '', $cnR['proyecto_codigo'] ?? '']));
                                $cnRes = $cnResumen[$cnId] ?? ['facultades' => 0, 's1' => 0, 's2' => 0];
                                // Lo que reporta la dependencia responsable
                                $dS1 = $cnLimite ? null : (($cnR['semestre1_seguimiento'] ?? '') !== '' ? (float)$cnR['semestre1_seguimiento'] : null);
                                $dS2 = ($cnR['semestre2_seguimiento'] ?? '') !== '' ? (float)$cnR['semestre2_seguimiento'] : null;
                                $dDesc = trim(($cnR['logros'] ?? '') !== '' ? 'S1: ' . $cnR['logros'] : '') . (($cnR['logros_sem2'] ?? '') !== '' ? "\nS2: " . $cnR['logros_sem2'] : '');
                                // Suma de lo que reportan las facultades
                                $fS1 = null; $fS2 = null;
                                foreach (($cnMat[$cnId] ?? []) as $cnD) {
                                    if (($cnD['seguimiento_sem1'] ?? '') !== '' && $cnD['seguimiento_sem1'] !== null) $fS1 = ($fS1 ?? 0) + (float)$cnD['seguimiento_sem1'];
                                    if (($cnD['seguimiento_sem2'] ?? '') !== '' && $cnD['seguimiento_sem2'] !== null) $fS2 = ($fS2 ?? 0) + (float)$cnD['seguimiento_sem2'];
                                }
                                if ($cnLimite) $fS1 = null;
                                $hayDatos = $dS1 !== null || $dS2 !== null || $fS1 !== null || $fS2 !== null;
                                $igual = function ($x, $y) { return abs(($x ?? 0) - ($y ?? 0)) < 0.01; };
                                $coinciden = $cnInfo[$cnId]['coin'] === 'si' ? true : ($cnInfo[$cnId]['coin'] === 'no' ? false : null);
                            ?>
                            <tr class="cn-fila" data-id="<?php echo $cnId; ?>" data-estado="<?php echo $cnInfo[$cnId]['estado']; ?>" data-coinciden="<?php echo $cnInfo[$cnId]['coin']; ?>" data-total="<?php echo (int)$cnRes['facultades']; ?>" data-linea="<?php echo htmlspecialchars($cnR['linea_codigo'] ?? ''); ?>"
                                data-buscar="<?php echo htmlspecialchars(mb_strtolower($cnNombre . ' ' . ($cnR['responsable_formulacion'] ?? '') . ' ' . $cnLmp, 'UTF-8')); ?>" onclick="abrirConcertacion(<?php echo $cnId; ?>)">
                                <td class="cn-sticky cn-nombre"><?php echo htmlspecialchars($cnNombre); ?></td>
                                <td><span class="cn-lmp" title="<?php echo htmlspecialchars($cnR['linea_estrategica'] ?? ''); ?>"><?php echo htmlspecialchars($cnLmp ?: '—'); ?></span></td>
                                <td class="cn-formula"><?php echo htmlspecialchars($cnTexto($cnR['formula_medicion'] ?? '', 110)); ?></td>
                                <td class="cn-tipo"><?php echo htmlspecialchars($cnTipo ?: '—'); ?></td>
                                <td class="cn-num cn-anio"><?php echo $cnV($cnR['anio_base_meta'] ?? '', $cnU) ?: '—'; ?></td>
                                <td class="cn-resp"><?php echo htmlspecialchars($cnR['responsable_formulacion'] ?: '—'); ?></td>
                                <td class="cn-num"><?php echo $cnV($dS1, $cnU) ?: ($cnLimite ? '<span class="cn-sin">N/A</span>' : '—'); ?></td>
                                <td class="cn-num"><?php echo $cnV($dS2, $cnU) ?: '—'; ?></td>
                                <td class="cn-num cn-tot"><?php echo $cnV($cnTot($cnTipo, $dS1, $dS2), $cnU) ?: '—'; ?></td>
                                <td class="cn-desc" title="<?php echo htmlspecialchars($dDesc); ?>"><?php echo htmlspecialchars($cnTexto($dDesc)); ?></td>
                                <?php foreach ($cnFacs as $cnF):
                                    $cnD = $cnMat[$cnId][(int)$cnF['id']] ?? null;
                                    $x1 = $cnD && ($cnD['seguimiento_sem1'] ?? '') !== '' ? $cnD['seguimiento_sem1'] : null;
                                    $x2 = $cnD && ($cnD['seguimiento_sem2'] ?? '') !== '' ? $cnD['seguimiento_sem2'] : null;
                                    if ($cnLimite) $x1 = null;
                                    $xd = $cnD ? trim((($cnD['seguimiento_descripcion'] ?? '') !== '' ? 'S1: ' . $cnD['seguimiento_descripcion'] : '') . (($cnD['seguimiento_descripcion2'] ?? '') !== '' ? "\nS2: " . $cnD['seguimiento_descripcion2'] : '')) : '';
                                    $fid = (int)$cnF['id'];
                                ?>
                                <td class="cn-num cn-fg cn-meta" data-fac="<?php echo $fid; ?>"><?php echo $cnD && (float)$cnD['distribucion'] > 0 ? $cnV($cnD['distribucion'], $cnU) : ($cnD ? '<span class="cn-sin">Sin meta</span>' : '<span class="cn-sin">—</span>'); ?></td>
                                <td class="cn-num cn-fg" data-fac="<?php echo $fid; ?>"><?php echo $cnV($x1, $cnU) ?: '—'; ?></td>
                                <td class="cn-num cn-fg" data-fac="<?php echo $fid; ?>"><?php echo $cnV($x2, $cnU) ?: '—'; ?></td>
                                <td class="cn-num cn-fg cn-tot" data-fac="<?php echo $fid; ?>"><?php echo $cnV($cnTot($cnTipo, $x1, $x2), $cnU) ?: '—'; ?></td>
                                <td class="cn-desc cn-fg" data-fac="<?php echo $fid; ?>" title="<?php echo htmlspecialchars($xd); ?>"><?php echo htmlspecialchars($cnTexto($xd)); ?></td>
                                <?php endforeach; ?>
                                <td class="cn-coinciden <?php echo $coinciden === null ? 'nd' : ($coinciden ? 'si' : 'no'); ?>"><?php echo $coinciden === null ? '—' : ($coinciden ? 'SÍ' : 'NO'); ?></td>
                                <td class="cn-concerta"><span class="cn-prog cn-s1 <?php echo $cnInfo[$cnId]['t1'] > 0 && $cnInfo[$cnId]['d1'] >= $cnInfo[$cnId]['t1'] ? 'ok' : ''; ?>" title="Semestre 1 concertado"><?php echo (int)$cnInfo[$cnId]['d1']; ?>/<?php echo (int)$cnInfo[$cnId]['t1']; ?></span>
                                    <span class="cn-prog cn-s2 <?php echo $cnInfo[$cnId]['t2'] > 0 && $cnInfo[$cnId]['d2'] >= $cnInfo[$cnId]['t2'] ? 'ok' : ''; ?>" title="Semestre 2 concertado"><?php echo (int)$cnInfo[$cnId]['d2']; ?>/<?php echo (int)$cnInfo[$cnId]['t2']; ?></span>
                                    <button type="button" class="cn-abrir" onclick="event.stopPropagation(); abrirConcertacion(<?php echo $cnId; ?>)">Concertar</button></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    </div><!-- /cnVistaMatriz -->
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- /Pestañas principales -->

        <?php endif; ?>
    </div>

    <div class="auto-save-indicator" id="autoSaveIndicator">
        <i class="fas fa-check-circle me-2"></i> Guardado automático
    </div>

    <!-- MODALES -->
    <div class="modal fade" id="modalNuevoBorrador" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: #143163;">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nuevo Borrador</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevoBorrador">
                    <input type="hidden" name="modulo" id="nuevo_modulo">
                    <input type="hidden" name="formulario_id" value="<?php echo $formulario['id']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Borrador (provisional)</label>
                            <input type="text" class="form-control" name="nombre_borrador" id="nuevo_nombre" required placeholder="Ej: Versión 1.0">
                            <small class="text-muted">Se reemplazará automáticamente por el contenido de "13. Fórmula de la Medición" al diligenciarlo.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Crear Borrador</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNuevoBorradorFacultad" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: #143163;">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Formulación - <span id="facultadNombreModal"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevoBorradorFacultad">
                    <input type="hidden" name="modulo" value="formulacion">
                    <input type="hidden" name="formulario_id" value="<?php echo $formulario['id']; ?>">
                    <input type="hidden" name="facultad_id" id="facultad_id_modal">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Formulación *</label>
                            <input type="text" class="form-control" name="nombre_borrador" id="nuevo_nombre_facultad" required placeholder="Ej: Plan Facultad 2025">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Crear Formulación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDuplicarBorrador" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: #143163;">
                    <h5 class="modal-title"><i class="fas fa-copy me-2"></i>Duplicar Formulación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formDuplicarBorrador">
                    <input type="hidden" name="modulo" id="duplicar_modulo">
                    <input type="hidden" name="id" id="duplicar_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Nueva Formulación *</label>
                            <input type="text" class="form-control" name="nombre_duplicado" id="duplicar_nombre" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info"><i class="fas fa-copy me-1"></i>Duplicar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalGestionSemestral" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: #143163;">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-alt me-2"></i>GESTIÓN SEMESTRAL - <span id="gestionTituloSpan"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formGestionSemestral">
                    <input type="hidden" id="gestion_id" name="id">
                    
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="text-muted mb-3" style="border-bottom: 2px solid #FF9800; padding-bottom: 10px;">
                                    <i class="fas fa-archway me-2"></i>ARQUITECTURA
                                </h6>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">SEM. 1</label>
                                <input type="text" class="form-control" name="gestion_sem1" id="gestion_sem1" placeholder="Ingrese gestión semestre 1">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">SEM. 2</label>
                                <input type="text" class="form-control" name="gestion_sem2" id="gestion_sem2" placeholder="Ingrese gestión semestre 2">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">VIGENCIA</label>
                                <select class="form-select" name="vigencia" id="gestion_vigencia">
                                    <option value="">Seleccione vigencia</option>
                                    <?php for ($i = date('Y'); $i <= date('Y') + 5; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-white">
                                        <i class="fas fa-chart-line me-2"></i>SEGUIMIENTO (0/0)
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">DESCRIPCIÓN DE LA GESTIÓN</label>
                                            <textarea class="form-control" name="descripcion_gestion" id="gestion_descripcion" rows="4" placeholder="Describa la gestión realizada..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Guardar Gestión Semestral
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA FORMULACIÓN -->
    <div class="modal fade" id="modalFormulacion" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header" style="background: #143163;">
                    <h5 class="modal-title" id="tituloFormulacion" ondblclick="editarTituloModal('formulacion')">
                        <i class="fas fa-clipboard-list me-2"></i>FORMULACIÓN 144 - <span id="tituloFormulacionSpan"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formFormulacion">
                    <input type="hidden" name="modulo" value="formulacion">
                    <input type="hidden" id="formulacion_id" name="id">
                    
                    <ul class="nav nav-tabs px-3 pt-3" id="formulacionTabs" role="tablist" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active tab-incomplete" id="tab-formulacion" data-bs-toggle="tab" data-bs-target="#formulacion" type="button" role="tab" aria-controls="formulacion" aria-selected="true" style="font-weight: 600;">
                                <i class="fas fa-clipboard-list me-2"></i>FORMULACIÓN
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link tab-incomplete" id="tab-indicador" data-bs-toggle="tab" data-bs-target="#indicador" type="button" role="tab" aria-controls="indicador" aria-selected="false" style="font-weight: 600;">
                                <i class="fas fa-chart-line me-2"></i>INDICADOR
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link tab-incomplete" id="tab-planes" data-bs-toggle="tab" data-bs-target="#planes" type="button" role="tab" aria-controls="planes" aria-selected="false" style="font-weight: 600;">
                                <i class="fas fa-file-alt me-2"></i>PLANES INSTITUCIONALES DECRETO 612 DE 2018
                            </button>
                        </li>
                    </ul>
                    
                    <div class="modal-body-scroll">
                        <div class="tab-content">
                            <!-- PESTAÑA 1: FORMULACIÓN -->
                            <div class="tab-pane fade show active" id="formulacion" role="tabpanel" aria-labelledby="tab-formulacion">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label" for="buscarProyecto">BUSCAR PROYECTO
                                            <small class="text-muted fw-normal ms-1">escribe el código (ej. L5-M1-P1) o parte del nombre: completa línea, objetivo, motor y proyecto</small>
                                        </label>
                                        <div class="m144-buscar" id="buscarProyectoWrap">
                                            <i class="fas fa-search"></i>
                                            <input type="text" class="form-control" id="buscarProyecto" autocomplete="off" role="combobox"
                                                   aria-expanded="false" aria-controls="buscarProyectoLista" aria-autocomplete="list"
                                                   placeholder="Ej: L5-M1-P1  ·  Mejoramiento y efectividad de procesos administrativos y académicos">
                                            <button type="button" class="m144-buscar-limpiar" id="buscarProyectoLimpiar" aria-label="Limpiar búsqueda" hidden>&times;</button>
                                            <div class="m144-buscar-lista" id="buscarProyectoLista" role="listbox" hidden></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-4 col-xxl-3 mb-3">
                                        <label class="form-label">1. LÍNEA ESTRATÉGICA</label>
                                        <select class="form-select" name="linea_estrategica" id="formulacion_linea" onchange="cargarObjetivoYestrategias(); cargarMotoresPorLinea(); validarPestanas(); actualizarLmpBadgeEnLista();">
                                            <option value="">Seleccione línea estratégica</option>
                                            <?php foreach ($lineas_estrategicas as $linea): ?>
                                            <option value="<?php echo htmlspecialchars($linea['nombre']); ?>" 
                                                    data-id="<?php echo $linea['id']; ?>" 
                                                    data-objetivo="<?php echo htmlspecialchars($linea['objetivo']); ?>">
                                                <?php echo htmlspecialchars($linea['codigo'] . ' - ' . $linea['nombre']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-4 col-xxl-3 mb-3">
                                        <label class="form-label">2. MOTOR DE DESARROLLO</label>
                                        <select class="form-select" name="motor_desarrollo" id="formulacion_motor" onchange="cargarProyectosPorMotor(); autoGuardarFormulacion(); validarPestanas(); actualizarLmpBadgeEnLista();">
                                            <option value="">Seleccione un motor de desarrollo</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-4 col-xxl-3 mb-3">
                                        <label class="form-label">3. ESTRATEGIA</label>
                                        <select class="form-select" name="estrategia" id="formulacion_estrategia" onchange="autoGuardarFormulacion(); validarPestanas()">
                                            <option value="">Seleccione una estrategia</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-4 col-xxl-3 mb-3">
                                        <label class="form-label">4. PLAN BASE</label>
                                        <select class="form-select" name="id_indicador" id="formulacion_id_indicador" onchange="autoGuardarFormulacion(); validarPestanas()">
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($id_indicadores as $idIndicador): ?>
                                            <option value="<?php echo htmlspecialchars($idIndicador); ?>">
                                                <?php echo htmlspecialchars($idIndicador); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6 col-xxl-6 mb-3">
                                        <label class="form-label">5. OBJETIVO</label>
                                        <textarea class="form-control" name="objetivo" id="formulacion_objetivo" rows="3" readonly></textarea>
                                    </div>

                                    <div class="col-12 col-md-6 col-xxl-6 mb-3">
                                        <label class="form-label">6. META DE RESULTADO</label>
                                        <textarea class="form-control" name="meta_resultado" id="formulacion_meta" rows="3" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa la meta de resultado..."></textarea>
                                    </div>

                                    <div class="col-12 col-md-6 col-xxl-6 mb-3">
                                        <label class="form-label">7. ACTIVIDAD DEL PROYECTO (205)</label>
                                        <textarea class="form-control" name="actividad_proyecto" id="formulacion_actividad" rows="5" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa la actividad del proyecto..."></textarea>
                                    </div>

                                    <div class="col-12 col-md-6 col-xxl-6 mb-3">
                                        <div class="mb-3">
                                            <label class="form-label">8. PONDERACIÓN DE LAS ACTIVIDADES POR PROYECTO</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="ponderacion_actividades" id="formulacion_ponderacion_actividades" step="0.01" min="0" max="100" oninput="calcularAcumuladoActividades(); autoGuardarFormulacion(); validarPestanas()" placeholder="0.00">
                                                <span class="input-group-text">%</span>
                                                <span class="input-group-text px-3" id="acumulado_actividades_badge" 
                                                      title="Total acumulado para este proyecto (incluyendo este registro)"
                                                      style="font-size:0.82rem; font-weight:600; min-width:110px; justify-content:center; background:#f8f9fa; color:#6c757d; border-left: 2px solid #dee2e6;">
                                                    Acum: — / 100%
                                                </span>
                                            </div>
                                            <div id="acumulado_actividades_msg" class="mt-1" style="font-size:0.8rem; display:none;"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">9. RESPONSABLE</label>
                                            <select class="form-select" name="responsable_formulacion_multi[]" id="formulacion_responsable" multiple="multiple">
                                                <?php foreach ($cargos as $cargo): ?>
                                                <option value="<?php echo htmlspecialchars($cargo['nombre']); ?>">
                                                    <?php echo htmlspecialchars($cargo['nombre']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="responsable_formulacion" id="formulacion_responsable_hidden">
                                        </div>
                                    </div>

                                    <div class="d-none" aria-hidden="true">
                                        <div class="col-12 col-md-6 col-lg-4 col-xxl-3 mb-3">
                                            <label class="form-label">5. PROYECTO</label>
                                            <select class="form-select" name="proyecto" id="formulacion_proyecto" onchange="calcularAcumuladoActividades(true); cargarPonderacionProyecto(); autoGuardarFormulacion(); validarPestanas(); actualizarLmpBadgeEnLista();">
                                                <option value="">Seleccione un proyecto</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-4 col-xxl-3 mb-3">
                                            <label class="form-label">7. PONDERACIÓN DE LOS PROYECTOS</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="ponderacion_proyectos" id="formulacion_ponderacion_proyectos" step="0.01" min="0" max="100" readonly placeholder="0.00" style="background-color: #e9ecef; cursor: not-allowed;">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PESTAÑA 2: INDICADOR DE RESULTADO -->
                            <div class="tab-pane fade" id="indicador" role="tabpanel" aria-labelledby="tab-indicador">
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <h5 class="indicador-title">INFORMACIÓN DEL INDICADOR</h5>
                                    </div>
                                    
                                    <div class="col-12 col-md-6 col-xxl-6 mb-3">
                                        <label class="form-label">10. NOMBRE DEL INDICADOR</label>
                                        <input type="text" class="form-control" name="nombre_indicador" id="formulacion_nombre_indicador" oninput="sincronizarNombreBorrador(); autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Ingrese el nombre del indicador">
                                    </div>

                                        <div class="col-12 col-md-6 col-xxl-3 mb-3">
                                            <label class="form-label">11. FRECUENCIA DE MEDICIÓN</label>
                                            <select class="form-select" name="frecuencia_medicion" id="formulacion_frecuencia_medicion" onchange="autoGuardarFormulacion(); validarPestanas()">
                                                <option value="">Seleccione frecuencia</option>
                                                <option value="Mensual">Mensual</option>
                                                <option value="Bimestral">Bimestral</option>
                                                <option value="Trimestral">Trimestral</option>
                                                <option value="Semestral">Semestral</option>
                                                <option value="Anual">Anual</option>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-6 col-xxl-3 mb-3">
                                            <label class="form-label">12. UNIDAD DE MEDIDA</label>
                                            <select class="form-select" name="unidad_medida" id="formulacion_unidad_medida" onchange="autoGuardarFormulacion(); validarPestanas()">
                                                <option value="">Seleccione unidad</option>
                                                <option value="Unidad">Unidad</option>
                                                <option value="Porcentaje">Porcentaje</option>
                                            </select>
                                        </div>

                                    <div class="col-12 col-md-6 col-xxl-6 mb-3">
                                        <label class="form-label">13. FÓRMULA DE LA MEDICIÓN</label>
                                        <textarea class="form-control" name="formula_medicion" id="formulacion_formula_medicion" rows="4" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Ej: (Número de estudiantes graduados / Total de estudiantes matriculados) * 100"></textarea>
                                    </div>

                                    <div class="col-12 col-md-6 col-xxl-6 mb-3">
                                        <label class="form-label">DESCRIPCIÓN DEL INDICADOR</label>
                                        <textarea class="form-control" name="descripcion_indicador" id="formulacion_descripcion_indicador" rows="4" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa detalladamente el indicador..."></textarea>
                                    </div>
                                    
                                    <div class="col-12 mt-4">
                                        <div class="meta-section">
                                            <h5 class="meta-title">METAS PROPUESTAS</h5>
                                            
                                            <div class="row">
                                                <div class="col-12 col-md-6 col-xl mb-3 order-1">
                                                    <label class="form-label">14. TIPO DE MEDICIÓN</label>
                                                    <select class="form-select" name="tipo_medicion" id="formulacion_tipo_medicion" onchange="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()">
                                                        <option value="">Seleccione tipo</option>
                                                        <option value="Acumulado">Acumulado</option>
                                                        <option value="Nuevo gestionado durante la vigencia">Nuevo gestionado durante la vigencia</option>
                                                        <option value="Promedio">Promedio</option>
                                                        <option value="Último valor reportado">Último valor reportado</option>
                                                        <option value="Límite">Límite</option>
                                                    </select>
                                                </div>

                                                <div class="col-12 col-md-6 col-xl mb-3 order-2">
                                                    <label class="form-label">15. LÍNEA BASE</label>
                                                    <input type="text" class="form-control" name="linea_base_meta" id="formulacion_linea_base_meta" oninput="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Valor de línea base">
                                                </div>
                                                
                                                <div class="col-12 col-md-6 col-xl mb-3 order-5">
                                                    <label class="form-label">META AÑO</label>
                                                    <input type="text" class="form-control" name="anio_base_meta" id="formulacion_anio_base_meta" readonly placeholder="Valor anual" style="background-color: #e8f5e9; font-weight: bold; color: #2e7d32;">
                                                </div>
                                                
                                                <div class="col-12 d-none">
                                                    <h6 class="mb-3" style="color: var(--color-primary);">METAS ANUALES</h6>
                                                </div>
                                                
                                                <div class="col-12 col-md-6 col-xl mb-3 order-3">
                                                    <label class="form-label">16. SEMESTRE 1</label>
                                                    <input type="text" class="form-control" name="meta_s1" id="formulacion_meta_s1" oninput="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Meta Semestre 1">
                                                </div>
                                                
                                                <div class="col-12 col-md-6 col-xl mb-3 order-4">
                                                    <label class="form-label">17. SEMESTRE 2</label>
                                                    <input type="text" class="form-control" name="meta_s2" id="formulacion_meta_s2" oninput="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Meta Semestre 2">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="gestionado_facultades" id="formulacion_gestionado_facultades" value="1" onchange="gestionarCheckboxFacultades(this); toggleDistribucionFacultadesTabla(this.checked)">
                                            <label class="form-check-label" for="formulacion_gestionado_facultades">
                                                <strong>ARTICULADO POR FACULTAD: MARQUE ✓ SI EL INDICADOR SERÁ GESTIONADO DESDE LAS FACULTADES</strong>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4" id="distribucionFacultadesWrap" style="display:none;">
                                        <div class="meta-section">
                                            <h5 class="meta-title">DISTRIBUCIÓN POR FACULTADES</h5>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle" id="tablaDistribucionFacultades">
                                                    <thead>
                                                        <tr>
                                                            <th>Facultad</th>
                                                            <th>Observación</th>
                                                            <th style="width:160px;">Distribución</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($facultades as $fac): ?>
                                                        <tr data-facultad-id="<?php echo (int)$fac['id']; ?>">
                                                            <td><?php echo htmlspecialchars($fac['nombre']); ?></td>
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm distribucion-observacion" placeholder="Observación...">
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control form-control-sm distribucion-valor" step="0.01" min="0" placeholder="0.00">
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="2" class="text-end fw-bold">Total distribuido:</td>
                                                            <td><span id="distribucionTotalBadge" class="fw-bold">0.00</span></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- PESTAÑA 3: PLANES INSTITUCIONALES -->
                            <div class="tab-pane fade" id="planes" role="tabpanel" aria-labelledby="tab-planes">
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <h5 class="indicador-title">PLANES INSTITUCIONALES</h5>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">SELECCIONAR PLAN</label>
                                        <select class="form-select" id="selectPlanInstitucional" style="width: 100%;">
                                            <option value="">-- Buscar y seleccionar plan --</option>
                                            <?php foreach ($planes_institucionales as $plan): ?>
                                            <option value="<?php echo htmlspecialchars($plan['nombre']); ?>">
                                                <?php echo htmlspecialchars($plan['nombre']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-primary mt-2" onclick="agregarPlan()">
                                            <i class="fas fa-plus me-2"></i>Agregar Plan
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">PLANES SELECCIONADOS</label>
                                        <div id="contenedorPlanes" style="max-height: 300px; overflow-y: auto; border: 1px solid #ced4da; border-radius: 8px; padding: 10px; background-color: #f8f9fa;">
                                            <p class="text-muted text-center mb-0">No hay planes seleccionados</p>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="planes_institucionales" id="formulacion_planes_institucionales" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA SEGUIMIENTO -->
    <!-- Modal Importar plantilla -->
    <div class="modal fade" id="modalImportarPlantilla" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-upload me-2"></i>Importar plantilla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ol class="small text-muted ps-3">
                        <li>Pulsa <strong>Exportar plantilla</strong> y abre el Excel. Tiene dos hojas: <strong>Formulación</strong> y <strong>Seguimiento</strong>.</li>
                        <li><strong>Formulación:</strong> corrige tus indicadores en borrador o agrega nuevos en las filas vacías (columna ID vacía).</li>
                        <li><strong>Seguimiento:</strong> llena el avance de los indicadores ya aprobados. No cambies la columna ID.</li>
                        <li>Guarda el archivo y súbelo aquí.</li>
                    </ol>
                    <input type="file" class="form-control" id="archivoPlantilla" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnImportarPlantilla" onclick="importarPlantilla()">
                        <i class="fas fa-upload me-1"></i>Importar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSeguimiento" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header" style="background: #143163;">
                    <h5 class="modal-title" id="tituloSeguimiento" ondblclick="editarTituloModal('seguimiento')">
                        <i class="fas fa-chart-line me-2"></i>SEGUIMIENTO 144 - <span id="tituloSeguimientoSpan"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSeguimiento">
                    <input type="hidden" name="modulo" value="seguimiento">
                    <input type="hidden" id="seguimiento_id" name="id">

                    <div class="modal-body-scroll">
                        <div class="alert alert-warning py-2 small" id="segBloqueoAviso" style="display:none;"><i class="fas fa-lock me-2"></i><span></span></div>
                        <!-- Información previa (solo lectura, viene de Formulación) -->
                        <details class="seg-ctx" open>
                            <summary>Información previa <small>(viene de Formulación, no se edita)</small></summary>
                            <div class="row g-3 px-3 pb-3">
                                <div class="col-12 col-md-6 col-xxl-3"><label class="form-label text-muted">LÍNEA ESTRATÉGICA</label><div class="bg-light-view" id="seguimiento_linea_view">-</div></div>
                                <div class="col-12 col-md-6 col-xxl-3"><label class="form-label text-muted">MOTOR DE DESARROLLO</label><div class="bg-light-view" id="seguimiento_motor_view">-</div></div>
                                <div class="col-12 col-md-6 col-xxl-3"><label class="form-label text-muted">PLAN BASE</label><div class="bg-light-view" id="seguimiento_id_indicador_view">-</div></div>
                                <div class="col-12 col-md-6 col-xxl-3"><label class="form-label text-muted">FRECUENCIA DE MEDICIÓN</label><div class="bg-light-view" id="seguimiento_frecuencia_medicion_view">-</div></div>
                                <div class="col-12 col-md-12 col-xxl-6"><label class="form-label text-muted">FÓRMULA DEL INDICADOR</label><div class="bg-light-view" id="seguimiento_formula_medicion_view" style="min-height:64px;">-</div></div>
                                <div class="col-12 col-md-6 col-xxl-3"><label class="form-label text-muted">UNIDAD DE MEDIDA</label><div class="bg-light-view" id="seguimiento_unidad_medida_view">-</div></div>
                                <div class="col-12 col-md-6 col-xxl-3"><label class="form-label text-muted">TIPO DE MEDICIÓN</label><div class="bg-light-view" id="seguimiento_tipo_medicion_view">-</div></div>
                                <div class="col-12"><label class="form-label text-muted">DESCRIPCIÓN DEL INDICADOR</label><div class="bg-light-view" id="seguimiento_descripcion_indicador_view" style="min-height:64px; white-space:pre-line;">-</div></div>
                            </div>
                        </details>

                        <!-- Campos ocultos requeridos por el JS existente (autoguardado, etc.) -->
                        <span id="seguimiento_proyecto_view" class="d-none"></span>
                        <span id="seguimiento_objetivo_view" class="d-none"></span>
                        <span id="seguimiento_estrategia_view" class="d-none"></span>
                        <span id="seguimiento_meta_resultado_view" class="d-none"></span>
                        <span id="seguimiento_ponderacion_proyectos_view" class="d-none"></span>
                        <span id="seguimiento_actividad_view" class="d-none"></span>
                        <span id="seguimiento_ponderacion_actividades_view" class="d-none"></span>
                        <span id="seguimiento_responsable_view" class="d-none"></span>
                        <input type="hidden" id="seguimiento_indicador" name="indicador">
                        <input type="hidden" id="seguimiento_fecha" name="fecha_seguimiento">
                        <input type="hidden" id="seguimiento_meta_programada" name="meta_programada">
                        <input type="hidden" id="seguimiento_meta_ejecutada" name="meta_ejecutada">
                        <input type="hidden" id="seguimiento_porcentaje" name="porcentaje_avance">
                        <input type="hidden" id="seguimiento_responsable" name="responsable_seguimiento">
                        <input type="hidden" id="seguimiento_observaciones" name="observaciones">

                        <div class="indicador-section field-group">
                            <h5 class="indicador-title"><i class="fas fa-chart-line me-2"></i>SEGUIMIENTO</h5>
                            <div class="seg-card">
                                <div class="seg-tiles">
                                    <div class="seg-tile" id="seguimiento_linea_base_wrap" style="display:none;">
                                        <label class="form-label text-muted">LÍNEA BASE</label>
                                        <div class="seg-num" id="seguimiento_linea_base_view">-</div>
                                    </div>
                                    <div class="seg-tile seg-tile-total">
                                        <label class="form-label text-muted">TOTAL (META DEL AÑO)</label>
                                        <div class="seg-num" id="seguimiento_anio_view">-</div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <label class="form-label text-muted d-block" style="font-size:0.75rem;">PORCENTAJE DE CUMPLIMIENTO</label>
                                    <div class="cumplimiento-ring-wrap">
                                        <svg width="84" height="84" viewBox="0 0 90 90">
                                            <circle cx="45" cy="45" r="38" fill="none" stroke="#e9ecef" stroke-width="8"></circle>
                                            <circle cx="45" cy="45" r="38" fill="none" stroke="#FF3B30" stroke-width="8"
                                                    stroke-linecap="round" stroke-dasharray="238.76" stroke-dashoffset="232.76"
                                                    transform="rotate(-90 45 45)" id="seguimiento_cumplimiento_arc"></circle>
                                        </svg>
                                        <div class="cumplimiento-ring-value" id="seguimiento_cumplimiento_text">0%</div>
                                    </div>
                                </div>
                            </div>

                            <div class="seg-sems">
                                <div class="seg-sem">
                                    <div class="seg-sem-head">
                                        <label class="form-label mb-0" for="seguimiento_semestre1">SEMESTRE 1</label>
                                        <span class="seg-ev-label">Evidencias
                                            <button type="button" class="seg-ev-btn" onclick="abrirEvidencias(0, 1)" title="Evidencias del semestre 1 (hasta 5 archivos)" aria-label="Evidencias del semestre 1">
                                                <i class="fas fa-plus"></i><span class="ev-n" id="segEvGenN1">0</span>
                                            </button>
                                        </span>
                                    </div>
                                    <input type="number" class="form-control seg-input" name="semestre1_seguimiento" id="seguimiento_semestre1" step="0.01" oninput="calcularCumplimientoSeguimiento(); autoGuardarSeguimiento()">
                                </div>
                                <div class="seg-sem">
                                    <div class="seg-sem-head">
                                        <label class="form-label mb-0" for="seguimiento_semestre2">SEMESTRE 2</label>
                                        <span class="seg-ev-label">Evidencias
                                            <button type="button" class="seg-ev-btn" onclick="abrirEvidencias(0, 2)" title="Evidencias del semestre 2 (hasta 5 archivos)" aria-label="Evidencias del semestre 2">
                                                <i class="fas fa-plus"></i><span class="ev-n" id="segEvGenN2">0</span>
                                            </button>
                                        </span>
                                    </div>
                                    <input type="number" class="form-control seg-input" name="semestre2_seguimiento" id="seguimiento_semestre2" step="0.01" oninput="calcularCumplimientoSeguimiento(); autoGuardarSeguimiento()">
                                </div>
                            </div>
                        </div>

                        <div class="indicador-section field-group">
                            <h5 class="indicador-title"><i class="fas fa-chart-line me-2"></i>ESTADO DE PROYECTOS</h5>
                            <div class="seg-estado-sem">
                                <div class="seg-estado-label"><span>SEMESTRE</span><b>1</b></div>
                                <div class="row g-3 seg-estado-campos">
                                    <div class="col-12 col-md-6 col-xl-3"><label class="form-label">LOGROS</label><textarea class="form-control" name="logros" id="seguimiento_logros" rows="4" oninput="autoGuardarSeguimiento()"></textarea></div>
                                    <div class="col-12 col-md-6 col-xl-3"><label class="form-label">LÍMITES</label><textarea class="form-control" name="limites" id="seguimiento_limites" rows="4" oninput="autoGuardarSeguimiento()"></textarea></div>
                                    <div class="col-12 col-md-6 col-xl-3"><label class="form-label">OBSERVACIÓN</label><textarea class="form-control" name="observacion_estado" id="seguimiento_observacion_estado" rows="4" oninput="autoGuardarSeguimiento()"></textarea></div>
                                    <div class="col-12 col-md-6 col-xl-3"><label class="form-label">ACCIONES DE FORTALECIMIENTO</label><textarea class="form-control" name="acciones_fortalecimiento" id="seguimiento_acciones_fortalecimiento" rows="4" oninput="autoGuardarSeguimiento()"></textarea></div>
                                </div>
                            </div>
                            <div class="seg-estado-sem">
                                <div class="seg-estado-label"><span>SEMESTRE</span><b>2</b></div>
                                <div class="row g-3 seg-estado-campos">
                                    <div class="col-12 col-md-6 col-xl-3"><label class="form-label">LOGROS</label><textarea class="form-control" name="logros_sem2" id="seguimiento_logros_sem2" rows="4" oninput="autoGuardarSeguimiento()"></textarea></div>
                                    <div class="col-12 col-md-6 col-xl-3"><label class="form-label">LÍMITES</label><textarea class="form-control" name="limites_sem2" id="seguimiento_limites_sem2" rows="4" oninput="autoGuardarSeguimiento()"></textarea></div>
                                    <div class="col-12 col-md-6 col-xl-3"><label class="form-label">OBSERVACIÓN</label><textarea class="form-control" name="observacion_estado_sem2" id="seguimiento_observacion_estado_sem2" rows="4" oninput="autoGuardarSeguimiento()"></textarea></div>
                                    <div class="col-12 col-md-6 col-xl-3"><label class="form-label">ACCIONES DE FORTALECIMIENTO</label><textarea class="form-control" name="acciones_fortalecimiento_sem2" id="seguimiento_acciones_fortalecimiento_sem2" rows="4" oninput="autoGuardarSeguimiento()"></textarea></div>
                                </div>
                            </div>
                        </div>

                        <div class="indicador-section field-group" id="seguimientoFacultadesWrap" style="display:none;">
                            <h5 class="indicador-title"><i class="fas fa-university me-2"></i>SEGUIMIENTO POR FACULTADES</h5>
                            <p class="text-muted small mb-2" id="segFacAyuda">Cada facultad registra el avance de su parte de la distribución en cada semestre.</p>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0" id="tablaSeguimientoFacultades">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Facultad</th>
                                            <th rowspan="2" style="width:110px;" class="text-end">Distribución</th>
                                            <th colspan="3" class="text-center">Semestre 1</th>
                                            <th colspan="3" class="text-center">Semestre 2</th>
                                        </tr>
                                        <tr>
                                            <th style="width:120px;">Seguimiento</th>
                                            <th style="min-width:200px;">Descripción</th>
                                            <th style="width:90px;" class="text-center">Evidencias</th>
                                            <th style="width:120px;">Seguimiento</th>
                                            <th style="min-width:200px;">Descripción</th>
                                            <th style="width:90px;" class="text-center">Evidencias</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($facultades as $fac): ?>
                                        <tr data-facultad-id="<?php echo (int)$fac['id']; ?>" style="display:none;">
                                            <td><?php echo htmlspecialchars($fac['nombre']); ?></td>
                                            <td class="text-end seg-fac-distribucion">0</td>
                                            <td><input type="number" class="form-control form-control-sm seg-fac-s1" step="0.01" min="0" placeholder="—" readonly tabindex="-1" title="Valor concertado (se asigna en Concertaciones)" style="background:#f1f3f8;cursor:not-allowed"></td>
                                            <td><input type="text" class="form-control form-control-sm seg-fac-desc1" maxlength="1000" placeholder="Descripción del semestre 1..."></td>
                                            <td class="text-center">
                                                <button type="button" class="seg-ev-btn" onclick="abrirEvidencias(<?php echo (int)$fac['id']; ?>, 1)" title="Evidencias del semestre 1 de <?php echo htmlspecialchars($fac['nombre']); ?> (hasta 5 archivos)" aria-label="Evidencias del semestre 1 de <?php echo htmlspecialchars($fac['nombre']); ?>">
                                                    <i class="fas fa-plus"></i><span class="ev-n" data-sem="1">0</span>
                                                </button>
                                            </td>
                                            <td><input type="number" class="form-control form-control-sm seg-fac-s2" step="0.01" min="0" placeholder="—" readonly tabindex="-1" title="Valor concertado (se asigna en Concertaciones)" style="background:#f1f3f8;cursor:not-allowed"></td>
                                            <td><input type="text" class="form-control form-control-sm seg-fac-desc2" maxlength="1000" placeholder="Descripción del semestre 2..."></td>
                                            <td class="text-center">
                                                <button type="button" class="seg-ev-btn" onclick="abrirEvidencias(<?php echo (int)$fac['id']; ?>, 2)" title="Evidencias del semestre 2 de <?php echo htmlspecialchars($fac['nombre']); ?> (hasta 5 archivos)" aria-label="Evidencias del semestre 2 de <?php echo htmlspecialchars($fac['nombre']); ?>">
                                                    <i class="fas fa-plus"></i><span class="ev-n" data-sem="2">0</span>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="fw-bold">Total</td>
                                            <td class="text-end fw-bold" id="segFacTotalDist">0</td>
                                            <td class="fw-bold" id="segFacTotalS1">0</td>
                                            <td></td>
                                            <td></td>
                                            <td class="fw-bold" id="segFacTotalS2">0</td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- VENTANA: concertación de un indicador (todas sus facultades) -->
    <div class="modal fade" id="modalConcertacion" tabindex="-1" aria-labelledby="cnTituloModal" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content cnx-modal">
                <div class="modal-header">
                    <div class="cnx-nav">
                        <button type="button" class="cnx-flechas" id="cnPrev" aria-label="Indicador anterior" title="Indicador anterior"><i class="fas fa-chevron-left"></i></button>
                        <span id="cnPos"></span>
                        <button type="button" class="cnx-flechas" id="cnNext" aria-label="Indicador siguiente" title="Indicador siguiente"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <h5 class="modal-title" id="cnTituloModal">Concertación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="cnx-ind" id="cnIndicador"></div>
                    <div class="cnx-metas" id="cnMeta"></div>
                    <div class="cnx-comp" id="cnResumenDep"></div>
                    <div class="alert alert-danger py-2 small" id="cnError" style="display:none;"></div>
                    <p class="cnx-ayuda" id="cnAyuda"></p>
                    <div id="cnSemestres"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- VENTANA EMERGENTE: evidencias (hasta 5 archivos, con enlaces para consultarlos) -->
    <div class="modal fade" id="modalEvidencias" tabindex="-1" aria-labelledby="evTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background:#143163; color:#fff;">
                    <h5 class="modal-title" id="evTitulo">Evidencias</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="fw-bold" id="evQuien"></div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 my-2" id="evSubir">
                        <button type="button" class="btn btn-primary btn-sm" id="evBtnElegir"><i class="fas fa-plus me-1"></i>Seleccionar archivos</button>
                        <span class="badge bg-light text-dark border" id="evContador">0 de 5</span>
                        <input type="file" id="evInput" multiple hidden accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.txt,.csv,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                    </div>
                    <p class="text-muted small mb-2" id="evReglas">PDF, imágenes, Word, Excel, PowerPoint, TXT o CSV · máx. 10 MB cada uno.</p>
                    <div class="alert alert-warning py-2 small" id="evError" style="display:none;"></div>
                    <ul class="ev-lista list-unstyled mb-2" id="evLista"></ul>
                    <p class="ev-vacio text-center text-muted mb-2" id="evVacio">Aún no hay archivos cargados.</p>
                    <div class="ev-ruta" id="evRuta"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Listo</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalVerFormulario" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: #143163;">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Ver Formulación - <span id="ver_titulo"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="ver_contenido"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Editar ítem gestionado desde facultades (vacío por ahora) -->
    <div class="modal fade" id="modalEditarFacultadItem" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: #143163;">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <input type="hidden" id="facultadItem_id">
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const basePath = '<?php echo $basePath; ?>';
        const formularioId = <?php echo $formulario['id']; ?>;
        const formularioAnio = <?php echo intval($formulario['anio'] ?? 0); ?>; // Año heredado del formulario padre

        const TODAS_FACULTADES = <?php
            echo json_encode(array_map(function($f) {
                return ['id' => (int)$f['id'], 'nombre' => $f['nombre'], 'codigo' => $f['codigo'] ?? ''];
            }, array_values(array_filter($facultades ?? [], function($f) { return $f['estado'] == 1; }))));
        ?>;

        const FACULTAD_ITEMS = <?php
            $facultades_lookup = [];
            foreach (($facultades ?? []) as $f) { $facultades_lookup[$f['id']] = $f; }
            $facultad_items_js = [];
            foreach (($formulaciones_con_check ?? []) as $it) {
                $facInfo = $facultades_lookup[$it['facultad_id'] ?? 0] ?? null;
                $facultad_items_js[] = [
                    'id'                 => (int)$it['id'],
                    'facultad_id'        => (int)($it['facultad_id'] ?? 0),
                    'facultad_nombre'    => $facInfo['nombre'] ?? 'Sin facultad',
                    'facultad_codigo'    => $facInfo['codigo'] ?? '',
                    'nombre_borrador'    => $it['nombre_borrador'] ?? '',
                    'estado_formulacion' => (int)$it['estado_formulacion'],
                ];
            }
            echo json_encode($facultad_items_js);
        ?>;

        const FILTER_USUARIO_ID = <?php echo (int)($_SESSION['usuario_id'] ?? 0); ?>;
        const FILTER_CARGOS     = <?php echo json_encode($cargos); ?>;
        const FILTER_CREADORES  = <?php
            $todos_creadores_js = [];
            foreach ($datos_modulos as $_km => $_mod) {
                foreach (array_merge($_mod['borradores'], $_mod['publicados'], $_mod['cancelados']) as $_it) {
                    if (!empty($_it['creado_por']) && !empty($_it['creado_por_nombre'])) {
                        $todos_creadores_js[$_it['creado_por']] = ['id' => $_it['creado_por'], 'nombre' => $_it['creado_por_nombre']];
                    }
                }
            }
            echo json_encode(array_values($todos_creadores_js));
        ?>;
        const FILTER_PREFS = <?php echo json_encode($filter_preferences ?? []); ?>;
        
        // Datos de formulaciones — se refresca vía AJAX para mantener sincronía
        let formulacionesExistentes = <?php
            $todas = [];
            $modulos_check = ['formulacion'];
            foreach ($modulos_check as $mk) {
                if (isset($datos_modulos[$mk])) {
                    foreach (['borradores', 'publicados'] as $estado) {
                        if (isset($datos_modulos[$mk][$estado])) {
                            foreach ($datos_modulos[$mk][$estado] as $b) {
                                if (!empty($b['proyecto']) && isset($b['ponderacion_actividades'])) {
                                    $todas[] = [
                                        'id' => intval($b['id']),
                                        'proyecto' => $b['proyecto'],
                                        'ponderacion_actividades' => floatval($b['ponderacion_actividades'])
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            echo json_encode($todas);
        ?>;


        // Refresca formulacionesExistentes desde el servidor
        function refrescarFormulacionesExistentes(callback) {
            $.ajax({
                url: basePath + '/modulo144/getPonderaciones?formulario_id=' + formularioId,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        formulacionesExistentes = res.formulaciones.map(function(f) {
                            return { id: parseInt(f.id), proyecto: f.proyecto, ponderacion_actividades: parseFloat(f.ponderacion_actividades) || 0 };
                        });
                    }
                    if (typeof callback === 'function') callback();
                },
                error: function() {
                    if (typeof callback === 'function') callback();
                }
            });
        }

        // Recuerda la pestaña principal visible (hijo directo: las pestañas internas de los módulos ocultos
        // también tienen .active) y la interna (Borradores / Publicados / Cancelados) para restaurarlas al recargar
        function guardarEstadoAcordeon() {
            const abierto = document.querySelector('#moduloTabsTopContent > .tab-pane.active');
            if (abierto) {
                const interno = abierto.querySelector('.tab-content > .tab-pane.active');
                sessionStorage.setItem('mod144_acordeon_' + formularioId, JSON.stringify({
                    top: abierto.id,
                    inner: interno ? interno.id : null
                }));
            } else {
                sessionStorage.removeItem('mod144_acordeon_' + formularioId);
            }
        }

        function restaurarEstadoAcordeon() {
            const raw = sessionStorage.getItem('mod144_acordeon_' + formularioId);
            if (!raw) return;
            sessionStorage.removeItem('mod144_acordeon_' + formularioId);
            let estado;
            try { estado = JSON.parse(raw); } catch (e) { estado = { top: raw, inner: null }; }
            [estado.top, estado.inner].forEach(function (id) {
                if (!id) return;
                const btn = document.querySelector('[data-bs-target="#' + id + '"]');
                if (btn) new bootstrap.Tab(btn).show();
            });
        }

        $(document).ready(function() {
            restaurarEstadoAcordeon();
        });

        let timeoutId = null;
        // Autoguardado de formulación: timer propio, "sesión" por cada apertura del modal (para descartar
        // respuestas AJAX de una apertura anterior) y bandera de edición real del usuario (abrir/ver no guarda).
        let timeoutFormulacion = null;
        let formulacionSesion = 0;
        let formulacionEditadaPorUsuario = false;
        let currentModule = null;
        let editandoTitulo = false;
        let planesSeleccionados = [];
        
        // FUNCIÓN PARA OBTENER EL ÚLTIMO VALOR REPORTADO (Puesto 1: Línea Base, Puesto 2: Semestre 1, Puesto 3: Semestre 2)
        function obtenerUltimoValorReportado(lineaBase, metaS1, metaS2) {
            // Prioridad: Semestre 2 (puesto 3) -> Semestre 1 (puesto 2) -> Línea Base (puesto 1)
            if (metaS2 !== null && metaS2 !== undefined && metaS2 !== '' && !isNaN(metaS2)) {
                return parseFloat(metaS2);
            }
            if (metaS1 !== null && metaS1 !== undefined && metaS1 !== '' && !isNaN(metaS1)) {
                return parseFloat(metaS1);
            }
            if (lineaBase !== null && lineaBase !== undefined && lineaBase !== '' && !isNaN(lineaBase)) {
                return parseFloat(lineaBase);
            }
            return null;
        }
        
        // FUNCIÓN PARA CALCULAR EL VALOR ANUAL SEGÚN EL TIPO DE MEDICIÓN
        function calcularValorAnual() {
            const tipoMedicion = $('#formulacion_tipo_medicion').val();
            const lineaBaseRaw = $('#formulacion_linea_base_meta').val();
            const metaS1Raw = $('#formulacion_meta_s1').val();
            const metaS2Raw = $('#formulacion_meta_s2').val();
            
            const lineaBase = (lineaBaseRaw && lineaBaseRaw !== '') ? parseFloat(lineaBaseRaw) : null;
            const metaS1 = (metaS1Raw && metaS1Raw !== '') ? parseFloat(metaS1Raw) : null;
            const metaS2 = (metaS2Raw && metaS2Raw !== '') ? parseFloat(metaS2Raw) : null;
            
            let valorAnual = '';
            
            if (tipoMedicion === 'Acumulado') {
                // Acumulado = Línea Base + Semestre 1 + Semestre 2
                let suma = 0;
                if (lineaBase !== null) suma += lineaBase;
                if (metaS1 !== null) suma += metaS1;
                if (metaS2 !== null) suma += metaS2;
                valorAnual = suma.toFixed(2);
            } 
            else if (tipoMedicion === 'Nuevo gestionado durante la vigencia') {
                // Nuevo gestionado durante la vigencia = Semestre 1 + Semestre 2
                let suma = 0;
                if (metaS1 !== null) suma += metaS1;
                if (metaS2 !== null) suma += metaS2;
                valorAnual = suma.toFixed(2);
            }
            else if (tipoMedicion === 'Promedio') {
                // Promedio = (Semestre 1 + Semestre 2) / 2
                let suma = 0;
                let contador = 0;
                if (metaS1 !== null) { suma += metaS1; contador++; }
                if (metaS2 !== null) { suma += metaS2; contador++; }
                valorAnual = contador > 0 ? (suma / contador).toFixed(2) : '0.00';
            }
            else if (tipoMedicion === 'Último valor reportado') {
                // Último valor reportado = prioridad: Semestre 2 -> Semestre 1 -> Línea Base
                const ultimoValor = obtenerUltimoValorReportado(lineaBase, metaS1, metaS2);
                valorAnual = ultimoValor !== null ? ultimoValor.toFixed(2) : '';
            }
            else if (tipoMedicion === 'Límite') {
                // Límite = valor de Semestre 2
                valorAnual = metaS2 !== null ? metaS2.toFixed(2) : '';
            }
            else {
                valorAnual = '';
            }
            
            $('#formulacion_anio_base_meta').val(valorAnual);

            // Bloquear/desbloquear Línea Base y Semestre 1 según tipo Límite
            if (tipoMedicion === 'Límite') {
                $('#formulacion_linea_base_meta')
                    .prop('readonly', true)
                    .val('')
                    .css({ 'background-color': '#e9ecef', 'opacity': '0.6', 'cursor': 'not-allowed' });
                $('#formulacion_meta_s1')
                    .prop('readonly', true)
                    .val('')
                    .css({ 'background-color': '#e9ecef', 'opacity': '0.6', 'cursor': 'not-allowed' });
            } else {
                $('#formulacion_linea_base_meta')
                    .prop('readonly', false)
                    .css({ 'background-color': '', 'opacity': '', 'cursor': '' });
                $('#formulacion_meta_s1')
                    .prop('readonly', false)
                    .css({ 'background-color': '', 'opacity': '', 'cursor': '' });
            }
        }
        
        // ── ACUMULADO DE PONDERACIÓN DE ACTIVIDADES POR PROYECTO ────────────
        function _ejecutarCalculoAcumulado() {
            const proyectoSeleccionado = $('#formulacion_proyecto').val();
            const idActual = $('#formulacion_id').val() ? parseInt($('#formulacion_id').val()) : null;
            const valorActual = parseFloat($('#formulacion_ponderacion_actividades').val()) || 0;
            const badge = $('#acumulado_actividades_badge');
            const msg   = $('#acumulado_actividades_msg');
            const input = $('#formulacion_ponderacion_actividades');

            if (!proyectoSeleccionado) {
                badge.text('Acum: — / 100%').css({ color: '#6c757d', background: '#f8f9fa', borderColor: '#dee2e6' });
                msg.hide();
                return;
            }

            // Suma de otros registros con el mismo proyecto (excluye el actual)
            // Usamos == en lugar de !== para evitar bug string vs integer (PDO retorna strings)
            let acumuladoOtros = 0;
            formulacionesExistentes.forEach(function(f) {
                if (f.proyecto === proyectoSeleccionado && parseInt(f.id) !== idActual) {
                    acumuladoOtros += parseFloat(f.ponderacion_actividades) || 0;
                }
            });

            const disponible = Math.max(0, 100 - acumuladoOtros);

            // Ajustar el atributo max del input al disponible real
            input.attr('max', disponible.toFixed(2));

            // Si el valor escrito supera lo disponible, lo recortamos al máximo
            let valorFinal = valorActual;
            if (valorActual > disponible) {
                valorFinal = parseFloat(disponible.toFixed(2));
                input.val(valorFinal);
            }

            // Actualizar data-ponderacion del item activo en el DOM
            const borradorId = $('#formulacion_id').val();
            if (borradorId) {
                const listItem = document.querySelector(`.lista-item[data-item-id="${borradorId}"]`);
                if (listItem) {
                    listItem.setAttribute('data-ponderacion', valorFinal);
                    const lineaItem   = listItem.getAttribute('data-linea-item');
                    const motorItem   = listItem.getAttribute('data-motor-item');
                    const proyectoItem = listItem.getAttribute('data-proyecto-item');
                    const moduloItem  = listItem.getAttribute('data-modulo');
                    // Repintar badges solo del mismo L-M-P en tiempo real
                    actualizarBadgesLinea(lineaItem, motorItem, proyectoItem, moduloItem);
                }
            }

            const totalConActual = acumuladoOtros + valorFinal;

            // Actualizar badge
            badge.text('Acum: ' + totalConActual.toFixed(2) + ' / 100%');

            if (totalConActual > 100) {
                // No debería llegar aquí tras el clamp, pero por seguridad
                badge.css({ color: '#fff', background: '#E74C3C', borderColor: '#E74C3C' });
                msg.html('<i class="fas fa-exclamation-triangle me-1"></i>Supera el 100%. Disponible: <strong>' + disponible.toFixed(2) + '%</strong>')
                   .css('color', '#E74C3C').show();
                input.css({ borderColor: '#E74C3C', boxShadow: '0 0 0 0.2rem rgba(231,76,60,0.25)' });
            } else if (totalConActual === 100) {
                // Completo justo
                badge.css({ color: '#fff', background: '#27AE60', borderColor: '#27AE60' });
                msg.html('<i class="fas fa-check-circle me-1"></i>Completo al 100%')
                   .css('color', '#27AE60').show();
                input.css({ borderColor: '#27AE60', boxShadow: '0 0 0 0.2rem rgba(39,174,96,0.25)' });
            } else if (totalConActual > 80) {
                // Advertencia: casi lleno
                badge.css({ color: '#fff', background: '#F39C12', borderColor: '#F39C12' });
                msg.html('<i class="fas fa-info-circle me-1"></i>Disponible: <strong>' + disponible.toFixed(2) + '%</strong>')
                   .css('color', '#F39C12').show();
                input.css({ borderColor: '#F39C12', boxShadow: '0 0 0 0.2rem rgba(243,156,18,0.25)' });
            } else {
                // Normal
                badge.css({ color: '#2C3E50', background: '#f0f8ff', borderColor: '#3498DB' });
                msg.html('<i class="fas fa-info-circle me-1"></i>Disponible: <strong>' + disponible.toFixed(2) + '%</strong>')
                   .css('color', '#3498DB').show();
                input.css({ borderColor: '#ced4da', boxShadow: '' });
            }
        }

        // Wrapper: refresca datos del servidor antes de calcular cuando cambia el proyecto
        function calcularAcumuladoActividades(forzarRefresh) {
            if (forzarRefresh) {
                refrescarFormulacionesExistentes(function() { _ejecutarCalculoAcumulado(); });
            } else {
                _ejecutarCalculoAcumulado();
            }
        }
        // ────────────────────────────────────────────────────────────────────

        // ── CARGA AUTOMÁTICA DE PONDERACIÓN DE PROYECTOS DESDE data_proyectos ──
        function cargarPonderacionProyecto() {
            const selectProyecto = document.getElementById('formulacion_proyecto');
            const proyectoOption = selectProyecto ? selectProyecto.options[selectProyecto.selectedIndex] : null;
            const proyectoId = proyectoOption ? proyectoOption.getAttribute('data-proyecto-id') : null;

            // El año viene del formulario padre (formularios.anio)
            const anio = formularioAnio;

            const input = $('#formulacion_ponderacion_proyectos');

            if (!proyectoId || !anio) {
                input.val('');
                return;
            }

            const sesion = formulacionSesion;
            $.ajax({
                url: basePath + '/modulo144/getPonderacionProyecto',
                type: 'GET',
                data: { proyecto_id: proyectoId, anio: anio },
                dataType: 'json',
                success: function(response) {
                    if (sesion !== formulacionSesion) return;
                    if (response.success && response.porcentaje !== null) {
                        input.val(parseFloat(response.porcentaje).toFixed(2));
                    } else {
                        input.val('');
                    }
                    autoGuardarFormulacion();
                    validarPestanas();
                },
                error: function() {
                    input.val('');
                }
            });
        }
        // ────────────────────────────────────────────────────────────────────

        <?php if ($estado_fechas['valido'] && $fecha_cierre): ?>
        function actualizarContador() {
            const fechaCierre = new Date('<?php echo $fecha_cierre; ?>').getTime();
            const ahora = new Date().getTime();
            const distancia = fechaCierre - ahora;
            
            if (distancia < 0) {
                document.getElementById('days').innerHTML = '00';
                document.getElementById('hours').innerHTML = '00';
                document.getElementById('minutes').innerHTML = '00';
                document.getElementById('seconds').innerHTML = '00';
                setTimeout(() => { guardarEstadoAcordeon(); location.reload(); }, 3000);
                return;
            }
            
            document.getElementById('days').innerHTML = Math.floor(distancia / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
            document.getElementById('hours').innerHTML = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
            document.getElementById('minutes').innerHTML = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
            document.getElementById('seconds').innerHTML = Math.floor((distancia % (1000 * 60)) / 1000).toString().padStart(2, '0');
        }
        actualizarContador();
        setInterval(actualizarContador, 1000);
        <?php endif; ?>

        function gestionarCheckboxFacultades(checkbox) {
            const id = $('#formulacion_id').val();
            const estadoActual = checkbox.checked;
            
            Swal.fire({
                title: estadoActual ? '¿Activar gestión desde facultades?' : '¿Desactivar gestión desde facultades?',
                html: estadoActual ? 
                    'Se creará un formulario de SEGUIMIENTO para cada facultad basado en esta formulación.' :
                    'Se ELIMINARÁN todos los formularios de seguimiento asociados a esta formulación en las facultades. Esta acción no se puede deshacer.',
                icon: estadoActual ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: estadoActual ? '#27AE60' : '#E74C3C',
                confirmButtonText: estadoActual ? 'Sí, activar' : 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: estadoActual ? 'Activando...' : 'Desactivando...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    
                    const data = {
                        modulo: 'formulacion',
                        id: id,
                        gestionado_facultades: estadoActual ? 1 : 0
                    };
                    
                    $.ajax({
                        url: basePath + '/modulo144/guardar',
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: estadoActual ? '¡Activado!' : '¡Desactivado!',
                                    text: estadoActual ? 'Se ha activado la gestión desde facultades.' : 'Se ha desactivado la gestión desde facultades.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    guardarEstadoAcordeon();
                                    location.reload();
                                });
                            } else {
                                checkbox.checked = !estadoActual;
                                toggleDistribucionFacultadesTabla(checkbox.checked);
                                Swal.fire('Error', response.message || 'No se pudo guardar el cambio', 'error');
                            }
                        },
                        error: function() {
                            checkbox.checked = !estadoActual;
                            toggleDistribucionFacultadesTabla(checkbox.checked);
                            Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
                        }
                    });
                } else {
                    checkbox.checked = !estadoActual;
                    toggleDistribucionFacultadesTabla(checkbox.checked);
                }
            });
        }

        function toggleDistribucionFacultadesTabla(mostrar) {
            $('#distribucionFacultadesWrap').toggle(!!mostrar);
        }

        function cargarDistribucionFacultades(formulacionId) {
            $('#tablaDistribucionFacultades tbody tr').each(function() {
                $(this).find('.distribucion-observacion').val('');
                $(this).find('.distribucion-valor').val('');
            });
            actualizarTotalDistribucionFacultades();
            if (!formulacionId) return;

            $.ajax({
                url: basePath + '/modulo144/getDistribucionFacultades',
                type: 'GET',
                data: { formulacion_id: formulacionId },
                dataType: 'json',
                success: function(response) {
                    if (!response.success) return;
                    (response.distribucion || []).forEach(function(d) {
                        const fila = $('#tablaDistribucionFacultades tbody tr[data-facultad-id="' + d.facultad_id + '"]');
                        if (!fila.length) return;
                        fila.find('.distribucion-observacion').val(d.observacion || '');
                        fila.find('.distribucion-valor').val(d.distribucion || '');
                    });
                    actualizarTotalDistribucionFacultades();
                }
            });
        }

        function actualizarTotalDistribucionFacultades() {
            let total = 0;
            $('#tablaDistribucionFacultades tbody tr').each(function() {
                const v = parseFloat($(this).find('.distribucion-valor').val());
                if (!isNaN(v)) total += v;
            });
            $('#distribucionTotalBadge').text(total.toFixed(2));

            const meta = parseFloat($('#formulacion_anio_base_meta').val());
            const badge = $('#distribucionTotalBadge');
            if (!isNaN(meta) && meta > 0 && total > meta) {
                badge.css('color', '#E74C3C');
            } else {
                badge.css('color', '');
            }
            return total;
        }

        let distribucionFacultadTimeout = null;
        function autoGuardarDistribucionFacultad(fila) {
            const formulacionId = $('#formulacion_id').val();
            if (!formulacionId) return;
            const facultadId = $(fila).data('facultad-id');
            const observacion = $(fila).find('.distribucion-observacion').val();
            const distribucion = parseFloat($(fila).find('.distribucion-valor').val()) || 0;

            const totalPrevio = actualizarTotalDistribucionFacultades();
            const meta = parseFloat($('#formulacion_anio_base_meta').val());
            if (!isNaN(meta) && meta > 0 && totalPrevio > meta) {
                Swal.fire({
                    title: 'Se ha pasado del valor',
                    text: 'La distribución total (' + totalPrevio.toFixed(2) + ') supera el total de la meta anual (' + meta.toFixed(2) + ').',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
            }

            clearTimeout(distribucionFacultadTimeout);
            distribucionFacultadTimeout = setTimeout(function() {
                $.ajax({
                    url: basePath + '/modulo144/guardarDistribucionFacultad',
                    type: 'POST',
                    data: {
                        formulacion_id: formulacionId,
                        facultad_id: facultadId,
                        observacion: observacion,
                        distribucion: distribucion
                    },
                    dataType: 'json'
                });
            }, 500);
        }

        $(document).on('input', '#tablaDistribucionFacultades .distribucion-observacion, #tablaDistribucionFacultades .distribucion-valor', function() {
            autoGuardarDistribucionFacultad($(this).closest('tr'));
        });

        // ═══ BUSCADOR DE PROYECTOS (código L-M-P o nombre) ═══
        // Al elegir un proyecto se completan línea, objetivo, motor y proyecto del formulario.
        const CATALOGO_PROYECTOS = <?php echo json_encode(array_values($catalogo_proyectos ?? []), JSON_UNESCAPED_UNICODE); ?>;
        const MAX_RESULTADOS_BUSQUEDA = 60;
        let catalogoSeleccionToken = 0;

        function normBusqueda(t) {
            return String(t == null ? '' : t).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        // Índice de búsqueda: código en varias formas + nombres de proyecto, motor y línea
        CATALOGO_PROYECTOS.forEach(function(p) {
            const l = p.linea_codigo || '', m = p.motor_codigo || '', c = p.codigo || '';
            p._etiqueta = [l, m, c].filter(Boolean).join(' - ');
            p._compacto = normBusqueda(l + m + c);
            p._texto = normBusqueda([l + '-' + m + '-' + c, l + ' ' + m + ' ' + c, p._etiqueta, c, p.nombre, p.linea_nombre, p.motor_nombre].join(' '));
        });

        function buscarProyectosCatalogo(consulta) {
            const q = normBusqueda(consulta).trim();
            if (!q) return CATALOGO_PROYECTOS.slice();
            const tokens = q.split(/\s+/).filter(Boolean);
            const compacto = q.replace(/[\s\-–·.]+/g, '');
            const coinciden = CATALOGO_PROYECTOS.filter(function(p) {
                return tokens.every(function(t) { return p._texto.indexOf(t) !== -1; });
            });
            // El código exacto va primero, luego los que empiezan por lo escrito
            const puntaje = function(p) { return p._compacto === compacto ? 0 : (compacto.length >= 2 && p._compacto.indexOf(compacto) === 0 ? 1 : 2); };
            return coinciden.sort(function(a, b) { return puntaje(a) - puntaje(b); });
        }

        function etiquetaProyectoBuscador(p) { return p._etiqueta + '  ·  ' + p.nombre; }

        (function iniciarBuscadorProyecto() {
            const input = document.getElementById('buscarProyecto');
            const lista = document.getElementById('buscarProyectoLista');
            const limpiar = document.getElementById('buscarProyectoLimpiar');
            if (!input || !lista) return;
            let resultados = [], activo = -1;

            function abrir(mostrar) {
                lista.hidden = !mostrar;
                input.setAttribute('aria-expanded', mostrar ? 'true' : 'false');
            }
            function marcarActivo(i) {
                const items = lista.querySelectorAll('.m144-opcion');
                items.forEach(function(el, k) { el.classList.toggle('activa', k === i); });
                activo = i;
                if (items[i]) items[i].scrollIntoView({ block: 'nearest' });
            }
            function pintar() {
                resultados = buscarProyectosCatalogo(input.value);
                limpiar.hidden = !input.value;
                if (!resultados.length) {
                    lista.innerHTML = '<div class="m144-buscar-vacio">Ningún proyecto coincide con "' + evalHtmlEscape(input.value) + '"</div>';
                    activo = -1;
                    abrir(true);
                    return;
                }
                const visibles = resultados.slice(0, MAX_RESULTADOS_BUSQUEDA);
                lista.innerHTML = visibles.map(function(p, i) {
                    return '<div class="m144-opcion" role="option" data-i="' + i + '">'
                        + '<span class="m144-opcion-codigo">' + evalHtmlEscape(p._etiqueta) + '</span>'
                        + '<span><div class="m144-opcion-nombre">' + evalHtmlEscape(p.nombre) + '</div>'
                        + '<div class="m144-opcion-sub">' + evalHtmlEscape((p.linea_nombre || '') + '  ›  ' + (p.motor_nombre || '')) + '</div></span></div>';
                }).join('') + (resultados.length > visibles.length
                    ? '<div class="m144-buscar-nota">Mostrando ' + visibles.length + ' de ' + resultados.length + ' proyectos: escribe más para acotar</div>' : '');
                activo = -1;
                abrir(true);
            }
            function elegir(i) {
                const p = resultados[i];
                if (!p) return;
                input.value = etiquetaProyectoBuscador(p);
                limpiar.hidden = false;
                abrir(false);
                aplicarProyectoCatalogo(p);
            }

            input.addEventListener('focus', pintar);
            input.addEventListener('input', pintar);
            input.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') { e.preventDefault(); if (lista.hidden) pintar(); marcarActivo(Math.min(activo + 1, Math.min(resultados.length, MAX_RESULTADOS_BUSQUEDA) - 1)); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); marcarActivo(Math.max(activo - 1, 0)); }
                else if (e.key === 'Enter') { if (!lista.hidden) { e.preventDefault(); elegir(activo >= 0 ? activo : 0); } }
                else if (e.key === 'Escape') { if (!lista.hidden) { e.stopPropagation(); abrir(false); } }
            });
            // mousedown (no click) para que el input no pierda el foco antes de elegir
            lista.addEventListener('mousedown', function(e) {
                const op = e.target.closest('.m144-opcion');
                if (!op) return;
                e.preventDefault();
                elegir(parseInt(op.getAttribute('data-i'), 10));
            });
            limpiar.addEventListener('click', function() { input.value = ''; limpiar.hidden = true; input.focus(); pintar(); });
            document.addEventListener('mousedown', function(e) {
                if (!document.getElementById('buscarProyectoWrap').contains(e.target)) abrir(false);
            });
        })();

        // Deja el buscador vacío (al cerrar el modal) o con el proyecto del borrador (al editar)
        function limpiarBuscadorProyecto() {
            const input = document.getElementById('buscarProyecto');
            if (input) input.value = '';
            const lim = document.getElementById('buscarProyectoLimpiar');
            if (lim) lim.hidden = true;
            const lista = document.getElementById('buscarProyectoLista');
            if (lista) lista.hidden = true;
        }
        function sincronizarBuscadorProyecto(b) {
            limpiarBuscadorProyecto();
            if (!b || !b.proyecto) return;
            const p = CATALOGO_PROYECTOS.find(function(x) {
                return x.nombre === b.proyecto && x.linea_nombre === b.linea_estrategica && x.motor_nombre === b.motor_desarrollo;
            });
            if (p) {
                document.getElementById('buscarProyecto').value = etiquetaProyectoBuscador(p);
                document.getElementById('buscarProyectoLimpiar').hidden = false;
            }
        }

        function llenarSelectMotores(motores) {
            const sel = document.getElementById('formulacion_motor');
            sel.innerHTML = '<option value="">Seleccione un motor de desarrollo</option>';
            (motores || []).forEach(function(motor) {
                const option = document.createElement('option');
                option.value = motor.nombre;
                option.setAttribute('data-motor-id', motor.id);
                option.textContent = (motor.codigo ? motor.codigo + ' - ' : '') + motor.nombre;
                sel.appendChild(option);
            });
        }
        function llenarSelectProyectos(proyectos) {
            const sel = document.getElementById('formulacion_proyecto');
            sel.innerHTML = '<option value="">Seleccione un proyecto</option>';
            (proyectos || []).forEach(function(proyecto) {
                const option = document.createElement('option');
                option.value = proyecto.nombre;
                option.setAttribute('data-proyecto-id', proyecto.id);
                option.textContent = proyecto.codigo + ' - ' + proyecto.nombre;
                sel.appendChild(option);
            });
        }

        // Completa línea → objetivo → motor → proyecto a partir del proyecto elegido en el buscador
        function aplicarProyectoCatalogo(p) {
            formulacionEditadaPorUsuario = true; // es una acción del usuario: habilita el autoguardado
            const token = ++catalogoSeleccionToken;
            const sesion = formulacionSesion;
            const vigente = function() { return token === catalogoSeleccionToken && sesion === formulacionSesion; };

            const selLinea = document.getElementById('formulacion_linea');
            const selMotor = document.getElementById('formulacion_motor');
            const selProyecto = document.getElementById('formulacion_proyecto');

            const lineaCambia = selLinea.value !== p.linea_nombre;
            selLinea.value = p.linea_nombre;
            if (selLinea.value !== p.linea_nombre) {
                Swal.fire('Línea no disponible', 'La línea de este proyecto no está habilitada en el formulario.', 'warning');
                return;
            }
            if (lineaCambia) {
                cargarObjetivoYestrategias(); // objetivo de la línea y sus estrategias (la estrategia la elige el usuario)
            } else {
                document.getElementById('formulacion_objetivo').value = selLinea.options[selLinea.selectedIndex].getAttribute('data-objetivo') || '';
            }

            selMotor.innerHTML = '<option value="">Cargando motores...</option>';
            selProyecto.innerHTML = '<option value="">Seleccione un motor primero</option>';
            $.getJSON(basePath + '/modulo144/getMotoresPorLinea', { linea_id: p.linea_id }).done(function(resMotores) {
                if (!vigente()) return;
                llenarSelectMotores(resMotores.success ? resMotores.motores : []);
                selMotor.value = p.motor_nombre;
                selProyecto.innerHTML = '<option value="">Cargando proyectos...</option>';
                $.getJSON(basePath + '/modulo144/getProyectosPorLineaYMotor', { linea_id: p.linea_id, motor_id: p.motor_id }).done(function(resProyectos) {
                    if (!vigente()) return;
                    llenarSelectProyectos(resProyectos.success ? resProyectos.proyectos : []);
                    selProyecto.value = p.nombre;
                    // El "change" del proyecto calcula la ponderación, el acumulado y guarda el borrador
                    selProyecto.dispatchEvent(new Event('change', { bubbles: true }));
                    validarPestanas();
                }).fail(function() { if (vigente()) selProyecto.innerHTML = '<option value="">Error al cargar proyectos</option>'; });
            }).fail(function() { if (vigente()) selMotor.innerHTML = '<option value="">Error al cargar motores</option>'; });
        }

        // ═══ IMPORTAR PLANTILLA ═══
        function abrirModalImportarPlantilla() {
            $('#archivoPlantilla').val('');
            $('#modalImportarPlantilla').modal('show');
        }

        function importarPlantilla() {
            const archivo = document.getElementById('archivoPlantilla').files[0];
            if (!archivo) {
                Swal.fire('Falta el archivo', 'Selecciona la plantilla de Excel (.xlsx) que llenaste.', 'warning');
                return;
            }
            const datos = new FormData();
            datos.append('formulario_id', formularioId);
            datos.append('archivo', archivo);

            const boton = $('#btnImportarPlantilla');
            boton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Importando…');

            $.ajax({
                url: basePath + '/modulo144/importarPlantilla',
                type: 'POST',
                data: datos,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    boton.prop('disabled', false).html('<i class="fas fa-upload me-1"></i>Importar');
                    if (!res.success) {
                        Swal.fire('No se pudo importar', res.message || 'Revisa el archivo', 'error');
                        return;
                    }
                    $('#modalImportarPlantilla').modal('hide');
                    const r = res.resumen;
                    const cambios = r.formulacion.creados + r.formulacion.actualizados + r.seguimiento.actualizados;
                    let html = '<div class="imp-resumen">'
                        + '<div><b>' + r.formulacion.creados + '</b><small>Indicadores nuevos</small></div>'
                        + '<div><b>' + r.formulacion.actualizados + '</b><small>Formulaciones actualizadas</small></div>'
                        + '<div><b>' + r.seguimiento.actualizados + '</b><small>Seguimientos actualizados</small></div>'
                        + '</div>';
                    if (r.errores.length) {
                        html += '<p class="text-start small mb-1"><strong>' + r.errores.length + ' fila(s) no se importaron:</strong></p><ul class="imp-errores">'
                             + r.errores.map(function(e) { return '<li>' + evalHtmlEscape(e) + '</li>'; }).join('') + '</ul>';
                    }
                    const idsCreados = r.ids_creados || [];
                    const ofrecerPublicar = ES_ADMIN_PLAN && idsCreados.length > 0;
                    Swal.fire({
                        icon: r.errores.length ? (cambios ? 'warning' : 'error') : 'success',
                        title: res.message,
                        html: html,
                        width: 640,
                        confirmButtonText: 'Aceptar',
                        showDenyButton: ofrecerPublicar,
                        denyButtonText: 'Publicar los ' + idsCreados.length + ' importados',
                        denyButtonColor: '#34C759'
                    }).then(function(result) {
                        if (result.isDenied) {
                            enviarPublicacion(idsCreados).always(function() { guardarEstadoAcordeon(); }).done(mostrarResultadoPublicacion)
                                .fail(function() { Swal.fire('Error', 'Error de conexión con el servidor', 'error').then(function() { location.reload(); }); });
                            return;
                        }
                        if (cambios) { guardarEstadoAcordeon(); location.reload(); }
                    });
                },
                error: function() {
                    boton.prop('disabled', false).html('<i class="fas fa-upload me-1"></i>Importar');
                    Swal.fire('Error', 'Error de conexión con el servidor', 'error');
                }
            });
        }

        // ═══ SEGUIMIENTO POR FACULTADES ═══
        const VIEWER_FACULTADES = <?php echo json_encode(array_map('intval', $viewer_facultades ?? [])); ?>;

        function segFacNum(v) {
            const n = parseFloat(String(v === null || v === undefined ? '' : v).replace(',', '.'));
            return isNaN(n) ? null : n;
        }

        function actualizarTotalesSeguimientoFacultades() {
            let dist = 0, s1 = 0, s2 = 0;
            $('#tablaSeguimientoFacultades tbody tr:visible').each(function() {
                dist += segFacNum($(this).find('.seg-fac-distribucion').text()) || 0;
                s1 += segFacNum($(this).find('.seg-fac-s1').val()) || 0;
                s2 += segFacNum($(this).find('.seg-fac-s2').val()) || 0;
            });
            $('#segFacTotalDist').text(dist.toFixed(2));
            $('#segFacTotalS1').text(s1.toFixed(2));
            $('#segFacTotalS2').text(s2.toFixed(2));
        }

        // Muestra solo las facultades con valor en la distribución del indicador
        // gestionado = indicador gestionado desde facultades: todas las facultades pueden reportar, aunque no tengan meta
        function cargarSeguimientoFacultades(formulacionId, gestionado) {
            const filas = $('#tablaSeguimientoFacultades tbody tr');
            filas.hide().removeClass('table-primary');
            filas.find('input').val('');
            filas.find('.seg-fac-distribucion').text('0');
            $('#seguimientoFacultadesWrap').hide();
            if (!formulacionId) return;

            $.ajax({
                url: basePath + '/modulo144/getDistribucionFacultades',
                type: 'GET',
                data: { formulacion_id: formulacionId },
                dataType: 'json',
                success: function(response) {
                    if (!response.success) return;
                    let hay = false;
                    (response.distribucion || []).forEach(function(d) {
                        const dist = segFacNum(d.distribucion) || 0;
                        const reporto = [d.seguimiento_sem1, d.seguimiento_sem2, d.seguimiento_descripcion, d.seguimiento_descripcion2]
                            .some(function(v) { return v !== null && v !== undefined && String(v).trim() !== ''; });
                        if (dist <= 0 && !reporto) return; // sin meta ni reporte: se decide abajo si se muestra
                        const fila = $('#tablaSeguimientoFacultades tbody tr[data-facultad-id="' + d.facultad_id + '"]');
                        if (!fila.length) return;
                        fila.find('.seg-fac-distribucion').text(dist > 0 ? dist.toFixed(2) : 'Sin meta');
                        fila.find('.seg-fac-s1').val(d.seguimiento_sem1 !== null && d.seguimiento_sem1 !== undefined ? d.seguimiento_sem1 : '');
                        fila.find('.seg-fac-s2').val(d.seguimiento_sem2 !== null && d.seguimiento_sem2 !== undefined ? d.seguimiento_sem2 : '');
                        fila.find('.seg-fac-desc1').val(d.seguimiento_descripcion || '');
                        fila.find('.seg-fac-desc2').val(d.seguimiento_descripcion2 || '');
                        if (VIEWER_FACULTADES.includes(parseInt(d.facultad_id, 10))) fila.addClass('table-primary');
                        fila.show();
                        hay = true;
                    });
                    // Indicador gestionado desde facultades: las facultades sin meta también aparecen y pueden reportar lo que hacen
                    if (gestionado) {
                        const soloIds = (response.solo_facultades || []).map(Number);
                        $('#tablaSeguimientoFacultades tbody tr').each(function() {
                            const fila = $(this);
                            const fid = parseInt(fila.data('facultad-id'), 10);
                            if (fila.is(':visible')) return;
                            if (soloIds.length && soloIds.indexOf(fid) === -1) return; // la facultad solo ve la suya
                            fila.find('.seg-fac-distribucion').text('Sin meta');
                            if (VIEWER_FACULTADES.includes(fid)) fila.addClass('table-primary');
                            fila.show();
                            hay = true;
                        });
                    }
                    $('#seguimientoFacultadesWrap').toggle(hay);
                    // Usuario de facultad: solo ve su propia fila (el servidor no envía las demás)
                    const soloPropia = (response.solo_facultades || []).length > 0;
                    $('#segFacAyuda').text(soloPropia
                        ? 'Registra el avance de tu facultad sobre su parte de la distribución en cada semestre.'
                        : 'Cada facultad registra el avance de su parte de la distribución en cada semestre.');
                    $('#tablaSeguimientoFacultades tfoot').toggle(!soloPropia);
                    actualizarTotalesSeguimientoFacultades();
                    aplicarBloqueoSemestres();
                }
            });
        }

        let seguimientoFacultadTimeout = {};
        $(document).on('input', '#tablaSeguimientoFacultades .seg-fac-s1, #tablaSeguimientoFacultades .seg-fac-s2, #tablaSeguimientoFacultades .seg-fac-desc1, #tablaSeguimientoFacultades .seg-fac-desc2', function() {
            const fila = $(this).closest('tr');
            const facultadId = fila.data('facultad-id');
            const formulacionId = $('#seguimiento_id').val();
            actualizarTotalesSeguimientoFacultades();
            if (!formulacionId) return;

            clearTimeout(seguimientoFacultadTimeout[facultadId]);
            seguimientoFacultadTimeout[facultadId] = setTimeout(function() {
                $.ajax({
                    url: basePath + '/modulo144/guardarSeguimientoFacultad',
                    type: 'POST',
                    data: {
                        formulacion_id: formulacionId,
                        facultad_id: facultadId,
                        seguimiento_sem1: fila.find('.seg-fac-s1').val(),
                        seguimiento_sem2: fila.find('.seg-fac-s2').val(),
                        seguimiento_descripcion: fila.find('.seg-fac-desc1').val(),
                        seguimiento_descripcion2: fila.find('.seg-fac-desc2').val()
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (!res.success) Swal.fire('No se guardó', res.message || 'Intenta de nuevo', 'error');
                    },
                    error: function() { Swal.fire('Error', 'Error de conexión con el servidor', 'error'); }
                });
            }, 600);
        });

        // ── Evidencias del seguimiento: hasta 5 archivos por semestre, por indicador y por facultad ──
        let evFacultadActual = 0;
        let evSemestreActual = 1;

        function evAplicarResumen(r) {
            const g = r.general || {};
            $('#segEvGenN1').text(g[1] || 0);
            $('#segEvGenN2').text(g[2] || 0);
            $('#tablaSeguimientoFacultades tbody tr').each(function() {
                const f = (r.facultades || {})[$(this).data('facultad-id')] || {};
                $(this).find('.ev-n[data-sem="1"]').text(f[1] || 0);
                $(this).find('.ev-n[data-sem="2"]').text(f[2] || 0);
            });
        }

        function cargarResumenEvidencias(formulacionId) {
            evAplicarResumen({ general: {}, facultades: {} });
            if (!formulacionId) return;
            $.getJSON(basePath + '/modulo144/resumenEvidencias', { formulacion_id: formulacionId }, function(r) {
                if (r.success) evAplicarResumen(r);
            });
        }

        function evFormatoTamano(n) {
            return n > 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB';
        }

        function evMostrarError(msg) {
            $('#evError').text(msg || '').toggle(!!msg);
        }

        function evPintar(r) {
            const archivos = r.archivos || [];
            const lista = $('#evLista').empty();
            archivos.forEach(function(a) {
                const url = basePath + '/modulo144/verEvidencia?id=' + a.id;
                const ext = (a.nombre.split('.').pop() || '').slice(0, 4);
                const quitar = r.puede_editar
                    ? '<button type="button" class="ev-quitar" data-id="' + a.id + '" aria-label="Quitar archivo" title="Quitar">&times;</button>' : '';
                lista.append(
                    '<li><a class="ev-ext" href="' + url + '" target="_blank" rel="noopener">' + evalHtmlEscape(ext) + '</a>' +
                    '<span class="ev-nombre"><a href="' + url + '" target="_blank" rel="noopener" title="Abrir ' + evalHtmlEscape(a.nombre) + ' en una pestaña nueva">' + evalHtmlEscape(a.nombre) + '</a>' +
                    '<small>' + evFormatoTamano(a.tamano) + ' · Abrir en pestaña nueva</small></span>' + quitar + '</li>'
                );
            });
            $('#evVacio').toggle(archivos.length === 0);
            $('#evContador').text(archivos.length + ' de ' + (r.maximo || 5));
            $('#evSubir, #evReglas').toggle(!!r.puede_editar);
            $('#evBtnElegir').prop('disabled', archivos.length >= (r.maximo || 5));
            $('#evRuta').text(r.carpeta || '');
            $('#modalEvidencias').data('estado', r);
            if (!evFacultadActual) $('#segEvGenN' + evSemestreActual).text(archivos.length);
            else $('#tablaSeguimientoFacultades tbody tr[data-facultad-id="' + evFacultadActual + '"] .ev-n[data-sem="' + evSemestreActual + '"]').text(archivos.length);
        }

        function evRecargar() {
            $.getJSON(basePath + '/modulo144/listarEvidencias', { formulacion_id: $('#seguimiento_id').val(), facultad_id: evFacultadActual, semestre: evSemestreActual }, function(r) {
                if (!r.success) { evMostrarError(r.message || 'No se pudieron cargar las evidencias'); return; }
                evPintar(r);
            }).fail(function() { evMostrarError('Error de conexión con el servidor'); });
        }

        // facultadId 0 = evidencias del indicador; semestre 1 o 2
        function abrirEvidencias(facultadId, semestre) {
            if (!$('#seguimiento_id').val()) return;
            evFacultadActual = parseInt(facultadId, 10) || 0;
            evSemestreActual = parseInt(semestre, 10) === 2 ? 2 : 1;
            const fila = $('#tablaSeguimientoFacultades tbody tr[data-facultad-id="' + evFacultadActual + '"]');
            $('#evTitulo').text('Evidencias · Semestre ' + evSemestreActual + (evFacultadActual ? ' · ' + fila.find('td:first').text().trim() : ''));
            $('#evQuien').text($('#tituloSeguimientoSpan').text());
            evMostrarError('');
            $('#evLista').empty();
            $('#evVacio').show();
            $('#modalEvidencias').modal('show');
            evRecargar();
        }

        $(document).on('click', '#evBtnElegir', function() { $('#evInput').trigger('click'); });

        $(document).on('change', '#evInput', function() {
            const archivos = Array.from(this.files || []);
            this.value = '';
            if (!archivos.length) return;
            evMostrarError('');
            const errores = [];
            let i = 0;
            (function siguiente() {
                if (i >= archivos.length) { if (errores.length) evMostrarError(errores.join(' · ')); evRecargar(); return; }
                const f = archivos[i++];
                const datos = new FormData();
                datos.append('formulacion_id', $('#seguimiento_id').val());
                datos.append('facultad_id', evFacultadActual);
                datos.append('semestre', evSemestreActual);
                datos.append('archivo', f);
                $.ajax({ url: basePath + '/modulo144/subirEvidencia', type: 'POST', data: datos, processData: false, contentType: false, dataType: 'json' })
                    .done(function(r) { if (!r.success) errores.push(f.name + ': ' + (r.message || 'no se pudo cargar')); })
                    .fail(function() { errores.push(f.name + ': error de conexión'); })
                    .always(siguiente);
            })();
        });

        $(document).on('click', '#evLista .ev-quitar', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Quitar este archivo?', text: 'Se eliminará de las evidencias.', icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#E74C3C', confirmButtonText: 'Sí, quitar', cancelButtonText: 'Cancelar'
            }).then(function(res) {
                if (!res.isConfirmed) return;
                $.post(basePath + '/modulo144/eliminarEvidencia', { id: id }, function(r) {
                    if (!r.success) evMostrarError(r.message || 'No se pudo quitar el archivo');
                    evRecargar();
                }, 'json');
            });
        });

        // La ventana de evidencias se abre sobre el seguimiento: sin esto queda detrás de su fondo y se pierde el scroll
        $('#modalEvidencias').on('shown.bs.modal', function() { $('.modal-backdrop').last().css('z-index', 1060); $(this).css('z-index', 1065); });
        $('#modalEvidencias').on('hidden.bs.modal', function() { if ($('.modal.show').length) $('body').addClass('modal-open'); });

        let nombreBorradorProvisional = 'Nuevo Borrador';

        function sincronizarNombreBorrador() {
            const nombreIndicador = $('#formulacion_nombre_indicador').val();
            const nombre = (nombreIndicador && nombreIndicador.trim() !== '') ? nombreIndicador.trim() : nombreBorradorProvisional;
            $('#tituloFormulacionSpan').text(nombre);
        }

        function evalHtmlEscape(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, function(c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function gestionVigenciaOptions(valorActual) {
            let html = '<option value="">Seleccione vigencia</option>';
            const anioActual = new Date().getFullYear();
            for (let i = anioActual; i <= anioActual + 5; i++) {
                html += '<option value="' + i + '"' + (String(valorActual) === String(i) ? ' selected' : '') + '>' + i + '</option>';
            }
            return html;
        }

        function gestionFacultadFormHtml(formulacionId, facultadId) {
            const key = formulacionId + '_' + facultadId;
            return ''
                + '<div class="row mb-3">'
                + '  <div class="col-12"><label class="form-label fw-bold text-primary">FORMULACIÓN</label></div>'
                + '  <div class="col-md-4 mb-2"><label class="form-label small">SEM. 1</label><input type="text" class="form-control form-control-sm" id="gsem_' + key + '_sem1" oninput="autoGuardarGestionFacultad(' + formulacionId + ',' + facultadId + ')"></div>'
                + '  <div class="col-md-4 mb-2"><label class="form-label small">SEM. 2</label><input type="text" class="form-control form-control-sm" id="gsem_' + key + '_sem2" oninput="autoGuardarGestionFacultad(' + formulacionId + ',' + facultadId + ')"></div>'
                + '  <div class="col-md-4 mb-2"><label class="form-label small">VIGENCIA</label><select class="form-select form-select-sm" id="gsem_' + key + '_vigencia" onchange="autoGuardarGestionFacultad(' + formulacionId + ',' + facultadId + ')">' + gestionVigenciaOptions('') + '</select></div>'
                + '</div>'
                + '<div class="card border-warning">'
                + '  <div class="card-header bg-warning text-white py-2"><i class="fas fa-chart-line me-2"></i>SEGUIMIENTO</div>'
                + '  <div class="card-body">'
                + '    <div class="row">'
                + '      <div class="col-md-3 mb-2"><label class="form-label small">SEM. 1</label><input type="text" class="form-control form-control-sm" id="gsem_' + key + '_segsem1" oninput="autoGuardarGestionFacultad(' + formulacionId + ',' + facultadId + ')"></div>'
                + '      <div class="col-md-3 mb-2"><label class="form-label small">SEM. 2</label><input type="text" class="form-control form-control-sm" id="gsem_' + key + '_segsem2" oninput="autoGuardarGestionFacultad(' + formulacionId + ',' + facultadId + ')"></div>'
                + '      <div class="col-md-6 mb-2"><label class="form-label small">DESCRIPCIÓN DE LA GESTIÓN</label><textarea class="form-control form-control-sm" rows="1" id="gsem_' + key + '_desc" oninput="autoGuardarGestionFacultad(' + formulacionId + ',' + facultadId + ')"></textarea></div>'
                + '    </div>'
                + '  </div>'
                + '</div>';
        }

        let _gestionFacultadTimeout = {};
        function autoGuardarGestionFacultad(formulacionId, facultadId) {
            const key = formulacionId + '_' + facultadId;
            clearTimeout(_gestionFacultadTimeout[key]);
            _gestionFacultadTimeout[key] = setTimeout(function() {
                $.ajax({
                    url: basePath + '/modulo144/guardarGestionFacultad144',
                    type: 'POST',
                    data: {
                        formulacion_id: formulacionId,
                        facultad_id: facultadId,
                        sem1: $('#gsem_' + key + '_sem1').val(),
                        sem2: $('#gsem_' + key + '_sem2').val(),
                        vigencia: $('#gsem_' + key + '_vigencia').val(),
                        seguimiento_sem1: $('#gsem_' + key + '_segsem1').val(),
                        seguimiento_sem2: $('#gsem_' + key + '_segsem2').val(),
                        descripcion_gestion: $('#gsem_' + key + '_desc').val()
                    },
                    dataType: 'json',
                    success: function(res) { if (res.success) mostrarAutoSaveIndicator(); }
                });
            }, 600);
        }

        function irATabFacultad(facultadId) {
            const btn = document.querySelector('#modalEditarFacultadItem [data-bs-target="#facTab' + facultadId + '"]');
            if (btn) new bootstrap.Tab(btn).show();
        }

        function abrirEditarFacultadItem(id) {
            const item = FACULTAD_ITEMS.find(function(it) { return it.id === id; });
            if (!item) return;
            const formulacionId = item.id;

            let navHtml = '<ul class="nav nav-tabs mb-3" role="tablist">';
            navHtml += '<li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#facTabResumen" type="button" role="tab">Resumen</button></li>';
            TODAS_FACULTADES.forEach(function(fac) {
                navHtml += '<li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#facTab' + fac.id + '" type="button" role="tab">' + evalHtmlEscape(fac.codigo || fac.nombre) + '</button></li>';
            });
            navHtml += '</ul>';

            let resumenListaHtml = '<div class="list-group">';
            TODAS_FACULTADES.forEach(function(fac) {
                resumenListaHtml += '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="irATabFacultad(' + fac.id + ')">'
                    + '<span><span class="badge bg-secondary me-2">' + evalHtmlEscape(fac.codigo || '') + '</span>' + evalHtmlEscape(fac.nombre) + '</span>'
                    + '<i class="fas fa-chevron-right text-muted"></i>'
                    + '</button>';
            });
            resumenListaHtml += '</div>';

            let contentHtml = '<div class="tab-content">';
            contentHtml += '<div class="tab-pane fade show active" id="facTabResumen" role="tabpanel">'
                + '<p class="text-muted mb-0">' + evalHtmlEscape(item.nombre_borrador) + '</p>'
                + '<p class="text-muted small mb-3">Cada facultad gestiona su propio seguimiento de este indicador. Hacé clic en una para ir a su pestaña.</p>'
                + resumenListaHtml
                + '</div>';
            TODAS_FACULTADES.forEach(function(fac) {
                contentHtml += '<div class="tab-pane fade" id="facTab' + fac.id + '" role="tabpanel">'
                    + '<h6 class="text-muted mb-3">' + evalHtmlEscape(fac.nombre) + '</h6>'
                    + gestionFacultadFormHtml(formulacionId, fac.id)
                    + '</div>';
            });
            contentHtml += '</div>';

            $('#facultadItem_id').val(id);
            $('#modalEditarFacultadItem .modal-body').html(navHtml + contentHtml);
            $('#modalEditarFacultadItem').modal('show');

            TODAS_FACULTADES.forEach(function(fac) {
                const key = formulacionId + '_' + fac.id;
                $.ajax({
                    url: basePath + '/modulo144/getGestionFacultad144?formulacion_id=' + formulacionId + '&facultad_id=' + fac.id,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (!response.success || !response.gestion) return;
                        const g = response.gestion;
                        $('#gsem_' + key + '_sem1').val(g.sem1 || '');
                        $('#gsem_' + key + '_sem2').val(g.sem2 || '');
                        $('#gsem_' + key + '_vigencia').html(gestionVigenciaOptions(g.vigencia || ''));
                        $('#gsem_' + key + '_segsem1').val(g.seguimiento_sem1 || '');
                        $('#gsem_' + key + '_segsem2').val(g.seguimiento_sem2 || '');
                        $('#gsem_' + key + '_desc').val(g.descripcion_gestion || '');
                    }
                });
            });
        }

        function abrirModalNuevoBorrador(modulo) {
            $('#nuevo_modulo').val(modulo);
            $('#nuevo_nombre').val('');
            $('#modalNuevoBorrador').modal('show');
        }

        function abrirModalNuevoBorradorFacultad(facultadId, facultadNombre) {
            $('#facultad_id_modal').val(facultadId);
            $('#facultadNombreModal').text(facultadNombre);
            $('#nuevo_nombre_facultad').val('Formulación ' + facultadNombre);
            $('#modalNuevoBorradorFacultad').modal('show');
        }

        $('#formNuevoBorrador').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: basePath + '/modulo144/crearBorrador',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Creado!', response.message, 'success');
                        setTimeout(() => { guardarEstadoAcordeon(); location.reload(); }, 1500);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        });

        $('#formNuevoBorradorFacultad').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: basePath + '/modulo144/crearBorrador',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Creado!', response.message, 'success');
                        setTimeout(() => { guardarEstadoAcordeon(); location.reload(); }, 1500);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        });

        function abrirModalDuplicar(modulo, id, nombre) {
            $('#duplicar_modulo').val(modulo);
            $('#duplicar_id').val(id);
            $('#duplicar_nombre').val('Copia de ' + nombre);
            $('#modalDuplicarBorrador').modal('show');
        }

        $('#formDuplicarBorrador').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: basePath + '/modulo144/duplicar',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Duplicado!', response.message, 'success');
                        setTimeout(() => { guardarEstadoAcordeon(); location.reload(); }, 1500);
                    }
                }
            });
        });

        function abrirGestionSemestral(id, nombre) {
            $('#gestion_id').val(id);
            $('#gestionTituloSpan').text(nombre);
            
            $.ajax({
                url: basePath + '/modulo144/getBorrador?modulo=formulacion&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const b = response.borrador;
                        $('#gestion_sem1').val(b.gestion_sem1 || '');
                        $('#gestion_sem2').val(b.gestion_sem2 || '');
                        $('#gestion_vigencia').val(b.vigencia || '');
                        $('#gestion_descripcion').val(b.descripcion_gestion || '');
                    }
                }
            });
            
            $('#modalGestionSemestral').modal('show');
        }

        $('#formGestionSemestral').on('submit', function(e) {
            e.preventDefault();
            
            const data = {
                id: $('#gestion_id').val(),
                gestion_sem1: $('#gestion_sem1').val(),
                gestion_sem2: $('#gestion_sem2').val(),
                vigencia: $('#gestion_vigencia').val(),
                descripcion_gestion: $('#gestion_descripcion').val()
            };
            
            $.ajax({
                url: basePath + '/modulo144/guardarGestionSemestral',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Guardado!', response.message, 'success');
                        $('#modalGestionSemestral').modal('hide');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
                }
            });
        });

        function validarPestanas() {
            const camposFormulacion = [
                $('#formulacion_linea').val(),
                $('#formulacion_estrategia').val(),
                $('#formulacion_motor').val(),
                $('#formulacion_proyecto').val(),
                $('#formulacion_meta').val(),
                $('#formulacion_ponderacion_proyectos').val(),
                $('#formulacion_actividad').val(),
                $('#formulacion_ponderacion_actividades').val(),
                $('#formulacion_responsable_hidden').val(),
                $('#formulacion_id_indicador').val()
            ];
            
            const formulacionCompleta = camposFormulacion.every(valor => valor && valor.trim() !== '');
            
            const camposIndicador = [
                $('#formulacion_nombre_indicador').val(),
                $('#formulacion_formula_medicion').val(),
                $('#formulacion_frecuencia_medicion').val(),
                $('#formulacion_unidad_medida').val(),
                $('#formulacion_tipo_medicion').val(),
                $('#formulacion_descripcion_indicador').val(),
                $('#formulacion_linea_base_meta').val(),
                $('#formulacion_meta_s1').val(),
                $('#formulacion_meta_s2').val()
            ];
            
            const indicadorCompleto = camposIndicador.every(valor => valor && valor.trim() !== '');
            const planesCompleto = true;
            
            actualizarClasePestana('#tab-formulacion', formulacionCompleta);
            actualizarClasePestana('#tab-indicador', indicadorCompleto);
            actualizarClasePestana('#tab-planes', planesCompleto);
        }
        
        function actualizarClasePestana(selector, completo) {
            const pestana = $(selector);
            if (completo) {
                pestana.removeClass('tab-incomplete').addClass('tab-complete');
            } else {
                pestana.removeClass('tab-complete').addClass('tab-incomplete');
            }
        }

        function cargarObjetivoYestrategias() {
            const selectLinea = document.getElementById('formulacion_linea');
            const selectedOption = selectLinea.options[selectLinea.selectedIndex];
            
            if (!selectedOption || !selectedOption.value) {
                document.getElementById('formulacion_objetivo').value = '';
                document.getElementById('formulacion_estrategia').innerHTML = '<option value="">Seleccione una estrategia</option>';
                validarPestanas();
                return;
            }
            
            const objetivo = selectedOption.getAttribute('data-objetivo') || '';
            document.getElementById('formulacion_objetivo').value = objetivo;
            
            const lineaId = selectedOption.getAttribute('data-id');
            
            if (lineaId) {
                document.getElementById('formulacion_estrategia').innerHTML = '<option value="">Cargando estrategias...</option>';
                
                $.ajax({
                    url: basePath + '/modulo144/getEstrategiasPorLinea',
                    type: 'GET',
                    data: { linea_id: lineaId },
                    dataType: 'json',
                    success: function(response) {
                        const selectEstrategia = document.getElementById('formulacion_estrategia');
                        selectEstrategia.innerHTML = '<option value="">Seleccione una estrategia</option>';
                        
                        if (response.success && response.estrategias && response.estrategias.length > 0) {
                            response.estrategias.forEach(function(estrategia) {
                                const option = document.createElement('option');
                                option.value = estrategia.descripcion;
                                option.textContent = estrategia.descripcion;
                                selectEstrategia.appendChild(option);
                            });
                        } else {
                            selectEstrategia.innerHTML = '<option value="">No hay estrategias disponibles</option>';
                        }
                        
                        autoGuardarFormulacion();
                        validarPestanas();
                    },
                    error: function() {
                        document.getElementById('formulacion_estrategia').innerHTML = '<option value="">Error al cargar estrategias</option>';
                        validarPestanas();
                    }
                });
            }
        }

        function cargarMotoresPorLinea() {
            const selectLinea = document.getElementById('formulacion_linea');
            const selectedOption = selectLinea.options[selectLinea.selectedIndex];
            
            if (!selectedOption || !selectedOption.value) {
                document.getElementById('formulacion_motor').innerHTML = '<option value="">Seleccione un motor de desarrollo</option>';
                document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Primero seleccione un motor</option>';
                validarPestanas();
                return;
            }
            
            const lineaId = selectedOption.getAttribute('data-id');
            
            if (lineaId) {
                document.getElementById('formulacion_motor').innerHTML = '<option value="">Cargando motores...</option>';
                document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Seleccione un motor primero</option>';
                
                $.ajax({
                    url: basePath + '/modulo144/getMotoresPorLinea',
                    type: 'GET',
                    data: { linea_id: lineaId },
                    dataType: 'json',
                    success: function(response) {
                        const selectMotor = document.getElementById('formulacion_motor');
                        selectMotor.innerHTML = '<option value="">Seleccione un motor de desarrollo</option>';
                        
                        if (response.success && response.motores && response.motores.length > 0) {
                            response.motores.forEach(function(motor) {
                                const option = document.createElement('option');
                                option.value = motor.nombre;
                                option.setAttribute('data-motor-id', motor.id);
                                option.textContent = (motor.codigo ? motor.codigo + ' - ' : '') + motor.nombre;
                                selectMotor.appendChild(option);
                            });
                        } else {
                            selectMotor.innerHTML = '<option value="">No hay motores disponibles</option>';
                        }
                        
                        autoGuardarFormulacion();
                        validarPestanas();
                    },
                    error: function() {
                        document.getElementById('formulacion_motor').innerHTML = '<option value="">Error al cargar motores</option>';
                        validarPestanas();
                    }
                });
            }
        }

        function cargarProyectosPorMotor() {
            const selectLinea = document.getElementById('formulacion_linea');
            const selectMotor = document.getElementById('formulacion_motor');
            
            const lineaOption = selectLinea.options[selectLinea.selectedIndex];
            const motorOption = selectMotor.options[selectMotor.selectedIndex];
            
            if (!lineaOption || !lineaOption.value || !motorOption || !motorOption.value) {
                document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Seleccione un motor primero</option>';
                validarPestanas();
                return;
            }
            
            const lineaId = lineaOption.getAttribute('data-id');
            const motorId = motorOption.getAttribute('data-motor-id');
            
            if (lineaId && motorId) {
                document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Cargando proyectos...</option>';
                
                $.ajax({
                    url: basePath + '/modulo144/getProyectosPorLineaYMotor',
                    type: 'GET',
                    data: { linea_id: lineaId, motor_id: motorId },
                    dataType: 'json',
                    success: function(response) {
                        const selectProyecto = document.getElementById('formulacion_proyecto');
                        selectProyecto.innerHTML = '<option value="">Seleccione un proyecto</option>';
                        
                        if (response.success && response.proyectos && response.proyectos.length > 0) {
                            response.proyectos.forEach(function(proyecto) {
                                const option = document.createElement('option');
                                option.value = proyecto.nombre;
                                option.setAttribute('data-proyecto-id', proyecto.id);
                                option.textContent = proyecto.codigo + ' - ' + proyecto.nombre;
                                selectProyecto.appendChild(option);
                            });
                        } else {
                            selectProyecto.innerHTML = '<option value="">No hay proyectos disponibles</option>';
                        }
                        
                        autoGuardarFormulacion();
                        validarPestanas();
                    },
                    error: function() {
                        document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Error al cargar proyectos</option>';
                        validarPestanas();
                    }
                });
            }
        }

        function agregarPlan() {
            const select = document.getElementById('selectPlanInstitucional');
            const planSeleccionado = select.value;
            
            if (!planSeleccionado) {
                Swal.fire('Atención', 'Por favor seleccione un plan', 'warning');
                return;
            }
            
            if (planesSeleccionados.includes(planSeleccionado)) {
                Swal.fire('Atención', 'Este plan ya ha sido agregado', 'info');
                return;
            }
            
            planesSeleccionados.push(planSeleccionado);
            actualizarContenedorPlanes();
            select.value = '';
            actualizarCampoOculto();
            guardarPlanesInmediatamente();
        }

        function eliminarPlan(plan) {
            planesSeleccionados = planesSeleccionados.filter(p => p !== plan);
            actualizarContenedorPlanes();
            actualizarCampoOculto();
            guardarPlanesInmediatamente();
        }

        function guardarPlanesInmediatamente() {
            const id = $('#formulacion_id').val();
            if (!id) return;
            
            const data = {
                modulo: 'formulacion',
                id: id,
                planes_institucionales: $('#formulacion_planes_institucionales').val()
            };
            
            $.ajax({
                url: basePath + '/modulo144/guardar',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarAutoSaveIndicator();
                    }
                }
            });
        }

        function actualizarContenedorPlanes() {
            const contenedor = document.getElementById('contenedorPlanes');
            
            if (planesSeleccionados.length === 0) {
                contenedor.innerHTML = '<p class="text-muted text-center mb-0">No hay planes seleccionados</p>';
                return;
            }
            
            let html = '';
            planesSeleccionados.forEach((plan, index) => {
                let colorClass = index % 3 === 0 ? 'bg-primary' : (index % 3 === 1 ? 'bg-success' : 'bg-info');
                const planEscaped = plan.replace(/'/g, "\\'");
                html += `<div class="d-flex justify-content-between align-items-center mb-2 p-2 ${colorClass} text-white rounded" style="opacity: 0.9;">
                            <span><strong>${index + 1}:</strong> ${plan}</span>
                            <button type="button" class="btn btn-sm btn-light" onclick="eliminarPlan('${planEscaped}')">
                                <i class="fas fa-times text-danger"></i>
                            </button>
                        </div>`;
            });
            contenedor.innerHTML = html;
        }

        function actualizarCampoOculto() {
            document.getElementById('formulacion_planes_institucionales').value = JSON.stringify(planesSeleccionados);
        }

        function cargarPlanesDesdeBD(planesJSON) {
            if (planesJSON && planesJSON !== '[]' && planesJSON !== '') {
                try {
                    planesSeleccionados = JSON.parse(planesJSON);
                } catch (e) {
                    planesSeleccionados = [];
                }
            } else {
                planesSeleccionados = [];
            }
            actualizarContenedorPlanes();
            actualizarCampoOculto();
        }

        function editarTituloModal(modulo) {
            if (editandoTitulo) return;
            
            const modalHeader = modulo === 'formulacion' ? $('#modalFormulacion .modal-header h5') : $('#modalSeguimiento .modal-header h5');
            const tituloActual = modulo === 'formulacion' ? $('#tituloFormulacionSpan').text() : $('#tituloSeguimientoSpan').text();
            
            modalHeader.html(`<input type="text" class="modal-title-input" id="editTituloInput" value="${tituloActual}" />`);
            $('#editTituloInput').focus();
            editandoTitulo = true;
            
            $('#editTituloInput').on('blur', function() { guardarTituloModal(modulo, $(this).val()); })
                .on('keypress', function(e) { if (e.which === 13) guardarTituloModal(modulo, $(this).val()); });
        }

        function guardarTituloModal(modulo, nuevoTitulo) {
            if (!nuevoTitulo.trim()) { restaurarTituloModal(modulo); return; }

            const id = modulo === 'formulacion' ? $('#formulacion_id').val() : $('#seguimiento_id').val();
            
            $.ajax({
                url: basePath + '/modulo144/guardar',
                type: 'POST',
                data: { modulo: modulo, id: id, nombre_borrador: nuevoTitulo },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (modulo === 'formulacion') {
                            $('#tituloFormulacionSpan').text(nuevoTitulo);
                            $('#modalFormulacion .modal-header h5').html(`<i class="fas fa-clipboard-list me-2"></i>FORMULACIÓN 144 - <span id="tituloFormulacionSpan">${nuevoTitulo}</span>`);
                        } else {
                            $('#tituloSeguimientoSpan').text(nuevoTitulo);
                            $('#modalSeguimiento .modal-header h5').html(`<i class="fas fa-chart-line me-2"></i>SEGUIMIENTO 144 - <span id="tituloSeguimientoSpan">${nuevoTitulo}</span>`);
                        }
                        editandoTitulo = false;
                        mostrarAutoSaveIndicator();
                    } else { restaurarTituloModal(modulo); }
                },
                error: function() { restaurarTituloModal(modulo); }
            });
        }

        function restaurarTituloModal(modulo) {
            const tituloActual = modulo === 'formulacion' ? $('#tituloFormulacionSpan').text() : $('#tituloSeguimientoSpan').text();
            if (modulo === 'formulacion') {
                $('#modalFormulacion .modal-header h5').html(`<i class="fas fa-clipboard-list me-2"></i>FORMULACIÓN 144 - <span id="tituloFormulacionSpan">${tituloActual}</span>`);
            } else {
                $('#modalSeguimiento .modal-header h5').html(`<i class="fas fa-chart-line me-2"></i>SEGUIMIENTO 144 - <span id="tituloSeguimientoSpan">${tituloActual}</span>`);
            }
            editandoTitulo = false;
        }

        const CUMPLIMIENTO_RING_CIRCUNFERENCIA = 238.76;
        const CUMPLIMIENTO_RING_MIN_VISIBLE = 6; // longitud mínima del arco (px) para que se vea la "puntita" en 0%

        function actualizarAnilloCumplimiento(porcentaje) {
            const p = Math.max(0, Math.min(100, parseFloat(porcentaje) || 0));
            let longitud = (p / 100) * CUMPLIMIENTO_RING_CIRCUNFERENCIA;
            if (longitud < CUMPLIMIENTO_RING_MIN_VISIBLE) longitud = CUMPLIMIENTO_RING_MIN_VISIBLE;
            const offset = CUMPLIMIENTO_RING_CIRCUNFERENCIA - longitud;

            const arc = document.getElementById('seguimiento_cumplimiento_arc');
            const text = document.getElementById('seguimiento_cumplimiento_text');
            if (arc) {
                arc.style.strokeDashoffset = offset;
                arc.style.stroke = p <= 0 ? '#FF3B30' : (p >= 100 ? '#34C759' : '#FF9500');
            }
            if (text) text.textContent = Math.round(p) + '%';
        }

        function calcularCumplimientoSeguimiento() {
            const tipoMedicion = $('#formSeguimiento').data('tipoMedicion') || '';
            const s1Raw = $('#seguimiento_semestre1').val();
            const s2Raw = $('#seguimiento_semestre2').val();
            const s1 = (s1Raw !== '' && s1Raw !== null && s1Raw !== undefined && !isNaN(s1Raw)) ? parseFloat(s1Raw) : null;
            const s2 = (s2Raw !== '' && s2Raw !== null && s2Raw !== undefined && !isNaN(s2Raw)) ? parseFloat(s2Raw) : null;

            let logrado = null;
            if (tipoMedicion === 'Acumulado') {
                if (s1 === null && s2 === null) {
                    // Sin ningún semestre reportado todavía, la línea base sola no cuenta como avance.
                    logrado = 0;
                } else {
                    const lbRaw = $('#formSeguimiento').data('lineaBaseMeta');
                    const lb = (lbRaw !== '' && lbRaw !== null && lbRaw !== undefined && !isNaN(lbRaw)) ? parseFloat(lbRaw) : null;
                    let suma = 0;
                    if (lb !== null) suma += lb;
                    if (s1 !== null) suma += s1;
                    if (s2 !== null) suma += s2;
                    logrado = suma;
                }
            } else if (tipoMedicion === 'Nuevo gestionado durante la vigencia') {
                let suma = 0;
                if (s1 !== null) suma += s1;
                if (s2 !== null) suma += s2;
                logrado = suma;
            } else if (tipoMedicion === 'Promedio') {
                let suma = 0, contador = 0;
                if (s1 !== null) { suma += s1; contador++; }
                if (s2 !== null) { suma += s2; contador++; }
                logrado = contador > 0 ? (suma / contador) : 0;
            } else if (tipoMedicion === 'Último valor reportado') {
                if (s2 !== null) logrado = s2;
                else if (s1 !== null) logrado = s1;
            } else if (tipoMedicion === 'Límite') {
                logrado = s2;
            }

            const logradoNum = logrado !== null ? logrado : 0;
            $('#seguimiento_meta_ejecutada').val(logradoNum.toFixed(2));

            const meta = parseFloat($('#seguimiento_meta_programada').val());
            $('#seguimiento_anio_view').text(logradoNum.toFixed(2) + '/' + (!isNaN(meta) ? meta.toFixed(2) : '-'));

            let pct;
            if (tipoMedicion === 'Límite') {
                // Declarado (semestre 2) debe ser MENOR que el calculado (límite) para cumplir.
                if (logrado === null || logrado === 0) {
                    pct = 0;
                } else if (!isNaN(meta) && logrado >= meta) {
                    pct = 0;
                } else if (!isNaN(meta)) {
                    pct = 100;
                } else {
                    pct = 0;
                }
            } else {
                pct = (!isNaN(meta) && meta > 0) ? (logradoNum / meta) * 100 : 0;
            }
            $('#seguimiento_porcentaje').val(pct.toFixed(2));
            actualizarAnilloCumplimiento(pct);
        }

        function cargarDatosFormulacionEnSeguimiento(b) {
            let valorAnual = '-';
            const lineaBaseRaw = b.linea_base_meta;
            const metaS1Raw = b.meta_s1;
            const metaS2Raw = b.meta_s2;
            
            const lineaBase = (lineaBaseRaw && lineaBaseRaw !== '') ? parseFloat(lineaBaseRaw) : null;
            const metaS1 = (metaS1Raw && metaS1Raw !== '') ? parseFloat(metaS1Raw) : null;
            const metaS2 = (metaS2Raw && metaS2Raw !== '') ? parseFloat(metaS2Raw) : null;
            
            if (b.tipo_medicion === 'Acumulado') {
                let suma = 0;
                if (lineaBase !== null) suma += lineaBase;
                if (metaS1 !== null) suma += metaS1;
                if (metaS2 !== null) suma += metaS2;
                valorAnual = suma.toFixed(2);
            } 
            else if (b.tipo_medicion === 'Nuevo gestionado durante la vigencia') {
                let suma = 0;
                if (metaS1 !== null) suma += metaS1;
                if (metaS2 !== null) suma += metaS2;
                valorAnual = suma.toFixed(2);
            }
            else if (b.tipo_medicion === 'Promedio') {
                let suma = 0;
                let contador = 0;
                if (metaS1 !== null) { suma += metaS1; contador++; }
                if (metaS2 !== null) { suma += metaS2; contador++; }
                valorAnual = contador > 0 ? (suma / contador).toFixed(2) : '0.00';
            }
            else if (b.tipo_medicion === 'Último valor reportado') {
                let ultimoValor = null;
                if (metaS2 !== null && metaS2 !== undefined && metaS2 !== '' && !isNaN(metaS2)) {
                    ultimoValor = metaS2;
                } else if (metaS1 !== null && metaS1 !== undefined && metaS1 !== '' && !isNaN(metaS1)) {
                    ultimoValor = metaS1;
                } else if (lineaBase !== null && lineaBase !== undefined && lineaBase !== '' && !isNaN(lineaBase)) {
                    ultimoValor = lineaBase;
                }
                valorAnual = ultimoValor !== null ? ultimoValor.toFixed(2) : '';
            }
            else if (b.tipo_medicion === 'Límite') {
                const limiteCalc = obtenerUltimoValorReportado(lineaBaseRaw, metaS1Raw, metaS2Raw);
                valorAnual = limiteCalc !== null ? limiteCalc.toFixed(2) : '';
            }

            $('#seguimiento_anio_view').text(valorAnual);
            $('#seguimiento_meta_programada').val(valorAnual);
            $('#formSeguimiento').data('tipoMedicion', b.tipo_medicion || '');
            $('#formSeguimiento').data('lineaBaseMeta', lineaBaseRaw);
            if (b.tipo_medicion === 'Acumulado') {
                $('#seguimiento_linea_base_wrap').show();
                $('#seguimiento_linea_base_view').text(lineaBase !== null ? lineaBase.toFixed(2) : '-');
            } else {
                $('#seguimiento_linea_base_wrap').hide();
            }
            $('#seguimiento_linea_view').text(b.linea_estrategica ? (b.linea_codigo ? b.linea_codigo + ' - ' : '') + b.linea_estrategica : '-');
            $('#seguimiento_objetivo_view').text(b.objetivo || '-');
            $('#seguimiento_estrategia_view').text(b.estrategia || '-');
            $('#seguimiento_motor_view').text(b.motor_desarrollo ? (b.motor_codigo ? b.motor_codigo + ' - ' : '') + b.motor_desarrollo : '-');
            $('#seguimiento_meta_resultado_view').text(b.meta_resultado || '-');
            $('#seguimiento_proyecto_view').text(b.proyecto ? (b.proyecto_codigo ? b.proyecto_codigo + ' - ' : '') + b.proyecto : '-');
            $('#seguimiento_formula_medicion_view').text(b.formula_medicion || '-');
            $('#seguimiento_id_indicador_view').text(b.id_indicador || '-');
            $('#seguimiento_frecuencia_medicion_view').text(b.frecuencia_medicion || '-');
            $('#seguimiento_unidad_medida_view').text(b.unidad_medida || '-');
            $('#seguimiento_tipo_medicion_view').text(b.tipo_medicion || '-');
            $('#seguimiento_descripcion_indicador_view').text(b.descripcion_indicador || '-');
            $('#seguimiento_ponderacion_proyectos_view').text(b.ponderacion_proyectos ? b.ponderacion_proyectos + '%' : '-');
            $('#seguimiento_actividad_view').text(b.actividad_proyecto || '-');
            $('#seguimiento_ponderacion_actividades_view').text(b.ponderacion_actividades ? b.ponderacion_actividades + '%' : '-');
            $('#seguimiento_responsable_view').text(b.responsable_formulacion || '-');
        }

        function verSeguimiento(modulo, id) { editarBorrador(modulo, id); }

        function autoGuardarFormulacion() {
            const id = $('#formulacion_id').val();
            if (!id) return;

            if (timeoutFormulacion) clearTimeout(timeoutFormulacion);

            const sesion = formulacionSesion;
            timeoutFormulacion = setTimeout(function() {
                timeoutFormulacion = null;
                // Solo se guarda si el usuario editó algo en esta apertura del modal; las cargas
                // automáticas (selects en cascada, ponderación del proyecto) no deben sobrescribir.
                if (sesion !== formulacionSesion || !formulacionEditadaPorUsuario) return;
                enviarFormulacion(construirDatosFormulacion());
            }, 500);
        }

        // Envía de inmediato un autoguardado pendiente (p. ej. al cerrar el modal antes de los 500 ms)
        function flushAutoGuardarFormulacion() {
            if (!timeoutFormulacion) return;
            clearTimeout(timeoutFormulacion);
            timeoutFormulacion = null;
            if (formulacionEditadaPorUsuario && $('#formulacion_id').val()) {
                enviarFormulacion(construirDatosFormulacion());
            }
        }

        function construirDatosFormulacion() {
            const id = $('#formulacion_id').val();
            const gestionado = $('#formulacion_gestionado_facultades').is(':checked') ? 1 : 0;

            const nombreIndicadorActual = $('#formulacion_nombre_indicador').val();
            const nombreBorradorActual = (nombreIndicadorActual && nombreIndicadorActual.trim() !== '') ? nombreIndicadorActual.trim() : nombreBorradorProvisional;

            return {
                modulo: 'formulacion', id: id,
                nombre_borrador: nombreBorradorActual,
                formulario_id: formularioId,
                anio: formularioAnio,
                linea_estrategica: $('#formulacion_linea').val(),
                objetivo: $('#formulacion_objetivo').val(),
                estrategia: $('#formulacion_estrategia').val(),
                motor_desarrollo: $('#formulacion_motor').val(),
                proyecto: $('#formulacion_proyecto').val(),
                meta_resultado: $('#formulacion_meta').val(),
                ponderacion_proyectos: $('#formulacion_ponderacion_proyectos').val(),
                actividad_proyecto: $('#formulacion_actividad').val(),
                ponderacion_actividades: $('#formulacion_ponderacion_actividades').val(),
                responsable_formulacion: $('#formulacion_responsable_hidden').val(),
                id_indicador: $('#formulacion_id_indicador').val(),
                gestionado_facultades: gestionado,
                nombre_indicador: $('#formulacion_nombre_indicador').val(),
                formula_medicion: $('#formulacion_formula_medicion').val(),
                frecuencia_medicion: $('#formulacion_frecuencia_medicion').val(),
                unidad_medida: $('#formulacion_unidad_medida').val(),
                tipo_medicion: $('#formulacion_tipo_medicion').val(),
                descripcion_indicador: $('#formulacion_descripcion_indicador').val(),
                linea_base_meta: $('#formulacion_linea_base_meta').val(),
                anio_base_meta: $('#formulacion_anio_base_meta').val(),
                meta_s1: $('#formulacion_meta_s1').val(),
                meta_s2: $('#formulacion_meta_s2').val(),
                planes_institucionales: $('#formulacion_planes_institucionales').val()
            };
        }

        function enviarFormulacion(data) {
            const sesion = formulacionSesion;
            $.ajax({
                url: basePath + '/modulo144/guardar',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        mostrarAutoSaveIndicator();
                        // Si el modal ya se cerró/reabrió, no tocar el formulario actual
                        if (sesion !== formulacionSesion) return;
                        validarPestanas();
                        // Refrescar datos locales tras guardar exitoso
                        refrescarFormulacionesExistentes(function() { _ejecutarCalculoAcumulado(); });
                    } else if (response.acumulado !== undefined) {
                        // Error de ponderación server-side: actualizar badge y bloquear
                        if (sesion === formulacionSesion) {
                            refrescarFormulacionesExistentes(function() { _ejecutarCalculoAcumulado(); });
                        }
                        Swal.fire({
                            icon: 'warning',
                            title: 'Ponderación excedida',
                            text: response.message,
                            confirmButtonColor: '#1D71B8'
                        });
                    }
                }
            });
        }

        function autoGuardarSeguimiento() {
            const id = $('#seguimiento_id').val();
            if (!id) return;
            
            if (timeoutId) clearTimeout(timeoutId);
            
            timeoutId = setTimeout(function() {
                const data = {
                    modulo: 'seguimiento', id: id,
                    indicador: $('#seguimiento_indicador').val(),
                    fecha_seguimiento: $('#seguimiento_fecha').val(),
                    semestre1_seguimiento: $('#seguimiento_semestre1').val(),
                    semestre2_seguimiento: $('#seguimiento_semestre2').val(),
                    meta_programada: $('#seguimiento_meta_programada').val(),
                    meta_ejecutada: $('#seguimiento_meta_ejecutada').val(),
                    porcentaje_avance: $('#seguimiento_porcentaje').val(),
                    responsable_seguimiento: $('#seguimiento_responsable').val(),
                    observaciones: $('#seguimiento_observaciones').val(),
                    logros: $('#seguimiento_logros').val(),
                    limites: $('#seguimiento_limites').val(),
                    observacion_estado: $('#seguimiento_observacion_estado').val(),
                    acciones_fortalecimiento: $('#seguimiento_acciones_fortalecimiento').val(),
                    logros_sem2: $('#seguimiento_logros_sem2').val(),
                    limites_sem2: $('#seguimiento_limites_sem2').val(),
                    observacion_estado_sem2: $('#seguimiento_observacion_estado_sem2').val(),
                    acciones_fortalecimiento_sem2: $('#seguimiento_acciones_fortalecimiento_sem2').val()
                };
                
                $.ajax({
                    url: basePath + '/modulo144/guardar',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) mostrarAutoSaveIndicator();
                    }
                });
            }, 500);
        }

        function mostrarAutoSaveIndicator() {
            const indicator = document.getElementById('autoSaveIndicator');
            indicator.style.display = 'block';
            indicator.style.animation = 'none';
            indicator.offsetHeight;
            indicator.style.animation = 'fadeInOut 2s ease';
            setTimeout(function() { indicator.style.display = 'none'; }, 2000);
        }

        // Recalcula y repinta badges de TODOS los items de una línea (acotado al mismo módulo)
        function actualizarBadgesLinea(lineaCodigo, motorCodigo, proyectoCodigo, moduloCodigo) {
            if (!lineaCodigo) return;

            // Filtrar por L+M+P para no acumular entre proyectos distintos
            let attrFiltro = `[data-linea-item="${lineaCodigo}"]`;
            if (motorCodigo)    attrFiltro += `[data-motor-item="${motorCodigo}"]`;
            if (proyectoCodigo) attrFiltro += `[data-proyecto-item="${proyectoCodigo}"]`;
            const selector = moduloCodigo
                ? `.lista-item${attrFiltro}[data-modulo="${moduloCodigo}"]`
                : `.lista-item${attrFiltro}`;

            // Sumar ponderaciones de todos los items de esa línea desde el DOM
            let suma = 0;
            document.querySelectorAll(selector).forEach(function(item) {
                suma += parseFloat(item.getAttribute('data-ponderacion') || 0);
            });

            const completo = suma >= 99.99 && suma <= 100.01;
            const excedido = suma > 100.01;

            // Actualizar cada badge de esa línea que pertenezca al mismo módulo
            // Los badges están dentro de .lista-item, así que los buscamos dentro del scope correcto
            document.querySelectorAll(selector).forEach(function(item) {
                const badge = item.querySelector(`.lmp-badge[data-linea="${lineaCodigo}"]`);
                if (!badge) return;

                // Suma visible
                const sumaEl = badge.querySelector('.lmp-suma');
                if (sumaEl) {
                    sumaEl.textContent = suma.toFixed(1);
                    const propio = parseFloat(item.getAttribute('data-ponderacion') || 0);
                    sumaEl.classList.toggle('sin-aporte', propio <= 0);
                }

                // Clases de color
                badge.classList.remove('lmp-completo', 'lmp-excedido');
                if (completo) badge.classList.add('lmp-completo');
                else if (excedido) badge.classList.add('lmp-excedido');

                // Tooltip
                const aviso = excedido ? ' ⚠ Excede el 100%' : '';
                badge.setAttribute('title', `Ponderación línea: ${suma.toFixed(2)} / 100${aviso}`);
            });

            // Actualizar color del nombre
            document.querySelectorAll(selector).forEach(function(item) {
                const titulo = item.querySelector('.lista-item-titulo');
                if (!titulo) return;
                titulo.classList.remove('titulo-linea-completa', 'titulo-linea-excedida');
                if (completo) titulo.classList.add('titulo-linea-completa');
                else if (excedido) titulo.classList.add('titulo-linea-excedida');
            });
        }

        // Actualiza la badge LMP en la lista para el borrador actualmente abierto
        function actualizarLmpBadgeEnLista() {
            const borradorId = $('#formulacion_id').val();
            if (!borradorId) return;

            const lineaSelect  = document.getElementById('formulacion_linea');
            const motorSelect  = document.getElementById('formulacion_motor');
            const proyectoSelect = document.getElementById('formulacion_proyecto');

            // Extraer código de línea (texto "L1 - nombre" → "L1")
            let lineaCodigo = null;
            if (lineaSelect && lineaSelect.selectedIndex > 0) {
                const txt = lineaSelect.options[lineaSelect.selectedIndex].textContent.trim();
                lineaCodigo = txt.split(' - ')[0];
            }

            // Extraer código de motor (texto "M1 - nombre" → "M1")
            let motorCodigo = null;
            if (motorSelect && motorSelect.selectedIndex > 0) {
                const txt = motorSelect.options[motorSelect.selectedIndex].textContent.trim();
                motorCodigo = txt.split(' - ')[0];
            }

            // Extraer código proyecto (texto "P1 - nombre" → "P1")
            let proyectoCodigo = null;
            if (proyectoSelect && proyectoSelect.selectedIndex > 0) {
                const txt = proyectoSelect.options[proyectoSelect.selectedIndex].textContent.trim();
                proyectoCodigo = txt.split(' - ')[0];
            }

            const parts = [lineaCodigo, motorCodigo, proyectoCodigo].filter(Boolean);
            const listItem = document.querySelector(`.lista-item[data-item-id="${borradorId}"]`);
            if (!listItem) return;

            const badge = listItem.querySelector('.lmp-badge');
            if (!badge) return;

            if (parts.length > 0) {
                const sumaEl = badge.querySelector('.lmp-suma');
                const sumaHtml = sumaEl ? sumaEl.outerHTML : '';
                badge.className = 'lmp-badge';
                badge.setAttribute('data-linea', lineaCodigo || '');
                badge.innerHTML = '<i class="fas fa-sitemap"></i>' + parts.join(' - ') + sumaHtml;
            } else {
                badge.className = 'lmp-badge sin-datos';
                badge.innerHTML = '—';
            }

            // Actualizar color del título
            const titulo = listItem.querySelector('.lista-item-titulo');
            if (titulo && lineaCodigo) {
                // Mantener clase verde si ya era completa (suma se recalcula en backend)
            }
        }

        function editarBorrador(modulo, id) {
            $.ajax({
                url: basePath + '/modulo144/getBorrador?modulo=' + modulo + '&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const b = response.borrador;
                        
                        if (modulo === 'formulacion') {
                            // Nueva apertura: invalida timers/respuestas pendientes de la anterior
                            if (timeoutFormulacion) { clearTimeout(timeoutFormulacion); timeoutFormulacion = null; }
                            const sesion = ++formulacionSesion;
                            formulacionEditadaPorUsuario = false;
                            $('#formulacion_id').val(b.id);
                            $('#tituloFormulacionSpan').text(b.nombre_borrador);
                            nombreBorradorProvisional = (b.nombre_indicador && b.nombre_indicador.trim() !== '') ? 'Nuevo Borrador' : (b.nombre_borrador || 'Nuevo Borrador');
                            $('#formulacion_linea').val(b.linea_estrategica);
                            sincronizarBuscadorProyecto(b);
                            $('#formulacion_objetivo').val(b.objetivo);
                            $('#formulacion_tipo_medicion').val(b.tipo_medicion);
                            
                            const selectLinea = document.getElementById('formulacion_linea');
                            const lineaOption = selectLinea.options[selectLinea.selectedIndex];
                            const lineaId = lineaOption ? lineaOption.getAttribute('data-id') : null;
                            
                            function cargarEstrategias(lineaId, valorEstrategia) {
                                if (lineaId) {
                                    document.getElementById('formulacion_estrategia').innerHTML = '<option value="">Cargando estrategias...</option>';
                                    $.ajax({
                                        url: basePath + '/modulo144/getEstrategiasPorLinea',
                                        type: 'GET',
                                        data: { linea_id: lineaId },
                                        dataType: 'json',
                                        success: function(res) {
                                            if (sesion !== formulacionSesion) return;
                                            const selectEstrategia = document.getElementById('formulacion_estrategia');
                                            selectEstrategia.innerHTML = '<option value="">Seleccione una estrategia</option>';
                                            if (res.success && res.estrategias && res.estrategias.length > 0) {
                                                res.estrategias.forEach(function(estrategia) {
                                                    const option = document.createElement('option');
                                                    option.value = estrategia.descripcion;
                                                    option.textContent = estrategia.descripcion;
                                                    selectEstrategia.appendChild(option);
                                                });
                                                if (valorEstrategia) $('#formulacion_estrategia').val(valorEstrategia);
                                            } else {
                                                selectEstrategia.innerHTML = '<option value="">No hay estrategias disponibles</option>';
                                            }
                                            validarPestanas();
                                        }
                                    });
                                } else {
                                    document.getElementById('formulacion_estrategia').innerHTML = '<option value="">Seleccione una estrategia</option>';
                                    validarPestanas();
                                }
                            }
                            
                            function cargarMotores(lineaId, valorMotor) {
                                if (lineaId) {
                                    document.getElementById('formulacion_motor').innerHTML = '<option value="">Cargando motores...</option>';
                                    $.ajax({
                                        url: basePath + '/modulo144/getMotoresPorLinea',
                                        type: 'GET',
                                        data: { linea_id: lineaId },
                                        dataType: 'json',
                                        success: function(res) {
                                            if (sesion !== formulacionSesion) return;
                                            const selectMotor = document.getElementById('formulacion_motor');
                                            selectMotor.innerHTML = '<option value="">Seleccione un motor de desarrollo</option>';
                                            if (res.success && res.motores && res.motores.length > 0) {
                                                res.motores.forEach(function(motor) {
                                                    const option = document.createElement('option');
                                                    option.value = motor.nombre;
                                                    option.setAttribute('data-motor-id', motor.id);
                                                    option.textContent = (motor.codigo ? motor.codigo + ' - ' : '') + motor.nombre;
                                                    selectMotor.appendChild(option);
                                                });
                                                if (valorMotor) {
                                                    $('#formulacion_motor').val(valorMotor);
                                                    setTimeout(function() {
                                                        if (sesion !== formulacionSesion) return;
                                                        cargarProyectosPorMotorConValor(b.proyecto);
                                                    }, 300);
                                                }
                                            } else {
                                                selectMotor.innerHTML = '<option value="">No hay motores disponibles</option>';
                                            }
                                            validarPestanas();
                                        }
                                    });
                                } else {
                                    document.getElementById('formulacion_motor').innerHTML = '<option value="">Seleccione un motor de desarrollo</option>';
                                    validarPestanas();
                                }
                            }
                            
                            function cargarProyectosPorMotorConValor(valorProyecto) {
                                const selectMotor = document.getElementById('formulacion_motor');
                                const motorOption = selectMotor.options[selectMotor.selectedIndex];
                                const motorId = motorOption ? motorOption.getAttribute('data-motor-id') : null;
                                
                                if (lineaId && motorId) {
                                    document.getElementById('formulacion_proyecto').innerHTML = '<option value="">Cargando proyectos...</option>';
                                    $.ajax({
                                        url: basePath + '/modulo144/getProyectosPorLineaYMotor',
                                        type: 'GET',
                                        data: { linea_id: lineaId, motor_id: motorId },
                                        dataType: 'json',
                                        success: function(res) {
                                            if (sesion !== formulacionSesion) return;
                                            const selectProyecto = document.getElementById('formulacion_proyecto');
                                            selectProyecto.innerHTML = '<option value="">Seleccione un proyecto</option>';
                                            if (res.success && res.proyectos && res.proyectos.length > 0) {
                                                res.proyectos.forEach(function(proyecto) {
                                                    const option = document.createElement('option');
                                                    option.value = proyecto.nombre;
                                                    option.setAttribute('data-proyecto-id', proyecto.id);
                                                    option.textContent = proyecto.codigo + ' - ' + proyecto.nombre;
                                                    selectProyecto.appendChild(option);
                                                });
                                                if (valorProyecto) {
                                                    $('#formulacion_proyecto').val(valorProyecto);
                                                    setTimeout(cargarPonderacionProyecto, 100);
                                                    setTimeout(actualizarLmpBadgeEnLista, 150);
                                                }
                                            } else {
                                                selectProyecto.innerHTML = '<option value="">No hay proyectos disponibles</option>';
                                            }
                                            validarPestanas();
                                        }
                                    });
                                }
                            }
                            
                            cargarEstrategias(lineaId, b.estrategia);
                            cargarMotores(lineaId, b.motor_desarrollo);
                            
                            $('#formulacion_meta').val(b.meta_resultado);
                            $('#formulacion_ponderacion_proyectos').val(b.ponderacion_proyectos);
                            $('#formulacion_actividad').val(b.actividad_proyecto);
                            $('#formulacion_ponderacion_actividades').val(b.ponderacion_actividades);
                            const responsableGuardado = b.responsable_formulacion || '';
                            $('#formulacion_responsable_hidden').val(responsableGuardado);
                            const responsablesArray = responsableGuardado
                                ? responsableGuardado.split(',').map(s => s.trim()).filter(s => s !== '')
                                : [];
                            $('#formulacion_responsable').val(responsablesArray).trigger('change.select2');
                            $('#formulacion_id_indicador').val(b.id_indicador);
                            $('#formulacion_gestionado_facultades').prop('checked', b.gestionado_facultades == 1);
                            toggleDistribucionFacultadesTabla(b.gestionado_facultades == 1);
                            cargarDistribucionFacultades(b.id);
                            $('#formulacion_nombre_indicador').val(b.nombre_indicador);
                            $('#formulacion_formula_medicion').val(b.formula_medicion);
                            $('#formulacion_frecuencia_medicion').val(b.frecuencia_medicion);
                            $('#formulacion_unidad_medida').val(b.unidad_medida);
                            $('#formulacion_descripcion_indicador').val(b.descripcion_indicador);
                            $('#formulacion_linea_base_meta').val(b.linea_base_meta);
                            $('#formulacion_meta_s1').val(b.meta_s1);
                            $('#formulacion_meta_s2').val(b.meta_s2);
                            
                            // Calcular valor anual después de cargar valores
                            calcularValorAnual();
                            actualizarTotalDistribucionFacultades();
                            setTimeout(function() { calcularAcumuladoActividades(true); }, 400);
                            
                            if (b.planes_institucionales) {
                                cargarPlanesDesdeBD(b.planes_institucionales);
                            } else {
                                planesSeleccionados = [];
                                actualizarContenedorPlanes();
                                actualizarCampoOculto();
                            }
                            
                            $('#modalFormulacion input, #modalFormulacion select, #modalFormulacion textarea').prop('disabled', false);
                            $('#modalFormulacion').modal('show');
                            setTimeout(validarPestanas, 500);
                        } else {
                            $('#seguimiento_id').val(b.id);
                            $('#tituloSeguimientoSpan').text(b.nombre_borrador);
                            $('#seguimiento_indicador').val(b.indicador);
                            $('#seguimiento_fecha').val(b.fecha_seguimiento);
                            $('#seguimiento_semestre1').val(b.semestre1_seguimiento);
                            $('#seguimiento_semestre2').val(b.semestre2_seguimiento);
                            $('#seguimiento_meta_programada').val(b.meta_programada);
                            $('#seguimiento_meta_ejecutada').val(b.meta_ejecutada);
                            $('#seguimiento_porcentaje').val(b.porcentaje_avance);
                            $('#seguimiento_responsable').val(b.responsable_seguimiento);
                            $('#seguimiento_observaciones').val(b.observaciones);
                            $('#seguimiento_logros').val(b.logros);
                            $('#seguimiento_limites').val(b.limites);
                            $('#seguimiento_observacion_estado').val(b.observacion_estado);
                            $('#seguimiento_acciones_fortalecimiento').val(b.acciones_fortalecimiento);
                            $('#seguimiento_logros_sem2').val(b.logros_sem2 || '');
                            $('#seguimiento_limites_sem2').val(b.limites_sem2 || '');
                            $('#seguimiento_observacion_estado_sem2').val(b.observacion_estado_sem2 || '');
                            $('#seguimiento_acciones_fortalecimiento_sem2').val(b.acciones_fortalecimiento_sem2 || '');
                            cargarDatosFormulacionEnSeguimiento(b);
                            calcularCumplimientoSeguimiento();
                            $('#modalSeguimiento input, #modalSeguimiento select, #modalSeguimiento textarea').prop('disabled', false);
                            $('#seguimiento_semestre1').prop('disabled', b.tipo_medicion === 'Límite');
                            aplicarBloqueoSemestres();
                            cargarSeguimientoFacultades(b.id, String(b.gestionado_facultades) === '1');
                            cargarResumenEvidencias(b.id);
                            $('#modalSeguimiento').modal('show');
                        }
                    }
                }
            });
        }

        function verBorrador(modulo, id) {
            $.ajax({
                url: basePath + '/modulo144/getBorrador?modulo=' + modulo + '&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const b = response.borrador;
                        $('#ver_titulo').text(b.nombre_borrador);
                        let html = '<div class="row">';
                        <?php foreach ($datos_modulos as $key => $modulo_config): ?>
                        if (modulo === '<?php echo $key; ?>') {
                            <?php foreach ($modulo_config['config']['campos_vista'] as $label => $campo): ?>
                            html += '<div class="col-md-6 mb-3"><strong><?php echo $label; ?>:</strong><br>' + (b.<?php echo $campo; ?> || 'No especificado') + '</div>';
                            <?php endforeach; ?>
                        }
                        <?php endforeach; ?>
                        html += '</div>';
                        $('#ver_contenido').html(html);
                        $('#modalVerFormulario').modal('show');
                    }
                }
            });
        }

        function verBorradorSoloLectura(modulo, id) {
            editarBorrador(modulo, id);
            const modalSel = modulo === 'formulacion' ? '#modalFormulacion' : '#modalSeguimiento';
            setTimeout(function() {
                $(modalSel + ' input, ' + modalSel + ' select, ' + modalSel + ' textarea').prop('disabled', true);
                // La facultad del usuario sí puede registrar su seguimiento aunque el resto sea de solo lectura
                if (modulo === 'seguimiento' && VIEWER_FACULTADES.length > 0) {
                    VIEWER_FACULTADES.forEach(function(facId) {
                        $('#tablaSeguimientoFacultades tbody tr[data-facultad-id="' + facId + '"] input').prop('disabled', false);
                    });
                    aplicarBloqueoSemestres();
                }
            }, 900);
        }

        function cambiarEstadoBorrador(modulo, id, estado) {
            const esAprobar = estado === 2;
            Swal.fire({
                title: esAprobar ? '¿Aprobar y publicar?' : '¿Cancelar formulación?',
                text: esAprobar ? 'Esta formulación pasará a estado APROBADO' : 'Esta formulación pasará a estado CANCELADO',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: esAprobar ? '#27AE60' : '#E74C3C',
                confirmButtonText: 'Sí, continuar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/modulo144/cambiarEstado',
                        type: 'POST',
                        data: { modulo: modulo, id: id, estado: estado },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('¡Completado!', response.message, 'success');
                                setTimeout(() => { guardarEstadoAcordeon(); location.reload(); }, 1500);
                            }
                        }
                    });
                }
            });
        }

        function devolverABorrador(modulo, id) {
            Swal.fire({
                title: '¿Devolver a borradores?',
                text: 'El registro dejará de estar APROBADO y se reiniciará su flujo de aprobación',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF9500',
                confirmButtonText: 'Sí, devolver',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/modulo144/cambiarEstado',
                        type: 'POST',
                        data: { modulo: modulo, id: id, estado: 0 },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('¡Listo!', response.message, 'success');
                                setTimeout(() => { guardarEstadoAcordeon(); location.reload(); }, 1500);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        function cambiarSolicitudEstado(modulo, id, solicitudEstado) {
            const textos = {
                1: { title: '¿Solicitar aprobación?', text: 'Un administrador podrá aprobarla o rechazarla.', color: '#FF9500', boton: 'Sí, solicitar' },
                2: { title: '¿Rechazar solicitud?', text: 'El creador podrá corregirla y volver a solicitar.', color: '#E74C3C', boton: 'Sí, rechazar' }
            };
            const info = textos[solicitudEstado];
            Swal.fire({
                title: info.title,
                text: info.text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: info.color,
                confirmButtonText: info.boton
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/modulo144/cambiarSolicitudEstado',
                        type: 'POST',
                        data: { modulo: modulo, id: id, solicitud_estado: solicitudEstado },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('¡Listo!', response.message, 'success');
                                setTimeout(() => { guardarEstadoAcordeon(); location.reload(); }, 1500);
                            } else {
                                Swal.fire('Error', response.message || 'No se pudo actualizar', 'error');
                            }
                        }
                    });
                }
            });
        }

        function avanzarSemaforo(modulo, id, etapaActual) {
            Swal.fire({
                title: '¿Aprobar esta etapa?',
                text: 'Tu aprobación quedará registrada en el semáforo.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#34C759',
                confirmButtonText: 'Sí, aprobar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/modulo144/avanzarSemaforo',
                        type: 'POST',
                        data: { modulo: modulo, id: id, etapa_actual: etapaActual },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('¡Aprobado!', response.message, 'success');
                                setTimeout(() => { guardarEstadoAcordeon(); location.reload(); }, 1200);
                            } else {
                                Swal.fire('Error', response.message || 'No se pudo aprobar', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Error de conexión con el servidor', 'error');
                        }
                    });
                }
            });
        }

        // ═══ PUBLICAR BORRADORES (solo administrador) ═══
        const ES_ADMIN_PLAN = <?php echo !empty($esAdminPlan) ? 'true' : 'false'; ?>;
        // Semestres bloqueados en Editar formulario → Campos (aplica a todos los usuarios)
        <?php
            // "Excepto administradores": superadmin, administrador y sub administrador no quedan bloqueados
            $exceptoAdminBloqueo = !empty($formulario['bloqueo_excepto_admin']) && (
                (int)($_SESSION['usuario_id'] ?? 0) === 1
                || in_array(m144_normalizarRol($_SESSION['usuario_rol'] ?? ''), ['admin', 'administrador', 'sub administrador'], true)
            );
        ?>
        const BLOQUEO_SEM1 = <?php echo (!empty($formulario['bloqueo_sem1']) && !$exceptoAdminBloqueo) ? 'true' : 'false'; ?>;
        const BLOQUEO_SEM2 = <?php echo (!empty($formulario['bloqueo_sem2']) && !$exceptoAdminBloqueo) ? 'true' : 'false'; ?>;

        function aplicarBloqueoSemestres() {
            if (BLOQUEO_SEM1) $('#seguimiento_logros, #seguimiento_limites, #seguimiento_observacion_estado, #seguimiento_acciones_fortalecimiento').prop('disabled', true);
            if (BLOQUEO_SEM2) $('#seguimiento_logros_sem2, #seguimiento_limites_sem2, #seguimiento_observacion_estado_sem2, #seguimiento_acciones_fortalecimiento_sem2').prop('disabled', true);
            if (BLOQUEO_SEM1) $('#seguimiento_semestre1, #tablaSeguimientoFacultades .seg-fac-s1, #tablaSeguimientoFacultades .seg-fac-desc1').prop('disabled', true);
            if (BLOQUEO_SEM2) $('#seguimiento_semestre2, #tablaSeguimientoFacultades .seg-fac-s2, #tablaSeguimientoFacultades .seg-fac-desc2').prop('disabled', true);
            const bloqueados = [BLOQUEO_SEM1 ? 'Semestre 1' : '', BLOQUEO_SEM2 ? 'Semestre 2' : ''].filter(Boolean);
            $('#segBloqueoAviso').toggle(bloqueados.length > 0)
                .find('span').text(bloqueados.join(' y ') + (bloqueados.length > 1 ? ' están bloqueados' : ' está bloqueado') + ' por el administrador de este formulario.');
        }

        // ids vacío = todos los borradores completos del formulario
        function enviarPublicacion(ids) {
            return $.ajax({
                url: basePath + '/modulo144/publicarBorradores',
                type: 'POST',
                dataType: 'json',
                data: { formulario_id: formularioId, ids: ids || [] }
            });
        }

        function mostrarResultadoPublicacion(res) {
            if (!res.success) {
                Swal.fire('No se pudo publicar', res.message || 'Intenta de nuevo', 'error');
                return;
            }
            const omitidos = res.omitidos || [];
            let html = '<div class="imp-resumen"><div><b>' + (res.publicados || []).length + '</b><small>Publicados</small></div>'
                     + '<div><b>' + omitidos.length + '</b><small>Sin publicar</small></div></div>';
            if (omitidos.length) {
                html += '<p class="text-start small mb-1"><strong>Estos borradores están incompletos y siguen en borrador:</strong></p><ul class="imp-errores">'
                     + omitidos.map(function(o) { return '<li>#' + o.id + ' · ' + evalHtmlEscape(o.nombre || '') + ' — ' + evalHtmlEscape(o.motivo) + '</li>'; }).join('') + '</ul>';
            }
            Swal.fire({
                icon: omitidos.length ? ((res.publicados || []).length ? 'warning' : 'error') : 'success',
                title: res.message, html: html, width: 640, confirmButtonText: 'Aceptar'
            }).then(function() { guardarEstadoAcordeon(); location.reload(); });
        }

        function publicarBorradores(total) {
            Swal.fire({
                title: '¿Publicar los borradores?',
                html: 'Se publicarán los borradores completos de <strong>Formulación</strong> (' + total + ' en la lista) <strong>sin pasar por las etapas de aprobación</strong>. '
                    + 'Desde ese momento cada responsable los verá en <strong>Seguimiento</strong>. Los que estén incompletos se quedan en borrador y se te indicará cuál dato falta.'
                    + '<br><br>Escribe <strong>PUBLICAR</strong> para confirmar.',
                icon: 'question',
                input: 'text',
                inputPlaceholder: 'PUBLICAR',
                inputValidator: function(v) { if ((v || '').trim().toUpperCase() !== 'PUBLICAR') return 'Escribe PUBLICAR para confirmar'; },
                showCancelButton: true,
                confirmButtonColor: '#34C759',
                confirmButtonText: 'Sí, publicar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                enviarPublicacion([]).done(mostrarResultadoPublicacion)
                    .fail(function() { Swal.fire('Error', 'Error de conexión con el servidor', 'error'); });
            });
        }

        // Publica un solo indicador (administrador), sin pasar por las etapas de aprobación
        function publicarIndicador(id) {
            Swal.fire({
                title: '¿Publicar este indicador?',
                html: 'Se publicará <strong>sin pasar por las etapas de aprobación</strong> que le faltan. Desde ese momento aparecerá en <strong>Seguimiento</strong>.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#34C759',
                confirmButtonText: 'Sí, publicar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                enviarPublicacion([id]).done(mostrarResultadoPublicacion)
                    .fail(function() { Swal.fire('Error', 'Error de conexión con el servidor', 'error'); });
            });
        }

        // Solo superadministrador: devuelve a borrador todos los publicados del módulo
        function devolverTodosABorrador(modulo, total) {
            const nombre = modulo === 'formulacion' ? 'Formulación' : 'Seguimiento';
            const extra = modulo === 'formulacion'
                ? '<br><br><strong>Ojo:</strong> al volver a borrador la formulación, esos indicadores dejan de aparecer en Seguimiento hasta que se publiquen de nuevo.'
                : '';
            Swal.fire({
                title: '¿Devolver todos a borrador?',
                html: 'Se devolverán <strong>' + total + '</strong> registro(s) aprobados de <strong>' + nombre + '</strong> a borrador y se reiniciará su semáforo de aprobación.' + extra +
                      '<br><br>Escribe <strong>DEVOLVER</strong> para confirmar.',
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'DEVOLVER',
                inputValidator: (v) => { if ((v || '').trim().toUpperCase() !== 'DEVOLVER') return 'Escribe DEVOLVER para confirmar'; },
                showCancelButton: true,
                confirmButtonColor: '#FF3B30',
                confirmButtonText: 'Sí, devolver todos',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: basePath + '/modulo144/devolverTodosABorrador',
                    type: 'POST',
                    data: { modulo: modulo, formulario_id: formularioId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Listo', response.message, 'success');
                            setTimeout(() => { guardarEstadoAcordeon(); location.reload(); }, 1200);
                        } else {
                            Swal.fire('Error', response.message || 'No se pudo devolver a borrador', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error de conexión con el servidor', 'error');
                    }
                });
            });
        }

        function rechazarSemaforo(modulo, id, etapaActual) {
            Swal.fire({
                title: '¿Rechazar y devolver al creador?',
                html: 'El ítem volverá al inicio para que el creador lo corrija. Este motivo se le enviará como mensaje directo.',
                icon: 'warning',
                input: 'textarea',
                inputPlaceholder: 'Escribe el motivo del rechazo...',
                inputValidator: (value) => {
                    if (!value || !value.trim()) return 'Debes ingresar el motivo del rechazo';
                },
                showCancelButton: true,
                confirmButtonColor: '#FF3B30',
                confirmButtonText: 'Sí, rechazar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const motivo = result.value.trim();
                    $.ajax({
                        url: basePath + '/modulo144/rechazarSemaforo',
                        type: 'POST',
                        data: { modulo: modulo, id: id, etapa_actual: etapaActual, motivo: motivo },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Rechazado', response.message, 'success');
                                setTimeout(() => { guardarEstadoAcordeon(); location.reload(); }, 1200);
                            } else {
                                Swal.fire('Error', response.message || 'No se pudo rechazar', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Error de conexión con el servidor', 'error');
                        }
                    });
                }
            });
        }

        function verHistorialEstado(modulo, id) {
            $.ajax({
                url: basePath + '/modulo144/getHistorialSemaforo',
                type: 'GET',
                data: { modulo: modulo, id: id },
                dataType: 'json',
                success: function(response) {
                    if (!response.success) {
                        Swal.fire('Error', response.message || 'No se pudo cargar el historial', 'error');
                        return;
                    }
                    const historial = response.historial || [];
                    let html = '';
                    if (historial.length === 0) {
                        html = '<p class="text-muted mb-0">Todavía no hay aprobaciones ni rechazos registrados.</p>';
                    } else {
                        html += '<div style="text-align:left;">';
                        historial.forEach(function(h) {
                            const esAprobado = h.accion === 'aprobado';
                            const icono = esAprobado
                                ? '<i class="fas fa-check-circle" style="color:#34C759;"></i>'
                                : '<i class="fas fa-times-circle" style="color:#FF3B30;"></i>';
                            const accionTexto = esAprobado ? 'Aprobó' : 'Rechazó';
                            const fecha = h.fecha_creacion ? new Date(h.fecha_creacion.replace(' ', 'T')).toLocaleString('es-CO') : '';
                            html += '<div style="padding:8px 0;border-bottom:1px solid #eee;">'
                                 + '<div>' + icono + ' <strong>' + accionTexto + '</strong> — ' + $('<div>').text(h.usuario_nombre).html()
                                 + (h.rol ? ' <span class="text-muted">(' + $('<div>').text(h.rol).html() + ')</span>' : '')
                                 + '</div>'
                                 + '<div class="text-muted small">' + fecha + '</div>';
                            if (!esAprobado && h.motivo) {
                                html += '<div class="small" style="margin-top:4px;"><strong>Motivo:</strong> ' + $('<div>').text(h.motivo).html() + '</div>';
                            }
                            html += '</div>';
                        });
                        html += '</div>';
                    }
                    Swal.fire({
                        title: 'Historial de aprobación',
                        html: html,
                        width: 550,
                        confirmButtonText: 'Cerrar'
                    });
                },
                error: function() {
                    Swal.fire('Error', 'Error de conexión con el servidor', 'error');
                }
            });
        }

        function eliminarBorrador(modulo, id) {
            Swal.fire({
                title: '¿Eliminar formulación?',
                text: 'Esta acción NO se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E74C3C',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/modulo144/eliminar',
                        type: 'POST',
                        data: { modulo: modulo, id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('¡Eliminado!', response.message, 'success');
                                setTimeout(() => { guardarEstadoAcordeon(); location.reload(); }, 1500);
                            }
                        }
                    });
                }
            });
        }

        $(document).ready(function() {
            console.log('=== SISTEMA CARGADO CORRECTAMENTE ===');

            // ── SELECT2: Responsable múltiple con buscador (máx. 3) ─────────
            $('#formulacion_responsable').select2({
                placeholder: 'Seleccione uno o más responsables',
                allowClear: true,
                maximumSelectionLength: 3,
                language: {
                    noResults: function() { return 'No se encontraron resultados'; },
                    searching:  function() { return 'Buscando...'; },
                    maximumSelected: function() {
                        return '<span class="select2-max-reached">Máximo 3 responsables permitidos</span>';
                    }
                },
                escapeMarkup: function(m) { return m; },
                dropdownParent: $('#modalFormulacion')
            });

            $('#formulacion_responsable').on('change', function() {
                const seleccionados = $(this).val() || [];
                $('#formulacion_responsable_hidden').val(seleccionados.join(', '));
                autoGuardarFormulacion();
                validarPestanas();
            });
            // ────────────────────────────────────────────────────────────────

            $('#modalFormulacion').on('shown.bs.modal', function() {
                validarPestanas();
                calcularValorAnual();
            });

            // Solo una edición real del usuario habilita el autoguardado (isTrusted = evento del navegador,
            // no un .val()/trigger del código al cargar el borrador)
            const modalFormulacionEl = document.getElementById('modalFormulacion');
            ['input', 'change'].forEach(function(tipo) {
                modalFormulacionEl.addEventListener(tipo, function(e) {
                    if (e.isTrusted) formulacionEditadaPorUsuario = true;
                }, true);
            });
            $('#formulacion_responsable').on('select2:select select2:unselect select2:clear', function() {
                formulacionEditadaPorUsuario = true;
            });

            // Al cerrar: enviar lo pendiente y cortar cualquier carga/guardado tardío de esta apertura
            $('#modalFormulacion').on('hide.bs.modal', function() {
                flushAutoGuardarFormulacion();
                limpiarBuscadorProyecto();
                formulacionSesion++;
                formulacionEditadaPorUsuario = false;
                $('#formulacion_id').val('');
            });
            $('#formulacion_nombre_indicador, #formulacion_formula_medicion, #formulacion_frecuencia_medicion, #formulacion_unidad_medida, #formulacion_tipo_medicion, #formulacion_descripcion_indicador, #formulacion_linea_base_meta, #formulacion_meta_s1, #formulacion_meta_s2, #formulacion_gestionado_facultades').on('input change', function() {
                calcularValorAnual();
            });
        });

        /* ===== CONCERTACIONES: lista, filtros y concertación por indicador ===== */
        (function() {
            const chips = Array.from(document.querySelectorAll('#cnChips .cnx-chip'));
            const q = document.getElementById('cnBuscar'), l = document.getElementById('cnLinea'), sf = document.getElementById('cnFacultad');
            const btnVista = document.getElementById('cnVista');
            if (!q || !l) return;
            let estado = '';

            const filas = function() {
                return Array.from(document.querySelectorAll('#cnVistaLista .cnx-item, #cnTabla tbody tr.cn-fila'));
            };
            function cumple(el, t) {
                if (t && normFiltro(el.getAttribute('data-buscar')).indexOf(t) === -1) return false;
                if (l.value && el.getAttribute('data-linea') !== l.value) return false;
                if (estado === 'nocoinciden') return el.getAttribute('data-coinciden') === 'no';
                if (estado && el.getAttribute('data-estado') !== estado) return false;
                return true;
            }
            window.cnFiltrar = function() {
                const t = normFiltro(q.value);
                let visibles = 0;
                filas().forEach(function(el) {
                    const ok = cumple(el, t);
                    el.style.display = ok ? '' : 'none';
                    if (ok && el.classList.contains('cnx-item')) visibles++;
                });
                const n = document.getElementById('cnContador');
                if (n) n.textContent = visibles + (visibles === 1 ? ' indicador' : ' indicadores');
                const v1 = document.getElementById('cnxVacio'); if (v1) v1.hidden = visibles > 0;
            };
            window.cnActualizarChips = function() {
                const items = Array.from(document.querySelectorAll('#cnVistaLista .cnx-item'));
                const cuenta = { '': items.length, pendiente: 0, listo: 0, enviado: 0, aprobado: 0, sinreporte: 0 };
                items.forEach(function(it) {
                    const e = it.getAttribute('data-estado'); if (cuenta[e] !== undefined) cuenta[e]++;
                    if (it.getAttribute('data-coinciden') === 'no') cuenta.nocoinciden++;
                });
                chips.forEach(function(c) { const b = c.querySelector('b'); if (b) b.textContent = cuenta[c.getAttribute('data-f')] || 0; });
            };

            chips.forEach(function(c) {
                c.addEventListener('click', function() {
                    estado = c.getAttribute('data-f');
                    chips.forEach(function(x) { x.classList.toggle('on', x === c); });
                    window.cnFiltrar();
                });
            });
            q.addEventListener('input', window.cnFiltrar);
            l.addEventListener('change', window.cnFiltrar);

            // Formato Excel: bloques por facultad
            if (sf) {
                const vistos = {};
                document.querySelectorAll('#cnTabla thead .cn-g-fac').forEach(function(th) {
                    const id = th.getAttribute('data-fac');
                    if (vistos[id]) return; vistos[id] = true;
                    const o = document.createElement('option'); o.value = id; o.textContent = th.textContent.trim(); sf.appendChild(o);
                });
                sf.addEventListener('change', function() {
                    document.querySelectorAll('#cnTabla .cn-fg').forEach(function(c) {
                        c.style.display = (!sf.value || c.getAttribute('data-fac') === sf.value) ? '' : 'none';
                    });
                });
            }

            // Vista simple (por defecto) o formato Excel; se recuerda la elección
            function vista(excel) {
                const lista = document.getElementById('cnVistaLista'), mat = document.getElementById('cnVistaMatriz');
                if (lista) lista.hidden = excel;
                if (mat) mat.hidden = !excel;
                if (sf) sf.hidden = !excel;
                if (btnVista) btnVista.innerHTML = excel ? '<i class="fas fa-list me-1"></i>Vista simple' : '<i class="fas fa-table me-1"></i>Formato Excel';
                try { localStorage.setItem('cnVistaExcel', excel ? '1' : '0'); } catch (e) {}
            }
            if (btnVista) {
                btnVista.addEventListener('click', function() { vista(!!document.getElementById('cnVistaLista').hidden === false); });
                let guardada = false; try { guardada = localStorage.getItem('cnVistaExcel') === '1'; } catch (e) {}
                vista(guardada);
            }
            window.cnActualizarChips();
        })();

        let cnIndicadorActual = 0;
        let cnOrden = [];

        function cnFmt(v, unidad) {
            if (v === null || v === undefined || v === '') return '—';
            const n = parseFloat(v);
            if (isNaN(n)) return '—';
            return (Math.round(n * 100) / 100).toLocaleString('es-CO') + (unidad === 'Porcentaje' ? '%' : '');
        }

        // ── Concertación: la facultad y la vicerrectoría colocan su valor; al concertar se envía al superadministrador ──
        let cnDatos = null;

        function cnNum(v) { return (v === null || v === undefined || v === '') ? null : parseFloat(v); }

        // vacio | pendiente (falta un valor) | listo (los dos valores, falta concertar) | enviado (espera al superadministrador) | aprobado
        function cnEstadoCelda(c) {
            if (c.aprobado) return 'aprobado';
            const a = cnNum(c.vf), b = cnNum(c.vv);
            if (a === null && b === null) return 'vacio';
            if (a === null || b === null) return 'pendiente';
            return c.enviado ? 'enviado' : 'listo';
        }

        // Un lado (declarante): su valor y la descripción con la que lo explica. Quien puede colocarlo ve campos; los demás, solo lectura
        function cnLadoHtml(f, sem, c, lado, unidad) {
            const esF = lado === 'facultad';
            const val = esF ? c.vf : c.vv;
            const desc = esF ? c.df : c.dv;
            const puede = esF ? f.puede_f : f.puede_v;
            const quien = esF ? (c.uf ? 'Puesto por ' + c.uf + (c.ff ? ' · ' + c.ff : '') : (c.vf_propio ? '' : 'Valor reportado en el seguimiento'))
                              : (c.uv ? 'Puesto por ' + c.uv + (c.fv ? ' · ' + c.fv : '') : '');
            const nombre = esF ? 'facultad' : 'vicerrectoría';
            const attrs = 'data-fac="' + f.facultad_id + '" data-sem="' + sem + '" data-lado="' + lado + '"';
            if (puede && !c.aprobado) {
                return '<input type="number" step="0.01" class="cnx-in" ' + attrs + ' value="' + (val === null ? '' : val) + '" placeholder="Valor" title="' + evalHtmlEscape(quien) + '" aria-label="Valor de la ' + nombre + '">' +
                       '<textarea class="cnx-txt" rows="2" ' + attrs + ' placeholder="Descripción de la ' + nombre + '…" aria-label="Descripción de la ' + nombre + '">' + evalHtmlEscape(desc || '') + '</textarea>';
            }
            return (val === null ? '<span class="cnx-nd" title="' + evalHtmlEscape(quien) + '">—</span>' : '<span class="cnx-valor" title="' + evalHtmlEscape(quien) + '">' + cnFmt(val, unidad) + '</span>') +
                   (desc ? '<div class="cnx-desc-ro">' + evalHtmlEscape(desc) + '</div>' : '<div class="cnx-desc-ro cnx-nd">Sin descripción</div>');
        }

        function cnResultadoHtml(f, sem, c, esSuper, unidad) {
            const e = cnEstadoCelda(c);
            const attrs = 'data-fac="' + f.facultad_id + '" data-sem="' + sem + '"';
            const iguales = Math.abs((cnNum(c.vf) || 0) - (cnNum(c.vv) || 0)) < 0.01;
            const hint = iguales ? 'Coinciden: al concertar pasa al seguimiento' : 'Difieren en ' + cnFmt(Math.abs((cnNum(c.vf) || 0) - (cnNum(c.vv) || 0)), '') + ': decide el superadministrador';
            if (e === 'aprobado') {
                const quien = (c.apr_usuario ? 'Aprobó ' + c.apr_usuario : '') + (c.apr_fecha ? ' · ' + c.apr_fecha : '');
                return '<span class="cnx-estado fin" title="' + evalHtmlEscape(quien) + '"><i class="fas fa-check-circle"></i> Meta real <b>' + cnFmt(c.final, cnDatos ? cnDatos.indicador.unidad : '') + '</b></span>' +
                       '<span class="cnx-hint">Ya está en el seguimiento por facultades</span>' +
                       (esSuper ? '<button type="button" class="cnx-link" data-acc="quitar" ' + attrs + '>Quitar aprobación</button>' : '');
            }
            if (e === 'enviado') {
                let html = '<span class="cnx-estado env"><i class="fas fa-paper-plane"></i> Enviado al superadministrador</span>';
                if (esSuper) {
                    html += iguales
                        ? '<span class="cnx-acciones"><button type="button" class="cnx-btn verde" data-acc="aprobar" ' + attrs + '>Aprobar ' + cnFmt(c.vf, cnDatos.indicador.unidad) + '</button>' +
                          '<button type="button" class="cnx-btn sec" data-acc="rechazar" ' + attrs + '>Rechazar</button></span>'
                        : '<span class="cnx-acciones"><button type="button" class="cnx-btn verde" data-acc="aprobar" data-elige="facultad" ' + attrs + '>Aprobar la de la facultad (' + cnFmt(c.vf, cnDatos.indicador.unidad) + ')</button>' +
                          '<button type="button" class="cnx-btn verde" data-acc="aprobar" data-elige="vicerrectoria" ' + attrs + '>Aprobar la de la vicerrectoría (' + cnFmt(c.vv, cnDatos.indicador.unidad) + ')</button>' +
                          '<button type="button" class="cnx-btn sec" data-acc="rechazar" ' + attrs + '>Rechazar</button></span>';
                } else {
                    html += '<span class="cnx-hint">' + hint + '</span>';
                }
                return html;
            }
            if (e === 'listo') {
                return (c.rechazado ? '<span class="cnx-estado deb">Rechazado · sigan concertando</span>' : '') +
                       (f.puede_enviar ? '<button type="button" class="cnx-btn verde" data-acc="enviar" ' + attrs + '>Concertar</button>' : '<span class="cnx-estado">Listo para concertar</span>') +
                       '<span class="cnx-hint">' + hint + '</span>';
            }
            if (e === 'pendiente') return '<span class="cnx-estado">Falta el valor de la ' + (cnNum(c.vf) === null ? 'facultad' : 'vicerrectoría') + '</span>';
            return '<span class="cnx-nd">Sin valores</span>';
        }

        function cnConteoSem(r, sem) {
            const o = { total: 0, aprobado: 0, enviado: 0, listo: 0, pendiente: 0, iguales: 0 };
            r.facultades.forEach(function(f) {
                const c = f.sem[sem];
                if (!c.aplica) return;
                const e = cnEstadoCelda(c);
                if (e === 'vacio') return;
                o.total++; o[e]++;
                if (e === 'enviado' && Math.abs((cnNum(c.vf) || 0) - (cnNum(c.vv) || 0)) < 0.01) o.iguales++;
            });
            return o;
        }

        function cnCabSemHtml(r, sem) {
            const n = cnConteoSem(r, sem);
            return '<h6>Semestre ' + sem + '</h6><span class="cnx-sem-cuenta">' + (n.total ? n.aprobado + ' de ' + n.total + ' aprobados' + (n.enviado ? ' · ' + n.enviado + ' por aprobar' : '') : 'Sin valores todavía') + '</span>' +
                   (r.es_superadmin && n.iguales > 1 ? '<button type="button" class="cnx-btn verde" data-acc="aprobar-todo" data-sem="' + sem + '">Aprobar los ' + n.iguales + ' que coinciden</button>' : '');
        }

        // Riel de facultades (con el estado de cada semestre) y, a la derecha, el debate de la facultad elegida
        const CN_TXT = { vacio: 'Sin valores', pendiente: 'Falta un valor', listo: 'Para concertar', enviado: 'Por aprobar', aprobado: 'Concertado' };
        let cnSel = 0;
        function cnNombreCorto(n) { return String(n).replace(/^Facultad de (las? )?/i, '').replace(/^Facultad /i, ''); }

        function cnRailHtml(r) {
            const unidad = r.indicador.unidad;
            let h = '<h3>Facultades · ' + r.facultades.length + '</h3>';
            r.facultades.forEach(function(f) {
                const dots = [1, 2].map(function(s) {
                    const c = f.sem[s];
                    if (!c.aplica) return '<span class="cnv-dot nd" title="Semestre ' + s + ': no aplica">S' + s + '</span>';
                    const e = cnEstadoCelda(c);
                    return '<span class="cnv-dot ' + e + '" title="Semestre ' + s + ': ' + CN_TXT[e] + '">S' + s + '</span>';
                }).join('');
                h += '<button type="button" class="cnv-fi" data-fac="' + f.facultad_id + '" aria-current="' + (f.facultad_id == cnSel) + '">' +
                     '<span class="n">' + evalHtmlEscape(cnNombreCorto(f.nombre)) + '</span><span class="cnv-dots">' + dots + '</span>' +
                     '<small>' + (f.propuesta > 0 ? 'Meta ' + cnFmt(f.propuesta, unidad) : 'Sin meta') + '</small></button>';
            });
            if (r.es_superadmin) {
                [1, 2].forEach(function(s) {
                    const n = cnConteoSem(r, s);
                    if (n.iguales > 1) h += '<button type="button" class="cnx-btn verde" data-acc="aprobar-todo" data-sem="' + s + '">Semestre ' + s + ': aprobar los ' + n.iguales + ' que coinciden</button>';
                });
            }
            return h;
        }

        function cnPanelHtml(r, f, sem) {
            const c = f.sem[sem], unidad = r.indicador.unidad;
            if (!c.aplica) return '<article class="cnv-sem"><header><h4>Semestre ' + sem + '</h4></header><div class="cnx-nd cnv-na">No aplica en este indicador</div></article>';
            const e = cnEstadoCelda(c);
            return '<article class="cnv-sem" data-sem="' + sem + '"><header><h4>Semestre ' + sem + '</h4><span class="cnv-pill ' + e + '">' + CN_TXT[e] + '</span></header>' +
                '<div class="cnv-duelo">' +
                    '<div class="cnv-lado"><span class="cnv-t">Facultad</span>' + cnLadoHtml(f, sem, c, 'facultad', unidad) + '</div>' +
                    '<div class="cnv-lado"><span class="cnv-t">Vicerrectoría</span>' + cnLadoHtml(f, sem, c, 'vicerrectoria', unidad) + '</div>' +
                '</div>' +
                '<div class="cnx-res" data-fac="' + f.facultad_id + '" data-sem="' + sem + '">' + cnResultadoHtml(f, sem, c, r.es_superadmin, unidad) + '</div></article>';
        }

        function cnDetalleHtml(r, f) {
            return '<div class="cnv-dh"><h3>' + evalHtmlEscape(f.nombre) + '</h3><span>' + (f.propuesta > 0 ? 'Meta propuesta ' + cnFmt(f.propuesta, r.indicador.unidad) : 'Sin meta propuesta') + '</span></div>' +
                   '<div class="cnv-sems">' + cnPanelHtml(r, f, 1) + cnPanelHtml(r, f, 2) + '</div>';
        }

        function cnTablaUnica(r) {
            if (!r.facultades.length) return '<div class="cnx-nd cnx-sem-vacio">Ninguna facultad tiene meta ni seguimiento en este indicador.</div>';
            if (!r.facultades.some(function(f) { return f.facultad_id == cnSel; })) cnSel = r.facultades[0].facultad_id;
            const f = r.facultades.filter(function(x) { return x.facultad_id == cnSel; })[0];
            const conRiel = r.facultades.length > 1;
            return '<div class="cnv-body' + (conRiel ? '' : ' solo') + '">' +
                (conRiel ? '<aside class="cnv-rail" id="cnRail">' + cnRailHtml(r) + '</aside>' : '') +
                '<section class="cnv-det" id="cnDet">' + cnDetalleHtml(r, f) + '</section></div>';
        }

        // Tras guardar un valor: actualiza el estado y la acción del panel y el riel, sin tocar los campos
        function cnRefrescarPanel(f, sem) {
            const c = f.sem[sem], e = cnEstadoCelda(c);
            const p = $('.cnv-sem[data-sem="' + sem + '"]');
            p.find('.cnv-pill').attr('class', 'cnv-pill ' + e).text(CN_TXT[e]);
            p.find('.cnx-res').html(cnResultadoHtml(f, sem, c, cnDatos.es_superadmin, cnDatos.indicador.unidad));
            $('#cnRail').html(cnRailHtml(cnDatos));
        }
        $(document).on('click', '#cnSemestres .cnv-fi', function() {
            cnSel = parseInt($(this).data('fac'), 10);
            const f = cnDatos.facultades.filter(function(x) { return x.facultad_id == cnSel; })[0];
            if (!f) return;
            $('#cnDet').html(cnDetalleHtml(cnDatos, f));
            $('#cnRail').html(cnRailHtml(cnDatos));
        });

        function cnActualizarLista(r) {
            const t = [0, 0, 0], ap = [0, 0, 0];
            let env = 0, listo = 0, pend = 0;
            [1, 2].forEach(function(s) {
                const n = cnConteoSem(r, s);
                t[s] = n.total; ap[s] = n.aprobado; env += n.enviado; listo += n.listo; pend += n.pendiente;
            });
            let estado = 'sinreporte';
            if (t[1] + t[2] > 0) estado = env ? 'enviado' : (listo ? 'listo' : (pend ? 'pendiente' : 'aprobado'));
            const etiquetas = { pendiente: ['Falta un valor', 'nd'], listo: ['Para concertar', 'no'], enviado: ['Por aprobar', 'env'], aprobado: ['Aprobado', 'si'], sinreporte: ['Sin datos', 'nd'] };
            const item = document.querySelector('#cnVistaLista .cnx-item[data-id="' + r.indicador.id + '"]');
            if (item) {
                [1, 2].forEach(function(s) {
                    const p = item.querySelector('.cnx-prog[data-sem="' + s + '"]');
                    if (!p) return;
                    p.querySelector('b').textContent = t[s] ? ap[s] + '/' + t[s] : '—';
                    p.querySelector('i').style.width = (t[s] ? Math.round(ap[s] / t[s] * 100) : 0) + '%';
                    p.classList.toggle('apr', t[s] > 0 && ap[s] >= t[s]);
                });
                item.setAttribute('data-estado', estado);
                const pill = item.querySelector('.cnx-pill');
                if (pill) { pill.textContent = etiquetas[estado][0]; pill.className = 'cnx-pill ' + etiquetas[estado][1]; }
            }
            const fm = document.querySelector('#cnTabla tr.cn-fila[data-id="' + r.indicador.id + '"]');
            if (fm) {
                fm.setAttribute('data-estado', estado);
                const a = fm.querySelector('.cn-s1'), b = fm.querySelector('.cn-s2');
                if (a) a.textContent = ap[1] + '/' + t[1];
                if (b) b.textContent = ap[2] + '/' + t[2];
                const cel = fm.querySelector('.cn-coinciden');
                if (cel) { const ok = estado === 'aprobado'; cel.className = 'cn-coinciden ' + (ok ? 'si' : 'nd'); cel.textContent = ok ? 'SÍ' : '—'; }
            }
            if (window.cnActualizarChips) window.cnActualizarChips();
        }

        function cnPintar(r) {
            cnDatos = r;
            const unidad = r.indicador.unidad;
            const dep = r.dependencia || {};
            const nomDep = dep.nombre || 'la vicerrectoría';
            $('#cnTituloModal').text('Concertación con ' + nomDep);
            $('#cnIndicador').text(r.indicador.nombre);
            $('#cnMeta').html(
                (r.indicador.lmp ? '<span class="cn-lmp">' + evalHtmlEscape(r.indicador.lmp) + '</span>' : '') +
                (r.indicador.tipo ? '<span class="cnx-dato">' + evalHtmlEscape(r.indicador.tipo) + '</span>' : '') +
                (r.ver_todas && r.indicador.meta !== null && r.indicador.meta !== undefined && r.indicador.meta !== '' ? '<span class="cnx-dato">Meta general <b>' + cnFmt(r.indicador.meta, unidad) + '</b></span>' : '') +
                (r.ver_todas ? '<span class="cnx-dato">' + evalHtmlEscape(nomDep) + ' reporta: Sem 1 <b>' + cnFmt(dep.s1, unidad) + '</b> · Sem 2 <b>' + cnFmt(dep.s2, unidad) + '</b></span>' : ''));
            $('#cnResumenDep').empty();
            $('#cnAyuda').text(r.ver_todas
                ? 'La facultad y la vicerrectoría colocan cada una su valor para debatir cuál es la meta real. Si coinciden, al concertar pasa al seguimiento; si difieren, decide el superadministrador.' + (r.puede_concertar ? ' Escribe tu valor en tu columna; se guarda al salir del campo.' : '')
                : 'Escribe el valor de tu facultad y su descripción; se guarda al salir del campo. Cuando la vicerrectoría coloque el suyo podrás concertar.');
            $('#cnSemestres').html(cnTablaUnica(r));

            const pos = cnOrden.indexOf(cnIndicadorActual);
            $('#cnPos').text(pos >= 0 ? (pos + 1) + ' de ' + cnOrden.length : '');
            $('#cnPrev').prop('disabled', pos <= 0);
            $('#cnNext').prop('disabled', pos < 0 || pos >= cnOrden.length - 1);
            cnActualizarLista(r);
        }

        function cnCargar() {
            $.getJSON(basePath + '/modulo144/getConcertacion', { formulacion_id: cnIndicadorActual }, function(r) {
                if (!r.success) { $('#cnError').text(r.message || 'No se pudo cargar la concertación').show(); return; }
                $('#cnError').hide();
                cnPintar(r);
            }).fail(function() { $('#cnError').text('Error de conexión con el servidor').show(); });
        }

        function cnIdsVisibles() {
            return Array.from(document.querySelectorAll('#cnVistaLista:not([hidden]) .cnx-item, #cnVistaMatriz:not([hidden]) tr.cn-fila'))
                .filter(function(el) { return el.style.display !== 'none'; })
                .map(function(el) { return parseInt(el.getAttribute('data-id'), 10); });
        }

        function abrirConcertacion(id) {
            cnIndicadorActual = parseInt(id, 10) || 0;
            if (!cnIndicadorActual) return;
            cnOrden = cnIdsVisibles();
            $('#cnSemestres').html('<div class="cnx-nd cnx-sem-vacio">Cargando…</div>');
            $('#cnError').hide();
            $('#modalConcertacion').modal('show');
            cnCargar();
        }

        function cnIr(delta) {
            const pos = cnOrden.indexOf(cnIndicadorActual) + delta;
            if (pos < 0 || pos >= cnOrden.length) return;
            cnIndicadorActual = cnOrden[pos];
            cnCargar();
        }
        $(document).on('click', '#cnPrev', function() { cnIr(-1); });
        $(document).on('click', '#cnNext', function() { cnIr(1); });

        // Guarda el valor de un lado al salir del campo (Enter o clic fuera) y refresca solo esa fila
        $(document).on('change', '#cnSemestres .cnx-in, #cnSemestres .cnx-txt', function() {
            const el = $(this);
            const fac = el.data('fac'), sem = el.data('sem'), lado = el.data('lado');
            const texto = el.val();
            const esDescripcion = el.hasClass('cnx-txt');
            const datos = { formulacion_id: cnIndicadorActual, facultad_id: fac, semestre: sem, lado: lado };
            datos[esDescripcion ? 'descripcion' : 'valor'] = texto;
            el.prop('disabled', true);
            $.post(basePath + '/modulo144/guardarConcertacion', datos, function(r) {
                el.prop('disabled', false);
                if (!r.success) { $('#cnError').text(r.message || 'No se pudo guardar').show(); cnCargar(); return; }
                $('#cnError').hide();
                const f = cnDatos.facultades.filter(function(x) { return x.facultad_id == fac; })[0];
                const c = f.sem[sem], n = cnNum(texto);
                if (esDescripcion) { if (lado === 'facultad') { c.df = texto; c.df_propia = true; } else { c.dv = texto; } return; }
                if (lado === 'facultad') { c.vf = n === null ? c.hecha : n; c.vf_propio = n !== null; } else { c.vv = n; }
                c.enviado = false; c.rechazado = false; // cambiar un valor obliga a concertar de nuevo
                cnRefrescarPanel(f, sem);
                cnActualizarLista(cnDatos);
            }, 'json').fail(function() { el.prop('disabled', false); $('#cnError').text('Error de conexión con el servidor').show(); });
        });

        // Concertar (envía al superadministrador) y decisiones del superadministrador (aprobar, rechazar, quitar)
        $(document).on('click', '#cnSemestres [data-acc]', function() {
            const btn = $(this);
            const acc = btn.data('acc');
            const fallo = function(m) { $('#cnError').text(m || 'No se pudo guardar').show(); btn.prop('disabled', false); };
            const post = function(ruta, datos) {
                return $.post(basePath + '/modulo144/' + ruta, $.extend({ formulacion_id: cnIndicadorActual }, datos), null, 'json');
            };
            btn.prop('disabled', true);
            if (acc === 'aprobar-todo') {
                const sem = btn.data('sem');
                const pend = cnDatos.facultades.filter(function(f) {
                    const c = f.sem[sem];
                    return c.aplica && cnEstadoCelda(c) === 'enviado' && Math.abs((cnNum(c.vf) || 0) - (cnNum(c.vv) || 0)) < 0.01;
                });
                (function siguiente(i) {
                    if (i >= pend.length) { cnCargar(); return; }
                    post('aprobarConcertacion', { facultad_id: pend[i].facultad_id, semestre: sem, aprobado: 1 }).done(function(r) { if (!r.success) { fallo(r.message); return; } siguiente(i + 1); })
                        .fail(function() { fallo('Error de conexión con el servidor'); });
                })(0);
                return;
            }
            const fac = btn.data('fac'), sem = btn.data('sem');
            let req;
            if (acc === 'enviar') req = post('enviarConcertacion', { facultad_id: fac, semestre: sem });
            else if (acc === 'aprobar') req = post('aprobarConcertacion', { facultad_id: fac, semestre: sem, aprobado: 1, elige: btn.data('elige') || '' });
            else if (acc === 'rechazar') req = post('aprobarConcertacion', { facultad_id: fac, semestre: sem, rechazar: 1 });
            else if (acc === 'quitar') req = post('aprobarConcertacion', { facultad_id: fac, semestre: sem, aprobado: 0 });
            else { btn.prop('disabled', false); return; }
            req.done(function(r) {
                if (!r.success) { fallo(r.message); return; }
                $('#cnError').hide();
                cnCargar();
            }).fail(function() { fallo('Error de conexión con el servidor'); });
        });

        $(document).on('click', '#cnSemestres .cnx-det', function() {
            $(this).closest('.cnx-ur').find('.cnx-gdesc').prop('hidden', function(_, h) { return !h; });
        });

        /* ===== FILTRO DE LISTA ===== */

        function onFiltroTipoChange(modulo) {
            const tipo  = document.getElementById('filtroTipo-' + modulo).value;
            const wrap  = document.getElementById('filtroInputWrap-' + modulo);
            const input = document.getElementById('filtroTexto-' + modulo);
            if (tipo === 'dependencia' || tipo === 'persona' || tipo === 'nombre') {
                wrap.style.display = 'block';
                input.placeholder  = tipo === 'persona' ? 'Buscar persona...'
                                   : tipo === 'nombre'  ? 'Buscar por indicador...'
                                   : 'Buscar dependencia...';
                input.value = '';
                input.focus();
            } else {
                wrap.style.display = 'none';
            }
            aplicarFiltro(modulo);
            guardarFiltroPreferencia(modulo);
        }

        function onFiltroTextoInput(modulo) {
            aplicarFiltro(modulo);
            mostrarSugerencias(modulo);
            guardarFiltroPreferencia(modulo);
        }

        // Minúsculas y sin tildes, para que "Dirección" coincida con "direccion"
        function normFiltro(s) {
            return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
        }

        function aplicarFiltro(modulo) {
            const tipo     = document.getElementById('filtroTipo-' + modulo)?.value || 'todos';
            const texto    = normFiltro(document.getElementById('filtroTexto-' + modulo)?.value || '');
            const linea    = document.getElementById('filtroLinea-' + modulo)?.value || '';
            const motor    = document.getElementById('filtroMotor-' + modulo)?.value || '';
            const proyecto = document.getElementById('filtroProyecto-' + modulo)?.value || '';
            const items    = document.querySelectorAll(`.lista-item[data-modulo="${modulo}"]`);
            let visibles   = 0;

            items.forEach(function(item) {
                let visible = true;
                if (tipo === 'mio') {
                    visible = item.getAttribute('data-creado-por') == FILTER_USUARIO_ID;
                } else if (tipo === 'dependencia' && texto) {
                    // Dependencia responsable del indicador (o la del creador)
                    visible = normFiltro(item.getAttribute('data-responsable')).includes(texto)
                        || normFiltro(item.getAttribute('data-cargo-nombre')).includes(texto);
                } else if (tipo === 'persona' && texto) {
                    visible = normFiltro(item.getAttribute('data-creado-por-nombre')).includes(texto);
                } else if (tipo === 'nombre' && texto) {
                    visible = normFiltro(item.getAttribute('data-nombre-borrador')).includes(texto);
                } else if (tipo === 'con_seguimiento') {
                    visible = item.getAttribute('data-tiene-seguimiento') === '1';
                } else if (tipo === 'sin_seguimiento') {
                    visible = item.getAttribute('data-tiene-seguimiento') === '0';
                }
                if (visible && linea) {
                    visible = (item.getAttribute('data-linea-filtro') || '') === linea;
                }
                if (visible && motor) {
                    visible = (item.getAttribute('data-motor-filtro') || '') === motor;
                }
                if (visible && proyecto) {
                    visible = (item.getAttribute('data-proyecto-filtro') || '') === proyecto;
                }
                item.style.display = visible ? '' : 'none';
                if (visible) visibles++;
            });

            const badge     = document.getElementById('filtroResultado-' + modulo);
            const anyActive = tipo !== 'todos' || linea !== '' || motor !== '' || proyecto !== '';
            if (badge) {
                badge.style.display = anyActive ? '' : 'none';
                badge.textContent   = visibles + ' de ' + items.length;
            }
        }

        function poblarSelectsFiltro(modulo) {
            const items = document.querySelectorAll(`.lista-item[data-modulo="${modulo}"]`);
            const lineas = new Set();
            const motoresPorLinea = {};
            const proyectosPorMotor = {};
            const lineaCodigos = {};
            const motorCodigos = {};
            const proyectoCodigos = {};

            items.forEach(function(item) {
                const lin = item.getAttribute('data-linea-filtro') || '';
                const mot = item.getAttribute('data-motor-filtro') || '';
                const pro = item.getAttribute('data-proyecto-filtro') || '';
                const linCod = item.getAttribute('data-linea-codigo') || '';
                const motCod = item.getAttribute('data-motor-codigo') || '';
                const proCod = item.getAttribute('data-proyecto-codigo') || '';
                if (lin) {
                    lineas.add(lin);
                    if (linCod) lineaCodigos[lin] = linCod;
                    if (!motoresPorLinea[lin]) motoresPorLinea[lin] = new Set();
                    if (mot) {
                        motoresPorLinea[lin].add(mot);
                        if (motCod) motorCodigos[mot] = motCod;
                        const pkey = lin + '||' + mot;
                        if (!proyectosPorMotor[pkey]) proyectosPorMotor[pkey] = new Set();
                        if (pro) {
                            proyectosPorMotor[pkey].add(pro);
                            if (proCod) proyectoCodigos[pro] = proCod;
                        }
                    }
                }
            });

            const bar = document.getElementById('filtroBar-' + modulo);
            if (bar) {
                bar._motoresPorLinea    = motoresPorLinea;
                bar._proyectosPorMotor  = proyectosPorMotor;
                bar._lineaCodigos       = lineaCodigos;
                bar._motorCodigos       = motorCodigos;
                bar._proyectoCodigos    = proyectoCodigos;
            }

            const selLinea = document.getElementById('filtroLinea-' + modulo);
            if (!selLinea) return;

            selLinea.innerHTML = '<option value="">Línea: Todas</option>';
            [...lineas].sort().forEach(function(lin) {
                const opt = document.createElement('option');
                opt.value = lin;
                const cod = lineaCodigos[lin];
                opt.textContent = cod ? (cod + ' - ' + lin) : lin;
                selLinea.appendChild(opt);
            });
            selLinea.style.display = lineas.size > 0 ? '' : 'none';
            restaurarFiltrosLineamiento(modulo);
            aplicarFiltro(modulo);
        }

        function actualizarMotores(modulo) {
            const bar        = document.getElementById('filtroBar-' + modulo);
            const selLinea   = document.getElementById('filtroLinea-' + modulo);
            const selMotor   = document.getElementById('filtroMotor-' + modulo);
            const selProy    = document.getElementById('filtroProyecto-' + modulo);
            if (!bar || !selLinea || !selMotor || !selProy) return;

            const linea = selLinea.value;
            const motoresPorLinea = bar._motoresPorLinea || {};
            const motorCodigos = bar._motorCodigos || {};
            const motores = linea && motoresPorLinea[linea]
                ? [...motoresPorLinea[linea]].sort()
                : [];

            selMotor.innerHTML = '<option value="">Motor: Todos</option>';
            motores.forEach(function(mot) {
                const opt = document.createElement('option');
                opt.value = mot;
                const cod = motorCodigos[mot];
                opt.textContent = cod ? (cod + ' - ' + mot) : mot;
                selMotor.appendChild(opt);
            });
            selMotor.style.display = (linea && motores.length > 0) ? '' : 'none';
            selMotor.value = '';

            selProy.innerHTML = '<option value="">Proyecto: Todos</option>';
            selProy.style.display = 'none';
            selProy.value = '';
        }

        function actualizarProyectos(modulo) {
            const bar      = document.getElementById('filtroBar-' + modulo);
            const selLinea = document.getElementById('filtroLinea-' + modulo);
            const selMotor = document.getElementById('filtroMotor-' + modulo);
            const selProy  = document.getElementById('filtroProyecto-' + modulo);
            if (!bar || !selLinea || !selMotor || !selProy) return;

            const linea   = selLinea.value;
            const motor   = selMotor.value;
            const proyectosPorMotor = bar._proyectosPorMotor || {};
            const proyectoCodigos = bar._proyectoCodigos || {};
            const pkey    = linea + '||' + motor;
            const proyectos = (linea && motor && proyectosPorMotor[pkey])
                ? [...proyectosPorMotor[pkey]].sort()
                : [];

            selProy.innerHTML = '<option value="">Proyecto: Todos</option>';
            proyectos.forEach(function(pro) {
                const opt = document.createElement('option');
                opt.value = pro;
                const cod = proyectoCodigos[pro];
                opt.textContent = cod ? (cod + ' - ' + pro) : pro;
                selProy.appendChild(opt);
            });
            selProy.style.display = (linea && motor && proyectos.length > 0) ? '' : 'none';
            selProy.value = '';
        }

        function guardarFiltrosLineamiento(modulo) {
            const linea   = document.getElementById('filtroLinea-'    + modulo)?.value || '';
            const motor   = document.getElementById('filtroMotor-'    + modulo)?.value || '';
            const proyecto = document.getElementById('filtroProyecto-' + modulo)?.value || '';
            sessionStorage.setItem(
                'mod144_lineas_' + formularioId + '_' + modulo,
                JSON.stringify({ linea: linea, motor: motor, proyecto: proyecto })
            );
        }

        function restaurarFiltrosLineamiento(modulo) {
            const raw = sessionStorage.getItem('mod144_lineas_' + formularioId + '_' + modulo);
            if (!raw) return;
            let saved;
            try { saved = JSON.parse(raw); } catch(e) { return; }

            const selLinea = document.getElementById('filtroLinea-'    + modulo);
            const selMotor = document.getElementById('filtroMotor-'    + modulo);
            const selProy  = document.getElementById('filtroProyecto-' + modulo);
            if (!selLinea) return;

            if (saved.linea) {
                selLinea.value = saved.linea;
                // Rebuild motor options for this linea
                const bar = document.getElementById('filtroBar-' + modulo);
                const motoresPorLinea = bar ? (bar._motoresPorLinea || {}) : {};
                const motorCodigos = bar ? (bar._motorCodigos || {}) : {};
                const proyectoCodigos = bar ? (bar._proyectoCodigos || {}) : {};
                const motores = motoresPorLinea[saved.linea]
                    ? [...motoresPorLinea[saved.linea]].sort() : [];
                if (selMotor) {
                    selMotor.innerHTML = '<option value="">Motor: Todos</option>';
                    motores.forEach(function(mot) {
                        const opt = document.createElement('option');
                        opt.value = mot;
                        const cod = motorCodigos[mot];
                        opt.textContent = cod ? (cod + ' - ' + mot) : mot;
                        selMotor.appendChild(opt);
                    });
                    selMotor.style.display = motores.length > 0 ? '' : 'none';
                    if (saved.motor) {
                        selMotor.value = saved.motor;
                        // Rebuild proyecto options for this motor
                        const proyectosPorMotor = bar ? (bar._proyectosPorMotor || {}) : {};
                        const pkey = saved.linea + '||' + saved.motor;
                        const proyectos = proyectosPorMotor[pkey]
                            ? [...proyectosPorMotor[pkey]].sort() : [];
                        if (selProy) {
                            selProy.innerHTML = '<option value="">Proyecto: Todos</option>';
                            proyectos.forEach(function(pro) {
                                const opt = document.createElement('option');
                                opt.value = pro;
                                const cod = proyectoCodigos[pro];
                                opt.textContent = cod ? (cod + ' - ' + pro) : pro;
                                selProy.appendChild(opt);
                            });
                            selProy.style.display = (proyectos.length > 0) ? '' : 'none';
                            if (saved.proyecto) selProy.value = saved.proyecto;
                        }
                    }
                }
            }
        }

        function onFiltroLineaChange(modulo) {
            actualizarMotores(modulo);
            guardarFiltrosLineamiento(modulo);
            aplicarFiltro(modulo);
        }

        function onFiltroMotorChange(modulo) {
            actualizarProyectos(modulo);
            guardarFiltrosLineamiento(modulo);
            aplicarFiltro(modulo);
        }

        function onFiltroProyectoChange(modulo) {
            guardarFiltrosLineamiento(modulo);
            aplicarFiltro(modulo);
        }

        function mostrarSugerencias(modulo) {
            const tipo   = document.getElementById('filtroTipo-' + modulo)?.value;
            const texto  = (document.getElementById('filtroTexto-' + modulo)?.value || '').trim().toLowerCase();
            const box    = document.getElementById('filtroSugerencias-' + modulo);
            if (!box || (tipo !== 'dependencia' && tipo !== 'persona')) return;

            const fuente = tipo === 'dependencia'
                ? FILTER_CARGOS.map(function(c) { return c.nombre; })
                : FILTER_CREADORES.map(function(c) { return c.nombre; });

            const filtrados = texto ? fuente.filter(function(n) { return n.toLowerCase().includes(texto); }) : fuente;

            if (!filtrados.length) { box.style.display = 'none'; return; }

            box.innerHTML = filtrados.slice(0, 12).map(function(nombre) {
                const safe = nombre.replace(/'/g, "\\'");
                return `<div class="sug-item" onmousedown="seleccionarSugerencia('${modulo}','${safe}')">${nombre}</div>`;
            }).join('');
            box.style.display = 'block';
        }

        function ocultarSugerencias(modulo) {
            const box = document.getElementById('filtroSugerencias-' + modulo);
            if (box) box.style.display = 'none';
        }

        function seleccionarSugerencia(modulo, valor) {
            const input = document.getElementById('filtroTexto-' + modulo);
            if (input) input.value = valor;
            ocultarSugerencias(modulo);
            aplicarFiltro(modulo);
            guardarFiltroPreferencia(modulo);
        }

        const _filtroSaveTimeout = {};
        function guardarFiltroPreferencia(modulo) {
            clearTimeout(_filtroSaveTimeout[modulo]);
            _filtroSaveTimeout[modulo] = setTimeout(function() {
                const tipo  = document.getElementById('filtroTipo-' + modulo)?.value || 'todos';
                const texto = document.getElementById('filtroTexto-' + modulo)?.value || null;
                fetch(basePath + '/modulo144/saveFilterPreference', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        formulario_id: formularioId,
                        modulo:        modulo,
                        tipo_filtro:   tipo,
                        valor_filtro:  (tipo === 'dependencia' || tipo === 'persona') ? texto : null
                    })
                });
            }, 800);
        }

        // Aplicar filtros guardados al cargar
        document.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($datos_modulos as $key => $modulo): ?>
            (function() {
                poblarSelectsFiltro('<?php echo $key; ?>');
                const pref = FILTER_PREFS['<?php echo $key; ?>'];
                if (pref && pref.tipo_filtro && pref.tipo_filtro !== 'todos') {
                    aplicarFiltro('<?php echo $key; ?>');
                }
            })();
            <?php endforeach; ?>
        });

        // Enlace directo desde el Dashboard: ?id=FORM&ver=ITEM[&modulo=formulacion|seguimiento][&lista=publicados|borradores|cancelados]
        // Abre la pestaña correspondiente y lleva al indicador (o a la lista pedida) resaltándolo.
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(location.search);
            const itemId = params.get('ver');
            const modulo = params.get('modulo') || 'formulacion';
            const lista  = params.get('lista');
            if (!itemId && !lista && !params.get('modulo')) return;

            function mostrarPestana(destino) {
                const boton = document.querySelector('[data-bs-target="' + destino + '"]');
                if (!boton) return;
                if (window.bootstrap && bootstrap.Tab) bootstrap.Tab.getOrCreateInstance(boton).show();
                else boton.click();
            }

            // Se espera a que terminen de aplicarse los filtros guardados y el estado de los acordeones
            setTimeout(function() {
                mostrarPestana('#panelModulo-' + modulo);
                if (lista) {
                    const nombre = lista.charAt(0).toUpperCase() + lista.slice(1);
                    mostrarPestana('#tab' + nombre + '-' + modulo);
                }
                if (!itemId) return;

                const item = document.querySelector('.lista-item[data-item-id="' + CSS.escape(itemId) + '"][data-modulo="' + CSS.escape(modulo) + '"]');
                if (!item) {
                    Swal.fire({ icon: 'info', title: 'Indicador no disponible', text: 'No tienes acceso a este indicador o ya cambió de estado.', timer: 2600, showConfirmButton: false });
                    return;
                }
                const pane = item.closest('.tab-pane[id^="tab"]');
                if (pane) mostrarPestana('#' + pane.id);

                // Muestra el indicador aunque un filtro guardado lo esté ocultando, y abre los acordeones que lo contienen
                item.style.display = '';
                let padre = item.parentElement;
                while (padre) {
                    if (padre.classList && padre.classList.contains('collapse') && !padre.classList.contains('show')) {
                        if (window.bootstrap && bootstrap.Collapse) bootstrap.Collapse.getOrCreateInstance(padre, { toggle: false }).show();
                        else padre.classList.add('show');
                    }
                    padre = padre.parentElement;
                }

                setTimeout(function() {
                    item.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    item.style.transition = 'box-shadow .3s, background-color .3s';
                    item.style.boxShadow = '0 0 0 3px rgba(29,113,184,.55)';
                    item.style.backgroundColor = 'rgba(29,113,184,.06)';
                    setTimeout(function() { item.style.boxShadow = ''; item.style.backgroundColor = ''; }, 3500);
                }, 350);
            }, 400);
        });

    </script>
<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
