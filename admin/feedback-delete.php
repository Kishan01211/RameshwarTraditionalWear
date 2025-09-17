<?php
// admin/feedback-delete.php - deletes a feedback record then redirects back
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
// Optional: check admin auth here if your panel uses a session key
// if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

require_once '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$redirect = 'manage-feedback.php';

if ($id > 0) {
    try {
        $stmt = $pdo->prepare('DELETE FROM feedback WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['flash_success'] = 'Feedback deleted successfully.';
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Failed to delete feedback: ' . $e->getMessage();
    }
}

header('Location: ' . $redirect);
exit;
