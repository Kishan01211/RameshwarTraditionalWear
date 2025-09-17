<?php
// Start session first
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// If user is already logged in, redirect to index (optional - remove if you want logged-in users to access login page)
// if (isset($_SESSION['user_id'])) {
//     header('Location: index.php');
//     exit;
// }

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_credential = trim($_POST['login_credential']);
    $password = $_POST['password'];

    $errors = [];

    if (empty($login_credential)) $errors[] = "Username or Email is required";
    if (empty($password)) $errors[] = "Password is required";

    if (empty($errors)) {
        // Check if input is email or username
        if (filter_var($login_credential, FILTER_VALIDATE_EMAIL)) {
            // Login with email
            $stmt = $pdo->prepare("SELECT id, user_name, name, email, password FROM users WHERE email = ?");
        } else {
            // Login with username
            $stmt = $pdo->prepare("SELECT id, user_name, name, email, password FROM users WHERE user_name = ?");
        }
        
        $stmt->execute([$login_credential]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_email'] = $user['email'];
            $redirect = $_GET['redirect'] ?? 'index.php';
            header("Location: $redirect");
            exit;
        } else {
            $errors[] = "Invalid username/email or password";
        }
    }
}

// Include header after all PHP logic that might redirect
//require_once '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-page">

<div class="auth-card">
    <h2>Welcome Back</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="auth-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="auth-alert error">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="auth-input">
            <input type="text" id="login_credential" name="login_credential" placeholder="Username or Email"
                   value="<?= htmlspecialchars($_POST['login_credential'] ?? '') ?>" required>
        </div>
        <div class="auth-input">
            <input type="password" id="password" name="password" placeholder="Password" required>
        </div>
        <button class="auth-btn" type="submit">Login</button>
    </form>
    <div class="auth-links">
        <p><a href="forgot-password.php">Forgot Password?</a> | <a href="register.php">Sign Up</a></p>
    </div>
</div>

</body>
</html>
