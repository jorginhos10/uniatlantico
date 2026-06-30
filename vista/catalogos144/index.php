<?php
// vista/catalogos144/index.php
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Líneas, Motores y Proyectos — Configuración';
$paginaActual = 'catalogos144';
$basePath     = Config::getBasePath();
$baseUrl      = Config::getBaseUrl();

ob_start();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --ios-blue:    #007AFF;
        --ios-green:   #34C759;
        --ios-red:     #FF3B30;
        --ios-orange:  #FF9500;
        --ios-purple:  #9C27B0;
        --ios-gray:    #8E8E93;
        --ios-bg:      #F2F2F7;
        --ios-surface: #FFFFFF;
        --ios-label:   #000000;
        --ios-label2:  rgba(60,60,67,.6);
        --ios-sep:     rgba(60,60,67,.12);
        --font: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
    }
    body { font-family: var(--font); }
    .cat-wrap { padding: 0 4px; }
    .cat-header {
        background: var(--ios-blue);
        color: white;
        border-radius: 20px;
        padding: 26px 28px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 6px 24px rgba(0,122,255,.3);
    }
    .cat-header-left { display: flex; align-items: center; gap: 16px; }
    .cat-header-icon {
        width: 52px; height: 52px;
        background: rgba(255,255,255,.2);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .cat-header h1 { font-size: 22px; font-weight: 700; margin: 0 0 3px; letter-spacing: -.3px; }
    .cat-header p  { font-size: 14px; opacity: .85; margin: 0; }
    .btn-nueva {
        background: rgba(255,255,255,.22);
        color: white;
        border: 1.5px solid rgba(255,255,255,.4);
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        display: flex; align-items: center; gap: 7px;
        transition: background .2s;
        font-family: var(--font);
        white-space: nowrap;
    }
    .btn-nueva:hover { background: rgba(255,255,255,.32); }
    .nav-tabs { border-bottom: none; margin-bottom: 18px; gap: 8px; }
    .nav-tabs .nav-link {
        border: none;
        border-radius: 12px;
        font-weight: 600;
        color: var(--ios-label2);
        padding: 10px 18px;
    }
    .nav-tabs .nav-link.active { background: var(--ios-blue); color: white; }
    .table-card {
        background: var(--ios-surface);
        border-radius: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,.08);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .table-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--ios-sep);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .table-card-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--ios-label);
        display: flex; align-items: center; gap: 8px;
    }
    .badge-count {
        background: var(--ios-blue);
        color: white;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 10px;
    }
    .search-input {
        border: 1.5px solid var(--ios-sep);
        border-radius: 10px;
        padding: 7px 12px;
        font-size: 14px;
        font-family: var(--font);
        outline: none;
        width: 220px;
        transition: border-color .2s;
    }
    .search-input:focus { border-color: var(--ios-blue); }
    .cat-table { width: 100%; border-collapse: collapse; }
    .cat-table thead th {
        background: var(--ios-bg);
        color: var(--ios-label2);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        padding: 11px 16px;
        border-bottom: 1px solid var(--ios-sep);
        white-space: nowrap;
    }
    .cat-table tbody tr { border-bottom: 1px solid var(--ios-sep); transition: background .15s; }
    .cat-table tbody tr:last-child { border-bottom: none; }
    .cat-table tbody tr:hover { background: rgba(0,122,255,.04); }
    .cat-table td { padding: 14px 16px; font-size: 14px; color: var(--ios-label); vertical-align: middle; }
    .badge-activo   { background: rgba(52,199,89,.15);  color: #1a7a3a; border: 1px solid rgba(52,199,89,.3); }
    .badge-inactivo { background: rgba(255,59,48,.1);   color: #c0392b; border: 1px solid rgba(255,59,48,.25); }
    .badge-estado {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 12px; font-weight: 600;
    }
    .badge-codigo {
        background: rgba(0,122,255,.1);
        color: var(--ios-blue);
        font-weight: 700;
        padding: 2px 9px;
        border-radius: 8px;
        font-size: 12px;
    }
    .btn-action {
        padding: 5px 10px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-family: var(--font);
        transition: opacity .2s, transform .15s;
        margin: 0 2px;
    }
    .btn-action:hover { opacity: .82; transform: translateY(-1px); }
    .btn-edit       { background: rgba(0,122,255,.12); color: var(--ios-blue); }
    .btn-delete     { background: rgba(255,59,48,.1);  color: var(--ios-red); }
    .btn-toggle-on  { background: rgba(255,59,48,.1);  color: var(--ios-red); }
    .btn-toggle-off { background: rgba(52,199,89,.12); color: #1a7a3a; }
    .empty-row td { text-align: center; padding: 60px 20px !important; color: var(--ios-label2); }
    .empty-row .empty-icon { font-size: 48px; color: var(--ios-gray); margin-bottom: 12px; }
    .modal-content { border-radius: 20px; border: none; overflow: hidden; }
    .modal-header { background: var(--ios-surface); border-bottom: 1px solid var(--ios-sep); padding: 20px 24px; }
    .modal-header .modal-title { font-weight: 700; font-size: 18px; color: var(--ios-label); }
    .modal-body  { padding: 24px; }
    .modal-footer { background: var(--ios-bg); border-top: 1px solid var(--ios-sep); padding: 16px 24px; }
    .form-label { font-weight: 600; font-size: 13px; color: var(--ios-label); margin-bottom: 6px; }
    .form-control, .form-select {
        border: 1.5px solid var(--ios-sep);
        border-radius: 12px;
        padding: 11px 14px;
        font-family: var(--font);
        font-size: 15px;
        background: var(--ios-bg);
        color: var(--ios-label);
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: var(--ios-blue);
        background: var(--ios-surface);
        box-shadow: 0 0 0 3px rgba(0,122,255,.15);
    }
    .btn-ios { padding: 11px 22px; border: none; border-radius: 12px; font-weight: 600; font-size: 15px; cursor: pointer; font-family: var(--font); transition: opacity .2s, transform .15s; }
    .btn-ios:hover { opacity: .87; transform: translateY(-1px); }
    .btn-ios-primary   { background: var(--ios-blue); color: white; }
    .btn-ios-secondary { background: var(--ios-bg); color: var(--ios-blue); border: 1.5px solid var(--ios-sep); }
    .btn-ios-danger    { background: var(--ios-red); color: white; }
    @media (max-width: 768px) { .search-input { width: 100%; } }
    @media (max-width: 480px) { .cat-header { flex-direction: column; align-items: flex-start; } }
</style>
<?php $cssExtra = ob_get_clean();
require_once __DIR__ . '/../complementos/header.php'; ?>

<div class="cat-wrap">

    <div class="cat-header">
        <div class="cat-header-left">
            <div class="cat-header-icon"><i class="fas fa-sitemap"></i></div>
            <div>
                <h1>Líneas, Motores y Proyectos</h1>
                <p>Gestiona el catálogo estratégico usado en FOR-DE-144</p>
            </div>
        </div>
        <a href="<?php echo $basePath; ?>/configuraciones" class="btn-nueva">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <ul class="nav nav-tabs" id="catTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-lineas-btn" data-bs-toggle="tab" data-bs-target="#tab-lineas" type="button">
                <i class="fas fa-bullseye me-1"></i> Líneas Estratégicas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-estrategias-btn" data-bs-toggle="tab" data-bs-target="#tab-estrategias" type="button">
                <i class="fas fa-chess me-1"></i> Estrategias
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-motores-btn" data-bs-toggle="tab" data-bs-target="#tab-motores" type="button">
                <i class="fas fa-cogs me-1"></i> Motores
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-proyectos-btn" data-bs-toggle="tab" data-bs-target="#tab-proyectos" type="button">
                <i class="fas fa-project-diagram me-1"></i> Proyectos
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ===================== TAB LÍNEAS ===================== -->
        <div class="tab-pane fade show active" id="tab-lineas" role="tabpanel">
            <div class="table-card">
                <div class="table-card-header">
                    <div class="table-card-title">
                        <i class="fas fa-list" style="color:var(--ios-blue);"></i>
                        Líneas Estratégicas
                        <span class="badge-count" id="badge-count-lineas">0</span>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="text" class="search-input" id="searchLineas" placeholder="Buscar línea...">
                        <button class="btn-nueva" style="background:var(--ios-blue);" onclick="abrirModalCrearLinea()">
                            <i class="fas fa-plus"></i> Nueva Línea
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="cat-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Objetivo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaLineasBody">
                            <tr class="empty-row"><td colspan="6"><div class="empty-icon"><i class="fas fa-spinner fa-spin"></i></div><div>Cargando...</div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================== TAB ESTRATEGIAS ===================== -->
        <div class="tab-pane fade" id="tab-estrategias" role="tabpanel">
            <div class="table-card">
                <div class="table-card-header">
                    <div class="table-card-title">
                        <i class="fas fa-list" style="color:var(--ios-blue);"></i>
                        Estrategias
                        <span class="badge-count" id="badge-count-estrategias">0</span>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="text" class="search-input" id="searchEstrategias" placeholder="Buscar estrategia...">
                        <button class="btn-nueva" style="background:var(--ios-blue);" onclick="abrirModalCrearEstrategia()">
                            <i class="fas fa-plus"></i> Nueva Estrategia
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="cat-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Línea</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaEstrategiasBody">
                            <tr class="empty-row"><td colspan="5"><div class="empty-icon"><i class="fas fa-spinner fa-spin"></i></div><div>Cargando...</div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================== TAB MOTORES ===================== -->
        <div class="tab-pane fade" id="tab-motores" role="tabpanel">
            <div class="table-card">
                <div class="table-card-header">
                    <div class="table-card-title">
                        <i class="fas fa-list" style="color:var(--ios-blue);"></i>
                        Motores
                        <span class="badge-count" id="badge-count-motores">0</span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <select class="search-input" id="filterLineaMotores" style="width:190px;">
                            <option value="">Todas las líneas</option>
                        </select>
                        <input type="text" class="search-input" id="searchMotores" placeholder="Buscar motor...">
                        <button class="btn-nueva" style="background:var(--ios-blue);" onclick="abrirModalCrearMotor()">
                            <i class="fas fa-plus"></i> Nuevo Motor
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="cat-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Línea</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Ponderación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaMotoresBody">
                            <tr class="empty-row"><td colspan="7"><div class="empty-icon"><i class="fas fa-spinner fa-spin"></i></div><div>Cargando...</div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================== TAB PROYECTOS ===================== -->
        <div class="tab-pane fade" id="tab-proyectos" role="tabpanel">
            <div class="table-card">
                <div class="table-card-header">
                    <div class="table-card-title">
                        <i class="fas fa-list" style="color:var(--ios-blue);"></i>
                        Proyectos
                        <span class="badge-count" id="badge-count-proyectos">0</span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <select class="search-input" id="filterLineaProyectos" style="width:170px;">
                            <option value="">Todas las líneas</option>
                        </select>
                        <select class="search-input" id="filterMotorProyectos" style="width:190px;">
                            <option value="">Todos los motores</option>
                        </select>
                        <input type="text" class="search-input" id="searchProyectos" placeholder="Buscar proyecto...">
                        <button class="btn-nueva" style="background:var(--ios-blue);" onclick="abrirModalCrearProyecto()">
                            <i class="fas fa-plus"></i> Nuevo Proyecto
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="cat-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Línea</th>
                                <th>Motor</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaProyectosBody">
                            <tr class="empty-row"><td colspan="7"><div class="empty-icon"><i class="fas fa-spinner fa-spin"></i></div><div>Cargando...</div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Línea -->
<div class="modal fade" id="modalLinea" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLineaTitulo">Nueva Línea Estratégica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formLinea">
                <input type="hidden" id="linea_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Código *</label>
                        <input type="text" class="form-control" name="codigo" id="linea_codigo" required maxlength="10" placeholder="Ej: L1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" id="linea_nombre" required placeholder="Ej: Formación Académica Integral">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Objetivo</label>
                        <textarea class="form-control" name="objetivo" id="linea_objetivo" rows="3" placeholder="Objetivo de la línea estratégica"></textarea>
                    </div>
                    <div class="mb-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activo" id="linea_activo" value="1" checked>
                            <label class="form-check-label" for="linea_activo"><strong>Activo</strong></label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ios btn-ios-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-ios btn-ios-primary" id="btnGuardarLinea"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Estrategia -->
<div class="modal fade" id="modalEstrategia" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEstrategiaTitulo">Nueva Estrategia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEstrategia">
                <input type="hidden" id="estrategia_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Línea Estratégica *</label>
                        <select class="form-select" name="linea_id" id="estrategia_linea_id" required>
                            <option value="">Seleccione una línea...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción *</label>
                        <textarea class="form-control" name="descripcion" id="estrategia_descripcion" rows="3" required placeholder="Describa la estrategia"></textarea>
                    </div>
                    <div class="mb-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activo" id="estrategia_activo" value="1" checked>
                            <label class="form-check-label" for="estrategia_activo"><strong>Activo</strong></label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ios btn-ios-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-ios btn-ios-primary" id="btnGuardarEstrategia"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Motor -->
<div class="modal fade" id="modalMotor" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMotorTitulo">Nuevo Motor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMotor">
                <input type="hidden" id="motor_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Línea Estratégica *</label>
                        <select class="form-select" name="linea_id" id="motor_linea_id" required>
                            <option value="">Seleccione una línea...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Código *</label>
                        <input type="text" class="form-control" name="codigo" id="motor_codigo" required maxlength="20" placeholder="Ej: M1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" id="motor_nombre" required placeholder="Ej: Motor de desarrollo">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ponderación (%)</label>
                        <input type="number" class="form-control" name="ponderacion" id="motor_ponderacion" step="0.01" min="0" max="100" placeholder="0.00">
                    </div>
                    <div class="mb-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activo" id="motor_activo" value="1" checked>
                            <label class="form-check-label" for="motor_activo"><strong>Activo</strong></label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ios btn-ios-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-ios btn-ios-primary" id="btnGuardarMotor"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Proyecto -->
<div class="modal fade" id="modalProyecto" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProyectoTitulo">Nuevo Proyecto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formProyecto">
                <input type="hidden" id="proyecto_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Línea Estratégica *</label>
                        <select class="form-select" id="proyecto_linea_id" required>
                            <option value="">Seleccione una línea...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motor *</label>
                        <select class="form-select" name="motor_id" id="proyecto_motor_id" required>
                            <option value="">Seleccione primero una línea...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Código *</label>
                        <input type="text" class="form-control" name="codigo" id="proyecto_codigo" required maxlength="20" placeholder="Ej: P1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" id="proyecto_nombre" required placeholder="Ej: Proyecto de modernización">
                    </div>
                    <div class="mb-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activo" id="proyecto_activo" value="1" checked>
                            <label class="form-check-label" for="proyecto_activo"><strong>Activo</strong></label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-ios btn-ios-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-ios btn-ios-primary" id="btnGuardarProyecto"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar genérico -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding: 28px 24px;">
                <div style="width:64px;height:64px;background:rgba(255,59,48,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--ios-red);">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <p style="font-size:15px;color:var(--ios-label2);margin:0;">
                    ¿Eliminar <strong id="nombreEliminar"></strong>? Esta acción no se puede deshacer.
                </p>
                <input type="hidden" id="id_eliminar">
                <input type="hidden" id="tipo_eliminar">
            </div>
            <div class="modal-footer" style="justify-content:center;gap:12px;">
                <button type="button" class="btn-ios btn-ios-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn-ios btn-ios-danger" onclick="confirmarEliminar()">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const basePath = '<?php echo $basePath; ?>';
    let todasLineas = [];
    let todasEstrategias = [];
    let todosMotores = [];
    let todosProyectos = [];

    $(document).ready(function() {
        cargarLineas();
        cargarEstrategias();
        cargarMotores();
        cargarProyectos();

        $('#searchLineas').on('input', function() { filtrarLineas($(this).val().toLowerCase()); });
        $('#searchEstrategias').on('input', function() { filtrarEstrategias($(this).val().toLowerCase()); });
        $('#searchMotores').on('input', function() { guardarFiltroMotores(); aplicarFiltrosMotores(); });
        $('#filterLineaMotores').on('change', function() { guardarFiltroMotores(); aplicarFiltrosMotores(); });
        $('#searchProyectos').on('input', function() { guardarFiltrosProyectos(); aplicarFiltrosProyectos(); });

        $('#filterLineaProyectos').on('change', function() {
            llenarFiltroMotorProyectos($(this).val(), '');
            guardarFiltrosProyectos();
            aplicarFiltrosProyectos();
        });
        $('#filterMotorProyectos').on('change', function() {
            guardarFiltrosProyectos();
            aplicarFiltrosProyectos();
        });
    });

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ===================== LÍNEAS =====================

    function cargarLineas() {
        $.ajax({
            url: basePath + '/catalogos144/listarLineas',
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    todasLineas = res.lineas;
                    renderTablaLineas(todasLineas);
                    llenarSelectsLineas();
                } else { mostrarErrorLineas(); }
            },
            error: function() { mostrarErrorLineas(); }
        });
    }

    function mostrarErrorLineas() {
        $('#tablaLineasBody').html('<tr class="empty-row"><td colspan="6"><div class="empty-icon" style="color:var(--ios-red);"><i class="fas fa-exclamation-circle"></i></div><div>Error al cargar las líneas</div></td></tr>');
    }

    function renderTablaLineas(lineas) {
        $('#badge-count-lineas').text(lineas.length);
        if (lineas.length === 0) {
            $('#tablaLineasBody').html('<tr class="empty-row"><td colspan="6"><div class="empty-icon"><i class="fas fa-bullseye"></i></div><div>No hay líneas estratégicas registradas</div></td></tr>');
            return;
        }
        let html = '';
        lineas.forEach(function(l) {
            const estadoClass = l.activo == 1 ? 'badge-activo' : 'badge-inactivo';
            const estadoText  = l.activo == 1 ? '<i class="fas fa-check-circle"></i> Activo' : '<i class="fas fa-times-circle"></i> Inactivo';
            const toggleClass = l.activo == 1 ? 'btn-toggle-on' : 'btn-toggle-off';
            const toggleIcon  = l.activo == 1 ? 'fa-ban' : 'fa-check-circle';
            html += `<tr>
                <td><strong style="color:var(--ios-blue);">#${l.id}</strong></td>
                <td><span class="badge-codigo">${escHtml(l.codigo)}</span></td>
                <td><strong>${escHtml(l.nombre)}</strong></td>
                <td style="color:var(--ios-label2);font-size:13px;max-width:320px;">${escHtml((l.objetivo || '').substring(0, 120))}${(l.objetivo || '').length > 120 ? '…' : ''}</td>
                <td><span class="badge-estado ${estadoClass}">${estadoText}</span></td>
                <td>
                    <button class="btn-action btn-edit" onclick="editarLinea(${l.id})" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn-action ${toggleClass}" onclick="cambiarEstadoLinea(${l.id}, ${l.activo == 1 ? 0 : 1})"><i class="fas ${toggleIcon}"></i></button>
                    <button class="btn-action btn-delete" onclick="pedirEliminar(${l.id}, '${escHtml(l.nombre)}', 'linea')" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        $('#tablaLineasBody').html(html);
    }

    function filtrarLineas(q) {
        if (!q) { renderTablaLineas(todasLineas); return; }
        renderTablaLineas(todasLineas.filter(l => (l.nombre || '').toLowerCase().includes(q) || (l.codigo || '').toLowerCase().includes(q)));
    }

    function abrirModalCrearLinea() {
        $('#modalLineaTitulo').text('Nueva Línea Estratégica');
        $('#formLinea')[0].reset();
        $('#linea_id').val('');
        $('#linea_activo').prop('checked', true);
        $('#modalLinea').modal('show');
    }

    function editarLinea(id) {
        $.ajax({
            url: basePath + '/catalogos144/getLinea?id=' + id,
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const l = res.linea;
                    $('#modalLineaTitulo').text('Editar Línea Estratégica');
                    $('#linea_id').val(l.id);
                    $('#linea_codigo').val(l.codigo);
                    $('#linea_nombre').val(l.nombre);
                    $('#linea_objetivo').val(l.objetivo);
                    $('#linea_activo').prop('checked', l.activo == 1);
                    $('#modalLinea').modal('show');
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() { Swal.fire('Error', 'No se pudo cargar la línea', 'error'); }
        });
    }

    $('#formLinea').on('submit', function(e) {
        e.preventDefault();
        const id  = $('#linea_id').val();
        const url = id ? basePath + '/catalogos144/actualizarLinea' : basePath + '/catalogos144/crearLinea';
        const data = {
            id: id,
            codigo: $('#linea_codigo').val(),
            nombre: $('#linea_nombre').val(),
            objetivo: $('#linea_objetivo').val(),
            activo: $('#linea_activo').is(':checked') ? 1 : 0
        };
        $('#btnGuardarLinea').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');
        $.ajax({
            url: url, type: 'POST', data: data, dataType: 'json',
            success: function(res) {
                $('#btnGuardarLinea').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Guardado!', text: res.message, timer: 1800, showConfirmButton: false });
                    $('#modalLinea').modal('hide');
                    cargarLineas();
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() {
                $('#btnGuardarLinea').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
            }
        });
    });

    function cambiarEstadoLinea(id, nuevoEstado) {
        $.ajax({
            url: basePath + '/catalogos144/cambiarEstadoLinea',
            type: 'POST', data: { id: id, activo: nuevoEstado }, dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: res.message, timer: 1400, showConfirmButton: false, toast: true, position: 'top-end' });
                    cargarLineas();
                } else { Swal.fire('Error', res.message, 'error'); }
            }
        });
    }

    function llenarSelectsLineas() {
        let options = '<option value="">Seleccione una línea...</option>';
        todasLineas.forEach(function(l) {
            options += `<option value="${l.id}">${escHtml(l.codigo)} - ${escHtml(l.nombre)}</option>`;
        });
        $('#motor_linea_id').html(options);
        $('#proyecto_linea_id').html(options);
        $('#estrategia_linea_id').html(options);

        let filterOpts = '<option value="">Todas las líneas</option>';
        todasLineas.forEach(function(l) {
            filterOpts += `<option value="${l.id}">${escHtml(l.codigo)} - ${escHtml(l.nombre)}</option>`;
        });
        $('#filterLineaProyectos').html(filterOpts);
        $('#filterLineaMotores').html(filterOpts);

        const savedProy = JSON.parse(localStorage.getItem('cat144_proy_filter') || '{}');
        if (savedProy.linea) $('#filterLineaProyectos').val(savedProy.linea);

        const savedMot = JSON.parse(localStorage.getItem('cat144_mot_filter') || '{}');
        if (savedMot.linea) $('#filterLineaMotores').val(savedMot.linea);
        if (savedMot.texto) $('#searchMotores').val(savedMot.texto);
    }

    // ===================== ESTRATEGIAS =====================

    function cargarEstrategias() {
        $.ajax({
            url: basePath + '/catalogos144/listarEstrategias',
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    todasEstrategias = res.estrategias;
                    renderTablaEstrategias(todasEstrategias);
                } else { mostrarErrorEstrategias(); }
            },
            error: function() { mostrarErrorEstrategias(); }
        });
    }

    function mostrarErrorEstrategias() {
        $('#tablaEstrategiasBody').html('<tr class="empty-row"><td colspan="5"><div class="empty-icon" style="color:var(--ios-red);"><i class="fas fa-exclamation-circle"></i></div><div>Error al cargar las estrategias</div></td></tr>');
    }

    function renderTablaEstrategias(estrategias) {
        $('#badge-count-estrategias').text(estrategias.length);
        if (estrategias.length === 0) {
            $('#tablaEstrategiasBody').html('<tr class="empty-row"><td colspan="5"><div class="empty-icon"><i class="fas fa-chess"></i></div><div>No hay estrategias registradas</div></td></tr>');
            return;
        }
        let html = '';
        estrategias.forEach(function(e) {
            const estadoClass = e.activo == 1 ? 'badge-activo' : 'badge-inactivo';
            const estadoText  = e.activo == 1 ? '<i class="fas fa-check-circle"></i> Activo' : '<i class="fas fa-times-circle"></i> Inactivo';
            const toggleClass = e.activo == 1 ? 'btn-toggle-on' : 'btn-toggle-off';
            const toggleIcon  = e.activo == 1 ? 'fa-ban' : 'fa-check-circle';
            const desc = (e.descripcion || '');
            html += `<tr>
                <td><strong style="color:var(--ios-blue);">#${e.id}</strong></td>
                <td><span class="badge-codigo">${escHtml(e.linea_codigo)}</span> <small class="text-muted">${escHtml(e.linea_nombre)}</small></td>
                <td style="color:var(--ios-label2);font-size:13px;max-width:380px;">${escHtml(desc.substring(0, 140))}${desc.length > 140 ? '…' : ''}</td>
                <td><span class="badge-estado ${estadoClass}">${estadoText}</span></td>
                <td>
                    <button class="btn-action btn-edit" onclick="editarEstrategia(${e.id})" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn-action ${toggleClass}" onclick="cambiarEstadoEstrategia(${e.id}, ${e.activo == 1 ? 0 : 1})"><i class="fas ${toggleIcon}"></i></button>
                    <button class="btn-action btn-delete" onclick="pedirEliminar(${e.id}, 'esta estrategia', 'estrategia')" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        $('#tablaEstrategiasBody').html(html);
    }

    function filtrarEstrategias(q) {
        if (!q) { renderTablaEstrategias(todasEstrategias); return; }
        renderTablaEstrategias(todasEstrategias.filter(e =>
            (e.descripcion || '').toLowerCase().includes(q) ||
            (e.linea_nombre || '').toLowerCase().includes(q) ||
            (e.linea_codigo || '').toLowerCase().includes(q)
        ));
    }

    function abrirModalCrearEstrategia() {
        $('#modalEstrategiaTitulo').text('Nueva Estrategia');
        $('#formEstrategia')[0].reset();
        $('#estrategia_id').val('');
        $('#estrategia_activo').prop('checked', true);
        $('#modalEstrategia').modal('show');
    }

    function editarEstrategia(id) {
        $.ajax({
            url: basePath + '/catalogos144/getEstrategia?id=' + id,
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const e = res.estrategia;
                    $('#modalEstrategiaTitulo').text('Editar Estrategia');
                    $('#estrategia_id').val(e.id);
                    $('#estrategia_linea_id').val(e.linea_id);
                    $('#estrategia_descripcion').val(e.descripcion);
                    $('#estrategia_activo').prop('checked', e.activo == 1);
                    $('#modalEstrategia').modal('show');
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() { Swal.fire('Error', 'No se pudo cargar la estrategia', 'error'); }
        });
    }

    $('#formEstrategia').on('submit', function(e) {
        e.preventDefault();
        const id  = $('#estrategia_id').val();
        const url = id ? basePath + '/catalogos144/actualizarEstrategia' : basePath + '/catalogos144/crearEstrategia';
        const data = {
            id: id,
            linea_id: $('#estrategia_linea_id').val(),
            descripcion: $('#estrategia_descripcion').val(),
            activo: $('#estrategia_activo').is(':checked') ? 1 : 0
        };
        $('#btnGuardarEstrategia').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');
        $.ajax({
            url: url, type: 'POST', data: data, dataType: 'json',
            success: function(res) {
                $('#btnGuardarEstrategia').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Guardado!', text: res.message, timer: 1800, showConfirmButton: false });
                    $('#modalEstrategia').modal('hide');
                    cargarEstrategias();
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() {
                $('#btnGuardarEstrategia').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
            }
        });
    });

    function cambiarEstadoEstrategia(id, nuevoEstado) {
        $.ajax({
            url: basePath + '/catalogos144/cambiarEstadoEstrategia',
            type: 'POST', data: { id: id, activo: nuevoEstado }, dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: res.message, timer: 1400, showConfirmButton: false, toast: true, position: 'top-end' });
                    cargarEstrategias();
                } else { Swal.fire('Error', res.message, 'error'); }
            }
        });
    }

    // ===================== MOTORES =====================

    function cargarMotores() {
        $.ajax({
            url: basePath + '/catalogos144/listarMotores',
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    todosMotores = res.motores;
                    const savedProy = JSON.parse(localStorage.getItem('cat144_proy_filter') || '{}');
                    llenarFiltroMotorProyectos($('#filterLineaProyectos').val(), savedProy.motor || '');
                    aplicarFiltrosMotores();
                } else { mostrarErrorMotores(); }
            },
            error: function() { mostrarErrorMotores(); }
        });
    }

    function mostrarErrorMotores() {
        $('#tablaMotoresBody').html('<tr class="empty-row"><td colspan="7"><div class="empty-icon" style="color:var(--ios-red);"><i class="fas fa-exclamation-circle"></i></div><div>Error al cargar los motores</div></td></tr>');
    }

    function renderTablaMotores(motores) {
        $('#badge-count-motores').text(motores.length);
        if (motores.length === 0) {
            $('#tablaMotoresBody').html('<tr class="empty-row"><td colspan="7"><div class="empty-icon"><i class="fas fa-cogs"></i></div><div>No hay motores registrados</div></td></tr>');
            return;
        }
        let html = '';
        motores.forEach(function(m) {
            const estadoClass = m.activo == 1 ? 'badge-activo' : 'badge-inactivo';
            const estadoText  = m.activo == 1 ? '<i class="fas fa-check-circle"></i> Activo' : '<i class="fas fa-times-circle"></i> Inactivo';
            const toggleClass = m.activo == 1 ? 'btn-toggle-on' : 'btn-toggle-off';
            const toggleIcon  = m.activo == 1 ? 'fa-ban' : 'fa-check-circle';
            const ponderacion = m.ponderacion !== null && m.ponderacion !== '' ? parseFloat(m.ponderacion).toFixed(2) + '%' : '—';
            html += `<tr>
                <td><strong style="color:var(--ios-blue);">#${m.id}</strong></td>
                <td><span class="badge-codigo">${escHtml(m.linea_codigo)}</span> <small class="text-muted">${escHtml(m.linea_nombre)}</small></td>
                <td><span class="badge-codigo">${escHtml(m.codigo)}</span></td>
                <td><strong>${escHtml(m.nombre)}</strong></td>
                <td>${ponderacion}</td>
                <td><span class="badge-estado ${estadoClass}">${estadoText}</span></td>
                <td>
                    <button class="btn-action btn-edit" onclick="editarMotor(${m.id})" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn-action ${toggleClass}" onclick="cambiarEstadoMotor(${m.id}, ${m.activo == 1 ? 0 : 1})"><i class="fas ${toggleIcon}"></i></button>
                    <button class="btn-action btn-delete" onclick="pedirEliminar(${m.id}, '${escHtml(m.nombre)}', 'motor')" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        $('#tablaMotoresBody').html(html);
    }

    function guardarFiltroMotores() {
        localStorage.setItem('cat144_mot_filter', JSON.stringify({
            linea: $('#filterLineaMotores').val(),
            texto: $('#searchMotores').val()
        }));
    }

    function aplicarFiltrosMotores() {
        const linea = $('#filterLineaMotores').val();
        const texto = $('#searchMotores').val().toLowerCase();
        let resultado = todosMotores;
        if (linea) resultado = resultado.filter(m => m.linea_id == linea);
        if (texto) resultado = resultado.filter(m =>
            (m.nombre || '').toLowerCase().includes(texto) ||
            (m.codigo || '').toLowerCase().includes(texto) ||
            (m.linea_nombre || '').toLowerCase().includes(texto)
        );
        renderTablaMotores(resultado);
    }

    function abrirModalCrearMotor() {
        $('#modalMotorTitulo').text('Nuevo Motor');
        $('#formMotor')[0].reset();
        $('#motor_id').val('');
        $('#motor_activo').prop('checked', true);
        $('#modalMotor').modal('show');
    }

    function editarMotor(id) {
        $.ajax({
            url: basePath + '/catalogos144/getMotor?id=' + id,
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const m = res.motor;
                    $('#modalMotorTitulo').text('Editar Motor');
                    $('#motor_id').val(m.id);
                    $('#motor_linea_id').val(m.linea_id);
                    $('#motor_codigo').val(m.codigo);
                    $('#motor_nombre').val(m.nombre);
                    $('#motor_ponderacion').val(m.ponderacion);
                    $('#motor_activo').prop('checked', m.activo == 1);
                    $('#modalMotor').modal('show');
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() { Swal.fire('Error', 'No se pudo cargar el motor', 'error'); }
        });
    }

    $('#formMotor').on('submit', function(e) {
        e.preventDefault();
        const id  = $('#motor_id').val();
        const url = id ? basePath + '/catalogos144/actualizarMotor' : basePath + '/catalogos144/crearMotor';
        const data = {
            id: id,
            linea_id: $('#motor_linea_id').val(),
            codigo: $('#motor_codigo').val(),
            nombre: $('#motor_nombre').val(),
            ponderacion: $('#motor_ponderacion').val(),
            activo: $('#motor_activo').is(':checked') ? 1 : 0
        };
        $('#btnGuardarMotor').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');
        $.ajax({
            url: url, type: 'POST', data: data, dataType: 'json',
            success: function(res) {
                $('#btnGuardarMotor').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Guardado!', text: res.message, timer: 1800, showConfirmButton: false });
                    $('#modalMotor').modal('hide');
                    cargarMotores();
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() {
                $('#btnGuardarMotor').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
            }
        });
    });

    function cambiarEstadoMotor(id, nuevoEstado) {
        $.ajax({
            url: basePath + '/catalogos144/cambiarEstadoMotor',
            type: 'POST', data: { id: id, activo: nuevoEstado }, dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: res.message, timer: 1400, showConfirmButton: false, toast: true, position: 'top-end' });
                    cargarMotores();
                } else { Swal.fire('Error', res.message, 'error'); }
            }
        });
    }

    // ===================== PROYECTOS =====================

    function cargarProyectos() {
        $.ajax({
            url: basePath + '/catalogos144/listarProyectos',
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    todosProyectos = res.proyectos;
                    const saved = JSON.parse(localStorage.getItem('cat144_proy_filter') || '{}');
                    if (saved.texto) $('#searchProyectos').val(saved.texto);
                    aplicarFiltrosProyectos();
                } else { mostrarErrorProyectos(); }
            },
            error: function() { mostrarErrorProyectos(); }
        });
    }

    function mostrarErrorProyectos() {
        $('#tablaProyectosBody').html('<tr class="empty-row"><td colspan="7"><div class="empty-icon" style="color:var(--ios-red);"><i class="fas fa-exclamation-circle"></i></div><div>Error al cargar los proyectos</div></td></tr>');
    }

    function renderTablaProyectos(proyectos) {
        $('#badge-count-proyectos').text(proyectos.length);
        if (proyectos.length === 0) {
            $('#tablaProyectosBody').html('<tr class="empty-row"><td colspan="7"><div class="empty-icon"><i class="fas fa-project-diagram"></i></div><div>No hay proyectos registrados</div></td></tr>');
            return;
        }
        let html = '';
        proyectos.forEach(function(p) {
            const estadoClass = p.activo == 1 ? 'badge-activo' : 'badge-inactivo';
            const estadoText  = p.activo == 1 ? '<i class="fas fa-check-circle"></i> Activo' : '<i class="fas fa-times-circle"></i> Inactivo';
            const toggleClass = p.activo == 1 ? 'btn-toggle-on' : 'btn-toggle-off';
            const toggleIcon  = p.activo == 1 ? 'fa-ban' : 'fa-check-circle';
            html += `<tr>
                <td><strong style="color:var(--ios-blue);">#${p.id}</strong></td>
                <td><span class="badge-codigo">${escHtml(p.linea_codigo)}</span> <small class="text-muted">${escHtml(p.linea_nombre)}</small></td>
                <td>${escHtml(p.motor_nombre)}</td>
                <td><span class="badge-codigo">${escHtml(p.codigo)}</span></td>
                <td><strong>${escHtml(p.nombre)}</strong></td>
                <td><span class="badge-estado ${estadoClass}">${estadoText}</span></td>
                <td>
                    <button class="btn-action btn-edit" onclick="editarProyecto(${p.id})" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn-action ${toggleClass}" onclick="cambiarEstadoProyecto(${p.id}, ${p.activo == 1 ? 0 : 1})"><i class="fas ${toggleIcon}"></i></button>
                    <button class="btn-action btn-delete" onclick="pedirEliminar(${p.id}, '${escHtml(p.nombre)}', 'proyecto')" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
        $('#tablaProyectosBody').html(html);
    }

    function llenarFiltroMotorProyectos(lineaId, motorSeleccionado) {
        const lista = lineaId ? todosMotores.filter(m => m.linea_id == lineaId) : todosMotores;
        let opts = '<option value="">Todos los motores</option>';
        lista.forEach(function(m) {
            opts += `<option value="${m.id}">${escHtml(m.codigo)} - ${escHtml(m.nombre)}</option>`;
        });
        $('#filterMotorProyectos').html(opts);
        if (motorSeleccionado) $('#filterMotorProyectos').val(motorSeleccionado);
    }

    function guardarFiltrosProyectos() {
        localStorage.setItem('cat144_proy_filter', JSON.stringify({
            linea: $('#filterLineaProyectos').val(),
            motor: $('#filterMotorProyectos').val(),
            texto: $('#searchProyectos').val()
        }));
    }

    function aplicarFiltrosProyectos() {
        const linea = $('#filterLineaProyectos').val();
        const motor = $('#filterMotorProyectos').val();
        const texto = $('#searchProyectos').val().toLowerCase();
        let resultado = todosProyectos;
        if (linea)  resultado = resultado.filter(p => p.linea_id == linea);
        if (motor)  resultado = resultado.filter(p => p.motor_id == motor);
        if (texto)  resultado = resultado.filter(p =>
            (p.nombre || '').toLowerCase().includes(texto) ||
            (p.codigo || '').toLowerCase().includes(texto) ||
            (p.motor_nombre || '').toLowerCase().includes(texto)
        );
        renderTablaProyectos(resultado);
    }

    function llenarSelectMotoresPorLinea(lineaId, motorSeleccionado) {
        const motoresDeLinea = todosMotores.filter(m => m.linea_id == lineaId);
        let options = '<option value="">Seleccione un motor...</option>';
        motoresDeLinea.forEach(function(m) {
            options += `<option value="${m.id}">${escHtml(m.codigo)} - ${escHtml(m.nombre)}</option>`;
        });
        $('#proyecto_motor_id').html(options);
        if (motorSeleccionado) $('#proyecto_motor_id').val(motorSeleccionado);
    }

    $('#proyecto_linea_id').on('change', function() {
        llenarSelectMotoresPorLinea($(this).val(), null);
    });

    function abrirModalCrearProyecto() {
        $('#modalProyectoTitulo').text('Nuevo Proyecto');
        $('#formProyecto')[0].reset();
        $('#proyecto_id').val('');
        $('#proyecto_motor_id').html('<option value="">Seleccione primero una línea...</option>');
        $('#proyecto_activo').prop('checked', true);
        $('#modalProyecto').modal('show');
    }

    function editarProyecto(id) {
        $.ajax({
            url: basePath + '/catalogos144/getProyecto?id=' + id,
            type: 'GET', dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const p = res.proyecto;
                    $('#modalProyectoTitulo').text('Editar Proyecto');
                    $('#proyecto_id').val(p.id);
                    $('#proyecto_linea_id').val(p.linea_id);
                    llenarSelectMotoresPorLinea(p.linea_id, p.motor_id);
                    $('#proyecto_codigo').val(p.codigo);
                    $('#proyecto_nombre').val(p.nombre);
                    $('#proyecto_activo').prop('checked', p.activo == 1);
                    $('#modalProyecto').modal('show');
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() { Swal.fire('Error', 'No se pudo cargar el proyecto', 'error'); }
        });
    }

    $('#formProyecto').on('submit', function(e) {
        e.preventDefault();
        const id  = $('#proyecto_id').val();
        const url = id ? basePath + '/catalogos144/actualizarProyecto' : basePath + '/catalogos144/crearProyecto';
        const data = {
            id: id,
            linea_id: $('#proyecto_linea_id').val(),
            motor_id: $('#proyecto_motor_id').val(),
            codigo: $('#proyecto_codigo').val(),
            nombre: $('#proyecto_nombre').val(),
            activo: $('#proyecto_activo').is(':checked') ? 1 : 0
        };
        $('#btnGuardarProyecto').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');
        $.ajax({
            url: url, type: 'POST', data: data, dataType: 'json',
            success: function(res) {
                $('#btnGuardarProyecto').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Guardado!', text: res.message, timer: 1800, showConfirmButton: false });
                    $('#modalProyecto').modal('hide');
                    cargarProyectos();
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() {
                $('#btnGuardarProyecto').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar');
                Swal.fire('Error', 'Error al comunicarse con el servidor', 'error');
            }
        });
    });

    function cambiarEstadoProyecto(id, nuevoEstado) {
        $.ajax({
            url: basePath + '/catalogos144/cambiarEstadoProyecto',
            type: 'POST', data: { id: id, activo: nuevoEstado }, dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: res.message, timer: 1400, showConfirmButton: false, toast: true, position: 'top-end' });
                    cargarProyectos();
                } else { Swal.fire('Error', res.message, 'error'); }
            }
        });
    }

    // ===================== ELIMINAR (genérico) =====================

    function pedirEliminar(id, nombre, tipo) {
        $('#id_eliminar').val(id);
        $('#tipo_eliminar').val(tipo);
        $('#nombreEliminar').text(nombre);
        $('#modalEliminar').modal('show');
    }

    function confirmarEliminar() {
        const id   = $('#id_eliminar').val();
        const tipo = $('#tipo_eliminar').val();
        const endpoints = {
            linea:      { url: '/catalogos144/eliminarLinea',      reload: cargarLineas },
            estrategia: { url: '/catalogos144/eliminarEstrategia', reload: cargarEstrategias },
            motor:      { url: '/catalogos144/eliminarMotor',      reload: cargarMotores },
            proyecto:   { url: '/catalogos144/eliminarProyecto',   reload: cargarProyectos }
        };
        const cfg = endpoints[tipo];
        if (!cfg) return;

        $.ajax({
            url: basePath + cfg.url,
            type: 'POST', data: { id: id }, dataType: 'json',
            success: function(res) {
                $('#modalEliminar').modal('hide');
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Eliminado!', text: res.message, timer: 1800, showConfirmButton: false });
                    cfg.reload();
                } else { Swal.fire('Error', res.message, 'error'); }
            },
            error: function() { $('#modalEliminar').modal('hide'); Swal.fire('Error', 'Error al eliminar', 'error'); }
        });
    }
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
