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

    // Verify tables exist
    $tables = ['users', 'restaurants', 'categories', 'products', 'orders', 'order_items', 'transactions'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            throw new Exception("Table '$table' was not created");
        }
    }

    echo "✓ All tables verified\n";

    // Check if sample data exists
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];

    if ($userCount > 0) {
        echo "✓ Sample data already exists ($userCount users)\n";
    } else {
        echo "⚠ No sample data found. Sample data should be inserted via schema.sql\n";
    }

    echo "\n🎉 Database setup completed successfully!\n\n";
    echo "You can now access the API at:\n";
    echo "- Health check: http://localhost/ikirahaapp/ikiraha-api/public/health\n";
    echo "- API root: http://localhost/ikirahaapp/ikiraha-api/public/\n\n";

    echo "Default test accounts:\n";
    echo "- Super Admin: admin@ikiraha.com / password\n";
    echo "- Merchant: merchant@ikiraha.com / password\n";
    echo "- Accountant: accountant@ikiraha.com / password\n";
    echo "- Client: client@ikiraha.com / password\n\n";

} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "Please check your MySQL configuration and try again.\n";
    exit(1);
}
?>