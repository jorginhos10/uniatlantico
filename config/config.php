<?php
// config/config.php

class Config {
    const DB_HOST = 'localhost';
    const DB_NAME = 'uniatlantico';
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_CHARSET = 'utf8';
    
    const SESSION_TIMEOUT = 1800;
    
    public static function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        $basePath = str_replace('/index.php', '', $script);
        return $protocol . "://" . $host . $basePath;
    }
    
    public static function getBasePath() {
        $script = $_SERVER['SCRIPT_NAME'];
        return str_replace('/index.php', '', $script);
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