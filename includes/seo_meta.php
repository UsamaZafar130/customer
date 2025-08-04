    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? h($pageTitle) . ' - ' : ''; ?>FrozoFun - Premium Frozen Foods</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo isset($pageDescription) ? h($pageDescription) : 'FrozoFun - Your trusted source for premium frozen foods and ready-to-eat meals. Quality guaranteed, freshness delivered.'; ?>">
    <meta name="keywords" content="frozen foods, frozen products, ready meals, FrozoFun, premium frozen items, quality frozen food">
    <meta name="author" content="FrozoFun">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo isset($pageTitle) ? h($pageTitle) . ' - ' : ''; ?>FrozoFun">
    <meta property="og:description" content="<?php echo isset($pageDescription) ? h($pageDescription) : 'Premium frozen foods and ready-to-eat meals. Quality guaranteed, freshness delivered.'; ?>">
    <meta property="og:image" content="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>/assets/img/logo.png">
    <meta property="og:site_name" content="FrozoFun">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="twitter:title" content="<?php echo isset($pageTitle) ? h($pageTitle) . ' - ' : ''; ?>FrozoFun">
    <meta property="twitter:description" content="<?php echo isset($pageDescription) ? h($pageDescription) : 'Premium frozen foods and ready-to-eat meals. Quality guaranteed, freshness delivered.'; ?>">
    <meta property="twitter:image" content="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>/assets/img/logo.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>/assets/img/favicon.png">
    <link rel="apple-touch-icon" href="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>/assets/img/logo.png">
    
    <!-- Canonical URL -->
    <?php if (isset($canonicalUrl)): ?>
    <link rel="canonical" href="<?php echo h($canonicalUrl); ?>">
    <?php endif; ?>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/minty/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- JavaScript Configuration -->
    <script>
        window.isUserLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
        window.baseUrl = '<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>';
    </script>