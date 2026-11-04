<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\exception\AuthnException;
use iutnc\touiteur\render\HtmlHelper;
use Override;

/**
 * Action gérant l'inscription de nouveaux utilisateurs sur Touiteur.
 */
class RegisterAction extends Action {
    protected bool $requireAuth = false;

    #[Override]
    public function get(): string {
        $require = '<span class="text-danger">*</span>';
        $registerButton = HtmlHelper::button('Inscription', 'bi-person-plus-fill');
        // Icône Bootstrap - https://icons.getbootstrap.com/icons/person-plus-fill/
        $title = HtmlHelper::title("Inscription", "bi-person-plus-fill");
        return <<<HTML
        {$title}
        <form method="post" action="?action=register">
            <div>
                <label for="nom" class="form-label">Nom$require</label>
                <input type="nom" name="nom" class="form-control" id="nom" placeholder="Dupont" required>
            </div>
            <div>
                <label for="prenom" class="form-label">Prénom$require</label>
                <input type="prenom" name="prenom" class="form-control" id="prenom" placeholder="Dupont" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Adresse mail$require</label>
                <input type="email" name="email" class="form-control" id="email" placeholder="nom@exemple.com" aria-describedby="emailHelp" required>
                <div id="emailHelp" class="form-text">Votre adresse e-mail servira d'identifiant de connexion.</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe$require</label>
                <input type="password" name="password" class="form-control" id="password" aria-describedby="passwordHelp" required>
                <div id="passwordHelp" class="form-text">
                    Le mot de passe doit contenir au moins 10 caractères et comporter :
                    <ul class="mb-0 ps-3">
                        <li>Au moins une lettre majuscule</li>
                        <li>Au moins une lettre minuscule</li>
                        <li>Au moins un chiffre</li>
                        <li>Au moins un caractère spécial (ex. @, #, $, %, etc.)</li>
                    </ul>
                </div>
            </div>
            <div class="mb-3">
                <label for="password-double" class="form-label">Confirmation du mot de passe$require</label>
                <input type="password" name="password-double" class="form-control" id="password-double" aria-describedby="passwordDoubleHelp" required>
                <div id="passwordDoubleHelp" class="form-text">Ressaisissez votre mot de passe à l'identique.</div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="connect-auto" name="connect-auto" checked>
                <label class="form-check-label" for="connect-auto">Se connecter automatiquement</label>
            </div>
            {$registerButton}
        </form>
        HTML;
    }

    #[Override]
    public function post(): string {
        if (!isset($_POST['email'], $_POST['nom'], $_POST['prenom'], $_POST['password'], $_POST['password-double'])) {
            return HtmlHelper::formError(errors: "Tous les champs sont obligatoires.", backUrl: "?action=register&form=1", title: "Échec de l'inscription");
        }

        if ($_POST['password'] !== $_POST['password-double']) {
            return HtmlHelper::formError(errors: "Les deux mots de passe doivent être identiques.", backUrl: "?action=register&form=1", title: "Échec de l'inscription");
        }

        $_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $_POST['nom'] = filter_var($_POST['nom'], FILTER_SANITIZE_SPECIAL_CHARS);
        $_POST['prenom'] = filter_var($_POST['prenom'], FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            AuthnProvider::register($_POST['email'], $_POST['password'], $_POST['nom'], $_POST['prenom']);
        } catch (AuthnException $error) {
            return HtmlHelper::formError(errors: $error->getMessage(), backUrl: "?action=register&form=1", title: "Échec de l'inscription");
        }

        if (isset($_POST['connect-auto'])) {
            try {
                AuthnProvider::signin($_POST['email'], $_POST['password']);
            } catch (AuthnException $error) {
                return HtmlHelper::formError(errors: $error->getMessage(), backUrl: "?action=register&form=1", title: "Échec de la connexion");
            }

            $displayName = htmlspecialchars(
                $_POST['prenom'] . ' ' . $_POST['nom'],
                ENT_QUOTES,
                'UTF-8'
            );

            return HtmlHelper::successPage(
                title: "Connexion réussie",
                message: "Bienvenue, <strong>{$displayName}</strong> ! Vous êtes maintenant connecté à Touiteur.",
                backLabel: "Accéder à mon feed"
            );
        }

        return HtmlHelper::successPage(
            title: "Inscription réussie",
            message: "Votre compte a bien été créé.",
            backUrl: "?action=signin",
            backLabel: "Se connecter"
        );
    }
}