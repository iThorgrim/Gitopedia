<!-- 
/**
 * Vue about.php - Page "À propos"
 * 
 * Cette vue définit le contenu de la page "À propos" du site.
 * Elle sera intégrée dans le layout principal par le contrôleur.
 * 
 * Dans l'architecture HMVC, cette vue :
 * - Est spécifique au module "Home"
 * - Ne contient que le contenu central de la page
 * - Est appelée par la méthode about() du HomeController
 * - Est ensuite intégrée dans le layout complet via la classe Layout
 * 
 * Cette page est intentionnellement simple pour illustrer le concept
 * de base des vues. Dans une application réelle, elle contiendrait
 * plus de contenu et de mise en forme.
 */
-->

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h2>Notre mission</h2>
            <p>Gitopedia a été créée pour simplifier la compréhension de Git et faciliter son utilisation...</p>
            
            <h2>Notre équipe</h2>
            <p>Notre équipe est composée de passionnés du développement web et des bonnes pratiques...</p>
            
            <h2>Notre histoire</h2>
            <p>Tout a commencé en 2023 lorsque nous avons réalisé que de nombreux développeurs...</p>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Contactez-nous</h5>
                    <p class="card-text">Des questions? N'hésitez pas à nous contacter.</p>
                    <a href="/contact" class="btn btn-primary">Contact</a>
                </div>
            </div>
        </div>
    </div>
</div>