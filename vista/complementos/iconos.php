<?php
// vista/complementos/iconos.php
// Íconos de línea (estilo Feather), los mismos que usa new-ua: SVG en línea que toman el color del texto.
// Uso: echo icono('dashboard');  echo icono('mensajes', 20, 'mi-clase');

if (!function_exists('icono')) {
    function icono($nombre, $tamano = 18, $clase = '') {
        static $trazos = [
            'dashboard'     => '<rect x="3" y="3" width="7" height="9" rx="1.5"></rect><rect x="14" y="3" width="7" height="5" rx="1.5"></rect><rect x="14" y="12" width="7" height="9" rx="1.5"></rect><rect x="3" y="16" width="7" height="5" rx="1.5"></rect>',
            'usuarios'      => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
            'plan'          => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>',
            'mensajes'      => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22 6 12 13 2 6"></polyline>',
            'organigrama'   => '<rect x="9" y="2" width="6" height="5" rx="1"></rect><rect x="2" y="17" width="6" height="5" rx="1"></rect><rect x="9" y="17" width="6" height="5" rx="1"></rect><rect x="16" y="17" width="6" height="5" rx="1"></rect><path d="M12 7v5M5 17v-2.5a1.5 1.5 0 0 1 1.5-1.5h11a1.5 1.5 0 0 1 1.5 1.5V17M12 12v5"></path>',
            'configuracion' => '<circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>',
            'inicio'        => '<circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>',
            'entrar'        => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line>',
            'salir'         => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line>',
            'campana'       => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>',
            'perfil'        => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
            'info'          => '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>',
            'abajo'         => '<polyline points="6 9 12 15 18 9"></polyline>',
            'dependencias'  => '<path d="M3 21h18"></path><path d="M5 21V7l7-4 7 4v14"></path><line x1="9" y1="21" x2="9" y2="14"></line><line x1="15" y1="21" x2="15" y2="14"></line>',
            'catalogo'      => '<polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline>',
            'facultades'    => '<path d="M22 10L12 5 2 10l10 5 10-5z"></path><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"></path>',
            'novedades'     => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>',
            'almacenamiento'=> '<ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>',
            'roles'         => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>',
            'snapshot'      => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle>',
            'informe'       => '<line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line>',
        ];
        $svg = $trazos[$nombre] ?? $trazos['dashboard'];
        $clase = trim('icono ' . $clase);
        return '<svg class="' . htmlspecialchars($clase) . '" width="' . (int)$tamano . '" height="' . (int)$tamano . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $svg . '</svg>';
    }
}

// Ícono de cada página (encabezado), según $paginaActual
if (!function_exists('icono_de_pagina')) {
    function icono_de_pagina($pagina) {
        $pagina = strtolower((string)$pagina);
        if (strpos($pagina, 'configuraciones') === 0) return 'configuracion';
        $mapa = [
            'dashboard' => 'dashboard', 'usuarios' => 'usuarios', 'for-de-144' => 'plan', 'formatos' => 'plan',
            'modulo144' => 'plan', 'formulacion144' => 'plan', 'mensajes' => 'mensajes', 'organigrama' => 'organigrama',
            'configuraciones' => 'configuracion', 'config144' => 'configuracion', 'dependencias' => 'dependencias',
            'catalogos144' => 'catalogo', 'facultades' => 'facultades', 'novedades' => 'novedades',
            'almacenamiento' => 'almacenamiento', 'roles' => 'roles', 'snapshots' => 'snapshot', 'perfil' => 'perfil',
            'publica' => 'inicio', 'login' => 'entrar',
        ];
        return $mapa[$pagina] ?? 'dashboard';
    }
}
