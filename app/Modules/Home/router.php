<?php
/**
 * Module Home - Définition des routes
 * 
 * Ce fichier définit toutes les routes associées au module Home dans l'architecture HMVC.
 * Il est automatiquement chargé par la classe Application lors de l'initialisation
 * du module, et reçoit les variables suivantes injectées par l'Application :
 * 
 * - $router : L'instance du routeur pour définir les routes
 * - $module : Le nom du module actuel ('Home' dans ce cas)
 * 
 * Chaque route associe un pattern d'URL à une méthode spécifique du contrôleur,
 * permettant ainsi d'organiser clairement la structure de navigation du site.
 */

// -----------------------------------------------------------------------------
// ROUTES PRINCIPALES DU SITE
// -----------------------------------------------------------------------------

/**
 * Route pour la page d'accueil
 * 
 * Cette route répond à l'URL racine du site ("/") et invoque la méthode "index"
 * du contrôleur "HomeController" du module "Home".
 * 
 * L'URL correspond exactement à "/" (sans paramètres dynamiques)
 * La méthode HTTP est GET (affichage simple sans envoi de données)
 * 
 * C'est généralement la page la plus visitée du site et sert de point d'entrée
 * principal pour les utilisateurs.
 */
$router->get('/', 'HomeController@index', $module);

/**
 * Route pour la page "À propos"
 * 
 * Cette route répond à l'URL "/about" et invoque la méthode "about"
 * du contrôleur "HomeController" du module "Home".
 * 
 * L'URL correspond exactement à "/about" (sans paramètres dynamiques)
 * La méthode HTTP est GET (affichage simple sans envoi de données)
 * 
 * Cette page statique présente typiquement des informations sur 
 * le site, l'entreprise, l'équipe, etc.
 */
$router->get('/about', 'HomeController@about', $module);

// -----------------------------------------------------------------------------
// ROUTES ADDITIONNELLES (EXEMPLES COMMENTÉS)
// -----------------------------------------------------------------------------

/**
 * Exemple de routes supplémentaires qui pourraient être ajoutées à ce module :
 * 
 * // Page de contact
 * $router->get('/contact', 'HomeController@contact', $module);
 * 
 * // Traitement du formulaire de contact
 * $router->post('/contact', 'HomeController@submitContact', $module);
 * 
 * // Page de conditions d'utilisation
 * $router->get('/terms', 'HomeController@terms', $module);
 * 
 * // Page de politique de confidentialité
 * $router->get('/privacy', 'HomeController@privacy', $module);
 */