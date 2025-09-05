<?php
/**
 * Transaction Controller for IKIRAHA API
 * Handles payment transactions and financial records
 */

class TransactionController {
    private $db;
    private $transaction;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->transaction = new Transaction($this->db);
    }

    /**
     * Get all transactions with filters
     */
    public function getAllTransactions() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['super_admin', 'accountant']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            // Get query parameters
            $filters = [];

            if (isset($_GET['status'])) {
                $filters['status'] = sanitizeInput($_GET['status']);
            }

            if (isset($_GET['payment_method'])) {
                $filters['payment_method'] = sanitizeInput($_GET['payment_method']);
            }

            if (isset($_GET['client_id'])) {
                $filters['client_id'] = (int)$_GET['client_id'];
            }

            if (isset($_GET['restaurant_id'])) {
                $filters['restaurant_id'] = (int)$_GET['restaurant_id'];
            }

            if (isset($_GET['date_from'])) {
                $filters['date_from'] = sanitizeInput($_GET['date_from']);
            }

            if (isset($_GET['date_to'])) {
                $filters['date_to'] = sanitizeInput($_GET['date_to']);
            }

            if (isset($_GET['min_amount'])) {
                $filters['min_amount'] = (int)$_GET['min_amount'];
            }

            if (isset($_GET['max_amount'])) {
                $filters['max_amount'] = (int)$_GET['max_amount'];
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

            $result = $this->transaction->getAllTransactions($filters);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Transactions retrieved successfully', $result['count']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('TransactionController getAllTransactions error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get transaction by ID
     */
    public function getTransactionById($id) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['super_admin', 'accountant', 'merchant']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $result = $this->transaction->getTransactionById($id);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Transaction retrieved successfully');
            } else {
                $this->sendError($result['message'], 404);
            }

        } catch (Exception $e) {
            logError('TransactionController getTransactionById error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get transactions by order ID
     */
    public function getTransactionsByOrder($orderId) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['super_admin', 'accountant', 'merchant', 'client']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $result = $this->transaction->getTransactionsByOrder($orderId);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Order transactions retrieved successfully', $result['count']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('TransactionController getTransactionsByOrder error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Create new transaction
     */
    public function createTransaction() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['super_admin', 'merchant']);
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

            $result = $this->transaction->createTransaction($data);

            if ($result['success']) {
                $this->sendSuccess($result['data'], $result['message'], null, 201);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('TransactionController createTransaction error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Update transaction status
     */
    public function updateTransactionStatus($id) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['super_admin', 'merchant']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            // Get PUT data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data || !isset($data['status'])) {
                $this->sendError('Status is required', 400);
                return;
            }

            // Sanitize input
            $data = sanitizeInput($data);

            $reference = isset($data['reference']) ? $data['reference'] : null;

            $result = $this->transaction->updateTransactionStatus($id, $data['status'], $reference);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('TransactionController updateTransactionStatus error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get transaction statistics
     */
    public function getTransactionStats() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['super_admin', 'accountant']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            // Get query parameters
            $filters = [];

            if (isset($_GET['date_from'])) {
                $filters['date_from'] = sanitizeInput($_GET['date_from']);
            }

            if (isset($_GET['date_to'])) {
                $filters['date_to'] = sanitizeInput($_GET['date_to']);
            }

            if (isset($_GET['restaurant_id'])) {
                $filters['restaurant_id'] = (int)$_GET['restaurant_id'];
            }

            $result = $this->transaction->getTransactionStats($filters);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Transaction statistics retrieved successfully');
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('TransactionController getTransactionStats error: ' . $e->getMessage());
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
