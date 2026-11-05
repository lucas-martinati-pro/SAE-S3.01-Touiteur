<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\repository\RepositoryFactory;
use Override;

/**
 * Action affichant la liste des tags suivis par un utilisateur.
 */
class DisplayFollowedAction extends Action {

    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = $this->user['id'];

        $backUrl = $id > 0 ? "?action=displayProfile&id={$id}" : "?action=displayFeed";

        $followers = $r->getFollowed($id);

        $header = HtmlHelper::detailHeader('Mon compte', $backUrl);

        if ($followers === null) {
            return $header . '<div class="followed-tags-empty">' . HtmlHelper::alert('info', 'Cet utilisateur ne follow perosnne.') . '</div>';
        }

        $items = '';
        foreach ($followers as $row) {
            $follower_id = (int) $row['id'];
            $nom = htmlspecialchars($row['nom'], ENT_QUOTES, 'UTF-8');
            $prenom = htmlspecialchars($row['prenom'], ENT_QUOTES, 'UTF-8');

            $items .= <<<HTML
                <a href="?action=displayProfile&id={$follower_id}" class="">
                    <span class="" aria-hidden="true">
                        <i class=""></i>
                    </span>
                    <span class="">@{$nom}_{$prenom}</span>
                    <i class="" aria-hidden="true"></i>
                </a>
            HTML;
        }

        return $header . <<<HTML
            <section class="" aria-label="Liste des personnes que je follow">
                {$items}
            </section>
        HTML;
    }
}