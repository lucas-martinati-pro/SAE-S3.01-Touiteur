<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\touite\Touite;
use iutnc\touiteur\enum\TypeRender;
use Override;

class TouiteRenderer implements Renderer {
    protected Touite $touite;

    private string $nom;
    private string $prenom;
    private int $user_id;

    public function __construct(Touite $touite) {
        $this->touite = $touite;
        $this->completeUserDetails();
    }

    private function completeUserDetails() {
        $r = RepositoryFactory::getReader();
        $details = $r->getUserByTouiteId($this->touite->getId());

        $this->nom = $details['nom'];
        $this->prenom = $details['prenom'];
        $this->user_id = $details['users_id'];
    }

    #[Override]
    public function render(TypeRender $selector = TypeRender::COMPACT): string {
        $touite_id = $this->touite->getId();
        $content = htmlspecialchars($this->touite->getContent(), ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars($this->touite->getPosted_at(), ENT_QUOTES, 'UTF-8');
        $note = $this->touite->getNote();
        $image = '';
        // TODO
        $tag = RepositoryFactory::getReader()->findTagsByTouiteId($this->touite->getId());
        $rawName = $this->nom . " " . $this->prenom;
        $rawNameId = $this->nom . "_" . $this->prenom;
        $completeName = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
        $completeNameId = htmlspecialchars($rawNameId, ENT_QUOTES, 'UTF-8');
        $premiereLettre = htmlspecialchars(mb_substr($rawName, 0, 1), ENT_QUOTES, 'UTF-8');

        $imagePath = $this->touite->getImage_path();
        if ($imagePath !== null && $imagePath !== '') {
            $path = htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars(
                $this->touite->getImage_description() ?? 'Image du touite',
                ENT_QUOTES,
                'UTF-8'
            );
            $image = "<img class=\"touite-image\" src=\"{$path}\" alt=\"{$description}\">";
        }

        $subBtn = "";
        $currentUser = AuthnProvider::getSignedInUser();
        $currentUserId = $currentUser['id'];

        if ($this->user_id !== $currentUserId && $selector === TypeRender::LONG) {
            $r = RepositoryFactory::getReader();
            if ($r->isSub($this->user_id, $currentUserId)) {
                $subBtn = <<<HTML
                    <form method="POST" action="?action=subscribe">
                        <input type="hidden" name="type" value="unsub">
                        <input type="hidden" name="id" value="{$this->user_id}">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Abonné</button>
                    </form>
                HTML;
            } else {
                $subBtn = <<<HTML
                    <form method="POST" action="?action=subscribe">
                        <input type="hidden" name="type" value="sub">
                        <input type="hidden" name="id" value="{$this->user_id}">
                        <button type="submit" class="btn btn-sm btn-primary">S'abonner</button>
                    </form>
                HTML;
            }
        }

        $noteClass = $note < 0 ? 'touite-note-negative' : ($note > 0 ? 'touite-note-positive' : 'touite-note-neutral');
        $actionsClass = $selector === TypeRender::LONG ? 'touite-actions-long' : 'touite-actions-compact';
        $noteActions = $selector === TypeRender::LONG
            ? <<<HTML
                <span class="touite-note {$noteClass}" aria-label="Note du touite"><i class="bi bi-heart"></i> {$note}</span>
                <form method="POST" action="?action=LikeAction&id={$touite_id}&type=like">
                    <button type="submit" aria-label="J'aime"><i class="bi bi-hand-thumbs-up"></i> J'aime</button>
                </form>
                <form method="POST" action="?action=LikeAction&id={$touite_id}&type=dislike">
                    <button type="submit" aria-label="Je n'aime pas"><i class="bi bi-hand-thumbs-down"></i> Je n'aime pas</button>
                </form>
                <form method="POST" action="?action=LikeAction&id={$touite_id}&type=annuler">
                    <button type="submit" aria-label="Annuler ma note"><i class="bi bi-x-circle"></i> Annuler</button>
                </form>
            HTML
            : <<<HTML
                <span class="touite-note {$noteClass}" aria-label="Note du touite"><i class="bi bi-heart"></i> {$note}</span>
            HTML;

        $class = $selector === TypeRender::LONG ? 'touite-card touite-card-long' : 'touite-card';
        $card = <<<HTML
                <article class="{$class}">
                    {$this->touiteLink($touite_id, $completeName, $selector)}
                    <div class="touite-row">
                        <a class="touite-profile-link" href="?action=displayProfile&id={$this->user_id}"
                           aria-label="Voir le profil de {$completeName}">
                            <div class="touite-avatar">{$premiereLettre}</div>
                        </a>
                        <div class="touite-body">
                            <div class="touite-header">
                                <a class="touite-author" href="?action=displayProfile&id={$this->user_id}"><strong>{$completeName}</strong></a>
                                <span>@{$completeNameId} · <time datetime="{$date}">{$date}</time></span>
                                {$subBtn}
                            </div>
                            <p class="touite-content">{$content}</p>
                            {$image}
                            <div class="touite-actions {$actionsClass}" aria-label="Actions du touite">
                                {$noteActions}
                            </div>
                        </div>
                    </div>
                </article>
        HTML;

        return $card;
    }

    private function touiteLink(int $touiteId, string $author, TypeRender $selector): string {
        if ($selector === TypeRender::LONG) {
            return '';
        }

        return <<<HTML
            <a class="touite-main-link" href="?action=displayTouite&id={$touiteId}"
               aria-label="Voir le touite de {$author}">
            </a>
        HTML;
    }
}