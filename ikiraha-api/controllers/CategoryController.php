<?php
/**
 * Category Controller for IKIRAHA API
 * Handles food category management
 */

class CategoryController {
    private $db;
    private $category;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->category = new Category($this->db);
    }

    /**
     * Get all categories
     */
    public function getAllCategories() {
        try {
            // Get query parameters
            $filters = [];

            if (isset($_GET['search'])) {
                $filters['search'] = sanitizeInput($_GET['search']);
            }

            if (isset($_GET['limit'])) {
                $filters['limit'] = min((int)$_GET['limit'], 100);
            }

            if (isset($_GET['offset'])) {
                $filters['offset'] = (int)$_GET['offset'];
            }

            $result = $this->category->getAllCategories($filters);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Categories retrieved successfully', $result['count']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('CategoryController getAllCategories error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get categories with product count
     */
    public function getCategoriesWithProductCount() {
        try {
            $result = $this->category->getCategoriesWithProductCount();

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Categories with product count retrieved successfully', $result['count']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('CategoryController getCategoriesWithProductCount error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get category by ID
     */
    public function getCategoryById($id) {
        try {
            $result = $this->category->getCategoryById($id);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Category retrieved successfully');
            } else {
                $this->sendError($result['message'], 404);
            }

        } catch (Exception $e) {
            logError('CategoryController getCategoryById error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Create new category
     */
    public function createCategory() {
        try {
            // Check authentication - only super_admin can create categories
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

            $result = $this->category->createCategory($data);

            if ($result['success']) {
                $this->sendSuccess($result['data'], $result['message'], null, 201);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('CategoryController createCategory error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Update category
     */
    public function updateCategory($id) {
        try {
            // Check authentication - only super_admin can update categories
            $authResult = $this->checkAuth(['super_admin']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            // Get PUT data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            // Sanitize input
            $data = sanitizeInput($data);

            $result = $this->category->updateCategory($id, $data);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('CategoryController updateCategory error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Delete category
     */
    public function deleteCategory($id) {
        try {
            // Check authentication - only super_admin can delete categories
            $authResult = $this->checkAuth(['super_admin']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $result = $this->category->deleteCategory($id);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('CategoryController deleteCategory error: ' . $e->getMessage());
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
