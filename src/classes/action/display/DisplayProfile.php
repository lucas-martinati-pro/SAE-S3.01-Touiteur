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
        if (empty($details)) {
            return HtmlHelper::notFound('Profile', 'The requested profile could not be found.');
        }

        $nbTouite = (int) ($details['counter_touite'] ?? 0);
        $touiteLabel = $nbTouite === 1 ? "{$nbTouite} touite" : "{$nbTouite} touites";
        $rawName = $details['nom'] . ' ' . $details['prenom'];
        $rawNameId = $details['nom'] . '_' . $details['prenom'];
        $completeName = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
        $completeNameId = htmlspecialchars($rawNameId, ENT_QUOTES, 'UTF-8');
        $profileAvatar = HtmlHelper::profileAvatar($details, 'profile-avatar');
        $profileColor = HtmlHelper::profileColor($id);
        $followerCount = count($r->findFollowersByUserId($id) ?? []);
        $followerLabel = $followerCount === 1 ? 'follower' : 'followers';
        $followingCount = count($r->findFollowedByUserId($id) ?? []);
        $averageNote = $r->findAverageNoteTouitesByUserId($id);
        $noteClass = $averageNote < 0
            ? 'touite-note-negative'
            : ($averageNote > 0 ? 'touite-note-positive' : 'touite-note-neutral');
        $noteFormatted = ($averageNote > 0 ? '+' : '') . $averageNote;

        $subBtn = '';

        if ((int) $id !== (int) $userId) {
            $subBtn = HtmlHelper::subscriptionButton(
                $id,
                $r->isFollowingUser($id, $userId),
                buttonClass: 'btn profile-button'
            );
        }

        $tabs = [
            'touites' => 'Touites',
            'medias' => 'Media',
        ];

        $tabsHtml = HtmlHelper::tabs($tabs, $type, 'profile', ['id' => $id]);

        $page = HtmlHelper::detailHeader($rawName, '?action=feed', '', $touiteLabel) . <<<HTML
            <section class="profile-header">
                <div class="profile-cover {$profileColor}"></div>
                <div class="profile-main">
                    {$profileAvatar}
                    {$subBtn}
                    <h2>{$completeName}</h2>
                    <p class="profile-handle">@{$completeNameId}</p>
                    <div class="d-flex flex-wrap gap-2 my-2 align-items-center">
                        <a href="?action=users&id={$id}&type=followed" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="bi bi-person-check me-1"></i> <strong>{$followingCount}</strong> following
                        </a>
                        <a href="?action=users&id={$id}&type=followers" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="bi bi-people me-1"></i> <strong>{$followerCount}</strong> {$followerLabel}
                        </a>
                        <a href="?action=listFollowTag&id={$id}" class="btn btn-outline-secondary btn-sm rounded-pill">
                            <i class="bi bi-tags me-1"></i> Followed tags
                        </a>
                        <span class="profile-note {$noteClass}">
                            <i class="bi bi-heart me-1"></i> <strong>{$noteFormatted}</strong>
                        </span>
                    </div>
                    <p class="profile-bio">Discover the touites published by this user.</p>
                    <div class="profile-meta d-flex flex-wrap gap-3">
                        <span><i class="bi bi-calendar3"></i> Touiteur member</span>
                        <span><i class="bi bi-chat-left-text"></i> {$touiteLabel}</span>
                    </div>
                </div>
                <nav class="profile-tabs" aria-label="Profile navigation">
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
