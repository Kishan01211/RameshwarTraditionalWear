<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];
$buyNow = isset($_GET['buynow']) && isset($_SESSION['buy_now_id']) ? (int)$_SESSION['buy_now_id'] : 0;

// Load user profile (for contact/delivery section)
$uStmt = $pdo->prepare('SELECT name, email, phone, address FROM users WHERE id = ?');
$uStmt->execute([$uid]);
$user = $uStmt->fetch() ?: ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];

// Load items to checkout
$items = [];
if ($buyNow > 0) {
    $stmt = $pdo->prepare('SELECT c.*, p.product_name, p.image_url, p.size AS size_options, p.color AS color_options FROM cart_items c JOIN products p ON p.id = c.product_id WHERE c.id = ? AND c.user_id = ?');
    $stmt->execute([$buyNow, $uid]);
    $row = $stmt->fetch();
    if ($row) $items[] = $row;
} else {
    $stmt = $pdo->prepare('SELECT c.*, p.product_name, p.image_url, p.size AS size_options, p.color AS color_options FROM cart_items c JOIN products p ON p.id = c.product_id WHERE c.user_id = ?');
    $stmt->execute([$uid]);
    $items = $stmt->fetchAll();
}

if (!$items) {
    header('Location: cart.php');
    exit;
}

// Helper to resolve first image with safe fallback (same as cart)
function resolve_image($image_csv) {
    $placeholder = '/rtwrs_web/assets/images/placeholder.jpg';
    if (!$image_csv) return $placeholder;
    $parts = array_map('trim', explode(',', $image_csv));
    $first = $parts[0] ?? '';
    if ($first === '') return $placeholder;
    $img = ltrim($first, '.');
    if (strpos($img, '/rtwrs_web/') !== 0) {
        $img = '/rtwrs_web/' . ltrim($img, '/');
    }
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $img;
    if (is_file($filePath)) return $img;
    return $placeholder;
}

