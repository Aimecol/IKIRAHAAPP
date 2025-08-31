<?php
/**
 * Health Controller for IKIRAHA API
 * Provides API status and health check endpoints
 */

class HealthController {
    private $db;

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch (Exception $e) {
            $this->db = null;
        }
    }

    /**
     * API root endpoint
     */
    public function index() {
        $this->sendSuccess([
            'message' => 'IKIRAHA Food Delivery API',
            'version' => APP_VERSION,
            'status' => 'running',
            'endpoints' => [
                'health' => '/health',
                'auth' => '/auth/*',
                'products' => '/products/*',
                'orders' => '/orders/*',
                'categories' => '/categories'
            ]
        ]);
    }

    /**
     * Health check endpoint
     */
    public function health() {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => APP_VERSION,
            'environment' => APP_ENV,
            'checks' => []
        ];

        // Database connectivity check
        try {
            if ($this->db) {
                $stmt = $this->db->query('SELECT 1');
                $health['checks']['database'] = [
                    'status' => 'healthy',
                    'message' => 'Database connection successful'
                ];
            } else {
                throw new Exception('Database connection failed');
            }
        } catch (Exception $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }

        // File system checks
        $health['checks']['filesystem'] = [
            'logs_writable' => is_writable(__DIR__ . '/../logs'),
            'uploads_writable' => is_writable(__DIR__ . '/../uploads')
        ];

        // Memory usage
        $health['checks']['memory'] = [
            'usage' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit')
        ];

        $statusCode = $health['status'] === 'healthy' ? 200 : 503;
        $this->sendSuccess($health, $statusCode);
    }

    /**
     * Send success response
     */
    private function sendSuccess($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
}
?>