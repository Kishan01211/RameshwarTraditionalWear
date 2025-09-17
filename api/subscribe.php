<?php
// api/subscribe.php - handle newsletter subscriptions
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../config/db.php';

function redirect_back($ok, $msg = '') {
    $ref = $_SERVER['HTTP_REFERER'] ?? '/rtwrs_web/user/index.php';
    $sep = (strpos($ref, '?') === false) ? '?' : '&';
    $flag = $ok ? 'subscribed=1' : 'subscribed=0';
    if ($msg !== '') {
        $flag .= '&msg=' . urlencode($msg);
    }
    header('Location: ' . $ref . $sep . $flag);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_back(false, 'Invalid request');
}

$email = trim((string)($_POST['subscribe_email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_back(false, 'Please enter a valid email address');
}

try {
    // Ensure table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Insert (ignore duplicates)
    $stmt = $pdo->prepare('INSERT INTO newsletter (email) VALUES (?)');
    $stmt->execute([$email]);

    redirect_back(true);
} catch (PDOException $e) {
    // If duplicate email, still consider success
    if ($e->getCode() === '23000') { // integrity constraint violation
        redirect_back(true);
    }
    error_log('Subscribe error: ' . $e->getMessage());
    redirect_back(false, 'Server error, please try again later');
}
