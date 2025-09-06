<?php
// Test simple GET route

echo "Testing simple GET route...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/ikirahaapp/ikiraha-api/public/test-get');
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "GET /test-get Response Code: $httpCode\n";
echo "GET /test-get Response: " . substr($response, 0, 200) . "...\n";

curl_close($ch);
