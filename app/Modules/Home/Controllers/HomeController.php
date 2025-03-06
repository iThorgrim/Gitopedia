<?php
/**
 * HomeController - Contrôleur principal pour les pages statiques du site
 * 
 * Ce contrôleur gère les pages fondamentales et statiques de l'application
 * comme la page d'accueil et la page "À propos". Il représente un exemple
 * simple mais essentiel de l'architecture HMVC en action.
 * 
 * Dans la structure HMVC (Hierarchical Model-View-Controller) :
 * - Ce contrôleur appartient au module "Home"
 * - Il interagit avec les vues spécifiques de ce module
 * - Il utilise le layout partagé pour maintenir une interface cohérente
 * 
 * Les méthodes de ce contrôleur démontrent différentes approches pour
 * le rendu des vues, illustrant la flexibilité du framework.
 */

namespace App\Modules\Home\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil du site
     * 
     * Cette méthode génère la page d'accueil principale en utilisant :
     * - La vue 'home.php' du module Home
     * - Le layout principal 'layout.php' des templates partagés
     * 
     * Elle utilise la méthode viewWithLayout() qui offre une approche
     * simplifiée pour combiner une vue et un layout en une seule étape.
     * 
     * La page d'accueil est généralement la plus visitée du site et présente
     * un aperçu des fonctionnalités ou contenus principaux.
     * 
     * @return string Le HTML complet de la page d'accueil
     */
    public function index(): string
    {
        // Utiliser la méthode viewWithLayout pour générer la page complète en une seule étape
        // - Premier paramètre : nom de la vue ('home' → home.php dans le dossier Views du module)
        // - Deuxième paramètre : nom du layout à utiliser ('layout' → layout.php dans templates partagés)
        // - Troisième paramètre : tableau de variables à passer à la vue et au layout
        return $this->viewWithLayout('home', 'layout', [
            'title' => 'Accueil - Gitopedia' // Définit le titre de la page
        ]);
    }
    
    /**
     * Affiche la page "À propos"
     * 
     * Cette méthode génère la page "À propos" en utilisant :
     * - La vue 'about.php' du module Home
     * - Le layout principal via la classe Layout
     * 
     * Contrairement à la méthode index(), cette implémentation utilise une approche
     * plus détaillée en séparant la génération de la vue et la configuration du layout.
     * Cette approche offre plus de contrôle et de flexibilité pour personnaliser le layout.
     * 
     * La page "À propos" contient typiquement des informations sur le site,
     * sa mission, son équipe ou son histoire.
     * 
     * @return string Le HTML complet de la page "À propos"
     */
    public function about(): string
    {
        // Étape 1 : Générer le contenu de la vue sans layout
        // La méthode view() charge simplement la vue et la rend avec les variables fournies
        $content = $this->view('about');
        
        // Étape 2 : Créer et configurer une instance de Layout
        // createLayout() instancie la classe Layout avec le titre fourni
        $layout = $this->createLayout('À propos - Gitopedia');
        
        // Étape 3 : Injecter le contenu généré dans le layout
        // setContent() place le HTML de la vue dans la zone de contenu principal du layout
        $layout->setContent($content);
        
        // Étape 4 : Générer et retourner le HTML complet
        // render() combine le layout et le contenu pour produire la page finale
        return $layout->render();
        
        // Note: Cette approche en plusieurs étapes permet de configurer davantage
        // le layout avant le rendu final, en ajoutant par exemple des variables
        // spécifiques avec $layout->setVariable('nom', $valeur)
    }
    
    /**
     * Exemple de méthode supplémentaire (commentée)
     * 
     * Cette méthode illustre comment pourrait être implémentée
     * une page de contact avec des données plus complexes.
     */
    /*
    public function contact(): string
    {
        // Exemple de données à passer à la vue
        $contactInfo = [
            'email' => 'contact@gitopedia.com',
            'phone' => '+33 1 23 45 67 89',
            'address' => '123 Rue du Code, 75000 Paris'
        ];
        
        // Créer le layout avec titre personnalisé
        $layout = $this->createLayout('Contact - Gitopedia');
        
        // Générer le contenu avec les données
        $content = $this->view('contact', [
            'contactInfo' => $contactInfo,
            'showMap' => true
        ]);
        
        // Configurer le layout avec contenu et variables additionnelles
        $layout->setContent($content);
        $layout->setVariable('activeMenu', 'contact');
        
        // CSS spécifique pour cette page uniquement
        $layout->setVariable('extraCss', '<link href="/css/contact.css" rel="stylesheet">');
        
        return $layout->render();
    }
    */
}