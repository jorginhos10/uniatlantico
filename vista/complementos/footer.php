<?php
// vista/complementos/footer.php
?>
            </div> <!-- Cierre de contentWrapper -->
        </main>
    </div>

    <!-- Script para el dropdown del usuario -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownBtn = document.querySelector('.userDropdownBtn');
            const dropdownMenu = document.querySelector('.dropdownMenu');
            
            if (dropdownBtn && dropdownMenu) {
                dropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                // Cerrar dropdown al hacer clic fuera
                document.addEventListener('click', function() {
                    dropdownMenu.classList.remove('show');
                });

                // Prevenir que el dropdown se cierre al hacer clic dentro
                dropdownMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

            // Navegación activa en sidebar
            const currentPath = window.location.pathname;
            const navItems = document.querySelectorAll('.navLink');
            
            navItems.forEach(item => {
                if (item.getAttribute('href') === currentPath) {
                    item.parentElement.classList.add('active');
                }
            });
        });
    </script>

    <?php echo $jsExtra ?? ''; ?>

    <!-- ── Notificaciones (ntf) ── -->
    <script>
    (function () {
        'use strict';
        var BASE  = '<?php echo $basePath; ?>';
        var btn   = document.getElementById('ntfBtn');
        if (!btn) return;

        var badge  = document.getElementById('ntfBadge');
        var panel  = document.getElementById('ntfPanel');
        var list   = document.getElementById('ntfList');
        var prev   = 0;
        var COLORS = ['#007AFF','#34C759','#FF9500','#AF52DE','#FF3B30','#5856D6','#32ADE6'];

        /* helpers */
        function esc(s) {
            return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }
        function initials(n) {
            var p = (n||'').trim().split(' ');
            return ((p[0]||'')[0]||'').toUpperCase() + ((p[1]||'')[0]||'').toUpperCase();
        }
        function color(n) {
            var h = 0;
            for (var i = 0; i < n.length; i++) h = n.charCodeAt(i) + ((h << 5) - h);
            return COLORS[Math.abs(h) % COLORS.length];
        }
        function av(avatar, nombre) {
            var ini = initials(nombre);
            var col = color(nombre);
            var sz  = 'width:34px;height:34px;border-radius:50%;';
            if (avatar && avatar !== 'default.png') {
                return '<img src="' + BASE + '/assets/media/users/' + esc(avatar) + '" '
                     + 'style="' + sz + 'object-fit:cover;border:1.5px solid rgba(0,0,0,.08)" '
                     + 'onerror="this.style.display=\'none\'">';
            }
            return '<div style="' + sz + 'background:' + col + ';color:#fff;'
                 + 'display:flex;align-items:center;justify-content:center;'
                 + 'font-size:12px;font-weight:700;">' + ini + '</div>';
        }
        function relTime(raw) {
            if (!raw) return '';
            var d   = new Date(raw.replace(' ', 'T'));
            var seg = Math.floor((Date.now() - d) / 1000);
            if (seg < 60)    return 'Ahora';
            if (seg < 3600)  return 'Hace ' + Math.floor(seg / 60) + ' min';
            if (seg < 86400) return 'Hace ' + Math.floor(seg / 3600) + ' h';
            return d.toLocaleDateString('es-CO', {day:'2-digit', month:'short'});
        }

        /* actualizar badge — expuesto globalmente */
        window.actualizarBellBadge = function (n) {
            badge.textContent = n;
            if (n > 0) {
                badge.classList.add('ntf-visible');
                badge.style.display = 'inline-flex';
            } else {
                badge.classList.remove('ntf-visible');
                badge.style.display = 'none';
            }
            if (n > prev) {                          /* nuevo mensaje → ring */
                btn.classList.remove('ntf-ring');
                void btn.offsetWidth;
                btn.classList.add('ntf-ring');
                setTimeout(function () { btn.classList.remove('ntf-ring'); }, 650);
            }
            prev = n;
        };

        /* polling contador */
        function pollCount() {
            fetch(BASE + '/mensajes/contarNoLeidos')
                .then(function (r) { return r.json(); })
                .then(function (d) { if (d.success) window.actualizarBellBadge(d.total); })
                .catch(function () {});
        }

        /* cargar 4 recientes no leídos */
        function loadRecent() {
            list.innerHTML = '<div class="ntf-empty">'
                + '<i class="fas fa-spinner fa-spin" style="color:#aeaeb2;font-size:22px"></i>'
                + '<span>Cargando…</span></div>';

            fetch(BASE + '/mensajes/recientesNoLeidos')
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (!d.success || !d.mensajes || !d.mensajes.length) {
                        list.innerHTML = '<div class="ntf-empty">'
                            + '<i class="fas fa-check-circle"></i>'
                            + '<span>Sin mensajes nuevos</span></div>';
                        return;
                    }
                    var html = '';
                    var items = d.mensajes.slice(0, 4);
                    for (var i = 0; i < items.length; i++) {
                        var m      = items[i];
                        var nombre = m.remitente_nombre || 'Desconocido';
                        html += '<a href="' + BASE + '/mensajes" class="ntf-item">'
                              + '<div class="ntf-av">' + av(m.remitente_avatar, nombre) + '</div>'
                              + '<div class="ntf-body">'
                              + '<div class="ntf-from">' + esc(nombre) + '</div>'
                              + '<div class="ntf-subj">' + esc(m.asunto || '') + '</div>'
                              + '<div class="ntf-time">' + relTime(m.fecha_envio) + '</div>'
                              + '</div></a>';
                    }
                    list.innerHTML = html;
                })
                .catch(function () {
                    list.innerHTML = '<div class="ntf-empty"><span>Error al cargar</span></div>';
                });
        }

        /* toggle panel */
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = panel.classList.contains('ntf-show');
            /* cerrar dropdown de usuario si está abierto */
            var uMenu = document.querySelector('.dropdownMenu');
            if (uMenu) uMenu.classList.remove('show');

            if (open) {
                panel.classList.remove('ntf-show');
                btn.classList.remove('ntf-open');
            } else {
                panel.classList.add('ntf-show');
                btn.classList.add('ntf-open');
                loadRecent();
            }
        });

        /* cerrar al hacer clic fuera */
        document.addEventListener('click', function (e) {
            if (!btn.contains(e.target) && !panel.contains(e.target)) {
                panel.classList.remove('ntf-show');
                btn.classList.remove('ntf-open');
            }
        });

        panel.addEventListener('click', function (e) { e.stopPropagation(); });

        /* arranque */
        pollCount();
        setInterval(pollCount, 30000);
    }());
    </script>
</body>
</html>