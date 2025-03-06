<?php
/**
 * Classe Response - Gestionnaire complet des réponses HTTP
 * 
 * Cette classe essentielle encapsule tous les aspects d'une réponse HTTP à envoyer au client.
 * Elle agit comme le point final du cycle requête-réponse, transformant les résultats
 * du traitement de l'application en une réponse HTTP structurée et conforme aux standards.
 * 
 * La classe Response joue plusieurs rôles fondamentaux dans l'architecture HMVC :
 * - Elle standardise la structure de toutes les réponses de l'application
 * - Elle isole le reste du code des détails d'implémentation HTTP
 * - Elle offre une interface fluide pour construire différents types de réponses
 * - Elle garantit que les bonnes pratiques HTTP sont respectées
 * - Elle facilite les tests en permettant de capturer la réponse sans l'envoyer
 * 
 * Cette abstraction complète les fonctionnalités de la classe Request pour former
 * un cycle HTTP cohérent et découplé de la logique métier de l'application.
 */

namespace App\Core;

class Response
{
    /**
     * Code de statut HTTP de la réponse
     * 
     * Le code de statut HTTP est un élément crucial qui indique au client
     * le résultat du traitement de sa requête. Les standards HTTP définissent
     * plusieurs catégories de codes :
     * 
     * - 1xx : Informationnel (100 Continue, 101 Switching Protocols)
     * - 2xx : Succès (200 OK, 201 Created, 204 No Content)
     * - 3xx : Redirection (301 Moved Permanently, 302 Found, 304 Not Modified)
     * - 4xx : Erreur client (400 Bad Request, 403 Forbidden, 404 Not Found)
     * - 5xx : Erreur serveur (500 Internal Server Error, 503 Service Unavailable)
     * 
     * Le choix du code de statut approprié est important pour :
     * - Informer correctement le client du résultat de sa requête
     * - Assurer le bon fonctionnement des caches et proxies
     * - Permettre aux clients d'automatiser certains comportements
     * - Respecter les conventions REST dans les API
     * 
     * @var int
     */
    private int $statusCode = 200;
    
    /**
     * En-têtes HTTP à envoyer avec la réponse
     * 
     * Les en-têtes HTTP fournissent des métadonnées cruciales sur la réponse
     * et influencent le comportement du client (navigateur, agent API).
     * 
     * Les en-têtes courants incluent :
     * - Content-Type : Type MIME du contenu (text/html, application/json, etc.)
     * - Content-Length : Taille du corps en octets
     * - Cache-Control : Directives pour les mécanismes de cache
     * - X-Content-Type-Options : Options de sécurité
     * - Location : URL de redirection
     * - Set-Cookie : Définition des cookies
     * 
     * Ce tableau associatif stocke les en-têtes sous la forme 'nom' => 'valeur'
     * et sera utilisé pour générer les en-têtes HTTP réels lors de l'envoi.
     * 
     * @var array
     */
    private array $headers = [];
    
    /**
     * Corps de la réponse HTTP
     * 
     * Le corps contient les données principales à transmettre au client,
     * comme le contenu HTML d'une page, les données JSON d'une API,
     * ou tout autre type de contenu demandé.
     * 
     * Le corps de la réponse doit être cohérent avec l'en-tête Content-Type
     * et représente généralement le résultat du traitement de la requête par
     * l'application (vue rendue, données formatées, message d'erreur, etc.).
     * 
     * @var string
     */
    private string $body = '';
    
    /**
     * Cookies à envoyer avec la réponse
     * 
     * Les cookies sont de petites données stockées par le navigateur client
     * et automatiquement renvoyées lors des requêtes suivantes au même domaine.
     * Ils sont cruciaux pour maintenir l'état entre les requêtes HTTP.
     * 
     * Utilisations courantes :
     * - Identification de session
     * - Préférences utilisateur
     * - Suivi d'utilisation
     * - Authentification persistante
     * 
     * Ce tableau stocke les cookies à définir avec toutes leurs options,
     * qui seront envoyés au client via les en-têtes Set-Cookie.
     * 
     * @var array
     */
    private array $cookies = [];
    
