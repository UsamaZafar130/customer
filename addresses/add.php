<?php
// Include init.php at the very top for PHP logic
require_once __DIR__ . '/../includes/init.php';

// Set page variables
$pageTitle = "Add Address";

// Check authentication and redirect if not logged in
if (!isLoggedIn()) {
    header('Location: /auth/login.php?redirect=/addresses/add.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission and redirects before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $houseNo = trim($_POST['house_no'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $location = trim($_POST['location'] ?? '');
    
    // Validation
    if (empty($name) || empty($contact)) {
        $error = 'Name and contact are required.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Normalize contact number (remove spaces, dashes, etc.)
            $contactNormalized = preg_replace('/[^0-9+]/', '', $contact);
            
            $stmt = $pdo->prepare("
                INSERT INTO customers (user_id, name, contact, contact_normalized, email, house_no, area, city, location, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            if ($stmt->execute([$_SESSION['user_id'], $name, $contact, $contactNormalized, $email ?: null, $houseNo ?: null, $area ?: null, $city ?: null, $location ?: null])) {
                header('Location: /addresses/?success=' . urlencode('Address added successfully!'));
                exit;
            } else {
                $error = 'Failed to add address. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Failed to add address. Please try again.';
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
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Add New Address</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo h($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo h($_POST['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact" name="contact" 
                                   value="<?php echo h($_POST['contact'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo h($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="house_no" class="form-label">House/Building Number</label>
                            <input type="text" class="form-control" id="house_no" name="house_no" 
                                   value="<?php echo h($_POST['house_no'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="area" class="form-label">Area/Locality</label>
                            <input type="text" class="form-control" id="area" name="area" 
                                   value="<?php echo h($_POST['area'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?php echo h($_POST['city'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Additional Location Details</label>
                            <textarea class="form-control" id="location" name="location" rows="3"><?php echo h($_POST['location'] ?? ''); ?></textarea>
                            <div class="form-text">Any additional details to help with delivery (landmarks, floor number, etc.)</div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Save Address
                            </button>
                            <a href="/addresses/" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Addresses
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>