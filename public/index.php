<?php
/**
 * Gitopedia - Point d'entrée principal de l'application
 * 
 * Ce fichier est le "Front Controller" de l'application, c'est-à-dire le point
 * d'entrée unique pour toutes les requêtes HTTP. Toutes les URLs du site passent
 * par ce script grâce aux règles de réécriture définies dans .htaccess.
 * 
 * Ce fichier remplit plusieurs fonctions fondamentales :
 * - Définition des constantes essentielles (ROOT_PATH)
 * - Chargement des fichiers de configuration
 * - Inclusion des classes de base du framework
 * - Initialisation et démarrage de l'application
 * 
 * Dans l'architecture HMVC, ce fichier représente le niveau supérieur de la
 * hiérarchie et orchestre le lancement de l'ensemble du système.
 */

// -----------------------------------------------------------------------------
// DÉFINITION DU CHEMIN RACINE
// -----------------------------------------------------------------------------
/**
 * Définir la constante ROOT_PATH qui représente le chemin absolu vers
 * le dossier racine de l'application.
 * 
 * Cette constante est essentielle car elle sert de point de référence pour
 * tous les chemins absolus utilisés dans l'application, permettant une
 * portabilité complète indépendamment de l'emplacement d'installation.
 * 
 * dirname(__DIR__) remonte d'un niveau par rapport au dossier 'public'
 * pour accéder à la racine du projet.
 */
define('ROOT_PATH', dirname(__DIR__));

// -----------------------------------------------------------------------------
// CHARGEMENT DES CONFIGURATIONS
// -----------------------------------------------------------------------------
/**
 * Inclure les fichiers de configuration de l'application
 * 
 * Ces fichiers définissent les constantes et paramètres globaux
 * nécessaires au fonctionnement de l'application :
 * - config.php : Configuration générale (débogage, URL, timezone...)
 * - database.php : Paramètres de connexion à la base de données
 */
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

// -----------------------------------------------------------------------------
// CHARGEMENT DES CLASSES DU FRAMEWORK
// -----------------------------------------------------------------------------
/**
 * Inclure les classes fondamentales du framework HMVC
 * 
 * Chaque classe représente un composant essentiel de l'architecture :
 * - Application : Point central qui orchestre tous les composants
 * - Router : Gestion du routage des requêtes vers les contrôleurs
 * - Request : Encapsulation des données de la requête HTTP
 * - Response : Construction et envoi de la réponse HTTP
 * - Layout : Gestion des templates et rendus de page
 * - Controller : Classe de base pour tous les contrôleurs
 * - Model : Classe de base pour tous les modèles
 * - Database : Abstraction des interactions avec la base de données
 * 
 * Note : Dans un système plus avancé, ces inclusions seraient remplacées
 * par un autoloader PSR-4, mais l'approche explicite est utilisée ici
 * pour des raisons pédagogiques.
 */
require_once ROOT_PATH . '/app/Core/Application.php';
require_once ROOT_PATH . '/app/Core/Router.php';
require_once ROOT_PATH . '/app/Core/Request.php';
require_once ROOT_PATH . '/app/Core/Response.php';
require_once ROOT_PATH . '/app/Core/Layout.php';
require_once ROOT_PATH . '/app/Core/Controller.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Core/Database.php';

// -----------------------------------------------------------------------------
// CHARGEMENT DES INTERFACES ET MIDDLEWARES
// -----------------------------------------------------------------------------
/**
 * Inclure les interfaces et classes de middleware
 * 
 * Les middlewares permettent d'intercepter et de traiter les requêtes
 * avant qu'elles n'atteignent les contrôleurs (authentification, validation, etc.)
 */
require_once ROOT_PATH . '/app/Middleware/MiddlewareInterface.php';

// -----------------------------------------------------------------------------
// INITIALISATION DE L'APPLICATION
// -----------------------------------------------------------------------------
/**
 * Créer l'instance principale de l'application
 * 
 * Cette instance unique (Singleton) coordonne tous les composants
 * et gère le cycle de vie complet de la requête HTTP.
 */
$app = new App\Core\Application();

// -----------------------------------------------------------------------------
// CONFIGURATION DE BASE
// -----------------------------------------------------------------------------
/**
 * Configurer les chemins essentiels de l'application
 * 
 * - setModulesPath : Définit l'emplacement des modules HMVC
 *   Les modules sont les composants fonctionnels de l'application (Blog, User, etc.)
 * 
 * - setSharedViewsPath : Définit l'emplacement des templates partagés
 *   Ces templates sont utilisés par tous les modules (layouts, partials, etc.)
 */
$app->setModulesPath(ROOT_PATH . '/app/Modules');
$app->setSharedViewsPath(ROOT_PATH . '/assets/templates');

// -----------------------------------------------------------------------------
// DÉMARRAGE DE L'APPLICATION
// -----------------------------------------------------------------------------
/**
 * Lancer l'exécution de l'application
 * 
 * Cette méthode déclenche le processus complet de traitement de la requête :
 * 1. Analyse de la requête entrante
 * 2. Correspondance avec une route définie
 * 3. Exécution des middlewares applicables
 * 4. Instanciation du contrôleur approprié
 * 5. Exécution de l'action du contrôleur
 * 6. Génération et envoi de la réponse
 * 
 * C'est le point final de ce fichier d'entrée, après quoi
 * le contrôle passe entièrement au framework HMVC.
 */
$app->run();