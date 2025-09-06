<?php
/**
 * Authentication Controller for IKIRAHA API
 * Handles user registration, login, logout, and token management
 */

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    /**
     * Handle user registration
     */
    public function register() {
        try {
            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            // Sanitize input
            $data = sanitizeInput($data);

            // Set default values
            $data['role'] = isset($data['role']) ? $data['role'] : 'client';
            $data['status'] = 'active';
            $data['email_verified'] = 0;

            // Validate role
            $validRoles = ['client', 'merchant'];
            if (!in_array($data['role'], $validRoles)) {
                $this->sendError('Invalid role. Only client and merchant registration allowed', 400);
                return;
            }

            // Register user
            $result = $this->user->register($data);

            if ($result['success']) {
                $this->sendSuccess([
                    'message' => 'Registration successful',
                    'user_id' => $result['user_id'],
                    'uuid' => $result['uuid']
                ], 201);
            } else {
                $this->sendError('Registration failed', 500);
            }

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Handle user login
     */
    public function login() {
        try {
            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data || !isset($data['email']) || !isset($data['password'])) {
                $this->sendError('Email and password are required', 400);
                return;
            }

            // Sanitize input
            $email = sanitizeInput($data['email']);
            $password = $data['password']; // Don't sanitize password

            // Attempt login
            $result = $this->user->login($email, $password);

            if ($result['success']) {
                $this->sendSuccess([
                    'message' => 'Login successful',
                    'user' => $result['user'],
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                    'expires_in' => $result['expires_in']
                ]);
            } else {
                $this->sendError('Login failed', 401);
            }

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 401);
        }
    }

    /**
     * Handle token refresh
     */
    public function refreshToken() {
        try {
            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data || !isset($data['refresh_token'])) {
                $this->sendError('Refresh token is required', 400);
                return;
            }

            // Validate refresh token
            $decoded = JWT::validateToken($data['refresh_token']);

            if ($decoded['type'] !== 'refresh') {
                $this->sendError('Invalid token type', 400);
                return;
            }

            // Get user data
            $userData = $this->user->getUserById($decoded['user_id']);

            if (!$userData) {
                $this->sendError('User not found', 404);
                return;
            }

            // Generate new access token
            $accessToken = JWT::generateAccessToken($userData['id'], $userData['role'], $userData['email']);

            $this->sendSuccess([
                'message' => 'Token refreshed successfully',
                'access_token' => $accessToken,
                'expires_in' => JWT_EXPIRY
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 401);
        }
    }

    /**
     * Handle user logout
     */
    public function logout() {
        try {
            if (!AuthMiddleware::authenticate()) {
                return;
            }

            $currentUser = AuthMiddleware::getCurrentUser();

            // Delete refresh tokens for this user
            $query = "DELETE FROM auth_tokens WHERE user_id = :user_id AND type = 'refresh'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $currentUser['user_id']);
            $stmt->execute();

            $this->sendSuccess(['message' => 'Logout successful']);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Get current user profile
     */
    public function getProfile() {
        try {
            if (!AuthMiddleware::authenticate()) {
                return;
            }

            $currentUser = AuthMiddleware::getCurrentUser();
            $userData = $this->user->getUserById($currentUser['user_id']);

            if (!$userData) {
                $this->sendError('User not found', 404);
                return;
            }

            $this->sendSuccess([
                'message' => 'Profile retrieved successfully',
                'user' => $userData
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile() {
        try {
            if (!AuthMiddleware::authenticate()) {
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
            $result = $this->user->updateProfile($currentUser['user_id'], $data);

            $this->sendSuccess([
                'message' => 'Profile updated successfully',
                'user' => $result
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Change user password
     */
    public function changePassword() {
        try {
            if (!AuthMiddleware::authenticate()) {
                return;
            }

            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data || !isset($data['current_password']) || !isset($data['new_password'])) {
                $this->sendError('Current password and new password are required', 400);
                return;
            }

            $currentUser = AuthMiddleware::getCurrentUser();
            $result = $this->user->changePassword(
                $currentUser['user_id'],
                $data['current_password'],
                $data['new_password']
            );

            $this->sendSuccess($result);

        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400);
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