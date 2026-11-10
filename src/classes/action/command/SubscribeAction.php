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
            header('Location: ?action=feed');
            exit;
        }

        $w = RepositoryFactory::getWriter();

        if ($type === 'sub'){
            $w->followUser($this->user['id'], $id);
        } else if ($type === 'unsub'){
            $w->unfollowUser($this->user['id'], $id);
        }

        // Redirection direct vers l'affichage d'un profil
        header('Location: ?action=profile&id=' . $id);
        exit;
    }
}