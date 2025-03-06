<?php
/**
 * Classe Model - Fondation de la couche d'accès aux données
 * 
 * Cette classe abstraite implémente le M (Model) du pattern MVC/HMVC et constitue
 * la base de tous les modèles de l'application. Elle fournit un ensemble complet
 * d'opérations CRUD (Create, Read, Update, Delete) génériques qui simplifient
 * considérablement l'interaction avec la base de données.
 * 
 * Inspirée du pattern ActiveRecord, cette classe établit une correspondance directe
 * entre :
 * - Une classe modèle et une table de la base de données
 * - Une instance d'un modèle et une ligne (enregistrement) de cette table
 * - Les propriétés d'un modèle et les colonnes de la table
 * 
 * Les modèles sont responsables de :
 * - Représenter la structure des données de l'application
 * - Encapsuler toute la logique d'accès à la base de données
 * - Appliquer les règles métier et valider les données
 * - Maintenir l'intégrité des données
 * - Isoler le reste de l'application des détails de stockage des données
 * 
 * En étendant cette classe, les modèles spécifiques héritent d'un ensemble
 * complet de méthodes tout en pouvant les personnaliser selon leurs besoins.
 */

namespace App\Core;

class Model
{
    /**
     * Instance de la base de données
     * 
     * Cette propriété stocke la connexion à la base de données qui sera utilisée
     * pour exécuter toutes les requêtes SQL. Elle est injectée dans le constructeur,
     * ce qui facilite les tests unitaires et renforce la modularité du code.
     * 
     * L'encapsulation de la connexion à la base de données dans cette propriété
     * permet également de s'assurer que tous les modèles utilisent la même
     * connexion, évitant ainsi les problèmes de concurrence et d'incohérence.
     * 
     * @var Database
     */
    protected Database $db;
    
    /**
     * Nom de la table dans la base de données
     * 
     * Cette propriété définit la table SQL associée au modèle. Elle peut être :
     * - Définie explicitement dans les classes enfants
     * - Déduite automatiquement à partir du nom de la classe du modèle
     * 
     * La convention de nommage par défaut suit ces règles :
     * - Le nom de classe UserModel correspond à la table 'users'
     * - Le nom de classe ProductCategory correspond à la table 'productcategories'
     * 
     * Cette correspondance automatique facilite le développement en évitant
     * d'avoir à spécifier manuellement chaque nom de table.
     * 
     * @var string
     */
    protected string $table;
    
    /**
     * Nom de la clé primaire de la table
     * 
     * Définit la colonne qui sert d'identifiant unique pour chaque enregistrement.
     * Par défaut, la convention est d'utiliser 'id', mais cette propriété peut
     * être redéfinie dans les classes enfants pour les tables utilisant une
     * convention différente (ex: 'user_id', 'product_code', etc.).
     * 
     * La clé primaire est utilisée par les méthodes find(), update() et delete()
     * pour cibler un enregistrement spécifique.
     * 
     * @var string
     */
    protected string $primaryKey = 'id';
    
