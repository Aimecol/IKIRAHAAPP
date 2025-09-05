<?php
/**
 * Create a test token for debugging
 */

require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/PasswordReset.php';

echo "🔧 Creating Test Token for Debugging\n";
echo "====================================\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    $passwordReset = new PasswordReset($db);

    // Get user data
    $email = 'aimecol314@gmail.com';
    $userData = $user->findByEmail($email);
    
    if (!$userData) {
        echo "❌ User not found with email: $email\n";
        exit(1);
    }

    echo "✅ User found: {$userData['name']} ({$userData['email']})\n";

    // Generate a test token
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry

    echo "🔑 Generated token: $token\n";
    echo "⏰ Expires at: $expiresAt\n\n";

    // Clean up old tokens
    $passwordReset->deleteByUserId($userData['id']);
    echo "🧹 Cleaned up old tokens\n";

    // Create new reset token (store unhashed for testing)
    $resetData = [
        'user_id' => $userData['id'],
        'email' => $email,
        'token' => $token, // Store unhashed for testing
        'expires_at' => $expiresAt,
        'created_at' => date('Y-m-d H:i:s'),
        'used' => 0
    ];

    $result = $passwordReset->create($resetData);
    
    if ($result) {
        echo "✅ Test token created successfully!\n";
        echo "📋 Token ID: $result\n\n";
        
        // Test the token validation
        echo "🧪 Testing token validation...\n";
        $tokenData = $passwordReset->findByToken($token);
        
        if ($tokenData) {
            echo "✅ Token found in database!\n";
            echo "📧 Email: {$tokenData['email']}\n";
            echo "⏰ Expires: {$tokenData['expires_at']}\n";
            echo "🔄 Used: {$tokenData['used']}\n\n";
            
            // Generate test URLs
            $resetUrl = "http://localhost/ikirahaapp/ikiraha-api/public/reset-password.html?token=$token";
            $apiUrl = "http://localhost/ikirahaapp/ikiraha-api/public/auth/validate-reset-token/$token";
            
            echo "🌐 Test URLs:\n";
            echo "Reset Page: $resetUrl\n";
            echo "API Validation: $apiUrl\n\n";
            
            // Test API validation
            echo "📡 Testing API validation...\n";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "HTTP Code: $httpCode\n";
            echo "Response: $response\n\n";
            
            if ($httpCode == 200) {
                echo "✅ API validation successful!\n";
                echo "🎉 You can now test the reset page with this token!\n";
            } else {
                echo "❌ API validation failed\n";
            }
            
        } else {
            echo "❌ Token not found in database after creation\n";
        }
        
    } else {
        echo "❌ Failed to create test token\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Test token creation completed!\n";
?>
