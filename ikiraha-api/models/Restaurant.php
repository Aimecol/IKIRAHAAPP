<?php
/**
 * Restaurant Model for IKIRAHA API
 * Handles restaurant management and merchant operations
 */

class Restaurant {
    private $conn;
    private $table_name = "restaurants";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all restaurants with filters
     */
    public function getAllRestaurants($filters = []) {
        try {
            $query = "SELECT r.*, u.name as merchant_name, u.phone as merchant_phone, u.email as merchant_email
                     FROM " . $this->table_name . " r
                     LEFT JOIN users u ON r.merchant_id = u.id
                     WHERE r.status = 'active'";

            $params = [];

            // Apply filters
            if (isset($filters['merchant_id'])) {
                $query .= " AND r.merchant_id = :merchant_id";
                $params[':merchant_id'] = $filters['merchant_id'];
            }

            if (isset($filters['search'])) {
                $query .= " AND (r.name LIKE :search OR r.description LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if (isset($filters['min_rating'])) {
                $query .= " AND r.rating >= :min_rating";
                $params[':min_rating'] = $filters['min_rating'];
            }

            if (isset($filters['max_delivery_fee'])) {
                $query .= " AND r.delivery_fee <= :max_delivery_fee";
                $params[':max_delivery_fee'] = $filters['max_delivery_fee'];
            }

            // Sorting
            $sortBy = isset($filters['sort_by']) ? $filters['sort_by'] : 'name';
            $sortOrder = isset($filters['sort_order']) && $filters['sort_order'] === 'desc' ? 'DESC' : 'ASC';
            
            $validSortFields = ['name', 'rating', 'delivery_fee', 'created_at'];
            if (in_array($sortBy, $validSortFields)) {
                $query .= " ORDER BY r." . $sortBy . " " . $sortOrder;
            }

            // Pagination
            if (isset($filters['limit'])) {
                $limit = min((int)$filters['limit'], 100);
                $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
                $query .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $limit;
                $params[':offset'] = $offset;
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $restaurants,
                'count' => count($restaurants)
            ];

        } catch (Exception $e) {
            logError('Restaurant getAllRestaurants error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch restaurants'
            ];
        }
    }

    /**
     * Get restaurant by ID
     */
    public function getRestaurantById($id) {
        try {
            $query = "SELECT r.*, u.name as merchant_name, u.phone as merchant_phone, u.email as merchant_email
                     FROM " . $this->table_name . " r
                     LEFT JOIN users u ON r.merchant_id = u.id
                     WHERE r.id = :id OR r.uuid = :uuid";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':uuid', $id);
            $stmt->execute();

            $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$restaurant) {
                return [
                    'success' => false,
                    'message' => 'Restaurant not found'
                ];
            }

            return [
                'success' => true,
                'data' => $restaurant
            ];

        } catch (Exception $e) {
            logError('Restaurant getRestaurantById error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch restaurant'
            ];
        }
    }

    /**
     * Create new restaurant
     */
    public function createRestaurant($data, $merchantId) {
        try {
            // Validate input
            if (!$this->validateRestaurantData($data)) {
                throw new Exception('Invalid restaurant data');
            }

            $query = "INSERT INTO " . $this->table_name . "
                     (uuid, merchant_id, name, description, image, delivery_time, delivery_fee, status)
                     VALUES (:uuid, :merchant_id, :name, :description, :image, :delivery_time, :delivery_fee, :status)";

            $stmt = $this->conn->prepare($query);

            $uuid = generateUUID();
            $status = 'active';

            $stmt->bindParam(':uuid', $uuid);
            $stmt->bindParam(':merchant_id', $merchantId);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':image', $data['image']);
            $stmt->bindParam(':delivery_time', $data['delivery_time']);
            $stmt->bindParam(':delivery_fee', $data['delivery_fee']);
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                $restaurantId = $this->conn->lastInsertId();
                
                return [
                    'success' => true,
                    'message' => 'Restaurant created successfully',
                    'data' => [
                        'id' => $restaurantId,
                        'uuid' => $uuid
                    ]
                ];
            } else {
                throw new Exception('Failed to create restaurant');
            }

        } catch (Exception $e) {
            logError('Restaurant createRestaurant error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update restaurant
     */
    public function updateRestaurant($id, $data, $merchantId = null) {
        try {
            // Validate input
            if (!$this->validateRestaurantData($data, false)) {
                throw new Exception('Invalid restaurant data');
            }

            // Build dynamic query
            $fields = [];
            $params = [];

            if (isset($data['name'])) {
                $fields[] = "name = :name";
                $params[':name'] = $data['name'];
            }

            if (isset($data['description'])) {
                $fields[] = "description = :description";
                $params[':description'] = $data['description'];
            }

            if (isset($data['image'])) {
                $fields[] = "image = :image";
                $params[':image'] = $data['image'];
            }

            if (isset($data['delivery_time'])) {
                $fields[] = "delivery_time = :delivery_time";
                $params[':delivery_time'] = $data['delivery_time'];
            }

            if (isset($data['delivery_fee'])) {
                $fields[] = "delivery_fee = :delivery_fee";
                $params[':delivery_fee'] = $data['delivery_fee'];
            }

            if (isset($data['status'])) {
                $fields[] = "status = :status";
                $params[':status'] = $data['status'];
            }

            if (empty($fields)) {
                throw new Exception('No fields to update');
            }

            $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $fields) . " WHERE (id = :id OR uuid = :uuid)";
            
            // Add merchant restriction if provided
            if ($merchantId) {
                $query .= " AND merchant_id = :merchant_id";
                $params[':merchant_id'] = $merchantId;
            }

            $params[':id'] = $id;
            $params[':uuid'] = $id;

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Restaurant updated successfully'
                ];
            } else {
                throw new Exception('Restaurant not found or no changes made');
            }

        } catch (Exception $e) {
            logError('Restaurant updateRestaurant error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete restaurant (soft delete by setting status to inactive)
     */
    public function deleteRestaurant($id, $merchantId = null) {
        try {
            $query = "UPDATE " . $this->table_name . " SET status = 'inactive' WHERE (id = :id OR uuid = :uuid)";
            
            $params = [':id' => $id, ':uuid' => $id];
            
            // Add merchant restriction if provided
            if ($merchantId) {
                $query .= " AND merchant_id = :merchant_id";
                $params[':merchant_id'] = $merchantId;
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Restaurant deleted successfully'
                ];
            } else {
                throw new Exception('Restaurant not found');
            }

        } catch (Exception $e) {
            logError('Restaurant deleteRestaurant error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get restaurants by merchant ID
     */
    public function getRestaurantsByMerchant($merchantId) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE merchant_id = :merchant_id ORDER BY created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':merchant_id', $merchantId);
            $stmt->execute();

            $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $restaurants,
                'count' => count($restaurants)
            ];

        } catch (Exception $e) {
            logError('Restaurant getRestaurantsByMerchant error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch merchant restaurants'
            ];
        }
    }

    /**
     * Validate restaurant data
     */
    private function validateRestaurantData($data, $isCreate = true) {
        if ($isCreate) {
            if (empty($data['name']) || empty($data['description'])) {
                return false;
            }
        }

        if (isset($data['name']) && (strlen($data['name']) < 2 || strlen($data['name']) > 255)) {
            return false;
        }

        if (isset($data['delivery_fee']) && (!is_numeric($data['delivery_fee']) || $data['delivery_fee'] < 0)) {
            return false;
        }

        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive', 'suspended'])) {
            return false;
        }

        return true;
    }
}
?>
