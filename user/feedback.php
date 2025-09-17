<?php
// user/feedback.php - User feedback submission page
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=feedback.php');
    exit;
}

require_once '../config/db.php';
require_once '../includes/header.php';

$user_id = (int)$_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : null;

// Fetch product for context (if provided)
$product = null;
if ($product_id > 0) {
    $stmt = $pdo->prepare("SELECT id, product_name, image_url FROM products WHERE id = ?");
    $stmt->execute([$product_id]);  
    $product = $stmt->fetch();
}
// Build product options if no product_id supplied: only products from user's orders (bookings)
$productOptions = [];
if ($product_id <= 0) {
    try {
        $stmt = $pdo->prepare('SELECT DISTINCT p.id, p.product_name
                               FROM bookings b
                               JOIN products p ON p.id = b.product_id
                               WHERE b.user_id = ?
                               ORDER BY p.product_name ASC');
        $stmt->execute([$user_id]);
        while ($row = $stmt->fetch()) {
            $productOptions[] = ['id' => (int)$row['id'], 'name' => $row['product_name']];
        }
    } catch (Throwable $e) {
        // Fallback: do not expose all products; keep empty to enforce "my orders" only
        $productOptions = [];
    }
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $booking_id = isset($_POST['booking_id']) && $_POST['booking_id'] !== '' ? (int)$_POST['booking_id'] : null;
    $rating     = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $feedback   = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

    // Basic validation
    if ($product_id <= 0) $errors[] = 'Product is required.';
    if ($rating < 1 || $rating > 5) $errors[] = 'Rating must be between 1 and 5.';
    if ($feedback === '') $errors[] = 'Feedback text is required.';
    if (strlen($feedback) > 5000) $errors[] = 'Feedback is too long.';

    // Verify product exists
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ?');
        $stmt->execute([$product_id]);
        if (!$stmt->fetch()) $errors[] = 'Selected product does not exist.';
    }

    // If booking_id provided, verify it belongs to the user and matches product
    if (!$errors && $booking_id) {
        $stmt = $pdo->prepare('SELECT id FROM bookings WHERE id = ? AND user_id = ? AND product_id = ?');
        $stmt->execute([$booking_id, $user_id, $product_id]);
        if (!$stmt->fetch()) $errors[] = 'Invalid booking selection.';
    }

    // Prevent duplicate feedback: one feedback per user+product(+booking)
    if (!$errors) {
        if ($booking_id) {
            $stmt = $pdo->prepare('SELECT id FROM feedback WHERE user_id = ? AND product_id = ? AND booking_id = ?');
            $stmt->execute([$user_id, $product_id, $booking_id]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM feedback WHERE user_id = ? AND product_id = ? AND booking_id IS NULL');
            $stmt->execute([$user_id, $product_id]);
        }
        if ($stmt->fetch()) $errors[] = 'You have already submitted feedback for this item.';
    }

    // Handle image upload (optional)
    $imagePath = null;
    if (!$errors && isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/jpg' => 'jpg', 'image/webp' => 'webp'];
        $mime = mime_content_type($_FILES['image']['tmp_name']);
        if (!isset($allowed[$mime])) {
            $errors[] = 'Only JPG, PNG or WEBP images are allowed.';
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image must be under 2MB.';
        } else {
            $ext = $allowed[$mime];
            $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'feedback';
            if (!is_dir($baseDir)) {
                @mkdir($baseDir, 0775, true);
            }
            $filename = 'fb_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = $baseDir . DIRECTORY_SEPARATOR . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                // Public path normalized like other assets: /rtwrs_web/uploads/feedback/...
                $imagePath = '/rtwrs_web/uploads/feedback/' . $filename;
            } else {
                $errors[] = 'Failed to save uploaded image.';
            }
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO feedback (user_id, product_id, booking_id, rating, feedback, image) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$user_id, $product_id, $booking_id, $rating, $feedback, $imagePath]);
        $success = 'Thank you! Your feedback has been submitted.';
        // Refresh product context
        $stmt = $pdo->prepare("SELECT id, product_name, image_url FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
    }
}

// Build product image (first image or placeholder)
$prodImg = '/rtwrs_web/assets/images/placeholder.jpg';
if ($product && !empty($product['image_url'])) {
    $imgs = array_filter(array_map('trim', explode(',', $product['image_url'])));
    if (!empty($imgs)) {
        $img = ltrim($imgs[0], '.');
        if (strpos($img, '/rtwrs_web/') !== 0) $img = '/rtwrs_web/' . ltrim($img, '/');
        $prodImg = $img;
    }
}
?>

<div class="container py-4 animate-fadeInUp">
  <div class="section-header">
    <h2 class="section-title">Share Your Feedback</h2>
    <p class="section-subtitle">Tell us about your experience to help us improve.</p>
  </div>

  <?php if ($product): ?>
    <div class="card mb-4">
      <div class="d-flex align-items-center gap-3">
        <img src="<?= htmlspecialchars($prodImg) ?>" alt="Product" style="width:96px;height:96px;object-fit:cover;border-radius:8px;border:1px solid var(--gray-200);"/>
        <div>
          <div class="text-muted small">Product</div>
          <div class="fw-semibold"><?= htmlspecialchars($product['product_name']) ?></div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php elseif ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="card">
    <?php if ($product_id > 0): ?>
      <input type="hidden" name="product_id" value="<?= (int)$product_id ?>" />
    <?php else: ?>
      <div class="form-group">
        <label class="form-label">Product<span class="text-danger"> *</span></label>
        <select name="product_id" class="form-select" required <?php if (empty($productOptions)) echo 'disabled'; ?>>
          <option value="">-- Select a product from your orders --</option>
          <?php 
            $postedPid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            foreach ($productOptions as $opt): 
              $sel = ($postedPid === $opt['id']) ? 'selected' : '';
          ?>
            <option value="<?= $opt['id'] ?>" <?= $sel ?>><?= htmlspecialchars($opt['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($productOptions)): ?>
          <div class="form-text text-warning">You have no orders yet. Please book an item to leave feedback.</div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <?php if ($booking_id): ?>
      <input type="hidden" name="booking_id" value="<?= (int)$booking_id ?>" />
    <?php endif; ?>

    <div class="form-group">
      <label class="form-label">Rating</label>
      <div class="d-flex gap-2 align-items-center">
        <?php for ($i=1; $i<=5; $i++): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="rating" id="rate<?= $i ?>" value="<?= $i ?>" <?= isset($_POST['rating']) && (int)$_POST['rating']===$i ? 'checked' : '' ?> required>
            <label class="form-check-label" for="rate<?= $i ?>"><?= $i ?></label>
          </div>
        <?php endfor; ?>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Your Feedback</label>
      <textarea name="feedback" class="form-textarea" rows="5" maxlength="5000" placeholder="Share your experience..." required><?= isset($_POST['feedback']) ? htmlspecialchars($_POST['feedback']) : '' ?></textarea>
      <div class="form-text">Maximum 5000 characters.</div>
    </div>

    <div class="form-group">
      <label class="form-label">Attach an image (optional)</label>
      <input type="file" name="image" class="form-input" accept="image/png,image/jpeg,image/webp">
      <div class="form-text">JPG/PNG/WEBP up to 2MB.</div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Submit Feedback</button>
      <?php if ($product_id): ?>
        <a class="btn btn-outline" href="product-detail.php?id=<?= (int)$product_id ?>">Back to Product</a>
      <?php endif; ?>
      <a class="btn btn-outline" href="my-bookings.php">My Bookings</a>
    </div>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>
