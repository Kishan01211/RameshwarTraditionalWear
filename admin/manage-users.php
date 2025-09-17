<?php include "includes/admin-header.php"; include "../config/db.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Users Management</h2>
</div>

<div class="admin-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                    $users = $stmt->fetchAll();
                    
                    if (empty($users)) {
                        echo "<tr><td colspan='6' class='text-center py-4 text-muted'>";
                        echo "<i class='fas fa-user-slash fa-2x mb-2'></i><br>";
                        echo "No users found.";
                        echo "</td></tr>";
                    } else {
                        foreach($users as $u) {
                            echo "<tr>";
                            echo "<td data-label='ID'><span class='badge bg-secondary'>#{$u['id']}</span></td>";
                            echo "<td data-label='Name'><strong>" . htmlspecialchars($u['name']) . "</strong><br><small class='text-muted'>" . htmlspecialchars($u['user_name']) . "</small></td>";
                            echo "<td data-label='Email'><a href='mailto:" . htmlspecialchars($u['email']) . "' class='text-decoration-none'>" . htmlspecialchars($u['email']) . "</a></td>";
                            echo "<td data-label='Phone'>" . (empty($u['phone']) ? '<span class="text-muted">N/A</span>' : htmlspecialchars($u['phone'])) . "</td>";
                            echo "<td data-label='Joined'><small class='text-muted'>" . date('M j, Y', strtotime($u['created_at'])) . "</small></td>";
                            echo "<td data-label='Actions'>";
                            echo "<div class='btn-group btn-group-sm'>";

                           
                            echo "<a href='users-delete.php?id={$u['id']}' class='btn btn-outline-danger' onclick='return confirm(\"Are you sure you want to delete user: " . htmlspecialchars($u['name']) . "?\")' title='Delete User'>";
                            echo "<i class='fas fa-trash'></i>";
                            echo "</a>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='6' class='text-center py-4 text-danger'>";
                    echo "<i class='fas fa-exclamation-triangle fa-2x mb-2'></i><br>";
                    echo "Error loading users: " . htmlspecialchars($e->getMessage());
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Display success or error messages
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">';
    echo htmlspecialchars($_SESSION['success']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">';
    echo htmlspecialchars($_SESSION['error']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['error']);
}
?>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user?</p>
                <p><strong>User:</strong> <span id="deleteUserName"></span></p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteUserBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>



<?php include "includes/admin-footer.php"; ?>
