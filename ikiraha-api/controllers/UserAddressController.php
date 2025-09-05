<?php
/**
 * UserAddress Controller for IKIRAHA API
 * Handles user delivery addresses management
 */

class UserAddressController {
    private $db;
    private $userAddress;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userAddress = new UserAddress($this->db);
    }

    /**
     * Get all addresses for authenticated user
     */
    public function getUserAddresses() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client', 'merchant']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->userAddress->getUserAddresses($user['id']);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'User addresses retrieved successfully', $result['count']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('UserAddressController getUserAddresses error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get address by ID
     */
    public function getAddressById($id) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client', 'merchant']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->userAddress->getAddressById($id, $user['id']);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Address retrieved successfully');
            } else {
                $this->sendError($result['message'], 404);
            }

        } catch (Exception $e) {
            logError('UserAddressController getAddressById error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Create new address
     */
    public function createAddress() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client', 'merchant']);
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

            $result = $this->userAddress->createAddress($data, $user['id']);

            if ($result['success']) {
                $this->sendSuccess($result['data'], $result['message'], null, 201);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('UserAddressController createAddress error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Update address
     */
    public function updateAddress($id) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client', 'merchant']);
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

            $result = $this->userAddress->updateAddress($id, $data, $user['id']);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('UserAddressController updateAddress error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Delete address
     */
    public function deleteAddress($id) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client', 'merchant']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->userAddress->deleteAddress($id, $user['id']);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 404);
            }

        } catch (Exception $e) {
            logError('UserAddressController deleteAddress error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Set address as default
     */
    public function setDefaultAddress($id) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client', 'merchant']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->userAddress->setDefaultAddress($id, $user['id']);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 404);
            }

        } catch (Exception $e) {
            logError('UserAddressController setDefaultAddress error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get default address
     */
    public function getDefaultAddress() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client', 'merchant']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->userAddress->getDefaultAddress($user['id']);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Default address retrieved successfully');
            } else {
                $this->sendError($result['message'], 404);
            }

        } catch (Exception $e) {
            logError('UserAddressController getDefaultAddress error: ' . $e->getMessage());
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
