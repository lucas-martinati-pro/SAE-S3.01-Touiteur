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
class DisplayTag extends Action {
    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? 0);
        $feed = $r->findAllTouitesByTagId($id);
        $tag = $r->findTagById($id);
        $tagLabel = $tag->getLibelle();

        $page = <<< HTML
            <header class="feed-header detail-header">
                <a class="back-link" href="?action=displayFeed" aria-label="Retour au feed">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1>#{$tagLabel}</h1>
            </header>
            <section class="tag-feed">
                <div class="feed-heading">
                    <h2>Touites associés au tag #{$tagLabel}</h2>
                </div>
            </section>
        HTML;

        if ($feed === null) {
            return $page . HtmlHelper::alert('info', 'Aucun touite associé à ce tag.');
        }

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT);
    }
}