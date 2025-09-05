<?php
/**
 * UserFavorite Model for IKIRAHA API
 * Handles user favorite products management
 */

class UserFavorite {
    private $conn;
    private $table_name = "user_favorites";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all favorite products for a user
     */
    public function getUserFavorites($userId, $filters = []) {
        try {
            $query = "SELECT uf.*, p.name, p.description, p.price, p.image, p.status,
                            c.name as category_name, r.name as restaurant_name, r.rating as restaurant_rating
                     FROM " . $this->table_name . " uf
                     LEFT JOIN products p ON uf.product_id = p.id
                     LEFT JOIN categories c ON p.category_id = c.id
                     LEFT JOIN restaurants r ON p.restaurant_id = r.id
                     WHERE uf.user_id = :user_id AND p.status = 'available'";

            $params = [':user_id' => $userId];

            // Apply filters
            if (isset($filters['category_id'])) {
                $query .= " AND p.category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }

            if (isset($filters['restaurant_id'])) {
                $query .= " AND p.restaurant_id = :restaurant_id";
                $params[':restaurant_id'] = $filters['restaurant_id'];
            }

            // Sorting
            $sortBy = isset($filters['sort_by']) ? $filters['sort_by'] : 'created_at';
            $sortOrder = isset($filters['sort_order']) && $filters['sort_order'] === 'asc' ? 'ASC' : 'DESC';
            
            $validSortFields = ['created_at', 'name', 'price'];
            if (in_array($sortBy, $validSortFields)) {
                if ($sortBy === 'name' || $sortBy === 'price') {
                    $query .= " ORDER BY p." . $sortBy . " " . $sortOrder;
                } else {
                    $query .= " ORDER BY uf." . $sortBy . " " . $sortOrder;
                }
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
            $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $favorites,
                'count' => count($favorites)
            ];

        } catch (Exception $e) {
            logError('UserFavorite getUserFavorites error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch user favorites'
            ];
        }
    }

    /**
     * Add product to favorites
     */
    public function addToFavorites($userId, $productId) {
        try {
            // Check if product exists and is available
            if (!$this->productExists($productId)) {
                throw new Exception('Product not found or unavailable');
            }

            // Check if already in favorites
            if ($this->isFavorite($userId, $productId)) {
                return [
                    'success' => false,
                    'message' => 'Product already in favorites'
                ];
            }

            $query = "INSERT INTO " . $this->table_name . " (user_id, product_id) VALUES (:user_id, :product_id)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':product_id', $productId);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Product added to favorites successfully'
                ];
            } else {
                throw new Exception('Failed to add product to favorites');
            }

        } catch (Exception $e) {
            logError('UserFavorite addToFavorites error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Remove product from favorites
     */
    public function removeFromFavorites($userId, $productId) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id AND product_id = :product_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':product_id', $productId);

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Product removed from favorites successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Product not found in favorites'
                ];
            }

        } catch (Exception $e) {
            logError('UserFavorite removeFromFavorites error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to remove product from favorites'
            ];
        }
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite($userId, $productId) {
        try {
            if ($this->isFavorite($userId, $productId)) {
                return $this->removeFromFavorites($userId, $productId);
            } else {
                return $this->addToFavorites($userId, $productId);
            }

        } catch (Exception $e) {
            logError('UserFavorite toggleFavorite error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to toggle favorite status'
            ];
        }
    }

    /**
     * Check if product is in user's favorites
     */
    public function isFavorite($userId, $productId) {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE user_id = :user_id AND product_id = :product_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':product_id', $productId);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            logError('UserFavorite isFavorite error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get favorite count for a user
     */
    public function getFavoriteCount($userId) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " uf
                     LEFT JOIN products p ON uf.product_id = p.id
                     WHERE uf.user_id = :user_id AND p.status = 'available'";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => [
                    'count' => (int)$result['count']
                ]
            ];

        } catch (Exception $e) {
            logError('UserFavorite getFavoriteCount error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get favorite count'
            ];
        }
    }

    /**
     * Get most favorited products
     */
    public function getMostFavoritedProducts($limit = 10) {
        try {
            $query = "SELECT p.*, c.name as category_name, r.name as restaurant_name, 
                            COUNT(uf.id) as favorite_count
                     FROM products p
                     LEFT JOIN " . $this->table_name . " uf ON p.id = uf.product_id
                     LEFT JOIN categories c ON p.category_id = c.id
                     LEFT JOIN restaurants r ON p.restaurant_id = r.id
                     WHERE p.status = 'available'
                     GROUP BY p.id
                     HAVING favorite_count > 0
                     ORDER BY favorite_count DESC, p.name ASC
                     LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();

            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $products,
                'count' => count($products)
            ];

        } catch (Exception $e) {
            logError('UserFavorite getMostFavoritedProducts error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch most favorited products'
            ];
        }
    }

    /**
     * Clear all favorites for a user
     */
    public function clearUserFavorites($userId) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'All favorites cleared successfully',
                    'data' => [
                        'deleted_count' => $stmt->rowCount()
                    ]
                ];
            } else {
                throw new Exception('Failed to clear favorites');
            }

        } catch (Exception $e) {
            logError('UserFavorite clearUserFavorites error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if product exists and is available
     */
    private function productExists($productId) {
        try {
            $query = "SELECT id FROM products WHERE id = :product_id AND status = 'available'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $productId);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (Exception $e) {
            return false;
        }
    }
}
?>
