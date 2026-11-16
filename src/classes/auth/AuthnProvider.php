<?php

namespace iutnc\touiteur\auth;

use iutnc\touiteur\exception\AuthnException;
use iutnc\touiteur\repository\RepositoryFactory;

/**
 * Fournisseur d'authentification.
 */
class AuthnProvider {
    /**
     * Authentifie un utilisateur à partir de son email et mot de passe. En cas de succès, stocke l'utilisateur sérialisé dans la session ($_SESSION['user']).
     * @param string $email adresse e-mail de l’utilisateur.
     * @param string $password mot de passe de l’utilisateur.
     * @return void Aucune valeur.
     * @throws AuthnException Si le traitement échoue.
     */
    public static function signin(string $email, string $password): void {
        $r = RepositoryFactory::getReader();

        $user = $r->findUserByEmail(filter_var($email, FILTER_SANITIZE_EMAIL));

        if (empty($user) || !password_verify($password, $user['passwd'])) {
            throw new AuthnException('Auth error : invalid credentials');
        }
        $_SESSION['user'] = serialize($user);
    }

    /**
     * Enregistre un nouvel utilisateur après validation de l'email et du mot de passe. Exigences pour le mot de passe : au moins 10 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.
     * @param string $email adresse e-mail de l’utilisateur.
     * @param string $password mot de passe de l’utilisateur.
     * @param string $nom nom de l’utilisateur.
     * @param string $prenom prénom de l’utilisateur.
     * @return void Aucune valeur.
     * @throws AuthnException Si le traitement échoue.
     */
    public static function register(string $email, string $password, string $nom, string $prenom): void {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $nom = filter_var($nom, FILTER_SANITIZE_SPECIAL_CHARS);
        $prenom = filter_var($prenom, FILTER_SANITIZE_SPECIAL_CHARS);

        if ($email === false) {
            throw new AuthnException("L'adresse email n'est pas valide.");
        }

        $r = RepositoryFactory::getReader();
        $w = RepositoryFactory::getWriter();

        if ($r->existsByEmail($email)) {
            throw new AuthnException('Cette adresse email est déjà utilisée.');
        }

        $minimumLength = 10;

        if (strlen($password) < $minimumLength) { // longueur minimale
            throw new AuthnException("Le mot de passe doit contenir au moins {$minimumLength} caractères.");
        }
        if (!preg_match("#\d#", $password)) {// au moins un digit
            throw new AuthnException('Le mot de passe doit contenir au moins un chiffre.');
        }
        if (!preg_match("#\W#", $password)) { // au moins un car. spécial
            throw new AuthnException('Le mot de passe doit contenir au moins un caractère spécial.');
        }
        if (!preg_match('#[a-z]#', $password)) { // au moins une minuscule
            throw new AuthnException('Le mot de passe doit contenir au moins une lettre minuscule.');
        }
        if (!preg_match('#[A-Z]#', $password)) { // au moins une majuscule
            throw new AuthnException('Le mot de passe doit contenir au moins une lettre majuscule.');
        }

        $w->addUser($email, $password, $nom, $prenom);
    }

    /**
     * Récupère le tableau de données de l'utilisateur actuellement connecté.
     * @return array{id: int|string, email: string, passwd: string, nom: string, prenom: string, counter_touite: int|string} Données du compte connecté.
     * @throws AuthnException Si le traitement échoue.
     */
    public static function getSignedInUser(): array {
        if (!isset($_SESSION['user']) || !is_string($_SESSION['user'])) {
            throw new AuthnException('Vous devez être connecté pour accéder à cette page.');
        }

        $user = unserialize($_SESSION['user'], ['allowed_classes' => false]);
        if (!is_array($user) || !isset($user['id'])) {
            throw new AuthnException('Vous devez être connecté pour accéder à cette page.');
        }

        return $user;
    }

    /**
     * Indique si l’utilisateur connecté est administrateur.
     * @return bool Résultat de la vérification.
     * @throws AuthnException Si aucun utilisateur n’est connecté.
     */
    public static function isAdminUser(): bool {
        $user = self::getSignedInUser();

        return RepositoryFactory::getReader()->isAdminUser($user);
    }
}