<!-- 
/**
 * footer.php - Pied de page et fermeture du document HTML
 * 
 * Ce fichier définit la partie inférieure de chaque page HTML et contient :
 * - Le pied de page (footer) avec informations de copyright et liens utiles
 * - L'inclusion des scripts JavaScript (Bootstrap et personnalisés)
 * - Support pour l'ajout de scripts JS spécifiques à certaines pages
 * - Fermeture des balises body et html
 * 
 * Le footer est inclus automatiquement dans toutes les pages via le layout.php
 * principal, garantissant une structure cohérente et une expérience utilisateur unifiée.
 */
-->
        <!-- 
            Pied de page principal de l'application
            Structure en colonnes responsive avec :
            - Description du site
            - Liens de navigation principaux
            - Liens vers les ressources externes
            - Copyright dynamique avec l'année courante
            
            Le fond sombre (bg-dark) et le texte blanc (text-white) créent
            un contraste visuel avec le reste de la page.
        -->
        <footer class="bg-dark text-white mt-5 py-4">
            <div class="container">
                <div class="row">
                    <!-- Colonne de gauche : nom et description du site -->
                    <div class="col-md-6">
                        <h5>Gitopedia</h5>
                        <p>Gitopedia : Parce que git `commit -m 'help'` ne marche pas.</p>
                    </div>
                    
                    <!-- Colonne centrale : liens de navigation principaux -->
                    <div class="col-md-3">
                        <h5>Liens</h5>
                        <ul class="list-unstyled">
                            <li><a href="/" class="text-white">Accueil</a></li>
                            <li><a href="/about" class="text-white">À propos</a></li>
                            <li><a href="/contact" class="text-white">Contact</a></li>
                        </ul>
                    </div>
                    
                    <!-- Colonne de droite : ressources et liens externes -->
                    <div class="col-md-3">
                        <h5>Ressources</h5>
                        <ul class="list-unstyled">
                            <li><a href="/docs" class="text-white">Documentation</a></li>
                            <li><a href="https://github.com/yourusername/gitopedia" class="text-white">GitHub</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Séparateur horizontal -->
                <hr>
                
                <!-- 
                    Copyright dynamique avec l'année courante
                    L'expression PHP date('Y') insère automatiquement l'année actuelle,
                    évitant ainsi d'avoir à mettre à jour manuellement cette information.
                -->
                <p class="text-center mb-0">&copy; <?= date('Y') ?> Gitopedia. Tous droits réservés.</p>
            </div>
        </footer>

        <!-- 
            Bootstrap JS Bundle avec Popper
            Inclut toutes les fonctionnalités JavaScript de Bootstrap (dropdowns, modals, etc.)
            et Popper.js pour la gestion des poppers et tooltips.
            Placé en fin de document pour optimiser le chargement de la page.
        -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- 
            JavaScript personnalisé de l'application
            Contient les scripts spécifiques au site (validations, interactions, etc.)
        -->
        <script src="/js/app.js"></script>

        <!-- 
            Inclusions JavaScript conditionnelles
            Permet d'ajouter des scripts spécifiques à certaines pages uniquement
            La variable $extraJs peut être définie dans le contrôleur pour inclure
            des scripts additionnels ou du code JavaScript inline
        -->
        <?php if (isset($extraJs)): ?>
            <?= $extraJs ?>
        <?php endif; ?>
    </body>
</html>