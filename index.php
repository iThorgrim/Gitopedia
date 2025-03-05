<?php
/**
 * Main application entry point
 */

// Load necessary classes
require_once __DIR__ . '/utils/application.php';
require_once __DIR__ . '/utils/modules_loader.php';

// Create application instance
$app = new Application();

// Load all modules
$app->loadModules(__DIR__ . '/modules');

// Run the application (process the request)
$app->run();