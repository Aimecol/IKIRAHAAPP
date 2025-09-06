<?php
// Decode JWT token to check expiration

require_once 'ikiraha-api/config/config.php';

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

// Decode the token manually
$tokenParts = explode('.', $accessToken);
if (count($tokenParts) !== 3) {
    echo "❌ Invalid token format\n";
    exit(1);
}

// Decode the payload (second part)
$payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
$payloadData = json_decode($payload, true);

echo "Token payload:\n";
print_r($payloadData);

$currentTime = time();
$issuedAt = $payloadData['iat'] ?? 0;
$expiresAt = $payloadData['exp'] ?? 0;

echo "\nTime analysis:\n";
echo "Current timestamp: $currentTime (" . date('Y-m-d H:i:s', $currentTime) . ")\n";
echo "Token issued at: $issuedAt (" . date('Y-m-d H:i:s', $issuedAt) . ")\n";
echo "Token expires at: $expiresAt (" . date('Y-m-d H:i:s', $expiresAt) . ")\n";
echo "Token age: " . ($currentTime - $issuedAt) . " seconds\n";
echo "Time until expiry: " . ($expiresAt - $currentTime) . " seconds\n";
echo "Token is " . ($currentTime < $expiresAt ? "VALID" : "EXPIRED") . "\n";

curl_close($ch);
