<?php
/**
 * Product Model for IKIRAHA API
 * Handles product management, categories, and restaurant menu items
 */

class Product {
    private $conn;
    private $table_name = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all products with filters
     */
    public function getAllProducts($filters = []) {
        try {
            $query = "SELECT p.*, c.name as category_name, r.name as restaurant_name, r.rating as restaurant_rating
                     FROM " . $this->table_name . " p
                     LEFT JOIN categories c ON p.category_id = c.id
                     LEFT JOIN restaurants r ON p.restaurant_id = r.id
                     WHERE p.status = 'available'";

            $params = [];

            // Apply filters
            if (isset($filters['category_id'])) {
                $query .= " AND p.category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }

            if (isset($filters['restaurant_id'])) {
                $query .= " AND p.restaurant_id = :restaurant_id";
                $params[':restaurant_id'] = $filters['restaurant_id'];
            }

            if (isset($filters['is_featured'])) {
                $query .= " AND p.is_featured = :is_featured";
                $params[':is_featured'] = $filters['is_featured'];
            }

            if (isset($filters['search'])) {
                $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if (isset($filters['min_price'])) {
                $query .= " AND p.price >= :min_price";
                $params[':min_price'] = $filters['min_price'];
            }

            if (isset($filters['max_price'])) {
                $query .= " AND p.price <= :max_price";
                $params[':max_price'] = $filters['max_price'];
            }

            $query .= " ORDER BY p.is_featured DESC, p.created_at DESC";

            if (isset($filters['limit'])) {
                $query .= " LIMIT :limit";
                $params[':limit'] = $filters['limit'];
            }

            if (isset($filters['offset'])) {
                $query .= " OFFSET :offset";
                $params[':offset'] = $filters['offset'];
            }

            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logError('Get all products failed: ' . $e->getMessage(), $filters);
            throw $e;
        }
    }

    /**
     * Get product by ID
     */
    public function getProductById($id) {
        try {
            $query = "SELECT p.*, c.name as category_name, r.name as restaurant_name,
                            r.rating as restaurant_rating, r.delivery_time, r.delivery_fee
                     FROM " . $this->table_name . " p
                     LEFT JOIN categories c ON p.category_id = c.id
                     LEFT JOIN restaurants r ON p.restaurant_id = r.id
                     WHERE p.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return null;
            }

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logError('Get product by ID failed: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Create new product (merchant only)
     */
    public function createProduct($data, $merchantId) {
        try {
            // Validate input
            if (!$this->validateProductData($data)) {
                throw new Exception('Invalid product data');
            }

            // Verify merchant owns the restaurant
            if (!$this->verifyRestaurantOwnership($data['restaurant_id'], $merchantId)) {
                throw new Exception('Unauthorized: Restaurant not owned by merchant');
            }

            $query = "INSERT INTO " . $this->table_name . "
                     (uuid, restaurant_id, category_id, name, description, price, image, is_featured, status)
                     VALUES (:uuid, :restaurant_id, :category_id, :name, :description, :price, :image, :is_featured, :status)";

            $stmt = $this->conn->prepare($query);

            $uuid = generateUUID();
            $is_featured = isset($data['is_featured']) ? $data['is_featured'] : 0;
            $status = isset($data['status']) ? $data['status'] : 'available';

            $stmt->bindParam(':uuid', $uuid);
            $stmt->bindParam(':restaurant_id', $data['restaurant_id']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':image', $data['image']);
            $stmt->bindParam(':is_featured', $is_featured);
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                $productId = $this->conn->lastInsertId();
                return $this->getProductById($productId);
            }

            throw new Exception('Product creation failed');

        } catch (Exception $e) {
            logError('Create product failed: ' . $e->getMessage(), $data);
            throw $e;
        }
    }

    /**
     * Update product (merchant only)
     */
    public function updateProduct($id, $data, $merchantId) {
        try {
            // Verify merchant owns the product's restaurant
            $product = $this->getProductById($id);
            if (!$product) {
                throw new Exception('Product not found');
            }

            if (!$this->verifyRestaurantOwnership($product['restaurant_id'], $merchantId)) {
                throw new Exception('Unauthorized: Product not owned by merchant');
            }

            $allowedFields = ['name', 'description', 'price', 'image', 'is_featured', 'status', 'category_id'];
            $updateFields = [];
            $params = [':id' => $id];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            if (empty($updateFields)) {
                throw new Exception('No valid fields to update');
            }

            $query = "UPDATE " . $this->table_name . "
                     SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                return $this->getProductById($id);
            }

            throw new Exception('Product update failed');

        } catch (Exception $e) {
            logError('Update product failed: ' . $e->getMessage(), ['id' => $id, 'data' => $data]);
            throw $e;
        }
    }

    /**
     * Delete product (merchant only)
     */
    public function deleteProduct($id, $merchantId) {
        try {
            // Verify merchant owns the product's restaurant
            $product = $this->getProductById($id);
            if (!$product) {
                throw new Exception('Product not found');
            }

            if (!$this->verifyRestaurantOwnership($product['restaurant_id'], $merchantId)) {
                throw new Exception('Unauthorized: Product not owned by merchant');
            }

            $query = "UPDATE " . $this->table_name . "
                     SET status = 'discontinued', updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Product deleted successfully'];
            }

            throw new Exception('Product deletion failed');

        } catch (Exception $e) {
            logError('Delete product failed: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Get categories
     */
    public function getCategories() {
        try {
            $query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logError('Get categories failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts($limit = 10) {
        try {
            return $this->getAllProducts([
                'is_featured' => 1,
                'limit' => $limit
            ]);

        } catch (Exception $e) {
            logError('Get featured products failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Search products
     */
    public function searchProducts($searchTerm, $filters = []) {
        try {
            $filters['search'] = $searchTerm;
            return $this->getAllProducts($filters);

        } catch (Exception $e) {
            logError('Search products failed: ' . $e->getMessage(), ['search' => $searchTerm]);
            throw $e;
        }
    }

    /**
     * Validate product data
     */
    private function validateProductData($data) {
        $required = ['restaurant_id', 'category_id', 'name', 'price'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return false;
            }
        }

        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Verify restaurant ownership
     */
    private function verifyRestaurantOwnership($restaurantId, $merchantId) {
        try {
            $query = "SELECT id FROM restaurants WHERE id = :restaurant_id AND merchant_id = :merchant_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':restaurant_id', $restaurantId);
            $stmt->bindParam(':merchant_id', $merchantId);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            logError('Verify restaurant ownership failed: ' . $e->getMessage());
            return false;
        }
    }
}
?>