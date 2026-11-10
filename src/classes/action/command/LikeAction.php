<?php

namespace iutnc\touiteur\action\command;

use iutnc\touiteur\enum\TypeNote;
use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\action\Action;
use iutnc\touiteur\render\HtmlHelper;
use Override;

/**
 * Enregistre un vote et revient à la page qui affiche le touite.
 */
class LikeAction extends Action {
    #[Override]
    public function post(): string {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $typeLabel = $_POST['type'] ?? null;

        if ($id === false || $id === null || $id < 1 || !is_string($typeLabel)
            || !in_array($typeLabel, ['like', 'dislike', 'annuler'], true)) {
            return HtmlHelper::errorPage(
                title: 'Vote invalide',
                message: 'Le touite ou le type de vote est invalide.',
                backUrl: '?action=feed',
                backLabel: 'Retour au fil'
            );
        }

        $w = RepositoryFactory::getWriter();

        $type = TypeNote::fromLabel($typeLabel);

        $w->noteTouite($this->user['id'], $id, $type);
        header('Location: ?action=touite&id=' . $id);
        exit;
    }
}
