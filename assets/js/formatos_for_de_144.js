// assets/js/formatos_for_de_144.js - VERSIÓN COMPLETA CON EDICIÓN

let formatoIdAEliminar = null;
let formatoActual = null;

// Popup crear
function abrirPopupCrear() {
    document.getElementById('popupCrear').style.display = 'flex';
    document.querySelector('#formNuevoFormato input[name="titulo"]').focus();
}

function cerrarPopup() {
    document.getElementById('popupCrear').style.display = 'none';
    document.getElementById('formNuevoFormato').reset();
    
    // Ocultar campos de fecha si estaban visibles
    const camposFecha = document.getElementById('camposFecha');
    if (camposFecha) {
        camposFecha.style.display = 'none';
    }
    document.getElementById('sin_restricciones').checked = true;
}

// Popup editar
function abrirPopupEditar(formato) {
    formatoActual = formato;
    
    // Llenar el formulario de edición con los datos del formato
    document.getElementById('edit_id').value = formato.id;
    document.getElementById('edit_titulo').value = formato.titulo;
    document.getElementById('edit_descripcion').value = formato.descripcion || '';
    
    // Configurar tipo de tiempo
    if (formato.fecha_inicio && formato.fecha_cierre) {
        document.getElementById('edit_con_restricciones').checked = true;
        document.getElementById('edit_camposFecha').style.display = 'block';
        document.getElementById('edit_fecha_inicio').value = formato.fecha_inicio ? formato.fecha_inicio.slice(0, 16) : '';
        document.getElementById('edit_fecha_cierre').value = formato.fecha_cierre ? formato.fecha_cierre.slice(0, 16) : '';
    } else {
        document.getElementById('edit_sin_restricciones').checked = true;
        document.getElementById('edit_camposFecha').style.display = 'none';
        document.getElementById('edit_fecha_inicio').value = '';
        document.getElementById('edit_fecha_cierre').value = '';
    }
    
    document.getElementById('popupEditar').style.display = 'flex';
    document.getElementById('edit_titulo').focus();
}

function cerrarPopupEditar() {
    document.getElementById('popupEditar').style.display = 'none';
    document.getElementById('formEditarFormato').reset();
    document.getElementById('edit_sin_restricciones').checked = true;
    document.getElementById('edit_camposFecha').style.display = 'none';
    formatoActual = null;
}

// Popup confirmar eliminar
function confirmarEliminar(id, nombre) {
    event.stopPropagation();
    formatoIdAEliminar = id;
    
    const mensaje = document.getElementById('mensajeConfirmacion');
    mensaje.innerHTML = `¿Estás seguro de eliminar el formato "<strong>${nombre}</strong>"?<br>
                        <small style="color: #e74c3c;">Esta acción no se puede deshacer.</small>`;
    
    document.getElementById('popupConfirmar').style.display = 'flex';
}

function cerrarConfirmar() {
    document.getElementById('popupConfirmar').style.display = 'none';
    formatoIdAEliminar = null;
}

// Configurar botón de eliminar
function configurarEliminar() {
    const btnEliminar = document.getElementById('btnEliminarConfirmar');
    if (btnEliminar) {
        btnEliminar.onclick = function() {
            if (formatoIdAEliminar) {
                eliminarFormato(formatoIdAEliminar);
            }
        };
    }
}

// Función para eliminar formato
function eliminarFormato(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = basePath + '/index.php?controller=FORDE144&action=eliminar';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'id';
    input.value = id;
    
    // Agregar token CSRF si existe
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

// Cerrar popups al hacer clic fuera
function configurarClicExterno() {
    document.querySelectorAll('.popup-overlay').forEach(popup => {
        popup.addEventListener('click', function(e) {
            if (e.target === this) {
                if (popup.id === 'popupCrear') {
                    cerrarPopup();
                } else if (popup.id === 'popupConfirmar') {
                    cerrarConfirmar();
                } else if (popup.id === 'popupEditar') {
                    cerrarPopupEditar();
                }
            }
        });
    });
}

// Cerrar con ESC
function configurarTeclaEscape() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarPopup();
            cerrarConfirmar();
            cerrarPopupEditar();
        }
    });
}

// Auto-guardar en localStorage mientras se escribe
function configurarAutoGuardado() {
    const formulario = document.getElementById('formNuevoFormato');
    if (formulario) {
        const tituloInput = formulario.querySelector('input[name="titulo"]');
        const descripcionTextarea = formulario.querySelector('textarea[name="descripcion"]');
        const tipoTiempoRadios = formulario.querySelectorAll('input[name="tipo_tiempo"]');
        const fechaInicio = formulario.querySelector('input[name="fecha_inicio"]');
        const fechaCierre = formulario.querySelector('input[name="fecha_cierre"]');
        
        const guardarBorrador = () => {
            let tipoTiempo = 'sin_restricciones';
            tipoTiempoRadios.forEach(radio => {
                if (radio.checked) tipoTiempo = radio.value;
            });
            
            const borrador = {
                titulo: tituloInput.value,
                descripcion: descripcionTextarea.value,
                tipo_tiempo: tipoTiempo,
                fecha_inicio: fechaInicio?.value || '',
                fecha_cierre: fechaCierre?.value || '',
                timestamp: new Date().toISOString()
            };
            
            if (borrador.titulo || borrador.descripcion) {
                localStorage.setItem('formato_for_de_144_borrador', JSON.stringify(borrador));
                console.log('Borrador guardado en localStorage');
            }
        };
        
        tituloInput.addEventListener('input', guardarBorrador);
        descripcionTextarea.addEventListener('input', guardarBorrador);
        tipoTiempoRadios.forEach(radio => radio.addEventListener('change', guardarBorrador));
        if (fechaInicio) fechaInicio.addEventListener('change', guardarBorrador);
        if (fechaCierre) fechaCierre.addEventListener('change', guardarBorrador);
        
        // Cargar borrador al abrir popup
        setTimeout(() => {
            const borradorGuardado = localStorage.getItem('formato_for_de_144_borrador');
            if (borradorGuardado) {
                try {
                    const borrador = JSON.parse(borradorGuardado);
                    const haceMasDe24Horas = new Date() - new Date(borrador.timestamp) > 24 * 60 * 60 * 1000;
                    
                    if (!haceMasDe24Horas && confirm('¿Recuperar el borrador guardado anteriormente?')) {
                        tituloInput.value = borrador.titulo || '';
                        descripcionTextarea.value = borrador.descripcion || '';
                        
                        // Restaurar tipo de tiempo
                        tipoTiempoRadios.forEach(radio => {
                            if (radio.value === borrador.tipo_tiempo) {
                                radio.checked = true;
                                if (radio.value === 'con_restricciones') {
                                    document.getElementById('camposFecha').style.display = 'block';
                                }
                            }
                        });
                        
                        if (fechaInicio) fechaInicio.value = borrador.fecha_inicio || '';
                        if (fechaCierre) fechaCierre.value = borrador.fecha_cierre || '';
                    }
                } catch (e) {
                    console.error('Error al cargar borrador:', e);
                }
            }
        }, 100);
        
        // Limpiar localStorage al enviar
        formulario.addEventListener('submit', () => {
            localStorage.removeItem('formato_for_de_144_borrador');
        });
    }
}

