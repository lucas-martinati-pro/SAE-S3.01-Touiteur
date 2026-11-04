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
        $r = RepositoryFactory::getReader();

        $feed = "";
        $activeGlobal = '';
        $activeFollow = '';
        $pageNumber = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        $pageNumber = $pageNumber !== false && $pageNumber > 0 ? $pageNumber : 1;
        $feedType = isset($_GET['type']) && $_GET['type'] === 'global' ? 'global' : null;

        if ($feedType === 'global') {
            $feed = $r->findFeed($pageNumber);
            $activeGlobal = 'class="active"';
        } else {
            $feed = $r->findFollowedFeed($this->user['id'], $pageNumber);
            $activeFollow = 'class="active"';
        }

        $feedQuery = $feedType === 'global' ? '&type=global' : '';
        $previousPage = max(1, $pageNumber - 1);
        $nextPage = $pageNumber + 1;
        $previousAttributes = $pageNumber === 1
            ? ' aria-disabled="true" tabindex="-1"'
            : '';

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

        $pagination = <<< HTML
            <nav class="feed-pagination" aria-label="Pagination du feed">
                <a class="btn btn-outline-secondary"
                   {$previousAttributes}
                   href="?action=displayFeed{$feedQuery}&page={$previousPage}">
                    <i class="bi bi-chevron-left"></i>
                    Page précédente
                </a>
                <span>Page {$pageNumber}</span>
                <a class="btn btn-outline-secondary"
                   href="?action=displayFeed{$feedQuery}&page={$nextPage}">
                    Page suivante
                    <i class="bi bi-chevron-right"></i>
                </a>
            </nav>
        HTML;

        if ($feed === null) {
            return $page . HtmlHelper::alert('info', 'Aucun touite à afficher pour le moment.') . $pagination;
        }

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT) . $pagination;
    }
}