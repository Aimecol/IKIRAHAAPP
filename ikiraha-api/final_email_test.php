<?php
/**
 * Final Email Test - Send actual password reset email
 */

echo "🧪 Final IKIRAHA Email Test\n";
echo "===========================\n\n";

// Test email address
$testEmail = 'test@example.com'; // This will test the security feature (non-existent email)

echo "📧 Sending password reset email to: $testEmail\n";
echo "This will test the complete forgot password flow...\n\n";

// Make API call to forgot password endpoint
$result = callForgotPasswordAPI($testEmail);

if ($result['success']) {
    echo "✅ SUCCESS: " . $result['message'] . "\n";
    echo "📬 Please check your email inbox for the password reset link.\n\n";
    
    echo "🔍 What to check in your email:\n";
    echo "- Subject: 'Reset Your IKIRAHA Password'\n";
    echo "- From: IKIRAHA Food Delivery\n";
    echo "- Professional HTML template with IKIRAHA branding\n";
    echo "- Clear 'Reset My Password' button\n";
    echo "- Security information displayed\n";
    echo "- Reset link that expires in 1 hour\n\n";
    
    echo "🧪 To test the reset link:\n";
    echo "1. Click the reset button in the email\n";
    echo "2. Or copy the reset link and open in browser\n";
    echo "3. Enter a new password\n";
    echo "4. Confirm the password reset works\n\n";
    
} else {
    echo "❌ ERROR: " . $result['message'] . "\n";
    echo "HTTP Code: " . $result['http_code'] . "\n";
    echo "Full Response: " . $result['response'] . "\n\n";
}

// Show recent database entries
echo "🗄️ Checking database for password reset tokens...\n";
checkPasswordResetTokens();

echo "✅ Final email test completed!\n";

/**
 * Call the forgot password API
 */
function callForgotPasswordAPI($email)
{
    $url = 'http://localhost/ikirahaapp/ikiraha-api/public/auth/forgot-password';
    
    $data = json_encode(['email' => $email]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'message' => 'cURL Error: ' . $error,
            'response' => '',
            'http_code' => 0
        ];
    }
    
    $responseData = json_decode($response, true);
    
    return [
        'success' => $responseData['success'] ?? false,
        'message' => $responseData['message'] ?? ($responseData['data']['message'] ?? 'Unknown response'),
        'response' => $response,
        'http_code' => $httpCode
    ];
}

/**
 * Check password reset tokens in database
 */
function checkPasswordResetTokens()
{
    try {
        $host = 'localhost';
        $dbname = 'ikiraha_db';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query("SELECT id, email, created_at, expires_at, used FROM password_resets ORDER BY created_at DESC LIMIT 5");
        $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($tokens)) {
            echo "No password reset tokens found in database.\n";
        } else {
            echo "Recent password reset tokens:\n";
            foreach ($tokens as $token) {
                $status = $token['used'] ? 'USED' : 'ACTIVE';
                echo "- ID: {$token['id']}, Email: {$token['email']}, Status: $status, Created: {$token['created_at']}, Expires: {$token['expires_at']}\n";
            }
        }
        
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage() . "\n";
    }
}
?>
