<?php
/**
 * Notification Manager
 * 
 * Handles all notification-related functionality including:
 * - Creating notifications
 * - Retrieving notifications with filters
 * - Marking notifications as read
 * - Getting notification counts
 */
class NotificationManager {
    private $pdo;
    
    // Notification types and their corresponding icons and badge classes
    const TYPES = [
        'booking' => [
            'icon' => 'fa-calendar-check',
            'badge' => 'bg-primary',
            'label' => 'Booking'
        ],
        'payment' => [
            'icon' => 'fa-credit-card',
            'badge' => 'bg-success',
            'label' => 'Payment'
        ],
        'stock' => [
            'icon' => 'fa-boxes',
            'badge' => 'bg-warning text-dark',
            'label' => 'Stock'
        ],
        'general' => [
            'icon' => 'fa-bell',
            'badge' => 'bg-info',
            'label' => 'General'
        ]
    ];

    /**
     * Constructor
     * @param PDO $pdo Database connection
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Add a new notification
     * @param string $type Notification type (booking|payment|stock|general)
     * @param string $title Notification title
     * @param string $message Notification message
     * @return bool True on success, false on failure
     */
    public function add($type, $title, $message) {
        if (!array_key_exists($type, self::TYPES)) {
            $type = 'general';
        }

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO admin_notifications (type, title, message) VALUES (?, ?, ?)"
            );
            return $stmt->execute([$type, $title, $message]);
        } catch (Exception $e) {
            error_log("Error adding notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get notifications with optional filters
     * @param array $filters Associative array of filters:
     *   - type: string Notification type
     *   - is_read: bool Read status
     *   - limit: int Maximum number of results
     *   - offset: int Offset for pagination
     * @return array Array of notification objects
     */
    public function getNotifications($filters = []) {
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
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Enhance notifications with additional data
            return array_map([$this, 'enhanceNotification'], $notifications);
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get unread notifications count
     * @param string $type Optional notification type
     * @return int Number of unread notifications
     */
    public function getUnreadCount($type = null) {
        $sql = "SELECT COUNT(*) as count FROM admin_notifications WHERE is_read = 0";
        $params = [];
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['count'] ?? 0);
        } catch (Exception $e) {
            error_log("Error getting unread count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Mark notification as read
     * @param int $id Notification ID
     * @return bool True on success, false on failure
     */
    public function markAsRead($id) {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE admin_notifications SET is_read = 1 WHERE id = ?"
            );
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all notifications as read
     * @param string $type Optional notification type
     * @return bool True on success, false on failure
     */
    public function markAllAsRead($type = null) {
        try {
            $sql = "UPDATE admin_notifications SET is_read = 1";
            $params = [];
            
            if ($type) {
                $sql .= " WHERE type = ?";
                $params[] = $type;
            }
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all notification types with their metadata
     * @return array Array of notification types with their properties
     */
    public function getNotificationTypes() {
        return self::TYPES;
    }

    /**
     * Enhance notification with additional data
     * @param array $notification Notification data
     * @return array Enhanced notification
     */
    private function enhanceNotification($notification) {
        $type = $notification['type'] ?? 'general';
        $meta = self::TYPES[$type] ?? self::TYPES['general'];
        
        return array_merge($notification, [
            'icon' => $meta['icon'],
            'badge_class' => $meta['badge'],
            'type_label' => $meta['label'],
            'time_ago' => $this->timeAgo($notification['created_at'])
        ]);
    }

    /**
     * Format timestamp to relative time
     * @param string $datetime The datetime string
     * @return string Formatted time ago string
     */
    private function timeAgo($datetime) {
        $time = strtotime($datetime);
        $timeDiff = time() - $time;
        
        if ($timeDiff < 60) return 'Just now';
        if ($timeDiff < 3600) return floor($timeDiff/60) . ' min ago';
        if ($timeDiff < 86400) return floor($timeDiff/3600) . ' hour' . (floor($timeDiff/3600) > 1 ? 's' : '') . ' ago';
        if ($timeDiff < 604800) return floor($timeDiff/86400) . ' day' . (floor($timeDiff/86400) > 1 ? 's' : '') . ' ago';
        return date('M d, Y', $time);
    }
}
