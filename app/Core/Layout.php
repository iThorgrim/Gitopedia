<?php
/**
 * Classe Layout - Gestionnaire de mise en page
 * 
 * Cette classe est responsable de :
 * - Gérer la structure globale des pages HTML
 * - Intégrer les éléments communs (header, footer, navbar)
 * - Injecter le contenu spécifique à chaque page
 * - Transmettre des variables du contrôleur vers les vues
 * 
 * Elle joue un rôle essentiel dans l'architecture HMVC en permettant
 * une séparation claire entre le contenu et la présentation.
 */

namespace App\Core;

class Layout
{
    /**
     * Instance de l'application
     * 
     * Référence à l'instance principale de l'application, permettant
     * d'accéder à ses méthodes et propriétés (routeur, base de données, etc.).
     * 
     * @var Application
     */
    protected Application $app;
    
    /**
     * Contenu de la page
     * 
     * Stocke le contenu HTML spécifique à la page actuelle.
     * Ce contenu sera injecté dans le layout global.
     * 
     * @var string
     */
    protected string $content = '';
    
    /**
     * Titre de la page
     * 
     * Définit le titre qui apparaîtra dans la balise <title>
     * et potentiellement dans d'autres parties du template.
     * 
     * @var string
     */
    protected string $title = 'Gitopedia';
    
    /**
     * Variables supplémentaires pour la vue
     * 
     * Stocke des données dynamiques qui seront transmises
     * au template pour être affichées dans la vue.
     * 
     * @var array
     */
    protected array $variables = [];
    
    /**
     * Constructeur de la classe Layout
     * 
     * Initialise une nouvelle instance du gestionnaire de mise en page
     * en lui associant l'instance de l'application.
     * 
     * Exemple d'utilisation :
     * $layout = new Layout(Application::getInstance());
     * 
     * @param Application $app L'instance de l'application
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    /**
     * Définit le contenu principal de la page
     * 
     * Cette méthode permet de définir le contenu HTML qui sera
     * injecté dans la section principale du layout.
     * 
     * Ce contenu est généralement le résultat du rendu d'une vue
     * spécifique à une action de contrôleur.
     * 
     * Exemple d'utilisation :
     * $layout->setContent($articleView);
     * 
     * @param string $content Le contenu HTML à injecter dans le layout
     * @return self Retourne l'instance du layout pour permettre le chaînage
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * Définit le titre de la page
     * 
     * Le titre est utilisé dans la balise <title> du document HTML
     * et peut également être affiché dans d'autres parties du layout.
     * 
     * Exemple d'utilisation :
     * $layout->setTitle('Accueil - Mon Site Web');
     * 
     * @param string $title Le titre de la page
     * @return self Retourne l'instance du layout pour permettre le chaînage
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }
    
    /**
     * Ajoute ou modifie une variable pour la vue
     * 
     * Cette méthode permet de transmettre des données dynamiques
     * au template, qui pourront être utilisées lors du rendu.
     * 
     * Les variables définies ici sont disponibles dans le layout
     * via la fonction extract() qui les transforme en variables PHP.
     * 
     * Exemple d'utilisation :
     * $layout->setVariable('user', $currentUser);
     * 
     * @param string $name Le nom de la variable
     * @param mixed $value La valeur à associer à la variable
     * @return self Retourne l'instance du layout pour permettre le chaînage
     */
    public function setVariable(string $name, $value): self
    {
        $this->variables[$name] = $value;
        return $this;
    }
    
    /**
     * Définit plusieurs variables à la fois
     * 
     * Cette méthode permet d'ajouter ou modifier plusieurs
     * variables en une seule opération, en fusionnant le tableau
     * passé en paramètre avec le tableau existant.
     * 
     * Exemple d'utilisation :
     * $layout->setVariables([
     *     'user' => $currentUser,
     *     'isAdmin' => $hasAdminRights,
     *     'notifications' => $notificationsList
     * ]);
     * 
     * @param array $variables Tableau associatif de variables à ajouter
     * @return self Retourne l'instance du layout pour permettre le chaînage
     */
    public function setVariables(array $variables): self
    {
        $this->variables = array_merge($this->variables, $variables);
        return $this;
    }
    
    /**
     * Génère le rendu complet de la page
     * 
     * Cette méthode est le point culminant du processus de rendu :
     * 1. Elle ajoute le titre et le contenu aux variables disponibles
     * 2. Elle localise le fichier de template de layout
     * 3. Elle extrait les variables pour qu'elles soient accessibles dans le template
     * 4. Elle inclut le fichier de template et capture sa sortie
     * 5. Elle retourne le HTML complet de la page
     * 
     * Le layout complet intègre généralement :
     * - L'en-tête HTML (doctype, balises meta, CSS, etc.)
     * - Le menu de navigation (navbar)
     * - L'en-tête de page (header)
     * - Le contenu principal (injecté via $content)
     * - Le pied de page (footer)
     * - Les scripts JavaScript
     * 
     * Exemple d'utilisation :
     * $html = $layout->setContent($pageContent)->setTitle('Accueil')->render();
     * $response->setBody($html);
     * 
     * @throws \Exception Si le fichier de layout n'est pas trouvé
     * @return string Le HTML complet de la page, prêt à être envoyé au navigateur
     */
    public function render(): string
    {
        // Ajouter le titre aux variables
        $this->variables['title'] = $this->title;
        
        // Ajouter le contenu aux variables
        $this->variables['content'] = $this->content;
        
        // Déterminer le chemin du fichier de layout
        // Ce fichier contient la structure HTML commune à toutes les pages
        $layoutPath = $this->app->getSharedViewsPath() . '/layout.php';
        
        // Vérifier si le fichier de layout existe
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout '$layoutPath' introuvable");
        }
        
        // Extraire les variables pour qu'elles soient disponibles dans le layout
        // Cette fonction transforme les clés du tableau en variables PHP
        // Exemple : $variables['user'] devient $user
        extract($this->variables);
        
        // Capturer la sortie générée par le layout
        // Plutôt que d'afficher directement le HTML, on le capture dans une variable
        ob_start();
        include $layoutPath;
        return ob_get_clean();
    }
}