<?php
if (!isset($pdo) || !($pdo instanceof PDO)) {
    return; // Exit if no valid PDO connection
}

require_once __DIR__ . '/NotificationManager.php';

$notificationManager = new NotificationManager($pdo);

// Get unread count and notifications
try {
    $unreadCount = $notificationManager->getUnreadCount();
    $notifications = $notificationManager->getNotifications(['limit' => 5]);
    $types = $notificationManager->getNotificationTypes();
} catch (Exception $e) {
    error_log("Notification dropdown error: " . $e->getMessage());
    $unreadCount = 0;
    $notifications = [];
    $types = [];
}
?>

<li class="nav-item dropdown">
    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                <?= $unreadCount ?>
                <span class="visually-hidden">unread notifications</span>
            </span>
        <?php endif; ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" style="width: 350px; max-height: 500px; overflow-y: auto;">
        <li class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <h6 class="mb-0">Notifications</h6>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-filter"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="notifications.php">
                        <i class="fas fa-list me-2"></i>All Types
                    </a></li>
                    <?php foreach ($types as $type => $meta): ?>
                        <li><a class="dropdown-item" href="notifications.php?type=<?= $type ?>">
                            <i class="fas <?= $meta['icon'] ?> me-2"></i><?= $meta['label'] ?>
                        </a></li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($unreadCount > 0): ?>
                    <a href="mark_all_read.php" class="btn btn-sm btn-outline-primary ms-2">
                        <i class="fas fa-check-double me-1"></i> Mark all read
                    </a>
                <?php endif; ?>
            </div>
        </li>
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notification): ?>
                <li class="notification-item">
                    <a class="dropdown-item p-3 <?= $notification['is_read'] ? 'text-muted' : 'bg-light' ?>" 
                       href="notifications.php?notification_id=<?= $notification['id'] ?>">
                        <div class="d-flex w-100">
                            <div class="me-2">
                                <span class="badge <?= $notification['badge_class'] ?> p-2">
                                    <i class="fas <?= $notification['icon'] ?> fa-fw"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h6>
                                    <small class="text-nowrap"><?= $notification['time_ago'] ?></small>
                                </div>
                                <p class="mb-0 small"><?= htmlspecialchars($notification['message']) ?></p>
                                <small class="text-muted">
                                    <i class="fas <?= $notification['icon'] ?> me-1"></i> 
                                    <?= $notification['type_label'] ?>
                            </div>
                        </div>
                        <?php if (!$notification['is_read']): ?>
                            <span class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-primary rounded-pill">New</span>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="dropdown-divider my-0"></li>
            <?php endforeach; ?>
            <li class="text-center py-2 bg-light">
                <a href="notifications.php" class="text-decoration-none">
                    <i class="fas fa-list me-1"></i> View All Notifications
                </a>
            </li>
        <?php else: ?>
            <li class="text-center py-4">
                <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 1.5rem;"></i>
                <p class="text-muted mb-0">No notifications yet</p>
            </li>
        <?php endif; ?>
    </ul>
</li>

<style>
.notification-item {
    position: relative;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}
.notification-item:hover {
    background-color: #f8f9fa;
    border-left-color: #0d6efd;
}
.dropdown-menu {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.1);
}
.badge {
    min-width: 1.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>

