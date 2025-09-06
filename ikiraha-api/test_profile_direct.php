<?php
/**
 * Direct profile test
 */

echo "👤 Testing Direct Profile API\n";
echo "=============================\n\n";

// First login to get token
$loginUrl = "http://localhost/ikirahaapp/ikiraha-api/public/auth/login";
$testEmail = 'profiletest@example.com';
$testPassword = 'testpassword123';

echo "🔐 Step 1: Getting access token...\n";

$loginData = json_encode([
    'email' => $testEmail,
    'password' => $testPassword
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($loginData)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$cleanResponse = preg_replace('/^[^{]*/', '', $response);
$loginResult = json_decode($cleanResponse, true);

if (!$loginResult || !$loginResult['success']) {
    echo "❌ Login failed\n";
    exit(1);
}

$accessToken = $loginResult['data']['access_token'];
echo "✅ Login successful! Token: " . substr($accessToken, 0, 20) . "...\n\n";

// Now test profile endpoint
echo "👤 Step 2: Testing profile endpoint...\n";
$profileUrl = "http://localhost/ikirahaapp/ikiraha-api/public/auth/profile";

echo "🌐 URL: $profileUrl\n";
echo "🎫 Token: " . substr($accessToken, 0, 30) . "...\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $profileUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "📥 HTTP Code: $httpCode\n";
echo "📥 Raw Response: $response\n";

if ($curlError) {
    echo "❌ cURL Error: $curlError\n";
}

if ($response) {
    $cleanResponse = preg_replace('/^[^{]*/', '', $response);
    echo "🧹 Cleaned Response: $cleanResponse\n\n";
    
    $data = json_decode($cleanResponse, true);
    if ($data) {
        echo "📋 Parsed response:\n";
        print_r($data);
        
        if ($data['success']) {
            echo "\n✅ Profile retrieval successful!\n";
        } else {
            echo "\n❌ Profile retrieval failed: " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ Failed to parse JSON response\n";
        echo "JSON Error: " . json_last_error_msg() . "\n";
    }
}
?>
