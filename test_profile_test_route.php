<?php
// Test the profile-test route

echo "Testing profile-test route...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/auth/profile-test');
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "GET /auth/profile-test Response Code: $httpCode\n";
echo "GET /auth/profile-test Response: " . substr($response, 0, 200) . "...\n";

curl_close($ch);
