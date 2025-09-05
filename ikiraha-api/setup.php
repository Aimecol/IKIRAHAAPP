<?php
/**
 * IKIRAHA API Database Setup Script
 * Run this script once to initialize the database
 */

// Include configuration
require_once 'config/config.php';

echo "IKIRAHA API Database Setup\n";
echo "==========================\n\n";

try {
    // Connect to MySQL server (without database)
    $dsn = "mysql:host=localhost;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "✓ Connected to MySQL server\n";

    // Read and execute schema
    $schemaFile = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    $schema = file_get_contents($schemaFile);
    if (!$schema) {
        throw new Exception("Could not read schema file");
    }

    echo "✓ Schema file loaded\n";

    // Split SQL statements and execute them
    $statements = array_filter(array_map('trim', explode(';', $schema)));

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignore "database exists" and "table exists" errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        }
    }

    echo "✓ Database schema created successfully\n";

    // Test database connection with the new database
    $database = new Database();
    $conn = $database->getConnection();

    // Verify all tables exist
    $tables = [
        'users', 'user_addresses', 'restaurants', 'categories', 'products',
        'orders', 'order_items', 'transactions', 'user_favorites',
        'auth_tokens', 'notifications'
    ];

    $missingTables = [];
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            $missingTables[] = $table;
        }
    }

    if (!empty($missingTables)) {
        throw new Exception("Missing tables: " . implode(', ', $missingTables));
    }

    echo "✓ All " . count($tables) . " tables verified\n";

    // Check if sample data exists
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];

    if ($userCount > 0) {
        echo "✓ Sample data already exists ($userCount users)\n";
    } else {
        echo "⚠ No sample data found. Sample data should be inserted via schema.sql\n";
    }

    // Verify API configuration
    echo "✓ Verifying API configuration...\n";

    // Check required directories
    $directories = [
        __DIR__ . '/logs',
        __DIR__ . '/uploads',
        __DIR__ . '/config',
        __DIR__ . '/models',
        __DIR__ . '/controllers'
    ];

    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "  ✓ Created directory: " . basename($dir) . "\n";
        } else {
            echo "  ✓ Directory exists: " . basename($dir) . "\n";
        }
    }

    // Check file permissions
    if (!is_writable(__DIR__ . '/logs')) {
        echo "  ⚠ Warning: logs directory is not writable\n";
    }

    if (!is_writable(__DIR__ . '/uploads')) {
        echo "  ⚠ Warning: uploads directory is not writable\n";
    }

    // Test API endpoints
    echo "✓ Testing API endpoints...\n";

    $baseUrl = 'http://localhost/ikirahaapp/ikiraha-api/public';
    $testEndpoints = [
        '/' => 'API Root',
        '/health' => 'Health Check'
    ];

    foreach ($testEndpoints as $endpoint => $name) {
        $url = $baseUrl . $endpoint;
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response !== false) {
            echo "  ✓ $name endpoint accessible\n";
        } else {
            echo "  ⚠ Warning: $name endpoint not accessible at $url\n";
        }
    }

    echo "\n🎉 Database and API setup completed successfully!\n\n";

    echo "📍 API Access Points:\n";
    echo "- Health check: http://localhost/ikirahaapp/ikiraha-api/public/health\n";
    echo "- API root: http://localhost/ikirahaapp/ikiraha-api/public/\n";
    echo "- API documentation: See API_DOCUMENTATION.md\n\n";

    echo "👥 Default test accounts (password: 'password'):\n";
    echo "- Super Admin: admin@ikiraha.com\n";
    echo "- Merchant: merchant@ikiraha.com\n";
    echo "- Accountant: accountant@ikiraha.com\n";
    echo "- Client: client@ikiraha.com\n\n";

    echo "📊 Database Statistics:\n";
    $stats = [
        'users' => 'SELECT COUNT(*) as count FROM users',
        'restaurants' => 'SELECT COUNT(*) as count FROM restaurants',
        'categories' => 'SELECT COUNT(*) as count FROM categories',
        'products' => 'SELECT COUNT(*) as count FROM products'
    ];

    foreach ($stats as $table => $query) {
        try {
            $stmt = $conn->query($query);
            $count = $stmt->fetch()['count'];
            echo "- " . ucfirst($table) . ": $count records\n";
        } catch (Exception $e) {
            echo "- " . ucfirst($table) . ": Error getting count\n";
        }
    }

    echo "\n🚀 Your IKIRAHA API is ready to use!\n";

} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "Please check your MySQL configuration and try again.\n";
    exit(1);
}
?>