<?php
/**
 * User Model for IKIRAHA API
 * Handles user authentication, registration, and profile management
 */

class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Register new user
     */
    public function register($data) {
        try {
            // Validate input
            if (!$this->validateRegistrationData($data)) {
                throw new Exception('Invalid registration data');
            }

            // Check if email already exists
            if ($this->emailExists($data['email'])) {
                throw new Exception('Email already registered');
            }

            $query = "INSERT INTO " . $this->table_name . "
                     (uuid, email, password_hash, name, phone, role, status, email_verified)
                     VALUES (:uuid, :email, :password_hash, :name, :phone, :role, :status, :email_verified)";

            $stmt = $this->conn->prepare($query);

            // Generate UUID and hash password
            $uuid = generateUUID();
            $password_hash = password_hash($data['password'], PASSWORD_HASH_ALGO);

            $stmt->bindParam(':uuid', $uuid);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':email_verified', $data['email_verified']);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'user_id' => $this->conn->lastInsertId(),
                    'uuid' => $uuid
                ];
            }

            throw new Exception('Registration failed');

        } catch (Exception $e) {
            logError('User registration failed: ' . $e->getMessage(), $data);
            throw $e;
        }
    }

    /**
     * Authenticate user login
     */
    public function login($email, $password) {
        try {
            $query = "SELECT id, uuid, email, password_hash, name, phone, role, status, email_verified
                     FROM " . $this->table_name . "
                     WHERE email = :email AND status = 'active'";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new Exception('Invalid credentials');
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($password, $user['password_hash'])) {
                throw new Exception('Invalid credentials');
            }

            // Generate tokens
            $accessToken = JWT::generateAccessToken($user['id'], $user['role'], $user['email']);
            $refreshToken = JWT::generateRefreshToken($user['id']);

            // Store refresh token in database
            $this->storeRefreshToken($user['id'], $refreshToken);

            // Remove password hash from response
            unset($user['password_hash']);

            return [
                'success' => true,
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => JWT_EXPIRY
            ];

        } catch (Exception $e) {
            logError('User login failed: ' . $e->getMessage(), ['email' => $email]);
            throw $e;
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        try {
            $query = "SELECT id, uuid, email, name, phone, role, status, email_verified, profile_image, created_at, updated_at
                     FROM " . $this->table_name . "
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return null;
            }

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logError('Get user by ID failed: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['name', 'phone', 'profile_image', 'address', 'date_of_birth', 'gender', 'bio'];
            $updateFields = [];
            $params = [':id' => $userId];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            if (empty($updateFields)) {
                throw new Exception('No valid fields to update');
            }

            $query = "UPDATE " . $this->table_name . "
                     SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                return $this->findById($userId);
            }

            throw new Exception('Profile update failed');

        } catch (Exception $e) {
            logError('Profile update failed: ' . $e->getMessage(), ['user_id' => $userId, 'data' => $data]);
            throw $e;
        }
    }

    /**
     * Check if email exists
     */
    private function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Validate registration data
     */
    private function validateRegistrationData($data) {
        $required = ['email', 'password', 'name'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return false;
            }
        }

        if (!$this->validateEmail($data['email'])) {
            return false;
        }

        if (strlen($data['password']) < 6) { // PASSWORD_MIN_LENGTH
            return false;
        }

        if (isset($data['phone']) && !empty($data['phone']) && !$this->validatePhone($data['phone'])) {
            return false;
        }

        return true;
    }

    /**
     * Store refresh token
     */
    private function storeRefreshToken($userId, $token) {
        try {
            // Delete existing refresh tokens for this user
            $deleteQuery = "DELETE FROM auth_tokens WHERE user_id = :user_id AND type = 'refresh'";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':user_id', $userId);
            $deleteStmt->execute();

            // Insert new refresh token
            $insertQuery = "INSERT INTO auth_tokens (user_id, token, type, expires_at)
                           VALUES (:user_id, :token, 'refresh', :expires_at)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $expiresAt = date('Y-m-d H:i:s', time() + JWT_REFRESH_EXPIRY);

            $insertStmt->bindParam(':user_id', $userId);
            $insertStmt->bindParam(':token', $token);
            $insertStmt->bindParam(':expires_at', $expiresAt);

            return $insertStmt->execute();

        } catch (Exception $e) {
            logError('Store refresh token failed: ' . $e->getMessage(), ['user_id' => $userId]);
            return false;
        }
    }

    /**
     * Get all users (admin only)
     */
    public function getAllUsers($limit = 50, $offset = 0) {
        try {
            $query = "SELECT id, uuid, email, name, phone, role, status, email_verified, created_at, updated_at
                     FROM " . $this->table_name . "
                     ORDER BY created_at DESC
                     LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logError('Get all users failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Change user password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Verify current password
            $query = "SELECT password_hash FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                throw new Exception('User not found');
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($currentPassword, $user['password_hash'])) {
                throw new Exception('Current password is incorrect');
            }

            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                throw new Exception('New password is too short');
            }

            // Update password
            $updateQuery = "UPDATE " . $this->table_name . "
                           SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP
                           WHERE id = :id";

            $updateStmt = $this->conn->prepare($updateQuery);
            $newPasswordHash = password_hash($newPassword, PASSWORD_HASH_ALGO);

            $updateStmt->bindParam(':password_hash', $newPasswordHash);
            $updateStmt->bindParam(':id', $userId);

            if ($updateStmt->execute()) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            }

            throw new Exception('Password change failed');

        } catch (Exception $e) {
            logError('Change password failed: ' . $e->getMessage(), ['user_id' => $userId]);
            throw $e;
        }
    }

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        try {
            $query = "SELECT id, uuid, email, name, phone, role, status, email_verified, created_at
                     FROM " . $this->table_name . "
                     WHERE email = :email";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Find by email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user by ID
     */
    public function findById($id) {
        try {
            $query = "SELECT id, uuid, email, name, phone, role, status, email_verified, profile_image,
                            address, date_of_birth, gender, bio, created_at, updated_at
                     FROM " . $this->table_name . "
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Find by ID error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user password
     */
    public function updatePassword($userId, $hashedPassword) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET password_hash = :password_hash, updated_at = NOW()
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password_hash', $hashedPassword);
            $stmt->bindParam(':id', $userId);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Update password error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate email format
     */
    private function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number format
     */
    private function validatePhone($phone) {
        // Basic phone validation - adjust regex as needed
        return preg_match('/^\+?[\d\s\-\(\)]+$/', $phone);
    }
}

// Helper functions
if (!function_exists('generateUUID')) {
    function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

if (!function_exists('logError')) {
    function logError($message, $context = []) {
        error_log($message . ' Context: ' . json_encode($context));
    }
}

// Constants
if (!defined('PASSWORD_HASH_ALGO')) {
    define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);
}

if (!defined('PASSWORD_MIN_LENGTH')) {
    define('PASSWORD_MIN_LENGTH', 6);
}

if (!defined('JWT_EXPIRY')) {
    define('JWT_EXPIRY', 3600); // 1 hour
}

if (!defined('JWT_REFRESH_EXPIRY')) {
    define('JWT_REFRESH_EXPIRY', 604800); // 1 week
}