// assets/js/limpiarCredenciales.js
// La función «Recordarme» guardaba el usuario y la contraseña (apenas ofuscada) en el navegador. Se eliminó:
// este script borra lo que haya quedado guardado de usos anteriores.
(function () {
    try {
        localStorage.removeItem('chefcontrol_remember');
        localStorage.removeItem('chefcontrol_expires');
    } catch (e) { /* almacenamiento bloqueado: no hay nada que borrar */ }
})();
