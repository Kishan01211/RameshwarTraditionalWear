<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $del = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND user_id = ?');
    $del->execute([$id, $uid]);
}

header('Location: cart.php');
