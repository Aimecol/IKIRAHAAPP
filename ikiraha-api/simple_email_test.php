<?php
/**
 * Simple Email Test for IKIRAHA
 * Tests SMTP connection and email sending
 */

require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "🧪 IKIRAHA Simple Email Test\n";
echo "============================\n\n";

// Configuration
$testEmail = 'aimecol314@gmail.com'; // Change this to test with different email
$smtpHost = 'smtp.gmail.com';
$smtpUsername = 'aimecol314@gmail.com';
$smtpPassword = 'dpol bvhx ovmo tvrx';
$smtpPort = 587;

// Test 1: SMTP Connection
echo "📧 Test 1: Testing SMTP Connection...\n";
$connectionResult = testSMTPConnection($smtpHost, $smtpUsername, $smtpPassword, $smtpPort);

if ($connectionResult) {
    echo "✅ SMTP connection successful!\n\n";
    
    // Test 2: Send Test Email
    echo "📧 Test 2: Sending test email to $testEmail...\n";
    $emailResult = sendTestEmail($testEmail, $smtpHost, $smtpUsername, $smtpPassword, $smtpPort);
    
    if ($emailResult) {
        echo "✅ Test email sent successfully!\n";
        echo "📬 Please check your inbox (and spam folder) for the test email.\n\n";
    } else {
        echo "❌ Failed to send test email.\n\n";
    }
} else {
    echo "❌ SMTP connection failed. Please check your credentials and network.\n\n";
}

// Test 3: Send Password Reset Style Email
echo "📧 Test 3: Sending password reset style email...\n";
$resetResult = sendPasswordResetTestEmail($testEmail, $smtpHost, $smtpUsername, $smtpPassword, $smtpPort);

if ($resetResult) {
    echo "✅ Password reset style email sent successfully!\n";
} else {
    echo "❌ Failed to send password reset style email.\n";
}

echo "\n🔍 Diagnostic Information:\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHPMailer Version: " . PHPMailer::VERSION . "\n";
echo "OpenSSL Available: " . (extension_loaded('openssl') ? 'Yes' : 'No') . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";

echo "\n✅ Email testing completed!\n";

/**
 * Test SMTP connection
 */
function testSMTPConnection($host, $username, $password, $port)
{
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $port;
        
        // Test connection
        return $mail->smtpConnect();
        
    } catch (Exception $e) {
        echo "Connection Error: {$e->getMessage()}\n";
        return false;
    }
}

/**
 * Send a simple test email
 */
function sendTestEmail($toEmail, $host, $username, $password, $port)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $port;

        // Recipients
        $mail->setFrom($username, 'IKIRAHA Test System');
        $mail->addAddress($toEmail, 'Test User');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'IKIRAHA Email Test - ' . date('Y-m-d H:i:s');
        $mail->Body = '
        <h2>🧪 IKIRAHA Email Test</h2>
        <p>This is a test email from the IKIRAHA system.</p>
        <p><strong>Test Details:</strong></p>
        <ul>
            <li>Timestamp: ' . date('Y-m-d H:i:s') . '</li>
            <li>PHP Version: ' . PHP_VERSION . '</li>
            <li>PHPMailer Version: ' . PHPMailer::VERSION . '</li>
        </ul>
        <p style="color: green;"><strong>✅ If you received this email, the SMTP configuration is working correctly!</strong></p>
        ';
        $mail->AltBody = 'IKIRAHA Email Test - If you received this email, the SMTP configuration is working correctly!';

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        echo "Email Error: {$mail->ErrorInfo}\n";
        return false;
    }
}

/**
 * Send password reset style email
 */
function sendPasswordResetTestEmail($toEmail, $host, $username, $password, $port)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $port;

        // Recipients
        $mail->setFrom($username, 'IKIRAHA Food Delivery');
        $mail->addAddress($toEmail, 'Test User');
        $mail->addReplyTo($username, 'IKIRAHA Support');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your IKIRAHA Password - TEST';
        
        $resetLink = 'https://your-app.com/reset-password?token=test-token-123';
        
        $mail->Body = "
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
                .test-notice { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0; color: #155724; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🍽️ IKIRAHA</h1>
                <p>Password Reset Request</p>
            </div>
            <div class='content'>
                <div class='test-notice'>
                    <strong>🧪 THIS IS A TEST EMAIL</strong><br>
                    This is a test of the password reset email functionality. Do not click the reset link below.
                </div>
                
                <h2>Hello Test User,</h2>
                <p>We received a request to reset your IKIRAHA account password. If you made this request, click the button below to reset your password:</p>
                
                <div style='text-align: center;'>
                    <a href='$resetLink' class='button'>Reset My Password (TEST)</a>
                </div>
                
                <div class='security-info'>
                    <h3>🔒 Important Security Information:</h3>
                    <ul>
                        <li>This link will expire in <strong>1 hour</strong></li>
                        <li>This link can only be used <strong>once</strong></li>
                        <li>If you didn't request this reset, please ignore this email</li>
                        <li>Never share this link with anyone</li>
                    </ul>
                </div>
                
                <p>Best regards,<br>
                <strong>The IKIRAHA Team</strong></p>
            </div>
            <div class='footer'>
                <p>This is a test email from IKIRAHA Food Delivery.</p>
                <p>© " . date('Y') . " IKIRAHA. All rights reserved.</p>
                <p>Need help? Contact us at $username</p>
            </div>
        </body>
        </html>";
        
        $mail->AltBody = "
IKIRAHA - Password Reset Request (TEST)

Hello Test User,

This is a test email for the password reset functionality.

Reset Link (TEST): $resetLink

IMPORTANT SECURITY INFORMATION:
- This link will expire in 1 hour
- This link can only be used once
- If you didn't request this reset, please ignore this email

Best regards,
The IKIRAHA Team
        ";

        // Additional headers to prevent spam
        $mail->addCustomHeader('X-Mailer', 'IKIRAHA Password Reset System');
        $mail->addCustomHeader('X-Priority', '1');

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        echo "Password Reset Email Error: {$mail->ErrorInfo}\n";
        return false;
    }
}
?>
