<?php
/**
 * Test Complete Password Reset Flow
 * This will send an email and show the reset link for testing
 */

echo "🧪 Testing Complete IKIRAHA Password Reset Flow\n";
echo "===============================================\n\n";

// Test email address
$testEmail = 'aimecol314@gmail.com';

echo "📧 Step 1: Sending password reset email to: $testEmail\n";

// Make API call to forgot password endpoint
$result = callForgotPasswordAPI($testEmail);

if ($result['success']) {
    echo "✅ SUCCESS: " . $result['message'] . "\n\n";
    
    // Get the token from database to construct the reset link
    echo "🔍 Step 2: Retrieving reset token from database...\n";
    $tokenData = getLatestResetToken($testEmail);
    
    if ($tokenData) {
        $resetLink = "http://localhost/ikirahaapp/ikiraha-api/public/reset-password.html?token=" . $tokenData['token'];
        
        echo "✅ Reset token found!\n";
        echo "📧 Email sent with reset link\n";
        echo "🔗 Reset Link: $resetLink\n\n";
        
        echo "🧪 Step 3: Testing the HTML reset page...\n";
        echo "To test the complete flow:\n";
        echo "1. Open the reset link in your browser\n";
        echo "2. Enter a new password (minimum 6 characters)\n";
        echo "3. Confirm the password\n";
        echo "4. Click 'Reset Password'\n";
        echo "5. Verify success message and redirect\n\n";
        
        echo "🌐 You can also test by opening this URL directly:\n";
        echo "$resetLink\n\n";
        
        // Test token validation
        echo "🔍 Step 4: Testing token validation...\n";
        $validationResult = testTokenValidation($tokenData['token']);
        
        if ($validationResult['success']) {
            echo "✅ Token validation successful!\n";
        } else {
            echo "❌ Token validation failed: " . $validationResult['message'] . "\n";
        }
        
    } else {
        echo "❌ Could not retrieve reset token from database\n";
    }
    
} else {
    echo "❌ ERROR: " . $result['message'] . "\n";
    echo "HTTP Code: " . $result['http_code'] . "\n";
}

echo "\n✅ Complete flow test finished!\n";

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
 * Get the latest reset token from database
 */
function getLatestResetToken($email)
{
    try {
        $host = 'localhost';
        $dbname = 'ikiraha_db';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT token, created_at, expires_at FROM password_resets WHERE email = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage() . "\n";
        return null;
    }
}

/**
 * Test token validation endpoint
 */
function testTokenValidation($token)
{
    $url = "http://localhost/ikirahaapp/ikiraha-api/public/auth/validate-reset-token/$token";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'message' => 'cURL Error: ' . $error
        ];
    }
    
    $responseData = json_decode($response, true);
    
    return [
        'success' => $responseData['success'] ?? false,
        'message' => $responseData['message'] ?? 'Unknown response'
    ];
}
?>
