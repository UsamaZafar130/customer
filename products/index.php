<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "Products";

// Get filter parameters
$categoryId = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$whereConditions = ["i.deleted_at IS NULL"];
$params = [];

if ($categoryId && $categoryId !== 'all') {
    $whereConditions[] = "i.category_id = ?";
    $params[] = $categoryId;
}

if ($search) {
    $whereConditions[] = "i.name LIKE ?";
    $params[] = "%{$search}%";
}

$whereClause = implode(' AND ', $whereConditions);

// Get items (only show items with images)
$stmt = $pdo->prepare("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE {$whereClause} AND i.image IS NOT NULL
    ORDER BY i.name ASC
");
$stmt->execute($params);
$items = $stmt->fetchAll();

// Get categories
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
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
            <h2>Our Products</h2>
            <p class="text-muted">Discover our wide range of premium frozen products.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-primary category-filter <?php echo empty($categoryId) || $categoryId === 'all' ? 'active' : ''; ?>" 
                        onclick="filterByCategory('all')">
                    All Categories
                </button>
                <?php foreach ($categories as $category): ?>
                    <button class="btn btn-outline-primary category-filter <?php echo $categoryId == $category['id'] ? 'active' : ''; ?>" 
                            onclick="filterByCategory('<?php echo h($category['id']); ?>')">
                        <?php echo h($category['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-4">
            <form method="GET" class="d-flex">
                <input type="text" class="form-control" name="search" placeholder="Search products..." 
                       value="<?php echo h($search); ?>">
                <button type="submit" class="btn btn-primary ms-2">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row" id="productGrid">
        <?php if (empty($items)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">No products found</h4>
                    <p class="text-muted">Try adjusting your search or filter criteria.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card product-card h-100" data-category-id="<?php echo h($item['category_id']); ?>">
                        <img src="https://admin.frozofun.com/uploads/items/<?php echo h($item['image']); ?>" 
                             class="card-img-top product-image" 
                             alt="<?php echo h($item['seo_title'] ?: $item['name']); ?>">
                        
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?php echo h($item['seo_title'] ?: $item['name']); ?></h6>
                            <p class="text-muted small mb-1">
                                <?php if ($item['category_name']): ?>
                                    <a href="?category=<?php echo h($item['category_id']); ?>" class="text-decoration-none">
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
                                    <?php if ($item['slug']): ?>
                                        <a href="<?php echo h($item['slug']); ?>.php" target="_blank" 
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="item.php?id=<?php echo $item['id']; ?>" target="_blank" 
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-add-to-cart btn-sm" 
                                            onclick="addToCart('item', <?php echo $item['id']; ?>, '<?php echo h($item['seo_title'] ?: $item['name']); ?>', <?php echo $item['price_per_unit']; ?>, <?php echo $item['default_pack_size']; ?>)">
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

<script>
// Override the filterByCategory function to use URL parameters
function filterByCategory(categoryId) {
    const url = new URL(window.location);
    if (categoryId === 'all') {
        url.searchParams.delete('category');
    } else {
        url.searchParams.set('category', categoryId);
    }
    window.location.href = url.toString();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>