<?php
/**
 * JWT (JSON Web Token) Utility Class
 * Secure token generation and validation for IKIRAHA API
 */

class JWT {

    /**
     * Generate JWT token
     */
    public static function encode($payload, $secret = null) {
        $secret = $secret ?: JWT_SECRET;

        $header = json_encode(['typ' => 'JWT', 'alg' => JWT_ALGORITHM]);
        $payload = json_encode($payload);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    /**
     * Decode and validate JWT token
     */
    public static function decode($jwt, $secret = null) {
        $secret = $secret ?: JWT_SECRET;

        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) {
            throw new Exception('Invalid token format');
        }

        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];

        // Verify signature
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if (!hash_equals($base64Signature, $signatureProvided)) {
            throw new Exception('Invalid token signature');
        }

        $payloadData = json_decode($payload, true);

        // Check expiration
        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
            throw new Exception('Token has expired');
        }

        return $payloadData;
    }

    /**
     * Generate access token
     */
    public static function generateAccessToken($userId, $role, $email) {
        $payload = [
            'user_id' => $userId,
            'role' => $role,
            'email' => $email,
            'type' => 'access',
            'iat' => time(),
            'exp' => time() + JWT_EXPIRY
        ];

        return self::encode($payload);
    }

    /**
     * Generate refresh token
     */
    public static function generateRefreshToken($userId) {
        $payload = [
            'user_id' => $userId,
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + JWT_REFRESH_EXPIRY
        ];

        return self::encode($payload);
    }

    /**
     * Validate token and return user data
     */
    public static function validateToken($token) {
        try {
            $decoded = self::decode($token);

            if (!isset($decoded['user_id']) || !isset($decoded['type'])) {
                throw new Exception('Invalid token payload');
            }

            return $decoded;
        } catch (Exception $e) {
            throw new Exception('Token validation failed: ' . $e->getMessage());
        }
    }

    /**
     * Extract token from Authorization header
     */
    public static function getBearerToken() {
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
?>