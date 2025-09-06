<?php
/**
 * Direct login test
 */

echo "🔐 Testing Direct Login\n";
echo "======================\n\n";

$url = "http://localhost/ikirahaapp/ikiraha-api/public/auth/login";
$testEmail = 'profiletest@example.com';
$testPassword = 'testpassword123';

echo "📧 Email: $testEmail\n";
echo "🔑 Password: $testPassword\n";
echo "🌐 URL: $url\n\n";

$postData = json_encode([
    'email' => $testEmail,
    'password' => $testPassword
]);

echo "📤 Request data: $postData\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($postData)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "📥 HTTP Code: $httpCode\n";
echo "📥 Response: $response\n";

if ($curlError) {
    echo "❌ cURL Error: $curlError\n";
}

if ($response) {
    // Clean the response by removing any PHP output before JSON
    $cleanResponse = preg_replace('/^[^{]*/', '', $response);
    echo "🧹 Cleaned response: $cleanResponse\n\n";

    $data = json_decode($cleanResponse, true);
    if ($data) {
        echo "📋 Parsed response:\n";
        print_r($data);

        if ($data['success'] && isset($data['data']['access_token'])) {
            echo "\n✅ Login successful!\n";
            echo "🎫 Access token: " . substr($data['data']['access_token'], 0, 50) . "...\n";
        }
    } else {
        echo "❌ Failed to parse JSON response\n";
        echo "JSON Error: " . json_last_error_msg() . "\n";
    }
}
?>
