<?php
// Delete address endpoint
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$addressId = (int)($input['address_id'] ?? 0);

if (!$addressId) {
    echo json_encode(['success' => false, 'message' => 'Invalid address ID']);
    exit;
}

try {
    // Soft delete - set deleted_at timestamp
    $stmt = $pdo->prepare("
        UPDATE customers 
        SET deleted_at = NOW() 
        WHERE id = ? AND user_id = ? AND deleted_at IS NULL
    ");
    $stmt->execute([$addressId, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Address deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Address not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete address']);
}
?>