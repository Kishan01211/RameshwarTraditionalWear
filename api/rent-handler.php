<?php
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to make a booking']);
    exit;
}

try {
    $product_id = $_POST['product_id'] ?? null;
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $total_price = $_POST['total_price'] ?? null;
    $selected_size = $_POST['selected_size'] ?? null;
    $selected_color = $_POST['selected_color'] ?? null;
    $special_requests = $_POST['special_requests'] ?? null;
    $payment_method = $_POST['payment_method'] ?? null;
    $upi_id = $_POST['upi_id'] ?? null;

    // Validation
    if (!$product_id || !$start_date || !$end_date || !$total_price || !$selected_size || !$selected_color || !$payment_method) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }

    // Validate dates
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $today = new DateTime();

    if ($start < $today) {
        echo json_encode(['success' => false, 'message' => 'Start date cannot be in the past']);
        exit;
    }

    if ($end <= $start) {
        echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
        exit;
    }

    // Check product availability
    $stmt = $pdo->prepare("SELECT quantity_available FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product || $product['quantity_available'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Product is not available']);
        exit;
    }

    // Check for conflicting bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings 
                          WHERE product_id = ? AND status != 'cancelled' 
                          AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?))");
    $stmt->execute([$product_id, $start_date, $start_date, $end_date, $end_date]);
    $conflict = $stmt->fetch();

    if ($conflict['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Product is already booked for selected dates']);
        exit;
    }

    // Create booking
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO bookings (product_id, user_id, start_date, end_date, total_price, 
                          selected_size, selected_color, special_requests, payment_method, upi_id, status, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");

    $stmt->execute([
        $product_id,
        $_SESSION['user_id'],
        $start_date,
        $end_date,
        $total_price,
        $selected_size,
        $selected_color,
        $special_requests,
        $payment_method,
        $upi_id
    ]);

    $booking_id = $pdo->lastInsertId();

    // Update product quantity
    $stmt = $pdo->prepare("UPDATE products SET quantity_available = quantity_available - 1 WHERE id = ?");
    $stmt->execute([$product_id]);

    // Create payment record
    $stmt = $pdo->prepare("INSERT INTO payments (booking_id, amount, payment_status, created_at) 
                          VALUES (?, ?, 'pending', NOW())");
    $stmt->execute([$booking_id, $total_price]);

    $pdo->commit();

    echo json_encode(['success' => true, 'booking_id' => $booking_id, 'message' => 'Booking confirmed successfully']);

} catch (Exception $e) {
    $pdo->rollback();
    echo json_encode(['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()]);
}
?>