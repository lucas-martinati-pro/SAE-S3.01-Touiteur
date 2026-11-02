<?php

namespace iutnc\touiteur\dispatch;

use iutnc\touiteur\action\DefaultAction;
use iutnc\touiteur\action\RegisterAction;
use iutnc\touiteur\action\SigninAction;
use iutnc\touiteur\action\SignoutAction;
use iutnc\touiteur\audio\tracks\AlbumTrack;
use iutnc\touiteur\audio\tracks\PodcastTrack;
use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\exception\AuthnException;
use iutnc\touiteur\config\Config;
use iutnc\touiteur\repository\TouiteurRepository;

/**
 * Contrôleur de l'affichage.
 */
class Dispatcher {
    /**
     * Identifiant textuel de l'action à exécuter (ex: 'playlists', 'signin', etc.).
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
    public function run() : void {
        $html = match ($this->action) {
            "register" => (new RegisterAction())(),
            "signin" => (new SigninAction())(),
            "signout" => (new SignoutAction())(),
            default => (new DefaultAction())(),
        };

        $this->renderPage($html);
    }

    /**
     * Génère et affiche la structure HTML complète de la page web.
     *
     * @param string $html Contenu HTML généré par l'action courante.
     * @return void
     */
    private function renderPage(string $html) : void {
        $navbar = $this->renderNavbar();
        $theme = (($_COOKIE['theme'] ?? 'dark') === 'light') ? 'light' : 'dark';

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
                    <!-- Script pour l'audio -->
                    <script type="module" src="https://cdn.jsdelivr.net/npm/player.style/tailwind-audio/+esm"></script>
                    <!-- Script pour l'audio, Swup : change de page sans couper la musique -->
                    <script src="https://unpkg.com/swup@4"></script>
                    <script>
                      const swup = new Swup();
                    </script>
                </head>
                <body class="d-flex flex-column min-vh-100">
                    <!-- Contenu rechargé à chaque page, le lecteur audio est en dehors donc il continue -->
                    <div class="flex-grow-1" id="swup">
                        <nav class="navbar navbar-expand-lg">
                            <div class="container">
                                <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="index.php">
                                    <img src="image/favicon.png" alt="Logo" width="24" height="24" class="me-2">
                                    Touiteur
                                </a>
                                <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarNav">
                                    {$navbar}
                                </div>
                            </div>
                        </nav>

                        <main class="container py-4">
                            $html
                        </main>
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
    private function renderNavbar() : string {
        $toggleThemeButton = <<<HTML
            <button id="toggle-theme" class="btn btn-outline-secondary btn-sm d-flex align-items-center" title="Changer de thème">
                <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/circle-half/ -->
                <i class="bi bi-circle-half"></i>
            </button>
        HTML;

        $activePlaylists = '';
        $activeTracks = '';
        $activeAddPlaylist = '';
        $activeCurrentPl = '';
        $activeAccueil = '';

        switch ($this->action) {
            case 'playlists' : {
                $activePlaylists = 'active';
                break;
            }
            case 'tracks' : {
                $activeTracks = 'active';
                break;
            }
            case 'add-playlist' : {
                $activeAddPlaylist = 'active';
                break;
            }
            case 'display-playlist' : {
                $activeCurrentPl = 'active';
                break;
            }
            case 'default' : $activeAccueil = 'active';
        }

        try {
            $user = AuthnProvider::getSignedInUser();

            $currentPlaylist = "";

            if (isset($_SESSION['playlist'])) {
                $currentPlaylist = <<<HTML
                    <li class="nav-item">
                        <a class="nav-link {$activeCurrentPl}" href="?action=display-playlist">
                            <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/music-note-list/ -->
                            <i class="bi bi-music-note-list me-1"></i>Playlist courante
                        </a>
                    </li>
                HTML;
            }

            return <<<HTML
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {$activeAccueil}" href="index.php">
                            <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/house-door-fill/ -->
                            <i class="bi bi-house-door-fill me-1"></i>Accueil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {$activePlaylists}" href="?action=playlists">
                            <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/collection-play-fill/ -->
                            <i class="bi bi-collection-play-fill me-1"></i>Mes playlists
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {$activeTracks}" href="?action=tracks">
                            <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/music-note/ -->
                            <i class="bi bi-music-note me-1"></i>Mes pistes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {$activeAddPlaylist}" href="?action=add-playlist">
                            <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/plus-circle-fill/ -->
                            <i class="bi bi-plus-circle-fill me-1"></i>Créer une playlist
                        </a>
                    </li>
                    {$currentPlaylist}
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/person-circle/ -->
                    <i class="bi bi-person-circle"></i>
                    <span class="navbar-text small text-muted me-2">{$user['email']}</span>
                    <a class="btn btn-danger btn-sm signout rounded-3" href="?action=signout">
                        <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-right/ -->
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Déconnexion
                    </a>
                    {$toggleThemeButton}
                </div>
            HTML;
        } catch (AuthnException) {
            return <<<HTML
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {$activeAccueil}" href="index.php">
                            <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/house-door-fill/ -->
                            <i class="bi bi-house-door-fill me-1"></i>Accueil
                        </a>
                    </li>
                </ul>
                <div class="d-flex gap-2 align-items-center">
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
                    {$toggleThemeButton}
                </div>
            HTML;
        }
    }
}