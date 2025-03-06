<?php
/**
 * Classe Request - Abstraction et gestion complète des requêtes HTTP
 * 
 * Cette classe fondamentale encapsule l'ensemble des données d'une requête HTTP entrante
 * et fournit une interface claire et sécurisée pour y accéder. Elle agit comme
 * une barrière de protection et d'abstraction entre les superglobales PHP brutes
 * ($_GET, $_POST, $_SERVER, etc.) et le reste de l'application.
 * 
 * La classe Request joue plusieurs rôles essentiels dans l'architecture HMVC :
 * - Centralise l'accès aux données de la requête HTTP, évitant l'utilisation directe des superglobales
 * - Normalise les URLs, en-têtes et paramètres pour une utilisation cohérente
 * - Facilite la détection du type de requête (AJAX, JSON, etc.) pour adapter les réponses
 * - Sécurise l'accès aux données entrantes en fournissant des valeurs par défaut
 * - Simplifie le traitement des requêtes REST en gérant automatiquement les méthodes HTTP
 * - Isole l'application des détails d'implémentation HTTP, facilitant les tests unitaires
 * 
 * Cette abstraction constitue l'un des piliers du découplage entre l'infrastructure
 * web et la logique métier de l'application.
 */

namespace App\Core;

class Request
{
    /**
     * Données de la requête GET
     * 
     * Stocke les paramètres transmis dans l'URL après le point d'interrogation
     * (ex: /articles?id=5&category=news).
     * 
     * Ces données sont généralement utilisées pour :
     * - Les paramètres de filtrage (tris, recherches, pagination)
     * - Les identifiants de ressources dans les architectures REST
     * - La persistance d'état entre les pages (paramètres de session, etc.)
     * 
     * Cette propriété encapsule $_GET pour un accès contrôlé et sécurisé.
     * 
     * @var array
     */
    private array $get;
    
    /**
     * Données de la requête POST
     * 
     * Contient les données soumises par un formulaire HTML ou par une
     * requête HTTP explicitement envoyée avec la méthode POST.
     * 
     * Ces données sont généralement utilisées pour :
     * - Les soumissions de formulaires (création ou modification de données)
     * - Les requêtes AJAX qui modifient des données sur le serveur
     * - L'upload de fichiers (en conjonction avec $_FILES)
     * 
     * Cette propriété encapsule $_POST pour un accès contrôlé et sécurisé.
     * 
     * @var array
     */
    private array $post;
    
    /**
     * Paramètres combinés de la requête
     * 
     * Fusion intelligente des données provenant de diverses sources :
     * - Paramètres GET de l'URL
     * - Données POST d'un formulaire
     * - Corps JSON décodé (pour les API REST)
     * 
     * Cette propriété offre un point d'accès unique à toutes les données
     * de la requête, quelle que soit leur origine, simplifiant ainsi
     * le code des contrôleurs qui n'ont pas besoin de vérifier la source.
     * 
     * @var array
     */
    private array $params;
    
    /**
     * En-têtes HTTP de la requête
     * 
     * Stocke l'ensemble des en-têtes HTTP envoyés par le client,
     * normalisés pour une utilisation cohérente (format Pascal-Case).
     * 
     * Les en-têtes HTTP contiennent des métadonnées cruciales comme :
     * - Le type de contenu (Content-Type)
     * - Les informations d'authentification (Authorization)
     * - Les préférences de mise en cache (Cache-Control)
     * - Les identifiants de session (Cookie)
     * - Les informations sur le client (User-Agent)
     * 
     * @var array
     */
    private array $headers;
    
    /**
     * Cookies de la requête
     * 
     * Stocke les cookies envoyés par le client avec la requête HTTP.
     * Ces données persistent côté client et sont souvent utilisées pour :
     * - L'identification de session
     * - Les préférences utilisateur
     * - Le suivi d'état entre les requêtes
     * 
     * Cette propriété encapsule $_COOKIE pour un accès contrôlé et sécurisé.
     * 
     * @var array
     */
    private array $cookies;
    
