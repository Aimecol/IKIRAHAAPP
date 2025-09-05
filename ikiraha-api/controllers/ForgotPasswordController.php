<?php
/**
 * Forgot Password Controller for IKIRAHA API
 * Handles password reset functionality with email verification
 */

class ForgotPasswordController
{
    private $db;
    private $user;
    private $passwordReset;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
        $this->passwordReset = new PasswordReset($this->db);
    }

    /**
     * Send password reset email
     * POST /auth/forgot-password
     */
    public function forgotPassword()
    {
        try {
            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            // Sanitize input
            $data = sanitizeInput($data);
            
            // Validate input
            if (empty($data['email'])) {
                $this->sendError('Email is required', 400);
                return;
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->sendError('Invalid email format', 400);
                return;
            }

            $email = trim(strtolower($data['email']));

            // Rate limiting - max 3 attempts per email per hour
            if (!$this->checkRateLimit($email)) {
                $this->sendError('Too many password reset attempts. Please try again later.', 429);
                return;
            }

            // Check if user exists
            $userData = $this->user->findByEmail($email);
            
            // Always return success to prevent email enumeration
            // But only send email if user exists
            if ($userData) {
                // Generate secure reset token
                $token = $this->generateSecureToken();
                $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry

                // Store reset token
                $resetData = [
                    'user_id' => $userData['id'],
                    'email' => $email,
                    'token' => hash('sha256', $token), // Store hashed token
                    'expires_at' => $expiresAt,
                    'created_at' => date('Y-m-d H:i:s'),
                    'used' => 0
                ];

                // Delete any existing reset tokens for this user
                $this->passwordReset->deleteByUserId($userData['id']);
                
                // Create new reset token
                $this->passwordReset->create($resetData);

                // Send reset email
                $resetLink = $this->generateResetLink($token);
                $emailSent = $this->sendPasswordResetEmail(
                    $email,
                    $userData['name'],
                    $resetLink
                );

                if (!$emailSent) {
                    error_log("Failed to send password reset email to: $email");
                }
            }

            $this->sendSuccess([
                'message' => 'If an account with that email exists, we have sent a password reset link.'
            ]);

        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $this->sendError('An error occurred while processing your request', 500);
        }
    }

    /**
     * Reset password with token
     * POST /auth/reset-password
     */
    public function resetPassword()
    {
        try {
            // Get POST data
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!$data) {
                $this->sendError('Invalid JSON data', 400);
                return;
            }

            // Sanitize input
            $data = sanitizeInput($data);
            
            // Validate input
            if (empty($data['token'])) {
                $this->sendError('Reset token is required', 400);
                return;
            }

            if (empty($data['password'])) {
                $this->sendError('Password is required', 400);
                return;
            }

            if (empty($data['password_confirmation'])) {
                $this->sendError('Password confirmation is required', 400);
                return;
            }

            if (strlen($data['password']) < 6) {
                $this->sendError('Password must be at least 6 characters long', 400);
                return;
            }

            $token = $data['token'];
            $password = $data['password'];
            $passwordConfirmation = $data['password_confirmation'];

            // Check password confirmation
            if ($password !== $passwordConfirmation) {
                $this->sendError('Password confirmation does not match', 400);
                return;
            }

            // Find reset token (search by hashed token)
            $hashedToken = hash('sha256', $token);
            $resetRecord = $this->passwordReset->findByToken($hashedToken);

            if (!$resetRecord) {
                $this->sendError('Invalid or expired reset token', 400);
                return;
            }

            // Check if token is expired
            if (strtotime($resetRecord['expires_at']) < time()) {
                // Delete expired token
                $this->passwordReset->delete($resetRecord['id']);
                $this->sendError('Reset token has expired', 400);
                return;
            }

            // Check if token is already used
            if ($resetRecord['used']) {
                $this->sendError('Reset token has already been used', 400);
                return;
            }

            // Get user
            $userData = $this->user->findById($resetRecord['user_id']);
            if (!$userData) {
                $this->sendError('User not found', 404);
                return;
            }

            // Update password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateResult = $this->user->updatePassword($userData['id'], $hashedPassword);

            if (!$updateResult) {
                $this->sendError('Failed to update password', 500);
                return;
            }

            // Mark token as used
            $this->passwordReset->markAsUsed($resetRecord['id']);

            // Delete all reset tokens for this user
            $this->passwordReset->deleteByUserId($userData['id']);

            $this->sendSuccess([
                'message' => 'Password has been reset successfully. You can now login with your new password.'
            ]);

        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            $this->sendError('An error occurred while resetting your password', 500);
        }
    }

    /**
     * Validate reset token
     * GET /auth/validate-reset-token/{token}
     */
    public function validateResetToken($token)
    {
        try {
            if (empty($token)) {
                $this->sendError('Token is required', 400);
                return;
            }

            // Find reset token
            $hashedToken = hash('sha256', $token);
            $resetRecord = $this->passwordReset->findByToken($hashedToken);

            if (!$resetRecord) {
                $this->sendError('Invalid reset token', 400);
                return;
            }

            // Check if token is expired
            if (strtotime($resetRecord['expires_at']) < time()) {
                $this->passwordReset->delete($resetRecord['id']);
                $this->sendError('Reset token has expired', 400);
                return;
            }

            // Check if token is already used
            if ($resetRecord['used']) {
                $this->sendError('Reset token has already been used', 400);
                return;
            }

            $this->sendSuccess([
                'message' => 'Token is valid',
                'email' => $resetRecord['email']
            ]);

        } catch (Exception $e) {
            error_log("Validate reset token error: " . $e->getMessage());
            $this->sendError('An error occurred while validating the token', 500);
        }
    }

    /**
     * Generate secure random token
     */
    private function generateSecureToken()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generate password reset link
     */
    private function generateResetLink($token)
    {
        $baseUrl = 'http://localhost/ikirahaapp';
        return $baseUrl . '/#/reset-password?token=' . urlencode($token);
    }

    /**
     * Check rate limiting for password reset attempts
     */
    private function checkRateLimit($email)
    {
        // Simple file-based rate limiting
        $rateLimitFile = sys_get_temp_dir() . '/ikiraha_rate_limit_' . md5($email);
        $maxAttempts = 3;
        $timeWindow = 3600; // 1 hour
        
        $attempts = [];
        if (file_exists($rateLimitFile)) {
            $content = file_get_contents($rateLimitFile);
            $attempts = json_decode($content, true) ?: [];
        }
        
        // Clean up old attempts
        $currentTime = time();
        $attempts = array_filter($attempts, function($timestamp) use ($currentTime, $timeWindow) {
            return ($currentTime - $timestamp) < $timeWindow;
        });
        
        // Check if limit exceeded
        if (count($attempts) >= $maxAttempts) {
            return false;
        }
        
        // Add current attempt
        $attempts[] = $currentTime;
        file_put_contents($rateLimitFile, json_encode($attempts), LOCK_EX);
        
        return true;
    }

    /**
     * Send password reset email using simple mail function
     */
    private function sendPasswordResetEmail($toEmail, $userName, $resetLink)
    {
        $subject = 'Reset Your IKIRAHA Password';
        
        $message = "
Hello $userName,

We received a request to reset your IKIRAHA account password. If you made this request, click the link below to reset your password:

$resetLink

IMPORTANT SECURITY INFORMATION:
- This link will expire in 1 hour
- This link can only be used once
- If you didn't request this reset, please ignore this email
- Never share this link with anyone

If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.

Best regards,
The IKIRAHA Team

---
This is an automated message from IKIRAHA Food Delivery.
© " . date('Y') . " IKIRAHA. All rights reserved.
        ";

        $headers = "From: IKIRAHA <noreply@ikiraha.com>\r\n";
        $headers .= "Reply-To: support@ikiraha.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        return mail($toEmail, $subject, $message, $headers);
    }

    /**
     * Send success response
     */
    private function sendSuccess($data, $statusCode = 200)
    {
        http_response_code($statusCode);
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
    private function sendError($message, $statusCode = 400)
    {
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
    private function getErrorCode($statusCode)
    {
        switch ($statusCode) {
            case 400: return 'BAD_REQUEST';
            case 401: return 'UNAUTHORIZED';
            case 404: return 'NOT_FOUND';
            case 429: return 'TOO_MANY_REQUESTS';
            case 500: return 'INTERNAL_ERROR';
            default: return 'UNKNOWN_ERROR';
        }
    }
}
