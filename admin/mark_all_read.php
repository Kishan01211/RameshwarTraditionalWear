<?php
// Start session if not already started
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Include required files
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/includes/NotificationManager.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}

// Initialize NotificationManager
$notificationManager = new NotificationManager($pdo);

// Get type from query string if present
$type = $_GET['type'] ?? null;

// Mark all as read
if ($notificationManager->markAllAsRead($type)) {
    $_SESSION['success'] = "All notifications marked as read";
} else {
    $_SESSION['error'] = "Error marking notifications as read";
}

// Redirect back to the previous page or notifications page
$redirect = $_SERVER['HTTP_REFERER'] ?? 'notifications.php';
if ($type) {
    $redirect = strpos($redirect, '?') !== false 
        ? $redirect . '&type=' . urlencode($type) 
        : $redirect . '?type=' . urlencode($type);
}

header("Location: $redirect");
exit;