    /**
     * Fichiers téléchargés
     * 
     * Contient les informations sur les fichiers uploadés via des formulaires.
     * Pour chaque fichier, on dispose de métadonnées essentielles :
     * - Nom d'origine (name)
     * - Type MIME (type)
     * - Emplacement temporaire (tmp_name)
     * - Code d'erreur éventuel (error)
     * - Taille en octets (size)
     * 
     * Cette propriété encapsule $_FILES pour un accès contrôlé et sécurisé.
     * 
     * @var array
     */
    private array $files;
    
    /**
     * URI de la requête
     * 
     * L'URI (Uniform Resource Identifier) normalisée représente le chemin demandé
     * dans l'URL, sans les paramètres de requête ni le nom de domaine.
     * 
     * Exemples :
     * - Pour http://exemple.com/articles/5?commentaires=true → /articles/5
     * - Pour http://exemple.com/utilisateurs/profil → /utilisateurs/profil
     * 
     * Cette URI est utilisée par le routeur pour déterminer quel contrôleur
     * et quelle action doivent traiter la requête.
     * 
     * @var string
     */
    private string $uri;
    
    /**
     * Méthode HTTP de la requête
     * 
     * Indique la méthode HTTP utilisée pour cette requête. Les méthodes
     * standard en REST sont :
     * - GET : Récupération de données (lecture seule)
     * - POST : Création de nouvelles ressources
     * - PUT : Mise à jour complète d'une ressource existante
     * - PATCH : Mise à jour partielle d'une ressource
     * - DELETE : Suppression d'une ressource
     * 
     * Cette classe gère également les méthodes simulées via le champ _method
     * pour les navigateurs qui ne supportent nativement que GET et POST.
     * 
     * @var string
     */
    private string $method;
    
    /**
     * Constructeur - Initialise l'objet Request à partir des données HTTP
     * 
     * Ce constructeur capture l'état complet de la requête HTTP entrante en :
     * 1. Extrayant les données des superglobales PHP ($_GET, $_POST, etc.)
     * 2. Normalisant l'URI pour le routage
     * 3. Déterminant la méthode HTTP réelle ou simulée
     * 4. Extrayant et normalisant les en-têtes HTTP
     * 5. Fusionnant les paramètres de différentes sources
     * 6. Traitant automatiquement les requêtes au format JSON
     * 
     * Une fois initialisé, cet objet devient le point d'accès unique et cohérent
     * à toutes les données de la requête pour le reste de l'application.
     */
    public function __construct()
    {
        // Copier les données des variables superglobales
        // Cette étape est cruciale pour l'encapsulation et la testabilité
        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
        
        // Analyser et normaliser l'URI pour le routage
        // L'URI est essentielle pour déterminer quel contrôleur doit traiter la requête
        $this->uri = $this->parseUri();
        
        // Déterminer la méthode HTTP (GET, POST, PUT, DELETE, etc.)
        // L'opérateur de fusion null (??) fournit une valeur par défaut si $_SERVER['REQUEST_METHOD'] n'existe pas
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Support pour les méthodes HTTP simulées
        // Permet d'utiliser PUT, PATCH, DELETE avec des formulaires HTML standard
        // qui ne supportent nativement que GET et POST
        if ($this->method === 'POST' && isset($this->post['_method'])) {
            // Convertir en majuscules pour standardiser
            $this->method = strtoupper($this->post['_method']);
        }
        
        // Extraire et normaliser tous les en-têtes HTTP
        // Les en-têtes contiennent des métadonnées importantes sur la requête
        $this->headers = $this->parseHeaders();
        
        // Fusionner GET et POST pour un accès unifié aux paramètres
        // Cela simplifie l'accès aux données dans les contrôleurs
        $this->params = array_merge($this->get, $this->post);
        
        // Traitement spécial pour les requêtes au format JSON
        // Particulièrement important pour les API REST
        if ($this->isJson()) {
            // Lire et décoder le corps JSON de la requête
            $json = json_decode(file_get_contents('php://input'), true);
            if ($json) {
                // Ajouter les données JSON aux paramètres combinés
                $this->params = array_merge($this->params, $json);
            }
        }
    }
    
