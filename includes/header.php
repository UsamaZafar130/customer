<body class="d-flex flex-column min-vh-100">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img src="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>/assets/img/logo.png" 
                     alt="FrozoFun Logo" height="40" class="me-2" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span style="display: none;"><i class="bi bi-snow2"></i></span>
                FrozoFun
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/products/">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/products/meals.php">Meals</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <!-- Search -->
                    <div class="me-3 position-relative">
                        <input type="text" class="form-control search-box" id="searchInput" placeholder="Search products..." style="width: 250px;">
                        <div id="searchResults" class="position-absolute bg-white border rounded shadow" style="top: 100%; left: 0; right: 0; z-index: 1000; display: none; max-height: 300px; overflow-y: auto;"></div>
                    </div>
                    
                    <!-- Cart -->
                    <a href="/cart/" class="btn btn-outline-light me-2 position-relative">
                        <i class="bi bi-cart3"></i>
                        <span class="cart-badge"><?php echo isLoggedIn() ? getCartCount() : '0'; ?></span>
                    </a>
                    
                    <!-- User Menu -->
                    <?php if (isLoggedIn()): ?>
                        <?php $user = getCurrentUser(); ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person"></i> <?php echo h($user['username']); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/orders/">My Orders</a></li>
                                <li><a class="dropdown-item" href="/addresses/">My Addresses</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/auth/logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="/auth/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-outline-light me-2">Login</a>
                        <a href="/auth/register.php" class="btn btn-light">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->