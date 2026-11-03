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
        if (isset($_GET['type']) && $_GET['type'] == 'global') {
            $feed = $r->findAllTouites();
            $activeGlobal = 'class="active"';
        } else {
            $feed = $r->findFollowedFeed($this->user['id']);
            $activeFollow = 'class="active"';
        }

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

        if ($feed === null) {
            return $page . HtmlHelper::alert('info', 'Aucun touite à afficher pour le moment.');
        }

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT);
    }

    #[Override]
    public function post(): string {
        return '';
    }
}