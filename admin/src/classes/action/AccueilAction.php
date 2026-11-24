<?php

namespace iutnc\touiteur\admin\action;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\action\Action;
use Override;

/**
 * Page d'accueil du back-office.
 */
class AccueilAction extends Action {
    protected bool $requireAuth = true;

    #[Override]
    public function get(): string {
        $tagsIcon = '<span class="followed-tag-icon" aria-hidden="true"><i class="bi bi-hash"></i></span>';
        $usersIcon = '<span class="followed-tag-icon" aria-hidden="true"><i class="bi bi-people"></i></span>';
        $tagsLink = HtmlHelper::listLink(
            '?action=trendTags',
            $tagsIcon,
            'Popular tags',
            'The most frequent topics in touites'
        );
        $usersLink = HtmlHelper::listLink(
            '?action=trendUsers',
            $usersIcon,
            'Popular users',
            'Members with the most followers'
        );

        return <<<HTML
            <header class="feed-header">
                <div class="feed-heading">
                    <h1>Administration</h1>
                    <i class="bi bi-shield-lock-fill text-primary" aria-hidden="true"></i>
                </div>
            </header>
            <section class="tag-intro" aria-label="Welcome">
                <p class="tag-description">Welcome to the Touiteur back-office. Explore community trends.</p>
            </section>
            <nav class="followed-tags" aria-label="Administration tools">
                {$tagsLink}
                {$usersLink}
            </nav>
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
