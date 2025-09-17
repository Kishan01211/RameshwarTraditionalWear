<?php
include "includes/admin-header.php";
include "../config/db.php";

$id = (int)$_GET['id'];

try {
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    $_SESSION['success'] = "Product deleted successfully!";
    header("Location: manage-products.php");
    exit();
} catch (PDOException $e) {
    // Check if it's a foreign key constraint violation
    if ($e->getCode() == '23000' && strpos($e->getMessage(), 'foreign key constraint') !== false) {
        $_SESSION['error'] = "Cannot delete this product because it is currently booked by customers.";
    } else {
        $_SESSION['error'] = "An error occurred while deleting the product.";
    }
    header("Location: manage-products.php");
    exit();
}
?>
