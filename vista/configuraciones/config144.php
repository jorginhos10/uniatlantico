<?php
// vista/configuraciones/config144.php
$basePath = Config::getBasePath();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA 144 - Configuración de Años</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --color-primary: #2C3E50;
            --color-primary-light: #34495E;
            --color-success: #27AE60;
            --color-warning: #F39C12;
            --color-danger: #E74C3C;
            --color-info: #3498DB;
            --color-bg: #F8F9FA;
        }
        
        body {
            background-color: var(--color-bg);
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        
        .header-info {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 20px rgba(44,62,80,0.2);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            color: var(--color-primary);
            font-weight: 600;
            border-bottom: 2px solid var(--color-primary-light);
        }
        
        /* Estilos para drag & drop */
        .sortable-ghost {
            opacity: 0.5;
            background: #c8e6c9 !important;
            border: 2px dashed var(--color-success) !important;
        }
        
        .sortable-drag {
            opacity: 0.8;
            transform: rotate(2deg);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .sortable-rows tbody tr {
            cursor: grab;
            transition: all 0.2s ease;
        }
        
        .sortable-rows tbody tr:active {
            cursor: grabbing;
        }
        
        .sortable-rows tbody tr:hover {
            background-color: #f0f7ff;
            transform: scale(1.002);
            box-shadow: 0 2px 8px rgba(44,62,80,0.1);
        }
        
        .drag-handle {
            color: var(--color-primary);
            opacity: 0.5;
            transition: opacity 0.2s ease;
            cursor: grab;
            font-size: 1.2rem;
            margin-right: 5px;
            display: inline-block;
        }
        
        tr:hover .drag-handle {
            opacity: 1;
        }
        
        .btn-action {
            padding: 5px 10px;
            margin: 0 2px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .badge-estado {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-activo {
            background: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);
            color: white;
        }
        
        .badge-inactivo {
            background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
            color: white;
        }
        
        .badge-orden {
            background-color: var(--color-primary);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 15px 20px;
        }
        
        .modal-header .btn-close {
            filter: invert(1) brightness(2);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 0.25rem rgba(44,62,80,0.15);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px;
            background: #f8f9fc;
            border-radius: 12px;
            border: 1px dashed #dee2e6;
        }
        
        .empty-state i {
            color: #adb5bd;
        }
        
        .fade-out {
            opacity: 0;
            transform: translateX(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        
        .fade-in {
            opacity: 1;
            transform: translateX(0);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .acciones-orden {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid var(--color-success);
        }
        
        .btn-orden {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="header-info">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-cogs me-3"></i>CONFIG-144
                    </h1>
                    <h4 class="mb-0">Configuración de Años para el Sistema 144</h4>
                    <p class="mb-0 mt-2 opacity-75">
                        <i class="fas fa-info-circle me-1"></i>
                        Gestione los años disponibles para formulación y seguimiento
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="<?php echo $basePath; ?>/configuraciones" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                    <button class="btn btn-success" onclick="abrirModalCrear()">
                        <i class="fas fa-plus me-1"></i>Nuevo Año
                    </button>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Total Años</h5>
                        <h2 class="mb-0" id="stats-total">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Activos</h5>
                        <h2 class="mb-0 text-success" id="stats-activos">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Inactivos</h5>
                        <h2 class="mb-0 text-danger" id="stats-inactivos">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Año más reciente</h5>
                        <h2 class="mb-0" id="stats-max">-</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones de ordenamiento -->
        <div class="acciones-orden d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-arrows-alt me-2 text-success"></i>
                <strong>Arrastra las filas</strong> para reordenar los años
                <span class="badge bg-success ms-2">NUEVO</span>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-success btn-orden me-2" onclick="guardarOrden()" id="btnGuardarOrden" style="display: none;">
                    <i class="fas fa-save me-1"></i>Guardar Orden
                </button>
                <button class="btn btn-sm btn-outline-secondary btn-orden" onclick="cancelarOrden()" id="btnCancelarOrden" style="display: none;">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
            </div>
        </div>

        <!-- Tabla de años con drag & drop -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i>Listado de Años
                <span class="badge bg-light text-dark ms-2" id="total-registros">0</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaAnos">
                        <thead>
                            <tr>
                                <th style="width: 50px"></th>
                                <th>ID</th>
                                <th>AÑO</th>
                                <th>ORDEN</th>
                                <th>ESTADO</th>
                                <th>FECHA CREACIÓN</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody id="tablaAnosBody" class="sortable-rows">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="py-4">
                                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                        <p class="mt-2">Cargando años...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="alert alert-info mt-4">
            <div class="row">
                <div class="col-md-8">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Nota:</strong> Los años marcados como <span class="badge badge-activo">Activo</span> aparecerán disponibles en los formularios de formulación. 
                    Los años inactivos no se mostrarán en los selects.
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-secondary">
                        <i class="fas fa-arrows-alt me-1"></i>Arrastrar para ordenar
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear/editar año -->
    <div class="modal fade" id="modalAno" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAnoTitulo">
                        <i class="fas fa-plus-circle me-2"></i>Nuevo Año
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formAno">
                    <input type="hidden" name="id" id="ano_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">AÑO *</label>
                            <input type="number" class="form-control" name="anio" id="ano_anio" required min="2000" max="2100" placeholder="Ej: 2025">
                            <small class="text-muted">El año debe estar entre 2000 y 2100</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ORDEN</label>
                            <input type="number" class="form-control" name="orden" id="ano_orden" placeholder="Ej: 1 (opcional)">
                            <small class="text-muted">Orden de visualización (si se deja vacío, se asignará automáticamente)</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="activo" id="ano_activo" value="1" checked>
                                <label class="form-check-label" for="ano_activo">
                                    <strong>ACTIVO</strong> - Mostrar en los formularios
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnGuardar">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles -->
    <div class="modal fade" id="modalVerAno" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detalles del Año</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="ver_contenido">
                    <!-- Se llena con JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar cambio de orden -->
    <div class="modal fade" id="modalGuardarOrden" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #27AE60 0%, #2ECC71 100%);">
                    <h5 class="modal-title"><i class="fas fa-save me-2"></i>Guardar Orden</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Deseas guardar el nuevo orden de los años?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        El orden se actualizará en la base de datos y afectará la visualización en los formularios.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="confirmarGuardarOrden()">
                        <i class="fas fa-check me-1"></i>Guardar Orden
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- SortableJS para Drag & Drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
        const basePath = '<?php echo $basePath; ?>';
        let sortable = null;
        let ordenModificado = false;

        // Cargar años al iniciar
        $(document).ready(function() {
            cargarAnos();
        });

        // Función para cargar años
        function cargarAnos() {
            $.ajax({
                url: basePath + '/config144/listar',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        actualizarTabla(response.anos);
                        actualizarEstadisticas(response.anos);
                        inicializarSortable();
                    } else {
                        Swal.fire('Error', 'No se pudieron cargar los años', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
                }
            });
        }

        // Inicializar SortableJS
        function inicializarSortable() {
            if (sortable) {
                sortable.destroy();
            }
            
            const el = document.getElementById('tablaAnosBody');
            if (!el) return;
            
            sortable = new Sortable(el, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                handle: '.drag-handle',
                onEnd: function() {
                    ordenModificado = true;
                    $('#btnGuardarOrden, #btnCancelarOrden').fadeIn();
                    
                    // Actualizar números de orden visuales
                    actualizarOrdenVisual();
                }
            });
        }

        // Actualizar números de orden en la tabla
        function actualizarOrdenVisual() {
            $('#tablaAnosBody tr').each(function(index) {
                $(this).find('.badge-orden').text(index + 1);
            });
        }

        // Actualizar tabla
        function actualizarTabla(anos) {
            let html = '';
            
            if (anos.length === 0) {
                html = `
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="empty-state py-4">
                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                <h6>No hay años registrados</h6>
                                <p class="text-muted small">Haz clic en "Nuevo Año" para comenzar</p>
                            </div>
                        </td>
                    </tr>
                `;
            } else {
                anos.forEach(function(ano, index) {
                    const estadoClass = ano.activo == 1 ? 'badge-activo' : 'badge-inactivo';
                    const estadoIcono = ano.activo == 1 ? 'check-circle' : 'times-circle';
                    const estadoTexto = ano.activo == 1 ? 'Activo' : 'Inactivo';
                    
                    html += `
                        <tr id="fila-${ano.id}" data-id="${ano.id}" data-orden="${ano.orden || ano.anio}">
                            <td class="text-center">
                                <span class="drag-handle">
                                    <i class="fas fa-grip-vertical"></i>
                                </span>
                            </td>
                            <td><strong>#${ano.id}</strong></td>
                            <td>${ano.anio}</td>
                            <td>
                                <span class="badge-orden">${index + 1}</span>
                                <small class="text-muted ms-2">(ID: ${ano.id})</small>
                            </td>
                            <td>
                                <span class="badge-estado ${estadoClass}">
                                    <i class="fas fa-${estadoIcono} me-1"></i>
                                    ${estadoTexto}
                                </span>
                            </td>
                            <td>
                                <i class="far fa-calendar-alt me-1 text-muted"></i>
                                ${new Date(ano.created_at).toLocaleString('es-CO')}
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning btn-action" onclick="editarAno(${ano.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                ${ano.activo == 1 ? 
                                    `<button class="btn btn-sm btn-danger btn-action" onclick="cambiarEstado(${ano.id}, 0)" title="Desactivar">
                                        <i class="fas fa-ban"></i>
                                    </button>` : 
                                    `<button class="btn btn-sm btn-success btn-action" onclick="cambiarEstado(${ano.id}, 1)" title="Activar">
                                        <i class="fas fa-check-circle"></i>
                                    </button>`
                                }
                                <button class="btn btn-sm btn-info btn-action" onclick="verAno(${ano.id})" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-action" onclick="eliminarAno(${ano.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            
            $('#tablaAnosBody').html(html);
            $('#total-registros').text(anos.length);
            
            // Reinicializar Sortable después de actualizar la tabla
            setTimeout(inicializarSortable, 100);
        }

        // Actualizar estadísticas
        function actualizarEstadisticas(anos) {
            const total = anos.length;
            const activos = anos.filter(a => a.activo == 1).length;
            const inactivos = total - activos;
            const maxAno = anos.length > 0 ? Math.max(...anos.map(a => parseInt(a.anio))) : '-';
            
            $('#stats-total').text(total);
            $('#stats-activos').text(activos);
            $('#stats-inactivos').text(inactivos);
            $('#stats-max').text(maxAno);
        }

        // Guardar orden
        function guardarOrden() {
            if (!ordenModificado) {
                Swal.fire('Sin cambios', 'No has modificado el orden de los años', 'info');
                return;
            }
            
            $('#modalGuardarOrden').modal('show');
        }

        // Confirmar guardar orden
        function confirmarGuardarOrden() {
            const ordenes = [];
            
            $('#tablaAnosBody tr').each(function(index) {
                const id = $(this).data('id');
                ordenes.push({
                    id: id,
                    orden: index + 1
                });
            });
            
            $.ajax({
                url: basePath + '/config144/actualizarOrden',
                type: 'POST',
                data: { ordenes: JSON.stringify(ordenes) },
                dataType: 'json',
                success: function(response) {
                    $('#modalGuardarOrden').modal('hide');
                    
                    if (response.success) {
                        Swal.fire('¡Orden guardado!', response.message, 'success');
                        ordenModificado = false;
                        $('#btnGuardarOrden, #btnCancelarOrden').fadeOut();
                        cargarAnos(); // Recargar para mostrar los nuevos órdenes
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    $('#modalGuardarOrden').modal('hide');
                    Swal.fire('Error', 'Error al guardar el orden', 'error');
                }
            });
        }

        // Cancelar orden
        function cancelarOrden() {
            Swal.fire({
                title: '¿Cancelar cambios?',
                text: 'Se perderán los cambios en el orden de los años',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#E74C3C',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, seguir editando'
            }).then((result) => {
                if (result.isConfirmed) {
                    ordenModificado = false;
                    $('#btnGuardarOrden, #btnCancelarOrden').fadeOut();
                    cargarAnos(); // Recargar orden original
                }
            });
        }

        // Abrir modal para crear nuevo año
        function abrirModalCrear() {
            $('#modalAnoTitulo').html('<i class="fas fa-plus-circle me-2"></i>Nuevo Año');
            $('#ano_id').val('');
            $('#ano_anio').val('');
            $('#ano_orden').val('');
            $('#ano_activo').prop('checked', true);
            $('#modalAno').modal('show');
        }

        // Abrir modal para editar año
        function editarAno(id) {
            $.ajax({
                url: basePath + '/config144/get?id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const a = response.ano;
                        $('#modalAnoTitulo').html('<i class="fas fa-edit me-2"></i>Editar Año');
                        $('#ano_id').val(a.id);
                        $('#ano_anio').val(a.anio);
                        $('#ano_orden').val(a.orden);
                        $('#ano_activo').prop('checked', a.activo == 1);
                        $('#modalAno').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al cargar los datos', 'error');
                }
            });
        }

        // Ver detalles de un año
        function verAno(id) {
            $.ajax({
                url: basePath + '/config144/get?id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const a = response.ano;
                        const estado = a.activo == 1 ? 
                            '<span class="badge badge-activo"><i class="fas fa-check-circle me-1"></i>Activo</span>' : 
                            '<span class="badge badge-inactivo"><i class="fas fa-times-circle me-1"></i>Inactivo</span>';
                        
                        const fecha = new Date(a.created_at).toLocaleString('es-CO');
                        
                        const html = `
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>ID:</strong><br>
                                    #${a.id}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>AÑO:</strong><br>
                                    ${a.anio}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>ORDEN:</strong><br>
                                    ${a.orden || 'No asignado'}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>ESTADO:</strong><br>
                                    ${estado}
                                </div>
                                <div class="col-12 mb-3">
                                    <strong>FECHA CREACIÓN:</strong><br>
                                    <i class="far fa-calendar-alt me-1"></i> ${fecha}
                                </div>
                            </div>
                        `;
                        $('#ver_contenido').html(html);
                        $('#modalVerAno').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }

        // Cambiar estado (activar/desactivar)
        function cambiarEstado(id, activo) {
            const accion = activo ? 'activar' : 'desactivar';
            Swal.fire({
                title: `¿${activo ? 'Activar' : 'Desactivar'} año?`,
                text: `El año pasará a estado ${activo ? 'ACTIVO' : 'INACTIVO'}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: activo ? '#27AE60' : '#E74C3C',
                confirmButtonText: `Sí, ${accion}`,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/config144/cambiarEstado',
                        type: 'POST',
                        data: { id: id, activo: activo ? 1 : 0 },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('¡Completado!', response.message, 'success');
                                cargarAnos(); // Recargar la tabla
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        // Eliminar año
        function eliminarAno(id) {
            Swal.fire({
                title: '¿Eliminar año?',
                text: 'Esta acción NO se puede deshacer. Verifique que el año no esté siendo usado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E74C3C',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: basePath + '/config144/eliminar',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('¡Eliminado!', response.message, 'success');
                                cargarAnos(); // Recargar la tabla
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        // Guardar año (crear o actualizar)
        $('#formAno').on('submit', function(e) {
            e.preventDefault();
            
            const id = $('#ano_id').val();
            const url = id ? basePath + '/config144/actualizar' : basePath + '/config144/crear';
            
            const data = {
                id: id,
                anio: $('#ano_anio').val(),
                orden: $('#ano_orden').val(),
                activo: $('#ano_activo').is(':checked') ? 1 : 0
            };
            
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('¡Guardado!', response.message, 'success');
                        $('#modalAno').modal('hide');
                        cargarAnos(); // Recargar la tabla
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
                }
            });
        });

        // Validación del año al escribir
        $('#ano_anio').on('input', function() {
            const anio = parseInt($(this).val());
            if (anio < 2000 || anio > 2100) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Detectar tecla Escape para cancelar orden
        $(document).keyup(function(e) {
            if (e.key === "Escape" && ordenModificado) {
                cancelarOrden();
            }
        });
    </script>
</body>
</html>