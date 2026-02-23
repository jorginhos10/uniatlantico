<<<<<<< Updated upstream
// assets/js/formatos_for_de_144.js

/**
 * Sistema de Gestión de Formatos FOR-DE-144
 * Versión con control de tiempo (libre / rango)
 */

// ========== VARIABLES GLOBALES ==========
let formatoIdAEliminar = null;
let basePath = '';

// Detectar basePath automáticamente
(function detectBasePath() {
    const metaBasePath = document.querySelector('meta[name="base-path"]');
    if (metaBasePath) {
        basePath = metaBasePath.getAttribute('content');
    } else {
        // Intentar deducir la ruta base
        const pathParts = window.location.pathname.split('/');
        // Eliminar el último segmento (archivo actual)
        pathParts.pop();
        basePath = pathParts.join('/') || '';
    }
})();

// ========== FUNCIONES PARA POPUP CREAR ==========

/**
 * Abre el popup para crear nuevo formato
 */
function abrirPopupCrear() {
    const popup = document.getElementById('popupCrear');
    if (popup) {
        popup.style.display = 'flex';
        
        // Enfocar el primer campo
        const primerCampo = document.querySelector('#formNuevoFormato input[name="formato_nombre"]');
        if (primerCampo) {
            setTimeout(() => primerCampo.focus(), 100);
        }
        
        // Resetear a tiempo libre por defecto
        const radioLibre = document.querySelector('input[name="tipo_tiempo"][value="libre"]');
        if (radioLibre) {
            radioLibre.checked = true;
            cambiarTipoTiempo('libre');
        }
        
        // Cargar borrador si existe
        cargarBorrador();
    }
}

/**
 * Cierra el popup de crear
 */
function cerrarPopup() {
    const popup = document.getElementById('popupCrear');
    if (popup) {
        popup.style.display = 'none';
    }
    const formulario = document.getElementById('formNuevoFormato');
    if (formulario) {
        formulario.reset();
    }
}

// ========== FUNCIONES PARA GESTIÓN DE TIEMPO ==========

/**
 * Cambia el tipo de tiempo y muestra/oculta campos de rango
 * @param {string} tipo - 'libre' o 'rango'
 */
function cambiarTipoTiempo(tipo) {
    const rangoContainer = document.getElementById('rangoFechasPopup');
    const fechaInicio = document.getElementById('fecha_inicio_popup');
    const fechaFin = document.getElementById('fecha_fin_popup');
    
    if (!rangoContainer) return;
    
    // Actualizar UI según tipo
    if (tipo === 'rango') {
        rangoContainer.style.display = 'block';
        rangoContainer.classList.add('slide-down');
        
        if (fechaInicio) fechaInicio.required = true;
        if (fechaFin) fechaFin.required = true;
        
        // Inicializar Flatpickr si está disponible
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#fecha_inicio_popup, #fecha_fin_popup", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                locale: "es",
                minDate: "today",
                time_24hr: true
            });
        }
    } else {
        rangoContainer.style.display = 'none';
        
        if (fechaInicio) {
            fechaInicio.required = false;
            fechaInicio.value = '';
        }
        if (fechaFin) {
            fechaFin.required = false;
            fechaFin.value = '';
        }
    }
    
    // Marcar opción seleccionada visualmente
    document.querySelectorAll('.opcion-tiempo').forEach(el => {
        el.classList.remove('selected');
    });
    
    const selectedOption = document.querySelector(`.opcion-tiempo input[value="${tipo}"]`).closest('.opcion-tiempo');
    if (selectedOption) {
        selectedOption.classList.add('selected');
    }
}

/**
 * Valida las fechas de rango
 * @returns {boolean} - true si las fechas son válidas
 */
function validarRangoFechas() {
    const tipoTiempo = document.querySelector('input[name="tipo_tiempo"]:checked')?.value;
    
    if (tipoTiempo === 'rango') {
        const fechaInicio = document.getElementById('fecha_inicio_popup')?.value;
        const fechaFin = document.getElementById('fecha_fin_popup')?.value;
        
        if (!fechaInicio || !fechaFin) {
            mostrarMensaje('error', 'Las fechas de inicio y fin son obligatorias');
            return false;
        }
        
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        
        if (inicio >= fin) {
            mostrarMensaje('error', 'La fecha de inicio debe ser anterior a la fecha de fin');
            return false;
        }
        
        if (inicio < new Date()) {
            if (!confirm('La fecha de inicio es anterior a la fecha actual. ¿Desea continuar?')) {
                return false;
            }
        }
    }
    
    return true;
}

// ========== FUNCIONES PARA ELIMINAR ==========

