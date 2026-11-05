<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\repository\RepositoryFactory;
use Override;

/**
 * Action affichant la liste des abonnements d'un utilisateur.
 */
class DisplayFollowedAction extends Action {

    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? $this->user['id']);

        $backUrl = $id > 0 ? "?action=displayProfile&id={$id}" : "?action=displayFeed";
        $header = HtmlHelper::detailHeader('Abonnements', $backUrl);

        $followed = $r->getFollowed($id);

        if ($followed === null || empty($followed)) {
            return $header . '<div class="followed-tags-empty">' . HtmlHelper::alert('info', 'Cet utilisateur ne suit personne.') . '</div>';
        }

        $items = '';
        foreach ($followed as $row) {
            $followedId = (int) $row['id'];
            $nom = htmlspecialchars($row['nom'], ENT_QUOTES, 'UTF-8');
            $prenom = htmlspecialchars($row['prenom'], ENT_QUOTES, 'UTF-8');
            $completeName = "{$nom} {$prenom}";
            $completeNameId = "{$nom}_{$prenom}";
            $avatar = HtmlHelper::profileAvatar($row);

            $items .= <<<HTML
                <a href="?action=displayProfile&id={$followedId}" class="followed-tag">
                    {$avatar}
                    <div class="followed-tag-label">
                        <div class="fw-bold">{$completeName}</div>
                        <div class="text-secondary small fw-normal">@{$completeNameId}</div>
                    </div>
                    <i class="bi bi-chevron-right followed-tag-arrow" aria-hidden="true"></i>
                </a>
            HTML;
        }

        return $header . <<<HTML
            <section class="followed-tags" aria-label="Liste des abonnements">
                {$items}
            </section>
        HTML;
    }
}