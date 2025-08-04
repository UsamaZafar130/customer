<?php
// Cart sync endpoint for logged-in users
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cart = $input['cart'] ?? [];

if (empty($cart)) {
    echo json_encode(['success' => true, 'message' => 'No items to sync']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    foreach ($cart as $item) {
        // Check if item/meal exists and is valid
        if ($item['type'] === 'item') {
            $stmt = $pdo->prepare("SELECT id FROM items WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$item['id']]);
            if (!$stmt->fetch()) continue;
            
            $stmt = $pdo->prepare("
                INSERT INTO customer_cart (user_id, item_id, qty, pack_size) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                qty = qty + VALUES(qty)
            ");
            $stmt->execute([$_SESSION['user_id'], $item['id'], $item['qty'], $item['pack_size']]);
            
        } elseif ($item['type'] === 'meal') {
            $stmt = $pdo->prepare("SELECT id FROM meals WHERE id = ? AND active = 1 AND deleted_at IS NULL");
            $stmt->execute([$item['id']]);
            if (!$stmt->fetch()) continue;
            
            $stmt = $pdo->prepare("
                INSERT INTO customer_cart (user_id, meal_id, qty, pack_size) 
                VALUES (?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE 
                qty = qty + VALUES(qty)
            ");
            $stmt->execute([$_SESSION['user_id'], $item['id'], $item['qty']]);
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Cart synced successfully']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Failed to sync cart']);
}
?>