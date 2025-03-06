<?php
/**
 * Classe Router - Système avancé de routage HTTP
 * 
 * Cette classe fondamentale fournit un mécanisme sophistiqué de routage
 * qui sert de "contrôleur frontal" de l'application. Elle est responsable de :
 * - Associer des chemins URL à des actions spécifiques (contrôleurs, méthodes)
 * - Extraire les paramètres dynamiques des URLs
 * - Diriger les requêtes vers les bons modules HMVC
 * - Appliquer les middlewares appropriés
 * - Organiser la structure de navigation de l'application
 * 
 * Le routeur est l'un des composants les plus critiques de l'architecture HMVC
 * car il détermine comment les requêtes des utilisateurs sont mappées
 * aux fonctionnalités de l'application, assurant ainsi une séparation claire
 * entre l'URL publique et la structure interne du code.
 * 
 * Dans un framework HMVC, le routeur joue également un rôle d'orchestrateur
 * entre les différents modules, permettant de construire des applications
 * modulaires tout en maintenant une structure d'URL cohérente.
 */

namespace App\Core;

class Router
{
    /**
     * Registre complet des routes définies dans l'application
     * 
     * Ce tableau stocke toutes les routes enregistrées avec leurs configurations
     * complètes. Chaque route est représentée par un tableau associatif contenant :
     * 
     * - method : La méthode HTTP à laquelle la route répond (GET, POST, PUT, DELETE...)
     * - pattern : Le modèle d'URL défini par le développeur avec paramètres (ex: /articles/{id})
     * - regex : L'expression régulière générée automatiquement pour la correspondance des URLs
     * - handler : Le gestionnaire de la route (chaîne "Contrôleur@action" ou closure)
     * - module : Le module HMVC auquel la route appartient
     * - middlewares : Un tableau des middlewares à appliquer à cette route
     * 
     * Cette structure permet au routeur de rechercher efficacement une correspondance
     * entre l'URL demandée par l'utilisateur et les routes définies, puis
     * d'extraire toutes les informations nécessaires pour exécuter la requête.
     * 
     * @var array
     */
    private array $routes = [];
    
    /**
     * Enregistrer une nouvelle route avec toutes ses spécifications
     * 
     * Cette méthode fondamentale enregistre une route complète dans le système,
     * en définissant tous ses attributs : méthode HTTP, pattern d'URL, handler,
     * module associé et middlewares. Elle constitue la base sur laquelle
     * reposent toutes les autres méthodes de définition de routes.
     * 
     * Le processus comprend :
     * 1. La conversion du pattern d'URL en expression régulière
     * 2. La normalisation de la méthode HTTP
     * 3. L'enregistrement de la route complète dans le registre
     * 
     * La méthode supporte deux types de handlers :
     * - Une chaîne au format "Contrôleur@action" qui sera résolue par l'application
     * - Une fonction anonyme (closure) qui sera exécutée directement
     * 
     * Exemples d'utilisation :
     * // Route standard vers un contrôleur
     * $router->addRoute('GET', '/articles/{id}', 'ArticleController@show', 'Blog', ['AuthMiddleware']);
     * 
     * // Route utilisant une closure
     * $router->addRoute('GET', '/maintenance', function() { 
     *     return 'Site en maintenance, revenez plus tard.';
     * }, 'Core');
     * 
     * // Route avec authentification requise
     * $router->addRoute('POST', '/profil', 'UserController@updateProfile', 'User', ['AuthMiddleware']);
     * 
     * @param string $method Méthode HTTP (GET, POST, PUT, DELETE, etc.) ou ANY pour toutes
     * @param string $pattern Modèle d'URL qui peut contenir des paramètres (ex: /articles/{id})
     * @param mixed $handler Fonction anonyme ou chaîne 'Contrôleur@action' à exécuter
     * @param string $module Nom du module HMVC qui traitera cette route
     * @param array $middlewares Liste des middlewares à appliquer avant le traitement
     * @return self Instance du routeur pour permettre le chaînage des méthodes
     */
    public function addRoute(string $method, string $pattern, mixed $handler, string $module, array $middlewares = []): self
    {
        // Transformer le pattern en expression régulière pour la correspondance des URLs
        // Cette conversion permet d'extraire les paramètres dynamiques (ex: {id} → (?<id>[^/]+))
        $regex = $this->patternToRegex($pattern);
        
        // Enregistrer la route avec toutes ses informations dans le registre
        $this->routes[] = [
            'method' => strtoupper($method),  // Normaliser la méthode en majuscules pour la cohérence
            'pattern' => $pattern,            // Conserver le pattern original pour référence
            'regex' => $regex,                // Expression régulière pour la correspondance d'URL
            'handler' => $handler,            // Gestionnaire à exécuter (Contrôleur@action ou closure)
            'module' => $module,              // Module HMVC auquel appartient cette route
            'middlewares' => $middlewares     // Middlewares à appliquer avant le traitement
        ];
        
        // Retourner l'instance du routeur pour permettre le chaînage des méthodes
        return $this;
    }
    
