<?php

namespace iutnc\touiteur\dispatch;

use iutnc\touiteur\action\AddTouiteAction;
use iutnc\touiteur\action\DisplayFeedAction;
use iutnc\touiteur\action\DisplayProfile;
use iutnc\touiteur\action\DisplayTag;
use iutnc\touiteur\action\DisplayTouite;
use iutnc\touiteur\action\RegisterAction;
use iutnc\touiteur\action\SigninAction;
use iutnc\touiteur\action\SignoutAction;
use iutnc\touiteur\action\SubscribeAction;
use iutnc\touiteur\action\SubscribeTagAction;
use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\exception\AuthnException;
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
            "addTouite" => (new AddTouiteAction())(),
            "displayTouite" => (new DisplayTouite())(),
            "displayProfile" => (new DisplayProfile())(),
            "displayTag" => (new DisplayTag)(),
            "register" => (new RegisterAction())(),
            "signin" => (new SigninAction())(),
            "signout" => (new SignoutAction())(),
            "subscribe" => (new SubscribeAction())(),
            "subtag" => (new SubscribeTagAction())(),
            default => (new DisplayFeedAction())(),
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
        $r = RepositoryFactory::getReader();
        $tags = $r->findAllTags(10);

        $tagsHtml = "";

        foreach ($tags as $tag) {
            $tagsHtml .= <<<HTML
                <a href="?action=displayTag&id={$tag->getId()}">#{$tag->getLibelle()}</a>
            HTML;
        }

        echo <<<HTML
            <!DOCTYPE html>
            <html lang="fr" data-bs-theme="{$theme}" id="html">
                <head>
                    <title>Touiteur</title>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <!-- css pour Bootstrap -->
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
                    <!-- css pour les icônes Boostrap -->
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
                    <!-- css Google font -->
                    <link rel="preconnect" href="https://fonts.googleapis.com">
                    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
                    <link href="css/style.css" rel="stylesheet">
                    <!-- Icône personnalisée pour le site -->
                    <link rel="icon" type="image/x-icon" href="image/favicon.png">
                </head>
                <body>
                    <div class="app-layout" id="swup">
                        <aside class="sidebar">
                            <a class="navbar-brand fw-bold text-primary d-flex align-items-center mb-4" href="index.php">
                                <img src="image/favicon.png" alt="Logo" width="28" height="28" class="me-2">
                                Touiteur
                            </a>
                            {$navbar}
                        </aside>
                        <main class="page-content">
                            $html
                        </main>
                        <aside class="right-sidebar">
                            <section class="trends">
                                <h2>Tags populaires</h2>
                                <div class="tag-list">
                                    $tagsHtml
                                </div>
                            </section>
                        </aside>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
                    <script src="js/script.js"></script>
                </body>
            </html>
        HTML;
    }

    /**
     * Génère le code HTML de la barre de navigation Bootstrap.
     *
     * @return string Balises HTML de la barre de navigation.
     */
    private function renderNavbar(): string {
        $toggleThemeButton = <<<HTML
            <button id="toggle-theme" class="btn btn-outline-secondary btn-sm d-flex align-items-center" title="Changer de thème">
                <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/circle-half/ -->
                <i class="bi bi-circle-half"></i>
            </button>
        HTML;

        try {
            $user = AuthnProvider::getSignedInUser();

            $userId = (int) $user['id'];
            $email = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');

            return <<<HTML
                <div class="sidebar-links">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="?action=displayFeed&type=global">
                            Pour vous
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?action=displayFeed">
                            Abonnements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?action=displayProfile&id={$userId}">
                            Profil
                        </a>
                    </li>
                </ul>
                <div class="theme-switch">
                    {$toggleThemeButton}
                    <span>Changer le thème</span>
                </div>
                </div>
                <div class="sidebar-user">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/person-circle/ -->
                    <i class="bi bi-person-circle"></i>
                    <span class="sidebar-email">{$email}</span>
                    <a class="sidebar-signout" href="?action=signout" title="Déconnexion">
                        <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-right/ -->
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            HTML;
        } catch (AuthnException) {
            return <<<HTML
                <div class="sidebar-links">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/house-door-fill/ -->
                            <i class="bi bi-house-door-fill me-1"></i>Accueil
                        </a>
                    </li>
                </ul>
                </div>
                <div class="theme-switch">
                    {$toggleThemeButton}
                    <span>Changer le thème</span>
                </div>
                <div class="sidebar-user guest-user">
                    <a class="btn btn-sm btn-dark rounded-3" href="?action=signin">
                        <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-in-right/ -->
                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Connexion
                    </a>
                    <a class="btn btn-sm btn-primary rounded-3" href="?action=register">
                        <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/person-plus-fill/ -->
                        <i class="bi bi-person-plus-fill me-1"></i>
                        Inscription
                    </a>
                </div>
            HTML;
        }
    }
}