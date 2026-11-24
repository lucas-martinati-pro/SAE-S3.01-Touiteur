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

        $tabs = [
            'global' => 'For you',
            'followed' => 'Following',
        ];

        $tabsHtml = HtmlHelper::tabs($tabs, $type, 'feed');

        $search = trim($_GET['search'] ?? '');
        $feed = match ($type) {
            'global' => $r->findFeed($search, $pagination),
            default => $r->findFollowedFeedByUserId($this->user['id'], $search, $pagination),
        };

        $searchSummary = '';
        if ($search !== '') {
            $searchLabel = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
            $clearUrl = htmlspecialchars('?action=feed&type=' . $type, ENT_QUOTES, 'UTF-8');
            $searchSummary = <<<HTML
                <div class="px-3 py-2 border-bottom">
                    <p class="mb-1">Results for: <strong>{$searchLabel}</strong></p>
                    <a href="{$clearUrl}">Clear search</a>
                </div>
            HTML;
        }

        $addTouite = HtmlHelper::addTouite($this->user);
        $page = <<< HTML
            <header class="feed-header">
                <div class="feed-heading">
                    <h1>Home</h1>
                </div>
                <nav class="feed-tabs" aria-label="Feed">
                    {$tabsHtml}
                </nav>
            </header>
            {$searchSummary}
            {$addTouite}
        HTML;

        // Le http_build_query permet d'automatiquement créer la query
        $action = 'feed&' . http_build_query([
            'type' => $type,
            'search' => $search,
        ]);
        return $page . RendererFactory::getRenderer($feed, $pagination, $action)->render(TypeRender::COMPACT);
    }

    #[Override]
    public function post(): string {
        $query = http_build_query([
            'action' => 'feed',
            'type' => 'global',
            'search' => trim($_POST['search'] ?? ''),
        ]);
        header('Location: ?' . $query);
        exit;
    }

    /**
     * Génère la page d’accueil affichée aux visiteurs non connectés.
     * @return string Texte ou contenu HTML généré.
     */
    private function noLogin(): string {
        return <<<HTML
            <section class="landing-page">
                <div class="landing-icon" aria-hidden="true">
                    <i class="bi bi-chat-heart-fill"></i>
                </div>
                <h1>Welcome to Touiteur</h1>
                <p class="landing-lead">
                    The social network where every touite counts.
                </p>
                <p class="landing-description">
                    Share your ideas, discover posts that interest you,
                    follow your favorite tags, and connect with the community.
                </p>
                <div class="landing-actions">
                    <a class="btn btn-primary" href="?action=register">
                        <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                        Create an account
                    </a>
                    <a class="btn btn-outline-primary" href="?action=signin">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                        Sign in
                    </a>
                </div>
                <p class="landing-tagline">
                    <i class="bi bi-stars" aria-hidden="true"></i>
                    The best social network in the world, obviously.
                </p>
            </section>
        HTML;
    }
}
