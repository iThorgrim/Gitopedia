<!-- 
/**
 * Vue home.php - Page d'accueil du site
 * 
 * Cette vue définit le contenu principal de la page d'accueil.
 * Elle sera intégrée dans le layout principal par le contrôleur.
 * 
 * Dans l'architecture HMVC, cette vue :
 * - Est spécifique au module "Home"
 * - Définit uniquement le contenu central de la page
 * - Est rendue par la méthode index() du HomeController
 * - Est intégrée dans le layout.php qui fournit la structure HTML complète
 * 
 * Toute variable définie dans le contrôleur et passée via le tableau $data
 * est disponible directement dans cette vue (comme $title, si définie).
 */
-->

<div class="container mt-5">
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Fonctionnalité 1</h5>
                    <p class="card-text">Description de la première fonctionnalité...</p>
                    <a href="/feature1" class="btn btn-primary">En savoir plus</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Fonctionnalité 2</h5>
                    <p class="card-text">Description de la deuxième fonctionnalité...</p>
                    <a href="/feature2" class="btn btn-primary">En savoir plus</a>
                </div>
            </div>
        </div>
    </div>
</div>