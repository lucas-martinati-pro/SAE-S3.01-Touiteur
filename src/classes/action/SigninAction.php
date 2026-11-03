<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\exception\AuthnException;
use iutnc\touiteur\render\HtmlHelper;
use Override;

/**
 * Action gérant l'authentification (connexion) des utilisateurs.
 */
class SigninAction extends Action {
    protected bool $requireAuth = false;

    #[Override]
    public function get() : string {
        try {
            $user = AuthnProvider::getSignedInUser();

            // Icône Bootstrap - https://icons.getbootstrap.com/icons/person-plus-fill/
            $title = HtmlHelper::title("Déjà connecté", "bi-person-plus-fill");
            return <<<HTML
                {$title}
                <p>Vous êtes déjà connecté avec l'adresse <strong>{$user['email']}</strong>.</p>
                <p>
                    <a class="btn btn-primary d-inline-flex align-items-center" href="?action=displayFeedFollowed">
                        <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/collection-play-fill/ -->
                        <i class="bi bi-collection-play-fill me-2"></i>
                        Mon feed
                    </a>
                    <a class="btn btn-danger ms-2 d-inline-flex align-items-center" href="?action=signout">
                        <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-right/ -->
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Se déconnecter
                    </a>
                    <a class="btn btn-secondary ms-2 d-inline-flex align-items-center" href="index.php">
                        <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/house-door-fill/ -->
                        <i class="bi bi-house-door-fill me-1"></i>
                        Accueil
                    </a>
                </p>
            HTML;
        } catch (AuthnException) {
            $require = '<span class="text-danger">*</span>';
            // Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-in-right/
            $title = HtmlHelper::title("Connexion", "bi-box-arrow-in-right");
            return <<<HTML
                {$title}
                <form method="post" action="?action=signin">
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse mail$require</label>
                        <input type="email" name="email" class="form-control" id="email" placeholder="nom@exemple.com" aria-describedby="emailHelp" required>
                        <div id="emailHelp" class="form-text">Saisissez l'adresse e-mail associée à votre compte Touiteur.</div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe$require</label>
                        <input type="password" name="password" class="form-control" id="password" aria-describedby="passwordHelp" required>
                        <div id="passwordHelp" class="form-text">Saisissez le mot de passe associé à votre compte.</div>
                    </div>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center">
                        <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-in-right/ -->
                        <i class="bi bi-box-arrow-in-right me-1"></i>Connexion
                    </button>
                </form>
            HTML;
        }
    }

    #[Override]
    public function post() : string {
        if (!isset($_POST['email'], $_POST['password'])) {
            return HtmlHelper::formError(errors: "Tous les champs sont obligatoires.", backUrl: "?action=signin", title: "Échec de la connexion");
        }

        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        try {
            AuthnProvider::signin($email, $_POST['password']);
        } catch (AuthnException $error) {
            return HtmlHelper::formError(errors: $error->getMessage(), backUrl: "?action=signin", title: "Échec de la connexion");
        }

        $_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        return HtmlHelper::successPage(
            title: "Connexion réussie",
            message: "Bienvenue, <strong>{$email}</strong> ! Vous êtes maintenant connecté à Touiteur.",
            backLabel: "Accéder à mon feed"
        );
    }
}