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


        $backUrl = "?action=displayProfile";

        $header = HtmlHelper::detailHeader('Influenceur', $backUrl);

        $users = $r->getTrendUser();

        if ($users === null || empty($users)) {
            return $header . 
            '<div class="followed-tags-empty">' . 
            HtmlHelper::alert('info', 'Il n\'y a aucun utilisateurs') . '</div>';
        };

        $items = '';
        foreach ($users as $row) {
            $userId = (int) $row['id'];
            $nbFollow = (int) $row['nbFol'];
            $nbTouite = (int) $row['counter_touite'];
            $abonneLabel = $nbFollow > 1 ? 'abonnés' : 'abonné';
            $touiteLabel = $nbTouite > 1 ? 'touites' : 'touite';
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
                        <div class="d-flex align-items-center flex-wrap gap-3 mt-1 text-secondary small fw-normal">
                            <span><i class="bi bi-people me-1"></i><strong>{$nbFollow}</strong> {$abonneLabel}</span>
                            <span><i class="bi bi-chat-left-text me-1"></i><strong>{$nbTouite}</strong> {$touiteLabel}</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right followed-tag-arrow" aria-hidden="true"></i>
                </a>
            HTML;
        }

        return $header . <<<HTML
            <section class="followed-tags" aria-label="Influenceurs">
                {$items}
            </section>
        HTML;
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
