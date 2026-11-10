<?php
namespace iutnc\touiteur\action\command;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\action\Action;
use Override;

class DeleteTouiteAction extends Action {
    protected bool $requireAuth = true;

    #[Override]
    public function post(): string {
        if (!isset($_GET['id'])) {
            return HtmlHelper::notFound(
                'Touite',
                'Aucun touite n’a été indiqué pour la suppression.',
                '?action=feed&type=global',
                'Retour au fil'
            );
        }

        $r = RepositoryFactory::getReader();
        $touite = $r->findTouiteById($_GET['id']);
        if ($touite === null) {
            return HtmlHelper::notFound('Touite', 'Ce touite n’existe plus ou a déjà été supprimé.');
        }

        $author = $r->findUserByTouiteId($touite->getId());
        if ($author === null || (int) $author['id'] !== (int) $this->user['id']) {
            return HtmlHelper::forbidden(
                message: "Vous n'avez pas les droits de supprimer ce touite.",
                backUrl: '?action=feed',
                backLabel: 'Retour au fil'
            );
        }

        $w = RepositoryFactory::getWriter();
        $w->deleteTouite($touite);
        header('Location: ?action=feed&type=global');
        exit;
    }
}