    /**
     * Convertir un pattern d'URL en expression régulière
     * 
     * Cette méthode interne transforme un pattern d'URL défini par le développeur,
     * comme "/articles/{id}", en une expression régulière utilisable pour la
     * correspondance d'URLs et l'extraction des paramètres.
     * 
     * Le processus de transformation :
     * 1. Échapper les caractères spéciaux pour éviter les conflits avec la syntaxe regex
     * 2. Remplacer les segments {param} par des groupes de capture nommés (?<param>[^/]+)
     * 3. Encadrer l'expression par des délimiteurs et des ancres pour une correspondance exacte
     * 
     * Par exemple, le pattern "/articles/{id}/comments/{commentId}" sera transformé
     * en "#^/articles/(?<id>[^/]+)/comments/(?<commentId>[^/]+)$#"
     * 
     * Cette transformation permet :
     * - De vérifier si une URL correspond au pattern
     * - D'extraire automatiquement les valeurs des paramètres dynamiques
     * - D'assurer que l'ensemble de l'URL correspond, pas seulement une partie
     * 
     * @param string $pattern Modèle d'URL avec paramètres (ex: /articles/{id})
     * @return string Expression régulière prête à l'emploi pour la correspondance
     */
    private function patternToRegex(string $pattern): string
    {
        // Échapper les caractères spéciaux du pattern pour la regex
        // Cela évite les conflits avec la syntaxe des expressions régulières
        // Le délimiteur '#' est spécifié comme deuxième paramètre
        $pattern = preg_quote($pattern, '#');
        
        // Remplacer les segments {param} par des groupes de capture nommés (?<param>[^/]+)
        // \\\{ et \\\} représentent les accolades échappées par preg_quote
        // [^/]+ capture tout caractère sauf le séparateur de chemin ('/')
        $pattern = preg_replace('#\\\{([a-zA-Z0-9_]+)\\\}#', '(?<$1>[^/]+)', $pattern);
        
        // Ajouter les délimiteurs et les ancres pour créer une regex complète
        // ^ assure que le match commence au début de l'URI
        // $ assure que le match va jusqu'à la fin de l'URI
        return "#^{$pattern}$#";
    }
    
    /**
     * Trouver une route correspondant à une URI et une méthode HTTP
     * 
     * Cette méthode essentielle est appelée par l'application pour déterminer
     * quelle route doit traiter une requête HTTP entrante. Elle parcourt
     * toutes les routes enregistrées et trouve celle qui correspond à l'URI 
     * et à la méthode HTTP spécifiées.
     * 
     * Le processus de correspondance :
     * 1. Normaliser la méthode HTTP pour la comparaison
     * 2. Parcourir toutes les routes enregistrées
     * 3. Vérifier si la méthode HTTP correspond (ou si la route accepte ANY)
     * 4. Tester l'URI contre l'expression régulière de la route
     * 5. Extraire les paramètres dynamiques si correspondance trouvée
     * 6. Préparer les informations de route pour l'exécution
     * 
     * En cas de correspondance, cette méthode retourne un tableau contenant
     * toutes les informations nécessaires pour traiter la requête :
     * - Le module HMVC concerné
     * - Le contrôleur à instancier (ou null pour les closures)
     * - L'action à exécuter (ou la closure elle-même)
     * - Les paramètres extraits de l'URL
     * - Les middlewares à appliquer
     * 
     * @param string $uri URI à matcher (ex: /articles/5)
     * @param string $method Méthode HTTP de la requête (GET, POST, etc.)
     * @return array|null Informations complètes sur la route correspondante ou null si aucune correspondance
     */
    public function match(string $uri, string $method): ?array
    {
        // Normaliser la méthode HTTP en majuscules pour la comparaison
        $method = strtoupper($method);
        
        // Parcourir toutes les routes enregistrées dans le système
        foreach ($this->routes as $route) {
            // Vérifier si la méthode HTTP correspond
            // 'ANY' est une méthode spéciale qui accepte toutes les méthodes HTTP
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                continue; // Passer à la route suivante si la méthode ne correspond pas
            }
            
            // Tester si l'URI correspond au pattern de la route
            // via l'expression régulière générée précédemment
            if (preg_match($route['regex'], $uri, $matches)) {
                // Correspondance trouvée ! Extraire les paramètres dynamiques de l'URL
                // en ignorant les correspondances numériques (qui sont les correspondances globales)
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        $params[$key] = $value;
                    }
                }
                
