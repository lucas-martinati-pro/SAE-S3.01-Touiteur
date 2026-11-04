<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\repository\RepositoryFactory;
use Override;

/**
 * Action affichant la liste des tags suivis par un utilisateur.
 */
class DisplayListFollowedTag extends Action {

    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? 0);
        $backUrl = $id > 0 ? "?action=displayProfile&id={$id}" : "?action=displayFeed";

        $listOfTag = $r->getAllFollowedTag($id);

        $header = HtmlHelper::detailHeader('Tags suivis', $backUrl);

        if ($listOfTag === null) {
            return $header . '<div class="p-3">' . HtmlHelper::alert('info', 'Cet utilisateur ne suit aucun tag.') . '</div>';
        }

        $items = '';
        foreach ($listOfTag as $row) {
            $tagId = (int) $row['id'];
            $libelle = $row['libelle'];

            $items .= <<<HTML
                <a href="?action=displayTag&id={$tagId}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-hash fs-5"></i>
                        </div>
                        <span class="fw-bold text-body">#{$libelle}</span>
                    </div>
                    <i class="bi bi-chevron-right text-secondary"></i>
                </a>
            HTML;
        }

        return $header . "<div class=\"list-group list-group-flush\">{$items}</div>";
    }
}