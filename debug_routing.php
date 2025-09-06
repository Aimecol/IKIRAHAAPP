<?php
// Debug routing issue

require_once 'ikiraha-api/config/config.php';

// Simple router class (copy from index.php)
class DebugRouter {
    private $routes = [];

    public function addRoute($method, $pattern, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];
        echo "Added route: $method $pattern -> $controller::$action\n";
    }

    public function testRoute($method, $uri) {
        echo "\nTesting: $method $uri\n";
        echo "Routes to check:\n";
        
        foreach ($this->routes as $index => $route) {
            echo "  [$index] {$route['method']} {$route['pattern']}\n";
            
            if ($route['method'] !== $method) {
                echo "    ❌ Method mismatch\n";
                continue;
            }

            // Convert route pattern to regex
            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['pattern']);
            $pattern = '#^' . $pattern . '$#';
            
            echo "    Regex: $pattern\n";

            if (preg_match($pattern, $uri, $matches)) {
                echo "    ✅ MATCH! -> {$route['controller']}::{$route['action']}\n";
                return true;
            } else {
                echo "    ❌ No match\n";
            }
        }
        
        echo "❌ No route found\n";
        return false;
    }
}

// Initialize router
$router = new DebugRouter();

// Add the profile routes
echo "Adding profile routes...\n";
$router->addRoute('GET', '/auth/profile', 'ProfileController', 'getProfile');
$router->addRoute('PUT', '/auth/profile', 'ProfileController', 'updateProfile');

// Test the route
$router->testRoute('GET', '/auth/profile');
