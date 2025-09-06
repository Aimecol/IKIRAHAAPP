<?php
/**
 * IKIRAHA API Main Entry Point
 * Production-ready REST API for food delivery system
 * Supports Client, Merchant, Accountant, and Super Admin roles
 */

// Set headers for CORS and JSON responses
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include configuration and autoloader
require_once '../config/config.php';

// Simple router class
class Router {
    private $routes = [];

    public function addRoute($method, $pattern, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Debug: Log the original request URI
        error_log("Original URI: " . $requestUri);

        // Remove base path if exists
        $basePath = '/ikirahaapp/ikiraha-api/public';
        if (strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        // Remove trailing slash
        $requestUri = rtrim($requestUri, '/');
        if (empty($requestUri)) {
            $requestUri = '/';
        }

        // Debug: Log the processed request URI
        error_log("Processed URI: " . $requestUri . " Method: " . $requestMethod);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Convert route pattern to regex
            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['pattern']);
            $pattern = '#^' . $pattern . '$#';

            // Debug: Log route matching
            error_log("Checking route: " . $route['pattern'] . " against " . $requestUri . " with pattern " . $pattern);

            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match

                try {
                    $controllerClass = $route['controller'];
                    $controller = new $controllerClass();
                    $action = $route['action'];

                    // Call controller method with parameters
                    if (!empty($matches)) {
                        call_user_func_array([$controller, $action], $matches);
                    } else {
                        $controller->$action();
                    }
                    return;

                } catch (Exception $e) {
                    $this->sendError('Internal server error: ' . $e->getMessage(), 500);
                    return;
                }
            }
        }

        // No route found
        $this->sendError('Endpoint not found', 404);
    }

    private function sendError($message, $statusCode = 404) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'error_code' => $statusCode === 404 ? 'NOT_FOUND' : 'INTERNAL_ERROR',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
}

// Initialize router
$router = new Router();

// API Routes

// Health check
$router->addRoute('GET', '/', 'HealthController', 'index');
$router->addRoute('GET', '/health', 'HealthController', 'health');

// Authentication routes
$router->addRoute('POST', '/auth/register', 'AuthController', 'register');
$router->addRoute('POST', '/auth/login', 'AuthController', 'login');
$router->addRoute('POST', '/auth/logout', 'AuthController', 'logout');
$router->addRoute('POST', '/auth/refresh', 'AuthController', 'refreshToken');
$router->addRoute('PUT', '/auth/change-password', 'AuthController', 'changePassword');

// Profile routes
$router->addRoute('GET', '/auth/profile', 'ProfileController', 'getProfile');
$router->addRoute('PUT', '/auth/profile', 'ProfileController', 'updateProfile');
$router->addRoute('POST', '/auth/profile/upload-picture', 'ProfileController', 'uploadProfilePicture');
$router->addRoute('DELETE', '/auth/profile/delete-picture', 'ProfileController', 'deleteProfilePicture');

// Password reset routes
$router->addRoute('POST', '/auth/forgot-password', 'ForgotPasswordController', 'forgotPassword');
$router->addRoute('POST', '/auth/reset-password', 'ForgotPasswordController', 'resetPassword');
$router->addRoute('GET', '/auth/validate-reset-token/{token}', 'ForgotPasswordController', 'validateResetToken');

// Product routes
$router->addRoute('GET', '/products', 'ProductController', 'getAllProducts');
$router->addRoute('GET', '/products/{id}', 'ProductController', 'getProductById');
$router->addRoute('POST', '/products', 'ProductController', 'createProduct');
$router->addRoute('PUT', '/products/{id}', 'ProductController', 'updateProduct');
$router->addRoute('DELETE', '/products/{id}', 'ProductController', 'deleteProduct');
$router->addRoute('GET', '/products/featured', 'ProductController', 'getFeaturedProducts');
$router->addRoute('GET', '/products/search', 'ProductController', 'searchProducts');

// Category routes
$router->addRoute('GET', '/categories', 'ProductController', 'getCategories');

// Order routes
$router->addRoute('POST', '/orders', 'OrderController', 'createOrder');
$router->addRoute('GET', '/orders/{id}', 'OrderController', 'getOrderById');
$router->addRoute('GET', '/orders', 'OrderController', 'getUserOrders');
$router->addRoute('PUT', '/orders/{id}/status', 'OrderController', 'updateOrderStatus');
$router->addRoute('GET', '/orders/all', 'OrderController', 'getAllOrders');
$router->addRoute('GET', '/restaurants/{id}/orders', 'OrderController', 'getRestaurantOrders');

