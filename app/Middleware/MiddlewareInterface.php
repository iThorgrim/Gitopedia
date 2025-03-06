<?php
/**
 * Interface MiddlewareInterface - Contrat pour tous les middlewares de l'application
 * 
 * Cette interface définit le contrat que tous les middlewares doivent respecter
 * dans l'architecture de l'application. Elle établit le pattern "chaîne de
 * responsabilité" (Chain of Responsibility) qui permet l'interception et le
 * traitement des requêtes HTTP avant qu'elles n'atteignent les contrôleurs.
 * 
 * Les middlewares jouent plusieurs rôles essentiels dans l'architecture HMVC :
 * - Filtrage des requêtes (authentification, autorisation, validation)
 * - Modification des requêtes (ajout d'informations, normalisation)
 * - Modification des réponses (ajout d'en-têtes, compression)
 * - Journalisation et suivi des requêtes
 * - Application de règles de sécurité (CSRF, rate limiting)
 * - Gestion des sessions et des cookies
 * 
 * Le concept de middleware forme une couche intermédiaire entre le client
 * et l'application, permettant une séparation claire des préoccupations
 * et un traitement modulaire des requêtes HTTP.
 */

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

interface MiddlewareInterface
{
    /**
     * Traiter la requête HTTP entrante
     * 
     * Cette méthode est le cœur de tout middleware. Elle reçoit la requête
     * HTTP actuelle et la réponse en cours de construction, et peut effectuer
     * diverses opérations comme :
     * 
     * - Examiner la requête (en-têtes, paramètres, cookies)
     * - Modifier la requête (ajouter des données, des attributs)
     * - Modifier la réponse (ajouter des en-têtes, définir des cookies)
     * - Rediriger l'utilisateur (authentification, maintenance)
     * - Bloquer complètement la requête (sécurité, restrictions d'accès)
     * 
     * Le mécanisme de chaînage est implémenté via la valeur de retour booléenne :
     * - true : Indique que la requête peut continuer vers le prochain middleware
     *   ou le contrôleur final
     * - false : Arrête immédiatement le traitement de la requête, empêchant
     *   l'exécution des middlewares suivants et du contrôleur
     * 
     * Ce système simple mais puissant permet d'implémenter divers comportements
     * comme l'authentification, l'autorisation, la validation ou la protection CSRF.
     * 
     * Exemple d'implémentation typique :
     * public function process(Request $request, Response $response): bool {
     *     // Vérifier une condition
     *     if (!$condition) {
     *         // Modifier la réponse (ex: redirection, message d'erreur)
     *         $response->setStatusCode(403)->setBody('Accès refusé');
     *         // Arrêter le traitement
     *         return false;
     *     }
     *     
     *     // Condition satisfaite, continuer le traitement
     *     return true;
     * }
     * 
     * @param Request $request La requête HTTP à traiter
     * @param Response $response La réponse en cours de construction, peut être modifiée
     * @return bool True pour continuer le traitement de la requête, false pour l'arrêter
     */
    public function process(Request $request, Response $response): bool;
}