<?php
// Start session early to allow header redirects before any HTML output
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once '../config/db.php';

// User must be logged in BEFORE outputting header HTML
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Safe to output the site header now
require_once '../includes/header.php';

// Fetch user bookings with product info
$stmt = $pdo->prepare("SELECT b.*, p.product_name, p.image_url
                      FROM bookings b
                      JOIN products p ON b.product_id = p.id
                      WHERE b.user_id = ?
                      ORDER BY b.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<div class="container my-5">
    <h2 class="mb-4">My Bookings</h2>

    <?php if (empty($bookings)): ?>
        <div class="card p-4 mb-3 shadow-sm">
            <div class="row">
                <div class="col-md-3 d-flex align-items-center justify-content-center">
                    <div style="width:160px;height:180px;background:#f4f4f4;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:2.2rem;color:#ccc;">No Image</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4 class="mb-2">Product Name</h4>
                    <div><strong>Booking ID:</strong> <span class="text-muted">#xxxxxx</span></div>
                    <div><strong>Rental Dates:</strong> <span class="text-muted">Start — End</span></div>
                    <div><strong>Size:</strong> <span class="text-muted">—</span></div>
                    <div><strong>Color:</strong> <span class="text-muted">—</span></div>
                    <div><strong>Status:</strong> <span class="badge bg-secondary">Pending</span></div>
                </div>
                <div class="col-md-3 d-flex flex-column align-items-end justify-content-center">
                    <div>
                        <strong>Total Paid:</strong>
                        <div class="h4 mb-3">₹0.00</div>
                    </div>
                    <a href="invoice-download.php?ids=<?= $booking['id'] ?>" class="btn btn-info">Download Invoice</a>
                </div>
            </div>
        </div>
        <div class="text-center p-5 bg-light mt-5 rounded">
            <h5 class="mt-2 mb-1" style="color:#bbb;">No bookings yet</h5>
            <p style="color:#888;">
                You haven't made any bookings yet.<br>
                <a href="products.php" class="btn btn-outline-primary mt-2">Browse Collection</a>
            </p>
        </div>
    <?php else: ?>
        <?php foreach ($bookings as $booking): ?>
            <?php
                // Get first product image (if present)
                $images = explode(',', $booking['image_url']);
                $imagePath = '';
                if (!empty($images[0])) {
                    $img = ltrim(trim($images[0]), '.');
                    if (strpos($img, '/rtwrs_web/') !== 0) {
                        $img = '/rtwrs_web/' . ltrim($img, '/');
                    }
                    // Verify file actually exists on server, else fallback
                    $filePath = $_SERVER['DOCUMENT_ROOT'] . $img;
                    $imagePath = (file_exists($filePath)) ? $img : '';
                }
                // Badge color logic
                $badgeColors = [
                    'pending'   => 'secondary',
                    'confirmed' => 'success',
                    'completed' => 'info',
                    'cancelled' => 'danger'
                ];
                $statusColor = $badgeColors[strtolower($booking['status'])] ?? 'secondary';
            ?>
            <div class="card p-4 mb-3 shadow-sm">
                <div class="row">
                    <div class="col-md-3 d-flex align-items-center justify-content-center">
                        <?php if ($imagePath): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($booking['product_name']) ?>" class="img-fluid" style="width:100%; height:auto; max-height:200px; object-fit:contain; border-radius:12px; border:1px solid #ddd;">
                        <?php else: ?>
                            <div style="width:160px;height:180px;background:#f4f4f4;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                                <span style="font-size:2.2rem;color:#ccc;">No Image</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h4 class="mb-2"><?= htmlspecialchars($booking['product_name']) ?></h4>
                        <div><strong>Booking ID:</strong> <span class="text-muted">#<?= $booking['id'] ?></span></div>
                        <div><strong>Rental Dates:</strong> <span class="text-muted"><?= date('M d, Y', strtotime($booking['start_date'])) ?> — <?= date('M d, Y', strtotime($booking['end_date'])) ?></span></div>
                        <div><strong>Size:</strong> <span class="text-muted"><?= htmlspecialchars($booking['selected_size']) ?: '—' ?></span></div>
                        <div><strong>Color:</strong> <span class="text-muted"><?= htmlspecialchars($booking['selected_color']) ?: '—' ?></span></div>
                        <div><strong>Status:</strong> 
                            <span class="badge bg-<?= $statusColor ?>"><?= ucfirst($booking['status']) ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex flex-column align-items-end justify-content-center">
                        <div>
                            <strong>Total Paid:</strong>
                            <div class="h4 mb-3">₹<?= number_format($booking['total_price'], 2) ?></div>
                        </div>
                        <a href="invoice-download.php?ids=<?= $booking['id'] ?>" class="btn btn-info mb-2" target="_blank">Download Invoice</a>
                        <?php if ($booking['status'] === 'completed'): ?>
                            <a href="feedback.php?booking_id=<?= $booking['id'] ?>" class="btn btn-primary btn-sm">Leave Feedback</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
