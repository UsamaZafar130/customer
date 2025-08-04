<?php
/**
 * SEO File Generator
 * Auto-generates SEO-optimized HTML files for items and meals
 */

require_once __DIR__ . '/../config/database.php';

class SEOFileGenerator {
    private $pdo;
    private $baseUrl;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    }
    
    /**
     * Generate SEO HTML file for an item
     */
    public function generateItemSEO($itemId) {
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
        
        $seoContent = $this->generateItemSEOHTML($item);
        $filePath = __DIR__ . '/../seo/items/' . $item['slug'] . '.html';
        
        return file_put_contents($filePath, $seoContent) !== false;
    }
    
    /**
     * Generate SEO HTML file for a meal
     */
    public function generateMealSEO($mealId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM meals 
            WHERE id = ? AND deleted_at IS NULL AND active = 1
        ");
        $stmt->execute([$mealId]);
        $meal = $stmt->fetch();
        
        if (!$meal || !$meal['slug']) {
            return false;
        }
        
        $seoContent = $this->generateMealSEOHTML($meal);
        $filePath = __DIR__ . '/../seo/meals/' . $meal['slug'] . '.html';
        
        return file_put_contents($filePath, $seoContent) !== false;
    }
    
    /**
     * Delete SEO file for an item
     */
    public function deleteItemSEO($slug) {
        if (!$slug) return false;
        $filePath = __DIR__ . '/../seo/items/' . $slug . '.html';
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true;
    }
    
    /**
     * Delete SEO file for a meal
     */
    public function deleteMealSEO($slug) {
        if (!$slug) return false;
        $filePath = __DIR__ . '/../seo/meals/' . $slug . '.html';
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true;
    }
    
    /**
     * Generate complete SEO HTML for an item
     */
    private function generateItemSEOHTML($item) {
        $title = htmlspecialchars($item['seo_title'] ?: $item['name']);
        $description = htmlspecialchars($item['seo_short_description'] ?: $item['name']);
        $fullDescription = htmlspecialchars($item['seo_description'] ?: $description);
        $imageUrl = 'https://admin.frozofun.com/uploads/items/' . $item['image'];
        $productUrl = $this->baseUrl . '/products/' . $item['slug'] . '.php';
        $categoryName = htmlspecialchars($item['category_name'] ?: 'Uncategorized');
        $price = number_format($item['price_per_unit'] * $item['default_pack_size'], 2);
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - FrozoFun</title>
    <meta name="description" content="{$description}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{$productUrl}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="{$productUrl}">
    <meta property="og:title" content="{$title}">
    <meta property="og:description" content="{$fullDescription}">
    <meta property="og:image" content="{$imageUrl}">
    <meta property="og:site_name" content="FrozoFun">
    <meta property="product:price:amount" content="{$price}">
    <meta property="product:price:currency" content="PKR">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{$productUrl}">
    <meta property="twitter:title" content="{$title}">
    <meta property="twitter:description" content="{$fullDescription}">
    <meta property="twitter:image" content="{$imageUrl}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{$this->baseUrl}/assets/img/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css">
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org/",
        "@type": "Product",
        "name": "{$title}",
        "image": "{$imageUrl}",
        "description": "{$fullDescription}",
        "brand": {
            "@type": "Brand",
            "name": "FrozoFun"
        },
        "category": "{$categoryName}",
        "offers": {
            "@type": "Offer",
            "url": "{$productUrl}",
            "priceCurrency": "PKR",
            "price": "{$price}",
            "availability": "https://schema.org/InStock"
        }
    }
    </script>
</head>
<body>
    <div class="container mt-5">
        <div class="text-center">
            <h1>{$title}</h1>
            <p class="lead">{$description}</p>
            <img src="{$imageUrl}" alt="{$title}" class="img-fluid mb-3" style="max-width: 400px;">
            <div class="mb-3">
                <strong>Price: Rs. {$price}</strong><br>
                <span class="text-muted">Category: {$categoryName}</span>
            </div>
            <a href="{$productUrl}" class="btn btn-primary btn-lg">View Product Details</a>
        </div>
    </div>
    
    <!-- This page is optimized for search engines. For the full interactive experience, visit the product page above. -->
