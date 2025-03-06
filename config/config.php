<?php
/**
 * Configuration générale de l'application - Paramètres centralisés du système
 * 
 * Ce fichier fondamental centralise toutes les configurations générales
 * de l'application, définissant son comportement global. Il établit :
 * 
 * - Les constantes système utilisées partout dans l'application
 * - Les paramètres de débogage et d'environnement
 * - Les configurations de sécurité et de sessions
 * - Les préférences par défaut (langue, fuseau horaire, etc.)
 * 
 * Il est typiquement chargé au tout début du bootstrap de l'application,
 * avant l'initialisation de l'Application et du chargement des modules.
 * 
 * Dans l'architecture HMVC, cette configuration centrale garantit une
 * cohérence entre tous les modules qui peuvent accéder aux mêmes paramètres.
 */

// -----------------------------------------------------------------------------
// Mode de débogage
// -----------------------------------------------------------------------------
/**
 * Active ou désactive le mode de débogage
 * 
 * En mode débogage (true) :
 * - Les erreurs PHP sont affichées avec détails
 * - Les exceptions incluent la stack trace complète
 * - Les requêtes SQL échouées montrent les messages d'erreur complets
 * 
 * En production (false) :
 * - Les erreurs sont journalisées mais pas affichées
 * - Les exceptions montrent des messages génériques
 * - Les erreurs SQL sont cachées aux utilisateurs
 * 
 * IMPORTANT : Toujours définir à false en production pour des raisons de sécurité
 */
define('DEBUG_MODE', true);

// -----------------------------------------------------------------------------
// URL de base de l'application
// -----------------------------------------------------------------------------
/**
 * Définit l'URL racine de l'application
 * 
 * Cette constante est utilisée pour :
 * - Générer des URLs absolues (liens, redirections)
 * - Gérer les assets (CSS, JS, images)
 * - Définir les routes relatives correctement
 * 
 * IMPORTANT : Mettre à jour cette valeur lors du déploiement sur différents environnements
 */
define('BASE_URL', 'http://172.30.170.6');

// -----------------------------------------------------------------------------
// Configuration du fuseau horaire
// -----------------------------------------------------------------------------
/**
 * Définit le fuseau horaire par défaut pour toutes les fonctions de date/heure
 * 
 * Cette configuration garantit que toutes les opérations liées au temps
 * (date(), time(), DateTime) utilisent le même fuseau horaire cohérent
 * à travers toute l'application.
 */
date_default_timezone_set('Europe/Paris');

// -----------------------------------------------------------------------------
// Sécurisation des sessions PHP
// -----------------------------------------------------------------------------
/**
 * Configuration de sécurité des cookies de session
 * 
 * httponly = 1 : Empêche l'accès au cookie de session via JavaScript (protection XSS)
 * use_only_cookies = 1 : Force l'utilisation de cookies pour les sessions (pas d'ID en URL)
 * 
 * Ces paramètres sont essentiels pour protéger les sessions contre les attaques
 * courantes comme le vol de session et le cross-site scripting.
 */
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

/**
 * Sécurisation supplémentaire pour les connexions HTTPS
 * 
 * Si la connexion est en HTTPS, cette directive limite les cookies de session
 * aux connexions sécurisées uniquement, empêchant leur transmission en clair.
 * 
 * cookie_secure = 1 : Cookie envoyé uniquement sur les connexions HTTPS
 */
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// -----------------------------------------------------------------------------
// Configuration avancée de l'application
// -----------------------------------------------------------------------------
/**
 * Tableau de configuration retourné pour être utilisé par l'application
 * 
 * Contrairement aux constantes définies plus haut qui sont globales,
 * ces paramètres sont accessibles uniquement lorsque ce fichier est inclus
 * avec 'return' (ex: $config = require('config.php');)
 * 
 * Cette approche à deux niveaux permet :
 * - Un accès rapide aux constantes critiques via define()
 * - Une configuration structurée et extensible via le tableau
 */
return [
    // Nom de l'application affiché dans l'interface et les métadonnées
    'app_name' => 'Gitopedia',
    
    // Version actuelle de l'application (pour le versioning des assets, API, etc.)
    'app_version' => '1.0.0',
    
    // Langue par défaut pour l'internationalisation
    'default_language' => 'fr',
    
    // Layout principal utilisé par défaut pour toutes les vues
    'default_layout' => 'main',
    
    // Fuseau horaire par défaut (redondant avec date_default_timezone_set, mais utile pour référence)
    'default_timezone' => 'Europe/Paris',
    
    // Mode maintenance - si true, affiche une page de maintenance au lieu du site
    'maintenance_mode' => false,
];