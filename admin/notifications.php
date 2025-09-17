<?php
// Start session if not already started
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Include required files
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/includes/NotificationManager.php';

// Initialize NotificationManager
$notificationManager = new NotificationManager($pdo);

// Mark notification as read if viewing a specific one
if (isset($_GET['notification_id'])) {
    $notificationId = (int)$_GET['notification_id'];
    $notificationManager->markAsRead($notificationId);
}

// Mark all as read if requested
if (isset($_POST['mark_all_read'])) {
    $type = $_GET['type'] ?? null;
    if ($notificationManager->markAllAsRead($type)) {
        $_SESSION['success'] = "All notifications marked as read";
    } else {
        $_SESSION['error'] = "Error marking notifications as read";
    }
    header("Location: notifications.php" . ($type ? "?type=$type" : ''));
    exit;
}

// Get filter parameters
$type = $_GET['type'] ?? null;
$isRead = isset($_GET['is_read']) ? (bool)$_GET['is_read'] : null;

// Get notifications with filters
$filters = [];
if ($type) $filters['type'] = $type;
if ($isRead !== null) $filters['is_read'] = $isRead;

// Get notifications
$notifications = $notificationManager->getNotifications($filters);
$unreadCount = $notificationManager->getUnreadCount($type);
$types = $notificationManager->getNotificationTypes();

// Include header
require_once 'includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <?= $type ? ($types[$type]['label'] ?? ucfirst($type)) . ' ' : '' ?>Notifications
                <?php if ($type): ?>
                    <a href="notifications.php" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="fas fa-times"></i> Clear filter
                    </a>
                <?php endif; ?>
            </h1>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Filter by Type</h6></li>
                    <li><a class="dropdown-item <?= !$type ? 'active' : '' ?>" href="notifications.php">
                        <i class="fas fa-list me-2"></i>All Types
                    </a></li>
                    <?php foreach ($types as $typeKey => $typeMeta): ?>
                        <li><a class="dropdown-item <?= $type === $typeKey ? 'active' : '' ?>" 
                              href="notifications.php?type=<?= $typeKey ?>">
                            <i class="fas <?= $typeMeta['icon'] ?> me-2"></i><?= $typeMeta['label'] ?>
                        </a></li>
                    <?php endforeach; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Filter by Status</h6></li>
                    <li><a class="dropdown-item <?= $isRead === null ? 'active' : '' ?>" href="notifications.php">
                        <i class="fas fa-asterisk me-2"></i>All Statuses
                    </a></li>
                    <li><a class="dropdown-item <?= $isRead === false ? 'active' : '' ?>" 
                          href="notifications.php?<?= $type ? "type=$type&" : '' ?>is_read=0">
                        <i class="fas fa-envelope me-2"></i>Unread Only
                    </a></li>
                    <li><a class="dropdown-item <?= $isRead === true ? 'active' : '' ?>" 
                          href="notifications.php?<?= $type ? "type=$type&" : '' ?>is_read=1">
                        <i class="fas fa-envelope-open me-2"></i>Read Only
                    </a></li>
                </ul>
                <?php if ($unreadCount > 0): ?>
                    <form method="post" class="ms-2">
                        <input type="hidden" name="mark_all_read" value="1">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-double me-1"></i> Mark All as Read
                        </button>
                    </form>
                <?php endif; ?>
            </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <?php if (!empty($notifications)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $notification): ?>
                        <a href="?notification_id=<?= $notification['id'] ?><?= $type ? "&type=$type" : '' ?>" 
                           class="list-group-item list-group-item-action <?= $notification['is_read'] ? 'text-muted' : 'fw-bold bg-light' ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <div class="d-flex align-items-start">
                                    <span class="badge <?= $notification['badge_class'] ?> me-2 mt-1 p-2">
                                        <i class="fas <?= $notification['icon'] ?> fa-fw"></i>
                                    </span>
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h6>
                                        <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                                        <small class="text-muted">
                                            <i class="fas <?= $notification['icon'] ?> me-1"></i>
                                            <?= $notification['type_label'] ?>
                                            <span class="mx-2">•</span>
                                            <?= $notification['time_ago'] ?>
                                        </small>
                                    </div>
                                </div>
                                <?php if (!$notification['is_read']): ?>
                                    <span class="badge bg-primary">New</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No notifications found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
