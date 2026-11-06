<?php

namespace iutnc\touiteur\admin\action;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\action\Action;
use Override;

/**
 * Page des utilisateurs populaires du back-office.
 */
class DisplayTrendUsers extends Action {
    protected bool $requireAuth = true;

    #[Override]
    public function get(): string {
        return '';
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
