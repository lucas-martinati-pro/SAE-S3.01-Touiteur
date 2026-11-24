<?php

namespace iutnc\touiteur\dispatch;

use iutnc\touiteur\action\display\DisplayFeedAction;
use iutnc\touiteur\action\display\DisplayListFollowedTag;
use iutnc\touiteur\action\display\DisplayProfile;
use iutnc\touiteur\action\display\DisplayTag;
use iutnc\touiteur\action\display\DisplayTouite;
use iutnc\touiteur\action\display\DisplayUsersAction;
use iutnc\touiteur\action\command\AddTouiteAction;
use iutnc\touiteur\action\command\AddTouiteTagAction;
use iutnc\touiteur\action\command\DeleteTouiteAction;
use iutnc\touiteur\action\command\LikeAction;
use iutnc\touiteur\action\command\SubscribeAction;
use iutnc\touiteur\action\RegisterAction;
use iutnc\touiteur\action\SigninAction;
use iutnc\touiteur\action\SignoutAction;
use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\exception\AuthnException;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\repository\RepositoryFactory;

/**
 * Contrôleur de l'affichage.
 */
class Dispatcher {
    /**
     * Identifiant textuel de l'action à exécuter (ex: 'signin', etc.).
     */
    private string $action;

    /**
     * Initialise le Dispatcher avec le nom de l'action demandée.
     * @param string $action nom de l’action.
     */
    public function __construct(string $action) {
        $this->action = $action;
    }

    /**
     * Point d'entrée de l'exécution du Dispatcher.
     * @return void Aucune valeur.
     */
    public function run(): void {
        $html = match ($this->action) {
            'feed' => (new DisplayFeedAction())(),
            'listFollowTag' => (new DisplayListFollowedTag())(),
            'profile' => (new DisplayProfile())(),
            'tag' => (new DisplayTag())(),
            'touite' => (new DisplayTouite())(),
            'users' => (new DisplayUsersAction())(),
            'addTouite' => (new AddTouiteAction())(),
            'addTouiteTag' => (new AddTouiteTagAction())(),
            'deleteTouite' => (new DeleteTouiteAction())(),
            'like' => (new LikeAction())(),
            'subscribe' => (new SubscribeAction())(),
            'register' => (new RegisterAction())(),
            'signin' => (new SigninAction())(),
            'signout' => (new SignoutAction())(),
            default => (new DisplayFeedAction())(),
        };

        $this->renderPage($html);
    }

    /**
     * Génère et affiche la structure HTML complète de la page web.
     * @param string $html contenu HTML à afficher.
     * @return void Aucune valeur.
     */
    private function renderPage(string $html): void {
        $navbar = $this->renderNavbar();
        $theme = (($_COOKIE['theme'] ?? 'dark') === 'light') ? 'light' : 'dark';

        $tagsHtml = $this->renderSideBar();

        echo <<<HTML
            <!DOCTYPE html>
            <html lang="en" data-bs-theme="{$theme}" id="html">
                <head>
                    <title>Touiteur</title>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <!-- css pour Bootstrap -->
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
                    <!-- css pour les icônes Boostrap -->
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
                    <link href="css/style.css" rel="stylesheet">
                    <!-- Icône personnalisée pour le site -->
                    <link rel="icon" type="image/x-icon" href="image/favicon.png">
                </head>
                <body>
                    <div class="app-layout" id="swup">
                        <aside class="sidebar">
                            <a class="navbar-brand fw-bold text-primary d-flex align-items-center mb-4" href="index.php" aria-label="Home">
                                <img src="image/favicon.png" alt="" width="28" height="28" class="me-2">
                                <span class="logo-word">Touiteur</span>
                            </a>
                            {$navbar}
                        </aside>
                        <main class="page-content">
                            $html
                        </main>
                        $tagsHtml
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
                    <script src="js/script.js"></script>
                </body>
            </html>
        HTML;
    }

    /**
     * Génère la barre latérale avec les tendances et la recherche.
     * @return string Texte ou contenu HTML généré.
     */
    private function renderSideBar(): string {
        try {
            AuthnProvider::getSignedInUser();

            $tagsHtml = '';
            $r = RepositoryFactory::getReader();
            $tags = $r->findAllTags(10);
            foreach ($tags as $tag) {
                $tagsHtml .= <<<HTML
                    <a href="?action=tag&id={$tag->getId()}">#{$tag->getLibelle()}</a>
                HTML;
            }

            $search = HtmlHelper::searchForm('?action=feed', trim((string) ($_GET['search'] ?? '')));

            return <<<HTML
                <aside class="right-sidebar">
                    {$search}
                    <section class="trends">
                        <h2>Popular tags</h2>
                        <div class="tag-list">
                                $tagsHtml
                        </div>
                    </section>
                </aside>
            HTML;
        } catch (AuthnException) {
            return '';
        }
    }

    /**
     * Génère les liens de navigation et les commandes du compte.
     * @return string Texte ou contenu HTML généré.
     */
    private function renderNavbar(): string {
        $sidebarLinks = <<<HTML
            <div class="sidebar-links">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php" aria-label="Home">
                            <i class="bi bi-house-door-fill" aria-hidden="true"></i><span>Home</span>
                        </a>
                    </li>
                </ul>
            </div>
        HTML;
        $themeSwitch = <<<HTML
            <div class="theme-switch">
                <button id="toggle-theme" class="theme-toggle" type="button" aria-label="Switch theme">
                <i class="bi bi-circle-half theme-toggle-icon" aria-hidden="true"></i>
                <span class="theme-toggle-label">Switch theme</span>
            </button>
            </div>
        HTML;

        try {
            $user = AuthnProvider::getSignedInUser();

            $userId = (int) $user['id'];
            $email = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
            $profileAvatar = HtmlHelper::profileAvatar($user, 'sidebar-avatar');

            $btnAdmin = "";

            if (AuthnProvider::isAdminUser()) {
                $btnAdmin = <<<HTML
                    <a href="admin" class="btn btn-outline-primary w-100 mb-2 sidebar-admin-link" aria-label="Admin Panel">
                        <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
                        <span>Admin Panel</span>
                    </a>
                HTML;
            }

            $accountArea = <<<HTML
                {$btnAdmin}
                <div class="sidebar-user">
                    <a class="sidebar-avatar-link sidebar-profile-link" href="?action=profile&id={$userId}"
                       aria-label="View my profile"><span>{$profileAvatar}</span><span class="sidebar-profile-label">Profile</span></a>
                    <span class="sidebar-email">{$email}</span>
                    <a class="sidebar-signout" href="?action=signout" title="Sign out">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            HTML;
        } catch (AuthnException) {
            $accountArea = <<<HTML
                <div class="sidebar-user guest-user">
                    <a class="btn btn-outline-primary mobile-nav-auth" href="?action=signin" aria-label="Sign in">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                        <span>Sign in</span>
                    </a>
                    <a class="btn btn-primary mobile-nav-auth" href="?action=register" aria-label="Create an account">
                        <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                        <span>Sign up</span>
                    </a>
                </div>
            HTML;
        }

        return $sidebarLinks . $themeSwitch . $accountArea;
    }
}
