<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "Ready-to-Eat Meals";

// Get active meals
$stmt = $pdo->prepare("
    SELECT m.*, 
           COUNT(mi.id) as item_count,
           GROUP_CONCAT(CONCAT(i.name, ' (', mi.qty, ' x ', mi.pack_size, ')') SEPARATOR ', ') as meal_items
    FROM meals m 
    LEFT JOIN meal_items mi ON m.id = mi.meal_id 
    LEFT JOIN items i ON mi.item_id = i.id AND i.deleted_at IS NULL
    WHERE m.active = 1 AND m.deleted_at IS NULL 
    GROUP BY m.id
    ORDER BY m.name ASC
");
$stmt->execute();
$meals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once __DIR__ . '/../includes/seo_meta.php'; ?>
</head>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <h2>Ready-to-Eat Meals</h2>
            <p class="text-muted">Convenient, delicious meals prepared with our quality ingredients.</p>
        </div>
    </div>

    <!-- Meals Grid -->
    <div class="row">
        <?php if (empty($meals)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-bowl-hot text-muted" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">No meals available</h4>
                    <p class="text-muted">Check back later for our delicious meal options.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($meals as $meal): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card product-card h-100">
                        <div class="card-img-top bg-gradient bg-primary text-white d-flex align-items-center justify-content-center" style="height: 250px;">
                            <i class="bi bi-bowl-hot text-white" style="font-size: 5rem;"></i>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo h($meal['name']); ?></h5>
                            
                            <?php if ($meal['meal_items']): ?>
                                <div class="mb-3">
                                    <h6 class="text-muted">Includes:</h6>
                                    <p class="card-text small">
                                        <?php echo h($meal['meal_items']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-auto">
                                <div class="price-display mb-3">
                                    <?php echo formatPrice($meal['price']); ?>
                                </div>
                                
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="showProductModal('meal', <?php echo $meal['id']; ?>)">
                                        <i class="bi bi-eye"></i> Quick View
                                    </button>
                                    <a href="meal.php?id=<?php echo $meal['id']; ?>" target="_blank" 
                                       class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-up-right-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-add-to-cart" 
                                            onclick="addToCart('meal', <?php echo $meal['id']; ?>, '<?php echo h($meal['name']); ?>', <?php echo $meal['price']; ?>, 1)">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>