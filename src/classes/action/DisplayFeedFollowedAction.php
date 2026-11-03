<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\exception\AuthnException;
use iutnc\touiteur\render\RendererFactory;
use iutnc\touiteur\repository\RepositoryFactory;
use Override;

/**
 * Action par défaut affichant la page d'accueil de l'application Touiteur.
 */
class DisplayFeedFollowedAction extends Action {
    #[Override]
    public function get(): string {
        $page = <<< HTML
            <header class="feed-header">
                <div class="feed-heading">
                    <h1>Accueil</h1>
                </div>
                <nav class="feed-tabs" aria-label="Fil d'actualité">
                    <a href="?action=displayFeed">Pour vous</a>
                    <a class="active" href="?action=displayFeedFollowed">Abonnements</a>
                </nav>
            </header>
            <div class="compose-box">
                <div class="touite-avatar">T</div>
                <div>
                    <p>Quoi de neuf ?</p>
                    <a class="btn btn-primary" href="?action=addTouite">Touiter</a>
                </div>
            </div>
        HTML;
        try {
            AuthnProvider::getSignedInUser();
            
            $r = RepositoryFactory::getReader();
            $feedFollowed = $r->findFollowedFeed($this->user['id']);
            $renderFeed = RendererFactory::getRenderer($feedFollowed);
            $affiche = $renderFeed->render();

            $page .= $affiche;
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