// Validar formulario antes de enviar
function configurarValidacionFormulario() {
    const formulario = document.getElementById('formNuevoFormato');
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            const tituloInput = this.querySelector('input[name="titulo"]');
            const titulo = tituloInput.value.trim();
            
            if (!titulo) {
                e.preventDefault();
                alert('Por favor, ingresa un título para el formato');
                tituloInput.focus();
                tituloInput.style.borderColor = '#e74c3c';
                
                setTimeout(() => {
                    tituloInput.style.borderColor = '';
                }, 2000);
                
                return false;
            }
            
            // Validar fechas si se seleccionó con restricciones
            const conRestricciones = document.getElementById('con_restricciones').checked;
            if (conRestricciones) {
                const fechaInicio = document.getElementById('fecha_inicio').value;
                const fechaCierre = document.getElementById('fecha_cierre').value;
                
                if (!fechaInicio || !fechaCierre) {
                    e.preventDefault();
                    alert('Por favor, completa las fechas de inicio y cierre');
                    return false;
                }
                
                if (new Date(fechaInicio) > new Date(fechaCierre)) {
                    e.preventDefault();
                    alert('La fecha de inicio no puede ser mayor a la fecha de cierre');
                    return false;
                }
            }
            
            // Mostrar indicador de carga
            const btnGuardar = document.querySelector('.btn-guardar');
            if (btnGuardar) {
                const originalText = btnGuardar.innerHTML;
                btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                btnGuardar.disabled = true;
                
                setTimeout(() => {
                    btnGuardar.innerHTML = originalText;
                    btnGuardar.disabled = false;
                }, 3000);
            }
            
            return true;
        });
    }
}

// Efectos hover en tarjetas
function configurarEfectosTarjetas() {
    const tarjetas = document.querySelectorAll('.card:not(.card-agregar):not(.disabled)');
    
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

// Animación para mensajes
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

// Contador de caracteres para textarea
function configurarContadorCaracteres() {
    const textarea = document.querySelector('#formNuevoFormato textarea[name="descripcion"]');
    if (textarea) {
        const contador = document.createElement('div');
        contador.className = 'contador-caracteres';
        contador.style.fontSize = '12px';
        contador.style.color = '#95a5a6';
        contador.style.textAlign = 'right';
        contador.style.marginTop = '5px';
        contador.textContent = '0/500 caracteres';
        
        textarea.parentNode.insertBefore(contador, textarea.nextSibling);
        
        textarea.addEventListener('input', function() {
            const longitud = this.value.length;
            contador.textContent = `${longitud}/500 caracteres`;
            
            if (longitud > 450) {
                contador.style.color = '#e74c3c';
            } else if (longitud > 400) {
                contador.style.color = '#f39c12';
            } else {
                contador.style.color = '#95a5a6';
            }
        });
    }
}

// Inicializar todo cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Obtener basePath desde variable global o meta tag
    if (typeof basePath === 'undefined') {
        const metaBasePath = document.querySelector('meta[name="base-path"]');
        if (metaBasePath) {
            basePath = metaBasePath.getAttribute('content');
        } else {
            // Intentar deducir la base path
            basePath = window.location.pathname.split('/').slice(0, -1).join('/') || '';
        }
    }
    
    // Configurar todas las funciones
    configurarEliminar();
    configurarClicExterno();
    configurarTeclaEscape();
    configurarAutoGuardado();
    configurarValidacionFormulario();
    configurarEfectosTarjetas();
    configurarContadorCaracteres();
    animarMensajes();
    
    // Agregar confirmación antes de salir si hay cambios
    window.addEventListener('beforeunload', function(e) {
        const borrador = localStorage.getItem('formato_for_de_144_borrador');
        if (borrador) {
            e.preventDefault();
            e.returnValue = 'Tienes un borrador sin guardar. ¿Seguro que quieres salir?';
            return e.returnValue;
        }
    });
    
    console.log('Sistema de Formatos FOR-DE-144 inicializado correctamente');
});

// Exportar funciones para uso global
window.FormatoForDe144 = {
    abrirPopupCrear,
    cerrarPopup,
    confirmarEliminar,
    cerrarConfirmar,
    eliminarFormato,
    abrirPopupEditar,
    cerrarPopupEditar
};