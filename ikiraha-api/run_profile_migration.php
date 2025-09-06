<?php
/**
 * Run profile fields migration
 */

require_once 'config/database.php';

echo "🔧 Running Profile Fields Migration\n";
echo "===================================\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();

    // Check if columns already exist
    $checkQuery = "SHOW COLUMNS FROM users LIKE 'address'";
    $stmt = $db->prepare($checkQuery);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Profile fields already exist in users table\n";
        exit(0);
    }

    echo "📝 Adding profile fields to users table...\n";

    // Add new columns
    $alterQueries = [
        "ALTER TABLE users ADD COLUMN address TEXT NULL AFTER profile_image",
        "ALTER TABLE users ADD COLUMN date_of_birth DATE NULL AFTER address", 
        "ALTER TABLE users ADD COLUMN gender ENUM('male', 'female', 'other') NULL AFTER date_of_birth",
        "ALTER TABLE users ADD COLUMN bio TEXT NULL AFTER gender"
    ];

    foreach ($alterQueries as $query) {
        echo "Executing: $query\n";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    echo "\n✅ Profile fields migration completed successfully!\n";

    // Verify the changes
    echo "\n🔍 Verifying new table structure...\n";
    $describeQuery = "DESCRIBE users";
    $stmt = $db->prepare($describeQuery);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }

    echo "\n🎉 Migration completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
