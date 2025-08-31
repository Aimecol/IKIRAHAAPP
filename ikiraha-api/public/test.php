<?php
// Simple test file to verify the setup
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'IKIRAHA API Test',
    'request_uri' => $_SERVER['REQUEST_URI'],
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s')
]);
?>