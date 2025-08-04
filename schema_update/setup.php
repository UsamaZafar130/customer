<?php
/**
 * Setup Script for SEO Auto-Generation System
 * Run this script to apply database changes and generate initial files
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seo_generator.php';
require_once __DIR__ . '/../includes/product_generator.php';

echo "FrozoFun SEO Auto-Generation System Setup\n";
echo "==========================================\n\n";

// Step 1: Apply database changes
echo "Step 1: Applying database schema updates...\n";

try {
    // Read and execute the SQL migration
    $sql = file_get_contents(__DIR__ . '/../schema_update/add_seo_fields.sql');
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "✓ Database schema updated successfully\n\n";
} catch (PDOException $e) {
    // Check if error is about column already existing
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "✓ Database schema already up to date\n\n";
    } else {
        echo "✗ Error updating database schema: " . $e->getMessage() . "\n\n";
        exit(1);
    }
}

// Step 2: Generate SEO files
echo "Step 2: Generating SEO HTML files...\n";

try {
    $seoGenerator = new SEOFileGenerator($pdo);
    
    $itemsSEO = $seoGenerator->generateAllItemSEO();
    echo "✓ Generated $itemsSEO SEO files for items\n";
    
    $mealsSEO = $seoGenerator->generateAllMealSEO();
    echo "✓ Generated $mealsSEO SEO files for meals\n\n";
} catch (Exception $e) {
    echo "✗ Error generating SEO files: " . $e->getMessage() . "\n\n";
}

// Step 3: Generate product display files
echo "Step 3: Generating product display files...\n";

try {
    $displayGenerator = new ProductDisplayGenerator($pdo);
    
    $itemsDisplay = $displayGenerator->generateAllItemDisplays();
    echo "✓ Generated $itemsDisplay product display files for items\n";
    
    $mealsDisplay = $displayGenerator->generateAllMealDisplays();
    echo "✓ Generated $mealsDisplay product display files for meals\n\n";
} catch (Exception $e) {
    echo "✗ Error generating product display files: " . $e->getMessage() . "\n\n";
}

// Step 4: Check directory structure
echo "Step 4: Verifying directory structure...\n";

$requiredDirs = [
    __DIR__ . '/../seo/items',
    __DIR__ . '/../seo/meals',
    __DIR__ . '/../products/meals',
    __DIR__ . '/../assets/img'
];

foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        echo "✓ Directory exists: " . basename(dirname($dir)) . "/" . basename($dir) . "\n";
    } else {
        echo "✗ Missing directory: " . basename(dirname($dir)) . "/" . basename($dir) . "\n";
    }
}

// Step 5: Check for required assets
echo "\nStep 5: Checking for required assets...\n";

$requiredAssets = [
    __DIR__ . '/../assets/img/logo.png' => 'Logo file (replace logo.png.placeholder)',
    __DIR__ . '/../assets/img/favicon.png' => 'Favicon file (replace favicon.png.placeholder)'
];

foreach ($requiredAssets as $file => $description) {
    if (file_exists($file)) {
        echo "✓ $description found\n";
    } else {
        echo "⚠ Missing: $description\n";
    }
}

echo "\n==========================================\n";
echo "Setup completed!\n\n";

echo "Next steps:\n";
echo "1. Replace placeholder files with actual logo.png and favicon.png in /assets/img/\n";
echo "2. Test the system by accessing product pages with slugs\n";
echo "3. Verify SEO meta tags are working correctly\n";
echo "4. Set up database triggers for auto-generation (optional)\n\n";

echo "Generated files summary:\n";
echo "- SEO HTML files: $itemsSEO items + $mealsSEO meals = " . ($itemsSEO + $mealsSEO) . " total\n";
echo "- Product display files: $itemsDisplay items + $mealsDisplay meals = " . ($itemsDisplay + $mealsDisplay) . " total\n";

echo "\nSystem is ready for use!\n";
?>