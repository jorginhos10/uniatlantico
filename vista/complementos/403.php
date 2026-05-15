<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado — 403</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', sans-serif;
            background: #F2F2F7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .error-card {
            background: #FFFFFF;
            border-radius: 24px;
            box-shadow: 0 4px 32px rgba(0,0,0,.10), 0 1px 4px rgba(0,0,0,.06);
            max-width: 480px;
            width: 100%;
            padding: 48px 40px 40px;
            text-align: center;
        }

        .error-icon-wrap {
            width: 88px;
            height: 88px;
            background: rgba(255,59,48,.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .error-icon-wrap svg {
            width: 44px;
            height: 44px;
            fill: #FF3B30;
        }

        .error-code {
            font-size: 72px;
            font-weight: 800;
            color: #FF3B30;
            letter-spacing: -2px;
            line-height: 1;
            margin-bottom: 12px;
        }

        .error-title {
            font-size: 22px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 12px;
            letter-spacing: -.3px;
        }

        .error-message {
            font-size: 15px;
            color: rgba(60,60,67,.6);
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .info-box {
            background: rgba(0,122,255,.07);
            border-left: 3px solid #007AFF;
            border-radius: 10px;
            padding: 14px 18px;
            margin: 20px 0 28px;
            text-align: left;
            font-size: 14px;
            color: rgba(60,60,67,.8);
            line-height: 1.55;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: opacity .2s, transform .15s;
            font-family: inherit;
            border: none;
        }

        .btn:hover { opacity: .86; transform: translateY(-1px); }

        .btn-primary { background: #007AFF; color: #fff; }
        .btn-secondary { background: #F2F2F7; color: #007AFF; border: 1.5px solid rgba(0,122,255,.2); }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon-wrap">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 1a11 11 0 1 0 11 11A11.013 11.013 0 0 0 12 1zm0 20a9 9 0 1 1 9-9 9.01 9.01 0 0 1-9 9zm1-5h-2v-2h2zm0-4h-2V7h2z"/>
            </svg>
        </div>

        <div class="error-code">403</div>
        <div class="error-title">Acceso Denegado</div>

        <p class="error-message">
            No tienes permisos para acceder a esta página.<br>
            Tu rol actual no está autorizado para ver este contenido.
        </p>

        <div class="info-box">
            Si crees que deberías tener acceso, contacta con el administrador del sistema para solicitar los permisos necesarios.
        </div>

        <div class="btn-group">
            <a href="login.php" class="btn btn-primary">Ir al Inicio</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Volver Atrás</a>
        </div>
    </div>
</body>
</html>