/**
 * Abre popup de confirmación para eliminar
 * @param {number} id - ID del formato a eliminar
 * @param {string} nombre - Nombre del formato
 */
function confirmarEliminar(id, nombre) {
    event.stopPropagation();
    formatoIdAEliminar = id;
    
    const mensaje = document.getElementById('mensajeConfirmacion');
    if (mensaje) {
        mensaje.innerHTML = `¿Estás seguro de eliminar el formato "<strong>${nombre}</strong>"?<br>
                            <small class="text-muted">Esta acción no se puede deshacer.</small>`;
    }
    
    const popup = document.getElementById('popupConfirmar');
    if (popup) {
        popup.style.display = 'flex';
    }
}

/**
 * Cierra el popup de confirmación
 */
function cerrarConfirmar() {
    const popup = document.getElementById('popupConfirmar');
    if (popup) {
        popup.style.display = 'none';
    }
    formatoIdAEliminar = null;
}

/**
 * Ejecuta la eliminación del formato
 */
function eliminarFormato() {
    if (!formatoIdAEliminar) return;
    
    // Mostrar indicador de carga
    const btnEliminar = document.getElementById('btnEliminarConfirmar');
    const originalText = btnEliminar?.innerHTML;
    if (btnEliminar) {
        btnEliminar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
        btnEliminar.disabled = true;
    }
    
    // Crear y enviar formulario
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = basePath + '/formatos-for-de-144/eliminar';
    form.style.display = 'none';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'formato_id';
    input.value = formatoIdAEliminar;
    
    // CSRF Token si existe
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = token.getAttribute('content');
        form.appendChild(csrfInput);
    }
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// ========== AUTO-GUARDADO EN LOCALSTORAGE ==========

/**
 * Guarda borrador en localStorage
 */
function guardarBorrador() {
    const nombre = document.querySelector('input[name="formato_nombre"]')?.value || '';
    const descripcion = document.querySelector('textarea[name="formato_descripcion"]')?.value || '';
    const tipoTiempo = document.querySelector('input[name="tipo_tiempo"]:checked')?.value || 'libre';
    const fechaInicio = document.getElementById('fecha_inicio_popup')?.value || '';
    const fechaFin = document.getElementById('fecha_fin_popup')?.value || '';
    
    // Solo guardar si hay contenido
    if (nombre || descripcion || (tipoTiempo === 'rango' && (fechaInicio || fechaFin))) {
        const borrador = {
            nombre,
            descripcion,
            tipo_tiempo: tipoTiempo,
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin,
            timestamp: new Date().toISOString()
        };
        
        localStorage.setItem('formato_for_de_144_borrador', JSON.stringify(borrador));
        console.log('Borrador guardado:', new Date().toLocaleTimeString());
    }
}

/**
 * Carga borrador desde localStorage
 */
function cargarBorrador() {
    const borradorGuardado = localStorage.getItem('formato_for_de_144_borrador');
    if (!borradorGuardado) return;
    
    try {
        const borrador = JSON.parse(borradorGuardado);
        const ahora = new Date();
        const tiempoBorrador = new Date(borrador.timestamp);
        const horasTranscurridas = (ahora - tiempoBorrador) / (1000 * 60 * 60);
        
        // Borrar borradores de más de 24 horas
        if (horasTranscurridas > 24) {
            localStorage.removeItem('formato_for_de_144_borrador');
            return;
        }
        
        // Preguntar si desea recuperar
        if (confirm('¿Recuperar el borrador guardado de ' + tiempoBorrador.toLocaleString() + '?')) {
            document.querySelector('input[name="formato_nombre"]').value = borrador.nombre || '';
            document.querySelector('textarea[name="formato_descripcion"]').value = borrador.descripcion || '';
            
            if (borrador.tipo_tiempo === 'rango') {
                const radioRango = document.querySelector('input[name="tipo_tiempo"][value="rango"]');
                if (radioRango) {
                    radioRango.checked = true;
                    cambiarTipoTiempo('rango');
                    
                    // Esperar a que Flatpickr se inicialice
                    setTimeout(() => {
                        if (document.getElementById('fecha_inicio_popup')) {
                            document.getElementById('fecha_inicio_popup').value = borrador.fecha_inicio || '';
                        }
                        if (document.getElementById('fecha_fin_popup')) {
                            document.getElementById('fecha_fin_popup').value = borrador.fecha_fin || '';
                        }
                    }, 100);
                }
            }
        }
    } catch (e) {
        console.error('Error al cargar borrador:', e);
        localStorage.removeItem('formato_for_de_144_borrador');
    }
}

// ========== VALIDACIÓN DE FORMULARIO ==========

/**
 * Valida el formulario antes de enviar
 * @returns {boolean} - true si es válido
 */
