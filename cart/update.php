<?php
// Cart update endpoint
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cartId = (int)($input['cart_id'] ?? 0);
$qty = (float)($input['qty'] ?? 0);

if (!$cartId || $qty <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE customer_cart 
        SET qty = ?, updated_at = NOW() 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$qty, $cartId, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
}
?>