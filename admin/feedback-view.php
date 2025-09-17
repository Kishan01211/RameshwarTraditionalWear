<?php
// admin/feedback-view.php - returns HTML snippet for feedback details modal
require_once '../config/db.php';

header('Content-Type: text/html; charset=utf-8');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo '<div class="text-danger">Invalid feedback ID.</div>';
    exit;
}

try {
    $sql = "SELECT f.*, u.name AS uname, u.email AS uemail, p.product_name, p.image_url
            FROM feedback f
            JOIN users u ON f.user_id = u.id
            JOIN products p ON f.product_id = p.id
            WHERE f.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $f = $stmt->fetch();

    if (!$f) {
        echo '<div class="text-danger">Feedback not found.</div>';
        exit;
    }

    // Build stars
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= ($i <= (int)$f['rating']) ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-muted"></i>';
    }

    // Product thumbnail (first image if available)
    $thumb = '/rtwrs_web/assets/images/placeholder.jpg';
    if (!empty($f['image_url'])) {
        $imgs = array_filter(array_map('trim', explode(',', $f['image_url'])));
        if (!empty($imgs)) {
            $img = ltrim($imgs[0], '.');
            if (strpos($img, '/rtwrs_web/') !== 0) $img = '/rtwrs_web/' . ltrim($img, '/');
            $thumb = $img;
        }
    }

    // Attached feedback image (optional)
    $attached = !empty($f['image']) ? htmlspecialchars($f['image']) : '';
?>
<div class="row g-3">
  <div class="col-md-3">
    <img src="<?= htmlspecialchars($thumb) ?>" alt="Product" class="img-fluid rounded border" />
  </div>
  <div class="col-md-9">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <div class="text-muted small">Product</div>
        <div class="fw-semibold mb-1"><?= htmlspecialchars($f['product_name']) ?></div>
        <div class="text-muted small">User</div>
        <div class="mb-2">
          <i class="fas fa-user me-1"></i>
          <?= htmlspecialchars($f['uname']) ?>
          <?php if (!empty($f['uemail'])): ?>
            <a href="mailto:<?= htmlspecialchars($f['uemail']) ?>" class="ms-2 small">(<?= htmlspecialchars($f['uemail']) ?>)</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="text-end">
        <div class="mb-1">Rating</div>
        <div><?= $stars ?><span class="ms-2 badge bg-info"><?= (int)$f['rating'] ?>/5</span></div>
      </div>
    </div>
    <hr/>
    <div class="mb-2">
      <div class="text-muted small">Feedback</div>
      <div><?= nl2br(htmlspecialchars($f['feedback'])) ?></div>
    </div>
    <?php if ($attached): ?>
      <div class="mt-3">
        <div class="text-muted small">Attached Image</div>
        <a href="<?= $attached ?>" target="_blank">
          <img src="<?= $attached ?>" alt="Attachment" style="max-width:240px; max-height:240px; object-fit:cover; border:1px solid #eee; border-radius:6px;"/>
        </a>
      </div>
    <?php endif; ?>
    <div class="mt-3 text-muted small">
      Submitted: <?= date('M j, Y g:i A', strtotime($f['created_at'])) ?>
      <?php if (!empty($f['booking_id'])): ?>
        • Booking ID: #<?= (int)$f['booking_id'] ?>
      <?php endif; ?>
      • Feedback ID: #<?= (int)$f['id'] ?>
    </div>
  </div>
</div>
<?php
} catch (Exception $e) {
    echo '<div class="text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
