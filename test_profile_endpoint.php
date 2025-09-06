<?php
// Test profile endpoint directly

echo "Testing profile endpoint...\n";

// Test with a simple GET request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/auth/profile');
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer test-token'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
if ($error) {
    echo "cURL Error: $error\n";
}

curl_close($ch);

// Also test the health endpoint to make sure the API is working
echo "\nTesting health endpoint...\n";
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/health');
curl_setopt($ch2, CURLOPT_HTTPGET, true);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);

$healthResponse = curl_exec($ch2);
$healthCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

echo "Health HTTP Code: $healthCode\n";
echo "Health Response: $healthResponse\n";

curl_close($ch2);
