<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "Order Details";

// Validate token parameter and fetch order data
if (!isset($_GET['token']) || empty(trim($_GET['token']))) {
    $error = "Order token is required.";
} else {
    $token = trim($_GET['token']);
    
    // Fetch order details with customer and items information
    try {
        $stmt = $pdo->prepare("
            SELECT so.*, c.name as customer_name, c.contact, c.email, c.house_no, c.area, c.city, c.location
            FROM sales_orders so 
            JOIN customers c ON so.customer_id = c.id 
            WHERE so.public_token = ? AND c.deleted_at IS NULL
        ");
        $stmt->execute([$token]);
        $order = $stmt->fetch();
        
        if (!$order) {
            $error = "Order not found or invalid token.";
        } else {
            // Update page title with order info
            $pageTitle = "Order #" . $order['id'] . " - Order Details";
            
            // Fetch order items
            $itemsStmt = $pdo->prepare("
                SELECT oi.*, i.name as item_name, i.image as item_image
                FROM order_items oi
                JOIN items i ON oi.item_id = i.id
                WHERE oi.order_id = ?
            ");
            $itemsStmt->execute([$order['id']]);
            $orderItems = $itemsStmt->fetchAll();
            
            // Fetch order meals
            $mealsStmt = $pdo->prepare("
                SELECT om.*, m.name as meal_name
                FROM order_meals om
                JOIN meals m ON om.meal_id = m.id
                WHERE om.order_id = ?
            ");
            $mealsStmt->execute([$order['id']]);
            $orderMeals = $mealsStmt->fetchAll();
            
            // Fetch order payments
            $paymentsStmt = $pdo->prepare("
                SELECT * FROM order_payments 
                WHERE order_id = ?
                ORDER BY paid_at DESC
            ");
            $paymentsStmt->execute([$order['id']]);
            $payments = $paymentsStmt->fetchAll();
        }
    } catch (PDOException $e) {
        $error = "An error occurred while loading order details.";
        error_log("Order view error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once __DIR__ . '/../includes/seo_meta.php'; ?>
</head>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container my-4">
    <?php if (isset($error)): ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        <h4 class="text-danger mt-3">Error</h4>
                        <p class="text-muted"><?php echo h($error); ?></p>
                        <a href="/" class="btn btn-primary">
                            <i class="bi bi-house"></i> Go Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Order Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-2">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">
                                <i class="bi bi-receipt"></i> Order #<?php echo h($order['id']); ?>
                            </h3>
                            <div>
                                <a href="invoice.php?token=<?php echo h($order['public_token']); ?>" 
                                   class="btn btn-light" target="_blank">
                                    <i class="bi bi-printer"></i> Print Invoice
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Order Information</h5>
                                <div class="note note-light">
                                    <p><strong>Order Date:</strong> <?php echo date('F d, Y \a\t h:i A', strtotime($order['order_date'])); ?></p>
                                    <p><strong>Order Status:</strong> 
                                        <?php
                                        if ($order['cancelled']) {
                                            echo '<span class="badge badge-danger">Cancelled</span>';
                                        } elseif ($order['delivered']) {
                                            echo '<span class="badge badge-success">Delivered</span>';
                                            if ($order['delivered_at']) {
                                                echo ' on ' . date('M d, Y', strtotime($order['delivered_at']));
                                            }
                                        } else {
                                            echo '<span class="badge badge-warning">Processing</span>';
                                        }
                                        ?>
                                    </p>
                                    <p><strong>Payment Status:</strong>
                                        <?php
                                        if ($order['paid'] == 1) {
                                            echo '<span class="badge badge-success">Paid</span>';
                                        } elseif ($order['paid'] == 2) {
                                            echo '<span class="badge badge-warning">Partially Paid</span>';
                                        } else {
                                            echo '<span class="badge badge-danger">Unpaid</span>';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>Customer Information</h5>
                                <div class="note note-light">
                                    <p><strong>Name:</strong> <?php echo h($order['customer_name']); ?></p>
                                    <p><strong>Contact:</strong> <?php echo h($order['contact']); ?></p>
                                    <?php if ($order['email']): ?>
                                        <p><strong>Email:</strong> <?php echo h($order['email']); ?></p>
                                    <?php endif; ?>
                                    <p><strong>Address:</strong> 
                                        <?php 
                                        $address = [];
                                        if ($order['house_no']) $address[] = $order['house_no'];
                                        if ($order['area']) $address[] = $order['area'];
                                        if ($order['city']) $address[] = $order['city'];
                                        echo h(implode(', ', $address));
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <?php if (!empty($orderItems) || !empty($orderMeals)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-2">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bi bi-bag"></i> Order Items</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Pack Size</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($item['item_image']): ?>
                                                            <img src="/assets/images/items/<?php echo h($item['item_image']); ?>" 
                                                                 alt="<?php echo h($item['item_name']); ?>"
                                                                 class="rounded me-3" 
                                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo h($item['item_name']); ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo number_format($item['qty'], 2); ?></td>
                                                <td><?php echo number_format($item['pack_size'], 2); ?></td>
                                                <td><?php echo formatPrice($item['price_per_unit']); ?></td>
                                                <td><?php echo formatPrice($item['total']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php foreach ($orderMeals as $meal): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-dish me-3 text-primary" style="font-size: 2rem;"></i>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo h($meal['meal_name']); ?></h6>
                                                            <small class="text-muted">Meal</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo number_format($meal['qty'], 2); ?></td>
                                                <td>-</td>
                                                <td><?php echo formatPrice($meal['price_per_meal']); ?></td>
                                                <td><?php echo formatPrice($meal['price_per_meal'] * $meal['qty']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Order Summary -->
        <div class="row mb-4">
            <div class="col-md-8">
                <!-- Payment History -->
                <?php if (!empty($payments)): ?>
                    <div class="card shadow-2">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payment History</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($payments as $payment): ?>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <strong><?php echo formatPrice($payment['amount']); ?></strong>
                                        <span class="badge badge-info ms-2"><?php echo ucfirst($payment['payment_method']); ?></span>
                                    </div>
                                    <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($payment['paid_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div class="card shadow-2">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-calculator"></i> Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between py-2">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($order['amount']); ?></span>
                        </div>
                        <?php if ($order['discount'] > 0): ?>
                            <div class="d-flex justify-content-between py-2 text-success">
                                <span>Discount:</span>
                                <span>-<?php echo formatPrice($order['discount']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($order['delivery_charges'] > 0): ?>
                            <div class="d-flex justify-content-between py-2">
                                <span>Delivery Charges:</span>
                                <span><?php echo formatPrice($order['delivery_charges']); ?></span>
                            </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between py-2">
                            <strong>Grand Total:</strong>
                            <strong class="text-primary"><?php echo formatPrice($order['grand_total']); ?></strong>
                        </div>
                        
                        <?php 
                        $totalPaid = array_sum(array_column($payments, 'amount'));
                        $balanceDue = $order['grand_total'] - $totalPaid;
                        ?>
                        <?php if ($totalPaid > 0): ?>
                            <div class="d-flex justify-content-between py-2 text-success">
                                <span>Total Paid:</span>
                                <span><?php echo formatPrice($totalPaid); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($balanceDue > 0): ?>
                            <div class="d-flex justify-content-between py-2 text-danger">
                                <strong>Balance Due:</strong>
                                <strong><?php echo formatPrice($balanceDue); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>