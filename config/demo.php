<?php
// Mock database configuration for demo purposes
function getDbConnection() {
    // Return null since we're in a demo environment
    return null;
}

// Mock functions for demo
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return false; // Demo as guest user
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        return null;
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        return number_format($price, 2);
    }
}

if (!function_exists('getItemDisplayName')) {
    function getItemDisplayName($name, $packSize, $pricePerUnit) {
        $totalPrice = $pricePerUnit * $packSize;
        return "{$name} {$packSize} Pcs, Price: " . formatPrice($totalPrice);
    }
}

if (!function_exists('h')) {
    function h($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

// Mock data for demo
$featuredItems = [
    [
        'id' => 1,
        'name' => 'Frozen Chicken Wings',
        'price_per_unit' => 2.50,
        'default_pack_size' => 10,
        'category_id' => 1,
        'category_name' => 'Poultry',
        'image' => 'chicken-wings.jpg'
    ],
    [
        'id' => 2,
        'name' => 'Frozen French Fries',
        'price_per_unit' => 1.80,
        'default_pack_size' => 12,
        'category_id' => 2,
        'category_name' => 'Vegetables',
        'image' => 'french-fries.jpg'
    ],
    [
        'id' => 3,
        'name' => 'Frozen Fish Fillets',
        'price_per_unit' => 3.20,
        'default_pack_size' => 8,
        'category_id' => 3,
        'category_name' => 'Seafood',
        'image' => 'fish-fillets.jpg'
    ],
    [
        'id' => 4,
        'name' => 'Frozen Pizza Base',
        'price_per_unit' => 1.50,
        'default_pack_size' => 6,
        'category_id' => 4,
        'category_name' => 'Bakery',
        'image' => 'pizza-base.jpg'
    ]
];

$featuredMeals = [
    [
        'id' => 1,
        'name' => 'Family Combo Meal',
        'price' => 25.99
    ],
    [
        'id' => 2,
        'name' => 'BBQ Special',
        'price' => 18.50
    ]
];

$categories = [
    ['id' => 1, 'name' => 'Poultry'],
    ['id' => 2, 'name' => 'Vegetables'],
    ['id' => 3, 'name' => 'Seafood'],
    ['id' => 4, 'name' => 'Bakery']
];
?>