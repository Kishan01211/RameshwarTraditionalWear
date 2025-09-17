<?php
include "includes/admin-header.php";
include "../config/db.php";

// Get booking ID from GET parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$id) { echo "<div class='alert alert-danger'>Invalid order ID.</div>"; include "../includes/footer.php"; exit; }

// Fetch booking, user, product info
$stmt = $pdo->prepare(
  "SELECT b.*, u.name as uname, u.email, u.phone, u.address,
          p.product_name, p.image_url, p.size as product_sizes, p.color as product_colors
   FROM bookings b
     JOIN users u ON b.user_id = u.id
     JOIN products p ON b.product_id = p.id
   WHERE b.id = ?");
$stmt->execute([$id]);
$b = $stmt->fetch();

if(!$b) { echo "<div class='alert alert-warning'>Booking not found.</div>"; include "../includes/footer.php"; exit; }

// Prepare product images
$images = [];
if (!empty($b['image_url'])) {
    $imgs = explode(',', $b['image_url']);
    foreach ($imgs as $img) {
        $img = ltrim(trim($img), '.');
        if (strpos($img, '/rtwrs_web/') !== 0) {
            $img = '/rtwrs_web/' . ltrim($img, '/');
        }
        $images[] = $img;
    }
}
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<h2>Booking Details — #<?= $b['id'] ?></h2>
<div class="card p-4 mb-3">
  <div class="row">
    <div class="col-md-4">
      <?php foreach ($images as $img): ?>
  <img src="<?= htmlspecialchars($img) ?>" class="img-fluid" style="width:100%; height:auto; max-height:350px; object-fit:contain; border-radius:10px; margin-bottom:12px; border:1px solid #ddd;" alt="Product Image">
<?php endforeach; ?>
      <div style="margin:10px 0">
        <strong>Product:</strong> <?=htmlspecialchars($b['product_name'])?><br>
        <strong>Size(s):</strong> <?=htmlspecialchars($b['product_sizes'])?><br>
        <strong>Color(s):</strong> <?=htmlspecialchars($b['product_colors'])?>
      </div>
    </div>
    <div class="col-md-8">
      <table class="table table-bordered">
        <tr><th>User</th><td><?=htmlspecialchars($b['uname'])?> — <?=htmlspecialchars($b['email'])?> — <?=htmlspecialchars($b['phone'])?></td></tr>
        <tr><th>Address</th><td><?=htmlspecialchars($b['address'])?></td></tr>
        <tr><th>Booking Dates</th><td><?=htmlspecialchars($b['start_date'])?> to <?=htmlspecialchars($b['end_date'])?></td></tr>
        <tr><th>Selected Size</th><td><?=htmlspecialchars($b['selected_size'])?></td></tr>
        <tr><th>Selected Color</th><td><?=htmlspecialchars($b['selected_color'])?></td></tr>
        <tr><th>Special Requests</th><td><?=htmlspecialchars($b['special_requests'])?></td></tr>
        <tr><th>Payment</th>
          <td>
            Method: <?=htmlspecialchars($b['payment_method'])?>
            <?php if($b['payment_method'] === 'UPI' && $b['upi_id']): ?>
              <br>UPI: <?=htmlspecialchars($b['upi_id'])?>
            <?php endif; ?>
          </td>
        </tr>
        <tr><th>Total Price</th><td><strong>₹<?= number_format($b['total_price'],2) ?></strong></td></tr>
        <tr>
          <th>Status</th>
          <td>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-secondary text-capitalize"><?= htmlspecialchars($b['status']) ?></span>
              <form method="post" action="update-booking-status.php" class="d-flex align-items-center gap-2">
                <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                <input type="hidden" name="redirect" value="order-view.php?id=<?= (int)$b['id'] ?>">
                <?php $allowed = ['pending','confirmed','dispatched','delivered','completed','cancelled']; ?>
                <select name="status" class="form-select form-select-sm" style="width:auto;min-width:160px">
                  <?php foreach ($allowed as $st): ?>
                    <option value="<?= $st ?>" <?= $b['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Update</button>
              </form>
            </div>
          </td>
        </tr>
        <tr><th>Placed On</th><td><?=htmlspecialchars($b['created_at'])?></td></tr>
      </table>
    </div>
  </div>
</div>

<a href="send-bill.php?id=<?= $b['id'] ?>" class="btn btn-secondary">
  <i class="bi bi-download"></i> Download Invoice 
</a>

<?php include "includes/admin-footer.php"; ?>
