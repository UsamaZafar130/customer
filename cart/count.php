<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT SUM(qty) as total_items FROM customer_cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $count = (int)($result['total_items'] ?? 0);
    
    echo json_encode(['success' => true, 'count' => $count]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'count' => 0]);
}
?>