<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "Meal Details";
$mealId = (int)($_GET['id'] ?? 0);

// Validate meal ID and redirect if invalid
if (!$mealId) {
    header('Location: /products/meals.php');
    exit;
}

// Get meal details
$stmt = $pdo->prepare("
    SELECT * FROM meals 
    WHERE id = ? AND active = 1 AND deleted_at IS NULL
");
$stmt->execute([$mealId]);
$meal = $stmt->fetch();

// Redirect if meal not found
if (!$meal) {
    header('Location: /products/meals.php');
    exit;
}

// Update page title with meal name
$pageTitle = h($meal['name']) . " - Meal Details";

// Get meal items
$stmt = $pdo->prepare("
    SELECT mi.*, i.name as item_name, i.price_per_unit, i.image 
    FROM meal_items mi 
    JOIN items i ON mi.item_id = i.id 
    WHERE mi.meal_id = ? AND i.deleted_at IS NULL
    ORDER BY i.name
");
$stmt->execute([$mealId]);
$mealItems = $stmt->fetchAll();
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
            <li class="breadcrumb-item"><a href="/products/meals.php">Meals</a></li>
            <li class="breadcrumb-item active"><?php echo h($meal['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <div class="bg-gradient bg-primary text-white rounded shadow d-flex align-items-center justify-content-center" style="height: 400px;">
                <i class="bi bi-bowl-hot text-white" style="font-size: 8rem;"></i>
            </div>
        </div>
        
        <div class="col-md-6">
            <h1><?php echo h($meal['name']); ?></h1>
            <p class="text-muted">Ready-to-eat meal</p>
            
            <div class="price-display mb-4" style="font-size: 2.5rem;">
                <?php echo formatPrice($meal['price']); ?>
            </div>
            
            <?php if (!empty($mealItems)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-list-check me-2"></i>
                            What's Included in This Meal
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($mealItems as $item): ?>
                                <div class="col-sm-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <?php if ($item['image']): ?>
                                                <img src="https://admin.frozofun.com/uploads/items/<?php echo h($item['image']); ?>" 
                                                     class="rounded" style="width: 50px; height: 50px; object-fit: cover;" 
                                                     alt="<?php echo h($item['item_name']); ?>">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo h($item['item_name']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo $item['qty']; ?> × <?php echo $item['pack_size']; ?> pcs
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary btn-lg" 
                        onclick="addToCart('meal', <?php echo $meal['id']; ?>, '<?php echo h($meal['name']); ?>', <?php echo $meal['price']; ?>, 1)">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
                <a href="/products/meals.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Meals
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>