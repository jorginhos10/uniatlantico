// assets/js/accessibility.js
// Widget de accesibilidad (equivalente a "One Click Accessibility"): tamaño de texto,
// alto contraste, escala de grises, subrayar enlaces, fuente legible. Se inyecta solo en
// el DOM y guarda preferencias en localStorage para que persistan al navegar entre páginas.
(function () {
    'use strict';

    var STORAGE_KEY = 'a11y_prefs_v1';
    var MAX_FS = 4;

    function leerPrefs() {
        try {
            return Object.assign({ fs: 0, contrast: false, grayscale: false, underline: false, readable: false },
                JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'));
        } catch (e) {
            return { fs: 0, contrast: false, grayscale: false, underline: false, readable: false };
        }
    }

    function guardarPrefs(p) {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(p)); } catch (e) {}
    }

    var prefs = leerPrefs();
    var html = document.documentElement;

    function aplicar() {
        for (var i = 1; i <= MAX_FS; i++) html.classList.remove('a11y-fs-' + i);
        if (prefs.fs > 0) html.classList.add('a11y-fs-' + prefs.fs);

        html.classList.toggle('a11y-contrast', !!prefs.contrast);
        html.classList.toggle('a11y-grayscale', !!prefs.grayscale);
        html.classList.toggle('a11y-underline', !!prefs.underline);
        html.classList.toggle('a11y-readable', !!prefs.readable);

        var contrastSwitch = document.getElementById('a11yContrastSwitch');
        var graySwitch = document.getElementById('a11yGraySwitch');
        var underlineSwitch = document.getElementById('a11yUnderlineSwitch');
        var readableSwitch = document.getElementById('a11yReadableSwitch');
        if (contrastSwitch) contrastSwitch.classList.toggle('a11y-on', !!prefs.contrast);
        if (graySwitch) graySwitch.classList.toggle('a11y-on', !!prefs.grayscale);
        if (underlineSwitch) underlineSwitch.classList.toggle('a11y-on', !!prefs.underline);
        if (readableSwitch) readableSwitch.classList.toggle('a11y-on', !!prefs.readable);
    }

    aplicar();

    document.addEventListener('DOMContentLoaded', function () {
        var wrap = document.createElement('div');
        wrap.innerHTML =
            '<button type="button" class="a11y-toggle-btn" id="a11yToggleBtn" title="Accesibilidad" aria-label="Opciones de accesibilidad">' +
                '<i class="fas fa-universal-access"></i>' +
            '</button>' +
            '<div class="a11y-panel" id="a11yPanel" role="dialog" aria-label="Panel de accesibilidad">' +
                '<div class="a11y-panel-header">' +
                    '<h3><i class="fas fa-universal-access me-1"></i> Accesibilidad</h3>' +
                    '<button type="button" class="a11y-panel-close" id="a11yPanelClose" aria-label="Cerrar">&times;</button>' +
                '</div>' +
                '<div class="a11y-panel-body">' +
                    '<div class="a11y-row">' +
                        '<span class="a11y-row-label"><i class="fas fa-text-height"></i> Tamaño de texto</span>' +
                        '<div class="a11y-btn-group">' +
                            '<button type="button" class="a11y-mini-btn" id="a11yFsDown" aria-label="Reducir texto">A-</button>' +
                            '<button type="button" class="a11y-mini-btn" id="a11yFsUp" aria-label="Aumentar texto">A+</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="a11y-row">' +
                        '<span class="a11y-row-label"><i class="fas fa-adjust"></i> Alto contraste</span>' +
                        '<button type="button" class="a11y-switch" id="a11yContrastSwitch"></button>' +
                    '</div>' +
                    '<div class="a11y-row">' +
                        '<span class="a11y-row-label"><i class="fas fa-tint-slash"></i> Escala de grises</span>' +
                        '<button type="button" class="a11y-switch" id="a11yGraySwitch"></button>' +
                    '</div>' +
                    '<div class="a11y-row">' +
                        '<span class="a11y-row-label"><i class="fas fa-underline"></i> Subrayar enlaces</span>' +
                        '<button type="button" class="a11y-switch" id="a11yUnderlineSwitch"></button>' +
                    '</div>' +
                    '<div class="a11y-row">' +
                        '<span class="a11y-row-label"><i class="fas fa-font"></i> Fuente legible</span>' +
                        '<button type="button" class="a11y-switch" id="a11yReadableSwitch"></button>' +
                    '</div>' +
                    '<button type="button" class="a11y-reset-btn" id="a11yResetBtn"><i class="fas fa-undo me-1"></i> Restablecer</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(wrap);

        var btn = document.getElementById('a11yToggleBtn');
        var panel = document.getElementById('a11yPanel');

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            panel.classList.toggle('a11y-open');
        });
        document.getElementById('a11yPanelClose').addEventListener('click', function () {
            panel.classList.remove('a11y-open');
        });
        document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && e.target !== btn) panel.classList.remove('a11y-open');
        });
        panel.addEventListener('click', function (e) { e.stopPropagation(); });

        document.getElementById('a11yFsUp').addEventListener('click', function () {
            prefs.fs = Math.min(MAX_FS, prefs.fs + 1);
            guardarPrefs(prefs); aplicar();
        });
        document.getElementById('a11yFsDown').addEventListener('click', function () {
            prefs.fs = Math.max(0, prefs.fs - 1);
            guardarPrefs(prefs); aplicar();
        });
        document.getElementById('a11yContrastSwitch').addEventListener('click', function () {
            prefs.contrast = !prefs.contrast; guardarPrefs(prefs); aplicar();
        });
        document.getElementById('a11yGraySwitch').addEventListener('click', function () {
            prefs.grayscale = !prefs.grayscale; guardarPrefs(prefs); aplicar();
        });
        document.getElementById('a11yUnderlineSwitch').addEventListener('click', function () {
            prefs.underline = !prefs.underline; guardarPrefs(prefs); aplicar();
        });
        document.getElementById('a11yReadableSwitch').addEventListener('click', function () {
            prefs.readable = !prefs.readable; guardarPrefs(prefs); aplicar();
        });
        document.getElementById('a11yResetBtn').addEventListener('click', function () {
            prefs = { fs: 0, contrast: false, grayscale: false, underline: false, readable: false };
            guardarPrefs(prefs); aplicar();
        });

        aplicar();
    });
}());
