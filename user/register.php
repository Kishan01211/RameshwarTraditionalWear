<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validation
    if (empty($user_name)) $errors[] = "Username is required";
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($phone)) $errors[] = "Phone is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";

    // Check if email or username already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR user_name = ?");
        $stmt->execute([$email, $user_name]);
        if ($stmt->fetch()) {
            $errors[] = "Email or username already registered";
        }
    }

    // Register user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (user_name, name, email, phone, address, password) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_name, $name, $email, $phone, $address, $hashed_password])) {
            $_SESSION['success'] = "Registration successful! Please login.";
            header('Location: login.php');
            exit;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account</title>
  <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-page">

<div class="auth-card">
                <h2>Create Account</h2>

                <?php if (!empty($errors)): ?>
                    <div class="auth-alert error">
                        <?php foreach ($errors as $error): ?>
                            <div><?= htmlspecialchars($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="auth-input">
                        <input type="text" id="user_name" name="user_name" 
                               value="<?= htmlspecialchars($_POST['user_name'] ?? '') ?>" 
                               placeholder="Username" required>
                    </div>

                    <div class="auth-input">
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Full Name" required>
                    </div>

                    <div class="auth-input">
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Email Address" required>
                    </div>

                    <div class="auth-input">
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="Phone Number" required>
                    </div>

                    <div class="auth-input">
                        <textarea id="address" name="address" rows="3" placeholder="Address" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>

                    <div class="auth-input">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>

                    <div class="auth-input">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    </div>

                    <button type="submit" class="auth-btn">Register</button>
                </form>

                <div class="auth-links">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
</div>
</body>
</html>
