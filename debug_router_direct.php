<?php
// Debug router directly

// Simulate the exact environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/ikirahaapp/ikiraha-api/public/auth/profile';

// Set headers
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test-token';

echo "Simulating GET /auth/profile request...\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";

// Include the index.php file which should handle the routing
ob_start();
try {
    include 'ikiraha-api/public/index.php';
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
$output = ob_get_clean();

echo "Output: $output\n";
