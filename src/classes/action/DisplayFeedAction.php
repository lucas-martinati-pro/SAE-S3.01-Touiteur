<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\RendererFactory;
use iutnc\touiteur\enum\TypeRender;
use iutnc\touiteur\render\HtmlHelper;
use Override;

/**
 * Action par défaut affichant la page d'accueil de l'application Touiteur.
 */
class DisplayFeedAction extends Action {
    #[Override]
    public function get(): string {
        $reader = RepositoryFactory::getReader();
        $feed = $reader->findAllTouites();

        $page = <<< HTML
            <header class="feed-header">
                <div class="feed-heading">
                    <h1>Accueil</h1>
                </div>
                <nav class="feed-tabs" aria-label="Fil d'actualité">
                    <a class="active" href="?action=displayFeed">Pour vous</a>
                    <a href="?action=displayFeedFollowed">Abonnements</a>
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

        if ($feed === null) {
            return $page . HtmlHelper::alert('info', 'Aucun touite à afficher pour le moment.');
        }

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT);
    }

    #[Override]
    public function post(): string {
        return '';
    }
}