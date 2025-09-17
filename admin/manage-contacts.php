<?php 
include "includes/admin-header.php"; 
include "../config/db.php"; 

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contactus WHERE id = ?");
        $result = $stmt->execute([$deleteId]);
        if ($result) {
            $success_message = "Contact inquiry deleted successfully.";
        } else {
            $error_message = "Failed to delete contact inquiry.";
        }
    } catch (PDOException $e) {
        $error_message = "Error deleting contact inquiry: " . $e->getMessage();
    }
    // Redirect to avoid resubmission on refresh
    header("Location: manage-contacts.php" . ($success_message ? "?success=1" : "?error=1"));
    exit;
}

// Check for success/error messages from redirect
if (isset($_GET['success'])) {
    $success_message = "Contact inquiry deleted successfully.";
}
if (isset($_GET['error'])) {
    $error_message = "Failed to delete contact inquiry.";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-envelope me-2"></i>Contact Us Inquiries</h2>
</div>

<!-- Success/Error Messages -->
<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="admin-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // Get all contact inquiries ordered by newest first
                        $stmt = $pdo->query("SELECT * FROM contactus ORDER BY created_at DESC");
                        $contacts = $stmt->fetchAll();
                        
                        if (empty($contacts)) {
                            echo "<tr><td colspan='7' class='text-center py-4 text-muted'>
                                    <i class='fas fa-inbox fa-2x mb-2'></i><br>
                                    No contact inquiries found.
                                  </td></tr>";
                        } else {
                            foreach ($contacts as $contact) {
                                $truncatedMessage = strlen($contact['message']) > 100 
                                    ? substr($contact['message'], 0, 100) . '...' 
                                    : $contact['message'];
                                
                                echo "<tr>";
                                echo "<td data-label='ID'><span class='badge bg-secondary'>" . htmlspecialchars($contact['id']) . "</span></td>";
                                echo "<td data-label='Name'><strong>" . htmlspecialchars($contact['name']) . "</strong></td>";
                                echo "<td data-label='Email'><a href='mailto:" . htmlspecialchars($contact['email']) . "' class='text-decoration-none'>" 
                                     . htmlspecialchars($contact['email']) . "</a></td>";
                                echo "<td data-label='Phone'>" . (empty($contact['phone']) ? '<span class="text-muted">N/A</span>' : htmlspecialchars($contact['phone'])) . "</td>";
                                echo "<td data-label='Message'><span title='" . htmlspecialchars($contact['message']) . "'>" 
                                     . htmlspecialchars($truncatedMessage) . "</span></td>";
                                echo "<td data-label='Date'><small class='text-muted'>" . date('M j, Y g:i A', strtotime($contact['created_at'])) . "</small></td>";
                                echo "<td data-label='Actions'>";
                                echo "<div class='btn-group btn-group-sm'>";
                                echo "<a href='manage-contacts.php?delete=" . (int)$contact['id'] . "' class='btn btn-outline-danger' onclick='return confirm(\"Are you sure you want to delete contact from: " . htmlspecialchars($contact['name']) . "?\")' title='Delete'>";
                                echo "<i class='fas fa-trash'></i>";
                                echo "</a>";
                                echo "</div>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='7' class='text-center py-4 text-danger'>
                                <i class='fas fa-exclamation-triangle fa-2x mb-2'></i><br>
                                Error loading contact inquiries: " . htmlspecialchars($e->getMessage()) . "
                              </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
    </div>
</div>

<!-- Message View Modal -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Full Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="messageContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this contact inquiry?</p>
                <p><strong>From:</strong> <span id="deleteName"></span></p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>



<?php include "includes/admin-footer.php"; ?>
