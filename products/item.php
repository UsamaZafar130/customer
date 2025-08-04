<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "Product Details";
$itemId = (int)($_GET['id'] ?? 0);

// Validate item ID and redirect if invalid
if (!$itemId) {
    header('Location: /products/');
    exit;
}

// Get item details
$stmt = $pdo->prepare("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE i.id = ? AND i.deleted_at IS NULL
");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

// Redirect if item not found
if (!$item) {
    header('Location: /products/');
    exit;
}

// Update page title with item name
$pageTitle = h($item['name']) . " - Product Details";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once __DIR__ . '/../includes/seo_meta.php'; ?>
</head>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/products/">Products</a></li>
            <li class="breadcrumb-item active"><?php echo h($item['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <?php if ($item['image']): ?>
                <img src="https://admin.frozofun.com/uploads/items/<?php echo h($item['image']); ?>" 
                     class="img-fluid rounded shadow" alt="<?php echo h($item['name']); ?>">
            <?php else: ?>
                <div class="bg-light rounded shadow d-flex align-items-center justify-content-center" style="height: 400px;">
                    <i class="bi bi-image text-muted" style="font-size: 6rem;"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-6">
            <h1><?php echo h($item['name']); ?></h1>
            
            <?php if ($item['category_name']): ?>
                <p class="text-muted">
                    <i class="bi bi-tag me-1"></i>
                    Category: <?php echo h($item['category_name']); ?>
                </p>
            <?php endif; ?>
            
            <p class="text-muted">
                <i class="bi bi-upc me-1"></i>
                Product Code: <?php echo h($item['code']); ?>
            </p>
            
            <div class="card my-4">
                <div class="card-body">
                    <h5>Pricing Information</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Price per unit:</strong></td>
                                <td><?php echo formatPrice($item['price_per_unit']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Default pack size:</strong></td>
                                <td><?php echo $item['default_pack_size']; ?> pieces</td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>Pack price:</strong></td>
                                <td class="fw-bold"><?php echo formatPrice($item['price_per_unit'] * $item['default_pack_size']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary btn-lg" 
                        onclick="addToCart('item', <?php echo $item['id']; ?>, '<?php echo h($item['name']); ?>', <?php echo $item['price_per_unit']; ?>, <?php echo $item['default_pack_size']; ?>)">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
                <a href="/products/" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>