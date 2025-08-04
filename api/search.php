<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$query = trim($query);

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

try {
    // Search in items
    $itemStmt = $pdo->prepare("
        SELECT 'item' as type, id, name, price_per_unit, default_pack_size, image, 
               CONCAT(name, ' ', default_pack_size, ' Pcs') as display_name
        FROM items 
        WHERE deleted_at IS NULL 
        AND name LIKE ? 
        ORDER BY name ASC 
        LIMIT 3
    ");
    $itemStmt->execute(['%' . $query . '%']);
    $items = $itemStmt->fetchAll();

    // Search in meals
    $mealStmt = $pdo->prepare("
        SELECT 'meal' as type, id, name, price, image,
               name as display_name
        FROM meals 
        WHERE deleted_at IS NULL 
        AND name LIKE ? 
        ORDER BY name ASC 
        LIMIT 2
    ");
    $mealStmt->execute(['%' . $query . '%']);
    $meals = $mealStmt->fetchAll();

    // Combine and format results
    $results = [];
    
    foreach ($items as $item) {
        $results[] = [
            'type' => 'item',
            'id' => $item['id'],
            'name' => $item['name'],
            'display_name' => $item['display_name'],
            'price' => 'Rs. ' . number_format($item['price_per_unit'] * $item['default_pack_size'], 2),
            'image' => $item['image'] ? 'https://admin.frozofun.com/uploads/items/' . $item['image'] : null,
            'url' => '/products/item.php?id=' . $item['id']
        ];
    }
    
    foreach ($meals as $meal) {
        $results[] = [
            'type' => 'meal',
            'id' => $meal['id'],
            'name' => $meal['name'],
            'display_name' => $meal['display_name'],
            'price' => 'Rs. ' . number_format($meal['price'], 2),
            'image' => $meal['image'] ? 'https://admin.frozofun.com/uploads/meals/' . $meal['image'] : null,
            'url' => '/products/meal.php?id=' . $meal['id']
        ];
    }

    echo json_encode(['results' => array_slice($results, 0, 5)]);

} catch (Exception $e) {
    echo json_encode(['results' => [], 'error' => 'Search failed']);
}
?>