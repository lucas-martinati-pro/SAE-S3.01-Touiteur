<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\enum\TypeNote;
use iutnc\touiteur\repository\RepositoryFactory;
use Override;

/**
 * Action par défaut affichant la page d'accueil de l'application Touiteur.
 */
class LikeAction extends Action {
    #[Override]
    public function post(): string {
        $w = RepositoryFactory::getWriter();

        $id = (isset($_GET['id'])) ? $_GET['id'] : 1;
        $type = (isset($_GET['type'])) ? TypeNote::fromLabel($_GET['type']) : TypeNote::annuler;

        $w->noteTouite($this->user['id'], $id, $type);

        $page = <<< HTML
            <div class="compose-box">
                <div class="touite-avatar">T</div>
                <div>
                    <p>Quoi de neuf ?</p>
                    <a class="btn btn-primary" href="?action=addTouite">Touiter</a>
                </div>
            </div>
        HTML;

        $feedQuery = $feedType === 'global' ? '&type=global' : '';
        $previous = $pagination->previous();
        $next = $pagination->next();
        $pageNumber = $pagination->getPage();
        $pagination = HtmlHelper::pagination(
            'Pagination du feed',
            "?action=displayFeed{$feedQuery}&page={$previous}",
            "?action=displayFeed{$feedQuery}&page={$next}",
            $pageNumber
        );

        if ($feed === null) {
            return $page . HtmlHelper::alert('info', 'Aucun touite à afficher pour le moment.') . $pagination;
        }

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT) . $pagination;

        header("Location: ?action=displayTouite&id=" . $touite->getId());
        exit;
    }
}