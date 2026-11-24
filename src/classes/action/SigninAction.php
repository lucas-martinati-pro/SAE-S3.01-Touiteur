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
            return HtmlHelper::alreadyLogin($user['email']);
        } catch (AuthnException) {
            $require = '<span class="text-danger">*</span>';
            // Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-in-right/
            $title = HtmlHelper::title('Sign in', 'bi-box-arrow-in-right');
            $loginButton = HtmlHelper::button('Sign in', 'bi-box-arrow-in-right');
            return <<<HTML
                {$title}
                <form method="post" action="?action=signin">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address$require</label>
                        <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" aria-describedby="emailHelp" required>
                        <div id="emailHelp" class="form-text">Enter the email address associated with your Touiteur account.</div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password$require</label>
                        <input type="password" name="password" class="form-control" id="password" aria-describedby="passwordHelp" required>
                        <div id="passwordHelp" class="form-text">Enter the password associated with your account.</div>
                    </div>
                    {$loginButton}
                </form>
                <section class="demo-accounts" aria-labelledby="demo-accounts-title">
                    <h2 id="demo-accounts-title">Demo accounts</h2>
                    <p>Use one of these accounts to sign in.</p>
                    <div class="demo-account-list">
                        <div class="demo-account">
                            <strong>User 1</strong>
                            <span>user1@mail.com</span>
                            <code>Password: user1</code>
                        </div>
                        <div class="demo-account">
                            <strong>User 2</strong>
                            <span>user2@mail.com</span>
                            <code>Password: user2</code>
                        </div>
                        <div class="demo-account">
                            <strong>Admin</strong>
                            <span>admin@mail.com</span>
                            <code>Password: admin</code>
                        </div>
                    </div>
                </section>
            HTML;
        }
    }

    #[Override]
    public function post(): string {
        try {
            $user = AuthnProvider::getSignedInUser();
            return HtmlHelper::alreadyLogin($user['email']);
        } catch (AuthnException) {
            if (!isset($_POST['email'], $_POST['password'])) {
                return HtmlHelper::formError(errors: 'All fields are required.', backUrl: '?action=signin&form=1', title: 'Sign in failed');
            }

            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

            try {
                AuthnProvider::signin($email, $_POST['password']);
            } catch (AuthnException $error) {
                return HtmlHelper::formError(errors: $error->getMessage(), backUrl: '?action=signin&form=1', title: 'Sign in failed');
            }

            $user = AuthnProvider::getSignedInUser();

            $displayName = htmlspecialchars(
                    $user['prenom'] . ' ' . $user['nom'],
                    ENT_QUOTES,
                    'UTF-8'
                );

            return HtmlHelper::successPage(
                title: 'Sign in successful',
                message: 'Welcome, <strong>' . $displayName . '</strong>! You are now signed in to Touiteur.',
                backLabel: 'Go to my feed'
            );
        }
    }
}