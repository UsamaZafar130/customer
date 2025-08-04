<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin_auth.php';

// Logout admin user
logoutAdmin();

// Redirect to admin login
header('Location: /auth/admin_login.php');
exit;
?>