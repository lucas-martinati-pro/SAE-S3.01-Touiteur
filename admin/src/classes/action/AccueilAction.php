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
            'Tags populaires',
            'Les sujets les plus présents dans les touites'
        );
        $usersLink = HtmlHelper::listLink(
            '?action=trendUsers',
            $usersIcon,
            'Utilisateurs populaires',
            'Les membres qui rassemblent le plus d’abonnés'
        );

        return <<<HTML
            <header class="feed-header">
                <div class="feed-heading">
                    <h1>Administration</h1>
                    <i class="bi bi-shield-lock-fill text-primary" aria-hidden="true"></i>
                </div>
            </header>
            <section class="tag-intro" aria-label="Bienvenue">
                <p class="tag-description">Bienvenue dans le back-office de Touiteur. Consultez les tendances de la communauté.</p>
            </section>
            <nav class="followed-tags" aria-label="Outils d’administration">
                {$tagsLink}
                {$usersLink}
            </nav>
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
