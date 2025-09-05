<?php
/**
 * Notification Controller for IKIRAHA API
 * Handles user notifications management
 */

class NotificationController {
    private $db;
    private $notification;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->notification = new Notification($this->db);
    }

    /**
     * Get all notifications for authenticated user
     */
    public function getUserNotifications() {
        try {
            // Check authentication
            $authResult = $this->checkAuth();
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            // Get query parameters
            $filters = [];

            if (isset($_GET['type'])) {
                $filters['type'] = sanitizeInput($_GET['type']);
            }

            if (isset($_GET['is_read'])) {
                $filters['is_read'] = (int)$_GET['is_read'];
            }

            if (isset($_GET['limit'])) {
                $filters['limit'] = min((int)$_GET['limit'], 100);
            }

            if (isset($_GET['offset'])) {
                $filters['offset'] = (int)$_GET['offset'];
            }

            $result = $this->notification->getUserNotifications($user['id'], $filters);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Notifications retrieved successfully', $result['count']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('NotificationController getUserNotifications error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Create new notification
     */
    public function createNotification() {
        try {
            // Check authentication - only super_admin can create notifications
            $authResult = $this->checkAuth(['super_admin']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            // Sanitize input
            $data = sanitizeInput($data);

            $result = $this->notification->createNotification($data);

            if ($result['success']) {
                $this->sendSuccess($result['data'], $result['message'], null, 201);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('NotificationController createNotification error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Create bulk notifications
     */
    public function createBulkNotifications() {
        try {
            // Check authentication - only super_admin can create bulk notifications
            $authResult = $this->checkAuth(['super_admin']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data || !isset($data['user_ids']) || !isset($data['title']) || !isset($data['message'])) {
                $this->sendError('user_ids, title, and message are required', 400);
                return;
            }

            // Sanitize input
            $data = sanitizeInput($data);

            $type = isset($data['type']) ? $data['type'] : 'system';

            $result = $this->notification->createBulkNotifications($data['user_ids'], $data['title'], $data['message'], $type);

            if ($result['success']) {
                $this->sendSuccess($result['data'], $result['message'], null, 201);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('NotificationController createBulkNotifications error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id) {
        try {
            // Check authentication
            $authResult = $this->checkAuth();
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->notification->markAsRead($id, $user['id']);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 404);
            }

        } catch (Exception $e) {
            logError('NotificationController markAsRead error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead() {
        try {
            // Check authentication
            $authResult = $this->checkAuth();
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->notification->markAllAsRead($user['id']);

            if ($result['success']) {
                $this->sendSuccess($result['data'], $result['message']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('NotificationController markAllAsRead error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Delete notification
     */
    public function deleteNotification($id) {
        try {
            // Check authentication
            $authResult = $this->checkAuth();
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->notification->deleteNotification($id, $user['id']);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 404);
            }

        } catch (Exception $e) {
            logError('NotificationController deleteNotification error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount() {
        try {
            // Check authentication
            $authResult = $this->checkAuth();
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->notification->getUnreadCount($user['id']);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Unread count retrieved successfully');
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('NotificationController getUnreadCount error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Clear old notifications
     */
    public function clearOldNotifications() {
        try {
            // Check authentication - only super_admin can clear old notifications
            $authResult = $this->checkAuth(['super_admin']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;

            $result = $this->notification->clearOldNotifications($days);

            if ($result['success']) {
                $this->sendSuccess($result['data'], $result['message']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('NotificationController clearOldNotifications error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Check authentication and authorization
     */
    private function checkAuth($allowedRoles = []) {
        try {
            $authMiddleware = new AuthMiddleware();
            $authResult = $authMiddleware->authenticate();

            if (!$authResult['success']) {
                return $authResult;
            }

            $user = $authResult['user'];

            // Check role authorization
            if (!empty($allowedRoles) && !in_array($user['role'], $allowedRoles)) {
                return [
                    'success' => false,
                    'message' => 'Insufficient permissions'
                ];
            }

            return [
                'success' => true,
                'user' => $user
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Authentication failed'
            ];
        }
    }

    /**
     * Send success response
     */
    private function sendSuccess($data = null, $message = 'Success', $count = null, $statusCode = 200) {
        http_response_code($statusCode);
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($count !== null) {
            $response['count'] = $count;
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Send error response
     */
    private function sendError($message, $statusCode = 400) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'error_code' => $this->getErrorCode($statusCode),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    /**
     * Get error code based on status code
     */
    private function getErrorCode($statusCode) {
        $errorCodes = [
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            500 => 'INTERNAL_ERROR'
        ];

        return isset($errorCodes[$statusCode]) ? $errorCodes[$statusCode] : 'UNKNOWN_ERROR';
    }
}
?>
