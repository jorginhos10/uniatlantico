<?php
// Vista pública de registro — no requiere sesión
require_once __DIR__ . '/../../config/config.php';
$basePath = Config::getBasePath();
$baseUrl  = Config::getBaseUrl();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario — Universidad del Atlántico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --blue:    #007AFF;
            --green:   #34C759;
            --red:     #FF3B30;
            --gray:    #8E8E93;
            --bg:      #F2F2F7;
            --surface: #FFFFFF;
            --label:   #1C1C1E;
            --label2:  rgba(60,60,67,.6);
            --sep:     rgba(60,60,67,.12);
            --font: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
        }
        body {
            font-family: var(--font);
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .reg-card {
            background: var(--surface);
            border-radius: 24px;
            box-shadow: 0 4px 32px rgba(0,0,0,.10);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }

        .reg-header {
            background: linear-gradient(135deg, #007AFF 0%, #5856D6 100%);
            padding: 32px 32px 28px;
            text-align: center;
            color: white;
        }
        .reg-header .logo-icon {
            width: 64px; height: 64px;
            background: rgba(255,255,255,.2);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            margin: 0 auto 16px;
        }
        .reg-header h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .reg-header p  { font-size: 14px; opacity: .85; }

        .reg-body { padding: 28px 32px 32px; }

        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--label);
            margin-bottom: 7px;
        }
        .form-control {
            width: 100%;
            border: 1.5px solid var(--sep);
            border-radius: 12px;
            padding: 12px 14px;
            font-family: var(--font);
            font-size: 15px;
            background: var(--bg);
            color: var(--label);
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--blue);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(0,122,255,.15);
        }
        .form-control.error { border-color: var(--red); }
        .field-error { font-size: 12px; color: var(--red); margin-top: 5px; display: none; }

        .input-icon-wrap { position: relative; }
        .input-icon-wrap .form-control { padding-left: 40px; }
        .input-icon-wrap i {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: var(--gray); font-size: 15px;
        }
        .toggle-pass {
            position: absolute; right: 13px; top: 50%;
            transform: translateY(-50%);
            color: var(--gray); font-size: 15px;
            cursor: pointer; background: none; border: none; padding: 0;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--blue);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            font-family: var(--font);
            cursor: pointer;
            margin-top: 6px;
            transition: opacity .2s, transform .15s;
        }
        .btn-submit:hover { opacity: .88; transform: translateY(-1px); }
        .btn-submit:disabled { opacity: .55; cursor: not-allowed; transform: none; }

        .aviso-pendiente {
            background: rgba(255,149,0,.10);
            border: 1px solid rgba(255,149,0,.3);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 13px;
            color: #8a5700;
            display: flex; gap: 8px; align-items: flex-start;
            margin-bottom: 20px;
        }
        .divider {
            border: none; border-top: 1px solid var(--sep);
            margin: 22px 0;
        }
        .login-link {
            text-align: center; font-size: 14px; color: var(--label2);
        }
        .login-link a { color: var(--blue); font-weight: 600; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
        .footer-text {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: var(--label2);
        }
    </style>
</head>
<body>

<div class="reg-card">
    <div class="reg-header">
        <div class="logo-icon"><i class="fas fa-university"></i></div>
        <h1>Universidad del Atlántico</h1>
        <p>Crea tu cuenta para acceder al sistema</p>
    </div>

    <div class="reg-body">

        <div class="aviso-pendiente">
            <i class="fas fa-info-circle" style="margin-top:1px;flex-shrink:0;"></i>
            <div>Tu cuenta quedará <strong>pendiente de aprobación</strong>. Un administrador la activará antes de que puedas ingresar.</div>
        </div>

        <form id="formRegistro" novalidate>

            <div class="form-group">
                <label class="form-label">Nombre completo *</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: María García López" autocomplete="name">
                </div>
                <div class="field-error" id="err-nombre"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Correo electrónico *</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control" id="email" name="email" placeholder="correo@uniatlantico.edu.co" autocomplete="email">
                </div>
                <div class="field-error" id="err-email"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Nombre de usuario *</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-at"></i>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Ej: mgarcia" autocomplete="username">
                </div>
                <div class="field-error" id="err-username"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Rol *</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-user-tag"></i>
                    <select class="form-control" id="rol" name="rol" style="padding-left:40px;appearance:none;">
                        <option value="">Selecciona un rol...</option>
                        <?php foreach (($roles ?? []) as $r): ?>
                            <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars(ucfirst($r)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-error" id="err-rol"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Dependencia</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-building"></i>
                    <select class="form-control" id="cargo_id" name="cargo_id" style="padding-left:40px;appearance:none;">
                        <option value="">Selecciona una dependencia...</option>
                        <?php foreach (($cargos ?? []) as $c): ?>
                            <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-error" id="err-cargo_id"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña *</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                    <button type="button" class="toggle-pass" onclick="togglePass('password', this)"><i class="fas fa-eye"></i></button>
                </div>
                <div class="field-error" id="err-password"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Confirmar contraseña *</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Repite tu contraseña" autocomplete="new-password">
                    <button type="button" class="toggle-pass" onclick="togglePass('password_confirmation', this)"><i class="fas fa-eye"></i></button>
                </div>
                <div class="field-error" id="err-password_confirmation"></div>
            </div>

            <button type="submit" class="btn-submit" id="btnRegistrar">
                <i class="fas fa-user-plus me-1"></i> Crear cuenta
            </button>
        </form>

        <hr class="divider">
        <div class="login-link">
            ¿Ya tienes cuenta? <a href="<?php echo $basePath; ?>/login">Inicia sesión</a>
        </div>
    </div>
</div>

<p class="footer-text">© <?php echo date('Y'); ?> Universidad del Atlántico — Sistema de Gestión</p>

<script>
const basePath = '<?php echo $basePath; ?>';

function togglePass(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function setError(field, msg) {
    const input = document.getElementById(field);
    const err   = document.getElementById('err-' + field);
    if (input)  input.classList.add('error');
    if (err)  { err.textContent = msg; err.style.display = 'block'; }
}

function clearErrors() {
    document.querySelectorAll('.form-control').forEach(i => i.classList.remove('error'));
    document.querySelectorAll('.field-error').forEach(e => { e.textContent = ''; e.style.display = 'none'; });
}

document.getElementById('formRegistro').addEventListener('submit', function(e) {
    e.preventDefault();
    clearErrors();

    const nombre   = document.getElementById('nombre').value.trim();
    const email    = document.getElementById('email').value.trim();
    const username = document.getElementById('username').value.trim();
    const rol      = document.getElementById('rol').value;
    const cargo_id = document.getElementById('cargo_id').value;
    const password = document.getElementById('password').value;
    const confirm  = document.getElementById('password_confirmation').value;

    let ok = true;
    if (!nombre)   { setError('nombre', 'El nombre es obligatorio'); ok = false; }
    if (!email)    { setError('email',  'El correo es obligatorio');  ok = false; }
    if (!username) { setError('username','El usuario es obligatorio'); ok = false; }
    if (!rol)      { setError('rol','Selecciona un rol'); ok = false; }
    if (!password) { setError('password','La contraseña es obligatoria'); ok = false; }
    else if (password.length < 6) { setError('password','Mínimo 6 caracteres'); ok = false; }
    if (password !== confirm) { setError('password_confirmation','Las contraseñas no coinciden'); ok = false; }
    if (!ok) return;

    const btn = document.getElementById('btnRegistrar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

    fetch(basePath + '/registroPublico', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre, email, username, rol, cargo_id, password, password_confirmation: confirm })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Cuenta creada!',
                html: 'Tu cuenta está <strong>pendiente de aprobación</strong>.<br>Un administrador la activará pronto.',
                confirmButtonColor: '#007AFF',
                confirmButtonText: 'Entendido'
            }).then(() => { window.location.href = basePath + '/login'; });
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-plus"></i> Crear cuenta';
            if (res.errors) {
                Object.keys(res.errors).forEach(k => setError(k, res.errors[k][0]));
            } else {
                Swal.fire('Error', res.message || 'No se pudo crear la cuenta', 'error');
            }
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-plus"></i> Crear cuenta';
        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
    });
});
</script>
</body>
</html>
