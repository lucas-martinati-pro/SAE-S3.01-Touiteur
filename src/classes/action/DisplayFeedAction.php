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
            return $this->noLogin();
        }

        $r = RepositoryFactory::getReader();

        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $pagination = new Pagination($page);

        $feed = '';
        $feedType = '';
        $activeGlobal = '';
        $activeFollow = '';
        $search = (isset($_GET['search'])) ? $_GET['search'] : '';
        if (isset($_GET['type']) && $_GET['type'] == 'global') {
            $feed = (!$search) ? $r->findFeed($pagination) : $r->findFeedSearched($search, $pagination);
            $activeGlobal = 'class="active"';
            $feedType = 'global';
        } else {
            $feed = (!$search) ? $r->findFollowedFeed($this->user['id'], $pagination) : $r->findFollowedFeedSearched($this->user['id'], $search, $pagination);
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
                <div class="compose-content">
                    <p>Quoi de neuf ?</p>
                    <a class="btn btn-primary" href="?action=addTouite">Touiter</a>
                </div>
            </div>
        HTML;

        $action = ($feedType === 'global') ? 'displayFeed&type=global' : 'displayFeed';
        return $page . RendererFactory::getRenderer($feed, $pagination, $action)->render(TypeRender::COMPACT);
    }

    #[Override]
    public function post(): string {
        header("Location: ?action=displayFeed&type=global&id=&search=" . $_POST['search']);
        exit;
    }

    private function noLogin(): String {
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
}