<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "Checkout";

// Check authentication and redirect if not logged in
if (!isLoggedIn()) {
    header('Location: /auth/login.php?redirect=/orders/checkout.php');
    exit;
}

// Get cart items
$stmt = $pdo->prepare("
    SELECT cc.*, 
           CASE 
               WHEN cc.item_id IS NOT NULL THEN i.name 
               WHEN cc.meal_id IS NOT NULL THEN m.name 
           END as product_name,
           CASE 
               WHEN cc.item_id IS NOT NULL THEN i.price_per_unit 
               WHEN cc.meal_id IS NOT NULL THEN m.price 
           END as unit_price,
           CASE 
               WHEN cc.item_id IS NOT NULL THEN 'item' 
               WHEN cc.meal_id IS NOT NULL THEN 'meal' 
           END as product_type
    FROM customer_cart cc 
    LEFT JOIN items i ON cc.item_id = i.id 
    LEFT JOIN meals m ON cc.meal_id = m.id 
    WHERE cc.user_id = ? 
    ORDER BY cc.created_at ASC
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

// Redirect if cart is empty
if (empty($cartItems)) {
    header('Location: /cart/?error=' . urlencode('Your cart is empty'));
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['unit_price'] * $item['pack_size'] * $item['qty']);
}

$deliveryCharges = 0; // Could be calculated based on location
$total = $subtotal + $deliveryCharges;

