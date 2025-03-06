<?php
/**
 * Classe Database - Gestionnaire de connexion à la base de données
 * 
 * Cette classe est responsable de :
 * - Établir et maintenir une connexion sécurisée à la base de données
 * - Fournir des méthodes simplifiées pour exécuter des requêtes SQL
 * - Sécuriser les requêtes contre les injections SQL via des requêtes préparées
 * - Gérer les erreurs de base de données de manière élégante
 * - Supporter les transactions pour garantir l'intégrité des données
 * 
 * Elle encapsule PDO (PHP Data Objects) qui est une couche d'abstraction 
 * permettant d'accéder à différents systèmes de bases de données 
 * avec une interface commune.
 */

namespace App\Core;

class Database
{
    /**
     * Instance PDO pour la connexion à la base de données
     * 
     * PDO (PHP Data Objects) est une extension PHP qui définit
     * une interface pour accéder à différents types de bases de données 
     * (MySQL, PostgreSQL, SQLite, Oracle, etc.) de manière uniforme.
     * 
     * Cette propriété stocke l'instance de connexion qui sera utilisée
     * par toutes les méthodes de la classe.
     * 
     * @var \PDO
     */
    private \PDO $pdo;
    
    /**
     * Constructeur - Établit la connexion à la base de données
     * 
     * Cette méthode :
     * 1. Construit la chaîne de connexion (DSN) à partir des paramètres fournis
     * 2. Configure les options de PDO pour une utilisation sécurisée et optimale
     * 3. Établit la connexion à la base de données
     * 4. Gère les erreurs de connexion de manière appropriée
     * 
     * Exemple d'utilisation :
     * $db = new Database('localhost', 'ma_base', 'utilisateur', 'mot_de_passe');
     * 
     * @param string $host     Nom d'hôte du serveur de base de données (ex: localhost)
     * @param string $database Nom de la base de données à laquelle se connecter
     * @param string $username Nom d'utilisateur pour l'authentification
     * @param string $password Mot de passe pour l'authentification
     * @throws \PDOException Si la connexion à la base de données échoue
     */
    public function __construct(string $host, string $database, string $username, string $password)
    {
        try {
            // Construire la chaîne de connexion (DSN - Data Source Name)
            // Le charset utf8mb4 permet de supporter tous les caractères Unicode, y compris les emojis
            $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
            
            // Options pour configurer le comportement de PDO
            $options = [
                // Mode de rapport d'erreur : lever des exceptions en cas d'erreur SQL
                // C'est préférable à d'autres modes qui pourraient silencieusement échouer
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                
                // Mode de récupération par défaut : tableaux associatifs
                // Les résultats seront retournés sous forme de tableau avec des clés nommées
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                
                // Désactiver l'émulation des requêtes préparées pour plus de sécurité
                // Cela force l'utilisation des véritables requêtes préparées côté serveur
                \PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            // Créer l'instance PDO avec les paramètres et options configurés
            $this->pdo = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            // Gérer l'erreur de connexion différemment selon le mode (développement ou production)
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                // En mode développement, afficher les détails de l'erreur pour faciliter le débogage
                die('Erreur de connexion à la base de données: ' . $e->getMessage());
            } else {
                // En production, afficher un message générique pour éviter de divulguer des informations sensibles
                die('Erreur de connexion à la base de données. Veuillez réessayer plus tard.');
            }
        }
    }
    
    /**
     * Exécute une requête SQL de type SELECT et récupère les résultats
     * 
     * Cette méthode est conçue pour les requêtes qui retournent des données.
     * Elle suit les bonnes pratiques de sécurité en :
     * 1. Préparant la requête SQL pour éviter les injections
     * 2. Liant les paramètres de manière sécurisée
     * 3. Exécutant la requête préparée
     * 4. Récupérant tous les résultats sous forme de tableau
     * 
     * Exemples d'utilisation :
     * 
     * $utilisateurs = $db->query("SELECT * FROM utilisateurs WHERE actif = :actif", [':actif' => 1]);
     * 
     * $produit = $db->query("SELECT * FROM produits WHERE id = :id LIMIT 1", [':id' => $produitId]);
     * if ($produit) {
     *     $produit = $produit[0]; // Premier élément du tableau
     * }
     * 
     * @param string $query  La requête SQL de type SELECT à exécuter
     * @param array $params  Tableau associatif des paramètres à lier à la requête préparée
     * @return array|null    Tableau contenant les résultats ou null si aucun résultat ou erreur
     */
    public function query(string $query, array $params = []): ?array
    {
        try {
            // Préparer la requête SQL
            // Cette étape crée une requête préparée qui sera compilée par le serveur de base de données
            $stmt = $this->pdo->prepare($query);
            
            // Exécuter la requête avec les paramètres fournis
            // PDO lie automatiquement les paramètres en s'assurant qu'ils sont correctement échappés
            $stmt->execute($params);
            
            // Récupérer tous les résultats sous forme de tableau associatif
            // La méthode fetchAll() récupère toutes les lignes résultantes
            $result = $stmt->fetchAll();
            
            // Renvoyer les résultats ou null si aucun résultat n'est trouvé
            // L'opérateur ?: (coalesce) retourne $result s'il est évalué à true, sinon null
            return $result ?: null;
        } catch (\PDOException $e) {
            // En cas d'erreur, journaliser les détails et retourner null
            $this->logError($e, $query, $params);
            return null;
        }
    }
    
