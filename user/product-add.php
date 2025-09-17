<?php
// Add product to cart (and optional Buy Now)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid   = (int)$_SESSION['user_id'];

    // Inputs (coming from product-detail/rent pages)
    $pid   = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $price = isset($_POST['product_price']) ? (float)$_POST['product_price'] : 0.0;
    $size  = isset($_POST['selected_size']) ? trim((string)$_POST['selected_size']) : null;
    $color = isset($_POST['selected_color']) ? trim((string)$_POST['selected_color']) : null;
    $sd    = isset($_POST['start_date']) ? trim((string)$_POST['start_date']) : '';
    $ed    = isset($_POST['end_date']) ? trim((string)$_POST['end_date']) : '';

    // Validate dates
    if (!$sd || !$ed) {
        $_SESSION['flash_error'] = 'Please select start and end dates.';
        header('Location: cart.php');
        exit;
    }

    // Compute inclusive rental days
    $days = (strtotime($ed) - strtotime($sd)) / 86400 + 1;
    if ($days < 1) $days = 1;

    // Ensure product exists and price fallback from DB if not posted
    try {
        $stmt = $pdo->prepare('SELECT id, product_name, image_url, price_per_day FROM products WHERE id = ? AND status = "active"');
        $stmt->execute([$pid]);
        $product = $stmt->fetch();
        if (!$product) {
            header('Location: products.php');
            exit;
        }
        if ($price <= 0) {
            $price = (float)$product['price_per_day'];
        }
        // image and name are not needed for cart insert; we only ensure product exists and price fallback

        // Check duplicate cart line
        $chk = $pdo->prepare('SELECT id FROM cart_items WHERE user_id = ? AND product_id = ? AND IFNULL(selected_size, "") = IFNULL(?, "") AND IFNULL(selected_color, "") = IFNULL(?, "") AND start_date = ? AND end_date = ?');
        $chk->execute([$uid, $pid, $size, $color, $sd, $ed]);
        $existingId = $chk->fetchColumn();
        $exists = (bool)$existingId;

        $cartId = null;
        if (!$exists) {
            $ins = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, selected_size, selected_color, start_date, end_date, price_per_day, rental_days, quantity) VALUES (?,?,?,?,?,?,?,?,?)');
            $qty = 1;
            $ins->execute([$uid, $pid, $size, $color, $sd, $ed, $price, (int)$days, $qty]);
            $cartId = (int)$pdo->lastInsertId();
            $_SESSION["flash_success"] = 'Item added to cart.';
        }
        else {
            $cartId = (int)$existingId;
            $_SESSION['flash_info'] = 'This item is already in your cart for the selected options and dates.';
        }

        // Proceed to checkout only when explicitly set to '1'
        if (isset($_POST['book_now']) && $_POST['book_now'] === '1') {
            $_SESSION['buy_now_id'] = $cartId; // may be null if existed already
            header('Location: checkout.php?buynow=1');
            exit;
        } else {
            header('Location: cart.php');
            exit;
        }
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = 'Failed to add to cart: ' . $e->getMessage();
        header('Location: cart.php');
        exit;
    }
}

header('Location: products.php');
