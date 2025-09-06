<?php
/**
 * Profile Controller for IKIRAHA API
 * Handles user profile management operations
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ProfileController {
    private $user;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    /**
     * Get user profile
     * GET /auth/profile
     */
    public function getProfile() {
        try {
            // Log the request for debugging
            error_log("[ProfileController] getProfile() called at " . date('Y-m-d H:i:s'));
            error_log("[ProfileController] Request method: " . $_SERVER['REQUEST_METHOD']);
            error_log("[ProfileController] Request URI: " . $_SERVER['REQUEST_URI']);

            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                error_log("[ProfileController] Authorization header: " . substr($headers['Authorization'], 0, 50) . "...");
            } else {
                error_log("[ProfileController] No Authorization header found");
            }

            // Authenticate user
            if (!AuthMiddleware::authenticate()) {
                error_log("[ProfileController] Authentication failed");
                return;
            }

            $currentUser = $GLOBALS['current_user'];
            $userId = $currentUser['user_id'];

            // Get user data
            $userData = $this->user->findById($userId);
            
            if (!$userData) {
                $this->sendError('User not found', 404);
                return;
            }

            // Remove sensitive data
            unset($userData['password_hash']);

            $this->sendSuccess([
                'user' => $userData,
                'message' => 'Profile retrieved successfully'
            ]);

        } catch (Exception $e) {
            error_log("Get profile error: " . $e->getMessage());
            $this->sendError('An error occurred while retrieving profile', 500);
        }
    }

    /**
     * Update user profile
     * PUT /auth/profile
     */
    public function updateProfile() {
        try {
            // Authenticate user
            if (!AuthMiddleware::authenticate()) {
                return;
            }

            $currentUser = $GLOBALS['current_user'];
            $userId = $currentUser['user_id'];

            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            // Sanitize input
            $data = sanitizeInput($data);

            // Validate required fields
            if (empty($data['name'])) {
                $this->sendError('Name is required', 400);
                return;
            }

            // Prepare update data
            $updateData = [
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'bio' => $data['bio'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Validate gender if provided
            if (!empty($updateData['gender']) && !in_array($updateData['gender'], ['male', 'female', 'other'])) {
                $this->sendError('Invalid gender value', 400);
                return;
            }

            // Validate date of birth if provided
            if (!empty($updateData['date_of_birth'])) {
                $date = DateTime::createFromFormat('Y-m-d', $updateData['date_of_birth']);
                if (!$date || $date->format('Y-m-d') !== $updateData['date_of_birth']) {
                    $this->sendError('Invalid date of birth format. Use YYYY-MM-DD', 400);
                    return;
                }
            }

            // Update user profile
            $result = $this->user->updateProfile($userId, $updateData);

            if (!$result) {
                $this->sendError('Failed to update profile', 500);
                return;
            }

            // Get updated user data
            $userData = $this->user->findById($userId);
            unset($userData['password_hash']);

            $this->sendSuccess([
                'user' => $userData,
                'message' => 'Profile updated successfully'
            ]);

        } catch (Exception $e) {
            error_log("Update profile error: " . $e->getMessage());
            $this->sendError('An error occurred while updating profile', 500);
        }
    }

    /**
     * Upload profile picture
     * POST /auth/profile/upload-picture
     */
    public function uploadProfilePicture() {
        try {
            // Authenticate user
            if (!AuthMiddleware::authenticate()) {
                return;
            }

            $currentUser = $GLOBALS['current_user'];
            $userId = $currentUser['user_id'];

            // Check if file was uploaded
            if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
                $this->sendError('No file uploaded or upload error', 400);
                return;
            }

            $file = $_FILES['profile_picture'];

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                $this->sendError('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed', 400);
                return;
            }

            // Validate file size (max 5MB)
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $maxSize) {
                $this->sendError('File too large. Maximum size is 5MB', 400);
                return;
            }

            // Create upload directory if it doesn't exist
            $uploadDir = 'uploads/profile_pictures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                $this->sendError('Failed to save uploaded file', 500);
                return;
            }

            // Delete old profile picture if exists
            $oldUserData = $this->user->findById($userId);
            if ($oldUserData && $oldUserData['profile_image']) {
                $oldFilePath = $oldUserData['profile_image'];
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // Update user profile with new image path
            $result = $this->user->updateProfile($userId, [
                'profile_image' => $filepath,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                // Clean up uploaded file if database update fails
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                $this->sendError('Failed to update profile picture', 500);
                return;
            }

            // Get updated user data
            $userData = $this->user->findById($userId);
            unset($userData['password_hash']);

            $this->sendSuccess([
                'user' => $userData,
                'message' => 'Profile picture uploaded successfully'
            ]);

        } catch (Exception $e) {
            error_log("Upload profile picture error: " . $e->getMessage());
            $this->sendError('An error occurred while uploading profile picture', 500);
        }
    }

    /**
     * Delete profile picture
     * DELETE /auth/profile/delete-picture
     */
    public function deleteProfilePicture() {
        try {
            // Authenticate user
            if (!AuthMiddleware::authenticate()) {
                return;
            }

            $currentUser = $GLOBALS['current_user'];
            $userId = $currentUser['user_id'];

            // Get current user data
            $userData = $this->user->findById($userId);
            
            if (!$userData) {
                $this->sendError('User not found', 404);
                return;
            }

            // Delete profile picture file if exists
            if ($userData['profile_image']) {
                $filePath = $userData['profile_image'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Update user profile to remove image path
            $result = $this->user->updateProfile($userId, [
                'profile_image' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                $this->sendError('Failed to delete profile picture', 500);
                return;
            }

            // Get updated user data
            $userData = $this->user->findById($userId);
            unset($userData['password_hash']);

            $this->sendSuccess([
                'user' => $userData,
                'message' => 'Profile picture deleted successfully'
            ]);

        } catch (Exception $e) {
            error_log("Delete profile picture error: " . $e->getMessage());
            $this->sendError('An error occurred while deleting profile picture', 500);
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
    }

    /**
     * Get error code based on status code
     */
    private function getErrorCode($statusCode) {
        switch ($statusCode) {
            case 400: return 'BAD_REQUEST';
            case 401: return 'UNAUTHORIZED';
            case 403: return 'FORBIDDEN';
            case 404: return 'NOT_FOUND';
            case 500: return 'INTERNAL_SERVER_ERROR';
            default: return 'UNKNOWN_ERROR';
        }
    }
}
