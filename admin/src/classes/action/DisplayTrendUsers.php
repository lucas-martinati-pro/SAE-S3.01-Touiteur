<?php

namespace iutnc\touiteur\admin\action;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\action\Action;
use iutnc\touiteur\repository\RepositoryFactory;
use Override;

/**
 * Page des utilisateurs populaires du back-office.
 */
class DisplayTrendUsers extends Action {
    protected bool $requireAuth = true;

    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $header = HtmlHelper::detailHeader('Top 200 Influencers', '?');

        $users = $r->findTrendingUsers();

        if (empty($users)) {
            return $header
                . '<div class="followed-tags-empty">'
                . HtmlHelper::alert('info', 'There are no users')
                . '</div>';
        }

        $items = '';
        foreach ($users as $row) {
            $userId = (int) $row['id'];
            $followerCount = (int) $row['nbFol'];
            $touiteCount = (int) $row['counter_touite'];
            $followerLabel = $followerCount === 1 ? 'follower' : 'followers';
            $touiteLabel = $touiteCount === 1 ? 'touite' : 'touites';
            $avatar = HtmlHelper::profileAvatar($row);
            $fullName = trim($row['nom'] . ' ' . $row['prenom']);
            $handle = '@' . $row['nom'] . '_' . $row['prenom'];
            $meta = "{$followerCount} {$followerLabel} · {$touiteCount} {$touiteLabel}";

            $items .= HtmlHelper::listLink(
                "../index.php?action=profile&id={$userId}",
                $avatar,
                $fullName,
                $handle,
                $meta
            );
        }

        return $header . <<<HTML
            <section class="followed-tags" aria-label="Influencers">
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
