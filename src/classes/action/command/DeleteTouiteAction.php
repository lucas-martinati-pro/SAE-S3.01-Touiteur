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
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id < 1) {
            return HtmlHelper::notFound(
                'Touite',
                'Aucun touite n’a été indiqué pour la suppression.',
                '?action=feed&type=global',
                'Retour au fil'
            );
        }

        $r = RepositoryFactory::getReader();
        $touite = $r->findTouiteById($id);
        if ($touite === null) {
            return HtmlHelper::notFound('Touite', 'Ce touite n’existe plus ou a déjà été supprimé.');
        }

        $author = $r->findUserByTouiteId($touite->getId());
        if ($author === null || (int) $author['id'] !== (int) $this->user['id']) {
            return HtmlHelper::forbidden(
                message: "Vous n'avez pas les droits de supprimer ce touite.",
                backLabel: 'Retour au fil'
            );
        }

        $w = RepositoryFactory::getWriter();
        $w->deleteTouite($touite);
        header('Location: ?action=feed&type=global');
        exit;
    }
}
