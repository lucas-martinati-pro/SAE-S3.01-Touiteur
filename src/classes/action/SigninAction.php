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
    public function get(): string {
        try {
            $user = AuthnProvider::getSignedInUser();
            $email = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');

            // Icône Bootstrap - https://icons.getbootstrap.com/icons/person-plus-fill/
            $title = HtmlHelper::title("Déjà connecté", "bi-person-plus-fill");
            $feedButton = HtmlHelper::linkButton('Mon feed', '?action=displayFeed', 'bi-collection-play-fill');
            $signoutButton = HtmlHelper::linkButton('Se déconnecter', '?action=signout', 'bi-box-arrow-right', 'danger');
            $homeButton = HtmlHelper::linkButton('Accueil', 'index.php', 'bi-house-door-fill', 'secondary');
            return <<<HTML
                {$title}
                <p>Vous êtes déjà connecté avec l'adresse <strong>{$email}</strong>.</p>
                <p>
                    {$feedButton}
                    {$signoutButton}
                    {$homeButton}
                </p>
            HTML;
        } catch (AuthnException) {
            $require = '<span class="text-danger">*</span>';
            // Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-in-right/
            $title = HtmlHelper::title("Connexion", "bi-box-arrow-in-right");
            $loginButton = HtmlHelper::button('Connexion', 'bi-box-arrow-in-right');
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
                    {$loginButton}
                </form>
                <section class="demo-accounts" aria-labelledby="demo-accounts-title">
                    <h2 id="demo-accounts-title">Comptes de démonstration</h2>
                    <p>Utilisez l'un de ces comptes pour vous connecter.</p>
                    <div class="demo-account-list">
                        <div class="demo-account">
                            <strong>User 1</strong>
                            <span>user1@mail.com</span>
                            <code>Mot de passe : user1</code>
                        </div>
                        <div class="demo-account">
                            <strong>User 2</strong>
                            <span>user2@mail.com</span>
                            <code>Mot de passe : user2</code>
                        </div>
                        <div class="demo-account">
                            <strong>Admin</strong>
                            <span>admin@mail.com</span>
                            <code>Mot de passe : admin</code>
                        </div>
                    </div>
                </section>
            HTML;
        }
    }

    #[Override]
    public function post(): string {
        if (!isset($_POST['email'], $_POST['password'])) {
            return HtmlHelper::formError(errors: "Tous les champs sont obligatoires.", backUrl: "?action=signin&form=1", title: "Échec de la connexion");
        }

        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        try {
            AuthnProvider::signin($email, $_POST['password']);
        } catch (AuthnException $error) {
            return HtmlHelper::formError(errors: $error->getMessage(), backUrl: "?action=signin&form=1", title: "Échec de la connexion");
        }

        return HtmlHelper::successPage(
            title: "Connexion réussie",
            message: "Bienvenue, <strong>" . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</strong> ! Vous êtes maintenant connecté à Touiteur.",
            backLabel: "Accéder à mon feed"
        );
    }
}