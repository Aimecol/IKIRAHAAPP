<?php
/**
 * Email Testing Script for IKIRAHA Forgot Password System
 * This script tests the email functionality independently
 */

require_once 'vendor/autoload.php';
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'controllers/ForgotPasswordController.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "🧪 IKIRAHA Email Testing Script\n";
echo "================================\n\n";

// Test 1: Basic SMTP Connection Test
echo "📧 Test 1: Testing SMTP Connection...\n";
testSMTPConnection();

// Test 2: Send Test Email
echo "\n📧 Test 2: Sending Test Email...\n";
$testEmail = 'aimecol314@gmail.com'; // Change this to your test email
sendTestEmail($testEmail);

// Test 3: Test Forgot Password API Endpoint
echo "\n📧 Test 3: Testing Forgot Password API...\n";
testForgotPasswordAPI($testEmail);

// Test 4: Check Email Logs
echo "\n📧 Test 4: Checking Email Logs...\n";
checkEmailLogs();

echo "\n✅ Email testing completed!\n";

/**
 * Test SMTP connection without sending email
 */
function testSMTPConnection()
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
        
        // Enable verbose debug output
        $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
        
        // Test connection
        if ($mail->smtpConnect()) {
            echo "✅ SMTP connection successful!\n";
            $mail->smtpClose();
            return true;
        } else {
            echo "❌ SMTP connection failed!\n";
            return false;
        }
        
    } catch (Exception $e) {
        echo "❌ SMTP connection error: {$mail->ErrorInfo}\n";
        return false;
    }
}

/**
 * Send a test email
 */
function sendTestEmail($toEmail)
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
        $mail->setFrom('aimecol314@gmail.com', 'IKIRAHA Test System');
        $mail->addAddress($toEmail, 'Test User');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'IKIRAHA Email Test - ' . date('Y-m-d H:i:s');
        $mail->Body    = '
        <h2>🧪 IKIRAHA Email Test</h2>
        <p>This is a test email from the IKIRAHA forgot password system.</p>
        <p><strong>Test Details:</strong></p>
        <ul>
            <li>Timestamp: ' . date('Y-m-d H:i:s') . '</li>
            <li>Server: ' . $_SERVER['SERVER_NAME'] ?? 'localhost' . '</li>
            <li>PHP Version: ' . PHP_VERSION . '</li>
            <li>PHPMailer Version: ' . PHPMailer::VERSION . '</li>
        </ul>
        <p>If you received this email, the SMTP configuration is working correctly!</p>
        ';
        $mail->AltBody = 'IKIRAHA Email Test - If you received this email, the SMTP configuration is working correctly!';

        $mail->send();
        echo "✅ Test email sent successfully to $toEmail\n";
        return true;
        
    } catch (Exception $e) {
        echo "❌ Test email failed: {$mail->ErrorInfo}\n";
        return false;
    }
}

/**
 * Test the forgot password API endpoint
 */
function testForgotPasswordAPI($email)
{
    try {
        // Simulate API request
        $_POST['email'] = $email;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        $controller = new ForgotPasswordController();
        
        // Capture output
        ob_start();
        $controller->forgotPassword();
        $output = ob_get_clean();
        
        echo "API Response: $output\n";
        
        $response = json_decode($output, true);
        if ($response && $response['success']) {
            echo "✅ Forgot password API test successful!\n";
            return true;
        } else {
            echo "❌ Forgot password API test failed!\n";
            return false;
        }
        
    } catch (Exception $e) {
        echo "❌ API test error: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Check email-related logs
 */
function checkEmailLogs()
{
    $logFiles = [
        'logs/error.log',
        '/var/log/mail.log',
        '/var/log/maillog',
        'C:/xampp/apache/logs/error.log',
        'C:/xampp/php/logs/php_error_log'
    ];
    
    echo "Checking log files for email-related entries...\n";
    
    foreach ($logFiles as $logFile) {
        if (file_exists($logFile)) {
            echo "📄 Found log file: $logFile\n";
            
            // Get last 20 lines
            $lines = file($logFile);
            if ($lines) {
                $recentLines = array_slice($lines, -20);
                $emailRelated = array_filter($recentLines, function($line) {
                    return stripos($line, 'mail') !== false || 
                           stripos($line, 'smtp') !== false || 
                           stripos($line, 'phpmailer') !== false;
                });
                
                if (!empty($emailRelated)) {
                    echo "📧 Recent email-related log entries:\n";
                    foreach ($emailRelated as $line) {
                        echo "   " . trim($line) . "\n";
                    }
                } else {
                    echo "   No recent email-related entries found.\n";
                }
            }
        }
    }
    
    // Check PHP error log
    $phpErrors = error_get_last();
    if ($phpErrors) {
        echo "🐛 Last PHP error: " . $phpErrors['message'] . "\n";
    }
}

/**
 * Additional diagnostic information
 */
function showDiagnosticInfo()
{
    echo "\n🔍 Diagnostic Information:\n";
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "PHPMailer Version: " . PHPMailer::VERSION . "\n";
    echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
    echo "Mail Function Available: " . (function_exists('mail') ? 'Yes' : 'No') . "\n";
    echo "SMTP Extension: " . (extension_loaded('openssl') ? 'Yes' : 'No') . "\n";
    echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
    echo "Server: " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\n";
}

showDiagnosticInfo();
?>
