<?php
/**
 * Configuration de la base de données - Paramètres de connexion
 * 
 * Ce fichier centralise tous les paramètres de connexion à la base de données 
 * et les configurations associées. Il sert de point unique de configuration
 * pour toutes les interactions avec les bases de données dans l'application.
 * 
 * Il définit :
 * - Les constantes de connexion principales pour un accès rapide
 * - Une configuration structurée pour des options avancées
 * - Les paramètres de jeu de caractères et de collation
 * 
 * Dans l'architecture HMVC, ce fichier est généralement chargé au démarrage 
 * de l'application, permettant à tous les modules d'accéder à la base de 
 * données via une connexion unique et cohérente.
 * 
 * SÉCURITÉ : Ce fichier contient des informations sensibles (identifiants)
 * et doit être protégé contre les accès non autorisés, idéalement en le
 * plaçant en dehors de la racine web publique.
 */

// -----------------------------------------------------------------------------
// Paramètres de connexion principaux
// -----------------------------------------------------------------------------
/**
 * Hôte de la base de données
 * 
 * Spécifie le serveur où se trouve la base de données :
 * - 'localhost' ou '127.0.0.1' pour une base locale
 * - Une adresse IP ou nom d'hôte pour une base distante
 * - Un socket Unix (ex: '/tmp/mysql.sock') pour les configurations spéciales
 */
define('DB_HOST', 'localhost');

/**
 * Nom de la base de données
 * 
 * Identifie la base de données spécifique à utiliser sur le serveur.
 * Cette base doit exister et l'utilisateur DB_USER doit y avoir accès.
 */
define('DB_NAME', 'gitopedia');

/**
 * Nom d'utilisateur pour la connexion
 * 
 * Identifiant utilisé pour l'authentification à la base de données.
 * Cet utilisateur doit disposer des privilèges appropriés pour les
 * opérations que l'application doit effectuer (SELECT, INSERT, etc.).
 */
define('DB_USER', 'test');

/**
 * Mot de passe pour la connexion
 * 
 * Utilisé avec DB_USER pour l'authentification au serveur de base de données.
 * IMPORTANT : En production, utiliser un mot de passe fort et unique.
 */
define('DB_PASS', 'test');

// -----------------------------------------------------------------------------
// Configuration avancée de la base de données
// -----------------------------------------------------------------------------
/**
 * Tableau de configuration détaillée retourné pour être utilisé par l'application
 * 
 * Cette structure étend les constantes de base avec des options supplémentaires,
 * permettant une configuration plus fine et le support potentiel de plusieurs
 * connexions de base de données.
 * 
 * Le format permet également d'ajouter facilement d'autres types de bases de données
 * (PostgreSQL, SQLite, etc.) si l'application évolue pour les supporter.
 */
return [
    // Configuration pour MySQL/MariaDB
    'mysql' => [
        // Paramètres de connexion (répétés depuis les constantes pour commodité)
        'host' => DB_HOST,
        'database' => DB_NAME,
        'username' => DB_USER,
        'password' => DB_PASS,
        
        // Jeu de caractères - UTF8MB4 supporte les caractères Unicode complets (y compris emojis)
        'charset' => 'utf8mb4',
        
        // Règles de comparaison/tri pour le jeu de caractères
        // 'unicode_ci' assure un tri insensible à la casse qui respecte les règles Unicode
        'collation' => 'utf8mb4_unicode_ci',
        
        // Options supplémentaires possibles (commentées car non utilisées)
        // 'port' => 3306,             // Port du serveur MySQL (3306 par défaut)
        // 'prefix' => 'prefix_',      // Préfixe pour les noms de tables
        // 'options' => [],            // Options PDO supplémentaires
        // 'strict' => true,           // Mode strict pour validation plus rigoureuse
        // 'timezone' => '+00:00',     // Fuseau horaire de la base de données
    ],
    
    // Exemples d'extensions possibles pour d'autres types de bases de données
    // 'sqlite' => [
    //     'database' => '/path/to/database.sqlite',
    //     'prefix' => '',
    //     'foreign_key_constraints' => true,
    // ],
    // 'pgsql' => [
    //     'host' => 'localhost',
    //     'database' => 'gitopedia',
    //     'username' => 'postgres',
    //     'password' => 'postgres',
    //     'charset' => 'utf8',
    //     'schema' => 'public',
    // ],
];