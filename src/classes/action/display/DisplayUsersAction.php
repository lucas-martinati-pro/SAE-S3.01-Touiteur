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

        $backUrl = "?action=profile&id={$id}";
        $type = $_GET['type'] ?? 'followed';

        $header = match ($type) {
            'followers' => HtmlHelper::detailHeader('Abonnés', $backUrl),
            default => HtmlHelper::detailHeader('Abonnements', $backUrl),
        };

        $users = match ($type) {
            'followers' => $r->findFollowersByUserId($id),
            default => $r->findFollowedByUserId($id),
        };

        if (empty($users)) {
            return $header . match ($type) {
                'followers' => '<div class="followed-tags-empty">' . HtmlHelper::alert('info', 'Cet utilisateur n’a aucun abonné.') . '</div>',
                default => '<div class="followed-tags-empty">' . HtmlHelper::alert('info', 'Cet utilisateur ne suit personne.') . '</div>',
            };
        };

        $items = '';
        foreach ($users as $row) {
            $userId = (int) $row['id'];
            $avatar = HtmlHelper::profileAvatar($row);
            $fullName = trim($row['nom'] . ' ' . $row['prenom']);
            $handle = '@' . $row['nom'] . '_' . $row['prenom'];

            $items .= HtmlHelper::listLink(
                "?action=profile&id={$userId}",
                $avatar,
                $fullName,
                $handle
            );
        }

        $listLabel = $type === 'followers' ? 'Liste des abonnés' : 'Liste des abonnements';
        return $header . <<<HTML
            <section class="followed-tags" aria-label="{$listLabel}">
                {$items}
            </section>
        HTML;
    }
}