    /**
     * Constructeur du modèle
     * 
     * Initialise une nouvelle instance du modèle en :
     * 1. Stockant la connexion à la base de données
     * 2. Déterminant automatiquement le nom de la table associée (si non spécifié)
     * 
     * L'auto-détection du nom de table suit la convention "convention over configuration"
     * qui favorise les conventions plutôt que la configuration explicite, réduisant
     * ainsi la quantité de code nécessaire.
     * 
     * Exemple d'utilisation dans une classe enfant :
     * // Instanciation d'un modèle spécifique
     * $userModel = new UserModel($database);
     * 
     * // Avec définition explicite de la table (dans la classe enfant)
     * class ProductModel extends Model {
     *     protected string $table = 'store_products';
     * }
     * 
     * @param Database $db L'instance de connexion à la base de données
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
        
        // Si le nom de la table n'est pas défini explicitement dans la classe enfant,
        // le déduire à partir du nom de la classe du modèle
        if (!isset($this->table)) {
            // Récupérer juste le nom de la classe (sans le namespace)
            // La fonction basename avec str_replace simule le comportement de récupérer
            // uniquement la dernière partie du nom de classe qualifié
            $className = basename(str_replace('\\', '/', get_class($this)));
            
            // Enlever le suffixe "Model" s'il existe
            // Ceci permet d'avoir des noms de classe comme UserModel -> table 'users'
            $className = preg_replace('/Model$/', '', $className);
            
            // Convertir en minuscules et pluraliser (ajout d'un 's')
            // Cette simple règle couvre la majorité des cas d'usage communs
            $this->table = strtolower($className) . 's';
        }
    }
    
    /**
     * Trouver un enregistrement par sa clé primaire
     * 
     * Cette méthode est l'implémentation du R (Read) du CRUD pour un enregistrement
     * unique. Elle permet de récupérer un enregistrement spécifique à partir de
     * sa valeur de clé primaire, généralement un identifiant numérique.
     * 
     * La méthode utilise une requête préparée avec la valeur de clé primaire
     * comme paramètre, garantissant ainsi la sécurité contre les injections SQL.
     * 
     * Exemples d'utilisation :
     * // Récupérer un utilisateur par son ID
     * $user = $userModel->find(5);
     * 
     * // Vérifier si l'utilisateur existe
     * if ($user) {
     *     echo "Utilisateur trouvé : " . $user['name'];
     * } else {
     *     echo "Utilisateur non trouvé";
     * }
     * 
     * @param mixed $id Valeur de la clé primaire (entier, chaîne, etc.)
     * @return array|null Tableau associatif des données de l'enregistrement, ou null si non trouvé
     */
    public function find(mixed $id): ?array
    {
        // Construire la requête SQL avec une clause WHERE sur la clé primaire
        // LIMIT 1 optimise la requête pour ne renvoyer qu'un seul résultat
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        
        // Définir les paramètres pour la requête préparée
        // L'utilisation de paramètres nommés (:id) améliore la lisibilité
        $params = [':id' => $id];
        
        // Exécuter la requête via l'instance de base de données
        $result = $this->db->query($query, $params);
        
        // Renvoyer le premier (et unique) enregistrement du résultat ou null si aucun résultat
        // L'indice [0] accède au premier élément du tableau de résultats
        return $result ? $result[0] : null;
    }
    
    /**
     * Récupérer tous les enregistrements de la table
     * 
     * Cette méthode implémente une requête SELECT simple sans condition,
     * récupérant ainsi tous les enregistrements présents dans la table.
     * 
     * Attention : Pour les tables volumineuses, cette méthode peut entraîner
     * des problèmes de performance ou de mémoire. Dans ces cas, il est préférable
     * d'utiliser des méthodes avec pagination ou des requêtes plus spécifiques.
     * 
     * Exemples d'utilisation :
     * // Récupérer tous les utilisateurs
     * $allUsers = $userModel->all();
     * 
     * // Parcourir les résultats
     * foreach ($allUsers as $user) {
     *     echo $user['name'] . " - " . $user['email'] . "<br>";
     * }
     * 
     * @return array Tableau contenant tous les enregistrements (vide si aucun)
     */
    public function all(): array
    {
        // Requête SQL simple pour sélectionner tous les enregistrements
        $query = "SELECT * FROM {$this->table}";
        
        // Exécuter la requête et renvoyer les résultats
        // L'opérateur ternaire garantit qu'un tableau vide est renvoyé si aucun résultat
        return $this->db->query($query) ?: [];
    }
    
