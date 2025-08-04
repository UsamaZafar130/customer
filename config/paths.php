<?php
// Define base paths for the application
define('APP_ROOT', dirname(__DIR__));
define('INCLUDES_PATH', APP_ROOT . '/includes');
define('CONFIG_PATH', APP_ROOT . '/config');
define('ASSETS_PATH', APP_ROOT . '/assets');

// Helper function to get absolute path
function getAbsolutePath($relativePath) {
    return APP_ROOT . '/' . ltrim($relativePath, '/');
}
?>