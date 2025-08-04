<?php
/**
 * Product Display Generator
 * Auto-generates product display PHP files for items and meals
 */

require_once __DIR__ . '/../config/database.php';

class ProductDisplayGenerator {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Generate product display PHP file for an item
     */
    public function generateItemDisplay($itemId) {
        $stmt = $this->pdo->prepare("
            SELECT i.*, c.name as category_name 
            FROM items i 
            LEFT JOIN categories c ON i.category_id = c.id 
            WHERE i.id = ? AND i.deleted_at IS NULL AND i.image IS NOT NULL
        ");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch();
        
        if (!$item || !$item['slug']) {
            return false;
        }
        
        $displayContent = $this->generateItemDisplayPHP($item);
        $filePath = __DIR__ . '/../products/' . $item['slug'] . '.php';
        
        return file_put_contents($filePath, $displayContent) !== false;
    }
    
    /**
     * Generate product display PHP file for a meal
     */
    public function generateMealDisplay($mealId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM meals 
            WHERE id = ? AND deleted_at IS NULL AND active = 1
        ");
        $stmt->execute([$mealId]);
        $meal = $stmt->fetch();
        
        if (!$meal || !$meal['slug']) {
            return false;
        }
        
        $displayContent = $this->generateMealDisplayPHP($meal);
        $filePath = __DIR__ . '/../products/meals/' . $meal['slug'] . '.php';
        
        // Ensure meals directory exists
        $mealsDir = dirname($filePath);
        if (!is_dir($mealsDir)) {
            mkdir($mealsDir, 0755, true);
        }
        
        return file_put_contents($filePath, $displayContent) !== false;
    }
    
    /**
     * Delete product display file for an item
     */
    public function deleteItemDisplay($slug) {
        if (!$slug) return false;
        $filePath = __DIR__ . '/../products/' . $slug . '.php';
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true;
    }
    
    /**
     * Delete product display file for a meal
     */
    public function deleteMealDisplay($slug) {
        if (!$slug) return false;
        $filePath = __DIR__ . '/../products/meals/' . $slug . '.php';
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true;
    }
    
    /**
     * Generate complete product display PHP for an item
     */
    private function generateItemDisplayPHP($item) {
        $title = addslashes($item['seo_title'] ?: $item['name']);
        $description = addslashes($item['seo_short_description'] ?: $item['name']);
        $fullDescription = addslashes($item['seo_description'] ?: $description);
        $categoryLink = $item['category_id'] ? "/products/?category=" . $item['category_id'] : "/products/";
        
        return <<<PHP
<?php
// Auto-generated product display file for item: {$item['name']}
// Item ID: {$item['id']}, Slug: {$item['slug']}

// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
\$pageTitle = "{$title}";
\$pageDescription = "{$description}";
\$canonicalUrl = \$_SERVER['REQUEST_SCHEME'] . '://' . \$_SERVER['HTTP_HOST'] . '/products/{$item['slug']}.php';

\$itemId = {$item['id']};

// Get fresh item details from database
\$stmt = \$pdo->prepare("
    SELECT i.*, c.name as category_name 
    FROM items i 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE i.id = ? AND i.deleted_at IS NULL
");
\$stmt->execute([\$itemId]);
\$item = \$stmt->fetch();

if (!\$item) {
    header('Location: /products/');
    exit;
}

// Check if item still has image (requirement: only show items with images)
if (!\$item['image']) {
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
            <?php if (\$item['category_name']): ?>
                <li class="breadcrumb-item">
                    <a href="{$categoryLink}"><?php echo h(\$item['category_name']); ?></a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?php echo h(\$item['seo_title'] ?: \$item['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <img src="https://admin.frozofun.com/uploads/items/<?php echo h(\$item['image']); ?>" 
                 class="img-fluid rounded shadow" 
                 alt="<?php echo h(\$item['seo_title'] ?: \$item['name']); ?>">
        </div>
        
        <div class="col-md-6">
            <h1><?php echo h(\$item['seo_title'] ?: \$item['name']); ?></h1>
            
            <?php if (\$item['category_name']): ?>
                <p class="text-muted">
                    <i class="bi bi-tag me-1"></i>
                    Category: <a href="{$categoryLink}" class="text-decoration-none">
                        <?php echo h(\$item['category_name']); ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <p class="text-muted">
                <i class="bi bi-upc me-1"></i>
                Product Code: <?php echo h(\$item['code']); ?>
            </p>
            
            <?php if (\$item['seo_short_description']): ?>
                <p class="lead"><?php echo h(\$item['seo_short_description']); ?></p>
            <?php endif; ?>
            
            <?php if (\$item['seo_description'] && \$item['seo_description'] != \$item['seo_short_description']): ?>
                <div class="mb-3">
                    <h5>Description</h5>
                    <p><?php echo nl2br(h(\$item['seo_description'])); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="card my-4">
                <div class="card-body">
                    <h5>Pricing Information</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Per Unit Price:</strong></td>
                                <td><?php echo formatPrice(\$item['price_per_unit']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Default pack size:</strong></td>
                                <td><?php echo \$item['default_pack_size']; ?> pieces</td>
                            </tr>
                            <tr class="table-success">
                                <td><strong>Pack price:</strong></td>
                                <td class="fw-bold"><?php echo formatPrice(\$item['price_per_unit'] * \$item['default_pack_size']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary btn-lg" 
                        onclick="addToCart('item', <?php echo \$item['id']; ?>, '<?php echo h(\$item['seo_title'] ?: \$item['name']); ?>', <?php echo \$item['price_per_unit']; ?>, <?php echo \$item['default_pack_size']; ?>)">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
                
                <!-- Social Share Buttons -->
                <div class="d-flex gap-2 mt-3">
                    <a href="https://wa.me/?text=Check%20out%20this%20product:%20<?php echo urlencode(\$item['seo_title'] ?: \$item['name']) . '%20' . urlencode(\$_SERVER['REQUEST_SCHEME'] . '://' . \$_SERVER['HTTP_HOST'] . \$_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="btn btn-success btn-sm">
                        <i class="bi bi-whatsapp"></i> Share on WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(\$_SERVER['REQUEST_SCHEME'] . '://' . \$_SERVER['HTTP_HOST'] . \$_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="btn btn-primary btn-sm">
                        <i class="bi bi-facebook"></i> Share on Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode(\$item['seo_title'] ?: \$item['name']); ?>&url=<?php echo urlencode(\$_SERVER['REQUEST_SCHEME'] . '://' . \$_SERVER['HTTP_HOST'] . \$_SERVER['REQUEST_URI']); ?>" 
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
PHP;
    }
    
    /**
     * Generate complete product display PHP for a meal
     */
    private function generateMealDisplayPHP($meal) {
        $title = addslashes($meal['seo_title'] ?: $meal['name']);
        $description = addslashes($meal['seo_short_description'] ?: $meal['name']);
        $fullDescription = addslashes($meal['seo_description'] ?: $description);
        
        return <<<PHP
<?php
// Auto-generated product display file for meal: {$meal['name']}
// Meal ID: {$meal['id']}, Slug: {$meal['slug']}

// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../../includes/init.php';

// Set page variables
\$pageTitle = "{$title}";
\$pageDescription = "{$description}";
\$canonicalUrl = \$_SERVER['REQUEST_SCHEME'] . '://' . \$_SERVER['HTTP_HOST'] . '/products/meals/{$meal['slug']}.php';

\$mealId = {$meal['id']};

// Get fresh meal details from database
\$stmt = \$pdo->prepare("
    SELECT * FROM meals 
    WHERE id = ? AND deleted_at IS NULL AND active = 1
");
\$stmt->execute([\$mealId]);
\$meal = \$stmt->fetch();

if (!\$meal) {
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
            <li class="breadcrumb-item active"><?php echo h(\$meal['seo_title'] ?: \$meal['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <div class="bg-gradient bg-primary text-white d-flex align-items-center justify-content-center rounded shadow" style="height: 400px;">
                <i class="bi bi-bowl-hot text-white" style="font-size: 8rem;"></i>
            </div>
        </div>
        
        <div class="col-md-6">
            <h1><?php echo h(\$meal['seo_title'] ?: \$meal['name']); ?></h1>
            
            <p class="text-muted">
                <i class="bi bi-award me-1"></i>
                Category: Ready-to-eat Meals
            </p>
            
            <?php if (\$meal['seo_short_description']): ?>
                <p class="lead"><?php echo h(\$meal['seo_short_description']); ?></p>
            <?php endif; ?>
            
            <?php if (\$meal['seo_description'] && \$meal['seo_description'] != \$meal['seo_short_description']): ?>
                <div class="mb-3">
                    <h5>Description</h5>
                    <p><?php echo nl2br(h(\$meal['seo_description'])); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="card my-4">
                <div class="card-body">
                    <h5>Pricing Information</h5>
                    <div class="price-display mb-2 fs-4">
                        <?php echo formatPrice(\$meal['price']); ?> per meal
                    </div>
                    <p class="text-muted">Ready to eat - just heat and serve!</p>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary btn-lg" 
                        onclick="addToCart('meal', <?php echo \$meal['id']; ?>, '<?php echo h(\$meal['seo_title'] ?: \$meal['name']); ?>', <?php echo \$meal['price']; ?>, 1)">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
                
                <!-- Social Share Buttons -->
                <div class="d-flex gap-2 mt-3">
                    <a href="https://wa.me/?text=Check%20out%20this%20meal:%20<?php echo urlencode(\$meal['seo_title'] ?: \$meal['name']) . '%20' . urlencode(\$_SERVER['REQUEST_SCHEME'] . '://' . \$_SERVER['HTTP_HOST'] . \$_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="btn btn-success btn-sm">
                        <i class="bi bi-whatsapp"></i> Share on WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(\$_SERVER['REQUEST_SCHEME'] . '://' . \$_SERVER['HTTP_HOST'] . \$_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" class="btn btn-primary btn-sm">
                        <i class="bi bi-facebook"></i> Share on Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode(\$meal['seo_title'] ?: \$meal['name']); ?>&url=<?php echo urlencode(\$_SERVER['REQUEST_SCHEME'] . '://' . \$_SERVER['HTTP_HOST'] . \$_SERVER['REQUEST_URI']); ?>" 
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
PHP;
    }
    
    /**
     * Generate all product display files for items with images
     */
    public function generateAllItemDisplays() {
        $stmt = $this->pdo->prepare("
            SELECT id FROM items 
            WHERE deleted_at IS NULL AND image IS NOT NULL AND slug IS NOT NULL
        ");
        $stmt->execute();
        $items = $stmt->fetchAll();
        
        $generated = 0;
        foreach ($items as $item) {
            if ($this->generateItemDisplay($item['id'])) {
                $generated++;
            }
        }
        
        return $generated;
    }
    
    /**
     * Generate all product display files for active meals
     */
    public function generateAllMealDisplays() {
        $stmt = $this->pdo->prepare("
            SELECT id FROM meals 
            WHERE deleted_at IS NULL AND active = 1 AND slug IS NOT NULL
        ");
        $stmt->execute();
        $meals = $stmt->fetchAll();
        
        $generated = 0;
        foreach ($meals as $meal) {
            if ($this->generateMealDisplay($meal['id'])) {
                $generated++;
            }
        }
        
        return $generated;
    }
}

// Function to be called from database triggers or admin actions
function handleItemDisplayChange($action, $itemId, $oldSlug = null) {
    global $pdo;
    $generator = new ProductDisplayGenerator($pdo);
    
    switch ($action) {
        case 'INSERT':
        case 'UPDATE':
            // Delete old display file if slug changed
            if ($oldSlug) {
                $generator->deleteItemDisplay($oldSlug);
            }
            return $generator->generateItemDisplay($itemId);
            
        case 'DELETE':
            if ($oldSlug) {
                return $generator->deleteItemDisplay($oldSlug);
            }
            break;
    }
    
    return false;
}

function handleMealDisplayChange($action, $mealId, $oldSlug = null) {
    global $pdo;
    $generator = new ProductDisplayGenerator($pdo);
    
    switch ($action) {
        case 'INSERT':
        case 'UPDATE':
            // Delete old display file if slug changed
            if ($oldSlug) {
                $generator->deleteMealDisplay($oldSlug);
            }
            return $generator->generateMealDisplay($mealId);
            
        case 'DELETE':
            if ($oldSlug) {
                return $generator->deleteMealDisplay($oldSlug);
            }
            break;
    }
    
    return false;
}