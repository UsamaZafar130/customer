<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "Shopping Cart";

// For logged-in users, get cart from database
$cartItems = [];
$cartTotal = 0;

if (isLoggedIn()) {
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
               END as product_type,
               CASE 
                   WHEN cc.item_id IS NOT NULL THEN i.image 
                   ELSE NULL 
               END as product_image
        FROM customer_cart cc 
        LEFT JOIN items i ON cc.item_id = i.id 
        LEFT JOIN meals m ON cc.meal_id = m.id 
        WHERE cc.user_id = ? 
        ORDER BY cc.created_at ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll();
    
    foreach ($cartItems as $item) {
        $cartTotal += ($item['unit_price'] * $item['pack_size'] * $item['qty']);
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
            <h2>Shopping Cart</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <?php if (isLoggedIn()): ?>
                <!-- Database Cart for Logged In Users -->
                <div id="database-cart">
                    <?php if (empty($cartItems)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">Your cart is empty</h4>
                            <p class="text-muted">Add some products to get started!</p>
                            <a href="/products/" class="btn btn-primary">Continue Shopping</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item" data-cart-id="<?php echo $item['id']; ?>">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <?php if ($item['product_image'] && $item['product_type'] === 'item'): ?>
                                            <img src="https://admin.frozofun.com/uploads/items/<?php echo h($item['product_image']); ?>" 
                                                 class="img-fluid rounded" alt="<?php echo h($item['product_name']); ?>">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                                <i class="bi bi-<?php echo $item['product_type'] === 'meal' ? 'bowl-hot' : 'image'; ?> text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <h6><?php echo h($item['product_name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo ucfirst($item['product_type']); ?>
                                            <?php if ($item['product_type'] === 'item'): ?>
                                                - Pack Size: <?php echo $item['pack_size']; ?> pcs
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Price</label>
                                        <div class="fw-bold">
                                            <?php echo formatPrice($item['unit_price'] * $item['pack_size']); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Quantity</label>
                                        <input type="number" class="form-control quantity-input" 
                                               value="<?php echo $item['qty']; ?>" min="1" max="99"
                                               onchange="updateDatabaseCartItem(<?php echo $item['id']; ?>, this.value)">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label small">Total</label>
                                        <div class="fw-bold">
                                            <?php echo formatPrice($item['unit_price'] * $item['pack_size'] * $item['qty']); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="removeDatabaseCartItem(<?php echo $item['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- LocalStorage Cart for Guests -->
                <div id="guest-cart">
                    <div class="text-center py-5" id="empty-cart-message">
                        <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mt-3">Your cart is empty</h4>
                        <p class="text-muted">Add some products to get started!</p>
                        <a href="/products/" class="btn btn-primary">Continue Shopping</a>
                    </div>
                    <div id="cart-items-container" style="display: none;"></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="cart-subtotal">
                            <?php echo isLoggedIn() ? formatPrice($cartTotal) : 'Rs. 0.00'; ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Charges:</span>
                        <span id="delivery-charges">Rs. 0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span id="cart-total">
                            <?php echo isLoggedIn() ? formatPrice($cartTotal) : 'Rs. 0.00'; ?>
                        </span>
                    </div>
                    
                    <div class="mt-3">
                        <?php if (isLoggedIn()): ?>
                            <?php if (!empty($cartItems)): ?>
                                <a href="/orders/checkout.php" class="btn btn-success w-100 btn-lg">
                                    <i class="bi bi-credit-card"></i> Proceed to Checkout
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="/auth/login.php?redirect=/cart/" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-box-arrow-in-right"></i> Login to Checkout
                            </a>
                            <div class="text-center">
                                <small class="text-muted">or <a href="/auth/register.php">create an account</a></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Guest cart management (localStorage)
function displayGuestCart() {
    const cart = JSON.parse(localStorage.getItem('frozofun_cart') || '[]');
    const container = document.getElementById('cart-items-container');
    const emptyMessage = document.getElementById('empty-cart-message');
    
    if (cart.length === 0) {
        container.style.display = 'none';
        emptyMessage.style.display = 'block';
        document.getElementById('cart-subtotal').textContent = 'Rs. 0.00';
        document.getElementById('cart-total').textContent = 'Rs. 0.00';
        return;
    }
    
    emptyMessage.style.display = 'none';
    container.style.display = 'block';
    
    let html = '';
    let total = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * item.pack_size * item.qty;
        total += itemTotal;
        
        html += `
        <div class="cart-item" data-item-type="${item.type}" data-item-id="${item.id}" data-pack-size="${item.pack_size}">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                        <i class="bi bi-${item.type === 'meal' ? 'bowl-hot' : 'image'} text-muted"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6>${item.name}</h6>
                    <small class="text-muted">
                        ${item.type.charAt(0).toUpperCase() + item.type.slice(1)}
                        ${item.type === 'item' ? '- Pack Size: ' + item.pack_size + ' pcs' : ''}
                    </small>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Price</label>
                    <div class="fw-bold">$${(item.price * item.pack_size).toFixed(2)}</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Quantity</label>
                    <input type="number" class="form-control quantity-input" 
                           value="${item.qty}" min="1" max="99"
                           onchange="updateGuestCartItem('${item.type}', ${item.id}, ${item.pack_size}, this.value)">
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Total</label>
                    <div class="fw-bold">$${itemTotal.toFixed(2)}</div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger btn-sm" 
                            onclick="removeGuestCartItem('${item.type}', ${item.id}, ${item.pack_size})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>`;
    });
    
    container.innerHTML = html;
    document.getElementById('cart-subtotal').textContent = 'Rs. ' + total.toFixed(2);
    document.getElementById('cart-total').textContent = 'Rs. ' + total.toFixed(2);
}

function updateGuestCartItem(type, id, packSize, newQty) {
    updateCartItemQty(type, id, packSize, newQty);
    displayGuestCart();
}

function removeGuestCartItem(type, id, packSize) {
    removeFromCart(type, id, packSize);
    displayGuestCart();
}

// Database cart management (logged-in users)
function updateDatabaseCartItem(cartId, newQty) {
    fetch('/cart/update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cart_id: cartId,
            qty: newQty
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showAlert('danger', data.message || 'Failed to update cart');
        }
    });
}

function removeDatabaseCartItem(cartId) {
    if (confirm('Remove this item from cart?')) {
        fetch('/cart/remove.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_id: cartId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showAlert('danger', data.message || 'Failed to remove item');
            }
        });
    }
}

// Initialize cart display for guests
<?php if (!isLoggedIn()): ?>
document.addEventListener('DOMContentLoaded', function() {
    displayGuestCart();
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>