<?php
// Test the new user-profile route

echo "Testing new user-profile route...\n";

// First, login to get a real token
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

$accessToken = $loginData['data']['access_token'];
echo "✅ Login successful, got token: " . substr($accessToken, 0, 50) . "...\n\n";

// Test the new GET route
echo "Testing GET /auth/user-profile...\n";

// Create a fresh cURL handle for GET request
curl_close($ch);
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/auth/user-profile');
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "GET Response Code: $httpCode\n";
echo "GET Response: " . substr($response, 0, 500) . "...\n";

curl_close($ch);
