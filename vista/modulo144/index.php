<?php
// vista/modulo144/index.php
require_once __DIR__ . '/../../config/security.php';

$basePath = Config::getBasePath();
$baseUrl  = Config::getBaseUrl();

$fecha_cierre = null;
if (($formulario['tipo_tiempo'] ?? '') === 'rango' && !empty($formulario['fecha_cierre'])) {
    $fecha_cierre = $formulario['fecha_cierre'];
}

$titulo       = 'SISTEMA 144 — ' . htmlspecialchars($formulario['titulo'] ?? '');
$paginaActual = 'modulo144';

$eval_colores = ['#9C27B0', '#FF9500', '#007AFF', '#34C759', '#FF3B30', '#673AB7', '#FF6230', '#32ADE6', '#3F51B5', '#FF2D55'];
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
            box-shadow: 0 0 0 3px rgba(0,122,255,.15);
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
            --color-primary: #007AFF;
            --color-primary-light: #0A84FF;
            --color-success: #34C759;
            --color-warning: #FF9500;
            --color-danger: #FF3B30;
            --color-info: #007AFF;
            --color-bg: #F2F2F7;
            --color-white: #FFFFFF;
            --color-tab-incomplete: #8E8E93;
            --color-tab-complete: #34C759;
        }

        .facultad-color-0 { border-left-color: #9C27B0; }
        .facultad-color-1 { border-left-color: #FF9500; }
        .facultad-color-2 { border-left-color: #007AFF; }
        .facultad-color-3 { border-left-color: #34C759; }
        .facultad-color-4 { border-left-color: #FF3B30; }
        .facultad-color-5 { border-left-color: #673AB7; }
        .facultad-color-6 { border-left-color: #FF6230; }
        .facultad-color-7 { border-left-color: #32ADE6; }
        .facultad-color-8 { border-left-color: #3F51B5; }
        .facultad-color-9 { border-left-color: #FF2D55; }

        .badge-facultad-0 { background-color: #9C27B0; }
        .badge-facultad-1 { background-color: #FF9500; }
        .badge-facultad-2 { background-color: #007AFF; }
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
            background: linear-gradient(135deg, #007AFF 0%, #5856D6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(0,122,255,.32);
            color: white;
            font-size: 22px;
        }

        .header-eyebrow {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #007AFF;
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
            background: rgba(0,122,255,.04);
            border-radius: 12px;
            padding: 14px 18px;
            margin-top: 14px;
            border: 1px solid rgba(0,122,255,.12);
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
            color: #007AFF;
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
        .estado-sin-fechas { background: rgba(0,122,255,.1);    color: #007AFF; }
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
            box-shadow: 0 0 0 3px rgba(0,122,255,.15);
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
            background-color: rgba(0, 122, 255, 0.05);
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
            border-bottom-color: rgba(0,122,255,.4);
            background-color: rgba(0,122,255,.05);
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

        /* ═══ EVALUACIÓN LÍNEAS — TARJETAS CON SLIDER ═══ */
        .eval-linea-card {
            position: relative;
            border-radius: 16px;
            padding: 22px 46px;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 16px rgba(0,0,0,.12);
            overflow: hidden;
        }

        .eval-linea-badge {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.68rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            background: rgba(255,255,255,.22);
            letter-spacing: .4px;
        }

        .eval-nav {
            position: absolute;
            top: 10px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: rgba(255,255,255,.18);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .2s ease;
            font-size: 0.8rem;
            z-index: 2;
        }

        .eval-nav:hover {
            background: rgba(255,255,255,.35);
        }

        .eval-nav-left  { left: 10px; }
        .eval-nav-right { right: 10px; }

        .eval-linea-content {
            margin-top: 16px;
            width: 100%;
        }

        .eval-linea-nombre {
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.35;
            word-break: break-word;
            transition: color .2s ease;
        }

        .eval-linea-nombre.sin-seguimiento {
            color: #FF6B6B;
        }

        .eval-linea-slide-label {
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            opacity: .75;
            margin-bottom: 4px;
        }

        .eval-linea-dots {
            display: flex;
            gap: 5px;
            justify-content: center;
            margin-top: 14px;
        }

        .eval-linea-dots .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255,255,255,.35);
        }

        .eval-linea-dots .dot.active {
            background: white;
        }

        .eval-linea-empty {
            font-size: 0.78rem;
            opacity: .7;
            font-style: italic;
        }

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
            background-color: #007AFF;
            color: white;
            margin-left: 8px;
        }

        .seguimiento-indicador i {
            font-size: 0.6rem;
            margin-right: 3px;
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
        .filtro-sugerencias .sug-item:hover { background: rgba(0,122,255,0.08); }
        .filtro-resultado-badge {
            font-size: 0.75rem; padding: 2px 9px; border-radius: 10px;
            background: rgba(0,122,255,0.09); color: #007aff;
            border: 1px solid rgba(0,122,255,0.18);
        }
    </style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>
    <div class="container-fluid">
        <div class="header-info">
            <div style="display:flex;align-items:flex-start;gap:16px;">
                <!-- Icon -->
                <div class="header-icon-box">
                    <i class="fas fa-cubes"></i>
                </div>

                <!-- Text block -->
                <div style="flex:1;min-width:0;">
                    <div class="header-eyebrow">SISTEMA 144</div>
                    <h1 class="header-title"><?php echo htmlspecialchars($formulario['titulo'] ?? ''); ?></h1>
                    <p class="header-desc"><?php echo htmlspecialchars($formulario['descripcion'] ?? 'Sin descripción'); ?></p>
                    <?php if (!empty($formulario['fecha_inicio']) || !empty($formulario['fecha_cierre'])): ?>
                    <div class="header-meta">
                        <?php if (!empty($formulario['fecha_inicio'])): ?>
                        <span><i class="fas fa-play-circle me-1" style="color:#34C759;"></i>Inicio: <?php echo date('d/m/Y H:i', strtotime($formulario['fecha_inicio'])); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($formulario['fecha_cierre'])): ?>
                        <span><i class="fas fa-stop-circle me-1" style="color:#FF3B30;"></i>Cierre: <?php echo date('d/m/Y H:i', strtotime($formulario['fecha_cierre'])); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right: status + back -->
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0;">
                    <span class="estado-badge estado-<?php echo $estado_fechas['clase']; ?>">
                        <i class="fas fa-<?php echo $estado_fechas['valido'] ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $estado_fechas['mensaje']; ?>
                    </span>
                    <a href="<?php echo $basePath; ?>/FOR-DE-144"
                       style="display:inline-flex;align-items:center;gap:6px;background:#F2F2F7;color:#1d1d1f;padding:7px 14px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:none;transition:background .15s;">
                        <i class="fas fa-arrow-left" style="font-size:11px;"></i>Volver
                    </a>
                </div>
            </div>

            <?php if ($estado_fechas['valido'] && $fecha_cierre): ?>
            <div class="countdown-container">
                <div style="text-align:center;font-size:12px;font-weight:600;color:#6e6e73;margin-bottom:10px;text-transform:uppercase;letter-spacing:.4px;">
                    <i class="fas fa-hourglass-half me-1" style="color:#007AFF;"></i>Tiempo restante
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

        <!-- Pestañas principales -->
        <ul class="nav nav-tabs modulo-tabs-top mb-4" id="moduloTabsTop" role="tablist">
            <?php $primer_modulo_tab = true; ?>
            <?php foreach ($datos_modulos as $key => $modulo): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $primer_modulo_tab ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#panelModulo-<?php echo $key; ?>" type="button" role="tab">
                    <i class="fas <?php echo $modulo['config']['icono']; ?> me-2"></i><?php echo $modulo['config']['nombre']; ?>
                    <span class="badge bg-secondary ms-2">B:<?php echo count($modulo['borradores']); ?> P:<?php echo count($modulo['publicados']); ?> C:<?php echo count($modulo['cancelados']); ?></span>
                </button>
            </li>
            <?php $primer_modulo_tab = false; ?>
            <?php endforeach; ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#panelFacultades" type="button" role="tab">
                    <i class="fas fa-university me-2"></i>FORMULACIÓN Y SEGUIMIENTO POR FACULTADES
                    <span class="badge bg-secondary ms-2"><?php echo count($facultades ?? []); ?> facultades</span>
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
            <div class="tab-pane fade <?php echo $primer_modulo ? 'show active' : ''; ?>" id="panelModulo-<?php echo $key; ?>" role="tabpanel">
                <div class="modulo-panel-header" style="background: <?php echo $modulo['config']['color_header']; ?>;">
                    <i class="fas <?php echo $modulo['config']['icono']; ?> fa-2x me-3"></i>
                    <div>
                        <span class="modulo-panel-title"><?php echo $modulo['config']['nombre']; ?></span><br>
                        <small><?php echo $modulo['config']['descripcion']; ?></small>
                    </div>
                </div>
                <div class="p-4">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0" style="color: <?php echo $modulo['config']['color']; ?>;">
                                <i class="fas <?php echo $modulo['config']['icono']; ?> me-2"></i>
                                Gestión de <?php echo $modulo['config']['nombre']; ?>
                            </h4>
                            <?php if ($key === 'formulacion'): ?>
                            <button class="btn btn-success" onclick="abrirModalNuevoBorrador('<?php echo $key; ?>')">
                                <i class="fas fa-plus me-1"></i>Nuevo Borrador
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- FILTRO DE LISTA -->
                        <?php $pref_key = $filter_preferences[$key] ?? ['tipo_filtro' => 'todos', 'valor_filtro' => null]; ?>
                        <div class="filtro-lista-bar" id="filtroBar-<?php echo $key; ?>">
                            <span class="filtro-label"><i class="fas fa-filter me-1"></i>Ver:</span>
                            <select class="filtro-tipo-select" id="filtroTipo-<?php echo $key; ?>"
                                    onchange="onFiltroTipoChange('<?php echo $key; ?>')">
                                <option value="todos"       <?php echo $pref_key['tipo_filtro']==='todos'       ? 'selected':''; ?>>Todos</option>
                                <option value="mio"         <?php echo $pref_key['tipo_filtro']==='mio'         ? 'selected':''; ?>>Solo mío</option>
                                <option value="dependencia" <?php echo $pref_key['tipo_filtro']==='dependencia' ? 'selected':''; ?>>Por dependencia</option>
                                <option value="persona"     <?php echo $pref_key['tipo_filtro']==='persona'     ? 'selected':''; ?>>Por persona</option>
                                <option value="nombre"      <?php echo $pref_key['tipo_filtro']==='nombre'      ? 'selected':''; ?>>Por nombre</option>
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
                                       placeholder="<?php echo $pref_key['tipo_filtro']==='persona' ? 'Buscar persona...' : ($pref_key['tipo_filtro']==='nombre' ? 'Buscar por nombre...' : 'Buscar dependencia...'); ?>"
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
                                    <i class="fas fa-pen-fancy me-1"></i>Borradores <span class="badge bg-secondary ms-1"><?php echo count($modulo['borradores']); ?></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPublicados-<?php echo $key; ?>" type="button" role="tab">
                                    <i class="fas fa-check-circle me-1"></i>Publicados <span class="badge bg-success ms-1"><?php echo count($modulo['publicados']); ?></span>
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
                            <?php if (count($modulo['borradores']) > 0): ?>
                                <div class="lista-container">
                                    <div class="lista-header">
                                        <div class="row">
                                            <div class="col-md-4">Nombre</div>
                                            <div class="col-md-2">L - M - P</div>
                                            <div class="col-md-2">Creado por</div>
                                            <div class="col-md-2">Fecha de creación</div>
                                            <div class="col-md-2">Acciones</div>
                                        </div>
                                    </div>
                                    <?php
                                    // Pre-calcular suma de ponderacion_actividades por L-M-P
                                    $pond_por_linea_b = [];
                                    foreach ($modulo['borradores'] as $_b) {
                                        $lk = ($_b['linea_codigo'] ?? '__') . '|' . ($_b['motor_id_num'] ?? '__') . '|' . ($_b['proyecto_codigo'] ?? '__');
                                        $pond_por_linea_b[$lk] = ($pond_por_linea_b[$lk] ?? 0) + (float)($_b['ponderacion_actividades'] ?? 0);
                                    }
                                    ?>
                                    <?php foreach ($modulo['borradores'] as $borrador):
                                        $l   = !empty($borrador['linea_codigo'])    ? $borrador['linea_codigo']    : null;
                                        $m   = !empty($borrador['motor_codigo'])    ? $borrador['motor_codigo']    : null;
                                        $p   = !empty($borrador['proyecto_codigo']) ? $borrador['proyecto_codigo'] : null;
                                        $lmp = array_filter([$l, $m, $p]);
                                        $lmp_key_b = ($l ?? '__') . '|' . ($borrador['motor_id_num'] ?? '__') . '|' . ($borrador['proyecto_codigo'] ?? '__');
                                        $suma_linea = $pond_por_linea_b[$lmp_key_b] ?? 0;
                                        $linea_completa = ($suma_linea >= 99.99 && $suma_linea <= 100.01);
                                        $linea_excedida = $suma_linea > 100.01;
                                        $tiene_seg_b = !empty($borrador['fecha_seguimiento']) || !empty($borrador['porcentaje_avance']) || !empty($borrador['indicador']);
                                    ?>
                                    <div class="lista-item" data-item-id="<?php echo $borrador['id']; ?>" data-ponderacion="<?php echo (float)($borrador['ponderacion_actividades'] ?? 0); ?>" data-linea-item="<?php echo htmlspecialchars($l ?? ''); ?>" data-motor-item="<?php echo htmlspecialchars($borrador['motor_id_num'] ?? ''); ?>" data-proyecto-item="<?php echo htmlspecialchars($p ?? ''); ?>" data-modulo="<?php echo $key; ?>" data-creado-por="<?php echo (int)($borrador['creado_por'] ?? 0); ?>" data-creado-por-nombre="<?php echo htmlspecialchars($borrador['creado_por_nombre'] ?? ''); ?>" data-cargo-id="<?php echo (int)($borrador['creado_por_cargo_id'] ?? 0); ?>" data-cargo-nombre="<?php echo htmlspecialchars($borrador['creado_por_cargo_nombre'] ?? ''); ?>" data-linea-filtro="<?php echo htmlspecialchars($borrador['linea_estrategica'] ?? ''); ?>" data-motor-filtro="<?php echo htmlspecialchars($borrador['motor_desarrollo'] ?? ''); ?>" data-proyecto-filtro="<?php echo htmlspecialchars($borrador['proyecto'] ?? ''); ?>" data-linea-codigo="<?php echo htmlspecialchars($l ?? ''); ?>" data-motor-codigo="<?php echo htmlspecialchars($m ?? ''); ?>" data-proyecto-codigo="<?php echo htmlspecialchars($p ?? ''); ?>" data-nombre-borrador="<?php echo htmlspecialchars(strtolower($borrador['nombre_borrador'] ?? '')); ?>" data-tiene-seguimiento="<?php echo $tiene_seg_b ? '1' : '0'; ?>">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-4">
                                                <div class="lista-item-titulo <?php echo $linea_completa ? 'titulo-linea-completa' : ($linea_excedida ? 'titulo-linea-excedida' : ''); ?>">
                                                    <?php echo htmlspecialchars($borrador['nombre_borrador']); ?>
                                                    <?php if ($borrador['gestionado_facultades'] == 1): ?>
                                                    <span class="gestionado-indicador"><i class="fas fa-check-circle"></i> Gestionado</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($borrador['fecha_seguimiento']) || !empty($borrador['porcentaje_avance']) || !empty($borrador['indicador'])): ?>
                                                    <span class="seguimiento-indicador"><i class="fas fa-check-circle"></i> Seguimiento</span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($borrador['anio'])): ?>
                                                <div class="lista-item-sub"><i class="fas fa-calendar me-1"></i> Año: <?php echo $borrador['anio']; ?></div>
                                                <?php endif; ?>
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
                                                <?php if (!empty($borrador['creado_por_nombre'])): ?>
                                                <span class="lista-item-autor"><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($borrador['creado_por_nombre']); ?></span>
                                                <?php else: ?>
                                                <span class="lista-item-autor sin-datos">—</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="lista-item-fecha">
                                                    <i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y H:i', strtotime($borrador['fecha_creacion'])); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="lista-item-actions">
                                                    <?php if ($key === 'formulacion'): ?>
                                                    <button class="btn btn-sm btn-warning" onclick="editarBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button class="btn btn-sm btn-info" onclick="verSeguimiento('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if ($key === 'formulacion'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="cambiarEstadoBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 2)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="cambiarEstadoBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, 1)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button class="btn btn-sm btn-danger" onclick="eliminarBorrador('<?php echo $key; ?>', <?php echo $borrador['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-info" onclick="abrirModalDuplicar('<?php echo $key; ?>', <?php echo $borrador['id']; ?>, '<?php echo htmlspecialchars($borrador['nombre_borrador']); ?>')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
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
                                    <p class="text-muted small"><?php echo $key === 'formulacion' ? 'Haz clic en "Nuevo Borrador" para comenzar' : 'Los borradores de formulación aparecerán aquí'; ?></p>
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
                                            <div class="col-md-2">Creado por</div>
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
                                    <div class="lista-item" data-item-id="<?php echo $publicado['id']; ?>" data-ponderacion="<?php echo (float)($publicado['ponderacion_actividades'] ?? 0); ?>" data-linea-item="<?php echo htmlspecialchars($l ?? ''); ?>" data-motor-item="<?php echo htmlspecialchars($publicado['motor_id_num'] ?? ''); ?>" data-proyecto-item="<?php echo htmlspecialchars($p ?? ''); ?>" data-modulo="<?php echo $key; ?>" data-creado-por="<?php echo (int)($publicado['creado_por'] ?? 0); ?>" data-creado-por-nombre="<?php echo htmlspecialchars($publicado['creado_por_nombre'] ?? ''); ?>" data-cargo-id="<?php echo (int)($publicado['creado_por_cargo_id'] ?? 0); ?>" data-cargo-nombre="<?php echo htmlspecialchars($publicado['creado_por_cargo_nombre'] ?? ''); ?>" data-linea-filtro="<?php echo htmlspecialchars($publicado['linea_estrategica'] ?? ''); ?>" data-motor-filtro="<?php echo htmlspecialchars($publicado['motor_desarrollo'] ?? ''); ?>" data-proyecto-filtro="<?php echo htmlspecialchars($publicado['proyecto'] ?? ''); ?>" data-linea-codigo="<?php echo htmlspecialchars($l ?? ''); ?>" data-motor-codigo="<?php echo htmlspecialchars($m ?? ''); ?>" data-proyecto-codigo="<?php echo htmlspecialchars($p ?? ''); ?>" data-nombre-borrador="<?php echo htmlspecialchars(strtolower($publicado['nombre_borrador'] ?? '')); ?>" data-tiene-seguimiento="<?php echo $tiene_seg_p ? '1' : '0'; ?>">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-4">
                                                <div class="lista-item-titulo <?php echo $linea_completa ? 'titulo-linea-completa' : ($linea_excedida ? 'titulo-linea-excedida' : ''); ?>">
                                                    <?php echo htmlspecialchars($publicado['nombre_borrador']); ?>
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
                                                <?php if (!empty($publicado['creado_por_nombre'])): ?>
                                                <span class="lista-item-autor"><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($publicado['creado_por_nombre']); ?></span>
                                                <?php else: ?>
                                                <span class="lista-item-autor sin-datos">—</span>
                                                <?php endif; ?>
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
                                            <div class="col-md-2">Creado por</div>
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
                                    <div class="lista-item" data-item-id="<?php echo $cancelado['id']; ?>" data-ponderacion="<?php echo (float)($cancelado['ponderacion_actividades'] ?? 0); ?>" data-linea-item="<?php echo htmlspecialchars($l ?? ''); ?>" data-motor-item="<?php echo htmlspecialchars($cancelado['motor_id_num'] ?? ''); ?>" data-proyecto-item="<?php echo htmlspecialchars($p ?? ''); ?>" data-modulo="<?php echo $key; ?>" data-creado-por="<?php echo (int)($cancelado['creado_por'] ?? 0); ?>" data-creado-por-nombre="<?php echo htmlspecialchars($cancelado['creado_por_nombre'] ?? ''); ?>" data-cargo-id="<?php echo (int)($cancelado['creado_por_cargo_id'] ?? 0); ?>" data-cargo-nombre="<?php echo htmlspecialchars($cancelado['creado_por_cargo_nombre'] ?? ''); ?>" data-nombre-borrador="<?php echo htmlspecialchars(strtolower($cancelado['nombre_borrador'] ?? '')); ?>">
                                        <div class="row align-items-center g-2">
                                            <div class="col-md-4">
                                                <div class="lista-item-titulo <?php echo $linea_completa ? 'titulo-linea-completa' : ($linea_excedida ? 'titulo-linea-excedida' : ''); ?>">
                                                    <?php echo htmlspecialchars($cancelado['nombre_borrador']); ?>
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
                                                <?php if (!empty($cancelado['creado_por_nombre'])): ?>
                                                <span class="lista-item-autor"><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($cancelado['creado_por_nombre']); ?></span>
                                                <?php else: ?>
                                                <span class="lista-item-autor sin-datos">—</span>
                                                <?php endif; ?>
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

            <!-- FORMULACIÓN Y SEGUIMIENTO POR FACULTADES -->
            <div class="tab-pane fade" id="panelFacultades" role="tabpanel">
                <div class="modulo-panel-header" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);">
                    <i class="fas fa-university fa-2x me-3"></i>
                    <div>
                        <span class="modulo-panel-title">FORMULACIÓN Y SEGUIMIENTO POR FACULTADES</span><br>
                        <small>Solo formulaciones con gestión desde facultades activada</small>
                    </div>
                </div>
                <div class="p-4">

                        <?php
                        $formulaciones_con_check = [];
                        if (isset($datos_modulos['formulacion']['borradores'])) {
                            foreach ($datos_modulos['formulacion']['borradores'] as $borrador) {
                                if (isset($borrador['gestionado_facultades']) && $borrador['gestionado_facultades'] == 1) {
                                    $formulaciones_con_check[] = $borrador;
                                }
                            }
                        }
                        if (isset($datos_modulos['formulacion']['publicados'])) {
                            foreach ($datos_modulos['formulacion']['publicados'] as $publicado) {
                                if (isset($publicado['gestionado_facultades']) && $publicado['gestionado_facultades'] == 1) {
                                    $formulaciones_con_check[] = $publicado;
                                }
                            }
                        }
                        usort($formulaciones_con_check, function($a, $b) {
                            return strtotime($b['fecha_creacion']) - strtotime($a['fecha_creacion']);
                        });
                        ?>
                        <?php if (count($formulaciones_con_check) > 0): ?>
                            <?php foreach ($formulaciones_con_check as $borrador): ?>
                                <?php
                                $estadoClass = 'bg-secondary';
                                $estadoText = 'Borrador';

                                if ($borrador['estado_formulacion'] == 2) {
                                    $estadoClass = 'bg-success';
                                    $estadoText = 'Publicado';
                                } else if ($borrador['estado_formulacion'] == 1) {
                                    $estadoClass = 'bg-danger';
                                    $estadoText = 'Cancelado';
                                }

                                $fecha = isset($borrador['fecha_creacion']) ? date('d/m/Y H:i', strtotime($borrador['fecha_creacion'])) : '-';
                                $l = !empty($borrador['linea_codigo'])    ? $borrador['linea_codigo']    : null;
                                $m = !empty($borrador['motor_codigo'])    ? $borrador['motor_codigo']    : null;
                                $p = !empty($borrador['proyecto_codigo']) ? $borrador['proyecto_codigo'] : null;
                                $lmp = array_filter([$l, $m, $p]);
                                ?>
                                <div class="facultad-item" id="formulacion-item-<?php echo $borrador['id']; ?>">
                                    <div class="facultad-item-info">
                                        <h6>
                                            <?php echo htmlspecialchars($borrador['nombre_borrador'] ?? 'Sin nombre'); ?>
                                            <span class="gestionado-indicador"><i class="fas fa-check-circle"></i> Gestionado</span>
                                        </h6>
                                        <small>
                                            <i class="far fa-calendar-alt me-1"></i> <?php echo $fecha; ?>
                                            <?php if (!empty($borrador['anio'])): ?>
                                                | <i class="fas fa-calendar me-1"></i> Año: <?php echo $borrador['anio']; ?>
                                            <?php endif; ?>
                                        </small>
                                        <div class="mt-2 d-flex align-items-center flex-wrap gap-3">
                                            <?php if (!empty($lmp)): ?>
                                            <span class="lmp-badge">
                                                <i class="fas fa-sitemap"></i><?php echo implode(' - ', $lmp); ?>
                                                <small class="lmp-suma"><?php echo number_format((float)($borrador['ponderacion_actividades'] ?? 0),1); ?></small>
                                            </span>
                                            <?php endif; ?>
                                            <?php if (!empty($borrador['creado_por_nombre'])): ?>
                                            <span class="lista-item-autor"><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($borrador['creado_por_nombre']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-1">
                                            <span class="badge <?php echo $estadoClass; ?>"><?php echo $estadoText; ?></span>
                                        </div>
                                    </div>
                                    <div class="facultad-item-actions">
                                        <button class="btn btn-sm btn-warning" onclick="editarBorrador('formulacion', <?php echo $borrador['id']; ?>)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm" style="background-color: #FF9800; color: white;" onclick="abrirGestionSemestral(<?php echo $borrador['id']; ?>, '<?php echo htmlspecialchars($borrador['nombre_borrador']); ?>')" title="Gestión Semestral">
                                            <i class="fas fa-calendar-alt"></i>
                                        </button>
                                        <?php if ($borrador['estado_formulacion'] == 0): ?>
                                            <button class="btn btn-sm btn-success" onclick="cambiarEstadoBorrador('formulacion', <?php echo $borrador['id']; ?>, 2)" title="Publicar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($borrador['estado_formulacion'] == 2): ?>
                                            <button class="btn btn-sm btn-info" onclick="verBorrador('formulacion', <?php echo $borrador['id']; ?>)" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($borrador['estado_formulacion'] == 1): ?>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarBorrador('formulacion', <?php echo $borrador['id']; ?>)" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-info" onclick="abrirModalDuplicar('formulacion', <?php echo $borrador['id']; ?>, '<?php echo htmlspecialchars($borrador['nombre_borrador']); ?>')" title="Duplicar">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state p-3">
                                <i class="fas fa-file-alt fa-2x mb-2"></i>
                                <p class="text-muted mb-0">No hay formulaciones con gestión desde facultades activada</p>
                                <p class="text-muted small mb-0 mt-2">
                                    Para que aparezcan aquí, marca la opción:
                                    <br>
                                    <strong>"12. MARQUE: ✓ SI EL INDICADOR SERÁ GESTIONADO DESDE LAS FACULTADES"</strong>
                                </p>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            <!-- EVALUACIÓN LÍNEAS -->
            <div class="tab-pane fade" id="panelEvaluacion" role="tabpanel">
                <div class="modulo-panel-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-chart-pie fa-2x me-3"></i>
                    <div>
                        <span class="modulo-panel-title">EVALUACIÓN LÍNEAS</span><br>
                        <small>Evaluación de líneas estratégicas por proyecto</small>
                    </div>
                </div>
                <div class="p-4">
                        <?php if (!empty($lineas_estrategicas)): ?>
                        <div class="row g-3">
                            <?php foreach ($lineas_estrategicas as $li => $linea):
                                $proyectos_linea = [];
                                $filas_linea = array_merge(
                                    $datos_modulos['formulacion']['borradores'] ?? [],
                                    $datos_modulos['formulacion']['publicados'] ?? []
                                );
                                foreach ($filas_linea as $row) {
                                    if (($row['linea_codigo'] ?? null) !== $linea['codigo']) continue;
                                    $pnombre = trim($row['proyecto'] ?? '');
                                    if ($pnombre === '') continue;
                                    $tiene_seg = !empty($row['fecha_seguimiento']) || !empty($row['porcentaje_avance']) || !empty($row['indicador']);
                                    $proyectos_linea[$pnombre] = ($proyectos_linea[$pnombre] ?? false) || $tiene_seg;
                                }
                                $proyectos_json = [];
                                foreach ($proyectos_linea as $pnombre => $tieneSeg) {
                                    $proyectos_json[] = ['nombre' => $pnombre, 'seguimiento' => $tieneSeg];
                                }
                                $colorIdx  = $li % 10;
                                $colorBase = $eval_colores[$colorIdx];
                                $colorDark = eval_darken($colorBase, 0.28);
                            ?>
                            <div class="col-md-4">
                                <div class="eval-linea-card"
                                     style="background: linear-gradient(135deg, <?php echo $colorBase; ?> 0%, <?php echo $colorDark; ?> 100%);"
                                     data-idx="0"
                                     data-linea="<?php echo htmlspecialchars($linea['nombre']); ?>"
                                     data-proyectos='<?php echo htmlspecialchars(json_encode($proyectos_json), ENT_QUOTES); ?>'>
                                    <button type="button" class="eval-nav eval-nav-left" onclick="evalLineaNav(this,-1)" aria-label="Anterior">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="eval-nav eval-nav-right" onclick="evalLineaNav(this,1)" aria-label="Siguiente">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                    <div class="eval-linea-badge"><?php echo htmlspecialchars($linea['codigo']); ?></div>
                                    <div class="eval-linea-content">
                                        <div class="eval-linea-slide-label">Línea</div>
                                        <div class="eval-linea-nombre"><?php echo htmlspecialchars($linea['nombre']); ?></div>
                                    </div>
                                    <div class="eval-linea-dots"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="desarrollo-section">
                            <div class="desarrollo-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="desarrollo-title">SIN LÍNEAS ESTRATÉGICAS</div>
                            <div class="desarrollo-subtitle">
                                No hay líneas estratégicas registradas en el catálogo
                            </div>
                        </div>
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
                <div class="modal-header" style="background: #007AFF;">
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
                            <small class="text-muted">Se reemplazará automáticamente por el contenido de "13.2 Fórmula de la Medición" al diligenciarlo.</small>
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
                <div class="modal-header" style="background: #007AFF;">
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
                <div class="modal-header" style="background: #007AFF;">
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
                <div class="modal-header" style="background: #FF9500;">
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: #007AFF;">
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
                                <i class="fas fa-chart-line me-2"></i>INDICADOR DE RESULTADO
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
                                    <div class="col-md-12 mb-3">
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

                                    <div class="col-12 mb-3">
                                        <label class="form-label">2. OBJETIVO</label>
                                        <textarea class="form-control" name="objetivo" id="formulacion_objetivo" rows="3" readonly></textarea>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">3. ESTRATEGIA</label>
                                        <select class="form-select" name="estrategia" id="formulacion_estrategia" onchange="autoGuardarFormulacion(); validarPestanas()">
                                            <option value="">Seleccione una estrategia</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">4. MOTOR DE DESARROLLO</label>
                                        <select class="form-select" name="motor_desarrollo" id="formulacion_motor" onchange="cargarProyectosPorMotor(); autoGuardarFormulacion(); validarPestanas(); actualizarLmpBadgeEnLista();">
                                            <option value="">Seleccione un motor de desarrollo</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">5. PROYECTO</label>
                                        <select class="form-select" name="proyecto" id="formulacion_proyecto" onchange="calcularAcumuladoActividades(true); cargarPonderacionProyecto(); autoGuardarFormulacion(); validarPestanas(); actualizarLmpBadgeEnLista();">
                                            <option value="">Seleccione un proyecto</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">6. META DE RESULTADO</label>
                                        <textarea class="form-control" name="meta_resultado" id="formulacion_meta" rows="2" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa la meta de resultado..."></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">7. PONDERACIÓN DE LOS PROYECTOS</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="ponderacion_proyectos" id="formulacion_ponderacion_proyectos" step="0.01" min="0" max="100" readonly placeholder="0.00" style="background-color: #e9ecef; cursor: not-allowed;">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">8. ACTIVIDAD DEL PROYECTO (205)</label>
                                        <textarea class="form-control" name="actividad_proyecto" id="formulacion_actividad" rows="4" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa la actividad del proyecto..."></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">9. PONDERACIÓN DE LAS ACTIVIDADES POR PROYECTO</label>
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

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">10. RESPONSABLE</label>
                                        <select class="form-select" name="responsable_formulacion_multi[]" id="formulacion_responsable" multiple="multiple">
                                            <?php foreach ($cargos as $cargo): ?>
                                            <option value="<?php echo htmlspecialchars($cargo['nombre']); ?>">
                                                <?php echo htmlspecialchars($cargo['nombre']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="responsable_formulacion" id="formulacion_responsable_hidden">
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">11. ID INDICADOR</label>
                                        <select class="form-select" name="id_indicador" id="formulacion_id_indicador" onchange="autoGuardarFormulacion(); validarPestanas()">
                                            <option value="">Seleccione...</option>
                                            <option value="PDI">PDI</option>
                                            <option value="PA">PA</option>
                                            <option value="PR">PR</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="gestionado_facultades" id="formulacion_gestionado_facultades" value="1" onchange="gestionarCheckboxFacultades(this)">
                                            <label class="form-check-label" for="formulacion_gestionado_facultades">
                                                <strong>12. MARQUE: ✓ SI EL INDICADOR SERÁ GESTIONADO DESDE LAS FACULTADES</strong>
                                            </label>
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
                                    
                                    <div class="col-12 mb-3">
                                        <label class="form-label">13.1 NOMBRE DEL INDICADOR</label>
                                        <input type="text" class="form-control" name="nombre_indicador" id="formulacion_nombre_indicador" oninput="sincronizarNombreBorrador(); autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Ingrese el nombre del indicador">
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label class="form-label">13.2 FÓRMULA DE LA MEDICIÓN</label>
                                        <textarea class="form-control" name="formula_medicion" id="formulacion_formula_medicion" rows="3" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Ej: (Número de estudiantes graduados / Total de estudiantes matriculados) * 100"></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">13.3 FRECUENCIA DE MEDICIÓN</label>
                                            <select class="form-select" name="frecuencia_medicion" id="formulacion_frecuencia_medicion" onchange="autoGuardarFormulacion(); validarPestanas()">
                                                <option value="">Seleccione frecuencia</option>
                                                <option value="Mensual">Mensual</option>
                                                <option value="Bimestral">Bimestral</option>
                                                <option value="Trimestral">Trimestral</option>
                                                <option value="Semestral">Semestral</option>
                                                <option value="Anual">Anual</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">13.4 UNIDAD DE MEDIDA</label>
                                            <select class="form-select" name="unidad_medida" id="formulacion_unidad_medida" onchange="autoGuardarFormulacion(); validarPestanas()">
                                                <option value="">Seleccione unidad</option>
                                                <option value="Unidad">Unidad</option>
                                                <option value="Porcentaje">Porcentaje</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">13.5 AÑO</label>
                                            <select class="form-select" name="tipo_medicion" id="formulacion_tipo_medicion" onchange="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()">
                                                <option value="">Seleccione tipo</option>
                                                <option value="Acumulado">Acumulado</option>
                                                <option value="Nuevo gestionado durante la vigencia">Nuevo gestionado durante la vigencia</option>
                                                <option value="Promedio">Promedio</option>
                                                <option value="Último valor reportado">Último valor reportado</option>
                                                <option value="Límite">Límite</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label class="form-label">DESCRIPCIÓN DEL INDICADOR</label>
                                        <textarea class="form-control" name="descripcion_indicador" id="formulacion_descripcion_indicador" rows="4" oninput="autoGuardarFormulacion(); validarPestanas()" placeholder="Describa detalladamente el indicador..."></textarea>
                                    </div>
                                    
                                    <div class="col-12 mt-4">
                                        <div class="meta-section">
                                            <h5 class="meta-title">METAS PROPUESTAS</h5>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">14.1 LÍNEA BASE</label>
                                                    <input type="text" class="form-control" name="linea_base_meta" id="formulacion_linea_base_meta" oninput="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Valor de línea base">
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">14.4 VALOR AÑO</label>
                                                    <input type="text" class="form-control" name="anio_base_meta" id="formulacion_anio_base_meta" readonly placeholder="Valor anual" style="background-color: #e8f5e9; font-weight: bold; color: #2e7d32;">
                                                </div>
                                                
                                                <div class="col-12">
                                                    <h6 class="mb-3" style="color: var(--color-primary);">METAS ANUALES</h6>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">14.2 SEMESTRE 1</label>
                                                    <input type="text" class="form-control" name="meta_s1" id="formulacion_meta_s1" oninput="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Meta Semestre 1">
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">14.3 SEMESTRE 2</label>
                                                    <input type="text" class="form-control" name="meta_s2" id="formulacion_meta_s2" oninput="autoGuardarFormulacion(); validarPestanas(); calcularValorAnual()" placeholder="Meta Semestre 2">
                                                </div>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA SEGUIMIENTO -->
    <div class="modal fade" id="modalSeguimiento" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: #34C759;">
                    <h5 class="modal-title" id="tituloSeguimiento" ondblclick="editarTituloModal('seguimiento')">
                        <i class="fas fa-chart-line me-2"></i>SEGUIMIENTO 144 - <span id="tituloSeguimientoSpan"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSeguimiento">
                    <input type="hidden" name="modulo" value="seguimiento">
                    <input type="hidden" id="seguimiento_id" name="id">
                    
                    <div class="modal-body-scroll">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-success">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Información Previa:</strong> Esta información proviene del formulario de formulación y no es editable.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3"><label class="form-label text-muted">LÍNEA ESTRATÉGICA</label><div class="bg-light-view" id="seguimiento_linea_view">-</div></div>
                            <div class="col-md-4 mb-3"><label class="form-label text-muted">MOTOR DE DESARROLLO</label><div class="bg-light-view" id="seguimiento_motor_view">-</div></div>
                            <div class="col-md-4 mb-3"><label class="form-label text-muted">PROYECTO</label><div class="bg-light-view" id="seguimiento_proyecto_view">-</div></div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12"><label class="form-label text-muted">FÓRMULA DEL INDICADOR</label><div class="bg-light-view" id="seguimiento_formula_medicion_view">-</div></div>
                        </div>

                        <!-- Campos ocultos requeridos por el JS existente (autoguardado, etc.) -->
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
                            <div class="row">
                                <div class="col-md-3 mb-3 mb-md-0"><label class="form-label">SEGUIMIENTO SEMESTRE 1</label><input type="number" class="form-control" name="semestre1_seguimiento" id="seguimiento_semestre1" step="0.01" oninput="autoGuardarSeguimiento()"></div>
                                <div class="col-md-3 mb-3 mb-md-0"><label class="form-label">SEGUIMIENTO SEMESTRE 2</label><input type="number" class="form-control" name="semestre2_seguimiento" id="seguimiento_semestre2" step="0.01" oninput="autoGuardarSeguimiento()"></div>
                                <div class="col-md-3 mb-3 mb-md-0 ms-md-auto">
                                    <label class="form-label text-muted">14.4 VALOR AÑO</label>
                                    <div class="bg-light-view" id="seguimiento_anio_view" style="background-color:#e8f5e9; font-weight:bold; color:#2e7d32;">-</div>
                                    <label class="form-label text-muted mt-2 mb-0" style="font-size:0.75rem;">PORCENTAJE DE CUMPLIMIENTO</label>
                                </div>
                            </div>
                        </div>

                        <div class="indicador-section field-group">
                            <h5 class="indicador-title"><i class="fas fa-chart-line me-2"></i>ESTADO DE PROYECTOS</h5>
                            <div class="row">
                                <div class="col-md-3 mb-3 mb-md-0"><label class="form-label">LOGROS</label><textarea class="form-control" name="logros" id="seguimiento_logros" rows="3" oninput="autoGuardarSeguimiento()"></textarea></div>
                                <div class="col-md-3 mb-3 mb-md-0"><label class="form-label">LÍMITES</label><textarea class="form-control" name="limites" id="seguimiento_limites" rows="3" oninput="autoGuardarSeguimiento()"></textarea></div>
                                <div class="col-md-3 mb-3 mb-md-0"><label class="form-label">OBSERVACIÓN</label><textarea class="form-control" name="observacion_estado" id="seguimiento_observacion_estado" rows="3" oninput="autoGuardarSeguimiento()"></textarea></div>
                                <div class="col-md-3"><label class="form-label">ACCIONES DE FORTALECIMIENTO</label><textarea class="form-control" name="acciones_fortalecimiento" id="seguimiento_acciones_fortalecimiento" rows="3" oninput="autoGuardarSeguimiento()"></textarea></div>
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

    <div class="modal fade" id="modalVerFormulario" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: #007AFF;">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const basePath = '<?php echo $basePath; ?>';
        const formularioId = <?php echo $formulario['id']; ?>;
        const formularioAnio = <?php echo intval($formulario['anio'] ?? 0); ?>; // Año heredado del formulario padre

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

        function guardarEstadoAcordeon() {
            const abierto = document.querySelector('#moduloTabsTopContent .tab-pane.active');
            if (abierto) {
                sessionStorage.setItem('mod144_acordeon_' + formularioId, abierto.id);
            } else {
                sessionStorage.removeItem('mod144_acordeon_' + formularioId);
            }
        }

        function restaurarEstadoAcordeon() {
            const id = sessionStorage.getItem('mod144_acordeon_' + formularioId);
            if (!id) return;
            sessionStorage.removeItem('mod144_acordeon_' + formularioId);
            const btn = document.querySelector('[data-bs-target="#' + id + '"]');
            if (!btn) return;
            new bootstrap.Tab(btn).show();
        }

        $(document).ready(function() {
            restaurarEstadoAcordeon();
        });

        let timeoutId = null;
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

            $.ajax({
                url: basePath + '/modulo144/getPonderacionProyecto',
                type: 'GET',
                data: { proyecto_id: proyectoId, anio: anio },
                dataType: 'json',
                success: function(response) {
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
                                Swal.fire('Error', response.message || 'No se pudo guardar el cambio', 'error');
                            }
                        },
                        error: function() {
                            checkbox.checked = !estadoActual;
                            Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
                        }
                    });
                } else {
                    checkbox.checked = !estadoActual;
                }
            });
        }

        let nombreBorradorProvisional = 'Nuevo Borrador';

        function sincronizarNombreBorrador() {
            const nombreIndicador = $('#formulacion_nombre_indicador').val();
            const nombre = (nombreIndicador && nombreIndicador.trim() !== '') ? nombreIndicador.trim() : nombreBorradorProvisional;
            $('#tituloFormulacionSpan').text(nombre);
        }

        // ═══ EVALUACIÓN LÍNEAS — SLIDER DE TARJETAS ═══
        function evalHtmlEscape(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, function(c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function evalLineaProyectos(card) {
            try { return JSON.parse(card.getAttribute('data-proyectos') || '[]'); }
            catch (e) { return []; }
        }

        function evalLineaRenderDots(card, idx, total) {
            const dotsWrap = card.querySelector('.eval-linea-dots');
            if (!dotsWrap) return;
            let html = '';
            for (let i = 0; i < total; i++) {
                html += '<span class="dot' + (i === idx ? ' active' : '') + '"></span>';
            }
            dotsWrap.innerHTML = html;
        }

        function evalLineaRenderSlide(card, idx, proyectos) {
            const contenido = card.querySelector('.eval-linea-content');
            if (idx === 0) {
                contenido.innerHTML = '<div class="eval-linea-slide-label">Línea</div><div class="eval-linea-nombre">' + evalHtmlEscape(card.getAttribute('data-linea')) + '</div>';
            } else {
                const p = proyectos[idx - 1];
                if (p) {
                    const clase = p.seguimiento ? '' : ' sin-seguimiento';
                    contenido.innerHTML = '<div class="eval-linea-slide-label">Proyecto</div><div class="eval-linea-nombre' + clase + '">' + evalHtmlEscape(p.nombre) + '</div>';
                } else {
                    contenido.innerHTML = '<div class="eval-linea-slide-label">Proyecto</div><div class="eval-linea-empty">Sin proyectos registrados</div>';
                }
            }
            evalLineaRenderDots(card, idx, 1 + proyectos.length);
        }

        function evalLineaNav(btn, dir) {
            const card = btn.closest('.eval-linea-card');
            const proyectos = evalLineaProyectos(card);
            const total = 1 + proyectos.length;
            let idx = parseInt(card.getAttribute('data-idx') || '0', 10);
            idx = (idx + dir + total) % total;
            card.setAttribute('data-idx', idx);
            evalLineaRenderSlide(card, idx, proyectos);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.eval-linea-card').forEach(function(card) {
                const proyectos = evalLineaProyectos(card);
                evalLineaRenderDots(card, 0, 1 + proyectos.length);
            });
        });

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
            
            $('#seguimiento_anio_view').text(valorAnual);
            $('#seguimiento_linea_view').text(b.linea_estrategica ? (b.linea_codigo ? b.linea_codigo + ' - ' : '') + b.linea_estrategica : '-');
            $('#seguimiento_objetivo_view').text(b.objetivo || '-');
            $('#seguimiento_estrategia_view').text(b.estrategia || '-');
            $('#seguimiento_motor_view').text(b.motor_desarrollo ? (b.motor_codigo ? b.motor_codigo + ' - ' : '') + b.motor_desarrollo : '-');
            $('#seguimiento_meta_resultado_view').text(b.meta_resultado || '-');
            $('#seguimiento_proyecto_view').text(b.proyecto ? (b.proyecto_codigo ? b.proyecto_codigo + ' - ' : '') + b.proyecto : '-');
            $('#seguimiento_formula_medicion_view').text(b.formula_medicion || '-');
            $('#seguimiento_ponderacion_proyectos_view').text(b.ponderacion_proyectos ? b.ponderacion_proyectos + '%' : '-');
            $('#seguimiento_actividad_view').text(b.actividad_proyecto || '-');
            $('#seguimiento_ponderacion_actividades_view').text(b.ponderacion_actividades ? b.ponderacion_actividades + '%' : '-');
            $('#seguimiento_responsable_view').text(b.responsable_formulacion || '-');
        }

        function verSeguimiento(modulo, id) { editarBorrador(modulo, id); }

        function autoGuardarFormulacion() {
            const id = $('#formulacion_id').val();
            if (!id) return;
            
            if (timeoutId) clearTimeout(timeoutId);
            
            timeoutId = setTimeout(function() {
                const gestionado = $('#formulacion_gestionado_facultades').is(':checked') ? 1 : 0;
                
                const nombreIndicadorActual = $('#formulacion_nombre_indicador').val();
                const nombreBorradorActual = (nombreIndicadorActual && nombreIndicadorActual.trim() !== '') ? nombreIndicadorActual.trim() : nombreBorradorProvisional;

                const data = {
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
                
                $.ajax({
                    url: basePath + '/modulo144/guardar',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mostrarAutoSaveIndicator();
                            validarPestanas();
                            // Refrescar datos locales tras guardar exitoso
                            refrescarFormulacionesExistentes(function() { _ejecutarCalculoAcumulado(); });
                        } else if (response.acumulado !== undefined) {
                            // Error de ponderación server-side: actualizar badge y bloquear
                            formulacionesExistentes = formulacionesExistentes; // forzar recalculo
                            refrescarFormulacionesExistentes(function() { _ejecutarCalculoAcumulado(); });
                            Swal.fire({
                                icon: 'warning',
                                title: 'Ponderación excedida',
                                text: response.message,
                                confirmButtonColor: '#007AFF'
                            });
                        }
                    }
                });
            }, 500);
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
                    acciones_fortalecimiento: $('#seguimiento_acciones_fortalecimiento').val()
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
                            $('#formulacion_id').val(b.id);
                            $('#tituloFormulacionSpan').text(b.nombre_borrador);
                            nombreBorradorProvisional = (b.nombre_indicador && b.nombre_indicador.trim() !== '') ? 'Nuevo Borrador' : (b.nombre_borrador || 'Nuevo Borrador');
                            $('#formulacion_linea').val(b.linea_estrategica);
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
                                                    setTimeout(function() { cargarProyectosPorMotorConValor(b.proyecto); }, 300);
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
                            setTimeout(calcularAcumuladoActividades, 400);
                            
                            if (b.planes_institucionales) {
                                cargarPlanesDesdeBD(b.planes_institucionales);
                            } else {
                                planesSeleccionados = [];
                                actualizarContenedorPlanes();
                                actualizarCampoOculto();
                            }
                            
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
                            cargarDatosFormulacionEnSeguimiento(b);
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

        function cambiarEstadoBorrador(modulo, id, estado) {
            Swal.fire({
                title: estado === 2 ? '¿Publicar formulación?' : '¿Cancelar formulación?',
                text: estado === 2 ? 'Esta formulación pasará a estado PUBLICADO' : 'Esta formulación pasará a estado CANCELADO',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: estado === 2 ? '#27AE60' : '#E74C3C',
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
            $('#formulacion_nombre_indicador, #formulacion_formula_medicion, #formulacion_frecuencia_medicion, #formulacion_unidad_medida, #formulacion_tipo_medicion, #formulacion_descripcion_indicador, #formulacion_linea_base_meta, #formulacion_meta_s1, #formulacion_meta_s2, #formulacion_gestionado_facultades').on('input change', function() {
                calcularValorAnual();
            });
        });

        /* ===== FILTRO DE LISTA ===== */

        function onFiltroTipoChange(modulo) {
            const tipo  = document.getElementById('filtroTipo-' + modulo).value;
            const wrap  = document.getElementById('filtroInputWrap-' + modulo);
            const input = document.getElementById('filtroTexto-' + modulo);
            if (tipo === 'dependencia' || tipo === 'persona' || tipo === 'nombre') {
                wrap.style.display = 'block';
                input.placeholder  = tipo === 'persona' ? 'Buscar persona...'
                                   : tipo === 'nombre'  ? 'Buscar por nombre...'
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

        function aplicarFiltro(modulo) {
            const tipo     = document.getElementById('filtroTipo-' + modulo)?.value || 'todos';
            const texto    = (document.getElementById('filtroTexto-' + modulo)?.value || '').trim().toLowerCase();
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
                    visible = (item.getAttribute('data-cargo-nombre') || '').toLowerCase().includes(texto);
                } else if (tipo === 'persona' && texto) {
                    visible = (item.getAttribute('data-creado-por-nombre') || '').toLowerCase().includes(texto);
                } else if (tipo === 'nombre' && texto) {
                    visible = (item.getAttribute('data-nombre-borrador') || '').toLowerCase().includes(texto);
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

    </script>
<?php require_once __DIR__ . '/../complementos/footer.php'; ?>