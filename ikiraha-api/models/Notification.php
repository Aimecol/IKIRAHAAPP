<?php
/**
 * Notification Model for IKIRAHA API
 * Handles user notifications management
 */

class Notification {
    private $conn;
    private $table_name = "notifications";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all notifications for a user
     */
    public function getUserNotifications($userId, $filters = []) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";

            $params = [':user_id' => $userId];

            // Apply filters
            if (isset($filters['type'])) {
                $query .= " AND type = :type";
                $params[':type'] = $filters['type'];
            }

            if (isset($filters['is_read'])) {
                $query .= " AND is_read = :is_read";
                $params[':is_read'] = (int)$filters['is_read'];
            }

            // Sorting
            $query .= " ORDER BY created_at DESC";

            // Pagination
            if (isset($filters['limit'])) {
                $limit = min((int)$filters['limit'], 100);
                $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
                $query .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $limit;
                $params[':offset'] = $offset;
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $notifications,
                'count' => count($notifications)
            ];

        } catch (Exception $e) {
            logError('Notification getUserNotifications error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch user notifications'
            ];
        }
    }

    /**
     * Create new notification
     */
    public function createNotification($data) {
        try {
            // Validate input
            if (!$this->validateNotificationData($data)) {
                throw new Exception('Invalid notification data');
            }

            $query = "INSERT INTO " . $this->table_name . "
                     (user_id, title, message, type, is_read)
                     VALUES (:user_id, :title, :message, :type, :is_read)";

            $stmt = $this->conn->prepare($query);

            $isRead = 0; // New notifications are unread by default

            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':message', $data['message']);
            $stmt->bindParam(':type', $data['type']);
            $stmt->bindParam(':is_read', $isRead);

            if ($stmt->execute()) {
                $notificationId = $this->conn->lastInsertId();
                
                return [
                    'success' => true,
                    'message' => 'Notification created successfully',
                    'data' => [
                        'id' => $notificationId
                    ]
                ];
            } else {
                throw new Exception('Failed to create notification');
            }

        } catch (Exception $e) {
            logError('Notification createNotification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create bulk notifications for multiple users
     */
    public function createBulkNotifications($userIds, $title, $message, $type = 'system') {
        try {
            if (empty($userIds) || !is_array($userIds)) {
                throw new Exception('Invalid user IDs array');
            }

            $this->conn->beginTransaction();

            $query = "INSERT INTO " . $this->table_name . "
                     (user_id, title, message, type, is_read)
                     VALUES (:user_id, :title, :message, :type, 0)";

            $stmt = $this->conn->prepare($query);

            $successCount = 0;
            foreach ($userIds as $userId) {
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':message', $message);
                $stmt->bindParam(':type', $type);

                if ($stmt->execute()) {
                    $successCount++;
                }
            }

            $this->conn->commit();

            return [
                'success' => true,
                'message' => 'Bulk notifications created successfully',
                'data' => [
                    'created_count' => $successCount,
                    'total_users' => count($userIds)
                ]
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            logError('Notification createBulkNotifications error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id, $userId = null) {
        try {
            $query = "UPDATE " . $this->table_name . " SET is_read = 1 WHERE id = :id";
            $params = [':id' => $id];

            // Add user restriction if provided
            if ($userId) {
                $query .= " AND user_id = :user_id";
                $params[':user_id'] = $userId;
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Notification marked as read'
                ];
            } else {
                throw new Exception('Notification not found');
            }

        } catch (Exception $e) {
            logError('Notification markAsRead error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId) {
        try {
            $query = "UPDATE " . $this->table_name . " SET is_read = 1 WHERE user_id = :user_id AND is_read = 0";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'All notifications marked as read',
                    'data' => [
                        'updated_count' => $stmt->rowCount()
                    ]
                ];
            } else {
                throw new Exception('Failed to mark notifications as read');
            }

        } catch (Exception $e) {
            logError('Notification markAllAsRead error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete notification
     */
    public function deleteNotification($id, $userId = null) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $params = [':id' => $id];

            // Add user restriction if provided
            if ($userId) {
                $query .= " AND user_id = :user_id";
                $params[':user_id'] = $userId;
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Notification deleted successfully'
                ];
            } else {
                throw new Exception('Notification not found');
            }

        } catch (Exception $e) {
            logError('Notification deleteNotification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get unread notification count for a user
     */
    public function getUnreadCount($userId) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE user_id = :user_id AND is_read = 0";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => [
                    'unread_count' => (int)$result['count']
                ]
            ];

        } catch (Exception $e) {
            logError('Notification getUnreadCount error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get unread count'
            ];
        }
    }

    /**
     * Clear old notifications (older than specified days)
     */
    public function clearOldNotifications($days = 30) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':days', $days);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Old notifications cleared successfully',
                    'data' => [
                        'deleted_count' => $stmt->rowCount()
                    ]
                ];
            } else {
                throw new Exception('Failed to clear old notifications');
            }

        } catch (Exception $e) {
            logError('Notification clearOldNotifications error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send order notification to user
     */
    public function sendOrderNotification($userId, $orderNumber, $status) {
        try {
            $statusMessages = [
                'confirmed' => 'Your order has been confirmed and is being prepared.',
                'preparing' => 'Your order is being prepared by the restaurant.',
                'ready' => 'Your order is ready for pickup/delivery.',
                'out_for_delivery' => 'Your order is out for delivery.',
                'delivered' => 'Your order has been delivered successfully.',
                'cancelled' => 'Your order has been cancelled.'
            ];

            $title = "Order Update - #" . $orderNumber;
            $message = isset($statusMessages[$status]) ? $statusMessages[$status] : 'Your order status has been updated.';

            return $this->createNotification([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => 'order'
            ]);

        } catch (Exception $e) {
            logError('Notification sendOrderNotification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send order notification'
            ];
        }
    }

    /**
     * Validate notification data
     */
    private function validateNotificationData($data, $isCreate = true) {
        if ($isCreate) {
            if (empty($data['user_id']) || empty($data['title']) || empty($data['message'])) {
                return false;
            }
        }

        if (isset($data['title']) && strlen($data['title']) > 255) {
            return false;
        }

        if (isset($data['type']) && !in_array($data['type'], ['order', 'promotion', 'system', 'payment'])) {
            return false;
        }

        return true;
    }
}
?>