    /**
     * Exécute une requête SQL de modification (INSERT, UPDATE, DELETE)
     * 
     * Cette méthode est conçue pour les requêtes qui modifient des données
     * mais ne retournent pas de résultats. Elle utilise également les
     * requêtes préparées pour la sécurité.
     * 
     * Exemples d'utilisation :
     * 
     * // Insertion d'un nouvel enregistrement
     * $success = $db->execute(
     *     "INSERT INTO utilisateurs (nom, email, date_creation) VALUES (:nom, :email, NOW())",
     *     [':nom' => 'Durand', ':email' => 'durand@exemple.fr']
     * );
     * 
     * // Mise à jour d'un enregistrement existant
     * $success = $db->execute(
     *     "UPDATE produits SET stock = stock - :quantite WHERE id = :id",
     *     [':quantite' => 5, ':id' => $produitId]
     * );
     * 
     * // Suppression d'un enregistrement
     * $success = $db->execute(
     *     "DELETE FROM commentaires WHERE id = :id AND utilisateur_id = :user_id",
     *     [':id' => $commentId, ':user_id' => $utilisateurId]
     * );
     * 
     * @param string $query  La requête SQL de modification à exécuter
     * @param array $params  Tableau associatif des paramètres à lier à la requête préparée
     * @return bool          True si la requête a réussi, false sinon
     */
    public function execute(string $query, array $params = []): bool
    {
        try {
            // Préparer la requête SQL
            $stmt = $this->pdo->prepare($query);
            
            // Exécuter la requête avec les paramètres et renvoyer directement le résultat
            // execute() retourne true si la requête réussit, false sinon
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            // En cas d'erreur, journaliser les détails et retourner false
            $this->logError($e, $query, $params);
            return false;
        }
    }
    
    /**
     * Récupère l'identifiant de la dernière ligne insérée
     * 
     * Cette méthode est particulièrement utile après une opération INSERT
     * pour récupérer l'identifiant auto-incrémenté généré par la base de données.
     * Elle doit être appelée immédiatement après l'opération d'insertion.
     * 
     * Exemple d'utilisation :
     * 
     * $db->execute(
     *     "INSERT INTO articles (titre, contenu, auteur_id) VALUES (:titre, :contenu, :auteur_id)",
     *     [':titre' => 'Mon titre', ':contenu' => 'Mon contenu', ':auteur_id' => $userId]
     * );
     * $nouvelArticleId = $db->lastInsertId();
     * 
     * // Utilisation de l'ID pour une opération subséquente
     * $db->execute(
     *     "INSERT INTO notifications (type, cible_id, message) VALUES ('nouvel_article', :article_id, 'Nouvel article publié')",
     *     [':article_id' => $nouvelArticleId]
     * );
     * 
     * @return int L'identifiant de la dernière ligne insérée (converti en entier)
     */
    public function lastInsertId(): int
    {
        // La méthode lastInsertId() de PDO retourne une chaîne
        // On la convertit explicitement en entier pour garantir le type de retour
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * Démarre une transaction dans la base de données
     * 
     * Une transaction permet de regrouper plusieurs opérations SQL
     * comme un ensemble atomique. Soit toutes les opérations réussissent
     * et sont appliquées (commit), soit aucune n'est appliquée si une erreur
     * survient (rollback).
     * 
     * Les transactions sont essentielles pour maintenir l'intégrité des données
     * lorsque plusieurs tables ou enregistrements doivent être modifiés ensemble.
     * 
     * Exemple d'utilisation :
     * 
     * try {
     *     $db->beginTransaction();
     *     
     *     // Déduire l'argent du compte source
     *     $db->execute("UPDATE comptes SET solde = solde - :montant WHERE id = :source", 
     *                  [':montant' => $montant, ':source' => $sourceId]);
     *     
     *     // Ajouter l'argent au compte destination
     *     $db->execute("UPDATE comptes SET solde = solde + :montant WHERE id = :dest", 
     *                  [':montant' => $montant, ':dest' => $destId]);
     *     
     *     // Enregistrer l'historique de la transaction
     *     $db->execute("INSERT INTO transferts (source_id, dest_id, montant, date) VALUES (:source, :dest, :montant, NOW())",
     *                  [':source' => $sourceId, ':dest' => $destId, ':montant' => $montant]);
     *     
     *     // Si tout s'est bien passé, valider les changements
     *     $db->commit();
     *     return true;
     * } catch (\Exception $e) {
     *     // En cas d'erreur, annuler toutes les modifications
     *     $db->rollBack();
     *     return false;
     * }
     * 
     * @return bool True si la transaction a été démarrée avec succès
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Valide (commit) une transaction en cours
     * 
     * Cette méthode confirme et applique définitivement toutes les
     * modifications effectuées depuis le début de la transaction.
     * Elle doit être appelée après beginTransaction() lorsque toutes
     * les opérations ont réussi.
     * 
     * @see beginTransaction() pour un exemple d'utilisation complet
     * 
     * @return bool True si la transaction a été validée avec succès
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }
    
