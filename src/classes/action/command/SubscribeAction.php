<?php

namespace iutnc\touiteur\action\command;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\action\Action;
use Override;

/**
 * Action pour s'abonner ou se desabonner
 */
class SubscribeAction extends Action {

    #[Override]
    public function post(): string {
        $id = $_POST['id'] ?? null;
        $type = $_POST['type'] ?? null;

        // redirection si l'id est null
        if (!$id) {
            header("Location: ?action=displayFeed");
        exit;
        }

        $r = RepositoryFactory::getWriter();

        if ($type === "sub"){
            $r->follow($this->user['id'], $id);
        } else if ($type === "unsub"){
            $r->unfollow($this->user['id'], $id);
        }

        // Redirection direct vers l'affichage d'un profil
        header("Location: ?action=displayProfile&id=" . $id);
        exit;
    }
}