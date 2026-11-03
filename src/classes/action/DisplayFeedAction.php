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
            <header class="feed-title">
                <h1>Feed général</h1>
                <p>Les derniers touites publiés sur Touiteur.</p>
            </header>
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