    /**
     * Analyser l'URI de la requête
     * 
     * Cette méthode extrait et normalise l'URI à partir des variables serveur
     * en effectuant plusieurs opérations essentielles :
     * 1. Récupération de l'URI brute depuis $_SERVER
     * 2. Suppression des paramètres de requête (tout ce qui suit le ?)
     * 3. Gestion du chemin de base pour les applications non installées à la racine
     * 4. Normalisation de l'URI (ajout du / initial, suppression des / finaux)
     * 
     * Cette normalisation est cruciale pour que le routage fonctionne correctement
     * quelles que soient les variations dans la façon dont l'URI est formée.
     * 
     * @return string L'URI normalisée prête à être utilisée par le routeur
     */
    private function parseUri(): string
    {
        // Récupérer l'URI brute ou utiliser '/' par défaut si non disponible
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Supprimer les paramètres de requête (tout ce qui suit le ?)
        // Cette étape est nécessaire car l'URI utilisée pour le routage
        // ne doit pas inclure les paramètres GET
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Supprimer le chemin de base si l'application n'est pas installée à la racine
        // Par exemple, si l'application est dans /monapp/ sur le serveur,
        // nous voulons que /monapp/utilisateurs soit traité comme /utilisateurs
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && $basePath !== '\\' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Normaliser l'URI pour garantir un format cohérent :
        // - Ajouter un / au début si absent
        // - Supprimer les / à la fin
        // - Gérer correctement le cas spécial de la page d'accueil ('/')
        $uri = '/' . trim($uri, '/');
        
        return $uri;
    }
    
