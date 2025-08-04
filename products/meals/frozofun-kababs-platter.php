<?php
// Auto-generated product display file for meal: Kababs Platter
// Meal ID: 1, Slug: frozofun-kababs-platter

// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../../includes/init.php';

// Set page variables
$pageTitle = "Kababs Platter";
$pageDescription = "Kababs Platter";
$canonicalUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/products/meals/frozofun-kababs-platter.php';

$mealId = 1;

// Get fresh meal details from database
$stmt = $pdo->prepare("
    SELECT * FROM meals 
    WHERE id = ? AND deleted_at IS NULL AND active = 1
");
$stmt->execute([$mealId]);
$meal = $stmt->fetch();

if (!$meal) {
    header('Location: /products/meals.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once __DIR__ . '/../../includes/seo_meta.php'; ?>
</head>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/products/">Products</a></li>
            <li class="breadcrumb-item"><a href="/products/meals.php">Meals</a></li>
            <li class="breadcrumb-item active"><?php echo h($meal['seo_title'] ?: $meal['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <div class="bg-gradient bg-primary text-white d-flex align-items-center justify-content-center rounded shadow" style="height: 400px;">
                <i class="bi bi-bowl-hot text-white" style="font-size: 8rem;"></i>
            </div>
        </div>
        
        <div class="col-md-6">
            <h1><?php echo h($meal['seo_title'] ?: $meal['name']); ?></h1>
            
            <p class="text-muted">
                <i class="bi bi-award me-1"></i>
                Category: Ready-to-eat Meals
            </p>
            
            <?php if ($meal['seo_short_description']): ?>
                <p class="lead"><?php echo h($meal['seo_short_description']); ?></p>
            <?php endif; ?>
            
            <?php if ($meal['seo_description'] && $meal['seo_description'] != $meal['seo_short_description']): ?>
                <div class="mb-3">
                    <h5>Description</h5>
                    <p><?php echo nl2br(h($meal['seo_description'])); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="card my-4">
                <div class="card-body">
                    <h5>Pricing Information</h5>
                    <div class="price-display mb-2 fs-4">
                        <?php echo formatPrice($meal['price']); ?> per meal
                    </div>
                    <p class="text-muted">Ready to eat - just heat and serve!</p>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary btn-lg" 
                        onclick="addToCart('meal', <?php echo $meal['id']; ?>, '<?php echo h($meal['seo_title'] ?: $meal['name']); ?>', <?php echo $meal['price']; ?>, 1)">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
                
                <!-- Social Share Buttons -->
                <div class="d-flex gap-2 mt-3">
                    <a href="https://wa.me/?text=Check%20out%20this%20meal:%20<?php echo urlencode($meal['seo_title'] ?: $meal['name']) . '%20' . urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="btn btn-success btn-sm">
                        <i class="bi bi-whatsapp"></i> Share on WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="btn btn-primary btn-sm">
                        <i class="bi bi-facebook"></i> Share on Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($meal['seo_title'] ?: $meal['name']); ?>&url=<?php echo urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="btn btn-info btn-sm">
                        <i class="bi bi-twitter"></i> Share on Twitter
                    </a>
                </div>
                
                <a href="/products/meals.php" class="btn btn-outline-secondary mt-3">
                    <i class="bi bi-arrow-left"></i> Back to Meals
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>