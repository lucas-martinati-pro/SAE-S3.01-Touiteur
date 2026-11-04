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
        // TODO DUPLICATION
        $pageNumber = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        $pageNumber = $pageNumber !== false && $pageNumber > 0 ? $pageNumber : 1;
        $feed = $r->findFeedByTagId($id, $pageNumber);
        $tag = $r->findTagById($id);
        $tagLabel = $tag->getLibelle();

        $previousPage = max(1, $pageNumber - 1);
        $nextPage = $pageNumber + 1;
        $previousAttributes = $pageNumber === 1
            ? ' aria-disabled="true" tabindex="-1"'
            : '';

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

        $pagination = <<< HTML
            <nav class="feed-pagination" aria-label="Pagination du tag">
                <a class="btn btn-outline-secondary"
                   {$previousAttributes}
                   href="?action=displayTag&id={$id}&page={$previousPage}">
                    <i class="bi bi-chevron-left"></i>
                    Page précédente
                </a>
                <span>Page {$pageNumber}</span>
                <a class="btn btn-outline-secondary"
                   href="?action=displayTag&id={$id}&page={$nextPage}">
                    Page suivante
                    <i class="bi bi-chevron-right"></i>
                </a>
            </nav>
        HTML;

        if ($feed === null) {
            return $page . HtmlHelper::alert('info', 'Aucun touite associé à ce tag.') . $pagination;
        }

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT) . $pagination;
    }
}