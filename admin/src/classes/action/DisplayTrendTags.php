<?php

namespace iutnc\touiteur\admin\action;

use \iutnc\touiteur\tag\Tag;
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

        $header = HtmlHelper::detailHeader('Famous-Tag', '?');

        $tags = $r->findTrendingTags();

        if ($tags === null || empty($tags)) {
            return $header
                . '<div class="followed-tags-empty">'
                . HtmlHelper::alert('info', 'Il n\'y a aucun tag')
                . '</div>';
        }

         $items = '';
        foreach ($tags as $tag) {
            $libelle = $tag['libelle'];
            $descr = $tag['description'];
            $id = $tag['id'];
            $nbAparition = $tag['nbAparition'];

            $items .= <<<HTML
                <a href="../index.php?action=profile&id={$id}" class="followed-tag">
                    $libelle;
                    $descr;
                    $id;
                    $nbAparition;
                </a>
            HTML;
        }

        return '';
    }

    #[Override]
    protected function check(): ?string {
        if (!AuthnProvider::isAdminUser()) {
            return HtmlHelper::errorPage(
                title: 'Accès refusé',
                message: "Vous n'avez pas les droits nécessaires pour accéder à cette page.",
                backUrl: 'index.php',
                backLabel: "Retour à l'accueil"
            );
        }

        return null;
    }
}
