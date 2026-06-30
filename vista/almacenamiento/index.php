<?php
require_once __DIR__ . '/../../config/security.php';

$titulo       = 'Almacenamiento';
$paginaActual = 'almacenamiento';
$baseUrl      = Config::getBaseUrl();
$basePath     = Config::getBasePath();

$cssExtra = '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">';
$cssExtra .= '<link rel="stylesheet" href="' . $baseUrl . '/assets/css/configuraciones.css">';
require_once __DIR__ . '/../complementos/header.php';
?>

<style>
    :root {
        --ios-blue:    #007AFF;
        --ios-green:   #34C759;
        --ios-red:     #FF3B30;
        --ios-orange:  #FF9500;
        --ios-gray:    #8E8E93;
        --ios-bg:      #F2F2F7;
        --ios-surface: #FFFFFF;
        --ios-label:   #000000;
        --ios-label2:  rgba(60,60,67,.6);
        --ios-sep:     rgba(60,60,67,.12);
        --font: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
    }
    body { font-family: var(--font); background: var(--ios-bg); }
    .alm-wrap { max-width: 1100px; margin: 0 auto; padding: 28px 20px; }
    .alm-header {
        background: linear-gradient(135deg, #5856D6 0%, #8e44ad 100%);
        color: white; border-radius: 20px; padding: 28px 32px;
        margin-bottom: 32px; display: flex; align-items: center;
        justify-content: space-between; gap: 16px; flex-wrap: wrap;
        box-shadow: 0 6px 24px rgba(88,86,214,.35);
    }
    .alm-header-left { display: flex; align-items: center; gap: 18px; }
    .alm-header-icon {
        width: 56px; height: 56px; background: rgba(255,255,255,.2);
        border-radius: 16px; display: flex; align-items: center;
        justify-content: center; font-size: 24px; flex-shrink: 0;
    }
    .alm-header h1 { font-size: 24px; font-weight: 700; margin: 0 0 4px; letter-spacing: -.3px; }
    .alm-header p  { font-size: 14px; opacity: .85; margin: 0; }
    .btn-volver {
        background: rgba(255,255,255,.2); color: white;
        border: 1.5px solid rgba(255,255,255,.4); padding: 10px 20px;
        border-radius: 12px; font-weight: 600; font-size: 14px;
        text-decoration: none; display: flex; align-items: center;
        gap: 7px; transition: background .2s; white-space: nowrap;
    }
    .btn-volver:hover { background: rgba(255,255,255,.32); color: white; }
    .section-title {
        font-size: 13px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .6px; color: var(--ios-label2); margin: 0 0 14px 4px;
    }
    .alm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px; margin-bottom: 32px;
    }
    .alm-card {
        background: var(--ios-surface); border-radius: 18px; padding: 24px 22px;
        text-decoration: none; display: flex; align-items: flex-start; gap: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,.07);
        transition: transform .18s, box-shadow .18s;
        border: 1.5px solid transparent;
    }
    .alm-card.clickable { cursor: pointer; }
    .alm-card.clickable:hover {
        transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.13);
        border-color: var(--ios-sep); text-decoration: none;
    }
    .alm-card.disabled { cursor: default; opacity: .75; }
    .alm-card-icon {
        width: 50px; height: 50px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; color: white; flex-shrink: 0;
    }
    .alm-card-body h3 { font-size: 15px; font-weight: 700; color: var(--ios-label); margin: 0 0 5px; }
    .alm-card-body p  { font-size: 13px; color: var(--ios-label2); margin: 0; line-height: 1.45; }
    .alm-badge {
        margin-top: 10px; display: inline-flex; align-items: center;
        gap: 5px; font-size: 11px; font-weight: 600;
        padding: 3px 10px; border-radius: 20px;
    }
    .badge-ok    { background: rgba(52,199,89,.12);  color: #1a7a3a; }
    .badge-soon  { background: rgba(255,149,0,.12);  color: #b36b00; }

    /* Modal */
    .modal-content  { border-radius: 20px; border: none; overflow: hidden; font-family: var(--font); }
    .modal-header   { background: var(--ios-surface); border-bottom: 1px solid var(--ios-sep); padding: 20px 24px; }
    .modal-header .modal-title { font-weight: 700; font-size: 18px; }
    .modal-body     { padding: 24px; }
    .modal-footer   { background: var(--ios-bg); border-top: 1px solid var(--ios-sep); padding: 16px 24px; }
    .form-label     { font-weight: 600; font-size: 13px; color: var(--ios-label); margin-bottom: 6px; }
    .form-control   { border: 1.5px solid var(--ios-sep); border-radius: 12px; padding: 11px 14px; font-family: var(--font); font-size: 15px; background: var(--ios-bg); }
    .form-control:focus { outline: none; border-color: var(--ios-blue); box-shadow: 0 0 0 3px rgba(0,122,255,.15); }
    .btn-ios { padding: 11px 22px; border: none; border-radius: 12px; font-weight: 600; font-size: 15px; cursor: pointer; font-family: var(--font); transition: opacity .2s; }
    .btn-ios:hover { opacity: .87; }
    .btn-ios-primary   { background: var(--ios-blue);   color: white; }
    .btn-ios-secondary { background: var(--ios-bg); color: var(--ios-blue); border: 1.5px solid var(--ios-sep); }
    .btn-ios-danger    { background: var(--ios-red);    color: white; }
    .alerta-danger {
        background: rgba(255,59,48,.08); border: 1px solid rgba(255,59,48,.25);
        border-radius: 12px; padding: 14px 16px; font-size: 13px;
        color: #c0392b; display: flex; gap: 10px; align-items: flex-start;
        margin-bottom: 18px;
    }
    .drop-zone {
        border: 2px dashed var(--ios-sep); border-radius: 14px;
        padding: 40px 20px; text-align: center; cursor: pointer;
        transition: border-color .2s, background .2s;
    }
    .drop-zone:hover, .drop-zone.drag-over { border-color: var(--ios-blue); background: rgba(0,122,255,.04); }
    .drop-zone i { font-size: 40px; color: var(--ios-blue); margin-bottom: 12px; display: block; }
    .drop-zone p { margin: 0; font-size: 14px; color: var(--ios-label2); }
    .drop-zone .file-name { font-size: 13px; font-weight: 600; color: var(--ios-blue); margin-top: 8px; }

    @media (max-width: 600px) {
        .alm-grid { grid-template-columns: 1fr; }
        .alm-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="alm-wrap">

    <div class="alm-header">
        <div class="alm-header-left">
            <div class="alm-header-icon"><i class="fas fa-database"></i></div>
            <div>
                <h1>Almacenamiento</h1>
                <p>Gestiona el espacio, copias de seguridad y sincronización del sistema</p>
            </div>
        </div>
        <a href="<?php echo $basePath; ?>/configuraciones" class="btn-volver">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <p class="section-title">Herramientas disponibles</p>

    <div class="alm-grid">

        <!-- Backup -->
        <a href="<?php echo $basePath; ?>/almacenamiento/backup" class="alm-card clickable">
            <div class="alm-card-icon" style="background: linear-gradient(135deg,#34C759,#2ecc71);">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="alm-card-body">
                <h3>Backup</h3>
                <p>Descarga una copia completa de la base de datos en formato <strong>.javb</strong></p>
                <span class="alm-badge badge-ok"><i class="fas fa-download"></i> Descargar ahora</span>
            </div>
        </a>

        <!-- Sincronización -->
        <div class="alm-card clickable" onclick="abrirModalSincronizar()">
            <div class="alm-card-icon" style="background: linear-gradient(135deg,#FF9500,#e67e22);">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="alm-card-body">
                <h3>Sincronización</h3>
                <p>Restaura la base de datos cargando un archivo <strong>.javb</strong> de respaldo</p>
                <span class="alm-badge badge-ok"><i class="fas fa-upload"></i> Cargar archivo</span>
            </div>
        </div>

        <!-- Espacio -->
        <div class="alm-card disabled">
            <div class="alm-card-icon" style="background: linear-gradient(135deg,#007AFF,#5856D6);">
                <i class="fas fa-hdd"></i>
            </div>
            <div class="alm-card-body">
                <h3>Espacio en disco</h3>
                <p>Monitorea el uso del almacenamiento y libera archivos temporales</p>
                <span class="alm-badge badge-soon"><i class="fas fa-clock"></i> Próximamente</span>
            </div>
        </div>

        <!-- Logs -->
        <div class="alm-card disabled">
            <div class="alm-card-icon" style="background: linear-gradient(135deg,#FF3B30,#c0392b);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="alm-card-body">
                <h3>Registros del sistema</h3>
                <p>Consulta y descarga los logs de actividad y errores del sistema</p>
                <span class="alm-badge badge-soon"><i class="fas fa-clock"></i> Próximamente</span>
            </div>
        </div>

    </div>
</div>

<!-- Modal Sincronización -->
<div class="modal fade" id="modalSincronizar" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-sync-alt me-2" style="color:var(--ios-orange);"></i> Restaurar base de datos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alerta-danger">
                    <i class="fas fa-exclamation-triangle" style="margin-top:2px;flex-shrink:0;"></i>
                    <div><strong>Acción destructiva:</strong> todos los datos actuales serán reemplazados por los del archivo. Esta operación no se puede deshacer.</div>
                </div>

                <label class="form-label">Selecciona un archivo <strong>.javb</strong></label>
                <div class="drop-zone" id="dropZone" onclick="document.getElementById('archivoJavb').click()">
                    <i class="fas fa-file-upload"></i>
                    <p>Haz clic o arrastra aquí tu archivo <strong>.javb</strong></p>
                    <div class="file-name" id="nombreArchivo" style="display:none;"></div>
                </div>
                <input type="file" id="archivoJavb" style="display:none;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-ios btn-ios-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn-ios btn-ios-danger" id="btnRestaurar" onclick="confirmarRestauraur()" disabled>
                    <i class="fas fa-upload me-1"></i> Restaurar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const basePath = '<?php echo $basePath; ?>';

function esJavb(nombre) {
    return nombre.toLowerCase().endsWith('.javb');
}

function mostrarArchivo(file) {
    document.getElementById('nombreArchivo').textContent = file.name;
    document.getElementById('nombreArchivo').style.display = 'block';
    document.getElementById('btnRestaurar').disabled = false;
}

function abrirModalSincronizar() {
    document.getElementById('archivoJavb').value = '';
    document.getElementById('nombreArchivo').style.display = 'none';
    document.getElementById('btnRestaurar').disabled = true;
    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSincronizar'));
    modal.show();
}

// Click en la drop-zone abre el selector de archivos
document.getElementById('dropZone').addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('archivoJavb').click();
});

// Selección de archivo via input
document.getElementById('archivoJavb').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    if (!esJavb(file.name)) {
        Swal.fire('Archivo inválido', 'Solo se aceptan archivos con extensión .javb', 'error');
        this.value = '';
        return;
    }
    mostrarArchivo(file);
});

