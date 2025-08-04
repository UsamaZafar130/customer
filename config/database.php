<?php
// Set timezone to UTC+5 (Pakistan Standard Time)
date_default_timezone_set('Asia/Karachi');

// Include paths configuration
require_once __DIR__ . '/paths.php';

// Database configuration
$config = [
    'host' => 'localhost',
    'username' => 'frozofun_usama',
    'password' => 'mEnAAl86UsAmA!@',
    'database' => 'frozofun_main'
];

// Create database connection
function getDbConnection() {
    global $config;
    
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4",
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Get database connection instance
$pdo = getDbConnection();
?>