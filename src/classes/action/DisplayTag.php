<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\RendererFactory;
use iutnc\touiteur\enum\TypeRender;
use iutnc\touiteur\pagination\Pagination;
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

        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $pagination = new Pagination($page);

        $feed = $r->findFeedByTagId($id, $pagination);
        $tag = $r->findTagById($id);
        $tagLabel = $tag->getLibelle();

        if ($feed === null) {
            return $page . HtmlHelper::alert('info', 'Aucun touite associé à ce tag.');
        }

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

        $previous = $pagination->previous();
        $next = $pagination->next();
        $pageNumber = $pagination->getPage();
        $previousAttributes = $pageNumber === 1
            ? ' aria-disabled="true" tabindex="-1"'
            : '';
        $pagination = <<< HTML
            <nav class="feed-pagination" aria-label="Pagination du tag">
                <a class="btn btn-outline-secondary"
                   {$previousAttributes}
                   href="?action=displayTag&id={$id}&page={$previous}">
                    <i class="bi bi-chevron-left"></i>
                    Page précédente
                </a>
                <span>Page {$pageNumber}</span>
                <a class="btn btn-outline-secondary"
                   href="?action=displayTag&id={$id}&page={$next}">
                    Page suivante
                    <i class="bi bi-chevron-right"></i>
                </a>
            </nav>
        HTML;

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT) . $pagination;
    }
}