    /**
     * Récupérer des enregistrements selon des conditions spécifiques
     * 
     * Cette méthode flexible permet de filtrer les enregistrements en fonction
     * d'un ensemble de conditions. Les conditions sont spécifiées sous forme
     * d'un tableau associatif où les clés sont les noms des champs et les valeurs
     * sont les valeurs à rechercher.
     * 
     * L'opérateur logique (AND/OR) permet de déterminer comment combiner les
     * conditions multiples :
     * - AND : tous les critères doivent être satisfaits (intersection)
     * - OR : au moins un critère doit être satisfait (union)
     * 
     * Exemples d'utilisation :
     * // Utilisateurs actifs avec un rôle spécifique (AND)
     * $activeAdmins = $userModel->where(['active' => 1, 'role' => 'admin']);
     * 
     * // Utilisateurs ayant l'un des deux rôles (OR)
     * $staff = $userModel->where(['role' => 'admin', 'role' => 'manager'], 'OR');
     * 
     * // Produits d'une catégorie spécifique
     * $categoryProducts = $productModel->where(['category_id' => $categoryId]);
     * 
     * @param array $conditions Tableau associatif de conditions (champ => valeur)
     * @param string $operator Opérateur logique entre les conditions ('AND' ou 'OR')
     * @return array Tableau des enregistrements correspondants (vide si aucun)
     */
    public function where(array $conditions, string $operator = 'AND'): array
    {
        // Préparer les conditions WHERE et les paramètres pour la requête préparée
        $whereConditions = [];
        $params = [];
        
        // Construire chaque condition et collecter les paramètres
        foreach ($conditions as $field => $value) {
            // Créer un paramètre nommé unique pour chaque condition
            $param = ':' . $field;
            // Ajouter la condition à la liste des conditions
            $whereConditions[] = "$field = $param";
            // Associer la valeur au paramètre nommé
            $params[$param] = $value;
        }
        
        // Construire la clause WHERE complète en joignant les conditions avec l'opérateur spécifié
        $whereClause = implode(" $operator ", $whereConditions);
        
        // Construire la requête SQL de base
        $query = "SELECT * FROM {$this->table}";
        
        // Ajouter la clause WHERE seulement s'il y a des conditions
        if (!empty($whereConditions)) {
            $query .= " WHERE $whereClause";
        }
        
        // Exécuter la requête et renvoyer les résultats (ou tableau vide si aucun)
        return $this->db->query($query, $params) ?: [];
    }
    
    /**
     * Récupérer le premier enregistrement correspondant aux conditions
     * 
     * Cette méthode est similaire à where(), mais elle est optimisée pour
     * récupérer uniquement le premier enregistrement correspondant aux critères.
     * Elle est particulièrement utile lorsqu'on s'attend à un résultat unique
     * (par exemple, recherche par email, nom d'utilisateur, etc.).
     * 
     * L'ajout de LIMIT 1 à la requête SQL optimise la performance en arrêtant
     * la recherche dès qu'un enregistrement correspondant est trouvé.
     * 
     * Exemples d'utilisation :
     * // Rechercher un utilisateur par son email
     * $user = $userModel->firstWhere(['email' => 'user@example.com']);
     * 
     * // Rechercher un produit par son code
     * $product = $productModel->firstWhere(['sku' => 'PROD123']);
     * 
     * // Vérifier si un nom d'utilisateur est déjà pris
     * $existing = $userModel->firstWhere(['username' => $requestedUsername]);
     * if ($existing) {
     *     echo "Ce nom d'utilisateur est déjà pris";
     * }
     * 
     * @param array $conditions Tableau associatif de conditions (champ => valeur)
     * @param string $operator Opérateur logique entre les conditions ('AND' ou 'OR')
     * @return array|null Premier enregistrement correspondant ou null si aucun
     */
    public function firstWhere(array $conditions, string $operator = 'AND'): ?array
    {
        // La logique est similaire à la méthode where() mais avec LIMIT 1
        $whereConditions = [];
        $params = [];
        
        foreach ($conditions as $field => $value) {
            $param = ':' . $field;
            $whereConditions[] = "$field = $param";
            $params[$param] = $value;
        }
        
        $whereClause = implode(" $operator ", $whereConditions);
        
        $query = "SELECT * FROM {$this->table}";
        if (!empty($whereConditions)) {
            $query .= " WHERE $whereClause";
        }
        
        // Ajouter LIMIT 1 pour ne récupérer que le premier enregistrement correspondant
        // Cette optimisation est importante pour les grandes tables
        $query .= " LIMIT 1";
        
        // Exécuter la requête
        $result = $this->db->query($query, $params);
        
        // Renvoyer le premier enregistrement ou null si aucun résultat
        return $result ? $result[0] : null;
    }
    
