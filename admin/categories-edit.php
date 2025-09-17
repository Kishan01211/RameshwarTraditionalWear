<?php include "includes/admin-header.php"; include "../config/db.php";

$id = (int)$_GET['id'];

if (!$id) {
    $_SESSION['error'] = "Invalid category ID.";
    header('Location: manage-categories.php'); 
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch();
    
    if (!$cat) {
        $_SESSION['error'] = "Category not found.";
        header('Location: manage-categories.php'); 
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error loading category: " . $e->getMessage();
    header('Location: manage-categories.php'); 
    exit();
}

if($_SERVER['REQUEST_METHOD']=='POST'){
    try {
        $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id=?")
            ->execute([$_POST['name'], $_POST['description'], $id]);
        $_SESSION['success'] = "Category updated successfully!";
        header('Location: manage-categories.php'); 
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating category: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Edit Category</h2>
    <a href="manage-categories.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Categories
    </a>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="admin-table">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-tag me-2"></i>Category Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= htmlspecialchars($cat['name']) ?>" required 
                           placeholder="Enter category name">
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" 
                              placeholder="Enter category description (optional)"><?= htmlspecialchars($cat['description']) ?></textarea>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Category
                        </button>
                        <a href="manage-categories.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="button" class="btn btn-outline-danger" 
                                onclick="confirmDeleteCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name']) ?>')">
                            <i class="fas fa-trash me-1"></i>Delete Category
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this category?</p>
                <p><strong>Category:</strong> <span id="deleteCategoryName"></span></p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteCategoryBtn" class="btn btn-danger">Delete Category</a>
            </div>
        </div>
    </div>
</div>

<script>
// Function to confirm delete category
function confirmDeleteCategory(categoryId, categoryName) {
    document.getElementById('deleteCategoryName').textContent = categoryName;
    document.getElementById('confirmDeleteCategoryBtn').href = 'categories-delete.php?id=' + categoryId;
    new bootstrap.Modal(document.getElementById('deleteCategoryModal')).show();
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

<?php include "includes/admin-footer.php"; ?>
