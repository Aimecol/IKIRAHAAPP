<?php
/**
 * Product Controller for IKIRAHA API
 * Handles product management, categories, and menu operations
 */

class ProductController {
    private $db;
    private $product;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
    }

    /**
     * Get all products with filters
     */
    public function getAllProducts() {
        try {
            // Get query parameters
            $filters = [];

            if (isset($_GET['category_id'])) {
                $filters['category_id'] = (int)$_GET['category_id'];
            }

            if (isset($_GET['restaurant_id'])) {
                $filters['restaurant_id'] = (int)$_GET['restaurant_id'];
            }

            if (isset($_GET['is_featured'])) {
                $filters['is_featured'] = (int)$_GET['is_featured'];
            }

            if (isset($_GET['search'])) {
                $filters['search'] = sanitizeInput($_GET['search']);
            }

            if (isset($_GET['min_price'])) {
                $filters['min_price'] = (int)$_GET['min_price'];
            }

            if (isset($_GET['max_price'])) {
                $filters['max_price'] = (int)$_GET['max_price'];
            }

            if (isset($_GET['limit'])) {
                $filters['limit'] = min((int)$_GET['limit'], 100); // Max 100 items
            }

            if (isset($_GET['offset'])) {
                $filters['offset'] = (int)$_GET['offset'];
            }

            $products = $this->product->getAllProducts($filters);

            $this->sendSuccess([
                'message' => 'Products retrieved successfully',
                'products' => $products,
                'count' => count($products)
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Get product by ID
     */
    public function getProductById($id) {
        try {
            $product = $this->product->getProductById($id);

            if (!$product) {
                $this->sendError('Product not found', 404);
                return;
            }

            $this->sendSuccess([
                'message' => 'Product retrieved successfully',
                'product' => $product
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Create new product (merchant only)
     */
    public function createProduct() {
        try {
            if (!AuthMiddleware::requireMerchant()) {
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
            $product = $this->product->createProduct($data, $currentUser['user_id']);

            $this->sendSuccess([
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Update product (merchant only)
     */
    public function updateProduct($id) {
        try {
            if (!AuthMiddleware::requireMerchant()) {
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
            $product = $this->product->updateProduct($id, $data, $currentUser['user_id']);

            $this->sendSuccess([
                'message' => 'Product updated successfully',
                'product' => $product
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Delete product (merchant only)
     */
    public function deleteProduct($id) {
        try {
            if (!AuthMiddleware::requireMerchant()) {
                return;
            }

            $currentUser = AuthMiddleware::getCurrentUser();
            $result = $this->product->deleteProduct($id, $currentUser['user_id']);

            $this->sendSuccess($result);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Get all categories
     */
    public function getCategories() {
        try {
            $categories = $this->product->getCategories();

            $this->sendSuccess([
                'message' => 'Categories retrieved successfully',
                'categories' => $categories
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts() {
        try {
            $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 10;
            $products = $this->product->getFeaturedProducts($limit);

            $this->sendSuccess([
                'message' => 'Featured products retrieved successfully',
                'products' => $products,
                'count' => count($products)
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Search products
     */
    public function searchProducts() {
        try {
            $searchTerm = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';

            if (empty($searchTerm)) {
                $this->sendError('Search term is required', 400);
                return;
            }

            // Get additional filters
            $filters = [];
            if (isset($_GET['category_id'])) {
                $filters['category_id'] = (int)$_GET['category_id'];
            }
            if (isset($_GET['restaurant_id'])) {
                $filters['restaurant_id'] = (int)$_GET['restaurant_id'];
            }
            if (isset($_GET['limit'])) {
                $filters['limit'] = min((int)$_GET['limit'], 100);
            }

            $products = $this->product->searchProducts($searchTerm, $filters);

            $this->sendSuccess([
                'message' => 'Search completed successfully',
                'search_term' => $searchTerm,
                'products' => $products,
                'count' => count($products)
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
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