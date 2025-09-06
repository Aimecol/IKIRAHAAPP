<?php
/**
 * Create test user for profile system testing
 */

require_once 'config/database.php';
require_once 'models/User.php';

echo "🔧 Creating Test User for Profile System\n";
echo "========================================\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    $testEmail = 'profiletest@example.com';
    $testPassword = 'testpassword123';
    $testName = 'Profile Test User';

    echo "📧 Creating user: $testEmail\n";
    echo "🔑 Password: $testPassword\n";
    echo "👤 Name: $testName\n\n";

    // Check if user already exists
    $existingUser = $user->findByEmail($testEmail);
    if ($existingUser) {
        echo "ℹ️  User already exists, deleting old user...\n";
        
        // Delete existing user
        $deleteQuery = "DELETE FROM users WHERE email = :email";
        $stmt = $db->prepare($deleteQuery);
        $stmt->bindParam(':email', $testEmail);
        $stmt->execute();
        
        echo "✅ Old user deleted\n";
    }

    // Create new user
    $userData = [
        'name' => $testName,
        'email' => $testEmail,
        'password' => $testPassword,
        'phone' => '+250788999888',
        'role' => 'client',
        'status' => 'active',
        'email_verified' => 1
    ];

    $result = $user->register($userData);

    if ($result['success']) {
        echo "✅ Test user created successfully!\n";
        echo "📋 User ID: " . $result['user_id'] . "\n";
        echo "🆔 UUID: " . $result['uuid'] . "\n\n";

        // Test login immediately
        echo "🔐 Testing login...\n";
        $loginResult = $user->login($testEmail, $testPassword);
        
        if ($loginResult['success']) {
            echo "✅ Login test successful!\n";
            echo "🎫 Access token generated\n\n";
            
            echo "🎯 Ready for profile system testing!\n";
            echo "Use these credentials:\n";
            echo "Email: $testEmail\n";
            echo "Password: $testPassword\n";
        } else {
            echo "❌ Login test failed: " . $loginResult['message'] . "\n";
        }
        
    } else {
        echo "❌ Failed to create test user\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Test user setup completed!\n";
?>
