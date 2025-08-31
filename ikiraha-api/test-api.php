<?php
/**
 * IKIRAHA API Test Script
 * Tests all major API endpoints to verify functionality
 */

// Configuration
$baseUrl = 'http://localhost/ikiraha-api/public';
$testEmail = 'test@example.com';
$testPassword = 'password123';

echo "IKIRAHA API Test Suite\n";
echo "=====================\n\n";

// Helper function to make HTTP requests
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
        'Content-Type: application/json'
    ], $headers));

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

// Test 1: Health Check
echo "1. Testing Health Check...\n";
$response = makeRequest("$baseUrl/health");
if ($response['status'] === 200 && $response['body']['success']) {
    echo "   ✓ Health check passed\n";
} else {
    echo "   ❌ Health check failed\n";
    print_r($response);
}

// Test 2: API Root
echo "2. Testing API Root...\n";
$response = makeRequest("$baseUrl/");
if ($response['status'] === 200 && $response['body']['success']) {
    echo "   ✓ API root accessible\n";
} else {
    echo "   ❌ API root failed\n";
}

// Test 3: User Registration
echo "3. Testing User Registration...\n";
$userData = [
    'name' => 'Test User',
    'email' => $testEmail,
    'password' => $testPassword,
    'phone' => '+250788123456',
    'role' => 'client'
];

$response = makeRequest("$baseUrl/auth/register", 'POST', $userData);
if ($response['status'] === 201 || ($response['status'] === 400 && strpos($response['body']['message'], 'already registered') !== false)) {
    echo "   ✓ User registration working\n";
} else {
    echo "   ❌ User registration failed\n";
    print_r($response);
}

// Test 4: User Login
echo "4. Testing User Login...\n";
$loginData = [
    'email' => 'client@ikiraha.com', // Use default test account
    'password' => 'password'
];

$response = makeRequest("$baseUrl/auth/login", 'POST', $loginData);
$accessToken = null;

if ($response['status'] === 200 && $response['body']['success']) {
    $accessToken = $response['body']['data']['access_token'];
    echo "   ✓ User login successful\n";
} else {
    echo "   ❌ User login failed\n";
    print_r($response);
}

// Test 5: Get Categories
echo "5. Testing Get Categories...\n";
$response = makeRequest("$baseUrl/categories");
if ($response['status'] === 200 && $response['body']['success']) {
    echo "   ✓ Categories retrieved successfully\n";
} else {
    echo "   ❌ Get categories failed\n";
}

// Test 6: Get Products
echo "6. Testing Get Products...\n";
$response = makeRequest("$baseUrl/products");
if ($response['status'] === 200 && $response['body']['success']) {
    echo "   ✓ Products retrieved successfully\n";
} else {
    echo "   ❌ Get products failed\n";
}

// Test 7: Get Featured Products
echo "7. Testing Get Featured Products...\n";
$response = makeRequest("$baseUrl/products/featured");
if ($response['status'] === 200 && $response['body']['success']) {
    echo "   ✓ Featured products retrieved successfully\n";
} else {
    echo "   ❌ Get featured products failed\n";
}

// Test 8: Authenticated Request (Get Profile)
if ($accessToken) {
    echo "8. Testing Authenticated Request (Get Profile)...\n";
    $headers = ["Authorization: Bearer $accessToken"];
    $response = makeRequest("$baseUrl/auth/profile", 'GET', null, $headers);

    if ($response['status'] === 200 && $response['body']['success']) {
        echo "   ✓ Authenticated request successful\n";
    } else {
        echo "   ❌ Authenticated request failed\n";
    }
} else {
    echo "8. Skipping authenticated tests (no token)\n";
}

// Test 9: Create Order (if authenticated)
if ($accessToken) {
    echo "9. Testing Create Order...\n";
    $orderData = [
        'restaurant_id' => 1,
        'items' => [
            [
                'product_id' => 1,
                'quantity' => 2,
                'price' => 2700
            ]
        ],
        'payment_method' => 'mtn_rwanda',
        'payment_phone' => '+250788123456',
        'delivery_address' => 'Test Address, Kigali',
        'delivery_phone' => '+250788123456',
        'notes' => 'Test order'
    ];

    $headers = ["Authorization: Bearer $accessToken"];
    $response = makeRequest("$baseUrl/orders", 'POST', $orderData, $headers);

    if ($response['status'] === 201 && $response['body']['success']) {
        echo "   ✓ Order creation successful\n";
    } else {
        echo "   ❌ Order creation failed\n";
        if (isset($response['body']['message'])) {
            echo "   Error: " . $response['body']['message'] . "\n";
        }
    }
} else {
    echo "9. Skipping order creation test (no token)\n";
}

echo "\n=== Test Summary ===\n";
echo "API testing completed. Check results above.\n";
echo "If all tests pass, the API is working correctly.\n\n";
?>