<?php include "includes/admin-header.php"; include "../config/db.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-alt me-2"></i>Bookings Management</h2>
</div>

<div class="admin-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Product</th>
                    <th>Rental Period</th>
                    <th>Size/Color</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $q = "SELECT b.*, u.name as uname, p.product_name FROM bookings b
                        JOIN users u ON b.user_id=u.id
                        JOIN products p ON b.product_id=p.id
                        ORDER BY b.created_at DESC";
                    $stmt = $pdo->query($q);
                    $bookings = $stmt->fetchAll();
                    
                    if (empty($bookings)) {
                        echo "<tr><td colspan='8' class='text-center py-4 text-muted'>";
                        echo "<i class='fas fa-calendar-times fa-2x mb-2'></i><br>";
                        echo "No bookings found.";
                        echo "</td></tr>";
                    } else {
                        foreach($bookings as $b) {
                            $statusClass = '';
                            switch($b['status']) {
                                case 'pending': $statusClass = 'badge-pending'; break;
                                case 'confirmed': $statusClass = 'badge-confirmed'; break;
                                case 'cancelled': $statusClass = 'badge-cancelled'; break;
                                case 'completed': $statusClass = 'badge-completed'; break;
                                default: $statusClass = 'bg-secondary';
                            }
                            
                            $duration = (strtotime($b['end_date']) - strtotime($b['start_date'])) / (60*60*24) + 1;
                            
                            echo "<tr>";
                            echo "<td data-label='ID'><span class='badge bg-primary'>#{$b['id']}</span></td>";
                            echo "<td data-label='User'><strong>" . htmlspecialchars($b['uname']) . "</strong></td>";
                            echo "<td data-label='Product'><strong>" . htmlspecialchars($b['product_name']) . "</strong></td>";
                            echo "<td data-label='Rental Period'><small>" . date('M j', strtotime($b['start_date'])) . " - " . date('M j, Y', strtotime($b['end_date'])) . "<br><span class='text-muted'>({$duration} days)</span></small></td>";
                            echo "<td data-label='Size/Color'><small>" . htmlspecialchars($b['selected_size']) . " / " . htmlspecialchars($b['selected_color']) . "</small></td>";
                            echo "<td data-label='Price'><strong class='text-success'>₹" . number_format($b['total_price'], 2) . "</strong></td>";
                            echo "<td data-label='Status'><span class='badge {$statusClass}'>" . ucfirst($b['status']) . "</span></td>";
                            echo "<td data-label='Actions'>";
                            echo "<div class='btn-group btn-group-sm'>";
                            echo "<a href='order-view.php?id={$b['id']}' class='btn btn-outline-info' title='View Details'>";
                            echo "<i class='fas fa-eye'></i>";
                            echo "</a>";
                            echo "<a href='send-bill.php?id={$b['id']}' class='btn btn-outline-success' title='Send Invoice'>";
                            echo "<i class='fas fa-file-invoice'></i>";
                            echo "</a>";
                            echo "<a href='order-edit.php?id={$b['id']}' class='btn btn-outline-warning' title='Edit Order'>";
                            echo "<i class='fas fa-edit'></i>";
                            echo "</a>";
                            echo "<a href='order-delete.php?id={$b['id']}' class='btn btn-outline-danger' onclick='return confirm(\"Are you sure you want to delete this booking for: " . htmlspecialchars($b['product_name']) . "?\")' title='Delete Order'>";
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
                    echo "Error loading bookings: " . htmlspecialchars($e->getMessage());
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <div class="mb-3">
                        <label for="orderStatus" class="form-label">Select Status</label>
                        <select class="form-select" id="orderStatus" name="status">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveStatus()">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this order?</p>
                <p><strong>Product:</strong> <span id="deleteOrderProduct"></span></p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteOrderBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>



<?php include "includes/admin-footer.php"; ?>
