<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\enum\TypeNote;
use iutnc\touiteur\repository\RepositoryFactory;
use Override;

/**
 * Enregistre un vote et revient à la page qui affiche le touite.
 */
class LikeAction extends Action {
    #[Override]
    public function post(): string {
        $w = RepositoryFactory::getWriter();

        $id = (isset($_GET['id'])) ? $_GET['id'] : 1;
        $type = (isset($_GET['type'])) ? TypeNote::fromLabel($_GET['type']) : TypeNote::annuler;

        $w->noteTouite($this->user['id'], $id, $type);
        header("Location: ?action=displayTouite&id=" . $id);
        exit;
    }
}
