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

        $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        $pagination = new Pagination($page);

        $feed = $r->findFeedByUserId($id, $pagination);

        $details = $r->getUserById($id);
        $completeName = $details['nom'] . " " . $details['prenom'];
        $premiereLettre = $completeName[0];

        $subBtn = "";

        if ($id !== $this->user['id']) {
            if ($r->isSub($id, $this->user['id'])) {

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
                <div class="profile-cover"></div>
                <div class="profile-main">
                    <div class="profile-avatar">{$premiereLettre}</div>
                    {$subBtn}
                    <h2>{$completeName}</h2>
                    <p class="profile-handle">@{$completeName}</p>
                    <p class="profile-bio">Découvrez les touites publiés par cet utilisateur.</p>
                    <div class="profile-meta">
                        <span><i class="bi bi-calendar3"></i> Membre de Touiteur</span>
                    </div>
                </div>
                <nav class="profile-tabs" aria-label="Navigation du profil">
                    <a class="active" href="?action=displayProfile&id={$id}">Touites</a>
                </nav>
            </section>
        HTML;

        $previous = $pagination->previous();
        $next = $pagination->next();
        $pageNumber = $pagination->getPage();
        $pagination = HtmlHelper::pagination(
            'Pagination du profil',
            "?action=displayProfile&id={$id}&page={$previous}",
            "?action=displayProfile&id={$id}&page={$next}",
            $pageNumber
        );

        if ($feed === null) {
            return $page . HtmlHelper::alert('info', 'Cet utilisateur n’a encore publié aucun touite.') . $pagination;
        }

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT) . $pagination;
    }
}