    /**
     * Créer un nouvel enregistrement
     * 
     * Cette méthode implémente le C (Create) du CRUD. Elle permet d'insérer
     * un nouvel enregistrement dans la table avec les données fournies dans
     * le tableau associatif.
     * 
     * La méthode construit dynamiquement une requête INSERT à partir des clés
     * et valeurs du tableau, assurant ainsi la flexibilité et la sécurité
     * (via des requêtes préparées).
     * 
     * Exemples d'utilisation :
     * // Créer un nouvel utilisateur
     * $newUserId = $userModel->create([
     *     'name' => 'Marie Dupont',
     *     'email' => 'marie@example.com',
     *     'password' => password_hash('secret123', PASSWORD_DEFAULT),
     *     'active' => 1,
     *     'registration_date' => date('Y-m-d H:i:s')
     * ]);
     * 
     * // Vérifier si la création a réussi
     * if ($newUserId) {
     *     echo "Nouvel utilisateur créé avec l'ID : $newUserId";
     * } else {
     *     echo "Erreur lors de la création de l'utilisateur";
     * }
     * 
     * @param array $data Tableau associatif des données à insérer (colonne => valeur)
     * @return int|null ID du nouvel enregistrement (via auto-increment) ou null en cas d'échec
     */
    public function create(array $data): ?int
    {
        // Extraire les noms des champs à partir des clés du tableau de données
        $fields = array_keys($data);
        
        // Construire la liste des noms de champs pour la clause INSERT
        $fieldsList = implode(', ', $fields);
        
        // Construire la liste des placeholders de paramètres pour les valeurs
        // Cette approche garantit une requête préparée sécurisée
        $placeholders = ':' . implode(', :', $fields);
        
        // Assembler la requête INSERT complète
        $query = "INSERT INTO {$this->table} ($fieldsList) VALUES ($placeholders)";
        
        // Préparer les paramètres pour la requête préparée
        // Les clés des paramètres doivent correspondre aux placeholders
        $params = [];
        foreach ($data as $field => $value) {
            $params[':' . $field] = $value;
        }
        
        // Exécuter la requête et renvoyer l'ID du nouvel enregistrement ou null
        // L'ID est récupéré via lastInsertId() qui retourne l'ID auto-incrémenté
        return $this->db->execute($query, $params) ? $this->db->lastInsertId() : null;
    }
    
    /**
     * Mettre à jour un enregistrement existant
     * 
     * Cette méthode implémente le U (Update) du CRUD. Elle permet de modifier
     * un enregistrement existant identifié par sa clé primaire.
     * 
     * Comme pour create(), la méthode construit dynamiquement une requête SQL
     * à partir des données fournies, garantissant flexibilité et sécurité.
     * 
     * Exemples d'utilisation :
     * // Mettre à jour les informations d'un utilisateur
     * $success = $userModel->update(5, [
     *     'name' => 'Marie Dupont-Martin',
     *     'email' => 'marie.new@example.com',
     *     'last_login' => date('Y-m-d H:i:s')
     * ]);
     * 
     * // Désactiver un utilisateur
     * $success = $userModel->update($userId, ['active' => 0]);
     * 
     * // Vérifier si la mise à jour a réussi
     * if ($success) {
     *     echo "Mise à jour réussie";
     * } else {
     *     echo "Erreur lors de la mise à jour";
     * }
     * 
     * @param mixed $id Valeur de la clé primaire de l'enregistrement à mettre à jour
     * @param array $data Tableau associatif des données à mettre à jour (colonne => valeur)
     * @return bool True si la mise à jour a réussi (au moins une ligne affectée), false sinon
     */
    public function update(mixed $id, array $data): bool
    {
        // Préparer les clauses SET pour chaque champ à mettre à jour
        $sets = [];
        $params = [];
        
        // Construire les clauses SET et collecter les paramètres
        foreach ($data as $field => $value) {
            $param = ':' . $field;
            $sets[] = "$field = $param";
            $params[$param] = $value;
        }
        
        // Assembler la clause SET complète
        $setClause = implode(', ', $sets);
        
        // Construire la requête UPDATE avec condition sur la clé primaire
        $query = "UPDATE {$this->table} SET $setClause WHERE {$this->primaryKey} = :id";
        
        // Ajouter l'ID aux paramètres pour la clause WHERE
        $params[':id'] = $id;
        
        // Exécuter la requête et retourner le résultat (succès ou échec)
        return $this->db->execute($query, $params);
    }
    