// Get user addresses
$stmt = $pdo->prepare("
    SELECT * FROM customers 
    WHERE user_id = ? AND deleted_at IS NULL 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$addresses = $stmt->fetchAll();

$error = '';
$success = '';

// Handle form submission and order creation before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedAddressId = (int)($_POST['address_id'] ?? 0);
    $useNewAddress = $_POST['use_new_address'] ?? '';
    
    $addressId = null;
    
    if ($useNewAddress === 'yes') {
        // Create new address
        $name = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $houseNo = trim($_POST['house_no'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $location = trim($_POST['location'] ?? '');
        
        if (empty($name) || empty($contact)) {
            $error = 'Name and contact are required for delivery address.';
        } else {
            try {
                $contactNormalized = preg_replace('/[^0-9+]/', '', $contact);
                
                $stmt = $pdo->prepare("
                    INSERT INTO customers (user_id, name, contact, contact_normalized, email, house_no, area, city, location, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                if ($stmt->execute([$_SESSION['user_id'], $name, $contact, $contactNormalized, $email ?: null, $houseNo ?: null, $area ?: null, $city ?: null, $location ?: null])) {
                    $addressId = $pdo->lastInsertId();
                }
            } catch (Exception $e) {
                $error = 'Failed to save delivery address.';
            }
        }
    } else {
        if (!$selectedAddressId) {
            $error = 'Please select a delivery address.';
        } else {
            // Verify address belongs to user
            $stmt = $pdo->prepare("SELECT id FROM customers WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
            $stmt->execute([$selectedAddressId, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $addressId = $selectedAddressId;
            } else {
                $error = 'Invalid address selected.';
            }
        }
    }
    
    if (!$error && $addressId) {
        // Create order
        try {
            $pdo->beginTransaction();
            
            // Generate public token for order
            $publicToken = bin2hex(random_bytes(16));
            
            // Create sales order
            $stmt = $pdo->prepare("
                INSERT INTO sales_orders (public_token, customer_id, amount, discount, delivery_charges, grand_total, created_at) 
                VALUES (?, ?, ?, 0, ?, ?, NOW())
            ");
            $stmt->execute([$publicToken, $addressId, $subtotal, $deliveryCharges, $total]);
            $orderId = $pdo->lastInsertId();
            
            // Add order items
            foreach ($cartItems as $item) {
                $itemTotal = $item['unit_price'] * $item['pack_size'] * $item['qty'];
                
                if ($item['product_type'] === 'item') {
                    $stmt = $pdo->prepare("
                        INSERT INTO order_items (order_id, item_id, qty, pack_size, price_per_unit, total) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$orderId, $item['item_id'], $item['qty'], $item['pack_size'], $item['unit_price'], $itemTotal]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO order_meals (order_id, meal_id, qty, price_per_meal) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$orderId, $item['meal_id'], $item['qty'], $item['unit_price']]);
                }
            }
            
            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM customer_cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            $pdo->commit();
            
            header('Location: /orders/success.php?order=' . $publicToken);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to place order. Please try again.';
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

<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <h2>Checkout</h2>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row">
            <div class="col-lg-8">
                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <h6 class="mb-0"><?php echo h($item['product_name']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo ucfirst($item['product_type']); ?>
                                        <?php if ($item['product_type'] === 'item'): ?>
                                            - Pack Size: <?php echo $item['pack_size']; ?> pcs
                                        <?php endif; ?>
                                        × <?php echo $item['qty']; ?>
                                    </small>
                                </div>
                                <div class="fw-bold">
                                    <?php echo formatPrice($item['unit_price'] * $item['pack_size'] * $item['qty']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Delivery Address</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($addresses)): ?>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="use_new_address" value="no" 
                                           id="use_existing" <?php echo ($_POST['use_new_address'] ?? 'no') === 'no' ? 'checked' : ''; ?>
                                           onchange="toggleAddressForm()">
                                    <label class="form-check-label fw-bold" for="use_existing">
                                        Use existing address
                                    </label>
                                </div>
                            </div>
                            
                            <div id="existing-addresses" class="mb-4">
                                <div class="row">
                                    <?php foreach ($addresses as $address): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="address_id" 
                                                       value="<?php echo $address['id']; ?>" id="addr_<?php echo $address['id']; ?>"
                                                       <?php echo ($_POST['address_id'] ?? '') == $address['id'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label w-100" for="addr_<?php echo $address['id']; ?>">
                                                    <div class="card address-card">
                                                        <div class="card-body p-3">
                                                            <h6><?php echo h($address['name']); ?></h6>
                                                            <p class="mb-1 small"><?php echo h($address['contact']); ?></p>
                                                            <p class="mb-0 small text-muted">
                                                                <?php if ($address['house_no']): ?>
                                                                    <?php echo h($address['house_no']); ?>,
                                                                <?php endif; ?>
                                                                <?php if ($address['area']): ?>
                                                                    <?php echo h($address['area']); ?>,
                                                                <?php endif; ?>
                                                                <?php if ($address['city']): ?>
                                                                    <?php echo h($address['city']); ?>
                                                                <?php endif; ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="use_new_address" value="yes" 
                                   id="use_new" <?php echo ($_POST['use_new_address'] ?? '') === 'yes' ? 'checked' : ''; ?>
                                   onchange="toggleAddressForm()">
                            <label class="form-check-label fw-bold" for="use_new">
                                Use new address
                            </label>
                        </div>

                        <div id="new-address-form" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo h($_POST['name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contact" class="form-label">Contact Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="contact" name="contact" 
                                           value="<?php echo h($_POST['contact'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo h($_POST['email'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="house_no" class="form-label">House/Building No.</label>
                                    <input type="text" class="form-control" id="house_no" name="house_no" 
                                           value="<?php echo h($_POST['house_no'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="area" class="form-label">Area/Locality</label>
                                    <input type="text" class="form-control" id="area" name="area" 
                                           value="<?php echo h($_POST['area'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?php echo h($_POST['city'] ?? ''); ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="location" class="form-label">Additional Details</label>
                                    <textarea class="form-control" id="location" name="location" rows="2"><?php echo h($_POST['location'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Charges:</span>
                            <span><?php echo formatPrice($deliveryCharges); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold mb-3">
                            <span>Total:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 btn-lg">
                            <i class="bi bi-check-circle"></i> Place Order
                        </button>
                        
                        <div class="text-center mt-3">
                            <a href="/cart/" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Back to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function toggleAddressForm() {
    const useNew = document.getElementById('use_new').checked;
    const newForm = document.getElementById('new-address-form');
    const existingAddresses = document.getElementById('existing-addresses');
    
    if (useNew) {
        newForm.style.display = 'block';
        if (existingAddresses) {
            existingAddresses.style.display = 'none';
            // Uncheck existing address selections
            document.querySelectorAll('input[name="address_id"]').forEach(input => {
                input.checked = false;
            });
        }
    } else {
        newForm.style.display = 'none';
        if (existingAddresses) {
            existingAddresses.style.display = 'block';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleAddressForm();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>