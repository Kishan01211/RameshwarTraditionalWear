<?php
// Admin endpoint to update booking status manually
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../config/db.php';

// Basic admin auth check
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Allowed statuses
$allowed = ['pending','confirmed','dispatched','delivered','completed','cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid    = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $status = isset($_POST['status']) ? strtolower(trim((string)$_POST['status'])) : '';
    $back   = isset($_POST['redirect']) ? trim((string)$_POST['redirect']) : 'manage-orders.php';

    if ($bid <= 0 || !in_array($status, $allowed, true)) {
        $_SESSION['flash_error'] = 'Invalid booking or status.';
        header('Location: ' . $back);
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE bookings SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$status, $bid]);
        $_SESSION['flash_success'] = 'Booking #' . $bid . ' status updated to ' . strtoupper($status) . '.';
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = 'Failed to update status: ' . $e->getMessage();
    }

    header('Location: ' . $back);
    exit;
}

// If accessed via GET, redirect safely
header('Location: manage-orders.php');
