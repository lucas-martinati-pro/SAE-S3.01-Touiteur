<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\repository\RepositoryFactory;
use Override;

/**
 * Action affichant la liste des abonnés d'un utilisateur.
 */
class DisplayFollowersAction extends Action {

    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? $this->user['id']);

        $backUrl = $id > 0 ? "?action=displayProfile&id={$id}" : "?action=displayFeed";
        $header = HtmlHelper::detailHeader('Abonnés', $backUrl);

        $followers = $r->getFollowers($id);

        if ($followers === null || empty($followers)) {
            return $header . '<div class="followed-tags-empty">' . HtmlHelper::alert('info', 'Cet utilisateur n’a aucun abonné.') . '</div>';
        }

        $items = '';
        foreach ($followers as $row) {
            $followerId = (int) $row['id'];
            $nom = htmlspecialchars($row['nom'], ENT_QUOTES, 'UTF-8');
            $prenom = htmlspecialchars($row['prenom'], ENT_QUOTES, 'UTF-8');
            $completeName = "{$nom} {$prenom}";
            $completeNameId = "{$nom}_{$prenom}";
            $avatar = HtmlHelper::profileAvatar($row);

            $items .= <<<HTML
                <a href="?action=displayProfile&id={$followerId}" class="followed-tag">
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
            <section class="followed-tags" aria-label="Liste des abonnés">
                {$items}
            </section>
        HTML;
    }
}