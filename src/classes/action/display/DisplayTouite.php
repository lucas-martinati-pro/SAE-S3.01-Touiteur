<?php

namespace iutnc\touiteur\action\display;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\RendererFactory;
use iutnc\touiteur\enum\TypeRender;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\action\Action;
use Override;

/**
 * Action par défaut affichant la page d'accueil de l'application Touiteur.
 */
class DisplayTouite extends Action {
    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();
        $touite = $r->findTouiteById((int) ($_GET['id'] ?? 0));

        $page = HtmlHelper::detailHeader('Touite');

        if ($touite === null) {
            return HtmlHelper::notFound('Touite', 'Ce touite est introuvable.');
        }

        return $page . '<div class="touite-detail">' .
            RendererFactory::getRenderer($touite)->render(TypeRender::LONG) .
            '</div>';
    }
}