    /**
     * Annule (rollback) une transaction en cours
     * 
     * Cette méthode annule toutes les modifications effectuées depuis
     * le début de la transaction, rétablissant la base de données dans
     * son état précédent. Elle est généralement appelée dans un bloc catch
     * pour gérer les erreurs pendant une transaction.
     * 
     * @see beginTransaction() pour un exemple d'utilisation complet
     * 
     * @return bool True si l'annulation de la transaction a réussi
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
    
    /**
     * Échappe une valeur pour une utilisation sécurisée dans une requête SQL
     * 
     * Cette méthode entoure une chaîne de caractères de guillemets et
     * échappe les caractères spéciaux pour éviter les injections SQL.
     * 
     * Note importante: Cette méthode est rarement nécessaire car les
     * requêtes préparées (avec query() et execute()) offrent une meilleure
     * protection. Utilisez-la uniquement dans des cas spécifiques où les
     * requêtes préparées ne sont pas applicables.
     * 
     * Exemple d'utilisation :
     * 
     * // Construction dynamique de clause IN
     * $ids = [1, 2, 3, 4];
     * $placeholders = [];
     * foreach ($ids as $id) {
     *     $placeholders[] = $db->quote($id);
     * }
     * $inClause = implode(',', $placeholders);
     * $query = "SELECT * FROM produits WHERE categorie_id IN ($inClause)";
     * 
     * @param string $value La valeur à échapper
     * @return string La valeur échappée et entourée de guillemets
     */
    public function quote(string $value): string
    {
        return $this->pdo->quote($value);
    }
    
    /**
     * Récupère l'instance PDO sous-jacente
     * 
     * Cette méthode donne un accès direct à l'objet PDO pour les cas
     * où des fonctionnalités avancées non couvertes par cette classe
     * seraient nécessaires.
     * 
     * À utiliser avec précaution car cela contourne les abstractions
     * et la gestion d'erreurs fournies par cette classe.
     * 
     * Exemple d'utilisation :
     * 
     * // Accès à des fonctionnalités spécifiques de PDO
     * $pdo = $db->getPdo();
     * $driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
     * 
     * // Exécution d'une requête complexe avec plusieurs jeux de résultats
     * $stmt = $pdo->query("CALL procedure_stockee()");
     * $premiersResultats = $stmt->fetchAll();
     * $stmt->nextRowset();
     * $secondsResultats = $stmt->fetchAll();
     * 
     * @return \PDO L'instance PDO utilisée par cette classe
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
    
    /**
     * Journalise une erreur SQL dans un fichier de log
     * 
     * Cette méthode privée gère l'enregistrement des erreurs SQL :
     * 1. Elle crée un message d'erreur détaillé avec horodatage
     * 2. Écrit ce message dans un fichier de journal
     * 3. En mode développement, affiche également les détails à l'écran
     * 
     * Le journal d'erreurs est essentiel pour :
     * - Diagnostiquer les problèmes en production
     * - Suivre les tentatives d'injection SQL
     * - Identifier les bugs dans le code SQL
     * 
     * @param \PDOException $e    L'exception PDO survenue
     * @param string $query       La requête SQL qui a provoqué l'erreur
     * @param array $params       Les paramètres utilisés dans la requête
     */
    private function logError(\PDOException $e, string $query, array $params): void
    {
        // Créer un message d'erreur détaillé avec horodatage
        $errorMessage = date('Y-m-d H:i:s') . " - Erreur SQL: " . $e->getMessage() . "\n";
        $errorMessage .= "Requête: $query\n";
        $errorMessage .= "Paramètres: " . json_encode($params) . "\n\n";
        
        // Écrire l'erreur dans le fichier de log
        // Le paramètre 3 de error_log indique que le message doit être ajouté à un fichier
        error_log($errorMessage, 3, ROOT_PATH . '/logs/database.log');
        
        // En mode développement, afficher les détails à l'écran pour faciliter le débogage
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo '<div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 5px;">';
            echo '<h3>Erreur SQL</h3>';
            echo '<p><strong>Message:</strong> ' . $e->getMessage() . '</p>';
            echo '<p><strong>Requête:</strong> ' . $query . '</p>';
            echo '<p><strong>Paramètres:</strong> ' . json_encode($params) . '</p>';
            echo '</div>';
        }
    }
}