<?php
require_once __DIR__ . '/../../config/security.php';

$roles_labels = [
    'admin'       => 'Administrador',
    'director'    => 'Director',
    'coordinador' => 'Coordinador',
    'jefe'        => 'Jefe de Área',
    'analista'    => 'Analista',
    'secretario'  => 'Secretario(a)',
    'auxiliar'    => 'Auxiliar',
    'tecnico'     => 'Técnico',
    'asesor'      => 'Asesor',
    'pasante'     => 'Pasante',
];
$rol_label    = $roles_labels[$usuario['rol']] ?? ucfirst($usuario['rol']);
$avatar_url   = $baseUrl . '/assets/media/users/' . ($usuario['avatar'] ?: 'default.png');
$ultimo_login = $usuario['ultimo_login'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) : 'Nunca';
$miembro_desde = $usuario['fecha_creacion'] ? date('d/m/Y', strtotime($usuario['fecha_creacion'])) : '—';

$cssExtra  = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
$cssExtra .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">';

require_once __DIR__ . '/../complementos/header.php';
?>

<style>
    .perfil-page { max-width: 900px; margin: 0 auto; padding: 32px 20px; }

    /* PAGE TITLE */
    .perfil-title { font-size: 1.9rem; font-weight: 700; color: #1d1d1f; margin-bottom: 28px; letter-spacing: -0.5px; }
    .perfil-title span { color: #0071e3; }

    /* CARDS */
    .perfil-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.07);
        padding: 28px 32px;
        margin-bottom: 22px;
        border: 1px solid rgba(0,0,0,0.06);
    }
    .perfil-card-title {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #86868b;
        margin-bottom: 20px;
    }

    /* HERO */
    .perfil-hero {
        display: flex;
        align-items: center;
        gap: 28px;
        flex-wrap: wrap;
    }
    .perfil-avatar-wrap {
        position: relative;
        width: 100px;
        height: 100px;
        flex-shrink: 0;
        cursor: pointer;
    }
    .perfil-avatar-wrap img {
        width: 100px; height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(0,113,227,0.2);
        display: block;
    }
    .perfil-avatar-overlay {
        position: absolute; inset: 0;
        border-radius: 50%;
        background: rgba(0,0,0,0.45);
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        opacity: 0; transition: opacity .2s;
        color: #fff; font-size: 0.7rem; text-align: center;
        gap: 3px;
    }
    .perfil-avatar-overlay i { font-size: 1.1rem; }
    .perfil-avatar-wrap:hover .perfil-avatar-overlay { opacity: 1; }

    .perfil-hero-info h2 {
        font-size: 1.4rem; font-weight: 700; color: #1d1d1f; margin: 0 0 4px;
    }
    .perfil-hero-info .role-badge {
        display: inline-block;
        background: rgba(0,113,227,0.1);
        color: #0071e3;
        border: 1px solid rgba(0,113,227,0.2);
        border-radius: 20px;
        padding: 3px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        margin-bottom: 6px;
    }
    .perfil-hero-info .dep-text {
        font-size: 0.85rem; color: #6e6e73;
    }
    .perfil-hero-info .dep-text i { margin-right: 4px; }

    /* INFO GRID */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 16px;
    }
    .info-item { display: flex; flex-direction: column; gap: 3px; }
    .info-label { font-size: 0.72rem; font-weight: 600; color: #86868b; text-transform: uppercase; letter-spacing: 0.05em; }
    .info-value { font-size: 0.92rem; color: #1d1d1f; font-weight: 500; }

    /* FORM */
    .pwd-form { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; align-items: end; }
    @media (max-width: 680px) { .pwd-form { grid-template-columns: 1fr; } }
    .field-wrap { display: flex; flex-direction: column; gap: 6px; }
    .field-wrap label { font-size: 0.78rem; font-weight: 600; color: #6e6e73; }
    .field-input {
        border: 1.5px solid rgba(0,0,0,0.12);
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.88rem;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
        width: 100%; box-sizing: border-box;
    }
    .field-input:focus { border-color: #0071e3; box-shadow: 0 0 0 3px rgba(0,113,227,0.12); }
    .pwd-strength { height: 3px; border-radius: 3px; margin-top: 4px; background: #e5e5ea; overflow: hidden; }
    .pwd-strength-bar { height: 100%; border-radius: 3px; transition: width .3s, background .3s; width: 0; }

    /* BUTTONS */
    .btn-primary-perfil {
        background: #0071e3; color: #fff;
        border: none; border-radius: 10px;
        padding: 10px 22px; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; transition: background .2s, transform .1s;
        white-space: nowrap;
    }
    .btn-primary-perfil:hover { background: #005bbf; }
    .btn-primary-perfil:active { transform: scale(0.97); }

    /* UPLOAD FEEDBACK */
    .upload-progress {
        display: none; align-items: center; gap: 10px;
        font-size: 0.82rem; color: #6e6e73; margin-top: 10px;
    }
    .upload-progress.show { display: flex; }
    .spinner-small {
        width: 16px; height: 16px; border: 2px solid #e5e5ea;
        border-top-color: #0071e3; border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ACTIVITY */
    .activity-row { display: flex; gap: 24px; flex-wrap: wrap; }
    .activity-item {
        flex: 1; min-width: 140px;
        background: rgba(0,113,227,0.05);
        border: 1px solid rgba(0,113,227,0.12);
        border-radius: 12px; padding: 16px 20px;
        display: flex; flex-direction: column; gap: 4px;
    }
    .activity-item .act-label { font-size: 0.72rem; color: #86868b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
    .activity-item .act-value { font-size: 1rem; font-weight: 700; color: #1d1d1f; }
</style>

<div class="perfil-page">

    <h1 class="perfil-title">Mi <span>Perfil</span></h1>

    <!-- HERO: Avatar + nombre + rol -->
    <div class="perfil-card">
        <div class="perfil-hero">
            <div class="perfil-avatar-wrap" onclick="document.getElementById('avatarInput').click()" title="Cambiar foto">
                <img id="avatarImg" src="<?php echo $avatar_url; ?>"
                     alt="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                     onerror="this.src='<?php echo $baseUrl; ?>/assets/media/users/default.png'">
                <div class="perfil-avatar-overlay">
                    <i class="fas fa-camera"></i>
                    Cambiar
                </div>
            </div>
            <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none">

            <div class="perfil-hero-info">
                <h2><?php echo htmlspecialchars($usuario['nombre']); ?></h2>
                <div class="role-badge"><?php echo $rol_label; ?></div><br>
                <?php if (!empty($usuario['cargo_nombre'])): ?>
                <div class="dep-text"><i class="fas fa-building"></i><?php echo htmlspecialchars($usuario['cargo_nombre']); ?></div>
                <?php endif; ?>
            </div>

            <div class="upload-progress" id="uploadProgress">
                <div class="spinner-small"></div>
                <span>Subiendo foto...</span>
            </div>
        </div>
    </div>

    <!-- INFORMACIÓN DE CUENTA -->
    <div class="perfil-card">
        <div class="perfil-card-title"><i class="fas fa-id-card me-1"></i> Información de cuenta</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Nombre completo</span>
                <span class="info-value"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Usuario</span>
                <span class="info-value">@<?php echo htmlspecialchars($usuario['username']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Correo electrónico</span>
                <span class="info-value"><?php echo htmlspecialchars($usuario['email']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Rol</span>
                <span class="info-value"><?php echo $rol_label; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Dependencia</span>
                <span class="info-value"><?php echo !empty($usuario['cargo_nombre']) ? htmlspecialchars($usuario['cargo_nombre']) : '—'; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Estado</span>
                <span class="info-value"><?php echo $usuario['activo'] ? '✅ Activo' : '❌ Inactivo'; ?></span>
            </div>
        </div>
    </div>

    <!-- ACTIVIDAD -->
    <div class="perfil-card">
        <div class="perfil-card-title"><i class="fas fa-clock me-1"></i> Actividad</div>
        <div class="activity-row">
            <div class="activity-item">
                <span class="act-label">Último acceso</span>
                <span class="act-value"><?php echo $ultimo_login; ?></span>
            </div>
            <div class="activity-item">
                <span class="act-label">Miembro desde</span>
                <span class="act-value"><?php echo $miembro_desde; ?></span>
            </div>
            <div class="activity-item">
                <span class="act-label">ID de usuario</span>
                <span class="act-value">#<?php echo $usuario['id']; ?></span>
            </div>
        </div>
    </div>

    <!-- CAMBIAR CONTRASEÑA -->
    <div class="perfil-card">
        <div class="perfil-card-title"><i class="fas fa-lock me-1"></i> Cambiar contraseña</div>
        <div class="pwd-form">
            <div class="field-wrap">
                <label>Contraseña actual</label>
                <input type="password" id="pwdActual" class="field-input" placeholder="••••••••">
            </div>
            <div class="field-wrap">
                <label>Nueva contraseña</label>
                <input type="password" id="pwdNueva" class="field-input" placeholder="••••••••" oninput="evaluarFuerza(this.value)">
                <div class="pwd-strength"><div class="pwd-strength-bar" id="pwdBar"></div></div>
            </div>
            <div class="field-wrap">
                <label>Confirmar nueva</label>
                <input type="password" id="pwdConfirmar" class="field-input" placeholder="••••••••">
            </div>
        </div>
        <div style="margin-top: 18px; text-align: right;">
            <button class="btn-primary-perfil" onclick="cambiarContrasena()">
                <i class="fas fa-key me-1"></i> Actualizar contraseña
            </button>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const basePath = '<?php echo $basePath; ?>';
    const baseUrl  = '<?php echo $baseUrl; ?>';

    /* ===== AVATAR ===== */
    document.getElementById('avatarInput').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!allowed.includes(file.type)) {
            Swal.fire({ icon: 'error', title: 'Formato no permitido', text: 'Use JPG, PNG, GIF o WebP', confirmButtonColor: '#0071e3' });
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({ icon: 'error', title: 'Imagen muy grande', text: 'El tamaño máximo es 2 MB', confirmButtonColor: '#0071e3' });
            return;
        }

        // Preview inmediato
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarImg').src = e.target.result;
        reader.readAsDataURL(file);

        // Upload
        const progress = document.getElementById('uploadProgress');
        progress.classList.add('show');

        const formData = new FormData();
        formData.append('avatar', file);

        fetch(basePath + '/perfil/update-avatar', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                progress.classList.remove('show');
                if (data.success) {
                    // Actualizar también el avatar del header
                    document.querySelectorAll('.userAvatar img').forEach(img => img.src = data.avatar_url);
                    Swal.fire({ icon: 'success', title: '¡Foto actualizada!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#0071e3' });
                }
            })
            .catch(() => {
                progress.classList.remove('show');
                Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#0071e3' });
            });
    });

    /* ===== FUERZA DE CONTRASEÑA ===== */
    function evaluarFuerza(pwd) {
        const bar = document.getElementById('pwdBar');
        let score = 0;
        if (pwd.length >= 6)  score++;
        if (pwd.length >= 10) score++;
        if (/[A-Z]/.test(pwd)) score++;
        if (/[0-9]/.test(pwd)) score++;
        if (/[^A-Za-z0-9]/.test(pwd)) score++;

        const pct   = (score / 5) * 100;
        const color = score <= 1 ? '#ff3b30' : score <= 3 ? '#ff9f0a' : '#34c759';
        bar.style.width = pct + '%';
        bar.style.background = color;
    }

    /* ===== CAMBIAR CONTRASEÑA ===== */
    function cambiarContrasena() {
        const actual    = document.getElementById('pwdActual').value.trim();
        const nueva     = document.getElementById('pwdNueva').value;
        const confirmar = document.getElementById('pwdConfirmar').value;

        if (!actual || !nueva || !confirmar) {
            Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Completa todos los campos', confirmButtonColor: '#0071e3' });
            return;
        }
        if (nueva.length < 6) {
            Swal.fire({ icon: 'warning', title: 'Contraseña muy corta', text: 'Mínimo 6 caracteres', confirmButtonColor: '#0071e3' });
            return;
        }
        if (nueva !== confirmar) {
            Swal.fire({ icon: 'warning', title: 'No coinciden', text: 'La nueva contraseña y su confirmación deben ser iguales', confirmButtonColor: '#0071e3' });
            return;
        }

        fetch(basePath + '/perfil/update-password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password_actual: actual, password_nueva: nueva, password_confirmar: confirmar })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('pwdActual').value    = '';
                document.getElementById('pwdNueva').value     = '';
                document.getElementById('pwdConfirmar').value = '';
                document.getElementById('pwdBar').style.width = '0';
                Swal.fire({ icon: 'success', title: '¡Contraseña actualizada!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#0071e3' });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#0071e3' }));
    }
</script>

<?php require_once __DIR__ . '/../complementos/footer.php'; ?>
