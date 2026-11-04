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

        $subTagBtn = "";

        if ($r->isTagSub($this->user['id'], $id)) {
            $subTagBtn = <<<HTML
                <form method="POST" action="?action=subtag" class="ms-auto">
                    <input type="hidden" name="type" value="unsub">
                    <input type="hidden" name="id" value="{$id}">
                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill">Abonné</button>
                </form>
            HTML;
        } else {
            $subTagBtn = <<<HTML
                <form method="POST" action="?action=subtag" class="ms-auto">
                    <input type="hidden" name="type" value="sub">
                    <input type="hidden" name="id" value="{$id}">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill">S'abonner</button>
                </form>
            HTML;
        }

        $page = <<< HTML
            <header class="feed-header detail-header d-flex align-items-center">
                <a class="back-link" href="?action=displayFeed" aria-label="Retour au feed">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1 class="mb-0">#{$tagLabel}</h1>
                {$subTagBtn}
            </header>
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