<?php
include "includes/admin-header.php";
include "../config/db.php";



// Handle delete action
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare('DELETE FROM newsletter WHERE id = ?');
        $ok = $stmt->execute([$deleteId]);
        $success_message = $ok ? 'Subscriber deleted successfully.' : 'Failed to delete subscriber.';
    } catch (PDOException $e) {
        $error_message = 'Error deleting subscriber: ' . $e->getMessage();
    }
    header('Location: manage-newsletter.php' . (isset($success_message) && $success_message ? '?success=1' : '?error=1'));
    exit;
}

// Flash messages via query
if (isset($_GET['success'])) {
    $success_message = 'Subscriber deleted successfully.';
}
if (isset($_GET['error'])) {
    $error_message = 'Failed to process the request.';
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-newspaper me-2"></i>Newsletter Subscribers</h2>
    
</div>

<?php if (!empty($success_message)): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <?= htmlspecialchars($success_message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (!empty($error_message)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?= htmlspecialchars($error_message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="admin-table">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Email</th>
          <th>Subscribed On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        try {
            $stmt = $pdo->query('SELECT id, email, created_at FROM newsletter ORDER BY created_at DESC');
            $rows = $stmt->fetchAll();
            if (!$rows) {
                echo "<tr><td colspan='4' class='text-center py-4 text-muted'>\n".
                     "<i class='fas fa-inbox fa-2x mb-2'></i><br>No subscribers found.\n".
                     "</td></tr>";
            } else {
                foreach ($rows as $r) {
                    echo "<tr>";
                    echo "<td><span class='badge bg-secondary'>" . (int)$r['id'] . "</span></td>";
                    echo "<td>" . htmlspecialchars($r['email']) . "</td>";
                    echo "<td><small class='text-muted'>" . date('M j, Y g:i A', strtotime($r['created_at'])) . "</small></td>";
                    echo "<td>\n";
                    echo "  <a href='manage-newsletter.php?delete=" . (int)$r['id'] . "' class='btn btn-outline-danger btn-sm' ";
                    echo "     onclick=\"return confirm('Delete subscriber: " . htmlspecialchars($r['email'], ENT_QUOTES) . " ?');\" title='Delete'>\n";
                    echo "    <i class='fas fa-trash'></i>\n";
                    echo "  </a>\n";
                    echo "</td>";
                    echo "</tr>";
                }
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='4' class='text-center py-4 text-danger'>\n".
                 "<i class='fas fa-exclamation-triangle fa-2x mb-2'></i><br>".
                 "Error loading subscribers: " . htmlspecialchars($e->getMessage()) .
                 "</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<?php include "includes/admin-footer.php"; ?>
