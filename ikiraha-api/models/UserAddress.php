<?php
/**
 * UserAddress Model for IKIRAHA API
 * Handles user delivery addresses management
 */

class UserAddress {
    private $conn;
    private $table_name = "user_addresses";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all addresses for a user
     */
    public function getUserAddresses($userId) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id ORDER BY is_default DESC, created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $addresses,
                'count' => count($addresses)
            ];

        } catch (Exception $e) {
            logError('UserAddress getUserAddresses error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch user addresses'
            ];
        }
    }

    /**
     * Get address by ID
     */
    public function getAddressById($id, $userId = null) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $params = [':id' => $id];

            // Add user restriction if provided
            if ($userId) {
                $query .= " AND user_id = :user_id";
                $params[':user_id'] = $userId;
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $address = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$address) {
                return [
                    'success' => false,
                    'message' => 'Address not found'
                ];
            }

            return [
                'success' => true,
                'data' => $address
            ];

        } catch (Exception $e) {
            logError('UserAddress getAddressById error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch address'
            ];
        }
    }

    /**
     * Create new address
     */
    public function createAddress($data, $userId) {
        try {
            // Validate input
            if (!$this->validateAddressData($data)) {
                throw new Exception('Invalid address data');
            }

            $this->conn->beginTransaction();

            // If this is set as default, unset other default addresses
            if (isset($data['is_default']) && $data['is_default']) {
                $this->unsetDefaultAddresses($userId);
            }

            $query = "INSERT INTO " . $this->table_name . "
                     (user_id, type, address, phone, is_default)
                     VALUES (:user_id, :type, :address, :phone, :is_default)";

            $stmt = $this->conn->prepare($query);

            $isDefault = isset($data['is_default']) ? (int)$data['is_default'] : 0;

            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':type', $data['type']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':is_default', $isDefault);

            if ($stmt->execute()) {
                $addressId = $this->conn->lastInsertId();
                $this->conn->commit();
                
                return [
                    'success' => true,
                    'message' => 'Address created successfully',
                    'data' => [
                        'id' => $addressId
                    ]
                ];
            } else {
                $this->conn->rollBack();
                throw new Exception('Failed to create address');
            }

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            logError('UserAddress createAddress error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update address
     */
    public function updateAddress($id, $data, $userId) {
        try {
            // Validate input
            if (!$this->validateAddressData($data, false)) {
                throw new Exception('Invalid address data');
            }

            $this->conn->beginTransaction();

            // If this is set as default, unset other default addresses
            if (isset($data['is_default']) && $data['is_default']) {
                $this->unsetDefaultAddresses($userId);
            }

            // Build dynamic query
            $fields = [];
            $params = [];

            if (isset($data['type'])) {
                $fields[] = "type = :type";
                $params[':type'] = $data['type'];
            }

            if (isset($data['address'])) {
                $fields[] = "address = :address";
                $params[':address'] = $data['address'];
            }

            if (isset($data['phone'])) {
                $fields[] = "phone = :phone";
                $params[':phone'] = $data['phone'];
            }

            if (isset($data['is_default'])) {
                $fields[] = "is_default = :is_default";
                $params[':is_default'] = (int)$data['is_default'];
            }

            if (empty($fields)) {
                throw new Exception('No fields to update');
            }

            $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :user_id";
            $params[':id'] = $id;
            $params[':user_id'] = $userId;

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                $this->conn->commit();
                return [
                    'success' => true,
                    'message' => 'Address updated successfully'
                ];
            } else {
                $this->conn->rollBack();
                throw new Exception('Address not found or no changes made');
            }

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            logError('UserAddress updateAddress error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete address
     */
    public function deleteAddress($id, $userId) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Address deleted successfully'
                ];
            } else {
                throw new Exception('Address not found');
            }

        } catch (Exception $e) {
            logError('UserAddress deleteAddress error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Set address as default
     */
    public function setDefaultAddress($id, $userId) {
        try {
            $this->conn->beginTransaction();

            // Unset all default addresses for user
            $this->unsetDefaultAddresses($userId);

            // Set the specified address as default
            $query = "UPDATE " . $this->table_name . " SET is_default = 1 WHERE id = :id AND user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                $this->conn->commit();
                return [
                    'success' => true,
                    'message' => 'Default address updated successfully'
                ];
            } else {
                $this->conn->rollBack();
                throw new Exception('Address not found');
            }

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            logError('UserAddress setDefaultAddress error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get default address for user
     */
    public function getDefaultAddress($userId) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id AND is_default = 1 LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $address = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$address) {
                return [
                    'success' => false,
                    'message' => 'No default address found'
                ];
            }

            return [
                'success' => true,
                'data' => $address
            ];

        } catch (Exception $e) {
            logError('UserAddress getDefaultAddress error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch default address'
            ];
        }
    }

    /**
     * Unset all default addresses for a user
     */
    private function unsetDefaultAddresses($userId) {
        try {
            $query = "UPDATE " . $this->table_name . " SET is_default = 0 WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
        } catch (Exception $e) {
            logError('UserAddress unsetDefaultAddresses error: ' . $e->getMessage());
        }
    }

    /**
     * Validate address data
     */
    private function validateAddressData($data, $isCreate = true) {
        if ($isCreate) {
            if (empty($data['address']) || empty($data['type'])) {
                return false;
            }
        }

        if (isset($data['type']) && !in_array($data['type'], ['home', 'work', 'other'])) {
            return false;
        }

        if (isset($data['address']) && strlen($data['address']) < 10) {
            return false;
        }

        if (isset($data['phone']) && !empty($data['phone']) && !preg_match('/^\+?[0-9\s\-\(\)]{10,20}$/', $data['phone'])) {
            return false;
        }

        return true;
    }
}
?>
