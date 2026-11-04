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
            $title = HtmlHelper::title("Déconnexion", "bi-box-arrow-right", "text-secondary");
            return $title
                . "<p>Vous n'êtes pas connecté.</p><p>"
                . HtmlHelper::linkButton("Retour à l'accueil", 'index.php', 'bi-house-door-fill', 'secondary')
                . '</p>';
        }

        // Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-right/
        $title = HtmlHelper::title("Déconnexion", "bi-box-arrow-right", "text-danger");
        $confirmButton = HtmlHelper::button('Confirmer la déconnexion', 'bi-box-arrow-right', 'danger');
        $cancelButton = HtmlHelper::linkButton('Annuler', 'index.php', 'bi-x-lg', 'secondary');

        return <<<HTML
            {$title}
            <p>Êtes-vous sûr de vouloir vous déconnecter ?</p>
            <form method="post" action="?action=signout">
                {$confirmButton}
                {$cancelButton}
            </form>
        HTML;
    }

    #[Override]
    public function post(): string {
        unset($_SESSION['user']);

        $reconnectButton = HtmlHelper::linkButton('Se reconnecter', '?action=signin', 'bi-box-arrow-in-right');
        $homeButton = HtmlHelper::linkButton("Retour à l'accueil", 'index.php', 'bi-house-door-fill', 'secondary');

        return HtmlHelper::title("Déconnexion réussie", "bi-check-circle-fill", "text-success") . <<<HTML
            <p>Vous avez été déconnecté avec succès.</p>
            <p>
                {$reconnectButton}
                {$homeButton}
            </p>
        HTML;
    }
}