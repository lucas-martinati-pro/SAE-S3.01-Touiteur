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
class DisplayTouite extends Action {
    #[Override]
    public function get(): string {
        $reader = RepositoryFactory::getReader();
        $touite = $reader->findTouiteById((int) ($_GET['id'] ?? 0));

        $page = <<< HTML
            <header class="feed-header detail-header">
                <a class="back-link" href="?action=displayFeed" aria-label="Retour au feed">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1>Touite</h1>
            </header>
        HTML;

        if ($touite === null) {
            return $page . HtmlHelper::alert('info', 'Ce touite est introuvable.');
        }

        return $page . '<div class="touite-detail">' .
            RendererFactory::getRenderer($touite)->render(TypeRender::LONG) .
            '</div>';
    }
}