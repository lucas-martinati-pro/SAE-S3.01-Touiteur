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

        if ($followedTags === null) {
            return $header . '<div class="followed-tags-empty">' . HtmlHelper::alert('info', 'Cet utilisateur ne suit aucun tag.') . '</div>';
        }

        $items = '';
        foreach ($followedTags as $tag) {
            $tagId = (int) $tag->getId();
            $tagLabel = htmlspecialchars($tag->getLibelle(), ENT_QUOTES, 'UTF-8');
            $rawDescr = $tag['description'] ?? '';
            $descr = htmlspecialchars($rawDescr, ENT_QUOTES, 'UTF-8');
            $descrHtml = $descr !== ''
                ? "<div class=\"text-secondary small fw-normal\">{$descr}</div>"
                : '';

            $items .= <<<HTML
                <a href="?action=tag&id={$tagId}" class="followed-tag">
                    <span class="followed-tag-icon" aria-hidden="true">
                        <i class="bi bi-hash"></i>
                    </span>
                    <div class="followed-tag-label">
                        <div class="fw-bold">#{$tagLabel}</div>
                        {$descrHtml}
                    </div>
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