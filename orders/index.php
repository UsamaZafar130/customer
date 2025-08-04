<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "My Orders";

// Check authentication and redirect if not logged in
if (!isLoggedIn()) {
    header('Location: /auth/login.php?redirect=/orders/');
    exit;
}

// Get user's orders
$stmt = $pdo->prepare("
    SELECT so.*, c.name as customer_name, c.contact, c.area, c.city,
           COUNT(DISTINCT oi.id) + COUNT(DISTINCT om.id) as item_count,
           sbo.id as shipping_batch_id
    FROM sales_orders so 
    JOIN customers c ON so.customer_id = c.id 
    LEFT JOIN order_items oi ON so.id = oi.order_id
    LEFT JOIN order_meals om ON so.id = om.order_id
    LEFT JOIN shipping_batch_orders sbo ON so.id = sbo.order_id
    WHERE c.user_id = ? 
    GROUP BY so.id
    ORDER BY so.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once __DIR__ . '/../includes/seo_meta.php'; ?>
</head>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <h2>My Orders</h2>
            <p class="text-muted">Track and view your order history.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php if (empty($orders)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-bag text-muted" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">No orders yet</h4>
                    <p class="text-muted">When you place your first order, it will appear here.</p>
                    <a href="/products/" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Delivery Address</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo h($order['id']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($order['order_date'])); ?><br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($order['order_date'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $order['item_count']; ?> items</span>
                                    </td>
                                    <td>
                                        <strong><?php echo h($order['customer_name']); ?></strong><br>
                                        <small class="text-muted">
                                            <?php echo h($order['area']); ?><?php echo $order['city'] ? ', ' . h($order['city']) : ''; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?php echo formatPrice($order['grand_total']); ?></strong>
                                        <?php if ($order['discount'] > 0): ?>
                                            <br><small class="text-success">-<?php echo formatPrice($order['discount']); ?> discount</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Order Status Badge
                                        $orderStatusClass = 'bg-warning';
                                        $orderStatusText = 'Pending';
                                        
                                        if ($order['cancelled']) {
                                            $orderStatusClass = 'bg-danger';
                                            $orderStatusText = 'Cancelled';
                                        } elseif ($order['delivered']) {
                                            $orderStatusClass = 'bg-success';
                                            $orderStatusText = 'Delivered';
                                        } elseif ($order['shipping_batch_id']) {
                                            $orderStatusClass = 'bg-info';
                                            $orderStatusText = 'Preparing';
                                        }
                                        
                                        // Payment Status Badge
                                        $paymentStatusClass = 'bg-danger';
                                        $paymentStatusText = 'Unpaid';
                                        
                                        if ($order['paid'] == 1) {
                                            $paymentStatusClass = 'bg-success';
                                            $paymentStatusText = 'Paid';
                                        } elseif ($order['paid'] == 2) {
                                            $paymentStatusClass = 'bg-warning';
                                            $paymentStatusText = 'Partial Paid';
                                        }
                                        ?>
                                        <div class="mb-1">
                                            <span class="badge <?php echo $orderStatusClass; ?>"><?php echo $orderStatusText; ?></span>
                                        </div>
                                        <div>
                                            <span class="badge <?php echo $paymentStatusClass; ?>"><?php echo $paymentStatusText; ?></span>
                                        </div>
                                        <?php if ($order['delivered'] && $order['delivered_at']): ?>
                                            <br><small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($order['delivered_at'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view.php?token=<?php echo h($order['public_token']); ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>