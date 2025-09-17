<?php require_once 'includes/admin-header.php'; ?>
<?php require_once '../config/db.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
                            echo $stmt->fetchColumn();
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tshirt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Bookings</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
                            echo $stmt->fetchColumn();
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                            echo $stmt->fetchColumn();
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Low Stock Items</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity_available <= 2 AND status = 'active'");
                            echo $stmt->fetchColumn();
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>User</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT b.id, p.product_name, u.name, b.created_at, b.status 
                                               FROM bookings b 
                                               JOIN products p ON b.product_id = p.id 
                                               JOIN users u ON b.user_id = u.id 
                                               ORDER BY b.created_at DESC LIMIT 5");
                            while ($booking = $stmt->fetch()):
                            ?>
                            <tr>
                                <td><?= $booking['id'] ?></td>
                                <td><?= htmlspecialchars($booking['product_name']) ?></td>
                                <td><?= htmlspecialchars($booking['name']) ?></td>
                                <td><?= date('Y-m-d', strtotime($booking['created_at'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $booking['status'] === 'confirmed' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Low Stock Products</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT id, product_name, quantity_available 
                                               FROM products 
                                               WHERE quantity_available <= 2 AND status = 'active' 
                                               ORDER BY quantity_available ASC");
                            while ($product = $stmt->fetch()):
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($product['product_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $product['quantity_available'] == 0 ? 'danger' : 'warning' ?>">
                                        <?= $product['quantity_available'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="manage-products.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary">Update</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
