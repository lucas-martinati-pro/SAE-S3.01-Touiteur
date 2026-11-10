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
        $state = $_POST['state'] ?? null;
        $type = $_POST['type'] ?? null;

        // redirection si l'id est null
        if (!$id) {
            header('Location: ?action=feed');
            exit;
        }

        $w = RepositoryFactory::getWriter();

        if ($state === 'sub') {
            match ($type) {
                'user' => $w->followUser($this->user['id'], $id),
                'tag' => $w->followTag($this->user['id'], $id),
            };
        } else if ($state === 'unsub') {
            match ($type) {
                'user' => $w->unfollowUser($this->user['id'], $id),
                'tag' => $w->unfollowTag($this->user['id'], $id),
            };
        }

        // Redirection direct vers l'affichage d'un profil
        match ($type) {
            'user' => header('Location: ?action=profile&id=' . $id),
            'tag' => header('Location: ?action=tag&id=' . $id),
            default => header('Location: ?action=feed'),
        };
        exit;
    }
}