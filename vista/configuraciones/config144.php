<?php
// vista/configuraciones/config144.php
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'CONFIG-144 — Configuración de Años';
$paginaActual = 'configuraciones/config144';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

ob_start();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
        :root {
            --color-primary: #007AFF;
            --color-primary-light: #0A84FF;
            --color-success: #34C759;
            --color-warning: #FF9500;
            --color-danger: #FF3B30;
            --color-info: #007AFF;
            --color-bg: #F2F2F7;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
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
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            margin-bottom: 20px;
        }

        .card-header {
            background: var(--color-bg);
            color: #000000;
            border-radius: 16px 16px 0 0 !important;
            border-bottom: 1px solid rgba(60,60,67,.12);
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
        
        .sortable-ghost {
            opacity: 0.5;
            background: rgba(52,199,89,.15) !important;
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
            background-color: rgba(0,122,255,.06);
            transform: scale(1.002);
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
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
            background: #34C759;
            color: white;
        }

        .badge-inactivo {
            background: #FF3B30;
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
            border-radius: 16px;
            border: none;
            overflow: hidden;
        }

        .modal-header {
            background: #FFFFFF;
            color: #000000;
            border-radius: 16px 16px 0 0;
            border-bottom: 1px solid rgba(60,60,67,.12);
            padding: 18px 22px;
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

        .nav-tabs .nav-link {
            color: var(--color-primary);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .nav-tabs .nav-link.active {
            background-color: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
        }
        
        .tab-content {
            background-color: white;
            border: 1px solid #dee2e6;
            border-top: none;
            padding: 20px;
            border-radius: 0 0 8px 8px;
        }

        #resumenDistribucionBody tr {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        #resumenDistribucionBody tr:hover {
            background-color: rgba(0,122,255,.06);
            transform: scale(1.002);
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        #resumenDistribucionBody tr td:first-child {
            border-left: 3px solid transparent;
            transition: border-left-color 0.2s ease;
        }

        #resumenDistribucionBody tr:hover td:first-child {
            border-left-color: var(--color-primary);
        }

        .breadcrumb-nav {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            border-left: 4px solid var(--color-primary);
            font-size: 0.9rem;
        }

        .breadcrumb-nav i {
            color: var(--color-primary);
        }

        .breadcrumb-nav .separator {
            margin: 0 8px;
            color: #6c757d;
            font-weight: bold;
        }

        .nivel-indicador {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
        }

        .nivel-linea {
            background: #9C27B0;
        }

        .nivel-motor {
            background: #FF9800;
        }

        .nivel-proyecto {
            background: #34C759;
        }
    </style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>
    <div class="container-fluid">
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

        <div class="alert alert-info mt-4">
            <div class="row">
                <div class="col-md-8">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Nota:</strong> Los años marcados como <span class="badge badge-activo">Activo</span> aparecerán disponibles en los formularios.
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-secondary">
                        <i class="fas fa-arrows-alt me-1"></i>Arrastrar para ordenar
                    </span>
                </div>
            </div>
        </div>
    </div>

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
                            <small class="text-muted">Orden de visualización</small>
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

    <div class="modal fade" id="modalVerAno" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detalles del Año</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="ver_contenido"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalGuardarOrden" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-save me-2"></i>Guardar Orden</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Deseas guardar el nuevo orden de los años?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        El orden se actualizará en la base de datos.
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

    <div class="modal fade" id="modalDistribucionPorcentajes" tabindex="-1" data-bs-backdrop="static" data-bs-size="xl">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: #007AFF;">
                    <h5 class="modal-title" style="color: white;">
                        <i class="fas fa-percent me-2"></i>DISTRIBUCIÓN PORCENTUAL POR LÍNEAS ESTRATÉGICAS
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">SELECCIONAR AÑO</label>
                            <select class="form-select" id="selectAnioDistribucion" onchange="cargarDistribucionPorcentajes()">
                                <option value="">Cargando años...</option>
                            </select>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="alert alert-info mb-0 py-2">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Total:</strong> <span id="totalPorcentaje">0%</span>
                                <span class="ms-3" id="totalValidacion" style="display: none;">
                                    <i class="fas fa-check-circle text-success"></i> 100% OK
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="breadcrumb-nav" id="navegacionNiveles" style="display: none;">
                                <i class="fas fa-sitemap me-2"></i>
                                <span id="rutaNavegacion"></span>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs" id="lineasTabs" role="tablist"></ul>

                    <div class="tab-content p-4 border border-top-0 rounded-bottom" id="lineasTabContent" style="background-color: #f8f9fa;">
                        <div class="text-center py-5" id="cargandoLineas">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="text-muted">Cargando líneas estratégicas...</p>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <i class="fas fa-chart-pie me-2"></i>RESUMEN DE DISTRIBUCIÓN
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="tablaResumenDistribucion">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>LÍNEA ESTRATÉGICA / MOTORES / PROYECTOS</th>
                                                    <th width="200">PORCENTAJE</th>
                                                    <th width="200">ACCIONES</th>
                                                </tr>
                                            </thead>
                                            <tbody id="resumenDistribucionBody">
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-3">
                                                        Seleccione un año para ver la distribución
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th class="text-end">TOTAL:</th>
                                                    <th id="totalResumen">0%</th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="guardarDistribucionPorcentajes()" id="btnGuardarDistribucion">
                        <i class="fas fa-save me-1"></i>Guardar Distribución
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarPorcentaje" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>EDITAR PORCENTAJE DE LÍNEA
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarPorcentaje">
                        <input type="hidden" id="editLineaId">
                        <input type="hidden" id="editAnio">
                        <div class="mb-3">
                            <label class="form-label fw-bold">LÍNEA ESTRATÉGICA</label>
                            <p class="form-control-plaintext" id="editLineaNombre"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">PORCENTAJE</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="editPorcentaje" step="0.01" min="0" max="100" placeholder="0.00">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Ingrese un valor entre 0 y 100</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="guardarPorcentajeLinea()">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
        const basePath = '<?php echo $basePath; ?>';
        let sortable = null;
        let ordenModificado = false;
        let lineasEstrategicas = [];
        let distribucionActual = {};
        
        let modoVisualizacion = 'lineas';
        let lineaSeleccionada = null;
        let motorSeleccionado = null;
        let motoresCache = {};
        let proyectosCache = {};

        $(document).ready(function() {
            cargarAnos();
        });

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

        function inicializarSortable() {
            if (sortable) sortable.destroy();
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
                    actualizarOrdenVisual();
                }
            });
        }

        function actualizarOrdenVisual() {
            $('#tablaAnosBody tr').each(function(index) {
                $(this).find('.badge-orden').text(index + 1);
            });
        }

        function actualizarTabla(anos) {
            let html = '';
            if (anos.length === 0) {
                html = `<tr><td colspan="7" class="text-center"><div class="empty-state py-4"><i class="fas fa-calendar-times fa-3x mb-3"></i><h6>No hay años registrados</h6><p class="text-muted small">Haz clic en "Nuevo Año" para comenzar</p></div></td></tr>`;
            } else {
                anos.forEach(function(ano, index) {
                    const estadoClass = ano.activo == 1 ? 'badge-activo' : 'badge-inactivo';
                    const estadoIcono = ano.activo == 1 ? 'check-circle' : 'times-circle';
                    const estadoTexto = ano.activo == 1 ? 'Activo' : 'Inactivo';
                    
                    html += `
                        <tr id="fila-${ano.id}" data-id="${ano.id}" data-orden="${ano.orden || ano.anio}">
                            <td class="text-center"><span class="drag-handle"><i class="fas fa-grip-vertical"></i></span></td>
                            <td><strong>#${ano.id}</strong></td>
                            <td>${ano.anio}</td>
                            <td><span class="badge-orden">${index + 1}</span> <small class="text-muted ms-2">(ID: ${ano.id})</small></td>
                            <td><span class="badge-estado ${estadoClass}"><i class="fas fa-${estadoIcono} me-1"></i>${estadoTexto}</span></td>
                            <td><i class="far fa-calendar-alt me-1 text-muted"></i>${new Date(ano.created_at).toLocaleString('es-CO')}</td>
                            <td>
                                <button class="btn btn-sm btn-info btn-action" onclick="abrirDistribucionPorAnio(${ano.anio})" data-bs-toggle="tooltip" data-bs-placement="top" title="Distribución de Porcentajes"><i class="fas fa-percent"></i></button>
                                <button class="btn btn-sm btn-warning btn-action" onclick="editarAno(${ano.id})" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-secondary btn-action" onclick="duplicarAno(${ano.id}, ${ano.anio})" data-bs-toggle="tooltip" data-bs-placement="top" title="Duplicar año"><i class="fas fa-copy"></i></button>
                                ${ano.activo == 1 ?
                                    `<button class="btn btn-sm btn-danger btn-action" onclick="cambiarEstado(${ano.id}, 0)" data-bs-toggle="tooltip" data-bs-placement="top" title="Desactivar"><i class="fas fa-ban"></i></button>` :
                                    `<button class="btn btn-sm btn-success btn-action" onclick="cambiarEstado(${ano.id}, 1)" data-bs-toggle="tooltip" data-bs-placement="top" title="Activar"><i class="fas fa-check-circle"></i></button>`
                                }
                                <button class="btn btn-sm btn-info btn-action" onclick="verAno(${ano.id})" data-bs-toggle="tooltip" data-bs-placement="top" title="Ver detalles"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-sm btn-danger btn-action" onclick="eliminarAno(${ano.id})" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                });
            }
            // Destruir tooltips anteriores antes de reemplazar el HTML
            document.querySelectorAll('#tablaAnosBody [data-bs-toggle="tooltip"]').forEach(el => {
                const tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.dispose();
            });

            $('#tablaAnosBody').html(html);
            $('#total-registros').text(anos.length);

            // Inicializar tooltips de los nuevos botones
            document.querySelectorAll('#tablaAnosBody [data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el, { trigger: 'hover' });
            });

            setTimeout(inicializarSortable, 100);
        }

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

        function guardarOrden() {
            if (!ordenModificado) {
                Swal.fire('Sin cambios', 'No has modificado el orden de los años', 'info');
                return;
            }
            $('#modalGuardarOrden').modal('show');
        }

        function confirmarGuardarOrden() {
            const ordenes = [];
            $('#tablaAnosBody tr').each(function(index) {
                ordenes.push({ id: $(this).data('id'), orden: index + 1 });
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
                        cargarAnos();
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
                    cargarAnos();
                }
            });
        }

        function abrirModalCrear() {
            $('#modalAnoTitulo').html('<i class="fas fa-plus-circle me-2"></i>Nuevo Año');
            $('#ano_id').val('');
            $('#ano_anio').val('');
            $('#ano_orden').val('');
            $('#ano_activo').prop('checked', true);
            $('#modalAno').modal('show');
        }

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

        function verAno(id) {
            $.ajax({
                url: basePath + '/config144/get?id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const a = response.ano;
                        const estado = a.activo == 1 ? '<span class="badge badge-activo"><i class="fas fa-check-circle me-1"></i>Activo</span>' : '<span class="badge badge-inactivo"><i class="fas fa-times-circle me-1"></i>Inactivo</span>';
                        const fecha = new Date(a.created_at).toLocaleString('es-CO');
                        const html = `<div class="row"><div class="col-md-6 mb-3"><strong>ID:</strong><br>#${a.id}</div><div class="col-md-6 mb-3"><strong>AÑO:</strong><br>${a.anio}</div><div class="col-md-6 mb-3"><strong>ORDEN:</strong><br>${a.orden || 'No asignado'}</div><div class="col-md-6 mb-3"><strong>ESTADO:</strong><br>${estado}</div><div class="col-12 mb-3"><strong>FECHA CREACIÓN:</strong><br><i class="far fa-calendar-alt me-1"></i> ${fecha}</div></div>`;
                        $('#ver_contenido').html(html);
                        $('#modalVerAno').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al cargar los datos', 'error');
                }
            });
        }

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
                                cargarAnos();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        function eliminarAno(id) {
            Swal.fire({
                title: '¿Eliminar año?',
                text: 'Esta acción NO se puede deshacer.',
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
                                cargarAnos();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        function duplicarAno(id, anioOrigen) {
            Swal.fire({
                title: 'Duplicar año',
                html: `Copia el año <strong>${anioOrigen}</strong> con toda su distribución de porcentajes.<br>
                       <br><label style="font-size:13px;font-weight:600;">Nuevo año destino:</label>`,
                input: 'number',
                inputValue: anioOrigen + 1,
                inputAttributes: { min: 2000, max: 2100, step: 1, placeholder: 'Ej: ' + (anioOrigen + 1) },
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-copy me-1"></i> Duplicar',
                cancelButtonText: 'Cancelar',
                preConfirm: (anio) => {
                    anio = parseInt(anio);
                    if (!anio || anio < 2000 || anio > 2100) {
                        Swal.showValidationMessage('Ingresa un año válido entre 2000 y 2100');
                        return false;
                    }
                    return anio;
                }
            }).then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: basePath + '/config144/duplicar',
                    type: 'POST',
                    data: { id: id, anio: result.value },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: '¡Duplicado!', text: res.message, confirmButtonColor: '#28a745' });
                            cargarAnos();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                    }
                });
            });
        }

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
            
            Swal.fire({
                title: 'Guardando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let mensaje = response.message;
                        if (response.distribucion_copiada) {
                            mensaje += `<br><small class="text-success">✓ Se copió la distribución del año ${response.anio_origen}</small>`;
                        } else if (response.distribucion_copiada === false && response.id) {
                            mensaje += `<br><small class="text-warning">⚠ No se encontró un año anterior con distribución para copiar</small>`;
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: '¡Guardado!',
                            html: mensaje,
                            timer: 3000,
                            showConfirmButton: false
                        });
                        $('#modalAno').modal('hide');
                        cargarAnos();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
                }
            });
        });

        $('#ano_anio').on('input', function() {
            const anio = parseInt($(this).val());
            if (anio < 2000 || anio > 2100) $(this).addClass('is-invalid');
            else $(this).removeClass('is-invalid');
        });

        $(document).keyup(function(e) {
            if (e.key === "Escape" && ordenModificado) cancelarOrden();
        });

        function abrirDistribucionPorAnio(anio) {
            modoVisualizacion = 'lineas';
            lineaSeleccionada = null;
            motorSeleccionado = null;
            
            if (lineasEstrategicas.length === 0) {
                cargarLineasEstrategicasParaAnio(anio);
            } else {
                abrirModalDistribucionConAnio(anio);
            }
        }

        function cargarLineasEstrategicasParaAnio(anio) {
            $('#cargandoLineas').html(`<div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Cargando...</span></div><p class="text-muted">Cargando líneas estratégicas...</p>`);
            $.ajax({
                url: basePath + '/config144/getLineasEstrategicas',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        lineasEstrategicas = response.lineas;
                        crearPestanasLineas();
                        abrirModalDistribucionConAnio(anio);
                    } else {
                        Swal.fire('Error', 'No se pudieron cargar las líneas estratégicas', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'Error al cargar líneas estratégicas: ' + error, 'error');
                }
            });
        }

        function abrirModalDistribucionConAnio(anio) {
            $('.modal-title').html('<i class="fas fa-percent me-2"></i>DISTRIBUCIÓN PORCENTUAL POR LÍNEAS ESTRATÉGICAS');
            $('#modalDistribucionPorcentajes').modal('show');
            cargarAniosParaDistribucion(function() {
                $('#selectAnioDistribucion').val(anio.toString());
                cargarDistribucionPorcentajes();
            });
        }

        function cargarAniosParaDistribucion(callback) {
            $.ajax({
                url: basePath + '/config144/activos',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.anos) {
                        let options = '<option value="">Seleccione un año</option>';
                        response.anos.forEach(ano => {
                            options += `<option value="${ano.anio}">${ano.anio}</option>`;
                        });
                        $('#selectAnioDistribucion').html(options);
                        if (callback) callback();
                    }
                },
                error: function() {
                    $('#selectAnioDistribucion').html('<option value="">Error al cargar años</option>');
                }
            });
        }

        function crearPestanasLineas() {
            if (lineasEstrategicas.length === 0) {
                $('#lineasTabs').html('<li class="nav-item"><span class="nav-link disabled">No hay líneas estratégicas</span></li>');
                $('#lineasTabContent').html('<div class="text-center py-5 text-muted">No hay líneas estratégicas configuradas</div>');
                return;
            }

            let tabs = '';
            let content = '';

            lineasEstrategicas.forEach((linea, index) => {
                const active = index === 0 ? 'active show' : '';
                const selected = index === 0 ? 'true' : 'false';
                const tituloPestana = `${linea.codigo} - ${linea.nombre}`;
                
                tabs += `<li class="nav-item" role="presentation"><button class="nav-link ${active}" id="tab-${linea.id}" data-bs-toggle="tab" data-bs-target="#content-${linea.id}" type="button" role="tab" aria-controls="content-${linea.id}" aria-selected="${selected}">${tituloPestana}</button></li>`;

                content += `
                    <div class="tab-pane fade ${active}" id="content-${linea.id}" role="tabpanel" aria-labelledby="tab-${linea.id}">
                        <div class="text-center py-4" id="loading-${linea.id}"><div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Cargando...</span></div><p class="text-muted">Cargando distribución para ${tituloPestana}...</p></div>
                        <div id="content-distribucion-${linea.id}" style="display: none;">
                            <h5 class="mb-3">${tituloPestana}</h5>
                            <p class="text-muted mb-3">${linea.objetivo || 'Sin descripción'}</p>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-3 text-muted">Distribución por años</h6>
                                            <table class="table table-sm">
                                                <thead> transduction<th>AÑO</th><th>PORCENTAJE</th><th>ESTADO</th> </thead>
                                                <tbody id="tabla-distribucion-${linea.id}"> <td colspan="3" class="text-center text-muted">Seleccione un año para ver la distribución</td> </tbody>
                                              </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-3 text-muted">Acciones rápidas</h6>
                                            <button class="btn btn-sm btn-success w-100 mb-2" onclick="abrirModalEditarPorcentaje(${linea.id}, '${tituloPestana.replace(/'/g, "\\'")}')"><i class="fas fa-edit me-1"></i>Editar porcentaje</button>
                                            <button class="btn btn-sm btn-info w-100" onclick="verDetalleLinea(${linea.id})"><i class="fas fa-chart-line me-1"></i>Ver detalle</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            $('#lineasTabs').html(tabs);
            $('#lineasTabContent').html(content);
            $('#cargandoLineas').hide();
        }

        function cargarDistribucionPorcentajes() {
            const anio = $('#selectAnioDistribucion').val();
            if (!anio) {
                $('#resumenDistribucionBody').html(' <td colspan="3" class="text-center text-muted py-3">Seleccione un año para ver la distribución</td>');
                $('#totalResumen').text('0%');
                return;
            }

            lineasEstrategicas.forEach(linea => {
                $(`#loading-${linea.id}`).show();
                $(`#content-distribucion-${linea.id}`).hide();
            });

            $('#resumenDistribucionBody').html(' <td colspan="3" class="text-center py-4"><div class="spinner-border text-primary mb-2" role="status"><span class="visually-hidden">Cargando...</span></div><p class="text-muted">Cargando distribución para el año ' + anio + '...</p></td>');

            $.ajax({
                url: basePath + '/config144/getDataDistribucionPorAnio',
                type: 'GET',
                data: { anio: anio },
                dataType: 'json',
                timeout: 10000,
                success: function(response) {
                    if (response.success) {
                        distribucionActual = response.distribucion || {};
                        
                        if (modoVisualizacion === 'lineas') {
                            actualizarResumenDistribucion();
                        } else if (modoVisualizacion === 'motores' && lineaSeleccionada) {
                            cargarDataMotoresPorLinea(lineaSeleccionada.id, anio);
                        } else if (modoVisualizacion === 'proyectos' && motorSeleccionado) {
                            cargarDataProyectosPorMotor(motorSeleccionado.id, anio);
                        }
                        
                        actualizarTablasDistribucion(anio);
                        calcularTotalPorcentaje();
                    } else {
                        distribucionActual = {};
                        if (modoVisualizacion === 'lineas') {
                            actualizarResumenDistribucion(true);
                        }
                        actualizarTablasDistribucion(anio, true);
                    }
                },
                error: function() {
                    distribucionActual = {};
                    if (modoVisualizacion === 'lineas') {
                        actualizarResumenDistribucion(true);
                    }
                    actualizarTablasDistribucion(anio, true);
                }
            });
        }

        function actualizarTablasDistribucion(anio, vacio = false) {
            lineasEstrategicas.forEach(linea => {
                $(`#loading-${linea.id}`).hide();
                $(`#content-distribucion-${linea.id}`).show();

                let tablaHtml = '';
                if (vacio || !distribucionActual[linea.id]) {
                    tablaHtml = ` <td>${anio}</td><td class="text-warning">Sin asignar</td><td><span class="badge bg-warning">Pendiente</span></td>`;
                } else {
                    const data = distribucionActual[linea.id];
                    tablaHtml = ` <td>${anio}</td><td><strong>${data}%</strong></td><td><span class="badge ${data > 0 ? 'bg-success' : 'bg-secondary'}">${data > 0 ? 'Asignado' : 'Sin asignar'}</span></td>`;
                }
                $(`#tabla-distribucion-${linea.id}`).html(tablaHtml);
            });
        }

        function actualizarResumenDistribucion(vacio = false) {
            let html = '';
            let total = 0;

            if (vacio || Object.keys(distribucionActual).length === 0) {
                lineasEstrategicas.forEach(linea => {
                    const tituloPestana = `${linea.codigo} - ${linea.nombre}`;
                    html += `
                        <tr ondblclick="mostrarMotoresPorLinea(${linea.id}, '${tituloPestana.replace(/'/g, "\\'")}', event)" style="cursor: pointer;">
                            <td><div class="d-flex align-items-center"><i class="fas fa-bullseye text-primary me-2" style="opacity: 0.7;"></i><div><strong>${tituloPestana}</strong><br><small class="text-muted">Doble clic para ver motores</small></div></div></td>
                            <td><span class="badge bg-warning">Sin asignar</span></td>
                            <td><button class="btn btn-sm btn-success" onclick="abrirModalEditarPorcentaje(${linea.id}, '${tituloPestana.replace(/'/g, "\\'")}')"><i class="fas fa-plus"></i> Asignar</button></td>
                        </tr>
                    `;
                });
            } else {
                lineasEstrategicas.forEach(linea => {
                    const tituloPestana = `${linea.codigo} - ${linea.nombre}`;
                    const porcentaje = distribucionActual[linea.id] ? parseFloat(distribucionActual[linea.id]) : 0;
                    total += porcentaje;
                    
                    html += `
                        <tr ondblclick="mostrarMotoresPorLinea(${linea.id}, '${tituloPestana.replace(/'/g, "\\'")}', event)" style="cursor: pointer;">
                            <td><div class="d-flex align-items-center"><i class="fas fa-bullseye text-primary me-2" style="opacity: 0.7;"></i><div><strong>${tituloPestana}</strong><br><small class="text-muted">Doble clic para ver motores</small></div></div></td>
                            <td><span class="badge ${porcentaje > 0 ? 'bg-success' : 'bg-secondary'} fs-6 p-2" style="cursor: pointer;" onclick="event.stopPropagation(); abrirModalEditarPorcentaje(${linea.id}, '${tituloPestana.replace(/'/g, "\\'")}')">${porcentaje.toFixed(2)}%</span></td>
                            <td><button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); abrirModalEditarPorcentaje(${linea.id}, '${tituloPestana.replace(/'/g, "\\'")}')"><i class="fas fa-edit"></i> Editar</button></td>
                        </tr>
                    `;
                });
            }

            $('#resumenDistribucionBody').html(html);
            $('#totalResumen').text(total.toFixed(2) + '%');
        }

        function calcularTotalPorcentaje() {
            let total = 0;
            lineasEstrategicas.forEach(linea => {
                if (distribucionActual[linea.id]) total += parseFloat(distribucionActual[linea.id]) || 0;
            });
            
            $('#totalPorcentaje').text(total.toFixed(2) + '%');
            
            if (Math.abs(total - 100) < 0.01) {
                $('#totalValidacion').show();
                $('#btnGuardarDistribucion').prop('disabled', false);
                Swal.fire({
                    icon: 'success',
                    title: '¡Distribución completa!',
                    text: 'El total es 100%. Puedes guardar la distribución.',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                $('#totalValidacion').hide();
                $('#btnGuardarDistribucion').prop('disabled', true);
            }
        }

        function abrirModalEditarPorcentaje(lineaId, lineaNombre) {
            const anio = $('#selectAnioDistribucion').val();
            if (!anio) {
                Swal.fire('Atención', 'Debe seleccionar un año primero', 'warning');
                return;
            }
            $('#editLineaId').val(lineaId);
            $('#editLineaNombre').text(lineaNombre);
            $('#editAnio').val(anio);
            $('#editPorcentaje').val(distribucionActual[lineaId] || 0);
            $('#modalEditarPorcentaje').modal('show');
        }

        function guardarPorcentajeLinea() {
            const lineaId = $('#editLineaId').val();
            const anio = $('#editAnio').val();
            const porcentaje = parseFloat($('#editPorcentaje').val()) || 0;

            if (porcentaje < 0 || porcentaje > 100) {
                Swal.fire('Error', 'El porcentaje debe estar entre 0 y 100', 'error');
                return;
            }

            let totalSinEstaLinea = 0;
            lineasEstrategicas.forEach(linea => {
                if (linea.id != lineaId && distribucionActual[linea.id]) {
                    totalSinEstaLinea += parseFloat(distribucionActual[linea.id]) || 0;
                }
            });
            
            const nuevoTotal = totalSinEstaLinea + porcentaje;
            
            if (nuevoTotal > 100) {
                Swal.fire({
                    icon: 'error',
                    title: 'Excede el 100%',
                    text: `El total sería ${nuevoTotal.toFixed(2)}%. Debe ser exactamente 100%`,
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            distribucionActual[lineaId] = porcentaje;
            actualizarTablasDistribucion(anio);
            actualizarResumenDistribucion();
            calcularTotalPorcentaje();
            $('#modalEditarPorcentaje').modal('hide');
        }

        function guardarDistribucionPorcentajes() {
            const anio = $('#selectAnioDistribucion').val();
            if (!anio) {
                Swal.fire('Error', 'Debe seleccionar un año', 'error');
                return;
            }

            const total = Object.values(distribucionActual).reduce((sum, item) => sum + (parseFloat(item) || 0), 0);
            
            if (Math.abs(total - 100) > 0.01) {
                Swal.fire({
                    icon: 'error',
                    title: 'Total incorrecto',
                    html: `El total actual es <strong>${total.toFixed(2)}%</strong>.<br>Debe ser exactamente <strong>100%</strong> para poder guardar.`,
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            const lineasSinAsignar = [];
            lineasEstrategicas.forEach(linea => {
                if (!distribucionActual[linea.id] || distribucionActual[linea.id] == 0) {
                    lineasSinAsignar.push(`${linea.codigo} - ${linea.nombre}`);
                }
            });

            if (lineasSinAsignar.length > 0 && lineasSinAsignar.length < lineasEstrategicas.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Líneas sin asignar',
                    html: `Las siguientes líneas tienen 0%:<br><strong>${lineasSinAsignar.join('<br>')}</strong><br><br>¿Deseas guardar de todos modos?`,
                    showCancelButton: true,
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        ejecutarGuardarDistribucion(anio);
                    }
                });
                return;
            }

            ejecutarGuardarDistribucion(anio);
        }

        function ejecutarGuardarDistribucion(anio) {
            const datos = [];
            lineasEstrategicas.forEach(linea => {
                datos.push({ 
                    linea_id: linea.id, 
                    anio: anio, 
                    porcentaje: distribucionActual[linea.id] || 0 
                });
            });

            Swal.fire({
                title: 'Guardando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: basePath + '/config144/guardarDataDistribucion',
                type: 'POST',
                data: { distribucion: JSON.stringify(datos) },
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Guardado!',
                            text: 'Distribución guardada exitosamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#modalDistribucionPorcentajes').modal('hide');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Error al guardar la distribución', 'error');
                }
            });
        }

        function verDetalleLinea(lineaId) {
            const anio = $('#selectAnioDistribucion').val();
            if (!anio) {
                Swal.fire('Atención', 'Debe seleccionar un año', 'warning');
                return;
            }
            const linea = lineasEstrategicas.find(l => l.id == lineaId);
            const porcentaje = distribucionActual[lineaId] || 0;
            Swal.fire({
                title: `Detalle: ${linea.codigo} - ${linea.nombre}`,
                html: `<div class="text-start"><p><strong>Código:</strong> ${linea.codigo}</p><p><strong>Año:</strong> ${anio}</p><p><strong>Porcentaje asignado:</strong> ${porcentaje}%</p><p><strong>Objetivo:</strong> ${linea.objetivo || 'No especificado'}</p></div>`,
                icon: 'info',
                confirmButtonText: 'Cerrar'
            });
        }

        function mostrarMotoresPorLinea(lineaId, lineaNombre, event) {
            event.stopPropagation();
            
            const anio = $('#selectAnioDistribucion').val();
            if (!anio) {
                Swal.fire('Atención', 'Debe seleccionar un año primero', 'warning');
                return;
            }

            modoVisualizacion = 'motores';
            lineaSeleccionada = { id: lineaId, nombre: lineaNombre };
            
            $('#resumenDistribucionBody').html(' <td colspan="3" class="text-center py-4"><div class="spinner-border text-primary mb-2" role="status"><span class="visually-hidden">Cargando...</span></div><p class="text-muted">Cargando motores para ${lineaNombre}...</p></td>');

            cargarDataMotoresPorLinea(lineaId, anio);
            actualizarRutaNavegacion();
        }

        function cargarDataMotoresPorLinea(lineaId, anio) {
            if (motoresCache[lineaId]) {
                cargarPorcentajesMotores(lineaId, anio, motoresCache[lineaId]);
                return;
            }

            $.ajax({
                url: basePath + '/config144/getMotoresPorLinea',
                type: 'GET',
                data: { linea_id: lineaId },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.motores) {
                        motoresCache[lineaId] = response.motores;
                        cargarPorcentajesMotores(lineaId, anio, response.motores);
                    } else {
                        $('#resumenDistribucionBody').html(' <td colspan="3" class="text-center text-muted py-4"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p>No hay motores asociados a esta línea</p><button class="btn btn-sm btn-primary mt-2" onclick="volverALineas()"><i class="fas fa-arrow-left me-1"></i>Volver a líneas</button></td>');
                    }
                },
                error: function() {
                    $('#resumenDistribucionBody').html(' <td colspan="3" class="text-center text-danger py-4"><i class="fas fa-times-circle fa-2x mb-2"></i><p>Error al cargar los motores</p><button class="btn btn-sm btn-primary mt-2" onclick="volverALineas()"><i class="fas fa-arrow-left me-1"></i>Volver a líneas</button></td>');
                }
            });
        }

        function cargarPorcentajesMotores(lineaId, anio, motores) {
            $.ajax({
                url: basePath + '/config144/getDataMotores',
                type: 'GET',
                data: { linea_id: lineaId, anio: anio },
                dataType: 'json',
                success: function(response) {
                    const porcentajes = response.success ? response.datos.motor : {};
                    mostrarMotoresEnTabla(motores, lineaId, anio, porcentajes);
                },
                error: function() {
                    mostrarMotoresEnTabla(motores, lineaId, anio, {});
                }
            });
        }

        function mostrarMotoresEnTabla(motores, lineaId, anio, porcentajes = {}) {
            if (!motores || motores.length === 0) {
                $('#resumenDistribucionBody').html(' <td colspan="3" class="text-center text-muted py-4"><i class="fas fa-database fa-2x mb-2"></i><p>No hay motores disponibles</p><button class="btn btn-sm btn-primary mt-2" onclick="volverALineas()"><i class="fas fa-arrow-left me-1"></i>Volver a líneas</button></td>');
                return;
            }

            let html = '';
            let total = 0;

            html += `
                <tr class="table-info">
                    <td colspan="3" class="py-2">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-primary me-2" onclick="volverALineas()"><i class="fas fa-arrow-left"></i></button>
                            <span><i class="fas fa-sitemap me-1"></i><strong>Motores de: ${lineaSeleccionada ? lineaSeleccionada.nombre : ''}</strong></span>
                        </div>
                    </td>
                </tr>
            `;

            motores.forEach(motor => {
                const porcentaje = porcentajes[motor.id] ? parseFloat(porcentajes[motor.id]) : 0;
                total += porcentaje;

                html += `
                    <tr ondblclick="mostrarProyectosPorMotor(${motor.id}, '${motor.nombre.replace(/'/g, "\\'")}', event)">
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-cog text-primary me-2" style="opacity: 0.7;"></i>
                                <div>
                                    <strong>${motor.nombre}</strong>
                                    <br>
                                    <small class="text-muted">Doble clic para ver proyectos</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge ${porcentaje > 0 ? 'bg-success' : 'bg-secondary'} fs-6 p-2" 
                                  style="cursor: pointer;" 
                                  onclick="event.stopPropagation(); abrirModalEditarPorcentajeMotor(${motor.id}, '${motor.nombre.replace(/'/g, "\\'")}', ${lineaId})">
                                ${porcentaje.toFixed(2)}%
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-warning" onclick="event.stopPropagation(); abrirModalEditarPorcentajeMotor(${motor.id}, '${motor.nombre.replace(/'/g, "\\'")}', ${lineaId})" title="Editar porcentaje"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-info" onclick="event.stopPropagation(); mostrarProyectosPorMotor(${motor.id}, '${motor.nombre.replace(/'/g, "\\'")}', event)" title="Ver proyectos"><i class="fas fa-project-diagram"></i></button>
                            </div>
                         </tr>
                `;
            });

            html += `
                <tr class="table-light fw-bold">
                    <td class="text-end">TOTAL MOTORES:</td>
                    <td><span class="badge ${Math.abs(total - 100) < 0.01 ? 'bg-success' : 'bg-warning'} fs-6 p-2">${total.toFixed(2)}%</span></td>
                    <td>${Math.abs(total - 100) > 0.01 ? '<small class="text-warning">Debe sumar 100%</small>' : '<small class="text-success"><i class="fas fa-check"></i> OK</small>'}</td>
                 </tr>
            `;

            $('#resumenDistribucionBody').html(html);
            $('#totalResumen').text(total.toFixed(2) + '%');
            $('.modal-title').html('<i class="fas fa-percent me-2"></i>DISTRIBUCIÓN PORCENTUAL - MOTORES');
        }

        function mostrarProyectosPorMotor(motorId, motorNombre, event) {
            event.stopPropagation();
            
            const anio = $('#selectAnioDistribucion').val();
            if (!anio) {
                Swal.fire('Atención', 'Debe seleccionar un año primero', 'warning');
                return;
            }

            modoVisualizacion = 'proyectos';
            motorSeleccionado = { id: motorId, nombre: motorNombre };
            
            $('#resumenDistribucionBody').html(' <td colspan="3" class="text-center py-4"><div class="spinner-border text-primary mb-2" role="status"><span class="visually-hidden">Cargando...</span></div><p class="text-muted">Cargando proyectos para ${motorNombre}...</p> </tr>');

            cargarDataProyectosPorMotor(motorId, anio);
            actualizarRutaNavegacion();
        }

        function cargarDataProyectosPorMotor(motorId, anio) {
            if (proyectosCache[motorId]) {
                cargarPorcentajesProyectos(motorId, anio, proyectosCache[motorId]);
                return;
            }

            $.ajax({
                url: basePath + '/config144/getProyectosPorMotor',
                type: 'GET',
                data: { motor_id: motorId },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.proyectos) {
                        proyectosCache[motorId] = response.proyectos;
                        cargarPorcentajesProyectos(motorId, anio, response.proyectos);
                    } else {
                        $('#resumenDistribucionBody').html(' <td colspan="3" class="text-center text-muted py-4"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p>No hay proyectos asociados a este motor</p><button class="btn btn-sm btn-primary mt-2" onclick="volverAMotores()"><i class="fas fa-arrow-left me-1"></i>Volver a motores</button> </tr>');
                    }
                },
                error: function() {
                    $('#resumenDistribucionBody').html(' <td colspan="3" class="text-center text-danger py-4"><i class="fas fa-times-circle fa-2x mb-2"></i><p>Error al cargar los proyectos</p><button class="btn btn-sm btn-primary mt-2" onclick="volverAMotores()"><i class="fas fa-arrow-left me-1"></i>Volver a motores</button> </tr>');
                }
            });
        }

        function cargarPorcentajesProyectos(motorId, anio, proyectos) {
            $.ajax({
                url: basePath + '/config144/getDataProyectos',
                type: 'GET',
                data: { motor_id: motorId, anio: anio },
                dataType: 'json',
                success: function(response) {
                    const porcentajes = response.success ? response.datos.proyecto : {};
                    mostrarProyectosEnTabla(proyectos, motorId, anio, porcentajes);
                },
                error: function() {
                    mostrarProyectosEnTabla(proyectos, motorId, anio, {});
                }
            });
        }

        function mostrarProyectosEnTabla(proyectos, motorId, anio, porcentajes = {}) {
            if (!proyectos || proyectos.length === 0) {
                $('#resumenDistribucionBody').html(' <td colspan="3" class="text-center text-muted py-4"><i class="fas fa-database fa-2x mb-2"></i><p>No hay proyectos disponibles</p><button class="btn btn-sm btn-primary mt-2" onclick="volverAMotores()"><i class="fas fa-arrow-left me-1"></i>Volver a motores</button> </tr>');
                return;
            }

            let html = '';
            let total = 0;

            html += `
                <tr class="table-info">
                    <td colspan="3" class="py-2">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-primary me-2" onclick="volverAMotores()"><i class="fas fa-arrow-left"></i></button>
                            <span><i class="fas fa-tasks me-1"></i><strong>Proyectos de: ${motorSeleccionado ? motorSeleccionado.nombre : ''}</strong></span>
                        </div>
                     </tr>
            `;

            proyectos.forEach(proyecto => {
                const porcentaje = porcentajes[proyecto.id] ? parseFloat(porcentajes[proyecto.id]) : 0;
                total += porcentaje;

                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2" style="opacity: 0.7;"></i>
                                <div>
                                    <strong>${proyecto.codigo ? proyecto.codigo + ' - ' : ''}${proyecto.nombre}</strong>
                                    <br>
                                    <small class="text-muted">ID: ${proyecto.id}</small>
                                </div>
                            </div>
                         </td>
                         <td>
                            <span class="badge ${porcentaje > 0 ? 'bg-success' : 'bg-secondary'} fs-6 p-2" 
                                  style="cursor: pointer;" 
                                  onclick="event.stopPropagation(); abrirModalEditarPorcentajeProyecto(${proyecto.id}, '${proyecto.nombre.replace(/'/g, "\\'")}', ${motorId})">
                                ${porcentaje.toFixed(2)}%
                            </span>
                         </td>
                         <td>
                            <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); abrirModalEditarPorcentajeProyecto(${proyecto.id}, '${proyecto.nombre.replace(/'/g, "\\'")}', ${motorId})" title="Editar porcentaje">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                         </td>
                     </tr>
                `;
            });

            html += `
                <tr class="table-light fw-bold">
                    <td class="text-end">TOTAL PROYECTOS:</td>
                    <td><span class="badge ${Math.abs(total - 100) < 0.01 ? 'bg-success' : 'bg-warning'} fs-6 p-2">${total.toFixed(2)}%</span></td>
                    <td>${Math.abs(total - 100) > 0.01 ? '<small class="text-warning">Debe sumar 100%</small>' : '<small class="text-success"><i class="fas fa-check"></i> OK</small>'}</td>
                 </tr>
            `;

            $('#resumenDistribucionBody').html(html);
            $('#totalResumen').text(total.toFixed(2) + '%');
            $('.modal-title').html('<i class="fas fa-percent me-2"></i>DISTRIBUCIÓN PORCENTUAL - PROYECTOS');
        }

        function volverALineas() {
            modoVisualizacion = 'lineas';
            lineaSeleccionada = null;
            motorSeleccionado = null;
            
            $('.modal-title').html('<i class="fas fa-percent me-2"></i>DISTRIBUCIÓN PORCENTUAL POR LÍNEAS ESTRATÉGICAS');
            
            const anio = $('#selectAnioDistribucion').val();
            if (anio) {
                cargarDistribucionPorcentajes();
            } else {
                actualizarResumenDistribucion(true);
            }
            actualizarRutaNavegacion();
        }

        function volverAMotores() {
            modoVisualizacion = 'motores';
            motorSeleccionado = null;
            
            $('.modal-title').html('<i class="fas fa-percent me-2"></i>DISTRIBUCIÓN PORCENTUAL - MOTORES');
            
            const anio = $('#selectAnioDistribucion').val();
            if (lineaSeleccionada && anio) {
                cargarDataMotoresPorLinea(lineaSeleccionada.id, anio);
            } else {
                volverALineas();
            }
            actualizarRutaNavegacion();
        }

        function actualizarRutaNavegacion() {
            let rutaHtml = '';
            
            if (modoVisualizacion === 'lineas') {
                $('#navegacionNiveles').hide();
                return;
            }
            
            $('#navegacionNiveles').show();
            
            if (modoVisualizacion === 'motores' && lineaSeleccionada) {
                rutaHtml = `
                    <span class="nivel-indicador nivel-linea">Línea</span>
                    <span class="mx-2">${lineaSeleccionada.nombre}</span>
                    <span class="separator">›</span>
                    <span class="nivel-indicador nivel-motor">Motores</span>
                `;
            } else if (modoVisualizacion === 'proyectos' && lineaSeleccionada && motorSeleccionado) {
                rutaHtml = `
                    <span class="nivel-indicador nivel-linea">Línea</span>
                    <span class="mx-2">${lineaSeleccionada.nombre}</span>
                    <span class="separator">›</span>
                    <span class="nivel-indicador nivel-motor">Motor</span>
                    <span class="mx-2">${motorSeleccionado.nombre}</span>
                    <span class="separator">›</span>
                    <span class="nivel-indicador nivel-proyecto">Proyectos</span>
                `;
            }
            
            $('#rutaNavegacion').html(rutaHtml);
        }

        function abrirModalEditarPorcentajeMotor(motorId, motorNombre, lineaId) {
            const anio = $('#selectAnioDistribucion').val();
            if (!anio) {
                Swal.fire('Atención', 'Debe seleccionar un año primero', 'warning');
                return;
            }

            if (!$('#modalEditarPorcentajeMotor').length) {
                $('body').append(`
                    <div class="modal fade" id="modalEditarPorcentajeMotor" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>EDITAR PORCENTAJE DE MOTOR</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formEditarPorcentajeMotor">
                                        <input type="hidden" id="editMotorId">
                                        <input type="hidden" id="editMotorLineaId">
                                        <input type="hidden" id="editMotorAnio">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">MOTOR</label>
                                            <p class="form-control-plaintext" id="editMotorNombre"></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">PORCENTAJE</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="editMotorPorcentaje" step="0.01" min="0" max="100" placeholder="0.00">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <small class="text-muted">Ingrese un valor entre 0 y 100</small>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-success" onclick="guardarPorcentajeMotor()"><i class="fas fa-save me-1"></i>Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }

            $('#editMotorId').val(motorId);
            $('#editMotorNombre').text(motorNombre);
            $('#editMotorLineaId').val(lineaId);
            $('#editMotorAnio').val(anio);
            
            $.ajax({
                url: basePath + '/config144/getDataMotores',
                type: 'GET',
                data: { linea_id: lineaId, anio: anio },
                dataType: 'json',
                success: function(response) {
                    const porcentaje = (response.success && response.datos.motor) ? response.datos.motor[motorId] || 0 : 0;
                    $('#editMotorPorcentaje').val(porcentaje);
                },
                error: function() {
                    $('#editMotorPorcentaje').val(0);
                }
            });
            
            $('#modalEditarPorcentajeMotor').modal('show');
        }

        function guardarPorcentajeMotor() {
            const motorId = $('#editMotorId').val();
            const lineaId = $('#editMotorLineaId').val();
            const anio = $('#editMotorAnio').val();
            const porcentaje = parseFloat($('#editMotorPorcentaje').val()) || 0;

            if (porcentaje < 0 || porcentaje > 100) {
                Swal.fire('Error', 'El porcentaje debe estar entre 0 y 100', 'error');
                return;
            }

            Swal.fire({ title: 'Guardando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            const datos = [{ motor_id: motorId, linea_id: lineaId, anio: anio, porcentaje: porcentaje }];

            $.ajax({
                url: basePath + '/config144/guardarDataMotores',
                type: 'POST',
                data: { datos: JSON.stringify(datos) },
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: '¡Guardado!', text: 'Porcentaje guardado exitosamente', timer: 1500, showConfirmButton: false });
                        $('#modalEditarPorcentajeMotor').modal('hide');
                        if (lineaSeleccionada && lineaSeleccionada.id == lineaId) {
                            cargarDataMotoresPorLinea(lineaId, anio);
                        }
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Error al guardar el porcentaje', 'error');
                }
            });
        }

        function abrirModalEditarPorcentajeProyecto(proyectoId, proyectoNombre, motorId) {
            const anio = $('#selectAnioDistribucion').val();
            if (!anio) {
                Swal.fire('Atención', 'Debe seleccionar un año primero', 'warning');
                return;
            }

            if (!$('#modalEditarPorcentajeProyecto').length) {
                $('body').append(`
                    <div class="modal fade" id="modalEditarPorcentajeProyecto" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>EDITAR PORCENTAJE DE PROYECTO</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="formEditarPorcentajeProyecto">
                                        <input type="hidden" id="editProyectoId">
                                        <input type="hidden" id="editProyectoMotorId">
                                        <input type="hidden" id="editProyectoAnio">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">PROYECTO</label>
                                            <p class="form-control-plaintext" id="editProyectoNombre"></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">PORCENTAJE</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="editProyectoPorcentaje" step="0.01" min="0" max="100" placeholder="0.00">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <small class="text-muted">Ingrese un valor entre 0 y 100</small>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-success" onclick="guardarPorcentajeProyecto()"><i class="fas fa-save me-1"></i>Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }

            $('#editProyectoId').val(proyectoId);
            $('#editProyectoNombre').text(proyectoNombre);
            $('#editProyectoMotorId').val(motorId);
            $('#editProyectoAnio').val(anio);
            
            $.ajax({
                url: basePath + '/config144/getDataProyectos',
                type: 'GET',
                data: { motor_id: motorId, anio: anio },
                dataType: 'json',
                success: function(response) {
                    const porcentaje = (response.success && response.datos.proyecto) ? response.datos.proyecto[proyectoId] || 0 : 0;
                    $('#editProyectoPorcentaje').val(porcentaje);
                },
                error: function() {
                    $('#editProyectoPorcentaje').val(0);
                }
            });
            
            $('#modalEditarPorcentajeProyecto').modal('show');
        }

        function guardarPorcentajeProyecto() {
            const proyectoId = $('#editProyectoId').val();
            const motorId = $('#editProyectoMotorId').val();
            const anio = $('#editProyectoAnio').val();
            const porcentaje = parseFloat($('#editProyectoPorcentaje').val()) || 0;

            if (porcentaje < 0 || porcentaje > 100) {
                Swal.fire('Error', 'El porcentaje debe estar entre 0 y 100', 'error');
                return;
            }

            Swal.fire({ title: 'Guardando...', text: 'Por favor espere', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            const datos = [{ proyecto_id: proyectoId, motor_id: motorId, anio: anio, porcentaje: porcentaje }];

            $.ajax({
                url: basePath + '/config144/guardarDataProyectos',
                type: 'POST',
                data: { datos: JSON.stringify(datos) },
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({ icon: 'success', title: '¡Guardado!', text: 'Porcentaje guardado exitosamente', timer: 1500, showConfirmButton: false });
                        $('#modalEditarPorcentajeProyecto').modal('hide');
                        if (motorSeleccionado && motorSeleccionado.id == motorId) {
                            cargarDataProyectosPorMotor(motorId, anio);
                        }
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Error al guardar el porcentaje', 'error');
                }
            });
        }
    </script>
<?php require_once __DIR__ . '/../complementos/footer.php'; ?>