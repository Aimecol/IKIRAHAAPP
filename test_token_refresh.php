<?php
// Test token refresh functionality

// First, let's login to get tokens
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

// Test profile endpoint with access token
echo "Testing profile endpoint with access token...\n";
curl_setopt($ch, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/auth/profile');
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, null);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$profileResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Profile API Response Code: $httpCode\n";
echo "Profile API Response: " . substr($profileResponse, 0, 200) . "...\n\n";

if ($httpCode === 401) {
    echo "🔄 Access token expired, testing refresh token...\n";
    
    // Test refresh token endpoint
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/auth/refresh');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['refresh_token' => $refreshToken]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $refreshResponse = curl_exec($ch);
    $refreshData = json_decode($refreshResponse, true);
    $refreshHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "Refresh API Response Code: $refreshHttpCode\n";
    echo "Refresh API Response: " . substr($refreshResponse, 0, 200) . "...\n\n";
    
    if ($refreshData && $refreshData['success'] && isset($refreshData['data']['access_token'])) {
        echo "✅ Token refresh successful\n";
        $newAccessToken = $refreshData['data']['access_token'];
        echo "New Access Token: " . substr($newAccessToken, 0, 50) . "...\n\n";
        
        // Test profile endpoint with new token
        echo "Testing profile endpoint with new access token...\n";
        curl_setopt($ch, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/auth/profile');
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $newAccessToken
        ]);
        
        $newProfileResponse = curl_exec($ch);
        $newHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        echo "New Profile API Response Code: $newHttpCode\n";
        echo "New Profile API Response: " . substr($newProfileResponse, 0, 200) . "...\n";
        
        if ($newHttpCode === 200) {
            echo "✅ Profile access with new token successful!\n";
        } else {
            echo "❌ Profile access with new token failed\n";
        }
    } else {
        echo "❌ Token refresh failed\n";
    }
} else if ($httpCode === 200) {
    echo "✅ Profile access successful - token is still valid\n";
} else {
    echo "❌ Profile access failed with unexpected error\n";
}

curl_close($ch);
