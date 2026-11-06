<?php

namespace iutnc\touiteur\action\display;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\RendererFactory;
use iutnc\touiteur\enum\TypeRender;
use iutnc\touiteur\pagination\Pagination;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\action\Action;
use Override;

/**
 * Action affichant le profil d'un utilisateur.
 */
class DisplayProfile extends Action {
    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? 0);
        $userId = $this->user['id'];

        $pageNumber = (int) ($_GET['page'] ?? 1);
        $pagination = new Pagination($pageNumber);

        $type = $_GET['type'] ?? 'touites';
        if (!in_array($type, ['touites', 'medias'], true)) {
            $type = 'touites';
        }
        $feed = match ($type) {
            'medias' => $r->findFeedWithMediasByUserId($id, $pagination),
            default => $r->findFeedByUserId($id, $pagination),
        };

        $details = $r->findUserById($id);
        if (!$details) {
            return HtmlHelper::formError(
                errors: 'Le profil demandé est introuvable.',
                backUrl: '?action=feed',
                backLabel: "Retour à l'accueil",
                title: 'Profil introuvable'
            );
        }

        $nbTouite = (int) ($details['counter_touite'] ?? 0);
        $touiteLabel = $nbTouite > 1 ? "{$nbTouite} touites" : "{$nbTouite} touite";
        $rawName = $details['nom'] . ' ' . $details['prenom'];
        $rawNameId = $details['nom'] . '_' . $details['prenom'];
        $completeName = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
        $completeNameId = htmlspecialchars($rawNameId, ENT_QUOTES, 'UTF-8');
        $profileAvatar = HtmlHelper::profileAvatar($details, 'profile-avatar');
        $profileColor = HtmlHelper::profileColor($id);
        $followerCount = count($r->findFollowersByUserId($id) ?? []);
        $followingCount = count($r->findFollowedByUserId($id) ?? []);
        $averageNote = $r->findAverageNoteTouitesByUserId($id);
        $noteClass = $averageNote < 0
            ? 'touite-note-negative'
            : ($averageNote > 0 ? 'touite-note-positive' : 'touite-note-neutral');
        $noteFormatted = ($averageNote > 0 ? '+' : '') . $averageNote;

        $subBtn = '';

        if ($id !== $userId) {
            if ($r->isFollowingUser($id, $userId)) {
                    $subBtn = <<<HTML
                    <form method="POST" action="?action=subscribe">
                        <input type="hidden" name="type" value="unsub">
                        <input type="hidden" name="id" value="{$id}">
                        <button type="submit" class="btn btn-outline-primary profile-button">Abonné</button>
                    </form>
                HTML;
            } else {
                $subBtn = <<<HTML
                    <form method="POST" action="?action=subscribe">
                        <input type="hidden" name="type" value="sub">
                        <input type="hidden" name="id" value="{$id}">
                        <button type="submit" class="btn btn-primary profile-button">S'abonner</button>
                    </form>
                HTML;
            }
        }

        $tabs = [
            'touites' => 'Touites',
            'medias' => 'Médias',
        ];

        $tabsHtml = HtmlHelper::tabs($tabs, $type, 'profile', ['id' => $id]);

        $backLink = HtmlHelper::backLink('?action=feed');
        $page = <<<HTML
            <header class="feed-header detail-header">
                {$backLink}
                <div>
                    <h1 class="mb-0 fw-bold" style="font-size: 1.25rem; line-height: 1.2;">{$completeName}</h1>
                    <div class="text-secondary small" style="font-size: .8rem;">{$touiteLabel}</div>
                </div>
            </header>
            <section class="profile-header">
                <div class="profile-cover {$profileColor}"></div>
                <div class="profile-main">
                    {$profileAvatar}
                    {$subBtn}
                    <h2>{$completeName}</h2>
                    <p class="profile-handle">@{$completeNameId}</p>
                    <div class="d-flex flex-wrap gap-2 my-2 align-items-center">
                        <a href="?action=users&id={$id}&type=followed" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="bi bi-person-check me-1"></i> <strong>{$followingCount}</strong> abonnements
                        </a>
                        <a href="?action=users&id={$id}&type=followers" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="bi bi-people me-1"></i> <strong>{$followerCount}</strong> abonnés
                        </a>
                        <a href="?action=listFollowTag&id={$id}" class="btn btn-outline-secondary btn-sm rounded-pill">
                            <i class="bi bi-tags me-1"></i> Tags suivis
                        </a>
                        <span class="d-inline-flex align-items-center px-3 py-1 rounded-pill border {$noteClass} fw-semibold" style="font-size: .85rem; min-height: 32px; border-color: var(--border) !important;">
                            <i class="bi bi-heart me-1"></i> <strong>{$noteFormatted}</strong>
                        </span>
                    </div>
                    <p class="profile-bio">Découvrez les touites publiés par cet utilisateur.</p>
                    <div class="profile-meta d-flex flex-wrap gap-3">
                        <span><i class="bi bi-calendar3"></i> Membre de Touiteur</span>
                        <span><i class="bi bi-chat-left-text"></i> {$touiteLabel}</span>
                    </div>
                </div>
                <nav class="profile-tabs" aria-label="Navigation du profil">
                    {$tabsHtml}
                </nav>
            </section>
        HTML;

        $action = 'profile&' . http_build_query([
            'type' => $type,
            'id' => $id,
        ]);
        return $page . RendererFactory::getRenderer($feed, $pagination, $action)->render(TypeRender::COMPACT);
    }
}
