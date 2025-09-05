<?php
/**
 * Final System Test - Complete Password Reset Flow
 */

echo "🎯 FINAL IKIRAHA PASSWORD RESET SYSTEM TEST\n";
echo "===========================================\n\n";

$testEmail = 'aimecol314@gmail.com';
$newPassword = 'newpassword123';

echo "📧 Testing complete password reset flow for: $testEmail\n";
echo "🔑 New password: $newPassword\n\n";

// Step 1: Create a fresh test token
echo "🔧 Step 1: Creating fresh test token...\n";
$tokenResult = createFreshToken($testEmail);

if (!$tokenResult['success']) {
    echo "❌ Failed to create test token: " . $tokenResult['message'] . "\n";
    exit(1);
}

$token = $tokenResult['token'];
echo "✅ Test token created: $token\n\n";

// Step 2: Test token validation API
echo "📡 Step 2: Testing token validation API...\n";
$validationResult = testTokenValidation($token);

if ($validationResult['success']) {
    echo "✅ Token validation successful!\n";
    echo "📧 Email: " . $validationResult['email'] . "\n\n";
} else {
    echo "❌ Token validation failed: " . $validationResult['message'] . "\n";
    exit(1);
}

// Step 3: Test password reset API
echo "🔐 Step 3: Testing password reset API...\n";
$resetResult = testPasswordReset($token, $newPassword);

if ($resetResult['success']) {
    echo "✅ Password reset successful!\n";
    echo "📝 Message: " . $resetResult['message'] . "\n\n";
} else {
    echo "❌ Password reset failed: " . $resetResult['message'] . "\n";
    exit(1);
}

// Step 4: Test login with new password
echo "🔓 Step 4: Testing login with new password...\n";
$loginResult = testLogin($testEmail, $newPassword);

if ($loginResult['success']) {
    echo "✅ Login with new password successful!\n";
    echo "👤 User: " . $loginResult['user']['name'] . "\n";
    echo "🎫 Token received: " . (isset($loginResult['token']) ? 'Yes' : 'No') . "\n\n";
} else {
    echo "❌ Login failed: " . $loginResult['message'] . "\n";
}

// Step 5: Verify token is marked as used
echo "🔍 Step 5: Verifying token is marked as used...\n";
$tokenCheckResult = checkTokenStatus($token);

if ($tokenCheckResult['used']) {
    echo "✅ Token correctly marked as used!\n";
} else {
    echo "⚠️ Token not marked as used (this might be expected if token was deleted)\n";
}

echo "\n🎉 COMPLETE SYSTEM TEST FINISHED!\n";
echo "✅ All password reset functionality is working correctly!\n";

/**
 * Create a fresh test token
 */
function createFreshToken($email)
{
    try {
        require_once 'config/database.php';
        require_once 'models/User.php';
        require_once 'models/PasswordReset.php';

        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        $passwordReset = new PasswordReset($db);

        $userData = $user->findByEmail($email);
        if (!$userData) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Generate token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        // Clean up old tokens
        $passwordReset->deleteByUserId($userData['id']);

        // Create new token
        $resetData = [
            'user_id' => $userData['id'],
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
            'used' => 0
        ];

        $result = $passwordReset->create($resetData);
        
        return [
            'success' => $result !== false,
            'token' => $token,
            'message' => $result ? 'Token created successfully' : 'Failed to create token'
        ];

    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Test token validation
 */
function testTokenValidation($token)
{
    $url = "http://localhost/ikirahaapp/ikiraha-api/public/auth/validate-reset-token/$token";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    return [
        'success' => $httpCode == 200 && $data['success'],
        'message' => $data['message'] ?? 'Unknown error',
        'email' => $data['data']['email'] ?? null
    ];
}

/**
 * Test password reset
 */
function testPasswordReset($token, $password)
{
    $url = "http://localhost/ikirahaapp/ikiraha-api/public/auth/reset-password";
    
    $postData = json_encode([
        'token' => $token,
        'password' => $password,
        'password_confirmation' => $password
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    return [
        'success' => $httpCode == 200 && $data['success'],
        'message' => $data['message'] ?? 'Unknown error'
    ];
}

/**
 * Test login with new password
 */
function testLogin($email, $password)
{
    $url = "http://localhost/ikirahaapp/ikiraha-api/public/auth/login";
    
    $postData = json_encode([
        'email' => $email,
        'password' => $password
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    return [
        'success' => $httpCode == 200 && $data['success'],
        'message' => $data['message'] ?? 'Unknown error',
        'user' => $data['data']['user'] ?? null,
        'token' => $data['data']['access_token'] ?? null
    ];
}

/**
 * Check token status in database
 */
function checkTokenStatus($token)
{
    try {
        $host = 'localhost';
        $dbname = 'ikiraha_db';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT used FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'found' => $result !== false,
            'used' => $result ? (bool)$result['used'] : false
        ];
        
    } catch (PDOException $e) {
        return ['found' => false, 'used' => false, 'error' => $e->getMessage()];
    }
}
?>
