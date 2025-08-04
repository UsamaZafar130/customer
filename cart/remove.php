<?php
// Cart remove endpoint
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cartId = (int)($input['cart_id'] ?? 0);

if (!$cartId) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM customer_cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cartId, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
}
?>