</body>
</html>
HTML;
    }
    
    /**
     * Generate complete SEO HTML for a meal
     */
    private function generateMealSEOHTML($meal) {
        $title = htmlspecialchars($meal['seo_title'] ?: $meal['name']);
        $description = htmlspecialchars($meal['seo_short_description'] ?: $meal['name']);
        $fullDescription = htmlspecialchars($meal['seo_description'] ?: $description);
        $productUrl = $this->baseUrl . '/products/meals/' . $meal['slug'] . '.php';
        $price = number_format($meal['price'], 2);
        $imageUrl = $this->baseUrl . '/assets/img/meal-placeholder.jpg'; // Default meal image
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - FrozoFun</title>
    <meta name="description" content="{$description}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{$productUrl}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="{$productUrl}">
    <meta property="og:title" content="{$title}">
    <meta property="og:description" content="{$fullDescription}">
    <meta property="og:image" content="{$imageUrl}">
    <meta property="og:site_name" content="FrozoFun">
    <meta property="product:price:amount" content="{$price}">
    <meta property="product:price:currency" content="PKR">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{$productUrl}">
    <meta property="twitter:title" content="{$title}">
    <meta property="twitter:description" content="{$fullDescription}">
    <meta property="twitter:image" content="{$imageUrl}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{$this->baseUrl}/assets/img/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css">
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org/",
        "@type": "Product",
        "name": "{$title}",
        "image": "{$imageUrl}",
        "description": "{$fullDescription}",
        "brand": {
            "@type": "Brand",
            "name": "FrozoFun"
        },
        "category": "Ready-to-eat Meals",
        "offers": {
            "@type": "Offer",
            "url": "{$productUrl}",
            "priceCurrency": "PKR",
            "price": "{$price}",
            "availability": "https://schema.org/InStock"
        }
    }
    </script>
</head>
<body>
    <div class="container mt-5">
        <div class="text-center">
            <h1>{$title}</h1>
            <p class="lead">{$description}</p>
            <div class="mb-3">
                <strong>Price: Rs. {$price}</strong><br>
                <span class="text-muted">Category: Ready-to-eat Meals</span>
            </div>
            <a href="{$productUrl}" class="btn btn-primary btn-lg">View Meal Details</a>
        </div>
    </div>
    
    <!-- This page is optimized for search engines. For the full interactive experience, visit the product page above. -->
</body>
</html>
HTML;
    }
    
    /**
     * Generate all SEO files for items with images
     */
    public function generateAllItemSEO() {
        $stmt = $this->pdo->prepare("
            SELECT id FROM items 
            WHERE deleted_at IS NULL AND image IS NOT NULL AND slug IS NOT NULL
        ");
        $stmt->execute();
        $items = $stmt->fetchAll();
        
        $generated = 0;
        foreach ($items as $item) {
            if ($this->generateItemSEO($item['id'])) {
                $generated++;
            }
        }
        
        return $generated;
    }
    
    /**
     * Generate all SEO files for active meals
     */
    public function generateAllMealSEO() {
        $stmt = $this->pdo->prepare("
            SELECT id FROM meals 
            WHERE deleted_at IS NULL AND active = 1 AND slug IS NOT NULL
        ");
        $stmt->execute();
        $meals = $stmt->fetchAll();
        
        $generated = 0;
        foreach ($meals as $meal) {
            if ($this->generateMealSEO($meal['id'])) {
                $generated++;
            }
        }
        
        return $generated;
    }
}

// Function to be called from database triggers or admin actions
function handleItemChange($action, $itemId, $oldSlug = null) {
    global $pdo;
    $generator = new SEOFileGenerator($pdo);
    
    switch ($action) {
        case 'INSERT':
        case 'UPDATE':
            // Delete old SEO file if slug changed
            if ($oldSlug) {
                $generator->deleteItemSEO($oldSlug);
            }
            return $generator->generateItemSEO($itemId);
            
        case 'DELETE':
            if ($oldSlug) {
                return $generator->deleteItemSEO($oldSlug);
            }
            break;
    }
    
    return false;
}

function handleMealChange($action, $mealId, $oldSlug = null) {
    global $pdo;
    $generator = new SEOFileGenerator($pdo);
    
    switch ($action) {
        case 'INSERT':
        case 'UPDATE':
            // Delete old SEO file if slug changed
            if ($oldSlug) {
                $generator->deleteMealSEO($oldSlug);
            }
            return $generator->generateMealSEO($mealId);
            
        case 'DELETE':
            if ($oldSlug) {
                return $generator->deleteMealSEO($oldSlug);
            }
            break;
    }
    
    return false;
}