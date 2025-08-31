<?php
/**
 * Order Model for IKIRAHA API
 * Handles order creation, management, and status updates
 */

class Order {
    private $conn;
    private $table_name = "orders";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create new order
     */
    public function createOrder($data, $clientId) {
        try {
            $this->conn->beginTransaction();

            // Validate order data
            if (!$this->validateOrderData($data)) {
                throw new Exception('Invalid order data');
            }

            // Calculate total amount
            $totalAmount = $this->calculateOrderTotal($data['items']);
            $deliveryFee = isset($data['delivery_fee']) ? $data['delivery_fee'] : 0;
            $finalTotal = $totalAmount + $deliveryFee;

            // Generate order number
            $orderNumber = generateOrderNumber();
            $uuid = generateUUID();

            // Insert order
            $query = "INSERT INTO " . $this->table_name . "
                     (uuid, order_number, client_id, restaurant_id, status, total_amount, delivery_fee,
                      payment_method, payment_phone, delivery_address, delivery_phone, notes, estimated_delivery_time)
                     VALUES (:uuid, :order_number, :client_id, :restaurant_id, :status, :total_amount, :delivery_fee,
                             :payment_method, :payment_phone, :delivery_address, :delivery_phone, :notes, :estimated_delivery_time)";

            $stmt = $this->conn->prepare($query);

            $estimatedDeliveryTime = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $stmt->bindParam(':uuid', $uuid);
            $stmt->bindParam(':order_number', $orderNumber);
            $stmt->bindParam(':client_id', $clientId);
            $stmt->bindParam(':restaurant_id', $data['restaurant_id']);
            $stmt->bindValue(':status', 'pending');
            $stmt->bindParam(':total_amount', $finalTotal);
            $stmt->bindParam(':delivery_fee', $deliveryFee);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->bindParam(':payment_phone', $data['payment_phone']);
            $stmt->bindParam(':delivery_address', $data['delivery_address']);
            $stmt->bindParam(':delivery_phone', $data['delivery_phone']);
            $stmt->bindParam(':notes', $data['notes']);
            $stmt->bindParam(':estimated_delivery_time', $estimatedDeliveryTime);

            if (!$stmt->execute()) {
                throw new Exception('Order creation failed');
            }

            $orderId = $this->conn->lastInsertId();

            // Insert order items
            $this->insertOrderItems($orderId, $data['items']);

            // Create transaction record
            $this->createTransaction($orderId, $finalTotal, $data['payment_method']);

            $this->conn->commit();

            return $this->getOrderById($orderId);

        } catch (Exception $e) {
            $this->conn->rollback();
            logError('Create order failed: ' . $e->getMessage(), $data);
            throw $e;
        }
    }

