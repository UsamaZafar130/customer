<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "Login";
$error = '';
$success = '';

// Handle form submission and redirects before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($usernameOrEmail) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check if login is username or email
        $stmt = $pdo->prepare("
            SELECT * FROM customer_users 
            WHERE (username = ? OR email = ?) 
            LIMIT 1
        ");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Sync localStorage cart to database if user was logged in
            $redirect = $_GET['redirect'] ?? '/';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once __DIR__ . '/../includes/seo_meta.php'; ?>
</head>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo h($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo h($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username_email" class="form-label">Username or Email</label>
                            <input type="text" class="form-control" id="username_email" name="username_email" 
                                   value="<?php echo h($_POST['username_email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                            <label class="form-check-label" for="remember_me">Remember me</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </form>
                    
                    <hr>
                    
                    <div class="text-center">
                        <p class="mb-0">Don't have an account? 
                            <a href="register.php">Register here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Sync cart after login
document.addEventListener('DOMContentLoaded', function() {
    // Check if user just logged in and has items in localStorage cart
    const cart = JSON.parse(localStorage.getItem('frozofun_cart') || '[]');
    if (cart.length > 0 && <?php echo isLoggedIn() ? 'true' : 'false'; ?>) {
        // Sync cart to database
        fetch('/cart/sync.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({cart: cart})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear localStorage cart after successful sync
                localStorage.removeItem('frozofun_cart');
                updateCartDisplay();
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>