    /**
     * Extraire et normaliser les en-têtes HTTP de la requête
     * 
     * Cette méthode récupère tous les en-têtes HTTP à partir des variables serveur
     * et les transforme en un format standardisé et facile à utiliser.
     * 
     * Le processus de normalisation comprend :
     * 1. Extraction des variables $_SERVER commençant par 'HTTP_'
     * 2. Transformation des noms (ex: HTTP_USER_AGENT → User-Agent)
     * 3. Gestion des cas spéciaux (Content-Type, Content-Length)
     * 4. Normalisation au format Pascal-Case pour cohérence
     * 
     * Cette standardisation permet d'accéder aux en-têtes de manière cohérente,
     * indépendamment des variations dans la façon dont le serveur les représente.
     * 
     * @return array Tableau associatif des en-têtes HTTP normalisés
     */
    private function parseHeaders(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            // Les en-têtes HTTP sont généralement stockés avec le préfixe HTTP_
            // Par exemple : HTTP_ACCEPT, HTTP_USER_AGENT, HTTP_AUTHORIZATION
            if (strpos($key, 'HTTP_') === 0) {
                // Transformation du format :
                // 1. Supprimer le préfixe HTTP_
                // 2. Remplacer les underscores par des espaces
                // 3. Convertir en minuscules puis capitaliser chaque mot
                // 4. Remplacer les espaces par des tirets
                // Exemple : HTTP_USER_AGENT → User-Agent
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$headerName] = $value;
            } 
            // Cas spéciaux : Content-Type et Content-Length n'ont pas le préfixe HTTP_
            // mais sont néanmoins des en-têtes HTTP importants
            elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                $headers[$headerName] = $value;
            }
        }
        
        return $headers;
    }
    
    /**
     * Vérifier si la requête est une requête AJAX
     * 
     * Cette méthode détecte si la requête a été effectuée de manière asynchrone
     * via JavaScript (AJAX) en vérifiant la présence et la valeur de l'en-tête
     * X-Requested-With, qui est automatiquement ajouté par la plupart des
     * bibliothèques JavaScript comme jQuery, Axios, ou Fetch avec les bons paramètres.
     * 
     * La détection des requêtes AJAX permet d'adapter la réponse en conséquence :
     * - Renvoyer du JSON au lieu d'HTML complet
     * - Omettre les en-têtes, pieds de page et navigation
     * - Personnaliser le format et le contenu de la réponse
     * 
     * Exemple d'utilisation dans un contrôleur :
     * if ($request->isAjax()) {
     *     // Renvoyer seulement les données au format JSON
     *     return $this->json($data);
     * } else {
     *     // Renvoyer une page HTML complète
     *     return $this->view('template', $data);
     * }
     * 
     * @return bool True si c'est une requête AJAX, false sinon
     */
    public function isAjax(): bool
    {
        return isset($this->headers['X-Requested-With']) && 
               $this->headers['X-Requested-With'] === 'XMLHttpRequest';
    }
    
    /**
     * Vérifier si la requête contient des données au format JSON
     * 
     * Cette méthode détecte si le corps de la requête est au format JSON
     * en examinant l'en-tête Content-Type. Les clients qui envoient des données
     * JSON définissent généralement cet en-tête à 'application/json'.
     * 
     * Détecter les requêtes JSON est crucial pour les API REST modernes qui
     * utilisent ce format pour échanger des données structurées.
     * 
     * Lorsqu'une requête JSON est détectée, le constructeur analyse automatiquement
     * le corps de la requête et intègre les données JSON aux paramètres combinés.
     * 
     * Exemple d'utilisation dans une API :
     * if ($request->isJson()) {
     *     $data = $request->getJson();
     *     // Traiter les données JSON...
     * } else {
     *     // Gérer le cas d'un format non supporté
     *     return $this->response->setStatusCode(415); // Unsupported Media Type
     * }
     * 
     * @return bool True si la requête contient des données JSON, false sinon
     */
    public function isJson(): bool
    {
        return isset($this->headers['Content-Type']) && 
               strpos($this->headers['Content-Type'], 'application/json') !== false;
    }
    
    /**
     * Récupérer l'URI normalisée de la requête
     * 
     * Cette méthode retourne l'URI (Uniform Resource Identifier) de la requête
     * après normalisation, sans les paramètres de requête. L'URI représente le
     * chemin demandé et est utilisée par le routeur pour déterminer quel
     * contrôleur et quelle action doivent traiter la requête.
     * 
     * Exemple d'utilisation dans le système de routage :
     * $uri = $request->getUri();
     * $route = $router->match($uri, $request->getMethod());
     * 
     * @return string L'URI normalisée (ex: /articles/5, /utilisateurs/profil)
     */
    public function getUri(): string
    {
        return $this->uri;
    }
    
    /**
     * Récupérer la méthode HTTP de la requête
     * 
     * Cette méthode retourne la méthode HTTP utilisée pour la requête actuelle,
     * qui peut être soit la méthode réelle (GET, POST) soit une méthode simulée
     * via le champ _method (PUT, DELETE, PATCH).
     * 
     * Connaître la méthode HTTP est essentiel pour les API RESTful où différentes
     * méthodes correspondent à différentes opérations sur les ressources :
     * - GET : Lecture (récupération de données)
     * - POST : Création (ajout de nouvelles ressources)
     * - PUT : Mise à jour complète
     * - PATCH : Mise à jour partielle
     * - DELETE : Suppression
     * 
     * Exemple d'utilisation dans le routeur :
     * public function match($uri, $method) {
     *     foreach ($this->routes[$method] as $route => $handler) {
     *         // Vérifier si l'URI correspond à cette route...
     *     }
     * }
     * 
     * @return string La méthode HTTP en majuscules (GET, POST, PUT, DELETE, etc.)
     */
    public function getMethod(): string
    {
        return $this->method;
    }
    
    /**
     * Récupérer un paramètre spécifique de la requête
     * 
     * Cette méthode flexible recherche un paramètre dans l'ensemble des données
     * combinées de la requête (GET, POST, JSON) et retourne soit sa valeur,
     * soit une valeur par défaut si le paramètre n'existe pas.
     * 
     * L'avantage principal est que le code appelant n'a pas besoin de se soucier
     * de la source du paramètre (URL, formulaire, corps JSON), simplifiant ainsi
     * l'accès aux données.
     * 
     * Exemples d'utilisation dans un contrôleur :
     * // Récupérer un ID avec 0 comme valeur par défaut
     * $id = $request->getParam('id', 0);
     * 
     * // Récupérer un paramètre optionnel
     * $page = $request->getParam('page', 1);
     * 
     * // Récupérer une valeur booléenne
     * $showDetails = $request->getParam('details', false);
     * 
     * @param string $name Le nom du paramètre à récupérer
     * @param mixed $default La valeur à retourner si le paramètre n'existe pas
     * @return mixed La valeur du paramètre ou la valeur par défaut
     */
    public function getParam(string $name, $default = null)
    {
        // L'opérateur de fusion null (??) retourne le premier opérande s'il existe et n'est pas null,
        // ou le second opérande sinon
        return $this->params[$name] ?? $default;
    }
    
    /**
     * Récupérer tous les paramètres de la requête
     * 
     * Cette méthode retourne l'ensemble complet des paramètres de la requête,
     * quelle que soit leur source (GET, POST, JSON). Elle permet d'accéder
     * à toutes les données d'un seul coup, ce qui est utile pour :
     * - Le débogage
     * - La validation en masse
     * - L'alimentation de modèles avec plusieurs champs
     * 
     * Exemple d'utilisation pour créer un nouvel enregistrement :
     * $userData = $request->getParams();
     * $user = new User();
     * $user->fill($userData);
     * $user->save();
     * 
     * @return array Tableau associatif de tous les paramètres combinés
     */
    public function getParams(): array
    {
        return $this->params;
    }
    
    /**
     * Récupérer un paramètre spécifique de la requête GET
     * 
     * Contrairement à getParam() qui recherche dans toutes les sources,
     * cette méthode se limite strictement aux paramètres d'URL (query string).
     * Elle est utile quand on veut explicitement un paramètre de l'URL,
     * même si une valeur portant le même nom existe dans POST ou JSON.
     * 
     * Exemples d'utilisation :
     * // Récupérer un paramètre de pagination
     * $page = $request->get('page', 1);
     * 
     * // Récupérer un paramètre de filtrage
     * $category = $request->get('category');
     * 
     * // Récupérer un paramètre de tri
     * $sortBy = $request->get('sort', 'date');
     * 
     * @param string $name Le nom du paramètre GET à récupérer
     * @param mixed $default La valeur à retourner si le paramètre n'existe pas
     * @return mixed La valeur du paramètre ou la valeur par défaut
     */
    public function get(string $name, $default = null)
    {
        return $this->get[$name] ?? $default;
    }
    
    /**
     * Récupérer un paramètre spécifique de la requête POST
     * 
     * Cette méthode se concentre uniquement sur les données envoyées par formulaire
     * ou en tant que corps de requête POST. Elle est particulièrement utile lorsqu'on
     * veut explicitement une valeur POST, même si un paramètre GET porte le même nom.
     * 
     * Exemples d'utilisation dans un traitement de formulaire :
     * // Récupérer des champs de formulaire
     * $username = $request->post('username');
     * $password = $request->post('password');
     * 
     * // Vérifier si un champ a été soumis, avec valeur par défaut
     * $rememberMe = $request->post('remember_me', false);
     * 
     * @param string $name Le nom du paramètre POST à récupérer
     * @param mixed $default La valeur à retourner si le paramètre n'existe pas
     * @return mixed La valeur du paramètre ou la valeur par défaut
     */
    public function post(string $name, $default = null)
    {
        return $this->post[$name] ?? $default;
    }
    
    /**
     * Récupérer un en-tête HTTP spécifique
     * 
     * Cette méthode permet d'accéder aux en-têtes HTTP de la requête de manière
     * normalisée, en gérant automatiquement les variations de casse et de format.
     * Les en-têtes HTTP contiennent des métadonnées cruciales sur la requête
     * qui influencent souvent la façon dont elle doit être traitée.
     * 
     * La méthode normalise automatiquement le nom de l'en-tête au format Pascal-Case
     * (ex: 'content-type' devient 'Content-Type'), assurant ainsi une utilisation
     * cohérente indépendamment de la façon dont l'en-tête est référencé.
     * 
     * Exemples d'utilisation :
     * // Vérifier le type de contenu
     * $contentType = $request->getHeader('content-type', 'text/html');
     * 
     * // Récupérer un token d'authentification
     * $token = $request->getHeader('Authorization');
     * 
     * // Vérifier l'origine de la requête
     * $origin = $request->getHeader('Origin');
     * 
     * @param string $name Le nom de l'en-tête (insensible à la casse)
     * @param mixed $default La valeur par défaut si l'en-tête n'existe pas
     * @return mixed La valeur de l'en-tête ou la valeur par défaut
     */
    public function getHeader(string $name, $default = null)
    {
        // Normaliser le nom de l'en-tête au format Pascal-Case
        // Exemple : 'content-type' → 'Content-Type'
        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $name))));
        return $this->headers[$name] ?? $default;
    }
    
    /**
     * Récupérer tous les en-têtes HTTP de la requête
     * 
     * Cette méthode retourne l'ensemble complet des en-têtes HTTP envoyés avec
     * la requête, déjà normalisés au format Pascal-Case. Elle est utile pour
     * l'inspection complète des métadonnées de la requête, le débogage, ou la
     * prise de décisions basées sur plusieurs en-têtes.
     * 
     * Exemples d'utilisation :
     * // Afficher tous les en-têtes pour le débogage
     * $headers = $request->getHeaders();
     * foreach ($headers as $name => $value) {
     *     echo "$name: $value<br>";
     * }
     * 
     * // Vérifier la présence d'en-têtes spécifiques
     * $headers = $request->getHeaders();
     * if (isset($headers['X-Csrf-Token']) && isset($headers['X-Requested-With'])) {
     *     // Traitement sécurisé...
     * }
     * 
     * @return array Tableau associatif de tous les en-têtes HTTP normalisés
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    /**
     * Récupérer la valeur d'un cookie
     * 
     * Cette méthode permet d'accéder de manière sécurisée aux cookies envoyés
     * par le client dans la requête HTTP. Les cookies sont souvent utilisés pour
     * stocker des informations persistantes côté client comme les identifiants
     * de session, les préférences utilisateur, ou des tokens d'authentification.
     * 
     * Exemples d'utilisation :
     * // Récupérer un identifiant de session
     * $sessionId = $request->getCookie('PHPSESSID');
     * 
     * // Vérifier les préférences utilisateur
     * $theme = $request->getCookie('theme', 'light');
     * 
     * // Récupérer un token de connexion automatique
     * $rememberToken = $request->getCookie('remember_me');
     * 
     * @param string $name Le nom du cookie à récupérer
     * @param mixed $default La valeur par défaut si le cookie n'existe pas
     * @return mixed La valeur du cookie ou la valeur par défaut
     */
    public function getCookie(string $name, $default = null)
    {
        return $this->cookies[$name] ?? $default;
    }
    
    /**
     * Récupérer les informations sur un fichier téléchargé
     * 
     * Cette méthode permet d'accéder aux données relatives à un fichier
     * téléchargé via un formulaire HTML avec l'attribut enctype="multipart/form-data".
     * Elle retourne un tableau contenant les métadonnées complètes du fichier.
     * 
     * Pour chaque fichier, on récupère généralement :
     * - name : Le nom original du fichier sur l'ordinateur du client
     * - type : Le type MIME du fichier (ex: image/jpeg, application/pdf)
     * - tmp_name : L'emplacement temporaire où le fichier a été stocké
     * - error : Un code d'erreur (0 signifie pas d'erreur)
     * - size : La taille du fichier en octets
     * 
     * Exemples d'utilisation :
     * // Récupérer les informations sur un fichier téléchargé
     * $avatar = $request->getFile('avatar');
     * 
     * // Vérifier s'il n'y a pas d'erreur et déplacer le fichier
     * if ($avatar && $avatar['error'] === 0) {
     *     $newPath = 'uploads/' . $userId . '/' . $avatar['name'];
     *     move_uploaded_file($avatar['tmp_name'], $newPath);
     * }
     * 
     * @param string $name Le nom du champ de formulaire pour le fichier
     * @return array|null Les informations sur le fichier ou null si aucun fichier
     */
    public function getFile(string $name)
    {
        return $this->files[$name] ?? null;
    }
    
    /**
     * Récupérer le corps brut de la requête HTTP
     * 
     * Cette méthode accède directement au flux d'entrée de la requête HTTP
     * (php://input) pour récupérer les données brutes envoyées dans le corps
     * de la requête. Contrairement aux méthodes get() et post() qui traitent
     * des données déjà analysées, getBody() retourne le contenu exact tel
     * qu'il a été envoyé.
     * 
     * Cette fonctionnalité est particulièrement utile pour :
     * - Traiter des formats de données personnalisés
     * - Accéder au corps brut des requêtes PUT ou PATCH
     * - Vérifier les signatures ou hachages basés sur le contenu exact
     * - Traiter des webhooks ou des requêtes API avec des formats spéciaux
     * 
     * Exemples d'utilisation :
     * // Récupérer et traiter manuellement un contenu XML
     * $xmlContent = $request->getBody();
     * $xml = simplexml_load_string($xmlContent);
     * 
     * // Traiter un webhook avec une signature
     * $body = $request->getBody();
     * $signature = hash_hmac('sha256', $body, $secretKey);
     * if ($signature === $request->getHeader('X-Signature')) {
     *     // La signature est valide, traiter le contenu...
     * }
     * 
     * @return string Le contenu brut du corps de la requête
     */
    public function getBody(): string
    {
        // php://input est un flux en lecture seule qui permet d'accéder
        // au corps brut de la requête (raw POST data)
        return file_get_contents('php://input');
    }
    
    /**
     * Récupérer et décoder automatiquement les données JSON du corps de la requête
     * 
     * Cette méthode combine l'accès au corps brut de la requête avec le décodage
     * JSON, simplifiant ainsi le traitement des requêtes d'API au format JSON.
     * Elle vérifie également si le décodage a réussi, retournant null en cas d'erreur.
     * 
     * L'avantage par rapport à l'utilisation des paramètres combinés est que cette
     * méthode garantit l'accès aux données JSON originales non fusionnées avec
     * d'autres sources, ce qui peut être important dans certains contextes d'API.
     * 
     * Exemples d'utilisation dans une API REST :
     * // Récupérer les données JSON d'une requête API
     * $data = $request->getJson();
     * 
     * // Traiter les données avec validation
     * if ($data !== null) {
     *     // Le décodage JSON a réussi
     *     $userId = $data['user_id'] ?? 0;
     *     $action = $data['action'] ?? 'view';
     *     
     *     // Traiter la demande...
     * } else {
     *     // JSON invalide, renvoyer une erreur
     *     $response->setStatusCode(400);
     *     $response->json(['error' => 'Invalid JSON format']);
     * }
     * 
     * @return array|null Les données JSON décodées ou null si le JSON est invalide
     */
    public function getJson()
    {
        // Récupérer le corps brut de la requête
        $body = $this->getBody();
        
        // Décoder le JSON en tableau associatif (true comme second paramètre)
        $data = json_decode($body, true);
        
        // Vérifier si le décodage JSON a réussi
        // json_last_error() retourne JSON_ERROR_NONE (0) si tout s'est bien passé
        return (json_last_error() === JSON_ERROR_NONE) ? $data : null;
    }
}