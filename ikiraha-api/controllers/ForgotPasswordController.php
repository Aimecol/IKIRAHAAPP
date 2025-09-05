<?php
/**
 * Forgot Password Controller for IKIRAHA API
 * Handles password reset functionality with email verification
 */

require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
     * Send password reset email using PHPMailer with Gmail SMTP
     */
    private function sendPasswordResetEmail($toEmail, $userName, $resetLink)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'aimecol314@gmail.com';
            $mail->Password   = 'dpol bvhx ovmo tvrx';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('aimecol314@gmail.com', 'IKIRAHA Food Delivery');
            $mail->addAddress($toEmail, $userName);
            $mail->addReplyTo('aimecol314@gmail.com', 'IKIRAHA Support');

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Reset Your IKIRAHA Password';

            // HTML email template
            $htmlMessage = $this->getPasswordResetEmailTemplate($userName, $resetLink);
            $mail->Body = $htmlMessage;

            // Plain text alternative
            $plainMessage = $this->getPasswordResetEmailPlainText($userName, $resetLink);
            $mail->AltBody = $plainMessage;

            // Additional headers to prevent spam
            $mail->addCustomHeader('X-Mailer', 'IKIRAHA Password Reset System');
            $mail->addCustomHeader('X-Priority', '1');
            $mail->addCustomHeader('X-MSMail-Priority', 'High');
            $mail->addCustomHeader('Importance', 'High');

            $mail->send();
            return true;

        } catch (Exception $e) {
            // Log the error for debugging
            error_log("Password reset email failed: {$mail->ErrorInfo}");

            // Fallback to simple mail function
            return $this->sendPasswordResetEmailFallback($toEmail, $userName, $resetLink);
        }
    }

    /**
     * Get HTML email template for password reset
     */
    private function getPasswordResetEmailTemplate($userName, $resetLink)
    {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Reset Your IKIRAHA Password</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #FF6B35; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background-color: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; background-color: #FF6B35; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .security-info { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🍽️ IKIRAHA</h1>
                <p>Password Reset Request</p>
            </div>
            <div class='content'>
                <h2>Hello $userName,</h2>
                <p>We received a request to reset your IKIRAHA account password. If you made this request, click the button below to reset your password:</p>

                <div style='text-align: center;'>
                    <a href='$resetLink' class='button'>Reset My Password</a>
                </div>

                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; background-color: #f0f0f0; padding: 10px; border-radius: 3px;'>$resetLink</p>

                <div class='security-info'>
                    <h3>🔒 Important Security Information:</h3>
                    <ul>
                        <li>This link will expire in <strong>1 hour</strong></li>
                        <li>This link can only be used <strong>once</strong></li>
                        <li>If you didn't request this reset, please ignore this email</li>
                        <li>Never share this link with anyone</li>
                    </ul>
                </div>

                <p>If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>

                <p>Best regards,<br>
                <strong>The IKIRAHA Team</strong></p>
            </div>
            <div class='footer'>
                <p>This is an automated message from IKIRAHA Food Delivery.</p>
                <p>© " . date('Y') . " IKIRAHA. All rights reserved.</p>
                <p>Need help? Contact us at aimecol314@gmail.com</p>
            </div>
        </body>
        </html>";
    }

    /**
     * Get plain text email template for password reset
     */
    private function getPasswordResetEmailPlainText($userName, $resetLink)
    {
        return "
IKIRAHA - Password Reset Request

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
Need help? Contact us at aimecol314@gmail.com
        ";
    }

    /**
     * Fallback email method using simple mail function
     */
    private function sendPasswordResetEmailFallback($toEmail, $userName, $resetLink)
    {
        $subject = 'Reset Your IKIRAHA Password';

        $message = $this->getPasswordResetEmailPlainText($userName, $resetLink);

        $headers = "From: IKIRAHA <aimecol314@gmail.com>\r\n";
        $headers .= "Reply-To: aimecol314@gmail.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: IKIRAHA Password Reset System\r\n";
        $headers .= "X-Priority: 1\r\n";

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
