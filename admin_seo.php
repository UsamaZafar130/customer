<?php
/**
 * Admin Utility for SEO File Management
 * Provides functions to manually regenerate SEO and product display files
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/seo_generator.php';
require_once __DIR__ . '/includes/product_generator.php';

// Require admin authentication
requireAdminAuth();

// Get current admin user for display
$adminUser = getCurrentAdminUser();

// Escape HTML function
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$action = $_GET['action'] ?? 'status';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO File Management - FrozoFun Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-gear-fill"></i> SEO File Management
            </a>
            <div class="navbar-text text-white">
                Welcome, <?php echo h($adminUser['name']); ?> 
                <a href="/auth/admin_logout.php" class="btn btn-outline-light btn-sm ms-2">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h1><i class="bi bi-file-earmark-code"></i> SEO File Management</h1>
        <p class="text-muted">Admin utility for managing SEO HTML files and product display files</p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="?action=regenerate_all" class="btn btn-primary mb-2 d-block">
                            <i class="bi bi-arrow-clockwise"></i> Regenerate All Files
                        </a>
                        <a href="?action=regenerate_items" class="btn btn-outline-primary mb-2 d-block">
                            <i class="bi bi-box"></i> Regenerate Item Files Only
                        </a>
                        <a href="?action=regenerate_meals" class="btn btn-outline-primary mb-2 d-block">
                            <i class="bi bi-cup-hot"></i> Regenerate Meal Files Only
                        </a>
                        <a href="?action=status" class="btn btn-outline-secondary mb-2 d-block">
                            <i class="bi bi-info-circle"></i> View Status
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Result</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            switch ($action) {
                                case 'regenerate_all':
                                    echo "<h6>Regenerating All Files...</h6>";
                                    
                                    $seoGenerator = new SEOFileGenerator($pdo);
                                    $displayGenerator = new ProductDisplayGenerator($pdo);
                                    
                                    $itemsSEO = $seoGenerator->generateAllItemSEO();
                                    $mealsSEO = $seoGenerator->generateAllMealSEO();
                                    $itemsDisplay = $displayGenerator->generateAllItemDisplays();
                                    $mealsDisplay = $displayGenerator->generateAllMealDisplays();
                                    
                                    echo "<div class='alert alert-success'>";
                                    echo "<strong>Success!</strong><br>";
                                    echo "Generated $itemsSEO SEO files for items<br>";
                                    echo "Generated $mealsSEO SEO files for meals<br>";
                                    echo "Generated $itemsDisplay display files for items<br>";
                                    echo "Generated $mealsDisplay display files for meals<br>";
                                    echo "<strong>Total: " . ($itemsSEO + $mealsSEO + $itemsDisplay + $mealsDisplay) . " files</strong>";
                                    echo "</div>";
                                    break;
                                    
                                case 'regenerate_items':
                                    echo "<h6>Regenerating Item Files...</h6>";
                                    
                                    $seoGenerator = new SEOFileGenerator($pdo);
                                    $displayGenerator = new ProductDisplayGenerator($pdo);
                                    
                                    $itemsSEO = $seoGenerator->generateAllItemSEO();
                                    $itemsDisplay = $displayGenerator->generateAllItemDisplays();
                                    
                                    echo "<div class='alert alert-success'>";
                                    echo "<strong>Success!</strong><br>";
                                    echo "Generated $itemsSEO SEO files for items<br>";
                                    echo "Generated $itemsDisplay display files for items<br>";
                                    echo "<strong>Total: " . ($itemsSEO + $itemsDisplay) . " files</strong>";
                                    echo "</div>";
                                    break;
                                    
                                case 'regenerate_meals':
                                    echo "<h6>Regenerating Meal Files...</h6>";
                                    
                                    $seoGenerator = new SEOFileGenerator($pdo);
                                    $displayGenerator = new ProductDisplayGenerator($pdo);
                                    
                                    $mealsSEO = $seoGenerator->generateAllMealSEO();
                                    $mealsDisplay = $displayGenerator->generateAllMealDisplays();
                                    
                                    echo "<div class='alert alert-success'>";
                                    echo "<strong>Success!</strong><br>";
                                    echo "Generated $mealsSEO SEO files for meals<br>";
                                    echo "Generated $mealsDisplay display files for meals<br>";
                                    echo "<strong>Total: " . ($mealsSEO + $mealsDisplay) . " files</strong>";
                                    echo "</div>";
                                    break;
                                    
                                case 'status':
                                default:
                                    echo "<h6>System Status</h6>";
                                    
                                    // Check directory structure
                                    $dirs = [
                                        '/seo/items' => __DIR__ . '/seo/items',
                                        '/seo/meals' => __DIR__ . '/seo/meals',
                                        '/products/meals' => __DIR__ . '/products/meals'
                                    ];
                                    
                                    echo "<table class='table table-sm'>";
                                    echo "<tr><th>Directory</th><th>Status</th><th>Files</th></tr>";
                                    
                                    foreach ($dirs as $name => $path) {
                                        $exists = is_dir($path);
                                        $fileCount = $exists ? count(glob($path . '/*')) : 0;
                                        $status = $exists ? '<span class="badge bg-success">OK</span>' : '<span class="badge bg-danger">Missing</span>';
                                        echo "<tr><td>$name</td><td>$status</td><td>$fileCount</td></tr>";
                                    }
                                    
                                    echo "</table>";
                                    
                                    // Check database counts
                                    try {
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM items WHERE deleted_at IS NULL AND image IS NOT NULL AND slug IS NOT NULL");
                                        $stmt->execute();
                                        $itemCount = $stmt->fetch()['count'];
                                        
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM meals WHERE deleted_at IS NULL AND active = 1 AND slug IS NOT NULL");
                                        $stmt->execute();
                                        $mealCount = $stmt->fetch()['count'];
                                        
                                        echo "<div class='alert alert-info'>";
                                        echo "<strong>Database Status:</strong><br>";
                                        echo "Items eligible for generation: $itemCount<br>";
                                        echo "Meals eligible for generation: $mealCount";
                                        echo "</div>";
                                        
                                    } catch (Exception $e) {
                                        echo "<div class='alert alert-warning'>";
                                        echo "<strong>Database Status:</strong> " . $e->getMessage();
                                        echo "</div>";
                                    }
                                    
                                    break;
                            }
                        } catch (Exception $e) {
                            echo "<div class='alert alert-danger'>";
                            echo "<strong>Error:</strong> " . $e->getMessage();
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <h5>How it Works</h5>
            <ul>
                <li><strong>SEO HTML Files:</strong> Located in <code>/seo/items/</code> and <code>/seo/meals/</code> - optimized for search engines</li>
                <li><strong>Product Display Files:</strong> Located in <code>/products/</code> and <code>/products/meals/</code> - for user interaction</li>
                <li><strong>Auto-generation:</strong> Files are automatically created/updated when products are modified in the admin panel</li>
                <li><strong>Requirements:</strong> Items must have non-null images and valid slugs to be included</li>
            </ul>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>