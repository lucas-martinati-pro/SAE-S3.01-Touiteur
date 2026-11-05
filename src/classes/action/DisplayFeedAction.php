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

        $pageNumber = (int) ($_GET['page'] ?? 1);
        $pagination = new Pagination($pageNumber);

        $type = $_GET['type'] ?? 'followed';
        $search = htmlspecialchars(trim($_GET['search'] ?? ''), ENT_QUOTES, 'UTF-8');
        $feed = match ($type) {
            'global' => ($search === '') ? $r->findFeed($pagination) : $r->findFeedSearched($search, $pagination),
            default => ($search === '') ? $r->findFollowedFeed($this->user['id'], $pagination) : $r->findFollowedFeedSearched($this->user['id'], $search, $pagination),
        };

        $tabs = [
            'global' => 'Pour vous',
            'followed' => 'Abonnements',
        ];

        $ongletsHtml = '';

        foreach ($tabs as $value => $label) {
            $active = $type === $value ? 'active' : '';
            $url = '?' . http_build_query([
                'action' => 'displayFeed',
                'type' => $value,
                'search' => $search,
            ]);
            $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $ongletsHtml .= <<<HTML
                <a class="{$active}" href="{$url}">{$label}</a>
            HTML;
        }

        $searchSummary = '';
        if ($search !== '') {
            $clearUrl = '?action=displayFeed&type=' . $type;
            $searchSummary = <<<HTML
                <div class="px-3 py-2 border-bottom">
                    <p class="mb-1">Résultats pour : <strong>{$search}</strong></p>
                    <a href="{$clearUrl}">Effacer la recherche</a>
                </div>
            HTML;
        }

        $profileAvatar = HtmlHelper::profileAvatar($this->user);
        $page = <<< HTML
            <header class="feed-header">
                <div class="feed-heading">
                    <h1>Accueil</h1>
                </div>
                <nav class="feed-tabs" aria-label="Fil d'actualité">
                    {$ongletsHtml}
                </nav>
            </header>
            {$searchSummary}
            <div class="compose-box">
                {$profileAvatar}
                <div class="compose-content">
                    <p>Quoi de neuf ?</p>
                    <a class="btn btn-primary" href="?action=addTouite">Touiter</a>
                </div>
            </div>
        HTML;

        // Le http_build_query permet d'automatiquement créer la query
        $action = 'displayFeed&' . http_build_query([
            'type' => $type,
            'search' => $search,
        ]);
        return $page . RendererFactory::getRenderer($feed, $pagination, $action)->render(TypeRender::COMPACT);
    }

    #[Override]
    public function post(): string {
        $query = http_build_query([
            'action' => 'displayFeed',
            'type' => 'global',
            'search' => trim($_POST['search'] ?? ''),
        ]);
        header('Location: ?' . $query);
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
