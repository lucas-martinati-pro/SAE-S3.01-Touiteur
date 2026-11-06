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
        $tagsButton = HtmlHelper::linkButton('Tags populaires', '?action=displayTrendTags', 'bi-hash');
        $usersButton = HtmlHelper::linkButton('Utilisateurs populaires', '?action=displayTrendUsers', 'bi-people');

        return HtmlHelper::title('Administration', 'bi-speedometer2')
            . '<p>Bienvenue dans le back-office de Touiteur.</p>'
            . '<div class="d-flex gap-2">' . $tagsButton . $usersButton . '</div>';
    }

    #[Override]
    protected function check(): ?string {
        if (!AuthnProvider::isAdminUser()) {
            return HtmlHelper::errorPage(
                title: "Accès refusé",
                message: "Vous n'avez pas les droits nécessaires pour accéder à cette page.",
                backUrl: "index.php",
                backLabel: "Retour à l'accueil"
            );
        }

        return null;
    }
}
