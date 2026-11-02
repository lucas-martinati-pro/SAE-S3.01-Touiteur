<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\exception\AuthnException;
use Override;

/**
 * Action par défaut affichant la page d'accueil de l'application Touiteur.
 */
class DefaultAction extends Action {
    protected bool $requireAuth = false;

    #[Override]
    public function get() : string {
        $page = <<< HTML
            <h1 class="h2 fw-bold mb-2">
                <img src="image/favicon.png" width="40" height="40" alt="Logo" class="align-text-top">
                Bienvenue sur Touiteur !
            </h1>
            <p class="text-muted">Bienvenue sur Touiteur, votre plateforme de musique.</p>
            <div class="list-group mb-3" style="max-width: 450px;">
        HTML;
        try {
            AuthnProvider::getSignedInUser();

            $currentPlaylist = "";

            if (isset($_SESSION['playlist'])) {
                $currentPlaylist = <<<HTML
                    <a class="list-group-item list-group-item-action d-flex align-items-center" href="?action=display-playlist">
                        <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/music-note-list/ -->
                        <i class="bi bi-music-note-list text-info me-2 fs-5"></i>
                        Afficher la playlist courante
                    </a>
                HTML;
            }

            $page .= <<<HTML
                <a class="list-group-item list-group-item-action d-flex align-items-center" href="?action=playlists">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/collection-play-fill/ -->
                    <i class="bi bi-collection-play-fill text-primary me-2 fs-5"></i>
                    Afficher mes playlists
                </a>
                <a class="list-group-item list-group-item-action d-flex align-items-center" href="?action=tracks">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/music-note/ -->
                    <i class="bi bi-music-note text-primary me-2 fs-5"></i>
                    Afficher mes pistes
                </a>
                {$currentPlaylist}
                <a class="list-group-item list-group-item-action d-flex align-items-center" href="?action=add-playlist">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/plus-circle-fill/ -->
                    <i class="bi bi-plus-circle-fill text-success me-2 fs-5"></i>
                    Créer une playlist
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
    public function post() : string {
        return '';
    }
}