                // Récupérer le handler de la route (contrôleur@action ou closure)
                $handler = $route['handler'];
                
                // Traiter différemment selon le type de handler
                
                // Si le handler est une chaîne au format 'Contrôleur@action'
                if (is_string($handler)) {
                    // Décomposer la chaîne pour extraire le contrôleur et l'action
                    [$controller, $action] = explode('@', $handler);
                    
                    // Retourner toutes les informations nécessaires pour l'exécution
                    return [
                        $route['module'],      // Module HMVC à utiliser
                        $controller,           // Nom du contrôleur à instancier
                        $action,               // Nom de la méthode à appeler
                        $params,               // Paramètres extraits de l'URL
                        $route['middlewares']  // Middlewares à appliquer
                    ];
                }
                
                // Si le handler est une closure (fonction anonyme)
                if ($handler instanceof \Closure) {
                    // Retourner les informations spécifiques pour l'exécution d'une closure
                    return [
                        $route['module'],      // Module HMVC à utiliser
                        null,                  // Pas de contrôleur (c'est une closure)
                        $handler,              // La closure à exécuter directement
                        $params,               // Paramètres extraits de l'URL
                        $route['middlewares']  // Middlewares à appliquer
                    ];
                }
            }
        }
        
        // Aucune route correspondante trouvée
        return null;
    }
    
    /**
     * Définir une route répondant à la méthode GET
     * 
     * Cette méthode pratique est un raccourci pour définir une route
     * qui ne répond qu'aux requêtes HTTP GET. Les requêtes GET sont
     * utilisées pour récupérer des informations sans modifier l'état du serveur.
     * 
     * Utilisations typiques :
     * - Afficher une page ou un formulaire
     * - Récupérer des données (liste d'articles, détails d'un produit)
     * - Navigation générale du site
     * - Endpoints API en lecture seule
     * 
     * Exemples d'utilisation :
     * // Page d'accueil
     * $router->get('/', 'HomeController@index', 'Core');
     * 
     * // Affichage d'un article
     * $router->get('/articles/{id}', 'ArticleController@show', 'Blog');
     * 
     * // Liste des utilisateurs (avec middleware d'authentification)
     * $router->get('/admin/users', 'UserController@index', 'Admin', ['AuthMiddleware']);
     * 
     * @param string $pattern Modèle d'URL (ex: /articles ou /articles/{id})
     * @param mixed $handler Contrôleur@action ou closure
     * @param string $module Nom du module HMVC
     * @param array $middlewares Middlewares à appliquer
     * @return self Instance du routeur pour le chaînage
     */
    public function get(string $pattern, mixed $handler, string $module, array $middlewares = []): self
    {
        return $this->addRoute('GET', $pattern, $handler, $module, $middlewares);
    }
    
    /**
     * Définir une route répondant à la méthode POST
     * 
     * Cette méthode pratique est un raccourci pour définir une route
     * qui ne répond qu'aux requêtes HTTP POST. Les requêtes POST sont
     * généralement utilisées pour soumettre des données qui modifieront
     * l'état du serveur ou de la base de données.
     * 
     * Utilisations typiques :
     * - Soumission de formulaires
     * - Création de nouveaux enregistrements
     * - Authentification (login)
     * - Upload de fichiers
     * 
     * Exemples d'utilisation :
     * // Traitement d'un formulaire de contact
     * $router->post('/contact', 'ContactController@submit', 'Core');
     * 
     * // Création d'un nouvel article
     * $router->post('/articles', 'ArticleController@store', 'Blog', ['AuthMiddleware']);
     * 
     * // Authentification utilisateur
     * $router->post('/login', 'AuthController@login', 'User');
     * 
     * @param string $pattern Modèle d'URL (ex: /articles ou /login)
     * @param mixed $handler Contrôleur@action ou closure
     * @param string $module Nom du module HMVC
     * @param array $middlewares Middlewares à appliquer
     * @return self Instance du routeur pour le chaînage
     */
    public function post(string $pattern, mixed $handler, string $module, array $middlewares = []): self
    {
        return $this->addRoute('POST', $pattern, $handler, $module, $middlewares);
    }
    
    /**
     * Définir une route répondant à la méthode PUT
     * 
     * Cette méthode pratique est un raccourci pour définir une route
     * qui ne répond qu'aux requêtes HTTP PUT. Dans une API RESTful,
     * les requêtes PUT sont utilisées pour mettre à jour complètement
     * une ressource existante en remplaçant toutes ses données.
     * 
     * Étant donné que les navigateurs ne supportent nativement que GET et POST,
     * les requêtes PUT sont souvent simulées via un champ _method dans un formulaire
     * POST ou en utilisant JavaScript.
     * 
     * Utilisations typiques :
     * - Mise à jour complète d'une ressource dans une API REST
     * - Remplacement total d'un contenu existant
     * 
     * Exemples d'utilisation :
     * // Mise à jour complète d'un article
     * $router->put('/articles/{id}', 'ArticleController@update', 'Blog', ['AuthMiddleware']);
     * 
     * // Mise à jour d'un profil utilisateur
     * $router->put('/users/{id}/profile', 'UserController@updateProfile', 'User', ['AuthMiddleware']);
     * 
     * @param string $pattern Modèle d'URL (ex: /articles/{id})
     * @param mixed $handler Contrôleur@action ou closure
     * @param string $module Nom du module HMVC
     * @param array $middlewares Middlewares à appliquer
     * @return self Instance du routeur pour le chaînage
     */
    public function put(string $pattern, mixed $handler, string $module, array $middlewares = []): self
    {
        return $this->addRoute('PUT', $pattern, $handler, $module, $middlewares);
    }
    
    /**
     * Définir une route répondant à la méthode DELETE
     * 
     * Cette méthode pratique est un raccourci pour définir une route
     * qui ne répond qu'aux requêtes HTTP DELETE. Dans une API RESTful,
     * les requêtes DELETE sont utilisées pour supprimer une ressource.
     * 
     * Comme pour PUT, cette méthode est souvent simulée dans les navigateurs
     * via un champ _method dans un formulaire POST ou en utilisant JavaScript.
     * 
     * Utilisations typiques :
     * - Suppression d'une ressource
     * - Suppression d'une session (déconnexion)
     * - Annulation d'une action
     * 
     * Exemples d'utilisation :
     * // Suppression d'un article
     * $router->delete('/articles/{id}', 'ArticleController@destroy', 'Blog', ['AuthMiddleware']);
     * 
     * // Suppression d'un compte utilisateur
     * $router->delete('/account', 'UserController@deleteAccount', 'User', ['AuthMiddleware']);
     * 
     * @param string $pattern Modèle d'URL (ex: /articles/{id})
     * @param mixed $handler Contrôleur@action ou closure
     * @param string $module Nom du module HMVC
     * @param array $middlewares Middlewares à appliquer
     * @return self Instance du routeur pour le chaînage
     */
    public function delete(string $pattern, mixed $handler, string $module, array $middlewares = []): self
    {
        return $this->addRoute('DELETE', $pattern, $handler, $module, $middlewares);
    }
    
    /**
     * Définir une route répondant à la méthode PATCH
     * 
     * Cette méthode pratique est un raccourci pour définir une route
     * qui ne répond qu'aux requêtes HTTP PATCH. Dans une API RESTful,
     * les requêtes PATCH sont utilisées pour une mise à jour partielle
     * d'une ressource existante, en ne modifiant que les champs spécifiés.
     * 
     * Comme pour PUT et DELETE, cette méthode est souvent simulée dans les navigateurs
     * via un champ _method dans un formulaire POST ou en utilisant JavaScript.
     * 
     * Utilisations typiques :
     * - Mise à jour partielle d'une ressource
     * - Modification de certains attributs spécifiques
     * - Activation/désactivation d'une fonctionnalité
     * 
     * Exemples d'utilisation :
     * // Mise à jour partielle d'un article
     * $router->patch('/articles/{id}', 'ArticleController@update', 'Blog', ['AuthMiddleware']);
     * 
     * // Modifier le statut d'une commande
     * $router->patch('/orders/{id}/status', 'OrderController@updateStatus', 'Shop', ['AdminMiddleware']);
     * 
     * @param string $pattern Modèle d'URL (ex: /articles/{id})
     * @param mixed $handler Contrôleur@action ou closure
     * @param string $module Nom du module HMVC
     * @param array $middlewares Middlewares à appliquer
     * @return self Instance du routeur pour le chaînage
     */
    public function patch(string $pattern, mixed $handler, string $module, array $middlewares = []): self
    {
        return $this->addRoute('PATCH', $pattern, $handler, $module, $middlewares);
    }
    
    /**
     * Définir une route répondant à toutes les méthodes HTTP
     * 
     * Cette méthode pratique permet de créer une route qui répondra
     * à n'importe quelle méthode HTTP (GET, POST, PUT, DELETE, etc.).
     * C'est particulièrement utile pour les routes génériques comme
     * les pages d'erreur ou les contrôleurs qui adaptent leur comportement
     * en fonction de la méthode utilisée.
     * 
     * Utilisations typiques :
     * - Pages d'erreur (404, 403, etc.)
     * - Contrôleurs REST qui gèrent toutes les méthodes
     * - Routes de diagnostic ou de maintenance
     * 
     * Exemples d'utilisation :
     * // Page d'erreur 404
     * $router->any('/not-found', 'ErrorController@notFound', 'Core');
     * 
     * // Contrôleur adaptant son comportement selon la méthode
     * $router->any('/api/{resource}', 'ApiController@handleRequest', 'API');
     * 
     * // Page de maintenance accessible par toutes les méthodes
     * $router->any('/maintenance', function() { return 'Site en maintenance'; }, 'Core');
     * 
     * @param string $pattern Modèle d'URL (ex: /not-found)
     * @param mixed $handler Contrôleur@action ou closure
     * @param string $module Nom du module HMVC
     * @param array $middlewares Middlewares à appliquer
     * @return self Instance du routeur pour le chaînage
     */
    public function any(string $pattern, mixed $handler, string $module, array $middlewares = []): self
    {
        return $this->addRoute('ANY', $pattern, $handler, $module, $middlewares);
    }
    
    /**
     * Créer un groupe de routes avec un préfixe commun
     * 
     * Cette méthode puissante permet d'organiser les routes liées en groupes
     * partageant un préfixe d'URL, des middlewares et un module communs.
     * Les routes du groupe sont définies dans une fonction de callback,
     * ce qui crée une syntaxe claire et lisible.
     * 
     * Avantages des groupes de routes :
     * - Organisation claire du code par section fonctionnelle
     * - Réduction de la duplication (préfixe et middlewares communs)
     * - Simplification de la gestion des routes associées
     * - Application automatique des middlewares à toutes les routes du groupe
     * 
     * Le fonctionnement interne utilise une approche subtile :
     * 1. Sauvegarde l'état actuel des routes
     * 2. Réinitialise les routes pour capturer uniquement celles du groupe
     * 3. Exécute le callback pour définir les routes du groupe
     * 4. Récupère les nouvelles routes définies
     * 5. Restaure l'état précédent
     * 6. Ajoute les routes du groupe modifiées avec le préfixe et les middlewares
     * 
     * Exemples d'utilisation :
     * // Groupe de routes pour le panneau d'administration
     * $router->group('/admin', ['AuthMiddleware', 'AdminMiddleware'], 'Admin', function($router) {
     *     $router->get('/dashboard', 'DashboardController@index', 'Admin');
     *     $router->get('/users', 'UserController@index', 'Admin');
     *     $router->get('/users/{id}', 'UserController@show', 'Admin');
     *     $router->post('/users', 'UserController@store', 'Admin');
     * });
     * 
     * // Groupe de routes pour une API
     * $router->group('/api/v1', ['ApiMiddleware'], 'API', function($router) {
     *     $router->get('/users', 'ApiController@listUsers', 'API');
     *     $router->get('/posts', 'ApiController@listPosts', 'API');
     * });
     * 
     * @param string $prefix Préfixe d'URL commun pour toutes les routes du groupe
     * @param array $middlewares Middlewares à appliquer à toutes les routes du groupe
     * @param string $module Nom du module HMVC pour les routes du groupe
     * @param callable $callback Fonction qui définit les routes du groupe
     * @return self Instance du routeur pour le chaînage
     */
    public function group(string $prefix, array $middlewares, string $module, callable $callback): self
    {
        // Sauvegarder l'état actuel des routes
        $currentRoutes = $this->routes;
        
        // Réinitialiser les routes pour ne capturer que celles définies dans le callback
        $this->routes = [];
        
        // Exécuter le callback pour définir les routes du groupe
        $callback($this);
        
        // Récupérer les nouvelles routes définies dans le callback
        $groupRoutes = $this->routes;
        
        // Restaurer l'état précédent des routes
        $this->routes = $currentRoutes;
        
        // Traiter les routes du groupe pour ajouter le préfixe et les middlewares
        foreach ($groupRoutes as $route) {
            // Construire le nouveau pattern avec le préfixe
            // Gérer le cas spécial où le pattern est '/' pour éviter les doubles /
            $pattern = $prefix . ($route['pattern'] !== '/' ? $route['pattern'] : '');
            
            // Fusionner les middlewares du groupe avec ceux spécifiques à la route
            $allMiddlewares = array_merge($middlewares, $route['middlewares']);
            
            // Ajouter la route modifiée au registre principal
            $this->addRoute(
                $route['method'],    // Méthode HTTP inchangée
                $pattern,            // Pattern avec préfixe ajouté
                $route['handler'],   // Handler inchangé
                $module,             // Module du groupe
                $allMiddlewares      // Middlewares fusionnés
            );
        }
        
        return $this;
    }
    
    /**
     * Définir automatiquement toutes les routes CRUD pour une ressource
     * 
     * Cette méthode de haut niveau simplifie considérablement la création
     * d'un ensemble complet de routes RESTful pour gérer une ressource.
     * En une seule ligne, elle crée les 7 routes standards nécessaires pour
     * un CRUD complet (Create, Read, Update, Delete) selon les conventions REST.
     * 
     * La méthode suit les conventions de nommage Rails/Laravel :
     * - index : Liste des ressources
     * - create : Formulaire de création
     * - store : Enregistrement d'une nouvelle ressource
     * - show : Affichage d'une ressource spécifique
     * - edit : Formulaire d'édition
     * - update : Mise à jour d'une ressource
     * - destroy : Suppression d'une ressource
     * 
     * Avantages :
     * - Réduction drastique du code nécessaire pour définir les routes
     * - Garantie de cohérence dans le nommage et la structure des URLs
     * - Respect des conventions RESTful
     * - Simplification de la maintenance
     * 
     * Exemples d'utilisation :
     * // Ressource 'articles' dans le module Blog
     * $router->resource('articles', 'ArticleController', 'Blog');
     * 
     * // Ressource 'products' avec middleware d'authentification
     * $router->resource('products', 'ProductController', 'Shop', ['AuthMiddleware']);
     * 
     * // Cette simple ligne crée 7 routes distincts :
     * // GET    /articles          -> ArticleController@index
     * // GET    /articles/create   -> ArticleController@create
     * // POST   /articles          -> ArticleController@store
     * // GET    /articles/{id}     -> ArticleController@show
     * // GET    /articles/{id}/edit -> ArticleController@edit
     * // PUT    /articles/{id}     -> ArticleController@update
     * // DELETE /articles/{id}     -> ArticleController@destroy
     * 
     * @param string $name Nom pluriel de la ressource (utilisé dans les URLs)
     * @param string $controller Nom du contrôleur qui gère la ressource
     * @param string $module Nom du module HMVC
     * @param array $middlewares Middlewares à appliquer à toutes les routes de la ressource
     * @return self Instance du routeur pour le chaînage
     */
    public function resource(string $name, string $controller, string $module, array $middlewares = []): self
    {
        // Route pour afficher la liste des ressources (index)
        // GET /resources
        $this->get("/$name", "$controller@index", $module, $middlewares);
        
        // Route pour afficher le formulaire de création (create)
        // GET /resources/create
        $this->get("/$name/create", "$controller@create", $module, $middlewares);
        
        // Route pour stocker une nouvelle ressource (store)
        // POST /resources
        $this->post("/$name", "$controller@store", $module, $middlewares);
        
        // Route pour afficher une ressource spécifique (show)
        // GET /resources/{id}
        $this->get("/$name/{id}", "$controller@show", $module, $middlewares);
        
        // Route pour afficher le formulaire d'édition d'une ressource (edit)
        // GET /resources/{id}/edit
        $this->get("/$name/{id}/edit", "$controller@edit", $module, $middlewares);
        
        // Route pour mettre à jour une ressource existante (update)
        // PUT /resources/{id}
        $this->put("/$name/{id}", "$controller@update", $module, $middlewares);
        
        // Route pour supprimer une ressource (destroy)
        // DELETE /resources/{id}
        $this->delete("/$name/{id}", "$controller@destroy", $module, $middlewares);
        
        return $this;
    }
}