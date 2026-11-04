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

        $page = HtmlHelper::detailHeader("#{$tagLabel}") . <<< HTML
            <section class="tag-feed">
                <div class="feed-heading">
                    <h2>Touites associés au tag #{$tagLabel}</h2>
                </div>
            </section>
        HTML;

        $previous = $pagination->previous();
        $next = $pagination->next();
        $pageNumber = $pagination->getPage();
        $pagination = HtmlHelper::pagination(
            'Pagination du tag',
            "?action=displayTag&id={$id}&page={$previous}",
            "?action=displayTag&id={$id}&page={$next}",
            $pageNumber
        );

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT) . $pagination;
    }
}