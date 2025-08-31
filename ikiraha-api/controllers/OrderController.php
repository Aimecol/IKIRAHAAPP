<?php
/**
 * Order Controller for IKIRAHA API
 * Handles order creation, management, and status updates
 */

class OrderController {
    private $db;
    private $order;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->order = new Order($this->db);
    }

    /**
     * Create new order (client only)
     */
    public function createOrder() {
        try {
            if (!AuthMiddleware::requireClient()) {
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

            $currentUser = AuthMiddleware::getCurrentUser();
            $order = $this->order->createOrder($data, $currentUser['user_id']);

            $this->sendSuccess([
                'message' => 'Order created successfully',
                'order' => $order
            ], 201);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Get order by ID
     */
    public function getOrderById($id) {
        try {
            if (!AuthMiddleware::authenticate()) {
                return;
            }

            $order = $this->order->getOrderById($id);

            if (!$order) {
                $this->sendError('Order not found', 404);
                return;
            }

            $currentUser = AuthMiddleware::getCurrentUser();

            // Check if user has permission to view this order
            if ($currentUser['role'] === 'client' && $order['client_id'] != $currentUser['user_id']) {
                $this->sendError('Unauthorized to view this order', 403);
                return;
            }

            $this->sendSuccess([
                'message' => 'Order retrieved successfully',
                'order' => $order
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Get user's orders (client only)
     */
    public function getUserOrders() {
        try {
            if (!AuthMiddleware::requireClient()) {
                return;
            }

            $currentUser = AuthMiddleware::getCurrentUser();
            $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $orders = $this->order->getOrdersByUserId($currentUser['user_id'], $limit, $offset);

            $this->sendSuccess([
                'message' => 'Orders retrieved successfully',
                'orders' => $orders,
                'count' => count($orders)
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Get restaurant orders (merchant only)
     */
    public function getRestaurantOrders($restaurantId) {
        try {
            if (!AuthMiddleware::requireMerchant()) {
                return;
            }

            $currentUser = AuthMiddleware::getCurrentUser();

            // Verify merchant owns the restaurant
            if (!$this->verifyRestaurantOwnership($restaurantId, $currentUser['user_id'])) {
                $this->sendError('Unauthorized: Restaurant not owned by merchant', 403);
                return;
            }

            $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $orders = $this->order->getOrdersByRestaurantId($restaurantId, $limit, $offset);

            $this->sendSuccess([
                'message' => 'Restaurant orders retrieved successfully',
                'orders' => $orders,
                'count' => count($orders)
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Update order status (merchant only)
     */
    public function updateOrderStatus($id) {
        try {
            if (!AuthMiddleware::requireMerchantOrAdmin()) {
                return;
            }

            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data || !isset($data['status'])) {
                $this->sendError('Status is required', 400);
                return;
            }

            $status = sanitizeInput($data['status']);
            $currentUser = AuthMiddleware::getCurrentUser();

            // For merchants, verify they own the restaurant for this order
            if ($currentUser['role'] === 'merchant') {
                $orderData = $this->order->getOrderById($id);
                if (!$orderData) {
                    $this->sendError('Order not found', 404);
                    return;
                }

                if (!$this->verifyRestaurantOwnership($orderData['restaurant_id'], $currentUser['user_id'])) {
                    $this->sendError('Unauthorized: Order not from your restaurant', 403);
                    return;
                }
            }

            $order = $this->order->updateOrderStatus($id, $status, $currentUser['user_id']);

            $this->sendSuccess([
                'message' => 'Order status updated successfully',
                'order' => $order
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Get all orders (admin/accountant only)
     */
    public function getAllOrders() {
        try {
            if (!AuthMiddleware::requireAccountantOrAdmin()) {
                return;
            }

            // Get filters from query parameters
            $filters = [];
            if (isset($_GET['status'])) {
                $filters['status'] = sanitizeInput($_GET['status']);
            }
            if (isset($_GET['payment_status'])) {
                $filters['payment_status'] = sanitizeInput($_GET['payment_status']);
            }
            if (isset($_GET['date_from'])) {
                $filters['date_from'] = sanitizeInput($_GET['date_from']);
            }
            if (isset($_GET['date_to'])) {
                $filters['date_to'] = sanitizeInput($_GET['date_to']);
            }

            $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $orders = $this->order->getAllOrders($filters, $limit, $offset);

            $this->sendSuccess([
                'message' => 'All orders retrieved successfully',
                'orders' => $orders,
                'count' => count($orders),
                'filters' => $filters
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Verify restaurant ownership
     */
    private function verifyRestaurantOwnership($restaurantId, $merchantId) {
        try {
            $query = "SELECT id FROM restaurants WHERE id = :restaurant_id AND merchant_id = :merchant_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':restaurant_id', $restaurantId);
            $stmt->bindParam(':merchant_id', $merchantId);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            logError('Verify restaurant ownership failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send success response
     */
    private function sendSuccess($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    /**
     * Send error response
     */
    private function sendError($message, $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
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
            500 => 'INTERNAL_SERVER_ERROR'
        ];

        return isset($errorCodes[$statusCode]) ? $errorCodes[$statusCode] : 'UNKNOWN_ERROR';
    }
}
?>