    /**
     * Get order by ID
     */
    public function getOrderById($id) {
        try {
            $query = "SELECT o.*, r.name as restaurant_name, u.name as client_name, u.phone as client_phone
                     FROM " . $this->table_name . " o
                     LEFT JOIN restaurants r ON o.restaurant_id = r.id
                     LEFT JOIN users u ON o.client_id = u.id
                     WHERE o.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return null;
            }

            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get order items
            $order['items'] = $this->getOrderItems($id);

            return $order;

        } catch (Exception $e) {
            logError('Get order by ID failed: ' . $e->getMessage(), ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Get orders by user ID
     */
    public function getOrdersByUserId($userId, $limit = 20, $offset = 0) {
        try {
            $query = "SELECT o.*, r.name as restaurant_name
                     FROM " . $this->table_name . " o
                     LEFT JOIN restaurants r ON o.restaurant_id = r.id
                     WHERE o.client_id = :user_id
                     ORDER BY o.created_at DESC
                     LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get items for each order
            foreach ($orders as &$order) {
                $order['items'] = $this->getOrderItems($order['id']);
            }

            return $orders;

        } catch (Exception $e) {
            logError('Get orders by user ID failed: ' . $e->getMessage(), ['user_id' => $userId]);
            throw $e;
        }
    }

    /**
     * Get orders by restaurant ID (for merchants)
     */
    public function getOrdersByRestaurantId($restaurantId, $limit = 50, $offset = 0) {
        try {
            $query = "SELECT o.*, u.name as client_name, u.phone as client_phone
                     FROM " . $this->table_name . " o
                     LEFT JOIN users u ON o.client_id = u.id
                     WHERE o.restaurant_id = :restaurant_id
                     ORDER BY o.created_at DESC
                     LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':restaurant_id', $restaurantId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get items for each order
            foreach ($orders as &$order) {
                $order['items'] = $this->getOrderItems($order['id']);
            }

            return $orders;

        } catch (Exception $e) {
            logError('Get orders by restaurant ID failed: ' . $e->getMessage(), ['restaurant_id' => $restaurantId]);
            throw $e;
        }
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($orderId, $status, $userId = null) {
        try {
            $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'];

            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid order status');
            }

            $query = "UPDATE " . $this->table_name . "
                     SET status = :status, updated_at = CURRENT_TIMESTAMP";

            $params = [':status' => $status, ':id' => $orderId];

            // Set delivered timestamp if status is delivered
            if ($status === 'delivered') {
                $query .= ", delivered_at = CURRENT_TIMESTAMP";
            }

            $query .= " WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                return $this->getOrderById($orderId);
            }

            throw new Exception('Order status update failed');

        } catch (Exception $e) {
            logError('Update order status failed: ' . $e->getMessage(), ['order_id' => $orderId, 'status' => $status]);
            throw $e;
        }
    }

    /**
     * Get order items
     */
    private function getOrderItems($orderId) {
        try {
            $query = "SELECT oi.*, p.name as product_name, p.image as product_image
                     FROM order_items oi
                     LEFT JOIN products p ON oi.product_id = p.id
                     WHERE oi.order_id = :order_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            logError('Get order items failed: ' . $e->getMessage(), ['order_id' => $orderId]);
            return [];
        }
    }

    /**
     * Insert order items
     */
    private function insertOrderItems($orderId, $items) {
        try {
            $query = "INSERT INTO order_items (order_id, product_id, quantity, price)
                     VALUES (:order_id, :product_id, :quantity, :price)";

            $stmt = $this->conn->prepare($query);

            foreach ($items as $item) {
                $stmt->bindParam(':order_id', $orderId);
                $stmt->bindParam(':product_id', $item['product_id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $item['price']);

                if (!$stmt->execute()) {
                    throw new Exception('Failed to insert order item');
                }
            }

            return true;

        } catch (Exception $e) {
            logError('Insert order items failed: ' . $e->getMessage(), ['order_id' => $orderId, 'items' => $items]);
            throw $e;
        }
    }

    /**
     * Calculate order total
     */
    private function calculateOrderTotal($items) {
        $total = 0;

        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    /**
     * Create transaction record
     */
    private function createTransaction($orderId, $amount, $paymentMethod) {
        try {
            $query = "INSERT INTO transactions (uuid, order_id, amount, payment_method, status)
                     VALUES (:uuid, :order_id, :amount, :payment_method, :status)";

            $stmt = $this->conn->prepare($query);

            $uuid = generateUUID();
            $status = 'pending';

            $stmt->bindParam(':uuid', $uuid);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':payment_method', $paymentMethod);
            $stmt->bindParam(':status', $status);

            return $stmt->execute();

        } catch (Exception $e) {
            logError('Create transaction failed: ' . $e->getMessage(), ['order_id' => $orderId]);
            throw $e;
        }
    }

    /**
     * Validate order data
     */
    private function validateOrderData($data) {
        $required = ['restaurant_id', 'items', 'payment_method', 'delivery_address'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }

        if (!is_array($data['items']) || empty($data['items'])) {
            return false;
        }

        foreach ($data['items'] as $item) {
            if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                return false;
            }

            if (!is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                return false;
            }

            if (!is_numeric($item['price']) || $item['price'] <= 0) {
                return false;
            }
        }

        $validPaymentMethods = ['mtn_rwanda', 'airtel_rwanda', 'cash'];
        if (!in_array($data['payment_method'], $validPaymentMethods)) {
            return false;
        }

        return true;
    }

    /**
     * Get all orders (admin/accountant only)
     */
    public function getAllOrders($filters = [], $limit = 50, $offset = 0) {
        try {
            $query = "SELECT o.*, r.name as restaurant_name, u.name as client_name, u.phone as client_phone
                     FROM " . $this->table_name . " o
                     LEFT JOIN restaurants r ON o.restaurant_id = r.id
                     LEFT JOIN users u ON o.client_id = u.id
                     WHERE 1=1";

            $params = [];

            if (isset($filters['status'])) {
                $query .= " AND o.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (isset($filters['payment_status'])) {
                $query .= " AND o.payment_status = :payment_status";
                $params[':payment_status'] = $filters['payment_status'];
            }

            if (isset($filters['date_from'])) {
                $query .= " AND DATE(o.created_at) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            if (isset($filters['date_to'])) {
                $query .= " AND DATE(o.created_at) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            $query .= " ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;

            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }

            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get items for each order
            foreach ($orders as &$order) {
                $order['items'] = $this->getOrderItems($order['id']);
            }

            return $orders;

        } catch (Exception $e) {
            logError('Get all orders failed: ' . $e->getMessage(), $filters);
            throw $e;
        }
    }
}
?>