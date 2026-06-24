<?php
// config/config.php

class Config {
    const DB_HOST = 'localhost';
    const DB_NAME = 'jorginho_app-uniatlantico';
    const DB_USER = 'jorginho_app-uniatlantico';
    const DB_PASS = 'jorginho10.';
    const DB_CHARSET = 'utf8';
    
    const SESSION_TIMEOUT = 1800;
    
    public static function getBasePath() {
        // Use filesystem paths — reliable regardless of how SCRIPT_NAME is set by the server
        if (!empty($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['SCRIPT_FILENAME'])) {
            $docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
            $appDir   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
            if ($docRoot !== '' && stripos($appDir, $docRoot) === 0) {
                return substr($appDir, strlen($docRoot)) ?: '';
            }
        }
        // Fallback for environments where the above vars are unavailable
        return str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? '');
    }

    public static function getBaseUrl() {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        return $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . self::getBasePath();
    }
}

date_default_timezone_set('America/Mexico_City');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $currentTime = time();
    $lastActivity = $_SESSION['last_activity'] ?? $currentTime;
    
    if (($currentTime - $lastActivity) > Config::SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['error'] = 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.';
        header('Location: ' . Config::getBasePath() . '/login');
        exit;
    } else {
        $_SESSION['last_activity'] = $currentTime;
    }
}
?>