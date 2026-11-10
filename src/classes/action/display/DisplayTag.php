<?php

namespace iutnc\touiteur\action\display;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\RendererFactory;
use iutnc\touiteur\enum\TypeRender;
use iutnc\touiteur\pagination\Pagination;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\action\Action;
use Override;

/**
 * Action par défaut affichant la page d'accueil de l'application Touiteur.
 */
class DisplayTag extends Action {
    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? 0);

        $pageNumber = (int) ($_GET['page'] ?? 1);
        $pagination = new Pagination($pageNumber);

        $feed = $r->findFeedByTagId($id, $pagination);
        $tag = $r->findTagById($id);
        if ($tag === null) {
            return HtmlHelper::notFound('Tag', 'Le tag demandé est introuvable.');
        }
        $tagLabel = $tag->getLibelle();
        $tagDescription = $tag->getDescription();
        $escapedTagDescription = htmlspecialchars($tagDescription, ENT_QUOTES, 'UTF-8');

        $subTagBtn = HtmlHelper::subscriptionButton(
            $id,
            $r->isFollowingTag($this->user['id'], $id),
            'tag',
            'btn btn-sm rounded-pill ms-auto'
        );

        $page = HtmlHelper::detailHeader("#{$tagLabel}", '?action=feed', $subTagBtn) . <<<HTML
            <section class="tag-intro">
                <p class="tag-description">{$escapedTagDescription}</p>
            </section>
        HTML;

        return $page . RendererFactory::getRenderer($feed, $pagination, 'tag&id=' . $id)->render(TypeRender::COMPACT);
    }
}
