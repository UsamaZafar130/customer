<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "Order Successful";

// Check authentication and redirect if not logged in
if (!isLoggedIn()) {
    header('Location: /auth/login.php');
    exit;
}

// Validate order token
$orderToken = $_GET['order'] ?? '';
if (!$orderToken) {
    header('Location: /');
    exit;
}

// Get order details
$stmt = $pdo->prepare("
    SELECT so.*, c.name as customer_name, c.contact, c.house_no, c.area, c.city 
    FROM sales_orders so 
    JOIN customers c ON so.customer_id = c.id 
    WHERE so.public_token = ? AND c.user_id = ?
");
$stmt->execute([$orderToken, $_SESSION['user_id']]);
$order = $stmt->fetch();

// Redirect if order not found
if (!$order) {
    header('Location: /');
    exit;
}

// Update page title with order ID
$pageTitle = "Order #" . $order['id'] . " - Order Successful";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once __DIR__ . '/../includes/seo_meta.php'; ?>
</head>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="text-center mb-4">
                <div class="text-success mb-3">
                    <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
                </div>
                <h2 class="text-success">Order Placed Successfully!</h2>
                <p class="text-muted">Thank you for your order. We'll start preparing it right away.</p>
            </div>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Order ID:</strong></div>
                        <div class="col-sm-8">#<?php echo h($order['id']); ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Order Date:</strong></div>
                        <div class="col-sm-8"><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Total Amount:</strong></div>
                        <div class="col-sm-8 fw-bold text-success"><?php echo formatPrice($order['grand_total']); ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Delivery To:</strong></div>
                        <div class="col-sm-8">
                            <?php echo h($order['customer_name']); ?><br>
                            <small class="text-muted">
                                <?php if ($order['house_no']): ?>
                                    <?php echo h($order['house_no']); ?>,
                                <?php endif; ?>
                                <?php if ($order['area']): ?>
                                    <?php echo h($order['area']); ?>,
                                <?php endif; ?>
                                <?php if ($order['city']): ?>
                                    <?php echo h($order['city']); ?>
                                <?php endif; ?>
                                <br>Contact: <?php echo h($order['contact']); ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Status:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-warning">Processing</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <div class="d-flex gap-2 justify-content-center">
                    <a href="/orders/" class="btn btn-primary">
                        <i class="bi bi-list-ul"></i> View All Orders
                    </a>
                    <a href="/" class="btn btn-outline-primary">
                        <i class="bi bi-house"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <div class="alert alert-info mt-4">
                <h6><i class="bi bi-info-circle"></i> What's Next?</h6>
                <ul class="mb-0 small">
                    <li>We'll start preparing your order immediately</li>
                    <li>You'll receive updates as your order progresses</li>
                    <li>Our delivery team will contact you before delivery</li>
                    <li>Payment can be made upon delivery (Cash on Delivery)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>