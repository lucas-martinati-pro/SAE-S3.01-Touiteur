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
    protected bool $requireAuth = false;

    #[Override]
    public function get(): string {
        if ($this->user === null) {
            return <<<HTML
                <section class="landing-page">
                    <div class="landing-icon" aria-hidden="true">
                        <i class="bi bi-chat-heart-fill"></i>
                    </div>
                    <h1>Bienvenue sur Touiteur</h1>
                    <p class="landing-lead">
                        Le réseau social où chaque touite compte.
                    </p>
                    <p class="landing-description">
                        Partagez vos idées, découvrez les publications qui vous intéressent,
                        suivez vos tags préférés et échangez avec la communauté.
                    </p>
                    <div class="landing-actions">
                        <a class="btn btn-primary" href="?action=register">
                            <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                            Créer un compte
                        </a>
                        <a class="btn btn-outline-primary" href="?action=signin">
                            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                            Se connecter
                        </a>
                    </div>
                    <p class="landing-tagline">
                        <i class="bi bi-stars" aria-hidden="true"></i>
                        Le meilleur réseau social du monde, évidemment.
                    </p>
                </section>
            HTML;
        }

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

        $profileAvatar = HtmlHelper::profileAvatar($this->user);
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
                {$profileAvatar}
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