<?php
// Test the exact same request that Flutter would make

echo "Testing Flutter-style request...\n";

// Login to get a fresh token
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
    echo "❌ Login failed\n";
    exit(1);
}

$accessToken = $loginData['data']['access_token'];
echo "✅ Login successful\n";
echo "Token: " . substr($accessToken, 0, 50) . "...\n\n";

// Wait a moment to simulate the time between login and profile request
echo "Waiting 2 seconds to simulate Flutter app delay...\n";
sleep(2);

// Test profile request exactly like Flutter would do it
echo "Testing profile request...\n";

curl_close($ch);
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/auth/user-profile');
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$profileResponse = curl_exec($ch);
$profileHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "Profile Response Code: $profileHttpCode\n";
echo "Profile Response: " . substr($profileResponse, 0, 300) . "...\n\n";

if ($profileHttpCode === 401) {
    echo "❌ Got 401 - Token validation failed\n";
    
    // Let's check what the token looks like when decoded
    $tokenParts = explode('.', $accessToken);
    $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
    $payloadData = json_decode($payload, true);
    
    $currentTime = time();
    $expiresAt = $payloadData['exp'] ?? 0;
    
    echo "Current time: $currentTime (" . date('Y-m-d H:i:s', $currentTime) . ")\n";
    echo "Token expires: $expiresAt (" . date('Y-m-d H:i:s', $expiresAt) . ")\n";
    echo "Token is " . ($currentTime < $expiresAt ? "VALID" : "EXPIRED") . "\n";
    
} else if ($profileHttpCode === 200) {
    echo "✅ Profile request successful!\n";
} else {
    echo "❌ Unexpected response code: $profileHttpCode\n";
}

curl_close($ch);
