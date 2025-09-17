<?php
// delete-order.php: Deletes a booking/order by ID and redirects back to manage-orders.php
require "includes/admin-header.php";
require "../config/db.php";

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        // Delete related payments first (to satisfy foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM payments WHERE booking_id = ?");
        $stmt->execute([$id]);
        // Now delete the booking
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Order #$id deleted.";
    } catch (PDOException $e) {
        $msg = "Failed to delete order: " . $e->getMessage();
    }
} else {
    $msg = "Invalid order ID.";
}
header("Location: manage-orders.php?msg=".urlencode($msg));
exit;
