<?php
/**
 * Format timestamp to relative time (e.g., "2 hours ago")
 * @param string $datetime The datetime string
 * @return string Formatted time ago string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $timeDiff = time() - $time;
    
    if ($timeDiff < 60) return 'Just now';
    if ($timeDiff < 3600) return floor($timeDiff/60) . ' min ago';
    if ($timeDiff < 86400) return floor($timeDiff/3600) . ' hour' . (floor($timeDiff/3600) > 1 ? 's' : '') . ' ago';
    if ($timeDiff < 604800) return floor($timeDiff/86400) . ' day' . (floor($timeDiff/86400) > 1 ? 's' : '') . ' ago';
    return date('M d, Y', $time);
}

/**
 * Get icon for notification type
 * @param string $type Notification type
 * @return string Font Awesome icon class
 */
function getNotificationIcon($type) {
    $icons = [
        'booking' => 'fa-calendar-check',
        'payment' => 'fa-credit-card',
        'stock' => 'fa-boxes',
        'general' => 'fa-bell'
    ];
    return $icons[$type] ?? 'fa-bell';
}

/**
 * Get badge class for notification type
 * @param string $type Notification type
 * @return string Bootstrap badge class
 */
function getNotificationBadgeClass($type) {
    $classes = [
        'booking' => 'bg-primary',
        'payment' => 'bg-success',
        'stock' => 'bg-warning text-dark',
        'general' => 'bg-info'
    ];
    return $classes[$type] ?? 'bg-secondary';
}

/**
 * Add a new notification
 * @param PDO $pdo Database connection
 * @param string $type Notification type (booking|payment|stock|general)
 * @param string $title Notification title
 * @param string $message Notification message
 * @return bool True on success, false on failure
 */
function addNotification($pdo, $type, $title, $message) {
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_notifications (type, title, message) VALUES (?, ?, ?)");
        return $stmt->execute([$type, $title, $message]);
    } catch (Exception $e) {
        error_log("Error adding notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get notifications with filters
 * @param PDO $pdo Database connection
 * @param array $filters Optional filters (type, is_read, limit, offset)
 * @return array Array of notifications
 */
function getNotifications($pdo, $filters = []) {
    $sql = "SELECT * FROM admin_notifications WHERE 1=1";
    $params = [];
    
    if (!empty($filters['type'])) {
        $sql .= " AND type = ?";
        $params[] = $filters['type'];
    }
    
    if (isset($filters['is_read'])) {
        $sql .= " AND is_read = ?";
        $params[] = (int)$filters['is_read'];
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT " . (int)$filters['limit'];
        if (!empty($filters['offset'])) {
            $sql .= " OFFSET " . (int)$filters['offset'];
        }
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting notifications: " . $e->getMessage());
        return [];
    }
}
