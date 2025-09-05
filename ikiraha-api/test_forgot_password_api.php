<?php
/**
 * Test Forgot Password API Endpoint
 */

echo "🧪 Testing IKIRAHA Forgot Password API\n";
echo "======================================\n\n";

// Test with different scenarios
$testCases = [
    [
        'email' => 'aimecol314@gmail.com',
        'description' => 'Valid email (should send email)'
    ],
    [
        'email' => 'nonexistent@example.com',
        'description' => 'Non-existent email (should return success but not send email)'
    ],
    [
        'email' => 'invalid-email',
        'description' => 'Invalid email format (should return error)'
    ],
    [
        'email' => '',
        'description' => 'Empty email (should return error)'
    ]
];

foreach ($testCases as $index => $testCase) {
    echo "📧 Test " . ($index + 1) . ": " . $testCase['description'] . "\n";
    echo "Email: " . $testCase['email'] . "\n";
    
    $result = testForgotPasswordAPI($testCase['email']);
    
    if ($result['success']) {
        echo "✅ API Response: " . $result['message'] . "\n";
    } else {
        echo "❌ API Error: " . $result['message'] . "\n";
    }
    
    echo "Response Code: " . $result['http_code'] . "\n";
    echo "Full Response: " . $result['response'] . "\n";
    echo str_repeat("-", 50) . "\n\n";
}

/**
 * Test the forgot password API endpoint
 */
function testForgotPasswordAPI($email)
{
    $url = 'http://localhost/ikirahaapp/ikiraha-api/public/auth/forgot-password';
    
    $data = json_encode(['email' => $email]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'message' => 'cURL Error: ' . $error,
            'response' => '',
            'http_code' => 0
        ];
    }
    
    $responseData = json_decode($response, true);
    
    return [
        'success' => $responseData['success'] ?? false,
        'message' => $responseData['message'] ?? 'Unknown response',
        'response' => $response,
        'http_code' => $httpCode
    ];
}

echo "✅ API testing completed!\n";
?>
