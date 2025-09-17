<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Determine current page for menu highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Rameshwar Traditional Wear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="assets/js/notifications.js" defer></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <button class="btn btn-outline-light me-2 d-lg-none" id="sidebarToggle" aria-label="Toggle sidebar" aria-controls="adminSidebar" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-tshirt me-2"></i>Admin Panel
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['admin_name']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <div class="sidebar-overlay d-lg-none" id="sidebarOverlay" hidden></div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 bg-light sidebar" id="adminSidebar" aria-label="Admin menu">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link<?= ($currentPage=="dashboard.php")?" active":"" ?>" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= (in_array($currentPage,["manage-categories.php","categories-add.php","categories-edit.php"]))?" active":"" ?>" href="manage-categories.php">
                                <i class="fas fa-list me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= (in_array($currentPage,["manage-products.php","products-add.php","products-edit.php"]))?" active":"" ?>" href="manage-products.php">
                                <i class="fas fa-tshirt me-2"></i>Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= ($currentPage=="manage-orders.php")?" active":"" ?>" href="manage-orders.php">
                                <i class="fas fa-calendar-alt me-2"></i>Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= ($currentPage=="manage-users.php")?" active":"" ?>" href="manage-users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= ($currentPage=="manage-feedback.php")?" active":"" ?>" href="manage-feedback.php">
                                <i class="fas fa-comments me-2"></i>Feedback
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= ($currentPage=="manage-newsletter.php")?" active":"" ?>" href="manage-newsletter.php">
                                <i class="fas fa-newspaper me-2"></i>Newsletter
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= ($currentPage=="notifications.php")?" active":"" ?>" href="notifications.php">
                                <i class="fas fa-bell me-2"></i>Notifications
                                <span class="badge bg-danger float-end" id="sidebarNotificationCount">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= ($currentPage=="billing.php")?" active":"" ?>" href="billing.php">
                                <i class="fas fa-money-check-alt me-2"></i>Billing
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link<?= ($currentPage=="manage-contacts.php")?" active":"" ?>" href="manage-contacts.php">
                                <i class="fas fa-envelope me-2"></i>Contact Us
                            </a>
                        </li>
                        <li class="nav-item mt-2 pt-2 border-top">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-right-from-bracket me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-10 main-content">
