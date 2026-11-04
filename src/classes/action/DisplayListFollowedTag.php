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
            return $header . '<div class="followed-tags-empty">' . HtmlHelper::alert('info', 'Cet utilisateur ne suit aucun tag.') . '</div>';
        }

        $items = '';
        foreach ($listOfTag as $row) {
            $tagId = (int) $row['id'];
            $libelle = htmlspecialchars($row['libelle'], ENT_QUOTES, 'UTF-8');

            $items .= <<<HTML
                <a href="?action=displayTag&id={$tagId}" class="followed-tag">
                    <span class="followed-tag-icon" aria-hidden="true">
                        <i class="bi bi-hash"></i>
                    </span>
                    <span class="followed-tag-label">#{$libelle}</span>
                    <i class="bi bi-chevron-right followed-tag-arrow" aria-hidden="true"></i>
                </a>
            HTML;
        }

        return $header . <<<HTML
            <section class="followed-tags" aria-label="Liste des tags suivis">
                {$items}
            </section>
        HTML;
    }
}