    /**
     * Constructeur - Initialise une nouvelle réponse HTTP avec les paramètres par défaut
     * 
     * Cette méthode configure les valeurs initiales de la réponse, notamment :
     * - Le code de statut par défaut (200 OK)
     * - L'en-tête Content-Type par défaut (HTML en UTF-8)
     * 
     * Cette initialisation garantit que même les réponses les plus simples
     * contiennent les informations essentielles requises par les standards HTTP.
     * 
     * Exemple d'instanciation :
     * $response = new Response(); // Crée une réponse HTML par défaut
     */
    public function __construct()
    {
        // Définir les en-têtes par défaut
        // Content-Type est l'en-tête le plus fondamental, indiquant le format du contenu
        // text/html est le type par défaut pour les pages web standards
        // charset=UTF-8 garantit le support des caractères internationaux
        $this->headers = [
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
    }
    
    /**
     * Définir le code de statut HTTP de la réponse
     * 
     * Cette méthode permet de modifier le code de statut HTTP qui sera envoyé au client.
     * Elle implémente une interface fluide qui facilite le chaînage des méthodes.
     * 
     * Le code de statut HTTP est crucial pour indiquer le résultat du traitement :
     * - 200 : La requête a réussi (par défaut)
     * - 201 : Ressource créée avec succès
     * - 400 : Requête mal formée
     * - 401 : Authentification requise
     * - 403 : Accès interdit
     * - 404 : Ressource non trouvée
     * - 500 : Erreur serveur interne
     * 
     * Exemples d'utilisation :
     * // Indiquer une création réussie (API REST)
     * $response->setStatusCode(201);
     * 
     * // Signaler une page non trouvée
     * $response->setStatusCode(404);
     * 
     * // Utilisation avec chaînage de méthodes
     * $response->setStatusCode(200)->setBody($content)->send();
     * 
     * @param int $statusCode Code de statut HTTP (200, 404, 500, etc.)
     * @return self Retourne l'instance actuelle pour le chaînage des méthodes
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    
    /**
     * Définir ou modifier un en-tête HTTP dans la réponse
     * 
     * Cette méthode permet d'ajouter ou de remplacer un en-tête HTTP spécifique.
     * Les en-têtes HTTP sont essentiels pour configurer divers aspects du comportement
     * de la réponse, du type de contenu aux directives de cache.
     * 
     * Exemples d'utilisation :
     * // Définir le type de contenu
     * $response->setHeader('Content-Type', 'application/json');
     * 
     * // Ajouter des en-têtes de sécurité
     * $response->setHeader('X-XSS-Protection', '1; mode=block');
     * $response->setHeader('X-Frame-Options', 'DENY');
     * 
     * // Configurer le cache
     * $response->setHeader('Cache-Control', 'max-age=3600, public');
     * 
     * // Utilisation avec chaînage
     * $response->setStatusCode(200)
     *          ->setHeader('Content-Type', 'text/html')
     *          ->setBody($htmlContent);
     * 
     * @param string $name Nom de l'en-tête HTTP (ex: 'Content-Type', 'X-Custom-Header')
     * @param string $value Valeur de l'en-tête HTTP
     * @return self Retourne l'instance actuelle pour le chaînage des méthodes
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Définir le corps de la réponse HTTP
     * 
     * Cette méthode permet de définir le contenu principal qui sera envoyé au client.
     * Le corps de la réponse constitue le cœur du message HTTP et peut contenir
     * différents types de données selon le contexte :
     * - HTML pour les pages web standard
     * - JSON ou XML pour les API
     * - Texte brut pour les réponses simples
     * - Contenu binaire pour les téléchargements
     * 
     * Le corps doit être cohérent avec l'en-tête Content-Type défini pour la réponse.
     * 
     * Exemples d'utilisation :
     * // Réponse HTML simple
     * $response->setBody('<h1>Bienvenue sur notre site</h1>');
     * 
     * // Affichage du contenu généré par une vue
     * $htmlContent = $view->render('homepage', $data);
     * $response->setBody($htmlContent);
     * 
     * // Avec chaînage
     * $response->setStatusCode(200)
     *          ->setHeader('Content-Type', 'text/html')
     *          ->setBody($pageContent)
     *          ->send();
     * 
     * @param string $body Contenu à envoyer au client
     * @return self Retourne l'instance actuelle pour le chaînage des méthodes
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }
    
    /**
     * Ajouter un cookie à la réponse HTTP
     * 
     * Cette méthode permet de définir un cookie qui sera envoyé au navigateur client
     * et stocké pour une utilisation future. Les cookies sont essentiels pour maintenir
     * l'état entre les requêtes HTTP et permettent de stocker des informations persistantes
     * côté client.
     * 
     * Les paramètres offrent un contrôle précis sur le comportement du cookie :
     * - $expire : durée de vie du cookie (timestamp Unix)
     * - $path : chemin sur le serveur où le cookie est accessible
     * - $domain : domaine(s) pour lequel le cookie est valide
     * - $secure : limite l'envoi du cookie aux connexions HTTPS
     * - $httponly : empêche l'accès au cookie via JavaScript (protection XSS)
     * 
     * Exemples d'utilisation :
     * // Cookie de session simple (expire à la fermeture du navigateur)
     * $response->setCookie('user_id', $userId);
     * 
     * // Cookie persistant (valide pendant 30 jours)
     * $response->setCookie('preferences', json_encode($prefs), time() + 30*86400);
     * 
     * // Cookie sécurisé (HTTPS uniquement, inaccessible via JavaScript)
     * $response->setCookie('auth_token', $token, time() + 3600, '/', '', true, true);
     * 
     * // Cookie limité à un sous-domaine
     * $response->setCookie('analytics', 'enabled', time() + 86400, '/', 'stats.exemple.com');
     * 
     * @param string $name Nom du cookie
     * @param string $value Valeur du cookie
     * @param int $expire Timestamp d'expiration (0 = fin de session)
     * @param string $path Chemin sur le serveur où le cookie sera disponible
     * @param string $domain Domaine où le cookie sera disponible
     * @param bool $secure Cookie uniquement envoyé sur connexions HTTPS
     * @param bool $httponly Cookie inaccessible via JavaScript
     * @return self Retourne l'instance actuelle pour le chaînage des méthodes
     */
    public function setCookie(
        string $name, 
        string $value, 
        int $expire = 0, 
        string $path = '/', 
        string $domain = '', 
        bool $secure = false, 
        bool $httponly = false
    ): self {
        // Stocker toutes les informations du cookie
        // Ce tableau sera utilisé lors de l'envoi de la réponse
        $this->cookies[$name] = [
            'value' => $value,      // Valeur du cookie
            'expire' => $expire,    // Timestamp d'expiration
            'path' => $path,        // Chemin du cookie
            'domain' => $domain,    // Domaine du cookie
            'secure' => $secure,    // Restreint aux connexions HTTPS
            'httponly' => $httponly // Inaccessible via JavaScript
        ];
        
        return $this;
    }
    
    /**
     * Supprimer un cookie existant
     * 
     * Cette méthode demande au navigateur client de supprimer un cookie précédemment défini.
     * Techniquement, elle fonctionne en définissant un cookie avec la même identification
     * (nom, chemin, domaine) mais avec une date d'expiration dans le passé, ce qui force
     * le navigateur à le supprimer immédiatement.
     * 
     * Il est crucial de spécifier les mêmes path et domain que lors de la création du cookie,
     * car ces paramètres font partie de l'identification unique du cookie.
     * 
     * Exemples d'utilisation :
     * // Supprimer un cookie simple
     * $response->removeCookie('user_id');
     * 
     * // Supprimer un cookie avec un chemin spécifique
     * $response->removeCookie('analytics', '/stats');
     * 
     * // Supprimer un cookie de sous-domaine
     * $response->removeCookie('settings', '/', 'admin.exemple.com');
     * 
     * // Utilisé lors de la déconnexion
     * public function logout() {
     *     $response->removeCookie('auth_token');
     *     $response->redirect('/login');
     * }
     * 
     * @param string $name Nom du cookie à supprimer
     * @param string $path Chemin du cookie (doit correspondre à celui utilisé lors de la création)
     * @param string $domain Domaine du cookie (doit correspondre à celui utilisé lors de la création)
     * @return self Retourne l'instance actuelle pour le chaînage des méthodes
     */
    public function removeCookie(string $name, string $path = '/', string $domain = ''): self
    {
        // Définir un cookie expiré (time() - 3600 = 1 heure dans le passé)
        // Cela force le navigateur à supprimer immédiatement le cookie
        return $this->setCookie($name, '', time() - 3600, $path, $domain);
    }
    
    /**
     * Envoyer la réponse HTTP complète au client
     * 
     * Cette méthode finale assemble et envoie tous les composants de la réponse HTTP :
     * 1. Le code de statut HTTP
     * 2. Les en-têtes HTTP (Content-Type, etc.)
     * 3. Les cookies définis pendant le traitement
     * 4. Le corps de la réponse (contenu HTML, JSON, etc.)
     * 
     * Après l'envoi, la méthode termine l'exécution du script avec exit pour éviter
     * que du contenu supplémentaire ne soit accidentellement envoyé, ce qui pourrait
     * corrompre la réponse ou provoquer des erreurs.
     * 
     * Cette méthode représente le point final du cycle de la requête HTTP et devrait
     * généralement être appelée une seule fois à la fin du traitement.
     * 
     * Exemples d'utilisation :
     * // Envoi simple après configuration
     * $response->setBody($content)->send();
     * 
     * // Flux de travail complet
     * $response->setStatusCode(200)
     *          ->setHeader('Content-Type', 'text/html')
     *          ->setBody($viewRenderer->render('home', $data))
     *          ->setCookie('last_visit', date('Y-m-d'))
     *          ->send();
     * 
     * // Dans le contrôleur frontal
     * try {
     *     $content = $controller->$action(...$params);
     *     $response->setBody($content)->send();
     * } catch (Exception $e) {
     *     $response->setStatusCode(500)
     *              ->setBody('Une erreur est survenue')
     *              ->send();
     * }
     */
    public function send(): void
    {
        // Définir le code de statut HTTP
        // Cette fonction PHP native configure l'en-tête de statut HTTP
        http_response_code($this->statusCode);
        
        // Envoyer tous les en-têtes HTTP définis
        foreach ($this->headers as $name => $value) {
            // La fonction header() de PHP envoie un en-tête HTTP brut
            header("$name: $value");
        }
        
        // Envoyer tous les cookies définis
        foreach ($this->cookies as $name => $params) {
            // La fonction setcookie() de PHP définit un cookie
            setcookie(
                $name,               // Nom du cookie
                $params['value'],    // Valeur du cookie
                $params['expire'],   // Timestamp d'expiration
                $params['path'],     // Chemin du cookie
                $params['domain'],   // Domaine du cookie
                $params['secure'],   // Restreint aux connexions HTTPS
                $params['httponly']  // Inaccessible via JavaScript
            );
        }
        
        // Envoyer le corps de la réponse
        // Sortie directe vers le flux de sortie HTTP
        echo $this->body;
        
        // Terminer l'exécution du script
        // Cela empêche l'envoi accidentel de contenu supplémentaire
        // qui pourrait corrompre la réponse
        exit;
    }
    
    /**
     * Effectuer une redirection HTTP vers une autre URL
     * 
     * Cette méthode spécialisée configure une réponse de redirection
     * et l'envoie immédiatement. Elle utilise l'en-tête HTTP 'Location'
     * pour indiquer au navigateur de demander automatiquement une nouvelle URL.
     * 
     * Les codes de statut courants pour les redirections sont :
     * - 301 : Redirection permanente (la ressource a définitivement changé d'URL)
     * - 302 : Redirection temporaire (par défaut, la ressource est temporairement ailleurs)
     * - 303 : See Other (utilisé après POST pour rediriger vers un résultat)
     * - 307 : Redirection temporaire (garantit que la méthode HTTP reste inchangée)
     * 
     * Exemples d'utilisation :
     * // Redirection simple vers la page d'accueil
     * $response->redirect('/home');
     * 
     * // Redirection après traitement de formulaire (POST-redirect-GET pattern)
     * public function store() {
     *     // Traitement du formulaire...
     *     $response->redirect('/success', 303);
     * }
     * 
     * // Redirection permanente (pour SEO)
     * $response->redirect('/nouvelle-page', 301);
     * 
     * // Redirection avec paramètres
     * $response->redirect('/produits?category=5&sort=prix');
     * 
     * @param string $url URL de destination absolue ou relative
     * @param int $statusCode Code de statut HTTP pour la redirection (301, 302, 303, 307)
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        // Définir le code de statut approprié pour la redirection
        $this->setStatusCode($statusCode);
        
        // Définir l'en-tête Location qui indique la destination
        // Cet en-tête est interprété par le navigateur comme une instruction de redirection
        $this->setHeader('Location', $url);
        
        // Envoyer immédiatement la réponse
        // Le corps est vide car le navigateur ne l'affichera pas
        $this->send();
    }
    
    /**
     * Envoyer une réponse au format JSON
     * 
     * Cette méthode spécialisée prend des données PHP (tableau, objet, valeur simple),
     * les convertit automatiquement en format JSON, configure les en-têtes appropriés
     * et envoie la réponse complète au client.
     * 
     * Elle est particulièrement utile pour :
     * - Les API REST qui renvoient des données structurées
     * - Les requêtes AJAX qui attendent du JSON en retour
     * - Les services web et interfaces programmatiques
     * 
     * La méthode gère automatiquement :
     * - La conversion des données en JSON (via json_encode)
     * - La définition du bon Content-Type (application/json)
     * - Le code de statut HTTP approprié
     * 
     * Exemples d'utilisation :
     * // Réponse JSON simple avec succès (API)
     * $response->json(['success' => true, 'data' => $results]);
     * 
     * // Réponse d'erreur API avec code 400
     * $response->json(['error' => 'Paramètres invalides'], 400);
     * 
     * // Renvoyer des données structurées
     * $response->json([
     *     'user' => $user,
     *     'comments' => $comments,
     *     'total' => $commentsCount
     * ]);
     * 
     * // Utilisé dans un contrôleur d'API
     * public function getUsers() {
     *     $users = $userModel->all();
     *     $this->response->json(['users' => $users]);
     * }
     * 
     * @param mixed $data Données à convertir en JSON et à envoyer
     * @param int $statusCode Code de statut HTTP (200 par défaut)
     */
    public function json($data, int $statusCode = 200): void
    {
        // Définir le code de statut approprié
        $this->setStatusCode($statusCode);
        
        // Définir l'en-tête Content-Type pour JSON
        // Ceci indique au client que le contenu est au format JSON
        $this->setHeader('Content-Type', 'application/json');
        
        // Convertir les données PHP en JSON et définir comme corps
        $this->setBody(json_encode($data));
        
        // Envoyer immédiatement la réponse complète
        $this->send();
    }
    
    /**
     * Déclencher le téléchargement d'un fichier par le client
     * 
     * Cette méthode avancée permet d'envoyer un fichier au client en le configurant
     * comme téléchargement plutôt que comme contenu à afficher. Elle définit un ensemble
     * d'en-têtes HTTP spécifiques qui indiquent au navigateur qu'il doit traiter
     * le contenu comme un fichier téléchargeable.
     * 
     * Fonctionnalités :
     * - Vérification de l'existence du fichier
     * - Configuration des en-têtes pour forcer le téléchargement
     * - Possibilité de renommer le fichier côté client
     * - Optimisation pour les gros fichiers (via readfile)
     * 
     * Exemples d'utilisation :
     * // Téléchargement simple
     * $response->download('/path/to/document.pdf');
     * 
     * // Téléchargement avec renommage du fichier
     * $response->download('/storage/f8a7s6d.pdf', 'Rapport-Annuel-2023.pdf');
     * 
     * // Dans un contrôleur
     * public function downloadInvoice($id) {
     *     $invoice = $invoiceModel->find($id);
     *     if ($invoice) {
     *         $filePath = $invoice->getFilePath();
     *         $fileName = 'Facture-' . $invoice->getNumber() . '.pdf';
     *         $this->response->download($filePath, $fileName);
     *     } else {
     *         $this->response->setStatusCode(404)->setBody('Facture non trouvée')->send();
     *     }
     * }
     * 
     * @param string $filePath Chemin complet vers le fichier à envoyer
     * @param string|null $fileName Nom du fichier à afficher au client (optionnel)
     */
    public function download(string $filePath, string $fileName = null): void
    {
        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            // Si le fichier n'existe pas, renvoyer une erreur 404
            $this->setStatusCode(404);
            $this->setBody('Fichier non trouvé');
            $this->send();
        }
        
        // Utiliser le nom du fichier d'origine si non spécifié
        // basename() extrait le nom du fichier d'un chemin complet
        $fileName = $fileName ?? basename($filePath);
        
        // Définir les en-têtes spécifiques pour le téléchargement de fichier
        $this->setHeader('Content-Description', 'File Transfer');
        $this->setHeader('Content-Type', 'application/octet-stream');
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $this->setHeader('Expires', '0');
        $this->setHeader('Cache-Control', 'must-revalidate');
        $this->setHeader('Pragma', 'public');
        $this->setHeader('Content-Length', filesize($filePath));
        
        // Envoyer les en-têtes manuellement
        // (car nous allons utiliser readfile au lieu de echo)
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        // Lire le fichier et l'envoyer directement au client
        // readfile() est optimisé pour les gros fichiers
        readfile($filePath);
        
        // Terminer l'exécution du script
        exit;
    }
}