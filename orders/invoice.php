<?php
require_once __DIR__ . '/../config/database.php';

// Validate token parameter
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
        error_log("Invoice view error: " . $e->getMessage());
    }
}

// Format price display
function formatPrice($price) {
    return 'Rs. ' . number_format($price, 2);
}

// Escape HTML
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order #<?php echo isset($order) ? h($order['id']) : 'N/A'; ?> - FrozoFun</title>
    
    <!-- MDBootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        @media print {
            .no-print { display: none !important; }
            .container { max-width: 100% !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
            body { font-size: 12px; }
            .navbar, .footer { display: none !important; }
        }
        
        .invoice-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            text-align: center;
        }
        
        .invoice-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .invoice-table th {
            background-color: #007bff;
            color: white;
            border: none;
        }
        
        .invoice-table td {
            border-bottom: 1px solid #dee2e6;
        }
        
        .total-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Print Button (hidden in print) -->
    <div class="no-print">
        <div class="container mt-3">
            <div class="d-flex justify-content-between align-items-center">
                <a href="view.php?token=<?php echo isset($order) ? h($order['public_token']) : ''; ?>" 
                   class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Order
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print Invoice
                </button>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <?php if (isset($error)): ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                            <h4 class="text-danger mt-3">Error</h4>
                            <p class="text-muted"><?php echo h($error); ?></p>
                            <a href="/" class="btn btn-primary no-print">
                                <i class="bi bi-house"></i> Go Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Invoice Header -->
            <div class="invoice-header">
                <div class="row">
                    <div class="col-md-6">
                        <div class="company-info">
                            <h2 class="text-primary">
                                <i class="bi bi-snow2"></i> FrozoFun
                            </h2>
                            <p class="mb-1">Quality Frozen Foods & Meals</p>
                            <p class="mb-1">Your trusted partner for fresh frozen products</p>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <h3 class="text-primary">INVOICE</h3>
                        <p class="mb-1"><strong>Invoice #:</strong> <?php echo h($order['id']); ?></p>
                        <p class="mb-1"><strong>Date:</strong> <?php echo date('F d, Y', strtotime($order['order_date'])); ?></p>
                        <p class="mb-1"><strong>Order Token:</strong> <?php echo h($order['public_token']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Customer and Order Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="invoice-details">
                        <h5 class="text-primary mb-3">Bill To:</h5>
                        <p class="mb-1"><strong><?php echo h($order['customer_name']); ?></strong></p>
                        <p class="mb-1"><?php echo h($order['contact']); ?></p>
                        <?php if ($order['email']): ?>
                            <p class="mb-1"><?php echo h($order['email']); ?></p>
                        <?php endif; ?>
                        <p class="mb-1">
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
                <div class="col-md-6">
                    <div class="invoice-details">
                        <h5 class="text-primary mb-3">Order Status:</h5>
                        <p class="mb-1"><strong>Status:</strong> 
                            <?php
                            if ($order['cancelled']) {
                                echo '<span class="badge bg-danger">Cancelled</span>';
                            } elseif ($order['delivered']) {
                                echo '<span class="badge bg-success">Delivered</span>';
                                if ($order['delivered_at']) {
                                    echo '<br><small>Delivered on: ' . date('M d, Y', strtotime($order['delivered_at'])) . '</small>';
                                }
                            } else {
                                echo '<span class="badge bg-warning">Processing</span>';
                            }
                            ?>
                        </p>
                        <p class="mb-1"><strong>Payment:</strong>
                            <?php
                            if ($order['paid'] == 1) {
                                echo '<span class="badge bg-success">Paid</span>';
                            } elseif ($order['paid'] == 2) {
                                echo '<span class="badge bg-warning">Partially Paid</span>';
                            } else {
                                echo '<span class="badge bg-danger">Unpaid</span>';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Order Items Table -->
            <?php if (!empty($orderItems) || !empty($orderMeals)): ?>
                <div class="table-responsive mb-4">
                    <table class="table invoice-table">
                        <thead>
                            <tr>
                                <th>Item Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Pack Size</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo h($item['item_name']); ?></strong>
                                    </td>
                                    <td class="text-center"><?php echo number_format($item['qty'], 2); ?></td>
                                    <td class="text-center"><?php echo number_format($item['pack_size'], 2); ?></td>
                                    <td class="text-end"><?php echo formatPrice($item['price_per_unit']); ?></td>
                                    <td class="text-end"><?php echo formatPrice($item['total']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php foreach ($orderMeals as $meal): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo h($meal['meal_name']); ?></strong>
                                        <br><small class="text-muted">Meal Package</small>
                                    </td>
                                    <td class="text-center"><?php echo number_format($meal['qty'], 2); ?></td>
                                    <td class="text-center">-</td>
                                    <td class="text-end"><?php echo formatPrice($meal['price_per_meal']); ?></td>
                                    <td class="text-end"><?php echo formatPrice($meal['price_per_meal'] * $meal['qty']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Invoice Total Section -->
            <div class="row">
                <div class="col-md-8">
                    <!-- Payment History -->
                    <?php if (!empty($payments)): ?>
                        <div class="mb-4">
                            <h5 class="text-primary">Payment History</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Method</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($payment['paid_at'])); ?></td>
                                                <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                                <td class="text-end"><?php echo formatPrice($payment['amount']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <div class="total-section">
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

            <!-- Invoice Footer -->
            <div class="invoice-footer">
                <p><strong>Thank you for your business!</strong></p>
                <p>For any questions about this invoice, please contact us.</p>
                <p>Generated on <?php echo date('F d, Y \a\t h:i A'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- MDBootstrap 5 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.umd.min.js"></script>
</body>
</html>