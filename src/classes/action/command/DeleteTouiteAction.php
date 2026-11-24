<?php
namespace iutnc\touiteur\action\command;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\action\Action;
use Override;

/**
 * Classe DeleteTouiteAction de l’application.
 */
class DeleteTouiteAction extends Action {
    protected bool $requireAuth = true;

    #[Override]
    public function post(): string {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id < 1) {
            return HtmlHelper::notFound(
                'Touite',
                'No touite was specified for deletion.',
                '?action=feed&type=global',
                'Back to feed'
            );
        }

        $r = RepositoryFactory::getReader();
        $touite = $r->findTouiteById($id);
        if ($touite === null) {
            return HtmlHelper::notFound('Touite', 'This touite no longer exists or has already been deleted.');
        }

        $author = $r->findUserByTouiteId($touite->getId());
        if ($author === null || (int) $author['id'] !== (int) $this->user['id']) {
            return HtmlHelper::forbidden(
                message: 'You do not have permission to delete this touite.',
                backLabel: 'Back to feed'
            );
        }

        $w = RepositoryFactory::getWriter();
        $w->deleteTouite($touite);
        header('Location: ?action=feed&type=global');
        exit;
    }
}
