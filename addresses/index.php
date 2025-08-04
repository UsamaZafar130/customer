<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "My Addresses";

// Check authentication and redirect if not logged in
if (!isLoggedIn()) {
    header('Location: /auth/login.php?redirect=/addresses/');
    exit;
}

$user = getCurrentUser();

// Get user's addresses
$stmt = $pdo->prepare("
    SELECT * FROM customers 
    WHERE user_id = ? AND deleted_at IS NULL 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$addresses = $stmt->fetchAll();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Addresses</h2>
                <a href="add.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Address
                </a>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo h($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo h($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($addresses)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-house text-muted" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">No addresses saved</h4>
                    <p class="text-muted">Add your delivery addresses to make ordering easier.</p>
                    <a href="add.php" class="btn btn-primary">Add Your First Address</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($addresses as $address): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card address-card h-100">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-person-fill me-2"></i><?php echo h($address['name']); ?>
                            </h6>
                            
                            <div class="mb-2">
                                <small class="text-muted">Contact:</small><br>
                                <span><?php echo h($address['contact']); ?></span>
                            </div>
                            
                            <?php if ($address['email']): ?>
                                <div class="mb-2">
                                    <small class="text-muted">Email:</small><br>
                                    <span><?php echo h($address['email']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <small class="text-muted">Address:</small><br>
                                <?php if ($address['house_no']): ?>
                                    <?php echo h($address['house_no']); ?>,<br>
                                <?php endif; ?>
                                <?php if ($address['area']): ?>
                                    <?php echo h($address['area']); ?>,<br>
                                <?php endif; ?>
                                <?php if ($address['city']): ?>
                                    <?php echo h($address['city']); ?>
                                <?php endif; ?>
                                <?php if ($address['location']): ?>
                                    <br><small class="text-muted"><?php echo h($address['location']); ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="btn-group w-100" role="group">
                                <a href="edit.php?id=<?php echo $address['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                        onclick="deleteAddress(<?php echo $address['id']; ?>)">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteAddress(addressId) {
    if (confirm('Are you sure you want to delete this address?')) {
        fetch('/addresses/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                address_id: addressId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showAlert('danger', data.message || 'Failed to delete address');
            }
        });
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>