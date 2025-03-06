<?php

namespace App\Modules\Login\Controllers;

use App\Core\Controller;

class LoginController extends Controller
{
    public function index(): string
    {
        return $this->viewWithLayout('index', 'layout', [
            'title' => 'Connexion - Gitopedia'
        ]);
    }

    public function login(): string
    {
        $email = $this->request->post('email');
        $password = $this->request->post('password');

        if (!$this->IsValidCredentials($email, $password)) {
            return $this->viewWithLayout('error', 'layout', [
                'title' => 'Error - Gitopedia',
                'message' => 'Votre email est invalide.'
            ]);
        }

        return "";
    }

    /**
    * Vérifie si les identifiants de connexion sont valides
    * 
    * Cette méthode effectue plusieurs validations sur l'email et le mot de passe fournis :
    * - Vérifie que les deux champs sont bien définis et non vides
    * - Vérifie que la longueur de chaque champ est comprise entre 6 et 64 caractères
    * - Vérifie que l'email correspond au format attendu à l'aide d'une expression régulière
    * 
    * @param string $email L'adresse email à valider
    * @param string $password Le mot de passe à valider
    * @return bool Retourne true si les identifiants sont valides, false sinon
    */
    protected function IsValidCredentials($email, $password)
    {
        // Création d'un tableau contenant les deux identifiants pour les vérifier en une boucle
        $credentials = array($email, $password);

        // Parcourir chaque identifiant (email et mot de passe)
        foreach ($credentials as $credential) 
        {
            // Vérifie si l'identifiant n'est pas défini ou est vide
            // Si c'est le cas, retourne false immédiatement
            if (!isset($credential) || empty($credential))
                return false;

            // Vérifie la longueur de l'identifiant
            $credential_len = strlen($credential);
            // Si la longueur est inférieure à 6 ou supérieure à 64 caractères
            // retourne false immédiatement
            if ($credential_len < 6 || $credential_len > 64)
                return false;
        }

        // Définit un modèle (pattern) d'expression régulière pour valider l'email
        // Ce pattern vérifie que:
        // - La partie avant @ contient des lettres, chiffres et certains caractères spéciaux
        // - Il y a bien un symbole @
        // - Le domaine contient des lettres, chiffres, points et tirets
        // - Il y a au moins un point après le domaine
        // - L'extension de domaine contient au moins 2 lettres
        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

        // Vérifie si l'email correspond au modèle défini
        // Si ce n'est pas le cas, retourne false
        if (!preg_match($pattern, $email))
            return false;

        // Si toutes les vérifications ont réussi, les identifiants sont valides
        return true;
    }
}
