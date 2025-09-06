<?php
// Test if ProfileController can be loaded and instantiated

require_once 'ikiraha-api/config/config.php';

echo "Testing ProfileController loading...\n";

try {
    // Check if the file exists
    $controllerFile = __DIR__ . '/ikiraha-api/controllers/ProfileController.php';
    echo "Controller file path: $controllerFile\n";
    echo "File exists: " . (file_exists($controllerFile) ? 'YES' : 'NO') . "\n";
    
    if (file_exists($controllerFile)) {
        echo "File size: " . filesize($controllerFile) . " bytes\n";
        
        // Try to include it manually
        require_once $controllerFile;
        echo "✅ Manual include successful\n";
        
        // Check if class exists
        if (class_exists('ProfileController')) {
            echo "✅ ProfileController class exists\n";
            
            // Try to instantiate
            $controller = new ProfileController();
            echo "✅ ProfileController instantiated successfully\n";
            
            // Check if methods exist
            $methods = ['getProfile', 'updateProfile', 'uploadProfilePicture', 'deleteProfilePicture'];
            foreach ($methods as $method) {
                if (method_exists($controller, $method)) {
                    echo "✅ Method $method exists\n";
                } else {
                    echo "❌ Method $method missing\n";
                }
            }
            
        } else {
            echo "❌ ProfileController class not found after include\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Test autoloader
echo "\nTesting autoloader...\n";
try {
    $controller2 = new ProfileController();
    echo "✅ Autoloader works - ProfileController instantiated\n";
} catch (Exception $e) {
    echo "❌ Autoloader failed: " . $e->getMessage() . "\n";
}
