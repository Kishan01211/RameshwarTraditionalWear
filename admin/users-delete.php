<?php
session_start();
include "includes/admin-header.php";
include "../config/db.php";

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "User deleted successfully!";
        header("Location: manage-users.php");
        exit();
    } catch (PDOException $e) {
        // Check if it's a foreign key constraint violation
        if ($e->getCode() == '23000' && strpos($e->getMessage(), 'foreign key constraint') !== false) {
            $_SESSION['error'] = "Cannot delete this user because they have active bookings or other associated records.";
        } else {
            $_SESSION['error'] = "An error occurred while deleting the user.";
        }
        header("Location: manage-users.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: manage-users.php");
    exit();
}