// Drag & Drop
const dz = document.getElementById('dropZone');
dz.addEventListener('dragover',  function(e) { e.preventDefault(); e.stopPropagation(); dz.classList.add('drag-over'); });
dz.addEventListener('dragleave', function(e) { e.stopPropagation(); dz.classList.remove('drag-over'); });
dz.addEventListener('drop', function(e) {
    e.preventDefault();
    e.stopPropagation();
    dz.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (!file) return;
    if (!esJavb(file.name)) {
        Swal.fire('Archivo inválido', 'Solo se aceptan archivos con extensión .javb', 'error');
        return;
    }
    try {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('archivoJavb').files = dt.files;
        _droppedFile = null;
    } catch(ex) {
        _droppedFile = file; // guardar referencia directa como fallback
    }
    mostrarArchivo(file);
});

let _droppedFile = null; // fallback cuando DataTransfer no está disponible

function confirmarRestauraur() {
    const file = document.getElementById('archivoJavb').files[0] || _droppedFile;
    if (!file) return;

    Swal.fire({
        title: '¿Restaurar base de datos?',
        html: `Se reemplazarán <strong>todos los datos actuales</strong> con los del archivo <em>${file.name}</em>.<br><br>Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF3B30',
        cancelButtonColor: '#8E8E93',
        confirmButtonText: 'Sí, restaurar',
        cancelButtonText: 'Cancelar',
    }).then(result => {
        if (!result.isConfirmed) return;

        const formData = new FormData();
        formData.append('archivo', file);

        document.getElementById('btnRestaurar').disabled = true;
        document.getElementById('btnRestaurar').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Restaurando...';

        $.ajax({
            url: basePath + '/almacenamiento/sincronizar',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                bootstrap.Modal.getInstance(document.getElementById('modalSincronizar')).hide();
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Restaurado!',
                        text: res.message,
                        confirmButtonColor: '#34C759'
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message, 'error');
                    document.getElementById('btnRestaurar').disabled = false;
                    document.getElementById('btnRestaurar').innerHTML = '<i class="fas fa-upload me-1"></i> Restaurar';
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo comunicar con el servidor', 'error');
                document.getElementById('btnRestaurar').disabled = false;
                document.getElementById('btnRestaurar').innerHTML = '<i class="fas fa-upload me-1"></i> Restaurar';
            }
        });
    });
}
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
