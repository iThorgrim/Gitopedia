<?php
/**
 * layout.php - Template principal de l'application
 * 
 * Ce fichier est le layout maître qui définit la structure générale de toutes
 * les pages de l'application. Il sert de squelette HTML en :
 * 
 * 1. Incluant l'en-tête (header.php) avec les métadonnées et les styles CSS
 * 2. Intégrant le contenu spécifique à chaque page (variable $content)
 * 3. Incluant le pied de page (footer.php) avec la navigation secondaire et les scripts JS
 * 
 * Cette approche modulaire présente plusieurs avantages :
 * - Maintien d'une structure HTML cohérente à travers toute l'application
 * - Centralisation des éléments communs (header, footer) pour faciliter la maintenance
 * - Séparation claire du contenu et de la présentation
 * - Possibilité d'ajouter facilement des éléments globaux sans modifier chaque vue
 * 
 * Dans l'architecture HMVC, ce layout est typiquement utilisé par la classe Layout
 * qui prépare les variables ($title, $content, etc.) avant le rendu final.
 */

    // Inclure l'en-tête de page
    // Le fichier header.php contient l'ouverture du document HTML,
    // les métadonnées, le titre et les inclusions CSS
    include __DIR__ . '/header.php';
?>

<!-- 
    Zone d'insertion du contenu principal
    
    La variable $content contient le HTML généré par la vue spécifique
    à chaque page. Cette variable est fournie par :
    - La méthode render() de la classe Layout
    - Ou directement via la méthode viewWithLayout() du contrôleur
    
    L'opérateur de coalescence null (??) assure qu'une chaîne vide
    est affichée si la variable $content n'est pas définie, évitant
    ainsi des erreurs potentielles.
-->
<?= $content ?? '' ?>

<?php
    // Inclure le pied de page
    // Le fichier footer.php contient le footer HTML, les inclusions JavaScript
    // et la fermeture des balises body et html
    include __DIR__ . '/footer.php';
?>