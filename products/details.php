<?php
// AJAX endpoint for product/meal details
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!in_array($type, ['item', 'meal']) || !$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    if ($type === 'item') {
        $stmt = $pdo->prepare("
            SELECT i.*, c.name as category_name 
            FROM items i 
            LEFT JOIN categories c ON i.category_id = c.id 
            WHERE i.id = ? AND i.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        $html = '
        <div class="row">
            <div class="col-md-6">
                ' . ($product['image'] ? 
                    '<img src="https://admin.frozofun.com/uploads/items/' . htmlspecialchars($product['image']) . '" class="img-fluid rounded" alt="' . htmlspecialchars($product['name']) . '">' :
                    '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;"><i class="bi bi-image text-muted" style="font-size: 4rem;"></i></div>'
                ) . '
            </div>
            <div class="col-md-6">
                <h4>' . htmlspecialchars($product['name']) . '</h4>
                <p class="text-muted">Category: ' . ($product['category_name'] ?: 'Uncategorized') . '</p>
                <p class="text-muted">Code: ' . htmlspecialchars($product['code']) . '</p>
                
                <div class="mb-3">
                    <h6>Pricing Information:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Price per unit:</strong> Rs. ' . number_format($product['price_per_unit'], 2) . '</li>
                        <li><strong>Default pack size:</strong> ' . $product['default_pack_size'] . ' pieces</li>
                        <li><strong>Pack price:</strong> Rs. ' . number_format($product['price_per_unit'] * $product['default_pack_size'], 2) . '</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <label for="packSizeModal" class="form-label">Pack Size:</label>
                    <select class="form-select" id="packSizeModal">
                        <option value="' . $product['default_pack_size'] . '">' . $product['default_pack_size'] . ' Pcs - Rs. ' . number_format($product['price_per_unit'] * $product['default_pack_size'], 2) . '</option>
                    </select>
                </div>
                
                <button type="button" class="btn btn-primary btn-lg w-100" 
                        onclick="addToCart(\'item\', ' . $product['id'] . ', \'' . htmlspecialchars($product['name']) . '\', ' . $product['price_per_unit'] . ', ' . $product['default_pack_size'] . ')">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
            </div>
        </div>';
        
    } else { // meal
        $stmt = $pdo->prepare("
            SELECT * FROM meals 
            WHERE id = ? AND active = 1 AND deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Meal not found']);
            exit;
        }
        
        // Get meal items
        $stmt = $pdo->prepare("
            SELECT mi.*, i.name as item_name, i.price_per_unit 
            FROM meal_items mi 
            JOIN items i ON mi.item_id = i.id 
            WHERE mi.meal_id = ? AND i.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        $mealItems = $stmt->fetchAll();
        
        $mealItemsHtml = '';
        if ($mealItems) {
            $mealItemsHtml = '<h6>Meal Contents:</h6><ul class="list-unstyled">';
            foreach ($mealItems as $item) {
                $mealItemsHtml .= '<li><i class="bi bi-check-circle text-success me-2"></i>' . 
                    htmlspecialchars($item['item_name']) . ' (' . $item['qty'] . ' x ' . $item['pack_size'] . ' pcs)</li>';
            }
            $mealItemsHtml .= '</ul>';
        }
        
        $html = '
        <div class="row">
            <div class="col-md-6">
                <div class="bg-gradient bg-primary text-white rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                    <i class="bi bi-bowl-hot text-white" style="font-size: 6rem;"></i>
                </div>
            </div>
            <div class="col-md-6">
                <h4>' . htmlspecialchars($product['name']) . '</h4>
                <p class="text-muted">Ready-to-eat meal</p>
                
                <div class="mb-3">
                    ' . $mealItemsHtml . '
                </div>
                
                <div class="price-display mb-3" style="font-size: 2rem;">
                    Rs. ' . number_format($product['price'], 2) . '
                </div>
                
                <button type="button" class="btn btn-primary btn-lg w-100" 
                        onclick="addToCart(\'meal\', ' . $product['id'] . ', \'' . htmlspecialchars($product['name']) . '\', ' . $product['price'] . ', 1)">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
            </div>
        </div>';
    }
    
    echo json_encode([
        'success' => true,
        'product' => $product,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}