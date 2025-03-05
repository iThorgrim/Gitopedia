<?php
/**
 * Home Controller
 */

class HomeController {
    /**
     * Index action for the home page
     * 
     * @param array $params Any parameters from the URL
     * @return array|string Response data
     */
    public static function index($params = []) {
        return [
            'title' => 'Accueil',
            'content' => 'Bienvenue sur notre site'
        ];
    }
}