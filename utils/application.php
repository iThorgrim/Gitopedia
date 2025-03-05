<?php
/**
 * Simple Application class that handles routing and module loading
 */
class Application {
    /**
     * Stores all registered routes
     */
    public $routes = [];
    
    /**
     * Stores static file directories
     */
    public $static = [];
    
    /**
     * Register a new route with HTTP method handlers
     *
     * @param string $path URL path
     * @param array $handlers Array of HTTP methods and their handlers
     */
    public function addRoute($path, $handlers) {
        $this->routes[$path] = $handlers;
    }
    
    /**
     * Load all modules from a directory
     *
     * @param string $path Path to modules directory
     */
    public function loadModules($path) {
        // Using the ModulesLoader class we created earlier
        ModulesLoader::Load($this, $path);
    }
    
    /**
     * Process the current request and call the appropriate handler
     */
    public function run() {
        // Get the current URI path
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Get the HTTP method
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Check if route exists
        if (isset($this->routes[$uri])) {
            // Check if method is supported
            if (isset($this->routes[$uri][$method])) {
                $handler = $this->routes[$uri][$method];
                
                // If handler is a string like 'Class::method'
                if (is_string($handler) && strpos($handler, '::') !== false) {
                    list($class, $method) = explode('::', $handler);
                    $response = $class::$method();
                }

                // If handler is a closure/function
                elseif (is_callable($handler)) {
                    $response = $handler();
                }
                else {
                    http_response_code(500);
                    echo "Invalid route handler";
                    return;
                }
                
                // Handle the response
                if (is_array($response)) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                } else {
                    echo $response;
                }
                
                return;
            }
            
            // Method not allowed
            http_response_code(405);
            echo "Method not allowed";
            return;
        }
        
        // Route not found
        http_response_code(404);
        echo "Not found";
    }
}