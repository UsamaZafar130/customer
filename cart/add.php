<?php
session_start();
// Add to cart endpoint for logged-in users
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? '';
$id = (int)($input['id'] ?? 0);
$qty = (float)($input['qty'] ?? 1);
$packSize = (float)($input['pack_size'] ?? 1);

if (!in_array($type, ['item', 'meal']) || !$id || $qty <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    if ($type === 'item') {
        // Verify item exists
        $stmt = $pdo->prepare("SELECT name FROM items WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        // Check if item already exists in cart
        $stmt = $pdo->prepare("SELECT id, qty FROM customer_cart WHERE user_id = ? AND item_id = ? AND pack_size = ?");
        $stmt->execute([$_SESSION['user_id'], $id, $packSize]);
        $existingItem = $stmt->fetch();
        
        if ($existingItem) {
            // Update existing item quantity
            $stmt = $pdo->prepare("UPDATE customer_cart SET qty = qty + ? WHERE id = ?");
            $stmt->execute([$qty, $existingItem['id']]);
        } else {
            // Insert new item
            $stmt = $pdo->prepare("INSERT INTO customer_cart (user_id, item_id, qty, pack_size) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $id, $qty, $packSize]);
        }
        
    } else { // meal
        // Verify meal exists
        $stmt = $pdo->prepare("SELECT name FROM meals WHERE id = ? AND active = 1 AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Meal not found']);
            exit;
        }
        
        // Check if meal already exists in cart
        $stmt = $pdo->prepare("SELECT id, qty FROM customer_cart WHERE user_id = ? AND meal_id = ?");
        $stmt->execute([$_SESSION['user_id'], $id]);
        $existingItem = $stmt->fetch();
        
        if ($existingItem) {
            // Update existing meal quantity
            $stmt = $pdo->prepare("UPDATE customer_cart SET qty = qty + ? WHERE id = ?");
            $stmt->execute([$qty, $existingItem['id']]);
        } else {
            // Insert new meal
            $stmt = $pdo->prepare("INSERT INTO customer_cart (user_id, meal_id, qty, pack_size) VALUES (?, ?, ?, 1)");
            $stmt->execute([$_SESSION['user_id'], $id, $qty]);
        }
    }
    
    echo json_encode(['success' => true, 'message' => $product['name'] . ' added to cart']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
}
?>