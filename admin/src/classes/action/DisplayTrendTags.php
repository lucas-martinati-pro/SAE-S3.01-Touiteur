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

        $header = HtmlHelper::detailHeader('Les 200 Tags les plus populaires', '?');

        $tags = $r->findTrendingTags();

        if (!$tags) {
            return $header
                . '<div class="followed-tags-empty">'
                . HtmlHelper::alert('info', 'Il n\'y a aucun tag')
                . '</div>';
        }

        $items = '';
        foreach ($tags as $tag) {
            $id = (int) $tag['id'];
            $nbAparition = (int) $tag['nbAparition'];
            $appearanceLabel = $nbAparition > 1 ? 'apparitions' : 'apparition';
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
            <section class="followed-tags" aria-label="Tags les plus populaires">
                {$items}
            </section>
        HTML;
    }

    #[Override]
    protected function check(): ?string {
        if (!AuthnProvider::isAdminUser()) {
            return HtmlHelper::forbidden(
                message: "Vous n'avez pas les droits nécessaires pour accéder à cette page.",
                backUrl: '../index.php',
                backLabel: "Retour à l'accueil"
            );
        }

        return null;
    }
}
