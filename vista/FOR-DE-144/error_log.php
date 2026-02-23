<?php
// error_log.php - Muestra los últimos errores PHP
header('Content-Type: text/plain');

$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    echo implode('', $lastLines);
} else {
    // Intentar leer el log por defecto
    $possibleLogs = [
        'error_log',
        '../error_log',
        '/var/log/apache2/error.log',
        '/var/log/nginx/error.log'
    ];
    
    $found = false;
    foreach ($possibleLogs as $log) {
        if (file_exists($log)) {
            $lines = file($log);
            $lastLines = array_slice($lines, -50);
            echo implode('', $lastLines);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "No se encontraron logs de error PHP";
    }
}
?>