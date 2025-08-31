<?php
/**
 * Authentication Middleware for IKIRAHA API
 * Handles JWT token validation and role-based access control
 */

class AuthMiddleware {

    /**
     * Authenticate user with JWT token
     */
    public static function authenticate() {
        try {
            $token = JWT::getBearerToken();

            if (!$token) {
                self::sendUnauthorized('Access token required');
                return false;
            }

            $decoded = JWT::validateToken($token);

            if ($decoded['type'] !== 'access') {
                self::sendUnauthorized('Invalid token type');
                return false;
            }

            // Store user data in global variable for use in controllers
            $GLOBALS['current_user'] = $decoded;

            return true;

        } catch (Exception $e) {
            self::sendUnauthorized($e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has required role
     */
    public static function requireRole($requiredRoles) {
        if (!self::authenticate()) {
            return false;
        }

        $userRole = $GLOBALS['current_user']['role'];

        if (!in_array($userRole, $requiredRoles)) {
            self::sendForbidden('Insufficient permissions');
            return false;
        }

        return true;
    }

    /**
     * Check if user is client
     */
    public static function requireClient() {
        return self::requireRole(['client']);
    }

    /**
     * Check if user is merchant
     */
    public static function requireMerchant() {
        return self::requireRole(['merchant']);
    }

    /**
     * Check if user is accountant
     */
    public static function requireAccountant() {
        return self::requireRole(['accountant']);
    }

    /**
     * Check if user is super admin
     */
    public static function requireSuperAdmin() {
        return self::requireRole(['super_admin']);
    }

    /**
     * Check if user is merchant or super admin
     */
    public static function requireMerchantOrAdmin() {
        return self::requireRole(['merchant', 'super_admin']);
    }

    /**
     * Check if user is accountant or super admin
     */
    public static function requireAccountantOrAdmin() {
        return self::requireRole(['accountant', 'super_admin']);
    }

    /**
     * Get current authenticated user
     */
    public static function getCurrentUser() {
        return isset($GLOBALS['current_user']) ? $GLOBALS['current_user'] : null;
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        $user = self::getCurrentUser();
        return $user ? $user['user_id'] : null;
    }

    /**
     * Get current user role
     */
    public static function getCurrentUserRole() {
        $user = self::getCurrentUser();
        return $user ? $user['role'] : null;
    }

    /**
     * Send unauthorized response
     */
    private static function sendUnauthorized($message = 'Unauthorized') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'error_code' => 'UNAUTHORIZED'
        ]);
        exit;
    }

    /**
     * Send forbidden response
     */
    private static function sendForbidden($message = 'Forbidden') {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'error_code' => 'FORBIDDEN'
        ]);
        exit;
    }
}
?>