<?php
/**
 * AuthToken Model for IKIRAHA API
 * Handles authentication tokens management
 */

class AuthToken {
    private $conn;
    private $table_name = "auth_tokens";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create new authentication token
     */
    public function createToken($userId, $token, $type = 'access', $expiresIn = 3600) {
        try {
            // Calculate expiration time
            $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);

            $query = "INSERT INTO " . $this->table_name . "
                     (user_id, token, type, expires_at)
                     VALUES (:user_id, :token, :type, :expires_at)";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':expires_at', $expiresAt);

            if ($stmt->execute()) {
                $tokenId = $this->conn->lastInsertId();
                
                return [
                    'success' => true,
                    'message' => 'Token created successfully',
                    'data' => [
                        'id' => $tokenId,
                        'expires_at' => $expiresAt
                    ]
                ];
            } else {
                throw new Exception('Failed to create token');
            }

        } catch (Exception $e) {
            logError('AuthToken createToken error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate token and get user info
     */
    public function validateToken($token, $type = 'access') {
        try {
            $query = "SELECT at.*, u.id as user_id, u.email, u.name, u.role, u.status
                     FROM " . $this->table_name . " at
                     LEFT JOIN users u ON at.user_id = u.id
                     WHERE at.token = :token AND at.type = :type AND at.expires_at > NOW()";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':type', $type);
            $stmt->execute();

            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tokenData) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired token'
                ];
            }

            // Check if user is active
            if ($tokenData['status'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'User account is not active'
                ];
            }

            return [
                'success' => true,
                'data' => $tokenData
            ];

        } catch (Exception $e) {
            logError('AuthToken validateToken error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Token validation failed'
            ];
        }
    }

    /**
     * Revoke token
     */
    public function revokeToken($token, $userId = null) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE token = :token";
            $params = [':token' => $token];

            // Add user restriction if provided
            if ($userId) {
                $query .= " AND user_id = :user_id";
                $params[':user_id'] = $userId;
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Token revoked successfully',
                    'data' => [
                        'revoked_count' => $stmt->rowCount()
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Token not found'
                ];
            }

        } catch (Exception $e) {
            logError('AuthToken revokeToken error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to revoke token'
            ];
        }
    }

    /**
     * Revoke all tokens for a user
     */
    public function revokeAllUserTokens($userId, $type = null) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
            $params = [':user_id' => $userId];

            if ($type) {
                $query .= " AND type = :type";
                $params[':type'] = $type;
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'All user tokens revoked successfully',
                    'data' => [
                        'revoked_count' => $stmt->rowCount()
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No tokens found to revoke'
                ];
            }

        } catch (Exception $e) {
            logError('AuthToken revokeAllUserTokens error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to revoke user tokens'
            ];
        }
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens() {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE expires_at <= NOW()";

            $stmt = $this->conn->prepare($query);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Expired tokens cleaned up successfully',
                    'data' => [
                        'deleted_count' => $stmt->rowCount()
                    ]
                ];
            } else {
                throw new Exception('Failed to cleanup expired tokens');
            }

        } catch (Exception $e) {
            logError('AuthToken cleanupExpiredTokens error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user tokens
     */
    public function getUserTokens($userId, $type = null) {
        try {
            $query = "SELECT id, type, expires_at, created_at FROM " . $this->table_name . " WHERE user_id = :user_id";
            $params = [':user_id' => $userId];

            if ($type) {
                $query .= " AND type = :type";
                $params[':type'] = $type;
            }

            $query .= " ORDER BY created_at DESC";

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $tokens,
                'count' => count($tokens)
            ];

        } catch (Exception $e) {
            logError('AuthToken getUserTokens error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch user tokens'
            ];
        }
    }

    /**
     * Check if token exists
     */
    public function tokenExists($token) {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE token = :token";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            logError('AuthToken tokenExists error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get token statistics
     */
    public function getTokenStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_tokens,
                        SUM(CASE WHEN type = 'access' THEN 1 ELSE 0 END) as access_tokens,
                        SUM(CASE WHEN type = 'refresh' THEN 1 ELSE 0 END) as refresh_tokens,
                        SUM(CASE WHEN expires_at > NOW() THEN 1 ELSE 0 END) as active_tokens,
                        SUM(CASE WHEN expires_at <= NOW() THEN 1 ELSE 0 END) as expired_tokens
                     FROM " . $this->table_name;

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $stats
            ];

        } catch (Exception $e) {
            logError('AuthToken getTokenStats error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch token statistics'
            ];
        }
    }

    /**
     * Extend token expiration
     */
    public function extendTokenExpiration($token, $additionalSeconds = 3600) {
        try {
            $newExpiresAt = date('Y-m-d H:i:s', time() + $additionalSeconds);

            $query = "UPDATE " . $this->table_name . " SET expires_at = :expires_at WHERE token = :token AND expires_at > NOW()";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':expires_at', $newExpiresAt);
            $stmt->bindParam(':token', $token);

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Token expiration extended successfully',
                    'data' => [
                        'new_expires_at' => $newExpiresAt
                    ]
                ];
            } else {
                throw new Exception('Token not found or already expired');
            }

        } catch (Exception $e) {
            logError('AuthToken extendTokenExpiration error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?>
