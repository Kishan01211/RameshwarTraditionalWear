<?php include "includes/admin-header.php"; include "../config/db.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tshirt me-2"></i>Products Management</h2>
    <a class="btn btn-success" href="products-add.php">
        <i class="fas fa-plus me-2"></i>Add Product
    </a>
</div>

<div class="admin-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Product #</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price/Day</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-light">
                <?php
                try {
                    $sql = "SELECT p.*, c.name as catname FROM products p JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC";
                    $stmt = $pdo->query($sql);
                    $products = $stmt->fetchAll();
                    
                    if (empty($products)) {
                        echo "<tr><td colspan='8' class='text-center py-4 text-muted'>";
                        echo "<i class='fas fa-box-open fa-2x mb-2'></i><br>";
                        echo "No products found.";
                        echo "</td></tr>";
                    } else {
                        foreach($products as $p) {
                            $statusClass = $p['status'] == 'active' ? 'badge-confirmed' : 'badge-cancelled';
                            $stockClass = $p['quantity_available'] <= 2 ? 'text-danger' : ($p['quantity_available'] <= 5 ? 'text-warning' : 'text-success');
                            
                            echo "<tr>";
                            echo "<td data-label='ID'><span class='badge bg-secondary'>#{$p['id']}</span></td>";
                            echo "<td data-label='Product #'><code>" . htmlspecialchars($p['product_number']) . "</code></td>";
                            echo "<td data-label='Name'><strong>" . htmlspecialchars($p['product_name']) . "</strong></td>";
                            echo "<td data-label='Category'><span class='badge bg-info'>" . htmlspecialchars($p['catname']) . "</span></td>";
                            echo "<td data-label='Price/Day'><strong class='text-success'>₹" . number_format($p['price_per_day'], 2) . "</strong></td>";
                            echo "<td data-label='Stock'><span class='{$stockClass}'><strong>{$p['quantity_available']}</strong> units</span></td>";
                            echo "<td data-label='Status'><span class='badge {$statusClass}'>" . ucfirst($p['status']) . "</span></td>";
                            echo "<td data-label='Actions'>";
                            echo "<div class='btn-group btn-group-sm'>";
                            echo "<a href='products-edit.php?id={$p['id']}' class='btn btn-outline-warning' title='Edit Product'>";
                            echo "<i class='fas fa-edit'></i>";
                            echo "</a>";
                            echo "<a href='products-delete.php?id={$p['id']}' class='btn btn-outline-danger' onclick='return confirm(\"Are you sure you want to delete product: " . htmlspecialchars($p['product_name']) . "?\")' title='Delete Product'>";
                            echo "<i class='fas fa-trash'></i>";
                            echo "</a>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='8' class='text-center py-4 text-danger'>";
                    echo "<i class='fas fa-exclamation-triangle fa-2x mb-2'></i><br>";
                    echo "Error loading products: " . htmlspecialchars($e->getMessage());
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

<!-- Product Details Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="productContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product?</p>
                <p><strong>Product:</strong> <span id="deleteProductName"></span></p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteProductBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>



<?php include "includes/admin-footer.php"; ?>