function validarFormulario() {
    const nombreInput = document.querySelector('input[name="formato_nombre"]');
    const nombre = nombreInput?.value.trim();
    
    if (!nombre) {
        mostrarMensaje('error', 'Por favor, ingresa un nombre para el formato');
        if (nombreInput) {
            nombreInput.focus();
            nombreInput.style.borderColor = '#e74c3c';
            setTimeout(() => {
                nombreInput.style.borderColor = '';
            }, 2000);
        }
        return false;
    }
    
    return validarRangoFechas();
}

/**
 * Muestra mensajes temporales
 * @param {string} tipo - 'success' o 'error'
 * @param {string} texto - Mensaje a mostrar
 */
function mostrarMensaje(tipo, texto) {
    // Eliminar mensajes anteriores
    const mensajesAnteriores = document.querySelectorAll('.mensaje-flotante');
    mensajesAnteriores.forEach(m => m.remove());
    
    const mensaje = document.createElement('div');
    mensaje.className = `mensaje-flotante mensaje-${tipo}`;
    mensaje.innerHTML = `
        <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${texto}</span>
    `;
    
    // Estilos para mensaje flotante
    mensaje.style.position = 'fixed';
    mensaje.style.top = '20px';
    mensaje.style.right = '20px';
    mensaje.style.padding = '15px 20px';
    mensaje.style.borderRadius = '8px';
    mensaje.style.backgroundColor = tipo === 'success' ? '#d4edda' : '#f8d7da';
    mensaje.style.color = tipo === 'success' ? '#155724' : '#721c24';
    mensaje.style.border = `1px solid ${tipo === 'success' ? '#c3e6cb' : '#f5c6cb'}`;
    mensaje.style.zIndex = '9999';
    mensaje.style.display = 'flex';
    mensaje.style.alignItems = 'center';
    mensaje.style.gap = '10px';
    mensaje.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
    mensaje.style.animation = 'slideDown 0.3s ease';
    
    document.body.appendChild(mensaje);
    
    setTimeout(() => {
        mensaje.style.animation = 'slideUp 0.3s ease';
        setTimeout(() => mensaje.remove(), 300);
    }, 3000);
}

// ========== CONFIGURACIÓN DE EVENTOS ==========

/**
 * Configura cierre de popups al hacer clic fuera
 */
function configurarClicExterno() {
    document.querySelectorAll('.popup-overlay').forEach(popup => {
        popup.addEventListener('click', function(e) {
            if (e.target === this) {
                if (popup.id === 'popupCrear') {
                    cerrarPopup();
                } else if (popup.id === 'popupConfirmar') {
                    cerrarConfirmar();
                }
            }
        });
    });
}

/**
 * Configura cierre con tecla ESC
 */
function configurarTeclaEscape() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarPopup();
            cerrarConfirmar();
        }
    });
}

/**
 * Configura auto-guardado en localStorage
 */
function configurarAutoGuardado() {
    const formulario = document.getElementById('formNuevoFormato');
    if (!formulario) return;
    
    const inputs = formulario.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', guardarBorrador);
    });
    
    // Guardar también al cambiar tipo de tiempo
    const radiosTiempo = formulario.querySelectorAll('input[name="tipo_tiempo"]');
    radiosTiempo.forEach(radio => {
        radio.addEventListener('change', guardarBorrador);
    });
    
    // Limpiar localStorage al enviar
    formulario.addEventListener('submit', function() {
        localStorage.removeItem('formato_for_de_144_borrador');
    });
}

/**
 * Configura validación del formulario
 */
function configurarValidacionFormulario() {
    const formulario = document.getElementById('formNuevoFormato');
    if (!formulario) return;
    
    formulario.addEventListener('submit', function(e) {
        if (!validarFormulario()) {
            e.preventDefault();
            return false;
        }
        
        // Mostrar indicador de carga
        const btnGuardar = document.querySelector('.btn-guardar');
        if (btnGuardar) {
            const originalText = btnGuardar.innerHTML;
            btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            btnGuardar.disabled = true;
        }
        
        return true;
    });
}

/**
 * Configura efectos hover en tarjetas
 */
function configurarEfectosTarjetas() {
    const tarjetas = document.querySelectorAll('.card:not(.card-agregar)');
    
    tarjetas.forEach(tarjeta => {
        tarjeta.addEventListener('mouseenter', function() {
            const footer = this.querySelector('.card-footer');
            if (footer) {
                footer.style.backgroundColor = '#e8f5e9';
                footer.style.transition = 'background-color 0.3s ease';
            }
        });
        
        tarjeta.addEventListener('mouseleave', function() {
            const footer = this.querySelector('.card-footer');
            if (footer) {
                footer.style.backgroundColor = '';
            }
        });
    });
}

