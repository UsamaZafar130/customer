<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/includes/init.php';

// Set page variables
$pageTitle = "Welcome to FrozoFun";

// Get featured items (first 8 active items with images)
$stmt = $pdo->prepare("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE i.deleted_at IS NULL AND i.image IS NOT NULL
    ORDER BY i.created_at DESC 
    LIMIT 8
");
$stmt->execute();
$featuredItems = $stmt->fetchAll();

// Get featured meals (first 4 active meals)
$stmt = $pdo->prepare("
    SELECT * FROM meals 
    WHERE active = 1 AND deleted_at IS NULL 
    ORDER BY created_at DESC 
    LIMIT 4
");
$stmt->execute();
$featuredMeals = $stmt->fetchAll();

// Get categories for filter
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once __DIR__ . '/includes/seo_meta.php'; ?>
</head>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container my-4">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="bg-primary text-white p-5 rounded">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-4 fw-bold">Welcome to FrozoFun!</h1>
                        <p class="lead">Discover our premium collection of frozen foods and delicious ready-to-eat meals. Quality guaranteed, freshness delivered.</p>
                        <a href="/products/" class="btn btn-light btn-lg">Shop Now</a>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-snow2" style="font-size: 8rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <h3>Browse by Category</h3>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-primary category-filter active" onclick="window.location.href='/products/'">
                    All Products
                </button>
                <?php foreach ($categories as $category): ?>
                    <button class="btn btn-outline-primary category-filter" onclick="window.location.href='/products/?category=<?php echo h($category['id']); ?>'">
                        <?php echo h($category['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Featured Items -->
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="mb-4">Featured Products</h3>
            <div class="row" id="productGrid">
                <?php foreach ($featuredItems as $item): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card product-card h-100" data-category-id="<?php echo h($item['category_id']); ?>">
                            <img src="https://admin.frozofun.com/uploads/items/<?php echo h($item['image']); ?>" 
                                 class="card-img-top product-image" 
                                 alt="<?php echo h($item['seo_title'] ?: $item['name']); ?>">
                            
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?php echo h($item['seo_title'] ?: $item['name']); ?></h6>
                                <p class="text-muted small mb-1">
                                    <?php if ($item['category_name']): ?>
                                        <a href="/products/?category=<?php echo h($item['category_id']); ?>" class="text-decoration-none">
                                            <?php echo h($item['category_name']); ?>
                                        </a>
                                    <?php else: ?>
                                        Uncategorized
                                    <?php endif; ?>
                                </p>
                                <p class="card-text small flex-grow-1">
                                    <?php echo h($item['seo_short_description'] ?: getItemDisplayName($item['name'], $item['default_pack_size'], $item['price_per_unit'])); ?>
                                </p>
                                <div class="price-display mb-2">
                                    <?php echo formatPrice($item['price_per_unit'] * $item['default_pack_size']); ?>
                                </div>
                                <div class="mt-auto">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="showProductModal('item', <?php echo $item['id']; ?>)">
                                            <i class="bi bi-eye"></i> Quick View
                                        </button>
                                        <button type="button" class="btn btn-add-to-cart btn-sm" 
                                                onclick="addToCart('item', <?php echo $item['id']; ?>, '<?php echo h($item['seo_title'] ?: $item['name']); ?>', <?php echo $item['price_per_unit']; ?>, <?php echo $item['default_pack_size']; ?>)">
                                            <i class="bi bi-cart-plus"></i> Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center">
                <a href="/products/" class="btn btn-primary btn-lg">View All Products</a>
            </div>
        </div>
    </div>

    <!-- Featured Meals -->
    <?php if (!empty($featuredMeals)): ?>
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">Featured Meals</h3>
            <div class="row">
                <?php foreach ($featuredMeals as $meal): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card product-card h-100">
                            <div class="card-img-top bg-gradient bg-primary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-bowl-hot text-white" style="font-size: 4rem;"></i>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?php echo h($meal['seo_title'] ?: $meal['name']); ?></h6>
                                <p class="text-muted small">Ready-to-eat meal</p>
                                <?php if ($meal['seo_short_description']): ?>
                                    <p class="card-text small flex-grow-1"><?php echo h($meal['seo_short_description']); ?></p>
                                <?php endif; ?>
                                <div class="price-display mb-2">
                                    <?php echo formatPrice($meal['price']); ?>
                                </div>
                                <div class="mt-auto">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="showProductModal('meal', <?php echo $meal['id']; ?>)">
                                            <i class="bi bi-eye"></i> Quick View
                                        </button>
                                        <button type="button" class="btn btn-add-to-cart btn-sm" 
                                                onclick="addToCart('meal', <?php echo $meal['id']; ?>, '<?php echo h($meal['seo_title'] ?: $meal['name']); ?>', <?php echo $meal['price']; ?>, 1)">
                                            <i class="bi bi-cart-plus"></i> Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center">
                <a href="/products/meals.php" class="btn btn-primary btn-lg">View All Meals</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>