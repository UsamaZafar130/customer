<?php
// Auto-generated product display file for item: Tikka Reisha Kababs
// Item ID: 3, Slug: frozofun-tikka-reisha-kababs

// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "Tikka Reisha Kababs | Spicy Chicken Kababs | FrozoFun";
$pageDescription = "Spicy tikka-flavored chicken kababs, ready to enjoy.";
$canonicalUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/products/frozofun-tikka-reisha-kababs.php';

$itemId = 3;

// Get fresh item details from database
$stmt = $pdo->prepare("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE i.id = ? AND i.deleted_at IS NULL
");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: /products/');
    exit;
}

// Check if item still has image (requirement: only show items with images)
if (!$item['image']) {
    header('Location: /products/');
    exit;
}
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
            <?php if ($item['category_name']): ?>
                <li class="breadcrumb-item">
                    <a href="/products/?category=10"><?php echo h($item['category_name']); ?></a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?php echo h($item['seo_title'] ?: $item['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <img src="https://admin.frozofun.com/uploads/items/<?php echo h($item['image']); ?>" 
                 class="img-fluid rounded shadow" 
                 alt="<?php echo h($item['seo_title'] ?: $item['name']); ?>">
        </div>
        
        <div class="col-md-6">
            <h1><?php echo h($item['seo_title'] ?: $item['name']); ?></h1>
            
            <?php if ($item['category_name']): ?>
                <p class="text-muted">
                    <i class="bi bi-tag me-1"></i>
                    Category: <a href="/products/?category=10" class="text-decoration-none">
                        <?php echo h($item['category_name']); ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <p class="text-muted">
                <i class="bi bi-upc me-1"></i>
                Product Code: <?php echo h($item['code']); ?>
            </p>
            
            <?php if ($item['seo_short_description']): ?>
                <p class="lead"><?php echo h($item['seo_short_description']); ?></p>
            <?php endif; ?>
            
            <?php if ($item['seo_description'] && $item['seo_description'] != $item['seo_short_description']): ?>
                <div class="mb-3">
                    <h5>Description</h5>
                    <p><?php echo nl2br(h($item['seo_description'])); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="card my-4">
                <div class="card-body">
                    <h5>Pricing Information</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Per Unit Price:</strong></td>
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
                        onclick="addToCart('item', <?php echo $item['id']; ?>, '<?php echo h($item['seo_title'] ?: $item['name']); ?>', <?php echo $item['price_per_unit']; ?>, <?php echo $item['default_pack_size']; ?>)">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
                
                <!-- Social Share Buttons -->
                <div class="d-flex gap-2 mt-3">
                    <a href="https://wa.me/?text=Check%20out%20this%20product:%20<?php echo urlencode($item['seo_title'] ?: $item['name']) . '%20' . urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="btn btn-success btn-sm">
                        <i class="bi bi-whatsapp"></i> Share on WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="btn btn-primary btn-sm">
                        <i class="bi bi-facebook"></i> Share on Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($item['seo_title'] ?: $item['name']); ?>&url=<?php echo urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="btn btn-info btn-sm">
                        <i class="bi bi-twitter"></i> Share on Twitter
                    </a>
                </div>
                
                <a href="/products/" class="btn btn-outline-secondary mt-3">
                    <i class="bi bi-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>