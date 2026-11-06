<?php

namespace iutnc\touiteur\admin\dispatch;

use iutnc\touiteur\admin\action\DisplayTrendUsers;
use iutnc\touiteur\admin\action\DisplayTrendTags;
use iutnc\touiteur\admin\action\AccueilAction;
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
class AdminDispatcher {
    /**
     * Identifiant textuel de l'action à exécuter (ex: 'signin', etc.).
     */
    private string $action;

    /**
     * Initialise le Dispatcher avec le nom de l'action demandée.
     *
     * @param string $action Nom de l'action (par défaut 'default').
     */
    public function __construct(string $action) {
        $this->action = $action;
    }

    /**
     * Point d'entrée de l'exécution du Dispatcher.
     *
     * @return void
     */
    public function run(): void {
        $html = match ($this->action) {
            'trendUsers' => (new DisplayTrendUsers())(),
            'trendTags' => (new DisplayTrendTags())(),
            'register' => (new RegisterAction())(),
            'signin' => (new SigninAction())(),
            'signout' => (new SignoutAction())(),
            default => (new AccueilAction())(),
        };

        $this->renderPage($html);
    }

    /**
     * Génère et affiche la structure HTML complète de la page web.
     *
     * @param string $html Contenu HTML généré par l'action courante.
     * @return void
     */
    private function renderPage(string $html): void {
        $navbar = $this->renderNavbar();
        $theme = (($_COOKIE['theme'] ?? 'dark') === 'light') ? 'light' : 'dark';

        $tagsHtml = $this->renderSideBar();

        echo <<<HTML
            <!DOCTYPE html>
            <html lang="fr" data-bs-theme="{$theme}" id="html">
                <head>
                    <title>Touiteur - Admin</title>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <!-- css pour Bootstrap -->
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
                    <!-- css pour les icônes Boostrap -->
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
                    <link href="../css/style.css" rel="stylesheet">
                    <!-- Icône personnalisée pour le site -->
                    <link rel="icon" type="image/x-icon" href="image/favicon.png">
                </head>
                <body>
                    <div class="app-layout" id="swup">
                        <aside class="sidebar">
                            <a class="navbar-brand fw-bold text-primary d-flex align-items-center mb-4" href="index.php">
                                <img src="../image/favicon.png" alt="" width="28" height="28" class="me-2">
                                <span class="logo-word">Touiteur - Admin</span>
                            </a>
                            {$navbar}
                        </aside>
                        <main class="page-content">
                            $html
                        </main>
                        $tagsHtml
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
                    <script src="../js/script.js"></script>
                </body>
            </html>
        HTML;
    }

    private function renderSideBar(): string {
        try {
            AuthnProvider::getSignedInUser();

            $tagsHtml = '';
            $r = RepositoryFactory::getReader();
            $tags = $r->findAllTags(10);
            foreach ($tags as $tag) {
                $tagsHtml .= <<<HTML
                    <a href="../index.php?action=tag&id={$tag->getId()}">#{$tag->getLibelle()}</a>
                HTML;
            }

            $searchValue = htmlspecialchars(trim($_GET['search'] ?? ''), ENT_QUOTES, 'UTF-8');
            $search = <<<HTML
                <form method="post" action="../index.php?action=feed" class="search-form mb-3" role="search">
                    <label for="search" class="visually-hidden">Rechercher un touite</label>
                    <input
                        type="search"
                        name="search"
                        id="search"
                        value="{$searchValue}"
                        class="form-control search-input"
                        placeholder="Rechercher un touite"
                        aria-label="Rechercher un touite"
                        required
                    >
                    <button type="submit" class="search-button" aria-label="Rechercher">
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </button>
                </form>
            HTML;

            return <<<HTML
                <aside class="right-sidebar">
                    {$search}
                    <section class="trends">
                        <h2>Tags populaires</h2>
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
     * Génère le code HTML de la barre de navigation Bootstrap.
     *
     * @return string Balises HTML de la barre de navigation.
     */
    private function renderNavbar(): string {
        $sidebarLinks = <<<HTML
            <div class="sidebar-links">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-speedometer2 me-1"></i>Administration
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?action=trendTags">
                            <i class="bi bi-hash me-1"></i>Tags populaires
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?action=trendUsers">
                            <i class="bi bi-people me-1"></i>Utilisateurs populaires
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="bi bi-house-door-fill me-1"></i>Site public
                        </a>
                    </li>
                </ul>
            </div>
        HTML;
        $themeSwitch = <<<HTML
            <div class="theme-switch">
                <button id="toggle-theme" class="theme-toggle" type="button" aria-label="Changer de thème">
                <i class="bi bi-circle-half theme-toggle-icon" aria-hidden="true"></i>
                <span class="theme-toggle-label">Changer de thème</span>
            </button>
            </div>
        HTML;

        try {
            $user = AuthnProvider::getSignedInUser();

            $userId = (int) $user['id'];
            $email = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
            $profileAvatar = HtmlHelper::profileAvatar($user, 'sidebar-avatar');

            $accountArea = <<<HTML
                <div class="sidebar-user">
                    <a class="sidebar-avatar-link" href="../index.php?action=profile&id={$userId}"
                       aria-label="Voir mon profil">{$profileAvatar}</a>
                    <span class="sidebar-email">{$email}</span>
                    <a class="sidebar-signout" href="?action=signout" title="Déconnexion">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            HTML;
        } catch (AuthnException) {
            $accountArea = <<<HTML
                <div class="sidebar-user guest-user">
                    <a class="btn btn-outline-primary" href="?action=signin">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                        Se connecter
                    </a>
                    <a class="btn btn-primary" href="?action=register">
                        <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                        Créer un compte
                    </a>
                </div>
            HTML;
        }

        return $sidebarLinks . $themeSwitch . $accountArea;
    }
}
