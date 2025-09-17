<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];

// Fetch active cart items for the user
$stmt = $pdo->prepare("SELECT c.*, p.product_name, p.image_url FROM cart_items c JOIN products p ON p.id = c.product_id WHERE c.user_id = ?");
$stmt->execute([$uid]);
$items = $stmt->fetchAll();

// Helper to resolve first image with safe fallback
function resolve_image($image_csv) {
    $placeholder = '/rtwrs_web/assets/images/placeholder.jpg';
    if (!$image_csv) return $placeholder;
    $parts = array_map('trim', explode(',', $image_csv));
    $first = $parts[0] ?? '';
    if ($first === '') return $placeholder;
    // Normalize like in rent.php
    $img = ltrim($first, '.');
    if (strpos($img, '/rtwrs_web/') !== 0) {
        $img = '/rtwrs_web/' . ltrim($img, '/');
    }
    $filePath = $_SERVER['DOCUMENT_ROOT'] . $img;
    if (is_file($filePath)) return $img;
    return $placeholder;
}

$total = 0.0;
foreach ($items as &$row) {
    $row['subtotal'] = (float)$row['price_per_day'] * (int)$row['rental_days'] * (int)$row['quantity'];
    $row['img'] = resolve_image($row['image_url']);
    $total += $row['subtotal'];
}
unset($row);

// Page-specific CSS
$GLOBALS['extra_css'] = $GLOBALS['extra_css'] ?? [];
$GLOBALS['extra_css'][] = '../assets/css/cart.css';

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container cart-page py-4">
  <h2 class="mb-3">Your Cart</h2>
  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); endif; ?>
  <?php if (!empty($_SESSION['flash_info'])): ?>
    <div class="alert alert-info"><?= htmlspecialchars($_SESSION['flash_info']) ?></div>
    <?php unset($_SESSION['flash_info']); endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); endif; ?>
  <?php if (!$items): ?>
    <div class="alert alert-warning">Cart is empty.</div>
  <?php else: ?>
    <div class="table-responsive cart-table">
      <table class="table align-middle">
        <thead><tr><th>Item</th><th>Options</th><th>Dates</th><th>Days</th><th>Qty</th><th>Price/day</th><th>Subtotal</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td>
                <div class="d-flex align-items-start gap-3">
                  <div class="cart-thumb">
                    <img class="cart-image" src="<?= htmlspecialchars($it['img']) ?>" alt="<?= htmlspecialchars($it['product_name']) ?>">
                  </div>
                  <div>
                    <div class="cart-item-name"><?= htmlspecialchars($it['product_name']) ?></div>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($it['selected_size'] ?? '') ?> / <?= htmlspecialchars($it['selected_color'] ?? '') ?></td>
              <td><?= htmlspecialchars($it['start_date']) ?> – <?= htmlspecialchars($it['end_date']) ?></td>
              <td><?= (int)$it['rental_days'] ?></td>
              <td><?= (int)$it['quantity'] ?></td>
              <td>₹<?= number_format((float)$it['price_per_day'], 2) ?></td>
              <td class="fw-bold">₹<?= number_format((float)$it['subtotal'], 2) ?></td>
              <td><a class="btn btn-sm btn-outline-danger" href="cart-remove.php?id=<?= (int)$it['id'] ?>">Remove</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><th colspan="6" class="text-end">Total</th><th class="fw-bold">₹<?= number_format($total, 2) ?></th><th></th></tr>
        </tfoot>
      </table>
    </div>
    <form method="post" action="checkout.php">
      <button class="btn btn-primary">Proceed to Checkout</button>
    </form>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
