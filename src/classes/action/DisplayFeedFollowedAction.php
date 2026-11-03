<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\exception\AuthnException;
use Override;

/**
 * Action par défaut affichant la page d'accueil de l'application Touiteur.
 */
class DisplayFeedFollowedAction extends Action {
    #[Override]
    public function get(): string {
        $page = <<< HTML
            <h1 class="h2 fw-bold mb-2">
                <img src="image/favicon.png" width="40" height="40" alt="Logo" class="align-text-top">
                Bienvenue sur Touiteur !
            </h1>
            <p class="text-muted">Bienvenue sur Touiteur, votre plateforme de touites.</p>
            <div class="list-group mb-3" style="max-width: 450px;">
        HTML;
        try {
            AuthnProvider::getSignedInUser();

            $page .= <<<HTML
                <a class="list-group-item list-group-item-action d-flex align-items-center" href="?action=displayFeedFollowed">
                    Mon feed
                </a>
                <a class="list-group-item list-group-item-action d-flex align-items-center" href="?action=displayFeed">
                    Feed général
                </a>
                <a class="list-group-item list-group-item-action d-flex align-items-center" href="?action=addTouite">
                    Poster un touite
                </a>
                <a class="list-group-item list-group-item-action text-danger d-flex align-items-center" href="?action=signout">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-right/ -->
                    <i class="bi bi-box-arrow-right text-danger me-2 fs-5"></i>
                    Se déconnecter
                </a>
            HTML;
        } catch (AuthnException) {
            $page .= <<<HTML
                <a class="list-group-item list-group-item-action d-flex align-items-center" href="?action=signin">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-in-right/ -->
                    <i class="bi bi-box-arrow-in-right text-primary me-2 fs-5"></i>
                    Se connecter
                </a>
                <a class="list-group-item list-group-item-action d-flex align-items-center" href="?action=register">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/person-plus-fill/ -->
                    <i class="bi bi-person-plus-fill text-success me-2 fs-5"></i>
                    Inscription
                </a>
            HTML;
        }

        $page .= <<<HTML
            </div>
        HTML;

        return $page;
    }

    #[Override]
    public function post(): string {
        return '';
    }
}