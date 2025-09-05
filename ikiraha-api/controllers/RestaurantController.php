<?php
/**
 * Restaurant Controller for IKIRAHA API
 * Handles restaurant management and merchant operations
 */

class RestaurantController {
    private $db;
    private $restaurant;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->restaurant = new Restaurant($this->db);
    }

    /**
     * Get all restaurants
     */
    public function getAllRestaurants() {
        try {
            // Get query parameters
            $filters = [];

            if (isset($_GET['merchant_id'])) {
                $filters['merchant_id'] = (int)$_GET['merchant_id'];
            }

            if (isset($_GET['search'])) {
                $filters['search'] = sanitizeInput($_GET['search']);
            }

            if (isset($_GET['min_rating'])) {
                $filters['min_rating'] = (float)$_GET['min_rating'];
            }

            if (isset($_GET['max_delivery_fee'])) {
                $filters['max_delivery_fee'] = (int)$_GET['max_delivery_fee'];
            }

            if (isset($_GET['sort_by'])) {
                $filters['sort_by'] = sanitizeInput($_GET['sort_by']);
            }

            if (isset($_GET['sort_order'])) {
                $filters['sort_order'] = sanitizeInput($_GET['sort_order']);
            }

            if (isset($_GET['limit'])) {
                $filters['limit'] = min((int)$_GET['limit'], 100);
            }

            if (isset($_GET['offset'])) {
                $filters['offset'] = (int)$_GET['offset'];
            }

            $result = $this->restaurant->getAllRestaurants($filters);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Restaurants retrieved successfully', $result['count']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('RestaurantController getAllRestaurants error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get restaurant by ID
     */
    public function getRestaurantById($id) {
        try {
            $result = $this->restaurant->getRestaurantById($id);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Restaurant retrieved successfully');
            } else {
                $this->sendError($result['message'], 404);
            }

        } catch (Exception $e) {
            logError('RestaurantController getRestaurantById error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Create new restaurant
     */
    public function createRestaurant() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['merchant', 'super_admin']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            // Sanitize input
            $data = sanitizeInput($data);

            // For merchants, use their own ID. For super_admin, allow specifying merchant_id
            $merchantId = $user['role'] === 'merchant' ? $user['id'] : 
                         (isset($data['merchant_id']) ? $data['merchant_id'] : $user['id']);

            $result = $this->restaurant->createRestaurant($data, $merchantId);

            if ($result['success']) {
                $this->sendSuccess($result['data'], $result['message'], null, 201);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('RestaurantController createRestaurant error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Update restaurant
     */
    public function updateRestaurant($id) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['merchant', 'super_admin']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            // Get PUT data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            // Sanitize input
            $data = sanitizeInput($data);

            // For merchants, restrict to their own restaurants. Super admin can update any
            $merchantId = $user['role'] === 'merchant' ? $user['id'] : null;

            $result = $this->restaurant->updateRestaurant($id, $data, $merchantId);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('RestaurantController updateRestaurant error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Delete restaurant
     */
    public function deleteRestaurant($id) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['merchant', 'super_admin']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            // For merchants, restrict to their own restaurants. Super admin can delete any
            $merchantId = $user['role'] === 'merchant' ? $user['id'] : null;

            $result = $this->restaurant->deleteRestaurant($id, $merchantId);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 404);
            }

        } catch (Exception $e) {
            logError('RestaurantController deleteRestaurant error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get restaurants by merchant
     */
    public function getRestaurantsByMerchant($merchantId = null) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['merchant', 'super_admin', 'accountant']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            // For merchants, only show their own restaurants
            if ($user['role'] === 'merchant') {
                $merchantId = $user['id'];
            } elseif (!$merchantId) {
                $this->sendError('Merchant ID is required', 400);
                return;
            }

            $result = $this->restaurant->getRestaurantsByMerchant($merchantId);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Merchant restaurants retrieved successfully', $result['count']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('RestaurantController getRestaurantsByMerchant error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get my restaurants (for authenticated merchant)
     */
    public function getMyRestaurants() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['merchant']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->restaurant->getRestaurantsByMerchant($user['id']);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Your restaurants retrieved successfully', $result['count']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('RestaurantController getMyRestaurants error: ' . $e->getMessage());
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
