<?php

namespace iutnc\touiteur\action\display;

use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\action\Action;
use Override;

/**
 * Action affichant la liste des abonnés d'un utilisateur.
 */
class DisplayUsersAction extends Action {

    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? $this->user['id']);

        $backUrl = "?action=displayProfile&id={$id}";
        $type = $_GET['type'] ?? 'followed';

        $header = match ($type) {
            'followers' => HtmlHelper::detailHeader('Abonnés', $backUrl),
            default => HtmlHelper::detailHeader('Abonnements', $backUrl),
        };

        $users = match ($type) {
            'followers' => $r->getFollowers($id),
            default => $r->getFollowed($id),
        };

        if ($users === null || empty($users)) {
            return $header . match ($type) {
                'followers' => '<div class="followed-tags-empty">' . HtmlHelper::alert('info', 'Cet utilisateur n’a aucun abonné.') . '</div>',
                default => '<div class="followed-tags-empty">' . HtmlHelper::alert('info', 'Cet utilisateur ne suit personne.') . '</div>',
            };
        };

        $items = '';
        foreach ($users as $row) {
            $userId = (int) $row['id'];
            $nom = htmlspecialchars($row['nom'], ENT_QUOTES, 'UTF-8');
            $prenom = htmlspecialchars($row['prenom'], ENT_QUOTES, 'UTF-8');
            $completeName = "{$nom} {$prenom}";
            $completeNameId = "{$nom}_{$prenom}";
            $avatar = HtmlHelper::profileAvatar($row);

            $items .= <<<HTML
                <a href="?action=displayProfile&id={$userId}" class="followed-tag">
                    {$avatar}
                    <div class="followed-tag-label">
                        <div class="fw-bold">{$completeName}</div>
                        <div class="text-secondary small fw-normal">@{$completeNameId}</div>
                    </div>
                    <i class="bi bi-chevron-right followed-tag-arrow" aria-hidden="true"></i>
                </a>
            HTML;
        }

        $listLabel = $type === 'followers' ? 'Liste des abonnés' : 'Liste des abonnements';
        return $header . <<<HTML
            <section class="followed-tags" aria-label="{$listLabel}">
                {$items}
            </section>
        HTML;
    }
}
