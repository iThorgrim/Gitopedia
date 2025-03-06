<?php
/**
 * Classe AuthMiddleware - Middleware d'authentification des utilisateurs
 * 
 * Ce middleware implémente un mécanisme fondamental d'authentification pour l'application.
 * Il vérifie si l'utilisateur est actuellement connecté en examinant la session PHP,
 * et redirige automatiquement les utilisateurs non authentifiés vers la page de connexion.
 * 
 * Rôle dans l'architecture HMVC :
 * - Protéger les routes et contrôleurs nécessitant une authentification
 * - Centraliser la logique de vérification d'authentification
 * - Éviter la duplication de code dans les contrôleurs
 * - Séparer clairement la logique d'authentification du reste de l'application
 * 
 * Ce middleware est typiquement appliqué :
 * - Globalement pour protéger toute l'application
 * - Sur des groupes de routes spécifiques (ex: pages administrateur)
 * - Sur des routes individuelles nécessitant une connexion
 * 
 * La structure illustre parfaitement le pattern middleware, où le code
 * d'authentification est complètement séparé de la logique métier,
 * permettant une meilleure organisation et maintenance du code.
 */

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Vérifier l'authentification de l'utilisateur
     * 
     * Cette méthode implémente le processus d'authentification en :
     * 1. S'assurant que la session PHP est active
     * 2. Vérifiant si un identifiant utilisateur est présent en session
     * 3. Redirigeant les utilisateurs non authentifiés vers la page de connexion
     * 4. Permettant aux utilisateurs authentifiés de continuer leur navigation
     * 
     * La redirection vers la page de connexion inclut automatiquement l'URL
     * actuellement demandée comme paramètre 'redirect', ce qui permet à
     * l'application de rediriger l'utilisateur vers sa destination initiale
     * après une connexion réussie (pattern "Post-Login Redirect").
     * 
     * Flux de fonctionnement :
     * - Si l'utilisateur est connecté (session user_id existe) :
     *   → Retourne true pour continuer le traitement normal de la requête
     * - Si l'utilisateur n'est pas connecté :
     *   → Redirige vers la page de connexion avec l'URL actuelle comme paramètre
     *   → Retourne false pour arrêter le traitement de la requête
     * 
     * Cas d'utilisation :
     * - Protection des tableaux de bord utilisateur
     * - Sécurisation des interfaces d'administration
     * - Restriction d'accès aux données personnelles
     * - Protection des opérations sensibles (paiement, modification de compte)
     * 
     * @param Request $request La requête HTTP entrante à examiner
     * @param Response $response La réponse à modifier (pour la redirection)
     * @return bool True si authentifié, false si non authentifié (après redirection)
     */
    public function process(Request $request, Response $response): bool
    {
        // Démarrer la session PHP si elle n'est pas déjà active
        // Cette vérification est importante pour éviter les erreurs
        // si le middleware est appelé plusieurs fois dans une même requête
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est authentifié
        // La présence de user_id en session est utilisée comme indicateur d'authentification
        if (!isset($_SESSION['user_id'])) {
            // Utilisateur non authentifié - préparation de la redirection
            
            // Capturer l'URL actuellement demandée pour y revenir après connexion
            $currentUrl = $request->getUri();
            
            // Rediriger vers la page de connexion avec l'URL actuelle en paramètre
            // Le paramètre est encodé pour éviter les problèmes avec les caractères spéciaux
            $response->redirect('/login?redirect=' . urlencode($currentUrl));
            
            // Retourner false pour arrêter le traitement de la requête
            // Cela empêche l'exécution des middlewares suivants et du contrôleur
            return false;
        }
        
        // Utilisateur authentifié - continuer le traitement normal
        // Cela permet à la requête de passer au middleware suivant ou au contrôleur
        return true;
    }
}