    /**
     * Supprimer un enregistrement
     * 
     * Cette méthode implémente le D (Delete) du CRUD. Elle permet de supprimer
     * un enregistrement spécifique identifié par sa clé primaire.
     * 
     * La suppression est définitive et ne peut être annulée, sauf si la base de données
     * possède un mécanisme de transactions ou de sauvegarde.
     * 
     * Exemples d'utilisation :
     * // Supprimer un utilisateur
     * $success = $userModel->delete(5);
     * 
     * // Supprimer un produit et vérifier le résultat
     * if ($productModel->delete($productId)) {
     *     echo "Produit supprimé avec succès";
     * } else {
     *     echo "Erreur lors de la suppression du produit";
     * }
     * 
     * // Pour une "suppression douce" (soft delete), utilisez plutôt update()
     * $userModel->update($userId, ['deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')]);
     * 
     * @param mixed $id Valeur de la clé primaire de l'enregistrement à supprimer
     * @return bool True si la suppression a réussi (au moins une ligne affectée), false sinon
     */
    public function delete(mixed $id): bool
    {
        // Construire une requête DELETE simple avec condition sur la clé primaire
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        
        // Définir le paramètre pour la clé primaire
        $params = [':id' => $id];
        
        // Exécuter la requête et retourner le résultat (succès ou échec)
        return $this->db->execute($query, $params);
    }
    
    /**
     * Compter le nombre d'enregistrements
     * 
     * Cette méthode utilitaire permet de compter le nombre total d'enregistrements
     * dans la table, ou le nombre d'enregistrements correspondant à des critères
     * spécifiques si des conditions sont fournies.
     * 
     * L'utilisation de COUNT(*) est plus efficace que de récupérer tous les
     * enregistrements puis de compter le tableau résultant, particulièrement
     * pour les grandes tables.
     * 
     * Exemples d'utilisation :
     * // Compter tous les utilisateurs
     * $totalUsers = $userModel->count();
     * 
     * // Compter les utilisateurs actifs
     * $activeUsers = $userModel->count(['active' => 1]);
     * 
     * // Compter les produits d'une catégorie
     * $categoryProductCount = $productModel->count(['category_id' => $categoryId]);
     * 
     * // Afficher des statistiques
     * echo "Utilisateurs actifs : $activeUsers sur $totalUsers utilisateurs total";
     * 
     * @param array $conditions Tableau associatif de conditions (champ => valeur)
     * @return int Le nombre d'enregistrements correspondants
     */
    public function count(array $conditions = []): int
    {
        // Utiliser COUNT(*) pour compter efficacement les enregistrements
        // L'alias 'count' simplifie l'accès au résultat
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        
        $params = [];
        
        // Ajouter des conditions WHERE si spécifiées
        if (!empty($conditions)) {
            $whereConditions = [];
            
            // Construire les conditions et collecter les paramètres
            foreach ($conditions as $field => $value) {
                $param = ':' . $field;
                $whereConditions[] = "$field = $param";
                $params[$param] = $value;
            }
            
            // Assembler la clause WHERE (toujours avec AND pour ce cas)
            $whereClause = implode(' AND ', $whereConditions);
            $query .= " WHERE $whereClause";
        }
        
        // Exécuter la requête
        $result = $this->db->query($query, $params);
        
        // Extraire et retourner le comptage (convertir en entier pour garantir le type)
        // En cas d'échec de la requête, retourner 0
        return $result ? (int) $result[0]['count'] : 0;
    }
}