<?php
// Test refresh token functionality

echo "Testing refresh token functionality...\n";

// First, login to get tokens
$loginData = [
    'email' => 'aimecol314@gmail.com',
    'password' => '123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/auth/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$loginResponse = curl_exec($ch);
$loginData = json_decode($loginResponse, true);

if (!$loginData || !$loginData['success']) {
    echo "❌ Login failed: " . ($loginData['message'] ?? 'Unknown error') . "\n";
    exit(1);
}

echo "✅ Login successful\n";
$accessToken = $loginData['data']['access_token'];
$refreshToken = $loginData['data']['refresh_token'];

echo "Access Token: " . substr($accessToken, 0, 50) . "...\n";
echo "Refresh Token: " . substr($refreshToken, 0, 50) . "...\n\n";

// Test refresh token endpoint
echo "Testing refresh token endpoint...\n";

// Create fresh cURL handle for refresh request
curl_close($ch);
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/auth/refresh');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $refreshToken
]);

$refreshResponse = curl_exec($ch);
$refreshData = json_decode($refreshResponse, true);
$refreshHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Refresh Response Code: $refreshHttpCode\n";
echo "Refresh Response: " . substr($refreshResponse, 0, 300) . "...\n\n";

if ($refreshData && $refreshData['success'] && isset($refreshData['data']['access_token'])) {
    echo "✅ Token refresh successful\n";
    $newAccessToken = $refreshData['data']['access_token'];
    $newRefreshToken = $refreshData['data']['refresh_token'] ?? 'Not provided';
    
    echo "New Access Token: " . substr($newAccessToken, 0, 50) . "...\n";
    echo "New Refresh Token: " . (is_string($newRefreshToken) ? substr($newRefreshToken, 0, 50) . "..." : $newRefreshToken) . "\n\n";
    
    // Test profile endpoint with new token
    echo "Testing profile endpoint with new access token...\n";
    
    curl_close($ch);
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/auth/user-profile');
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $newAccessToken
    ]);
    
    $profileResponse = curl_exec($ch);
    $profileHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "Profile Response Code: $profileHttpCode\n";
    echo "Profile Response: " . substr($profileResponse, 0, 200) . "...\n";
    
    if ($profileHttpCode === 200) {
        echo "✅ Profile access with new token successful!\n";
    } else {
        echo "❌ Profile access with new token failed\n";
    }
} else {
    echo "❌ Token refresh failed\n";
    if (isset($refreshData['message'])) {
        echo "Error: " . $refreshData['message'] . "\n";
    }
}

curl_close($ch);
