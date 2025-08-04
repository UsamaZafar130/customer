<?php
/**
 * Admin Authentication Functions
 * Handles admin user authentication from the users table
 */

// Check if admin user is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_user_id']) && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}

// Get current admin user info
function getCurrentAdminUser() {
    global $pdo;
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin' AND deleted_at IS NULL");
    $stmt->execute([$_SESSION['admin_user_id']]);
    return $stmt->fetch();
}

// Authenticate admin user
function authenticateAdmin($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_name'] = $user['name'];
        
        // Update last login
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return true;
    }
    
    return false;
}

// Logout admin user
function logoutAdmin() {
    unset($_SESSION['admin_user_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_role']);
    unset($_SESSION['admin_name']);
}

// Require admin authentication - redirect if not logged in
function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        // Redirect to admin login with return URL
        $current_url = urlencode($_SERVER['REQUEST_URI']);
        header("Location: /auth/admin_login.php?redirect=" . $current_url);
        exit;
    }
}
?>