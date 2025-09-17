<?php include "includes/admin-header.php"; include "../config/db.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tags me-2"></i>Product Categories</h2>
    <a href="categories-add.php" class="btn btn-success">
        <i class="fas fa-plus me-1"></i>Add Category
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="admin-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Products Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-light">
                <?php
                try {
                    $sql = "SELECT c.*, COUNT(p.id) as product_count 
                           FROM categories c 
                           LEFT JOIN products p ON c.id = p.category_id 
                           GROUP BY c.id 
                           ORDER BY c.name ASC";
                    $stmt = $pdo->query($sql);
                    $categories = $stmt->fetchAll();
                    
                    if (empty($categories)) {
                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>";
                        echo "<i class='fas fa-tags fa-2x mb-2'></i><br>";
                        echo "No categories found. <a href='categories-add.php'>Add your first category</a>.";
                        echo "</td></tr>";
                    } else {
                        foreach($categories as $cat) {
                            echo "<tr>";
                            echo "<td data-label='ID'><span class='badge bg-secondary'>#{$cat['id']}</span></td>";
                            echo "<td data-label='Category Name'><strong>" . htmlspecialchars($cat['name']) . "</strong></td>";
                            echo "<td data-label='Description'>" . (empty($cat['description']) ? '<em class="text-muted">No description</em>' : htmlspecialchars($cat['description'])) . "</td>";
                            echo "<td data-label='Products Count'><span class='badge bg-info'>{$cat['product_count']} products</span></td>";
                            echo "<td data-label='Actions'>";
                            echo "<div class='btn-group btn-group-sm'>";
                            echo "<a href='categories-edit.php?id={$cat['id']}' class='btn btn-outline-primary' title='Edit Category'>";
                            echo "<i class='fas fa-edit'></i>";
                            echo "</a>";
                            echo "<a href='categories-delete.php?id={$cat['id']}' class='btn btn-outline-danger' onclick='return confirm(\"Are you sure you want to delete category: " . htmlspecialchars($cat['name']) . "? This category has {$cat['product_count']} product(s).\")' title='Delete Category'>";
                            echo "<i class='fas fa-trash'></i>";
                            echo "</a>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='5' class='text-center py-4 text-danger'>";
                    echo "<i class='fas fa-exclamation-triangle fa-2x mb-2'></i><br>";
                    echo "Error loading categories: " . htmlspecialchars($e->getMessage());
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
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
                <div id="productWarning" class="alert alert-warning" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This category has <span id="productCount"></span> associated with it. 
                    Deleting this category may affect those products.
                </div>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteCategoryBtn" class="btn btn-danger">Delete Category</a>
            </div>
        </div>
    </div>
</div>



<?php include "includes/admin-footer.php"; ?>