// Compute totals and resolve images
$total = 0.0;
foreach ($items as &$it) {
    $it['subtotal'] = (float)$it['price_per_day'] * (int)$it['rental_days'] * (int)$it['quantity'];
    $it['img'] = resolve_image($it['image_url']);
    $total += $it['subtotal'];
}
unset($it);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // 1) Update user's contact/delivery info
    $name  = trim((string)($_POST['name'] ?? $user['name']));
    $email = trim((string)($_POST['email'] ?? $user['email']));
    $phone = trim((string)($_POST['phone'] ?? $user['phone']));
    $addr1 = trim((string)($_POST['address'] ?? ''));
    $fullAddress = $addr1;
    try {
        $up = $pdo->prepare('UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?');
        $up->execute([$name ?: $user['name'], $email ?: $user['email'], $phone, $fullAddress ?: $user['address'], $uid]);
    } catch (Throwable $e) {
        // ignore profile update failures for order flow
    }

    // 2) Persist any rental detail edits per item back to cart_items
    if (!empty($_POST['items']) && is_array($_POST['items'])) {
        $upd = $pdo->prepare('UPDATE cart_items SET selected_size = ?, selected_color = ?, start_date = ?, end_date = ?, rental_days = ?, quantity = ? WHERE id = ? AND user_id = ?');
        foreach ($_POST['items'] as $cid => $data) {
            $cid = (int)$cid;
            $sz = isset($data['selected_size']) ? trim((string)$data['selected_size']) : null;
            $cl = isset($data['selected_color']) ? trim((string)$data['selected_color']) : null;
            $sd = isset($data['start_date']) ? trim((string)$data['start_date']) : '';
            $ed = isset($data['end_date']) ? trim((string)$data['end_date']) : '';
            $qty = max(1, (int)($data['quantity'] ?? 1));
            if ($sd && $ed) {
                $days = (int)floor((strtotime($ed) - strtotime($sd)) / 86400) + 1;
                if ($days < 1) $days = 1;
                try { $upd->execute([$sz, $cl, $sd, $ed, $days, $qty, $cid, $uid]); } catch (Throwable $e) { /* ignore invalid rows */ }
            }
        }

        // Reload items after updates
        if ($buyNow > 0) {
            $stmt = $pdo->prepare('SELECT c.*, p.product_name, p.image_url, p.size AS size_options, p.color AS color_options FROM cart_items c JOIN products p ON p.id = c.product_id WHERE c.id = ? AND c.user_id = ?');
            $stmt->execute([$buyNow, $uid]);
            $items = [];
            $row = $stmt->fetch();
            if ($row) $items[] = $row;
        } else {
            $stmt = $pdo->prepare('SELECT c.*, p.product_name, p.image_url, p.size AS size_options, p.color AS color_options FROM cart_items c JOIN products p ON p.id = c.product_id WHERE c.user_id = ?');
            $stmt->execute([$uid]);
            $items = $stmt->fetchAll();
        }
    }
    $payment_method = isset($_POST['payment_method']) && in_array($_POST['payment_method'], ['COD','UPI']) ? $_POST['payment_method'] : 'COD';
    $upi_id = null;
    if ($payment_method === 'UPI') {
        $upi_id = trim((string)($_POST['upi_id'] ?? ''));
        if ($upi_id === '') {
            // Basic validation: require UPI ID when UPI selected
            header('Location: checkout.php?error=upi_required');
            exit;
        }
    }
    $pdo->beginTransaction();
    try {
        $bookingIds = [];
        $ins = $pdo->prepare('INSERT INTO bookings (product_id, user_id, start_date, end_date, total_price, selected_size, selected_color, payment_method, upi_id, status) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $del = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND user_id = ?');

        foreach ($items as $row) {
            $totalPrice = (float)$row['price_per_day'] * (int)$row['rental_days'] * (int)$row['quantity'];
            $status = ($payment_method === 'UPI') ? 'completed' : 'pending';
            $ins->execute([
                (int)$row['product_id'],
                $uid,
                $row['start_date'],
                $row['end_date'],
                $totalPrice,
                $row['selected_size'] ?? null,
                $row['selected_color'] ?? null,
                $payment_method,
                $upi_id,
                $status
            ]);
            $bookingIds[] = (int)$pdo->lastInsertId();
            $del->execute([(int)$row['id'], $uid]);
        }

        $pdo->commit();
        
        // Send admin notification for each booking
        foreach ($bookingIds as $bookingId) {
            $productName = $items[0]['product_name'];
            $orderDate = date('Y-m-d H:i:s');
            $totalPrice = (float)$items[0]['price_per_day'] * (int)$items[0]['rental_days'] * (int)$items[0]['quantity'];
            
            // Prepare notification message
            $title = "New Order #$bookingId";
            $message = "New order placed by " . htmlspecialchars($user['name']) . " for $productName. Total Amount: ₹$totalPrice";
            
            // Insert notification into database
            $stmt = $pdo->prepare("INSERT INTO admin_notifications (type, title, message, is_read) VALUES (?, ?, ?, 0)");
            $type = 'booking';
            $stmt->execute([$type, $title, $message]);
            
            // Optional: Send email to admin
            /*
            $to = 'admin@rtwrs.com';
            $subject = "New Order #$bookingId - $productName";
            $emailMessage = "A new order has been placed:\n\n";
            $emailMessage .= "Order ID: #$bookingId\n";
            $emailMessage .= "Customer: " . htmlspecialchars($user['name']) . "\n";
            $emailMessage .= "Product: $productName\n";
            $emailMessage .= "Total Amount: ₹$totalPrice\n";
            $emailMessage .= "Order Date: $orderDate\n\n";
            $emailMessage .= "Please log in to the admin panel to view and process this order.";
            
            $headers = "From: RTWRS Notifications <noreply@rtwrs.com>\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            mail($to, $subject, $emailMessage, $headers);
            */
        }
        
        // Clear buy now flag
        if ($buyNow > 0) unset($_SESSION['buy_now_id']);

        header('Location: my-bookings.php?success=1');
        exit;
    } catch (Throwable $e) {
        $pdo->rollBack();
        header('Location: cart.php?error=checkout_failed');
        exit;
    }
}

