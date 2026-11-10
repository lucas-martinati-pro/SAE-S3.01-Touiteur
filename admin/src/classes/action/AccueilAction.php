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
                <a href="?action=trendTags" class="followed-tag">
                    <span class="followed-tag-icon" aria-hidden="true"><i class="bi bi-hash"></i></span>
                    <span class="followed-tag-label">
                        <span class="d-block">Tags populaires</span>
                        <span class="text-secondary small fw-normal">Les sujets les plus présents dans les touites</span>
                    </span>
                    <i class="bi bi-chevron-right followed-tag-arrow" aria-hidden="true"></i>
                </a>
                <a href="?action=trendUsers" class="followed-tag">
                    <span class="followed-tag-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
                    <span class="followed-tag-label">
                        <span class="d-block">Utilisateurs populaires</span>
                        <span class="text-secondary small fw-normal">Les membres qui rassemblent le plus d’abonnés</span>
                    </span>
                    <i class="bi bi-chevron-right followed-tag-arrow" aria-hidden="true"></i>
                </a>
            </nav>
        HTML;
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
