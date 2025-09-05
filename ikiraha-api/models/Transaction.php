<?php
/**
 * Transaction Model for IKIRAHA API
 * Handles payment transactions and financial records
 */

class Transaction {
    private $conn;
    private $table_name = "transactions";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create new transaction
     */
    public function createTransaction($data) {
        try {
            // Validate input
            if (!$this->validateTransactionData($data)) {
                throw new Exception('Invalid transaction data');
            }

            $query = "INSERT INTO " . $this->table_name . "
                     (uuid, order_id, transaction_id, amount, payment_method, status, reference)
                     VALUES (:uuid, :order_id, :transaction_id, :amount, :payment_method, :status, :reference)";

            $stmt = $this->conn->prepare($query);

            $uuid = generateUUID();
            $status = isset($data['status']) ? $data['status'] : 'pending';

            $stmt->bindParam(':uuid', $uuid);
            $stmt->bindParam(':order_id', $data['order_id']);
            $stmt->bindParam(':transaction_id', $data['transaction_id']);
            $stmt->bindParam(':amount', $data['amount']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':reference', $data['reference']);

            if ($stmt->execute()) {
                $transactionId = $this->conn->lastInsertId();
                
                return [
                    'success' => true,
                    'message' => 'Transaction created successfully',
                    'data' => [
                        'id' => $transactionId,
                        'uuid' => $uuid
                    ]
                ];
            } else {
                throw new Exception('Failed to create transaction');
            }

        } catch (Exception $e) {
            logError('Transaction createTransaction error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get transaction by ID
     */
    public function getTransactionById($id) {
        try {
            $query = "SELECT t.*, o.order_number, o.client_id, u.name as client_name
                     FROM " . $this->table_name . " t
                     LEFT JOIN orders o ON t.order_id = o.id
                     LEFT JOIN users u ON o.client_id = u.id
                     WHERE t.id = :id OR t.uuid = :uuid";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':uuid', $id);
            $stmt->execute();

            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found'
                ];
            }

            return [
                'success' => true,
                'data' => $transaction
            ];

        } catch (Exception $e) {
            logError('Transaction getTransactionById error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch transaction'
            ];
        }
    }

    /**
     * Get transactions by order ID
     */
    public function getTransactionsByOrder($orderId) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE order_id = :order_id ORDER BY created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();

            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $transactions,
                'count' => count($transactions)
            ];

        } catch (Exception $e) {
            logError('Transaction getTransactionsByOrder error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch order transactions'
            ];
        }
    }

    /**
     * Update transaction status
     */
    public function updateTransactionStatus($id, $status, $reference = null) {
        try {
            // Validate status
            $validStatuses = ['pending', 'completed', 'failed', 'cancelled', 'refunded'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid transaction status');
            }

            $query = "UPDATE " . $this->table_name . " SET status = :status";
            $params = [':status' => $status];

            if ($reference !== null) {
                $query .= ", reference = :reference";
                $params[':reference'] = $reference;
            }

            $query .= " WHERE id = :id OR uuid = :uuid";
            $params[':id'] = $id;
            $params[':uuid'] = $id;

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Transaction status updated successfully'
                ];
            } else {
                throw new Exception('Transaction not found');
            }

        } catch (Exception $e) {
            logError('Transaction updateTransactionStatus error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all transactions with filters
     */
    public function getAllTransactions($filters = []) {
        try {
            $query = "SELECT t.*, o.order_number, o.client_id, u.name as client_name, r.name as restaurant_name
                     FROM " . $this->table_name . " t
                     LEFT JOIN orders o ON t.order_id = o.id
                     LEFT JOIN users u ON o.client_id = u.id
                     LEFT JOIN restaurants r ON o.restaurant_id = r.id
                     WHERE 1=1";

            $params = [];

            // Apply filters
            if (isset($filters['status'])) {
                $query .= " AND t.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (isset($filters['payment_method'])) {
                $query .= " AND t.payment_method = :payment_method";
                $params[':payment_method'] = $filters['payment_method'];
            }

            if (isset($filters['client_id'])) {
                $query .= " AND o.client_id = :client_id";
                $params[':client_id'] = $filters['client_id'];
            }

            if (isset($filters['restaurant_id'])) {
                $query .= " AND o.restaurant_id = :restaurant_id";
                $params[':restaurant_id'] = $filters['restaurant_id'];
            }

            if (isset($filters['date_from'])) {
                $query .= " AND DATE(t.created_at) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            if (isset($filters['date_to'])) {
                $query .= " AND DATE(t.created_at) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            if (isset($filters['min_amount'])) {
                $query .= " AND t.amount >= :min_amount";
                $params[':min_amount'] = $filters['min_amount'];
            }

            if (isset($filters['max_amount'])) {
                $query .= " AND t.amount <= :max_amount";
                $params[':max_amount'] = $filters['max_amount'];
            }

            // Sorting
            $sortBy = isset($filters['sort_by']) ? $filters['sort_by'] : 'created_at';
            $sortOrder = isset($filters['sort_order']) && $filters['sort_order'] === 'asc' ? 'ASC' : 'DESC';
            
            $validSortFields = ['created_at', 'amount', 'status'];
            if (in_array($sortBy, $validSortFields)) {
                $query .= " ORDER BY t." . $sortBy . " " . $sortOrder;
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
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $transactions,
                'count' => count($transactions)
            ];

        } catch (Exception $e) {
            logError('Transaction getAllTransactions error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch transactions'
            ];
        }
    }

    /**
     * Get transaction statistics
     */
    public function getTransactionStats($filters = []) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_transactions,
                        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_completed_amount,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_transactions,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_transactions,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_transactions,
                        AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as avg_transaction_amount
                     FROM " . $this->table_name . " t
                     LEFT JOIN orders o ON t.order_id = o.id
                     WHERE 1=1";

            $params = [];

            // Apply filters
            if (isset($filters['date_from'])) {
                $query .= " AND DATE(t.created_at) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            if (isset($filters['date_to'])) {
                $query .= " AND DATE(t.created_at) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            if (isset($filters['restaurant_id'])) {
                $query .= " AND o.restaurant_id = :restaurant_id";
                $params[':restaurant_id'] = $filters['restaurant_id'];
            }

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $stats
            ];

        } catch (Exception $e) {
            logError('Transaction getTransactionStats error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch transaction statistics'
            ];
        }
    }

    /**
     * Validate transaction data
     */
    private function validateTransactionData($data, $isCreate = true) {
        if ($isCreate) {
            if (empty($data['order_id']) || empty($data['amount']) || empty($data['payment_method'])) {
                return false;
            }
        }

        if (isset($data['amount']) && (!is_numeric($data['amount']) || $data['amount'] <= 0)) {
            return false;
        }

        if (isset($data['payment_method']) && !in_array($data['payment_method'], ['mtn_rwanda', 'airtel_rwanda', 'cash'])) {
            return false;
        }

        if (isset($data['status']) && !in_array($data['status'], ['pending', 'completed', 'failed', 'cancelled', 'refunded'])) {
            return false;
        }

        return true;
    }
}
?>
