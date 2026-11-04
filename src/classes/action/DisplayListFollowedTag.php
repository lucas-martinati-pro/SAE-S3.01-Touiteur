<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\RendererFactory;
use iutnc\touiteur\enum\TypeRender;
use iutnc\touiteur\pagination\Pagination;
use iutnc\touiteur\render\HtmlHelper;
use Override;

/**
 * Action par défaut affichant la page d'accueil de l'application Touiteur.
 */
class DisplayListFollowedTag extends Action {

    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = $this->user['id'];

        $listOfTag = $r->getAllFollowedTag($id);

        $page = "";

        foreach($listOfTag as $row){
            $page .= <<<HTML
                <p> {$row['libelle']}<p>
            HTML;
        }


        return $page;
    }
}