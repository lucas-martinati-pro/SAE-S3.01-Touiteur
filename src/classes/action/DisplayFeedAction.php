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
class DisplayFeedAction extends Action {
    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $pagination = new Pagination($page);
        $feedType = (isset($_GET['type']) && $_GET['type'] === 'global') ? 'global' : null;

        $feed = "";
        $activeGlobal = '';
        $activeFollow = '';
        if ($feedType === 'global') {
            $feed = $r->findFeed($pagination);
            $activeGlobal = 'class="active"';
        } else {
            $feed = $r->findFollowedFeed($this->user['id'], $pagination);
            $activeFollow = 'class="active"';
        }

        $page = <<< HTML
            <header class="feed-header">
                <div class="feed-heading">
                    <h1>Accueil</h1>
                </div>
                <nav class="feed-tabs" aria-label="Fil d'actualité">
                    <a {$activeGlobal} href="?action=displayFeed&type=global">Pour vous</a>
                    <a {$activeFollow} href="?action=displayFeed">Abonnements</a>
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

        $feedQuery = $feedType === 'global' ? '&type=global' : '';
        $previous = $pagination->previous();
        $next = $pagination->next();
        $pageNumber = $pagination->getPage();
        $pagination = HtmlHelper::pagination(
            'Pagination du feed',
            "?action=displayFeed{$feedQuery}&page={$previous}",
            "?action=displayFeed{$feedQuery}&page={$next}",
            $pageNumber
        );

        if ($feed === null) {
            return $page . HtmlHelper::alert('info', 'Aucun touite à afficher pour le moment.') . $pagination;
        }

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT) . $pagination;
    }
}