<?php

namespace iutnc\touiteur\admin\action;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\action\Action;
use iutnc\touiteur\repository\RepositoryFactory;
use Override;

/**
 * Affiche les tags les plus suivis dans le back-office.
 */
class DisplayTrendTags extends Action {
    protected bool $requireAuth = true;

    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $header = HtmlHelper::detailHeader('Top 200 Popular Tags', '?');

        $tags = $r->findTrendingTags();

        if (empty($tags)) {
            return $header
                . '<div class="followed-tags-empty">'
                . HtmlHelper::alert('info', 'There are no tags')
                . '</div>';
        }

        $items = '';
        foreach ($tags as $tag) {
            $id = (int) $tag['id'];
            $nbAparition = (int) $tag['nbAparition'];
            $appearanceLabel = $nbAparition === 1 ? 'appearance' : 'appearances';
            $icon = '<span class="followed-tag-icon" aria-hidden="true"><i class="bi bi-hash"></i></span>';
            $items .= HtmlHelper::listLink(
                "../index.php?action=tag&id={$id}",
                $icon,
                '#' . $tag['libelle'],
                $tag['description'] ?? '',
                "{$nbAparition} {$appearanceLabel}"
            );
        }

        return $header . <<<HTML
            <section class="followed-tags" aria-label="Most popular tags">
                {$items}
            </section>
        HTML;
    }

    #[Override]
    protected function check(): ?string {
        if (!AuthnProvider::isAdminUser()) {
            return HtmlHelper::forbidden(
                message: 'You do not have the required permissions to access this page.',
                backUrl: '../index.php',
                backLabel: 'Back to home'
            );
        }

        return null;
    }
}
