<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\render\HtmlHelper;
use Override;

/**
 * Action de déconnexion de l'utilisateur.
 */
class SignoutAction extends Action {
    #[Override]
    public function get() : string {
        // Si l'utilisateur n'est même pas connecté
        if (!isset($_SESSION['user'])) {
            // Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-right/
            $title = HtmlHelper::title("Déconnexion", "bi-box-arrow-right", "secondary");
            return <<<HTML
                {$title}
                <p>Vous n'êtes pas connecté.</p>
                <p>
                    <a class="btn btn-secondary d-inline-flex align-items-center" href="index.php">
                        <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/house-door-fill/ -->
                        <i class="bi bi-house-door-fill me-1"></i>Retour à l'accueil
                    </a>
                </p>
            HTML;
        }

        // Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-right/
        $title = HtmlHelper::title("Déconnexion", "bi-box-arrow-right", "danger");
        return <<<HTML
            {$title}
            <p>Êtes-vous sûr de vouloir vous déconnecter ?</p>
            <form method="post" action="?action=signout">
                <button class="btn btn-danger d-inline-flex align-items-center" type="submit">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-right/ -->
                    <i class="bi bi-box-arrow-right me-1"></i>Confirmer la déconnexion
                </button>
                <a class="btn btn-secondary ms-2 d-inline-flex align-items-center" href="index.php">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/x-lg/ -->
                    <i class="bi bi-x-lg me-1"></i>Annuler
                </a>
            </form>
        HTML;
    }

    #[Override]
    public function post() : string {
        unset($_SESSION['user']);
        
        return <<<HTML
            <h1 class="h2 fw-bold text-success mb-3 d-flex align-items-center">
                <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/check-circle-fill/ -->
                <i class="bi bi-check-circle-fill text-success me-2"></i>Déconnexion réussie
            </h1>
            <p>Vous avez été déconnecté avec succès.</p>
            <p>
                <a class="btn btn-primary d-inline-flex align-items-center" href="?action=signin">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-in-right/ -->
                    <i class="bi bi-box-arrow-in-right me-1"></i>Se reconnecter
                </a>
                <a class="btn btn-secondary ms-2 d-inline-flex align-items-center" href="index.php">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/house-door-fill/ -->
                    <i class="bi bi-house-door-fill me-1"></i>Retour à l'accueil
                </a>
            </p>
        HTML;
    }
}