// Page-specific CSS
$GLOBALS['extra_css'] = $GLOBALS['extra_css'] ?? [];
$GLOBALS['extra_css'][] = '../assets/css/checkout.css';

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4 checkout-wrap">
  <h2 class="mb-3">Checkout</h2>

  <div class="checkout-grid">
    <!-- Left: Payment & details -->
    <div>
      <form id="checkoutForm" method="post" class="checkout-card mb-3">
        <input type="hidden" name="confirm" value="1">

        <div class="checkout-title">Contact</div>
        <div class="row g-2 mb-2">
          <div class="col-12">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
          </div>
        </div>

        <div class="checkout-title mt-2">Delivery</div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Full name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Address, Apartment, etc." required>
          </div>
        </div>
      </form>

      <div class="checkout-card mb-3">
        <div class="checkout-title">Payment Method</div>
        <div class="checkout-subtitle">Choose how you’d like to pay</div>

        <div class="pm-option">
          <label class="d-flex align-items-center">
            <input type="radio" name="payment_method" id="pm_cod" value="COD" form="checkoutForm" checked>
            <span>Cash on Delivery (COD)</span>
          </label>
        </div>
        <div class="pm-option">
          <label class="d-flex align-items-center">
            <input type="radio" name="payment_method" id="pm_upi" value="UPI" form="checkoutForm">
            <span>UPI Payment</span>
          </label>
          <div id="upiSection" class="mt-2" style="display:none;">
            <label for="upi_id" class="form-label">UPI ID</label>
            <input type="text" class="form-control" name="upi_id" id="upi_id" placeholder="yourname@bank" form="checkoutForm">
          </div>
        </div>
      </div>

      <!-- Rental details editor per item -->
      <form id="rentalDetailsForm" method="post" class="checkout-card">
        <input type="hidden" name="confirm" value="1">
        <div class="checkout-title">Rental Details</div>
        <div class="checkout-subtitle">Update dates, size and color for each item</div>
        <?php foreach ($items as $it): $cid = (int)$it['id']; ?>
          <div class="border rounded p-3 mb-3">
            <div class="fw-semibold mb-2"><?= htmlspecialchars($it['product_name']) ?></div>
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="items[<?= $cid ?>][start_date]" value="<?= htmlspecialchars($it['start_date']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="items[<?= $cid ?>][end_date]" value="<?= htmlspecialchars($it['end_date']) ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Size</label>
                <select class="form-select" name="items[<?= $cid ?>][selected_size]" required>
                  <option value="">Choose size...</option>
                  <?php foreach (explode(',', (string)$it['size_options']) as $sz): $sz = trim($sz); if ($sz==='') continue; ?>
                    <option value="<?= htmlspecialchars($sz) ?>" <?= (isset($it['selected_size']) && trim((string)$it['selected_size']) === $sz) ? 'selected' : '' ?>><?= htmlspecialchars($sz) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Color</label>
                <select class="form-select" name="items[<?= $cid ?>][selected_color]" required>
                  <option value="">Choose color...</option>
                  <?php foreach (explode(',', (string)$it['color_options']) as $cl): $cl = trim($cl); if ($cl==='') continue; ?>
                    <option value="<?= htmlspecialchars($cl) ?>" <?= (isset($it['selected_color']) && trim((string)$it['selected_color']) === $cl) ? 'selected' : '' ?>><?= htmlspecialchars($cl) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Quantity</label>
                <input type="number" min="1" class="form-control" name="items[<?= $cid ?>][quantity]" value="<?= (int)$it['quantity'] ?>">
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="d-grid gap-2">
          <button class="btn btn-primary btn-lg">Confirm & Place Order</button>
          <a href="cart.php" class="btn btn-outline-secondary">Back to Cart</a>
        </div>
      </form>
    </div>

    <!-- Right: Summary -->
    <aside class="checkout-card summary-card">
      <div class="summary-header">Order Summary</div>
      <div class="summary-items">
        <?php foreach ($items as $it): ?>
          <div class="summary-item">
            <div class="summary-thumb">
              <img src="<?= htmlspecialchars($it['img']) ?>" alt="<?= htmlspecialchars($it['product_name']) ?>">
            </div>
            <div>
              <div class="summary-name"><?= htmlspecialchars($it['product_name']) ?></div>
              <div class="summary-meta">
                <?= htmlspecialchars($it['selected_size'] ?? '') ?> <?= isset($it['selected_color']) && $it['selected_color'] !== '' ? ' / ' . htmlspecialchars($it['selected_color']) : '' ?><br>
                <?= htmlspecialchars($it['start_date']) ?> – <?= htmlspecialchars($it['end_date']) ?>
              </div>
              <div class="summary-meta">Qty: <?= (int)$it['quantity'] ?> · Days: <?= (int)$it['rental_days'] ?> · ₹<?= number_format((float)$it['price_per_day'], 2) ?>/day</div>
            </div>
            <div class="ms-auto fw-semibold">₹<?= number_format((float)$it['subtotal'], 2) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="totals">
        <div class="totals-row"><span>Subtotal</span><span>₹<?= number_format($total, 2) ?></span></div>
        <div class="taxes-note">Tax included. Shipping calculated at checkout.</div>
        <div class="totals-row total mt-2"><span>Total</span><span>₹<?= number_format($total, 2) ?></span></div>
      </div>
    </aside>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
// Toggle UPI field based on selection
function toggleUPI(){
  const upi = document.getElementById('pm_upi').checked;
  document.getElementById('upiSection').style.display = upi ? 'block' : 'none';
}
document.getElementById('pm_cod').addEventListener('change', toggleUPI);
document.getElementById('pm_upi').addEventListener('change', toggleUPI);
toggleUPI();
</script>
