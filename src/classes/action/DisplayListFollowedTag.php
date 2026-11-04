<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\RepositoryFactory;
use Override;

/**
 * Action par défaut affichant la page d'accueil de l'application Touiteur.
 */
class DisplayListFollowedTag extends Action {

    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? 0);

        $listOfTag = $r->getAllFollowedTag($id);
        $page = "";

        if ($listOfTag !== null) {
            foreach ($listOfTag as $row) {
                
                $page .= <<<HTML
                    <a href="?action=displayTag&id={$row['id']}"> <p>{$row['libelle']}</p> </a>
                HTML;
            }
        } else {
            $page .= "<p>Cet utilisateur ne suit aucun tag.</p>";
        }


        return $page;
    }
}