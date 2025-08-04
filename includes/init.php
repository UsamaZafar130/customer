<?php
// Include security and HTTPS redirection
require_once __DIR__ . '/security.php';

session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user info
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM customer_users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Format price display
function formatPrice($price) {
    return 'Rs. ' . number_format($price, 2);
}

// Get item display name with pack size
function getItemDisplayName($name, $packSize, $pricePerUnit) {
    $totalPrice = $pricePerUnit * $packSize;
    return "{$name} {$packSize} Pcs, Price: " . formatPrice($totalPrice);
}

// Escape HTML
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Get cart count for logged-in users
function getCartCount() {
    global $pdo;
    if (!isLoggedIn()) {
        return 0;
    }
    
    $stmt = $pdo->prepare("SELECT SUM(qty) as total_items FROM customer_cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    return (int)($result['total_items'] ?? 0);
}
?>