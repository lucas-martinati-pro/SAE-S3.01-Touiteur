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
class DisplayProfile extends Action {
    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? 0);
        // TODO DUPLICATION
        $pageNumber = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        $pageNumber = $pageNumber !== false && $pageNumber > 0 ? $pageNumber : 1;
        $feed = $r->findFeedByUserId($id, $pageNumber);
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
                <h1>Profil</h1>
            </header>
            <section class="profile-header">
                <div class="profile-cover"></div>
                <div class="profile-main">
                    <div class="profile-avatar">T</div>
                    <a class="btn btn-outline-primary profile-button" href="?action=displayFeed">Retour au feed</a>
                    <h2>Utilisateur {$id}</h2>
                    <p class="profile-handle">@utilisateur{$id}</p>
                    <p class="profile-bio">Découvrez les touites publiés par cet utilisateur.</p>
                    <div class="profile-meta">
                        <span><i class="bi bi-calendar3"></i> Membre de Touiteur</span>
                    </div>
                </div>
                <nav class="profile-tabs" aria-label="Navigation du profil">
                    <a class="active" href="?action=displayProfile&id={$id}">Touites</a>
                </nav>
            </section>
        HTML;

        $pagination = <<< HTML
            <nav class="feed-pagination" aria-label="Pagination du profil">
                <a class="btn btn-outline-secondary"
                   {$previousAttributes}
                   href="?action=displayProfile&id={$id}&page={$previousPage}">
                    <i class="bi bi-chevron-left"></i>
                    Page précédente
                </a>
                <span>Page {$pageNumber}</span>
                <a class="btn btn-outline-secondary"
                   href="?action=displayProfile&id={$id}&page={$nextPage}">
                    Page suivante
                    <i class="bi bi-chevron-right"></i>
                </a>
            </nav>
        HTML;

        if ($feed === null) {
            return $page . HtmlHelper::alert('info', 'Cet utilisateur n’a encore publié aucun touite.') . $pagination;
        }

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT) . $pagination;
    }
}