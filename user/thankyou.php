<?php
require_once '../includes/header.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
// Optionally: Get the latest order ID or other info from session or GET param
$orderId = $_GET['order_id'] ?? '';
$userName = $_SESSION['user']['name'] ?? 'Customer';
?>
<div class="container d-flex align-items-center justify-content-center" style="min-height:80vh;">
  <div class="card text-center shadow p-4" style="max-width:420px;margin:auto;">
    <img src="../assets/images/sapha.jpg" alt="Order Confirmed"
         style="width:120px;margin:0 auto 16px;display:block;">
    <h2 class="mb-3" style="color:#28a745;">Thank You<?= ', ' . htmlspecialchars($userName) ?></h2>
    <h5>Your Order is Confirmed!</h5>
    <p class="text-muted mb-4">
      We have received your order<?php if($orderId) echo " <b>#$orderId</b>" ?>.<br>
      Our team will contact you soon.
    </p>
    <a href="my-bookings.php" class="btn btn-primary w-100 mb-2">View My Bookings</a>
    <a href="products.php" class="btn btn-outline-secondary w-100">Continue Shopping</a>
  </div>
</div>
<script>
  // Optional: auto-redirect back to products or bookings after X seconds
  setTimeout(() => { window.location.href = "my-bookings.php"; }, 15000);
</script>
<?php require_once '../includes/footer.php'; ?>
