<?php
require_once '../includes/header.php';
require_once '../config/db.php';

$token = $_GET['token'] ?? '';
$errors = [];
$success = '';

// Validate token
if (empty($token)) {
    $errors[] = "Invalid or missing reset token";
} else {
    // Check if token exists and is not expired
    $stmt = $pdo->prepare("SELECT id, email, name FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $errors[] = "Invalid or expired reset token. Please request a new password reset.";
    }
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($new_password)) {
        $errors[] = "New password is required";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        // Update password and clear reset token
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
        $stmt->execute([$hashed_password, $token]);
        
        $success = "Your password has been successfully reset! You can now login with your new password.";
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="booking-form">
                <h2 class="text-center mb-4">Reset Password</h2>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?= $success ?>
                        <div class="text-center mt-3">
                            <a href="login.php" class="btn btn-primary">Go to Login</a>
                        </div>
                    </div>
                <?php elseif (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?= htmlspecialchars($error) ?></div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="forgot-password.php" class="btn btn-outline-primary">Request New Reset</a>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted mb-4">
                        Enter your new password below.
                    </p>

                    <form method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   placeholder="Enter your new password" minlength="6" required>
                            <small class="text-muted">Password must be at least 6 characters long</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm your new password" minlength="6" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <p>
                        <a href="login.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Back to Login
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
