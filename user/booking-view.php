<?php
require_once '../includes/header.php';
require_once '../config/db.php';

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$booking_id = $_GET['id'] ?? null;
if (!$booking_id) {
    header('Location: my-bookings.php');
    exit;
}

// Fetch booking details for this user
$stmt = $pdo->prepare("SELECT b.*, p.product_name, p.image_url, p.price_per_day, p.size, p.color, p.description FROM bookings b JOIN products p ON b.product_id = p.id WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    echo '<div class="container my-5"><div class="alert alert-danger">Booking not found or access denied.</div></div>';
    require_once '../includes/footer.php';
    exit;
}

// Normalize images
// DEBUG: Show raw image_url from DB
// echo '<pre>DB image_url: ' . htmlspecialchars($booking['image_url']) . '</pre>';
$images = array_map(function($img) {
    $img = ltrim(trim($img), '.');
    if (strpos($img, '/rtwrs_web/') !== 0) {
        $img = '/rtwrs_web/' . ltrim($img, '/');
    }
    return $img;
}, explode(',', $booking['image_url']));
?>
<div class="container my-5">
  <a href="my-bookings.php" class="btn btn-outline-secondary mb-4">&larr; Back to My Bookings</a>
  <div class="card shadow-lg p-4">
    <div class="row">
      <div class="col-md-5 text-center">
        <?php foreach ($images as $img): ?>
          <img src="<?= htmlspecialchars($img) ?>" alt="Product Image" class="img-fluid mb-2" style="width:100%; height:auto; max-height:300px; object-fit:contain; border-radius:10px; border:1px solid #ddd;">
        <?php endforeach; ?>
      </div>
      <div class="col-md-7">
        <h3><?= htmlspecialchars($booking['product_name']) ?></h3>
        <hr>
        <dl class="row mb-1">
          <dt class="col-sm-5">Booking ID:</dt>
          <dd class="col-sm-7">#<?= $booking['id'] ?></dd>

          <dt class="col-sm-5">Rental Dates:</dt>
          <dd class="col-sm-7"><?= date('M d, Y', strtotime($booking['start_date'])) ?> — <?= date('M d, Y', strtotime($booking['end_date'])) ?></dd>

          <dt class="col-sm-5">Size:</dt>
          <dd class="col-sm-7"><?= htmlspecialchars($booking['selected_size']) ?></dd>

          <dt class="col-sm-5">Color:</dt>
          <dd class="col-sm-7"><?= htmlspecialchars($booking['selected_color']) ?></dd>

          <dt class="col-sm-5">Status:</dt>
          <dd class="col-sm-7"><span class="badge bg-<?php
            if ($booking['status'] === 'confirmed') echo 'success';
            else if ($booking['status'] === 'pending') echo 'warning';
            else if ($booking['status'] === 'completed') echo 'info';
            else echo 'danger';
          ?>"><?= ucfirst($booking['status']) ?></span></dd>

          <dt class="col-sm-5">Total Paid:</dt>
          <dd class="col-sm-7">₹<?= number_format($booking['total_price'], 2) ?></dd>

          <dt class="col-sm-5">Payment Method:</dt>
          <dd class="col-sm-7"><?= htmlspecialchars($booking['payment_method']) ?></dd>
        </dl>
        <div class="mb-3"><strong>Description:</strong><br><?= nl2br(htmlspecialchars($booking['description'])) ?></div>
        <div class="mb-2">
          <a href="invoice-download.php?ids=<?= $booking['id'] ?>" class="btn btn-info me-2" target="_blank">Download Invoice</a>
          <?php if ($booking['status'] === 'completed'): ?>
            <a href="feedback.php?booking_id=<?= $booking['id'] ?>" class="btn btn-primary">Leave Feedback</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
