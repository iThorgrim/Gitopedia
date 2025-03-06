<!-- 
/**
 * header.php - Partie supérieure du template HTML
 * 
 * Ce fichier définit la structure du début de chaque page HTML dans l'application.
 * Il contient :
 * - La déclaration DOCTYPE et l'ouverture des balises html et body
 * - Les métadonnées essentielles pour le référencement et l'affichage responsive
 * - Le titre dynamique de la page
 * - Les inclusions de CSS (Bootstrap et personnalisé)
 * - Support pour l'ajout de CSS spécifique à certaines pages
 * 
 * Le header est inclus automatiquement dans toutes les pages via le layout.php
 * principal, assurant ainsi une structure HTML cohérente à travers l'application.
 */
-->
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Encodage des caractères - UTF-8 pour le support complet des caractères internationaux -->
    <meta charset="UTF-8">
    
    <!-- Configuration d'affichage responsive - assure le rendu correct sur tous les appareils -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- 
        Titre de la page - dynamique avec fallback
        La variable $title est fournie par le contrôleur via la classe Layout
        L'opérateur de coalescence null (??) permet d'utiliser un titre par défaut
        si la variable $title n'est pas définie
    -->
    <title><?= $title ?? 'Gitopedia - Framework HMVC' ?></title>
    
    <!-- 
        Bootstrap CSS - framework CSS responsive
        Utilisation de la version CDN pour une mise en cache optimale et une réduction
        de la charge serveur. La version 5.3.0 fournit des composants modernes et accessibles.
    -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- 
        Bootstrap Icons - bibliothèque d'icônes compatible avec Bootstrap
        Fournit un large éventail d'icônes vectorielles utilisables dans l'interface
    -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- 
        CSS personnalisé de l'application
        Ce fichier contient les styles spécifiques qui surchargent ou complètent Bootstrap
    -->
    <link href="/css/style.css" rel="stylesheet">
    
    <!-- 
        Inclusions CSS conditionnelles
        Permet d'ajouter des styles spécifiques à certaines pages uniquement
        La variable $extraCss peut être définie dans le contrôleur pour inclure
        des feuilles de styles additionnelles ou des styles inline
    -->
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>
<!-- Le contenu de la page sera inséré ici, suivi par footer.php -->