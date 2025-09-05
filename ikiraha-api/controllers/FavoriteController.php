<?php
/**
 * Favorite Controller for IKIRAHA API
 * Handles user favorite products management
 */

class FavoriteController {
    private $db;
    private $userFavorite;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userFavorite = new UserFavorite($this->db);
    }

    /**
     * Get all favorite products for authenticated user
     */
    public function getUserFavorites() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            // Get query parameters
            $filters = [];

            if (isset($_GET['category_id'])) {
                $filters['category_id'] = (int)$_GET['category_id'];
            }

            if (isset($_GET['restaurant_id'])) {
                $filters['restaurant_id'] = (int)$_GET['restaurant_id'];
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

            $result = $this->userFavorite->getUserFavorites($user['id'], $filters);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'User favorites retrieved successfully', $result['count']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('FavoriteController getUserFavorites error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Add product to favorites
     */
    public function addToFavorites() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data || !isset($data['product_id'])) {
                $this->sendError('Product ID is required', 400);
                return;
            }

            $productId = (int)$data['product_id'];

            $result = $this->userFavorite->addToFavorites($user['id'], $productId);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message'], null, 201);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('FavoriteController addToFavorites error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Remove product from favorites
     */
    public function removeFromFavorites($productId) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->userFavorite->removeFromFavorites($user['id'], $productId);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 404);
            }

        } catch (Exception $e) {
            logError('FavoriteController removeFromFavorites error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite($productId) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->userFavorite->toggleFavorite($user['id'], $productId);

            if ($result['success']) {
                $this->sendSuccess(null, $result['message']);
            } else {
                $this->sendError($result['message'], 400);
            }

        } catch (Exception $e) {
            logError('FavoriteController toggleFavorite error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Check if product is favorite
     */
    public function isFavorite($productId) {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $isFavorite = $this->userFavorite->isFavorite($user['id'], $productId);

            $this->sendSuccess([
                'is_favorite' => $isFavorite
            ], 'Favorite status retrieved successfully');

        } catch (Exception $e) {
            logError('FavoriteController isFavorite error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get favorite count for authenticated user
     */
    public function getFavoriteCount() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->userFavorite->getFavoriteCount($user['id']);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Favorite count retrieved successfully');
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('FavoriteController getFavoriteCount error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Get most favorited products
     */
    public function getMostFavoritedProducts() {
        try {
            $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 10;

            $result = $this->userFavorite->getMostFavoritedProducts($limit);

            if ($result['success']) {
                $this->sendSuccess($result['data'], 'Most favorited products retrieved successfully', $result['count']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('FavoriteController getMostFavoritedProducts error: ' . $e->getMessage());
            $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Clear all favorites for authenticated user
     */
    public function clearUserFavorites() {
        try {
            // Check authentication
            $authResult = $this->checkAuth(['client']);
            if (!$authResult['success']) {
                $this->sendError($authResult['message'], 401);
                return;
            }

            $user = $authResult['user'];

            $result = $this->userFavorite->clearUserFavorites($user['id']);

            if ($result['success']) {
                $this->sendSuccess($result['data'], $result['message']);
            } else {
                $this->sendError($result['message'], 500);
            }

        } catch (Exception $e) {
            logError('FavoriteController clearUserFavorites error: ' . $e->getMessage());
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
