<?php include "includes/admin-header.php"; include "../config/db.php";

if($_SERVER['REQUEST_METHOD']=='POST'){
    try {
        $pdo->prepare("INSERT INTO categories (name, description) VALUES (?,?)")
            ->execute([$_POST['name'], $_POST['description']]);
        $_SESSION['success'] = "Category added successfully!";
        header('Location: manage-categories.php'); 
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding category: " . $e->getMessage();
    }
}

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Add New Category</h2>
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
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required 
                           placeholder="Enter category name">
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" 
                              placeholder="Enter category description (optional)"></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success me-2">
                        <i class="fas fa-save me-1"></i>Add Category
                    </button>
                    <a href="manage-categories.php" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "includes/admin-footer.php"; ?>
