<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\render\HtmlHelper;
use Override;

/**
 * Action de déconnexion de l'utilisateur.
 */
class SignoutAction extends Action {
    #[Override]
    public function get(): string {
        // Si l'utilisateur n'est même pas connecté
        if (!isset($_SESSION['user'])) {
            // Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-right/
            $title = HtmlHelper::title('Sign out', 'bi-box-arrow-right', 'text-secondary');
            return $title
                . '<p>You are not signed in.</p><p>'
                . HtmlHelper::linkButton('Back to home', 'index.php', 'bi-house-door-fill', 'secondary')
                . '</p>';
        }

        // Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-right/
        $title = HtmlHelper::title('Sign out', 'bi-box-arrow-right', 'text-danger');
        $confirmButton = HtmlHelper::button('Confirm sign out', 'bi-box-arrow-right', 'danger');
        $cancelButton = HtmlHelper::linkButton('Cancel', 'index.php', 'bi-x-lg', 'secondary');

        return <<<HTML
            {$title}
            <p>Are you sure you want to sign out?</p>
            <form method="post" action="?action=signout">
                {$confirmButton}
                {$cancelButton}
            </form>
        HTML;
    }

    #[Override]
    public function post(): string {
        unset($_SESSION['user']);

        return HtmlHelper::successPage(
            title: 'Sign out successful',
            message: 'You have been signed out successfully.',
            backUrl: '?action=signin',
            backLabel: 'Sign in again',
            secondaryUrl: 'index.php',
            secondaryLabel: 'Back to home'
        );
    }
}
