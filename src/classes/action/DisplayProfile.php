<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\RendererFactory;
use iutnc\touiteur\enum\TypeRender;
use iutnc\touiteur\pagination\Pagination;
use iutnc\touiteur\render\HtmlHelper;
use Override;

/**
 * Action affichant le profil d'un utilisateur.
 */
class DisplayProfile extends Action {
    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? 0);
        $user_id = $this->user['id'];

        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $pagination = new Pagination($page);

        $feed = "";
        $feedType = "";
        $activeTouites = '';
        $activeMedias = '';
        if (isset($_GET['type']) && $_GET['type'] == 'medias') {
            $feed = $r->findFeedWithMediasByUserId($id, $pagination);
            $activeMedias = 'class="active"';
            $feedType = 'medias';
        } else {
            $feed = $r->findFeedByUserId($id, $pagination);
            $activeTouites = 'class="active"';
        }

        $details = $r->getUserById($id);
        if (!$details) {
            return HtmlHelper::formError(
                errors: "Le profil demandé est introuvable.",
                backUrl: "?action=displayFeed",
                backLabel: "Retour à l'accueil",
                title: "Profil introuvable"
            );
        }

        $rawName = $details['nom'] . " " . $details['prenom'];
        $rawNameId = $details['nom'] . "_" . $details['prenom'];
        $completeName = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
        $completeNameId = htmlspecialchars($rawNameId, ENT_QUOTES, 'UTF-8');
        $profileAvatar = HtmlHelper::profileAvatar($details, 'profile-avatar');
        $profileColor = HtmlHelper::profileColor($id);
        $nbFollowers = count($r->getFollowers($id) ?? []);
        $nbFollowed = count($r->getFollowed($id) ?? []);
        $noteMoyenne = $r->getMoyenneNoteTouite($id);
        $noteClass = $noteMoyenne < 0
            ? 'touite-note-negative'
            : ($noteMoyenne > 0 ? 'touite-note-positive' : 'touite-note-neutral');
        $noteFormatted = ($noteMoyenne > 0 ? '+' : '') . $noteMoyenne;

        $subBtn = "";

        if ($id !== $user_id) {
            if ($r->isSub($id, $user_id)) {
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

        $page = HtmlHelper::detailHeader('Profil') . <<< HTML
            <section class="profile-header">
                <div class="profile-cover {$profileColor}"></div>
                <div class="profile-main">
                    {$profileAvatar}
                    {$subBtn}
                    <h2>{$completeName}</h2>
                    <p class="profile-handle">@{$completeNameId}</p>
                    <div class="d-flex flex-wrap gap-2 my-2 align-items-center">
                        <a href="?action=listFollowed&id={$id}" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="bi bi-person-check me-1"></i> <strong>{$nbFollowed}</strong> abonnements
                        </a>
                        <a href="?action=listFollowers&id={$id}" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="bi bi-people me-1"></i> <strong>{$nbFollowers}</strong> abonnés
                        </a>
                        <a href="?action=listFollowTag&id={$id}" class="btn btn-outline-secondary btn-sm rounded-pill">
                            <i class="bi bi-tags me-1"></i> Tags suivis
                        </a>
                        <span class="d-inline-flex align-items-center px-3 py-1 rounded-pill border {$noteClass} fw-semibold" style="font-size: .85rem; min-height: 32px; border-color: var(--border) !important;">
                            <i class="bi bi-heart me-1"></i> <strong>{$noteFormatted}</strong>
                        </span>
                    </div>
                    <p class="profile-bio">Découvrez les touites publiés par cet utilisateur.</p>
                    <div class="profile-meta">
                        <span><i class="bi bi-calendar3"></i> Membre de Touiteur</span>
                    </div>
                </div>
                <nav class="profile-tabs" aria-label="Navigation du profil">
                    <a {$activeTouites} href="?action=displayProfile&id={$id}">Touites</a>
                    <a {$activeMedias} href="?action=displayProfile&id={$id}&type=medias">Médias</a>
                </nav>
            </section>
        HTML;

        $action = ($feedType === 'medias') ? 'displayProfile&type=medias&id=' : 'displayProfile&id=';
        $action .= $id;
        return $page . RendererFactory::getRenderer($feed, $pagination, $action)->render(TypeRender::COMPACT);
    }
}