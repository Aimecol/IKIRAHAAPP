<?php
/**
 * Category Model for IKIRAHA API
 * Handles food category management
 */

class Category {
    private $conn;
    private $table_name = "categories";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all categories
     */
    public function getAllCategories($filters = []) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'active'";

            $params = [];

            if (isset($filters['search'])) {
                $query .= " AND name LIKE :search";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            $query .= " ORDER BY name ASC";

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
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $categories,
                'count' => count($categories)
            ];

        } catch (Exception $e) {
            logError('Category getAllCategories error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch categories'
            ];
        }
    }

    /**
     * Get category by ID
     */
    public function getCategoryById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Category not found'
                ];
            }

            return [
                'success' => true,
                'data' => $category
            ];

        } catch (Exception $e) {
            logError('Category getCategoryById error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch category'
            ];
        }
    }

    /**
     * Create new category
     */
    public function createCategory($data) {
        try {
            // Validate input
            if (!$this->validateCategoryData($data)) {
                throw new Exception('Invalid category data');
            }

            // Check if category name already exists
            if ($this->categoryNameExists($data['name'])) {
                throw new Exception('Category name already exists');
            }

            $query = "INSERT INTO " . $this->table_name . " (name, icon, status) VALUES (:name, :icon, :status)";

            $stmt = $this->conn->prepare($query);

            $status = 'active';

            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':icon', $data['icon']);
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                $categoryId = $this->conn->lastInsertId();
                
                return [
                    'success' => true,
                    'message' => 'Category created successfully',
                    'data' => [
                        'id' => $categoryId
                    ]
                ];
            } else {
                throw new Exception('Failed to create category');
            }

        } catch (Exception $e) {
            logError('Category createCategory error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update category
     */
    public function updateCategory($id, $data) {
        try {
            // Validate input
            if (!$this->validateCategoryData($data, false)) {
                throw new Exception('Invalid category data');
            }

            // Check if new name already exists (if name is being updated)
            if (isset($data['name']) && $this->categoryNameExists($data['name'], $id)) {
                throw new Exception('Category name already exists');
            }

            // Build dynamic query
            $fields = [];
            $params = [];

            if (isset($data['name'])) {
                $fields[] = "name = :name";
                $params[':name'] = $data['name'];
            }

            if (isset($data['icon'])) {
                $fields[] = "icon = :icon";
                $params[':icon'] = $data['icon'];
            }

            if (isset($data['status'])) {
                $fields[] = "status = :status";
                $params[':status'] = $data['status'];
            }

            if (empty($fields)) {
                throw new Exception('No fields to update');
            }

            $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $fields) . " WHERE id = :id";
            $params[':id'] = $id;

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Category updated successfully'
                ];
            } else {
                throw new Exception('Category not found or no changes made');
            }

        } catch (Exception $e) {
            logError('Category updateCategory error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete category (soft delete by setting status to inactive)
     */
    public function deleteCategory($id) {
        try {
            // Check if category has products
            $productQuery = "SELECT COUNT(*) as count FROM products WHERE category_id = :category_id AND status != 'discontinued'";
            $productStmt = $this->conn->prepare($productQuery);
            $productStmt->bindParam(':category_id', $id);
            $productStmt->execute();
            $productCount = $productStmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($productCount > 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete category with active products'
                ];
            }

            $query = "UPDATE " . $this->table_name . " SET status = 'inactive' WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Category deleted successfully'
                ];
            } else {
                throw new Exception('Category not found');
            }

        } catch (Exception $e) {
            logError('Category deleteCategory error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get categories with product count
     */
    public function getCategoriesWithProductCount() {
        try {
            $query = "SELECT c.*, COUNT(p.id) as product_count 
                     FROM " . $this->table_name . " c
                     LEFT JOIN products p ON c.id = p.category_id AND p.status = 'available'
                     WHERE c.status = 'active'
                     GROUP BY c.id
                     ORDER BY c.name ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $categories,
                'count' => count($categories)
            ];

        } catch (Exception $e) {
            logError('Category getCategoriesWithProductCount error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch categories with product count'
            ];
        }
    }

    /**
     * Check if category name exists
     */
    private function categoryNameExists($name, $excludeId = null) {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE name = :name";
            
            if ($excludeId) {
                $query .= " AND id != :exclude_id";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            
            if ($excludeId) {
                $stmt->bindParam(':exclude_id', $excludeId);
            }

            $stmt->execute();
            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Validate category data
     */
    private function validateCategoryData($data, $isCreate = true) {
        if ($isCreate) {
            if (empty($data['name'])) {
                return false;
            }
        }

        if (isset($data['name']) && (strlen($data['name']) < 2 || strlen($data['name']) > 100)) {
            return false;
        }

        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            return false;
        }

        return true;
    }
}
?>
