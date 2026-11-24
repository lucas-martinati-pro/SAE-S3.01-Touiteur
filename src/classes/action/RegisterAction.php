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
        try {
            $user = AuthnProvider::getSignedInUser();
            return HtmlHelper::alreadyLogin($user['email']);
        } catch (AuthnException) {
            $require = '<span class="text-danger">*</span>';
            $registerButton = HtmlHelper::button('Sign up', 'bi-person-plus-fill');
            // Icône Bootstrap - https://icons.getbootstrap.com/icons/person-plus-fill/
            $title = HtmlHelper::title('Sign up', 'bi-person-plus-fill');
            return <<<HTML
            {$title}
            <form method="post" action="?action=register">
                <div>
                    <label for="nom" class="form-label">Last name$require</label>
                    <input type="nom" name="nom" class="form-control" id="nom" placeholder="Doe" required>
                </div>
                <div>
                    <label for="prenom" class="form-label">First name$require</label>
                    <input type="prenom" name="prenom" class="form-control" id="prenom" placeholder="John" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address$require</label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" aria-describedby="emailHelp" required>
                    <div id="emailHelp" class="form-text">Your email address will be used as your login identifier.</div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password$require</label>
                    <input type="password" name="password" class="form-control" id="password" aria-describedby="passwordHelp" required>
                    <div id="passwordHelp" class="form-text">
                        Your password must be at least 10 characters long and include:
                        <ul class="mb-0 ps-3">
                            <li>At least one uppercase letter</li>
                            <li>At least one lowercase letter</li>
                            <li>At least one number</li>
                            <li>At least one special character (e.g., @, #, $, %, etc.)</li>
                        </ul>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password-double" class="form-label">Confirm password$require</label>
                    <input type="password" name="password-double" class="form-control" id="password-double" aria-describedby="passwordDoubleHelp" required>
                    <div id="passwordDoubleHelp" class="form-text">Re-enter your password identically.</div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="connect-auto" name="connect-auto" checked>
                    <label class="form-check-label" for="connect-auto">Sign in automatically</label>
                </div>
                {$registerButton}
            </form>
            HTML;
        }
    }

    #[Override]
    public function post(): string {
        try {
            $user = AuthnProvider::getSignedInUser();
            return HtmlHelper::alreadyLogin($user['email']);
        } catch (AuthnException) {
            if (!isset($_POST['email'], $_POST['nom'], $_POST['prenom'], $_POST['password'], $_POST['password-double'])) {
                return HtmlHelper::formError(errors: 'All fields are required.', backUrl: '?action=register&form=1', title: 'Sign up failed');
            }

            if ($_POST['password'] !== $_POST['password-double']) {
                return HtmlHelper::formError(errors: 'Passwords must match.', backUrl: '?action=register&form=1', title: 'Sign up failed');
            }

            $_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $_POST['nom'] = filter_var($_POST['nom'], FILTER_SANITIZE_SPECIAL_CHARS);
            $_POST['prenom'] = filter_var($_POST['prenom'], FILTER_SANITIZE_SPECIAL_CHARS);

            try {
                AuthnProvider::register($_POST['email'], $_POST['password'], $_POST['nom'], $_POST['prenom']);
            } catch (AuthnException $error) {
                return HtmlHelper::formError(errors: $error->getMessage(), backUrl: '?action=register&form=1', title: 'Sign up failed');
            }

            if (isset($_POST['connect-auto'])) {
                try {
                    AuthnProvider::signin($_POST['email'], $_POST['password']);
                } catch (AuthnException $error) {
                    return HtmlHelper::formError(errors: $error->getMessage(), backUrl: '?action=register&form=1', title: 'Sign in failed');
                }

                $displayName = htmlspecialchars(
                    $_POST['prenom'] . ' ' . $_POST['nom'],
                    ENT_QUOTES,
                    'UTF-8'
                );

                return HtmlHelper::successPage(
                    title: 'Sign in successful',
                    message: "Welcome, <strong>{$displayName}</strong>! You are now signed in to Touiteur.",
                    backLabel: 'Go to my feed'
                );
            }

            return HtmlHelper::successPage(
                title: 'Sign up successful',
                message: 'Your account has been created.',
                backUrl: '?action=signin',
                backLabel: 'Sign in'
            );
        }
    }
}