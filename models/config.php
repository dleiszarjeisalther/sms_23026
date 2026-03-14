<?php

// Base URL configuration
if (!defined('BASE_URL')) {
    $projectRoot = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $baseUrl = str_replace($docRoot, '', $projectRoot);
    define('BASE_URL', '/' . ltrim($baseUrl, '/'));
}

if (!defined('APP_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('APP_URL', $protocol . "://" . $host . BASE_URL);
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sms_db');

try {
    // Create a PDO instance (Connect to database)
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // On failure, display error message
    die("Database Connection Failed: " . $e->getMessage());
}

// skip this for now
// Skipping for now
$smtp_accounts = [
    [
        'host' => '',
        'username' => '',
        'password' => '',
        'port' => 587
    ],
    [
        'host' => '',
        'username' => '',
        'password' => '',
        'port' => 587
    ]
];
