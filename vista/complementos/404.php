<?php
// vista/complementos/404.php

http_response_code(404);

$basePath = Config::getBasePath();
$baseUrl  = Config::getBaseUrl();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página No Encontrada — 404</title>
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
            background: rgba(255,149,0,.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .error-icon-wrap svg {
            width: 44px;
            height: 44px;
            fill: #FF9500;
        }

        .error-code {
            font-size: 80px;
            font-weight: 800;
            color: #FF9500;
            letter-spacing: -3px;
            line-height: 1;
            margin-bottom: 14px;
        }

        .error-title {
            font-size: 22px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 12px;
            letter-spacing: -.3px;
        }

        .error-description {
            font-size: 15px;
            color: rgba(60,60,67,.6);
            line-height: 1.6;
            margin-bottom: 32px;
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
        }

        .btn:hover { opacity: .86; transform: translateY(-1px); }

        .btn-primary   { background: #007AFF; color: #fff; }
        .btn-secondary { background: #F2F2F7; color: #007AFF; border: 1.5px solid rgba(0,122,255,.2); }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon-wrap">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8.009 8.009 0 0 1-8 8zm3.293-11.293a1 1 0 0 0-1.414 0L12 10.586l-1.879-1.879a1 1 0 0 0-1.414 1.414L10.586 12l-1.879 1.879a1 1 0 1 0 1.414 1.414L12 13.414l1.879 1.879a1 1 0 0 0 1.414-1.414L13.414 12l1.879-1.879a1 1 0 0 0 0-1.414z"/>
            </svg>
        </div>

        <div class="error-code">404</div>
        <h1 class="error-title">Página No Encontrada</h1>
        <p class="error-description">
            La página que estás buscando no existe o ha sido movida.<br>
            Verifica la dirección o regresa al inicio.
        </p>

        <div class="btn-group">
            <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-primary">Ir al Dashboard</a>
            <a href="<?php echo $basePath; ?>/publica" class="btn btn-secondary">Página Pública</a>
        </div>
    </div>
</body>
</html>
