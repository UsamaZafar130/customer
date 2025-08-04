    <!-- Footer -->
    <footer class="footer mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-snow2"></i> FrozoFun</h5>
                    <p>Your trusted partner for quality frozen foods and meals.</p>
                </div>
                <div class="col-md-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="/" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="/products/" class="text-light text-decoration-none">Products</a></li>
                        <li><a href="/products/meals.php" class="text-light text-decoration-none">Meals</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Account</h6>
                    <ul class="list-unstyled">
                        <?php if (isLoggedIn()): ?>
                            <li><a href="/orders/" class="text-light text-decoration-none">My Orders</a></li>
                            <li><a href="/addresses/" class="text-light text-decoration-none">My Addresses</a></li>
                        <?php else: ?>
                            <li><a href="/auth/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="text-light text-decoration-none">Login</a></li>
                            <li><a href="/auth/register.php" class="text-light text-decoration-none">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <hr class="my-3">
            <div class="text-center">
                <small>&copy; <?php echo date('Y'); ?> FrozoFun. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <!-- Product Quick View Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']; ?>/assets/js/main.js"></script>
</body>
</html>