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
            return HtmlHelper::errorPage(
                title: 'Suppression impossible',
                message: 'Aucun touite n’a été indiqué pour la suppression.',
                backUrl: 'index.php?action=feed&type=global',
                backLabel: 'Retour au fil'
            );
        }

        $r = RepositoryFactory::getReader();
        $touite = $r->findTouiteById($_GET['id']);
        if ($touite === null) {
            return HtmlHelper::errorPage(
                title: 'Touite introuvable',
                message: 'Ce touite n’existe plus ou a déjà été supprimé.',
                backUrl: 'index.php',
                backLabel: 'Retour à l’accueil'
            );
        }

        $author = $r->findUserByTouiteId($touite->getId());
        if ($author['id'] !== $this->user['id']) {
            return HtmlHelper::errorPage(
                title: 'Accès refusé',
                message: "Vous n'avez pas les droits de supprimer de touite.",
                backUrl: 'index.php',
                backLabel: "Retour à l'accueil"
            );
        }

        $w = RepositoryFactory::getWriter();
        $w->deleteTouite($touite);
        header('Location: ?action=feed&type=global');
        exit;
    }
}