/**
 * Anima mensajes existentes
 */
function animarMensajes() {
    const mensajes = document.querySelectorAll('.mensaje-exito, .mensaje-error');
    
    mensajes.forEach(mensaje => {
        mensaje.style.opacity = '0';
        mensaje.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            mensaje.style.transition = 'all 0.3s ease';
            mensaje.style.opacity = '1';
            mensaje.style.transform = 'translateY(0)';
        }, 100);
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            if (mensaje.parentNode) {
                mensaje.style.opacity = '0';
                mensaje.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    if (mensaje.parentNode) {
                        mensaje.parentNode.removeChild(mensaje);
                    }
                }, 300);
            }
        }, 5000);
    });
}

/**
 * Configura contador de caracteres para textarea
 */
function configurarContadorCaracteres() {
    const textarea = document.querySelector('#formNuevoFormato textarea[name="formato_descripcion"]');
    if (!textarea) return;
    
    // Crear contador si no existe
    let contador = document.querySelector('.contador-caracteres');
    if (!contador) {
        contador = document.createElement('div');
        contador.className = 'contador-caracteres';
        textarea.parentNode.insertBefore(contador, textarea.nextSibling);
    }
    
    const maxLength = 500;
    
    const actualizarContador = () => {
        const longitud = textarea.value.length;
        contador.textContent = `${longitud}/${maxLength} caracteres`;
        
        // Cambiar color según longitud
        contador.classList.remove('warning', 'danger');
        if (longitud > maxLength * 0.9) {
            contador.classList.add('danger');
        } else if (longitud > maxLength * 0.8) {
            contador.classList.add('warning');
        }
        
        // Prevenir exceder el límite
        if (longitud > maxLength) {
            textarea.value = textarea.value.substring(0, maxLength);
            contador.textContent = `${maxLength}/${maxLength} caracteres (máximo alcanzado)`;
        }
    };
    
    textarea.addEventListener('input', actualizarContador);
    actualizarContador(); // Inicializar
}

/**
 * Configura tooltips para elementos con título
 */
function configurarTooltips() {
    const elementosConTooltip = document.querySelectorAll('[title]');
    elementosConTooltip.forEach(el => {
        el.classList.add('tooltip-tiempo');
        
        const tooltip = document.createElement('span');
        tooltip.className = 'tooltip-text';
        tooltip.textContent = el.getAttribute('title');
        
        el.appendChild(tooltip);
        el.removeAttribute('title');
    });
}

// ========== INICIALIZACIÓN ==========

