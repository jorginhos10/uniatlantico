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
</body>
</html>