// Restaurant routes
$router->addRoute('GET', '/restaurants', 'RestaurantController', 'getAllRestaurants');
$router->addRoute('GET', '/restaurants/{id}', 'RestaurantController', 'getRestaurantById');
$router->addRoute('POST', '/restaurants', 'RestaurantController', 'createRestaurant');
$router->addRoute('PUT', '/restaurants/{id}', 'RestaurantController', 'updateRestaurant');
$router->addRoute('DELETE', '/restaurants/{id}', 'RestaurantController', 'deleteRestaurant');
$router->addRoute('GET', '/restaurants/merchant/{id}', 'RestaurantController', 'getRestaurantsByMerchant');
$router->addRoute('GET', '/my-restaurants', 'RestaurantController', 'getMyRestaurants');

// Category routes (enhanced)
$router->addRoute('POST', '/categories', 'CategoryController', 'createCategory');
$router->addRoute('PUT', '/categories/{id}', 'CategoryController', 'updateCategory');
$router->addRoute('DELETE', '/categories/{id}', 'CategoryController', 'deleteCategory');
$router->addRoute('GET', '/categories/{id}', 'CategoryController', 'getCategoryById');
$router->addRoute('GET', '/categories/with-count', 'CategoryController', 'getCategoriesWithProductCount');

// User Address routes
$router->addRoute('GET', '/addresses', 'UserAddressController', 'getUserAddresses');
$router->addRoute('GET', '/addresses/{id}', 'UserAddressController', 'getAddressById');
$router->addRoute('POST', '/addresses', 'UserAddressController', 'createAddress');
$router->addRoute('PUT', '/addresses/{id}', 'UserAddressController', 'updateAddress');
$router->addRoute('DELETE', '/addresses/{id}', 'UserAddressController', 'deleteAddress');
$router->addRoute('PUT', '/addresses/{id}/default', 'UserAddressController', 'setDefaultAddress');
$router->addRoute('GET', '/addresses/default', 'UserAddressController', 'getDefaultAddress');

// Transaction routes
$router->addRoute('GET', '/transactions', 'TransactionController', 'getAllTransactions');
$router->addRoute('GET', '/transactions/{id}', 'TransactionController', 'getTransactionById');
$router->addRoute('GET', '/transactions/order/{id}', 'TransactionController', 'getTransactionsByOrder');
$router->addRoute('POST', '/transactions', 'TransactionController', 'createTransaction');
$router->addRoute('PUT', '/transactions/{id}/status', 'TransactionController', 'updateTransactionStatus');
$router->addRoute('GET', '/transactions/stats', 'TransactionController', 'getTransactionStats');

// Notification routes
$router->addRoute('GET', '/notifications', 'NotificationController', 'getUserNotifications');
$router->addRoute('POST', '/notifications', 'NotificationController', 'createNotification');
$router->addRoute('POST', '/notifications/bulk', 'NotificationController', 'createBulkNotifications');
$router->addRoute('PUT', '/notifications/{id}/read', 'NotificationController', 'markAsRead');
$router->addRoute('PUT', '/notifications/read-all', 'NotificationController', 'markAllAsRead');
$router->addRoute('DELETE', '/notifications/{id}', 'NotificationController', 'deleteNotification');
$router->addRoute('GET', '/notifications/unread-count', 'NotificationController', 'getUnreadCount');
$router->addRoute('DELETE', '/notifications/old', 'NotificationController', 'clearOldNotifications');

// Favorite routes
$router->addRoute('GET', '/favorites', 'FavoriteController', 'getUserFavorites');
$router->addRoute('POST', '/favorites', 'FavoriteController', 'addToFavorites');
$router->addRoute('DELETE', '/favorites/{id}', 'FavoriteController', 'removeFromFavorites');
$router->addRoute('PUT', '/favorites/{id}/toggle', 'FavoriteController', 'toggleFavorite');
$router->addRoute('GET', '/favorites/{id}/status', 'FavoriteController', 'isFavorite');
$router->addRoute('GET', '/favorites/count', 'FavoriteController', 'getFavoriteCount');
$router->addRoute('GET', '/favorites/popular', 'FavoriteController', 'getMostFavoritedProducts');
$router->addRoute('DELETE', '/favorites/clear', 'FavoriteController', 'clearUserFavorites');

// Dispatch the request
try {
    $router->dispatch();
} catch (Exception $e) {
    logError('Router dispatch error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error_code' => 'INTERNAL_ERROR',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}