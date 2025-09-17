<?php include "includes/admin-header.php"; include "../config/db.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-money-check-alt me-2"></i>Billing / Invoices</h2>
</div>

<div class="admin-table">
    <div class="table-responsive">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <button id="downloadSelected" class="btn btn-sm btn-primary" disabled>
                    <i class="fas fa-file-pdf me-1"></i>Download Selected
                </button>
                <button id="clearSelection" class="btn btn-sm btn-outline-secondary ms-2" disabled>Clear</button>
            </div>
        </div>
        <table class="table table-hover mb-0" id="billingTable">
            <thead>
                <tr>
                    <th style="width:32px;"><input type="checkbox" id="selectAll"></th>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $stmt = $pdo->query("SELECT b.*, u.name as uname FROM bookings b JOIN users u ON b.user_id=u.id ORDER BY b.created_at DESC");
                    $bookings = $stmt->fetchAll();
                    
                    if (empty($bookings)) {
                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>";
                        echo "<i class='fas fa-inbox fa-2x mb-2'></i><br>";
                        echo "No billing records found.";
                        echo "</td></tr>";
                    } else {
                        foreach($bookings as $o) {
                            $statusClass = '';
                            switch($o['status']) {
                                case 'pending': $statusClass = 'badge-pending'; break;
                                case 'confirmed': $statusClass = 'badge-confirmed'; break;
                                case 'cancelled': $statusClass = 'badge-cancelled'; break;
                                case 'completed': $statusClass = 'badge-completed'; break;
                                default: $statusClass = 'bg-secondary';
                            }
                            
                            echo "<tr>";
                            echo "<td><input type='checkbox' class='order-check' value='".$o['id']."'></td>";
                            echo "<td data-label='Order ID'><span class='badge bg-primary'>#{$o['id']}</span></td>";
                            echo "<td data-label='User'><strong>" . htmlspecialchars($o['uname']) . "</strong></td>";
                            echo "<td data-label='Total'><strong class='text-success'>₹" . number_format($o['total_price'], 2) . "</strong></td>";
                            echo "<td data-label='Date'><small class='text-muted'>" . date('M j, Y g:i A', strtotime($o['created_at'])) . "</small></td>";
                            echo "<td data-label='Actions'>";
                            echo "<div class='btn-group btn-group-sm'>";
                            echo "<a href='invoice-download.php?ids={$o['id']}' class='btn btn-outline-success' title='Download PDF Invoice'>";
                            echo "<i class='fas fa-file-pdf me-1'></i>Download";
                            echo "</a>";
                            echo "<a href='manage-orders.php?view={$o['id']}' class='btn btn-outline-info' title='View Details'>";
                            echo "<i class='fas fa-eye'></i>";
                            echo "</a>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='5' class='text-center py-4 text-danger'>";
                    echo "<i class='fas fa-exclamation-triangle fa-2x mb-2'></i><br>";
                    echo "Error loading billing records: " . htmlspecialchars($e->getMessage());
                    echo "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const selectAll = document.getElementById('selectAll');
  const btnDownload = document.getElementById('downloadSelected');
  const btnClear = document.getElementById('clearSelection');
  const tbody = document.querySelector('#billingTable tbody');

  function updateButtons(){
    const checked = tbody.querySelectorAll('.order-check:checked');
    const has = checked.length > 0;
    btnDownload.disabled = !has;
    btnClear.disabled = !has;
  }

  selectAll.addEventListener('change', function(){
    tbody.querySelectorAll('.order-check').forEach(cb => cb.checked = selectAll.checked);
    updateButtons();
  });

  tbody.addEventListener('change', function(e){
    if (e.target.classList.contains('order-check')){
      const all = tbody.querySelectorAll('.order-check');
      const checked = tbody.querySelectorAll('.order-check:checked');
      selectAll.checked = checked.length === all.length && all.length > 0;
      updateButtons();
    }
  });

  btnDownload.addEventListener('click', function(){
    const ids = Array.from(tbody.querySelectorAll('.order-check:checked')).map(cb => cb.value).join(',');
    if (!ids) return;
    window.location.href = 'invoice-download.php?ids=' + encodeURIComponent(ids);
  });

  btnClear.addEventListener('click', function(){
    selectAll.checked = false;
    tbody.querySelectorAll('.order-check').forEach(cb => cb.checked = false);
    updateButtons();
  });
});
</script>

