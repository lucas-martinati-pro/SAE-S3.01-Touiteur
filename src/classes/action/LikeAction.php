<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\enum\TypeNote;
use iutnc\touiteur\render\HtmlHelper;
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

        $profileAvatar = HtmlHelper::profileAvatar($this->user);

        $page = <<< HTML
            <div class="compose-box">
                {$profileAvatar}
                <div>
                    <p>Quoi de neuf ?</p>
                    <a class="btn btn-primary" href="?action=addTouite">Touiter</a>
                </div>
            </div>
        HTML;

        header("Location: ?action=displayTouite&id=" . $id);
        exit;
    }
}