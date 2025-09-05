<?php
/**
 * PasswordReset Model for IKIRAHA API
 * Handles password reset token management
 */

class PasswordReset
{
    private $conn;
    private $table = 'password_resets';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Create a new password reset record
     */
    public function create($data)
    {
        try {
            $query = "INSERT INTO " . $this->table . " 
                     (user_id, email, token, expires_at, created_at, used) 
                     VALUES (:user_id, :email, :token, :expires_at, :created_at, :used)";

            $stmt = $this->conn->prepare($query);

            // Bind parameters
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':token', $data['token']);
            $stmt->bindParam(':expires_at', $data['expires_at']);
            $stmt->bindParam(':created_at', $data['created_at']);
            $stmt->bindParam(':used', $data['used']);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }

            return false;

        } catch (PDOException $e) {
            error_log("Create password reset error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find password reset by token
     */
    public function findByToken($hashedToken)
    {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE token = :token LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $hashedToken);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Find by token error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find password reset by email
     */
    public function findByEmail($email)
    {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                     WHERE email = :email 
                     AND expires_at > NOW() 
                     AND used = 0
                     ORDER BY created_at DESC 
                     LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Find by email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete password resets by user ID
     */
    public function deleteByUserId($userId)
    {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Delete by user ID error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark token as used
     */
    public function markAsUsed($id)
    {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET used = 1, updated_at = NOW() 
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Mark as used error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a password reset record
     */
    public function delete($id)
    {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Delete password reset error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens()
    {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE expires_at < NOW()";
            $stmt = $this->conn->prepare($query);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Cleanup expired tokens error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active reset count for email (for rate limiting)
     */
    public function getActiveResetCount($email, $timeWindow = 3600)
    {
        try {
            $query = "SELECT COUNT(*) as count 
                     FROM " . $this->table . " 
                     WHERE email = :email 
                     AND created_at > DATE_SUB(NOW(), INTERVAL :time_window SECOND)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':time_window', $timeWindow);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['count'] : 0;

        } catch (PDOException $e) {
            error_log("Get active reset count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all password resets for a user
     */
    public function getByUserId($userId)
    {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                     WHERE user_id = :user_id 
                     ORDER BY created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Get by user ID error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if token exists and is valid
     */
    public function isValidToken($token)
    {
        try {
            $hashedToken = hash('sha256', $token);
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                     WHERE token = :token 
                     AND expires_at > NOW() 
                     AND used = 0";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $hashedToken);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['count'] > 0;

        } catch (PDOException $e) {
            error_log("Is valid token error: " . $e->getMessage());
            return false;
        }
    }
}
