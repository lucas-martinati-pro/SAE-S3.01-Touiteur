<?php

namespace iutnc\touiteur\action\display;

use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\action\Action;
use Override;

/**
 * Action affichant la liste des tags suivis par un utilisateur.
 */
class DisplayListFollowedTag extends Action {

    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? 0);

        $backUrl = $id > 0 ? "?action=profile&id={$id}" : '?action=feed';
        $followedTags = $r->findAllFollowedTagByUserId($id);

        $header = HtmlHelper::detailHeader('Tags suivis', $backUrl);

        $items = '';
        foreach ($followedTags as $tag) {
            $tagId = (int) $tag->getId();
            $icon = '<span class="followed-tag-icon" aria-hidden="true"><i class="bi bi-hash"></i></span>';

            $items .= HtmlHelper::listLink(
                "?action=tag&id={$tagId}",
                $icon,
                '#' . $tag->getLibelle(),
                $tag->getDescription() ?? ''
            );
        }

        return $header . <<<HTML
            <section class="followed-tags" aria-label="Liste des tags suivis">
                {$items}
            </section>
        HTML;
    }
}
