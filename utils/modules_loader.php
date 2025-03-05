<?php
/**
 * Copyright (C) 2025 - Gitopedia
 *
 * ModulesLoader
 * Provides functionality to load and configure modules from a given path
 */

class ModulesLoader {
    /**
     * Load modules from a given path into the application
     * 
     * @param object $application Application object to load modules into
     * @param string $path Path to look for modules
     * @return void
     */
    public static function Load($application, $path) {
        if ($handle = opendir($path)) {
            while (false !== ($module_name = readdir($handle))) {
                if ($module_name != "." && $module_name != "..") {
                    $full_path = "{$path}/{$module_name}";
                    
                    if (is_dir($full_path)) {
                        $router_path = "{$full_path}/router.php";
                        
                        if (file_exists($router_path)) {
                            try {
                                $module = require_once "modules/{$module_name}/router.php";
                                
                                if (isset($module) && $module) {
                                    if (!isset($application->static[$module_name]) || !is_array($application->static[$module_name])) {
                                        $application->static[$module_name] = [];
                                    }
                                    
                                    $application->static[$module_name] = "modules/{$module_name}/static";
                                    
                                    // Assuming the module is a callable function or object
                                    if (is_callable($module)) {
                                        $module($application);
                                    }
                                } else {
                                    error_log("Failed to load module: {$module_name}");
                                }
                            } catch (Exception $e) {
                                error_log("Failed to load module: {$module_name}\n" . $e->getMessage());
                            }
                        } else {
                            error_log("Router not found for module: {$module_name}");
                        }
                    }
                }
            }
            closedir($handle);
        }
    }
}