/**
 * Inicializa todas las funcionalidades
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema de Formatos FOR-DE-144 inicializando...');
    
    // Configurar todas las funcionalidades
    configurarClicExterno();
    configurarTeclaEscape();
    configurarAutoGuardado();
    configurarValidacionFormulario();
    configurarEfectosTarjetas();
    configurarContadorCaracteres();
    configurarTooltips();
    animarMensajes();
    
    // Inicializar opciones de tiempo si existen en el DOM
    const radiosTiempo = document.querySelectorAll('input[name="tipo_tiempo"]');
    if (radiosTiempo.length > 0) {
        radiosTiempo.forEach(radio => {
            radio.addEventListener('change', function() {
                cambiarTipoTiempo(this.value);
            });
            
            // Inicializar estado
            if (radio.checked) {
                cambiarTipoTiempo(radio.value);
            }
        });
    }
    
    // Inicializar Flatpickr para inputs datetime-local
    if (typeof flatpickr !== 'undefined') {
        flatpickr("input[type=datetime-local]", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            locale: "es",
            minDate: "today",
            time_24hr: true,
            onChange: guardarBorrador
        });
    }
    
    // Confirmación antes de salir si hay borrador
    window.addEventListener('beforeunload', function(e) {
        const borrador = localStorage.getItem('formato_for_de_144_borrador');
        if (borrador) {
            // Verificar si realmente hay cambios sin guardar
            const nombre = document.querySelector('input[name="formato_nombre"]')?.value;
            if (nombre) {
                e.preventDefault();
                e.returnValue = 'Tienes un borrador sin guardar. ¿Seguro que quieres salir?';
                return e.returnValue;
            }
        }
    });
    
    console.log('✅ Sistema de Formatos FOR-DE-144 inicializado correctamente');
});

// ========== EXPORTAR FUNCIONES PARA USO GLOBAL ==========

window.FormatoForDe144 = {
    abrirPopupCrear,
    cerrarPopup,
    confirmarEliminar,
    cerrarConfirmar,
    eliminarFormato,
    cambiarTipoTiempo,
    validarFormulario,
    guardarBorrador,
    mostrarMensaje
};

// Exponer funciones individuales para uso en HTML
window.abrirPopupCrear = abrirPopupCrear;
window.cerrarPopup = cerrarPopup;
window.confirmarEliminar = confirmarEliminar;
window.cerrarConfirmar = cerrarConfirmar;
window.eliminarFormato = eliminarFormato;
window.cambiarTipoTiempo = cambiarTipoTiempo;
=======
// assets/js/formatos_for_de_144.js - VERSIÓN COMPLETA Y DEFINITIVA
// Sistema de gestión de formatos FOR-DE-144 con AJAX

const FormatoForDe144 = {
    // Variables de instancia
    idEliminar: null,
    formatoActual: null,
    baseUrl: window.location.pathname.includes('index.php') ? '' : 'index.php',

    /**
     * Inicializa el sistema
     */
    init: function() {
        this.configurarEventos();
        this.configurarRadios();
        this.configurarCierreExterno();
        this.cargarBorrador();
        this.animarMensajes();
        console.log('✅ Sistema de formatos FOR-DE-144 inicializado correctamente');
    },

    /**
     * Configura todos los event listeners
     */
    configurarEventos: function() {
        // Formulario de creación
        const formCrear = document.getElementById('formNuevoFormato');
        if (formCrear) {
            formCrear.addEventListener('submit', (e) => {
                e.preventDefault();
                this.guardarFormulario();
            });
            
            // Auto-guardar mientras se escribe
            const inputs = ['titulo', 'descripcion', 'fecha_inicio', 'fecha_cierre'];
            inputs.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', () => this.guardarBorrador());
                }
            });
        }

        // Formulario de edición
        const formEditar = document.getElementById('formEditarFormato');
        if (formEditar) {
            formEditar.addEventListener('submit', (e) => {
                e.preventDefault();
                this.actualizarFormulario();
            });
        }

        // Botón eliminar confirmación
        const btnEliminar = document.getElementById('btnEliminarConfirmar');
        if (btnEliminar) {
            btnEliminar.addEventListener('click', () => {
                this.eliminarFormulario();
            });
        }

        // Validación de fechas en tiempo real
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaCierre = document.getElementById('fecha_cierre');
        if (fechaInicio && fechaCierre) {
            fechaInicio.addEventListener('change', () => {
                fechaCierre.min = fechaInicio.value;
            });
        }

        const editFechaInicio = document.getElementById('edit_fecha_inicio');
        const editFechaCierre = document.getElementById('edit_fecha_cierre');
        if (editFechaInicio && editFechaCierre) {
            editFechaInicio.addEventListener('change', () => {
                editFechaCierre.min = editFechaInicio.value;
            });
        }
    },

    /**
     * Configura los radio buttons para mostrar/ocultar campos de fecha
     */
    configurarRadios: function() {
        // Popup de creación
        const sinRestricciones = document.getElementById('sin_restricciones');
        const conRestricciones = document.getElementById('con_restricciones');
        const camposFecha = document.getElementById('camposFecha');
        
        if (sinRestricciones && conRestricciones && camposFecha) {
            sinRestricciones.addEventListener('change', () => {
                camposFecha.style.display = 'none';
                document.getElementById('fecha_inicio').value = '';
                document.getElementById('fecha_cierre').value = '';
                document.getElementById('fecha_inicio').required = false;
                document.getElementById('fecha_cierre').required = false;
            });
            
            conRestricciones.addEventListener('change', () => {
                camposFecha.style.display = 'block';
                document.getElementById('fecha_inicio').required = true;
                document.getElementById('fecha_cierre').required = true;
                
                // Establecer fecha mínima como hoy
                const hoy = new Date().toISOString().slice(0, 16);
                document.getElementById('fecha_inicio').min = hoy;
                document.getElementById('fecha_cierre').min = hoy;
            });
        }

        // Popup de edición
        const editSinRestricciones = document.getElementById('edit_sin_restricciones');
        const editConRestricciones = document.getElementById('edit_con_restricciones');
        const editCamposFecha = document.getElementById('edit_camposFecha');
        
        if (editSinRestricciones && editConRestricciones && editCamposFecha) {
            editSinRestricciones.addEventListener('change', () => {
                editCamposFecha.style.display = 'none';
                document.getElementById('edit_fecha_inicio').value = '';
                document.getElementById('edit_fecha_cierre').value = '';
                document.getElementById('edit_fecha_inicio').required = false;
                document.getElementById('edit_fecha_cierre').required = false;
            });
            
            editConRestricciones.addEventListener('change', () => {
                editCamposFecha.style.display = 'block';
                document.getElementById('edit_fecha_inicio').required = true;
                document.getElementById('edit_fecha_cierre').required = true;
                
                const hoy = new Date().toISOString().slice(0, 16);
                document.getElementById('edit_fecha_inicio').min = hoy;
                document.getElementById('edit_fecha_cierre').min = hoy;
            });
        }
    },

    /**
     * Configura el cierre de popups al hacer clic fuera o presionar ESC
     */
    configurarCierreExterno: function() {
        // Clic fuera del popup
        document.querySelectorAll('.popup-overlay').forEach(popup => {
            popup.addEventListener('click', (e) => {
                if (e.target === popup) {
                    if (popup.id === 'popupCrear') this.cerrarPopup();
                    if (popup.id === 'popupEditar') this.cerrarPopupEditar();
                    if (popup.id === 'popupConfirmar') this.cerrarConfirmar();
                }
            });
        });

        // Tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.cerrarPopup();
                this.cerrarPopupEditar();
                this.cerrarConfirmar();
            }
        });
    },

    /**
     * Abre el popup para crear un nuevo formato
     */
    abrirPopupCrear: function() {
        const popup = document.getElementById('popupCrear');
        if (popup) {
            popup.style.display = 'flex';
            document.getElementById('formNuevoFormato').reset();
            document.getElementById('sin_restricciones').checked = true;
            document.getElementById('camposFecha').style.display = 'none';
            document.getElementById('titulo').focus();
        }
    },

    /**
     * Cierra el popup de creación
     */
    cerrarPopup: function() {
        document.getElementById('popupCrear').style.display = 'none';
    },

    /**
     * Abre el popup para editar un formato existente
     * @param {Object} formato - Datos del formato a editar
     */
    abrirPopupEditar: function(formato) {
        this.formatoActual = formato;
        const popup = document.getElementById('popupEditar');
        
        if (popup) {
            popup.style.display = 'flex';
            
            // Llenar el formulario con los datos
            document.getElementById('edit_id').value = formato.id;
            document.getElementById('edit_titulo').value = formato.titulo;
            document.getElementById('edit_descripcion').value = formato.descripcion || '';
            
            const editCamposFecha = document.getElementById('edit_camposFecha');
            const editFechaInicio = document.getElementById('edit_fecha_inicio');
            const editFechaCierre = document.getElementById('edit_fecha_cierre');
            
            // Configurar según tenga fechas o no
            if (formato.fecha_inicio && formato.fecha_cierre) {
                document.getElementById('edit_con_restricciones').checked = true;
                editCamposFecha.style.display = 'block';
                editFechaInicio.value = formato.fecha_inicio.slice(0, 16);
                editFechaCierre.value = formato.fecha_cierre.slice(0, 16);
                editFechaInicio.required = true;
                editFechaCierre.required = true;
            } else {
                document.getElementById('edit_sin_restricciones').checked = true;
                editCamposFecha.style.display = 'none';
                editFechaInicio.value = '';
                editFechaCierre.value = '';
                editFechaInicio.required = false;
                editFechaCierre.required = false;
            }
            
            document.getElementById('edit_titulo').focus();
        }
    },

    /**
     * Cierra el popup de edición
     */
    cerrarPopupEditar: function() {
        document.getElementById('popupEditar').style.display = 'none';
        this.formatoActual = null;
    },

    /**
     * Muestra el popup de confirmación para eliminar
     * @param {number} id - ID del formato a eliminar
     * @param {string} titulo - Título del formato
     */
    confirmarEliminar: function(id, titulo) {
        event.stopPropagation();
        this.idEliminar = id;
        
        const mensaje = document.getElementById('mensajeConfirmacion');
        if (mensaje) {
            mensaje.innerHTML = `¿Estás seguro de eliminar el formato "<strong>${this.escapeHtml(titulo)}</strong>"?<br>
                               <small style="color: #e74c3c; margin-top: 10px; display: block;">
                               <i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</small>`;
        }
        
        document.getElementById('popupConfirmar').style.display = 'flex';
    },

    /**
     * Cierra el popup de confirmación
     */
    cerrarConfirmar: function() {
        document.getElementById('popupConfirmar').style.display = 'none';
        this.idEliminar = null;
    },

    /**
     * Guarda un nuevo formulario vía AJAX
     */
    guardarFormulario: function() {
        const formData = new FormData(document.getElementById('formNuevoFormato'));
        
        // Validaciones básicas
        const titulo = formData.get('titulo');
        if (!titulo || titulo.trim() === '') {
            this.mostrarNotificacion('El título es obligatorio', 'error');
            document.getElementById('titulo').focus();
            return;
        }

        // Validar fechas si es con restricciones
        if (formData.get('tipo_tiempo') === 'con_restricciones') {
            const fechaInicio = formData.get('fecha_inicio');
            const fechaCierre = formData.get('fecha_cierre');
            
            if (!fechaInicio || !fechaCierre) {
                this.mostrarNotificacion('Las fechas son obligatorias', 'error');
                return;
            }
            
            if (new Date(fechaInicio) > new Date(fechaCierre)) {
                this.mostrarNotificacion('La fecha de inicio no puede ser mayor a la fecha de cierre', 'error');
                return;
            }
        }

        // Mostrar indicador de carga
        const btnGuardar = document.querySelector('#formNuevoFormato .btn-guardar');
        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        btnGuardar.disabled = true;

        // Enviar petición
        fetch(`${this.baseUrl}?controller=FORDE144&action=crear`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                this.cerrarPopup();
                this.mostrarNotificacion('✅ ' + data.message, 'exito');
                this.limpiarBorrador();
                setTimeout(() => location.reload(), 1500);
            } else {
                this.mostrarNotificacion('❌ ' + data.message, 'error');
                btnGuardar.innerHTML = textoOriginal;
                btnGuardar.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.mostrarNotificacion('❌ Error de conexión: ' + error.message, 'error');
            btnGuardar.innerHTML = textoOriginal;
            btnGuardar.disabled = false;
        });
    },

    /**
     * Actualiza un formulario existente vía AJAX
     */
    actualizarFormulario: function() {
        const formData = new FormData(document.getElementById('formEditarFormato'));
        
        // Validaciones básicas
        const titulo = formData.get('titulo');
        if (!titulo || titulo.trim() === '') {
            this.mostrarNotificacion('El título es obligatorio', 'error');
            document.getElementById('edit_titulo').focus();
            return;
        }

        // Validar fechas si es con restricciones
        if (formData.get('edit_tipo_tiempo') === 'con_restricciones') {
            const fechaInicio = formData.get('fecha_inicio');
            const fechaCierre = formData.get('fecha_cierre');
            
            if (!fechaInicio || !fechaCierre) {
                this.mostrarNotificacion('Las fechas son obligatorias', 'error');
                return;
            }
            
            if (new Date(fechaInicio) > new Date(fechaCierre)) {
                this.mostrarNotificacion('La fecha de inicio no puede ser mayor a la fecha de cierre', 'error');
                return;
            }
        }

        // Mostrar indicador de carga
        const btnGuardar = document.querySelector('#formEditarFormato .btn-guardar');
        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
        btnGuardar.disabled = true;

        // Enviar petición
        fetch(`${this.baseUrl}?controller=FORDE144&action=editar`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                this.cerrarPopupEditar();
                this.mostrarNotificacion('✅ ' + data.message, 'exito');
                setTimeout(() => location.reload(), 1500);
            } else {
                this.mostrarNotificacion('❌ ' + data.message, 'error');
                btnGuardar.innerHTML = textoOriginal;
                btnGuardar.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.mostrarNotificacion('❌ Error de conexión: ' + error.message, 'error');
            btnGuardar.innerHTML = textoOriginal;
            btnGuardar.disabled = false;
        });
    },

    /**
     * Elimina un formulario vía AJAX
     */
    eliminarFormulario: function() {
        if (!this.idEliminar) return;

        const formData = new FormData();
        formData.append('id', this.idEliminar);

        // Mostrar indicador de carga
        const btnEliminar = document.getElementById('btnEliminarConfirmar');
        const textoOriginal = btnEliminar.innerHTML;
        btnEliminar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
        btnEliminar.disabled = true;

        // Enviar petición
        fetch(`${this.baseUrl}?controller=FORDE144&action=eliminar`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                this.cerrarConfirmar();
                this.mostrarNotificacion('✅ ' + data.message, 'exito');
                setTimeout(() => location.reload(), 1500);
            } else {
                this.mostrarNotificacion('❌ ' + data.message, 'error');
                btnEliminar.innerHTML = textoOriginal;
                btnEliminar.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.mostrarNotificacion('❌ Error de conexión: ' + error.message, 'error');
            btnEliminar.innerHTML = textoOriginal;
            btnEliminar.disabled = false;
        });
    },

    /**
     * Muestra una notificación temporal
     * @param {string} mensaje - Texto a mostrar
     * @param {string} tipo - Tipo de notificación (exito/error)
     */
    mostrarNotificacion: function(mensaje, tipo) {
        // Crear elemento de notificación
        const notificacion = document.createElement('div');
        notificacion.className = `mensaje-${tipo}`;
        notificacion.innerHTML = mensaje;
        notificacion.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: slideInRight 0.3s ease;
        `;

        // Agregar al DOM
        document.body.appendChild(notificacion);

        // Eliminar después de 3 segundos
        setTimeout(() => {
            notificacion.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notificacion.remove(), 300);
        }, 3000);
    },

    /**
     * Muestra un mensaje en el contenedor principal
     * @param {string} texto - Mensaje a mostrar
     * @param {string} tipo - Tipo de mensaje (exito/error)
     */
    mostrarMensaje: function(texto, tipo) {
        const mensajeDiv = document.createElement('div');
        mensajeDiv.className = `mensaje-${tipo}`;
        mensajeDiv.innerHTML = `<i class="fas fa-${tipo === 'exito' ? 'check-circle' : 'exclamation-circle'}"></i> ${texto}`;
        
        const container = document.querySelector('.formatos-container');
        if (container) {
            container.insertBefore(mensajeDiv, container.firstChild);
            
            setTimeout(() => {
                mensajeDiv.style.opacity = '0';
                mensajeDiv.style.transform = 'translateY(-20px)';
                setTimeout(() => mensajeDiv.remove(), 300);
            }, 3000);
        }
    },

    /**
     * Anima los mensajes existentes
     */
    animarMensajes: function() {
        const mensajes = document.querySelectorAll('.mensaje-exito, .mensaje-error');
        mensajes.forEach(mensaje => {
            mensaje.style.opacity = '0';
            mensaje.style.transform = 'translateY(-20px)';
            
            setTimeout(() => {
                mensaje.style.transition = 'all 0.3s ease';
                mensaje.style.opacity = '1';
                mensaje.style.transform = 'translateY(0)';
            }, 100);
        });
    },

    /**
     * Guarda un borrador en localStorage
     */
    guardarBorrador: function() {
        const titulo = document.getElementById('titulo')?.value || '';
        const descripcion = document.getElementById('descripcion')?.value || '';
        const tipoTiempo = document.querySelector('input[name="tipo_tiempo"]:checked')?.value || 'sin_restricciones';
        const fechaInicio = document.getElementById('fecha_inicio')?.value || '';
        const fechaCierre = document.getElementById('fecha_cierre')?.value || '';
        
        if (titulo || descripcion) {
            const borrador = {
                titulo,
                descripcion,
                tipoTiempo,
                fechaInicio,
                fechaCierre,
                timestamp: new Date().toISOString()
            };
            localStorage.setItem('formato_borrador', JSON.stringify(borrador));
            console.log('📝 Borrador guardado');
        }
    },

    /**
     * Carga un borrador desde localStorage
     */
    cargarBorrador: function() {
        const borrador = localStorage.getItem('formato_borrador');
        if (!borrador) return;
        
        try {
            const datos = JSON.parse(borrador);
            const haceMasDe24Horas = new Date() - new Date(datos.timestamp) > 24 * 60 * 60 * 1000;
            
            if (!haceMasDe24Horas) {
                const cargar = confirm('📝 ¿Deseas recuperar el borrador guardado?');
                if (cargar) {
                    document.getElementById('titulo').value = datos.titulo || '';
                    document.getElementById('descripcion').value = datos.descripcion || '';
                    
                    if (datos.tipoTiempo === 'con_restricciones') {
                        document.getElementById('con_restricciones').click();
                        document.getElementById('fecha_inicio').value = datos.fechaInicio || '';
                        document.getElementById('fecha_cierre').value = datos.fechaCierre || '';
                    }
                } else {
                    this.limpiarBorrador();
                }
            } else {
                this.limpiarBorrador();
            }
        } catch (e) {
            console.error('Error al cargar borrador:', e);
            this.limpiarBorrador();
        }
    },

    /**
     * Limpia el borrador de localStorage
     */
    limpiarBorrador: function() {
        localStorage.removeItem('formato_borrador');
        console.log('🗑️ Borrador eliminado');
    },

    /**
     * Escapa HTML para prevenir XSS
     * @param {string} text - Texto a escapar
     * @returns {string} Texto escapado
     */
    escapeHtml: function(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    /**
     * Formatea una fecha para mostrar
     * @param {string} fecha - Fecha en formato ISO
     * @returns {string} Fecha formateada
     */
    formatearFecha: function(fecha) {
        if (!fecha) return '';
        const d = new Date(fecha);
        return d.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    FormatoForDe144.init();
});

// Agregar estilos para las animaciones si no existen
if (!document.querySelector('#animaciones-custom')) {
    const style = document.createElement('style');
    style.id = 'animaciones-custom';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .mensaje-exito, .mensaje-error {
            transition: all 0.3s ease;
        }
    `;
    document.head.appendChild(style);
}

// Exportar para uso global
window.FormatoForDe144 = FormatoForDe144;
>>>>>>> Stashed changes
