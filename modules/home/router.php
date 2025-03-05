<?php
/**
 * Home module router
 */

// Import controller
require_once "modules/home/controller.php";

// Return a function that sets up routes for the home module
return function($application) {
    // Register route for the home page using the addRoute method
    $application->addRoute('/home', [
        'GET' => 'HomeController::index'
    ]);
    
    // You could register more routes for this module here
    
    // Return the application object to maintain chainability
    return $application;
};