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
        if ($tag === null) {
            return HtmlHelper::errorPage(
                title: "Tag introuvable",
                message: "Le tag demandé est introuvable.",
                backUrl: "?action=displayFeed",
                backLabel: "Retour à l'accueil"
            );
        }
        $tagLabel = $tag->getLibelle();
        $tagDescription = $tag->getDescription();
        $escapedTagLabel = htmlspecialchars($tagLabel, ENT_QUOTES, 'UTF-8');
        $escapedTagDescription = htmlspecialchars($tagDescription, ENT_QUOTES, 'UTF-8');

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
                <h1 class="mb-0">#{$escapedTagLabel}</h1>
                {$subTagBtn}
            </header>
            <section class="tag-intro">
                <p class="tag-description">{$escapedTagDescription}</p>
            </section>
        HTML;

        return $page . RendererFactory::getRenderer($feed, $pagination, 'displayTags')->render(TypeRender::COMPACT);
    }
}