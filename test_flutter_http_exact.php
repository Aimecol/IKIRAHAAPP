<?php
// Test the exact same HTTP request that Flutter makes

echo "Testing exact Flutter HTTP request...\n";

// First login to get a token
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

// Now test the profile request with EXACT Flutter headers
echo "Testing profile request with Flutter-style headers...\n";

curl_close($ch);
$ch = curl_init();

// Use the exact URL that Flutter would construct
$profileUrl = 'http://localhost/ikirahaapp/ikiraha-api/public/auth/user-profile';
echo "Profile URL: $profileUrl\n";

curl_setopt($ch, CURLOPT_URL, $profileUrl);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Use EXACT same headers as Flutter
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);

// Capture verbose output
$verboseOutput = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verboseOutput);

$profileResponse = curl_exec($ch);
$profileHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Get verbose output
rewind($verboseOutput);
$verboseInfo = stream_get_contents($verboseOutput);
fclose($verboseOutput);

echo "HTTP Status Code: $profileHttpCode\n";
echo "Response Body: " . substr($profileResponse, 0, 500) . "...\n\n";

echo "Verbose cURL output:\n";
echo $verboseInfo . "\n";

if ($profileHttpCode === 401) {
    echo "❌ Got 401 - Analyzing token...\n";
    
    // Decode the token to check if it's valid
    $tokenParts = explode('.', $accessToken);
    if (count($tokenParts) === 3) {
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $payloadData = json_decode($payload, true);
        
        $currentTime = time();
        $expiresAt = $payloadData['exp'] ?? 0;
        
        echo "Current time: $currentTime (" . date('Y-m-d H:i:s', $currentTime) . ")\n";
        echo "Token expires: $expiresAt (" . date('Y-m-d H:i:s', $expiresAt) . ")\n";
        echo "Token is " . ($currentTime < $expiresAt ? "VALID" : "EXPIRED") . "\n";
        echo "Token payload: " . json_encode($payloadData, JSON_PRETTY_PRINT) . "\n";
    }
} else if ($profileHttpCode === 200) {
    echo "✅ Profile request successful!\n";
} else {
    echo "❌ Unexpected response code: $profileHttpCode\n";
}

curl_close($ch);
