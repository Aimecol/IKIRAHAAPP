<?php
/**
 * Debug Token Validation Issue
 */

echo "🔍 DEBUGGING TOKEN VALIDATION ISSUE\n";
echo "===================================\n\n";

$token = '5533cf5fd51fa6a1b03b503789a6062c2edb3e135900e47a87a9a28a990ddbac';

echo "🧪 Testing token: $token\n\n";

// Test 1: Direct API call to validate-reset-token endpoint
echo "📡 Test 1: Testing API endpoint directly...\n";
$apiResult = testTokenValidationAPI($token);
echo "API Response: " . json_encode($apiResult, JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Check database directly
echo "🗄️ Test 2: Checking database directly...\n";
$dbResult = checkTokenInDatabase($token);
echo "Database Result: " . json_encode($dbResult, JSON_PRETTY_PRINT) . "\n\n";

// Test 3: Check token hashing
echo "🔐 Test 3: Testing token hashing mechanism...\n";
$hashResult = testTokenHashing($token);
echo "Hash Test Result: " . json_encode($hashResult, JSON_PRETTY_PRINT) . "\n\n";

// Test 4: Check the actual validation logic
echo "⚙️ Test 4: Testing validation logic directly...\n";
$validationResult = testValidationLogic($token);
echo "Validation Logic Result: " . json_encode($validationResult, JSON_PRETTY_PRINT) . "\n\n";

echo "✅ Debug testing completed!\n";

/**
 * Test the API endpoint directly
 */
function testTokenValidationAPI($token)
{
    $url = "http://localhost/ikirahaapp/ikiraha-api/public/auth/validate-reset-token/$token";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, fopen('php://temp', 'w+'));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $responseData = json_decode($response, true);
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'parsed_response' => $responseData,
        'curl_error' => $error
    ];
}

/**
 * Check token directly in database
 */
function checkTokenInDatabase($token)
{
    try {
        $host = 'localhost';
        $dbname = 'ikiraha_db';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check raw token
        $stmt = $pdo->prepare("SELECT id, token, email, created_at, expires_at, used, (expires_at > NOW()) as is_valid FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        $rawResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check hashed token
        $hashedToken = hash('sha256', $token);
        $stmt2 = $pdo->prepare("SELECT id, token, email, created_at, expires_at, used, (expires_at > NOW()) as is_valid FROM password_resets WHERE token = ?");
        $stmt2->execute([$hashedToken]);
        $hashedResult = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        return [
            'raw_token_search' => $rawResult ?: 'Not found',
            'hashed_token_search' => $hashedResult ?: 'Not found',
            'provided_token' => $token,
            'hashed_token' => $hashedToken
        ];
        
    } catch (PDOException $e) {
        return [
            'error' => 'Database error: ' . $e->getMessage()
        ];
    }
}

/**
 * Test token hashing mechanism
 */
function testTokenHashing($token)
{
    $hashedToken = hash('sha256', $token);
    
    return [
        'original_token' => $token,
        'original_length' => strlen($token),
        'hashed_token' => $hashedToken,
        'hashed_length' => strlen($hashedToken),
        'hash_algorithm' => 'sha256'
    ];
}

/**
 * Test validation logic by simulating the controller
 */
function testValidationLogic($token)
{
    try {
        // Include necessary files
        require_once 'config/database.php';
        require_once 'models/PasswordReset.php';
        
        $database = new Database();
        $db = $database->getConnection();
        $passwordReset = new PasswordReset($db);
        
        // Test the findByToken method
        $tokenData = $passwordReset->findByToken($token);
        
        return [
            'token_data' => $tokenData,
            'token_found' => $tokenData !== false,
            'validation_method' => 'PasswordReset::findByToken()'
        ];
        
    } catch (Exception $e) {
        return [
            'error' => 'Validation logic error: ' . $e->getMessage()
        ];
    }
}
?>
