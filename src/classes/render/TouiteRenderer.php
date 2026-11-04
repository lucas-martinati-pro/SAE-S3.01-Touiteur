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
        $image = '';
        // TODO
        $tag = RepositoryFactory::getReader()->findTagsByTouiteId($this->touite->getId());
        $completeName = $this->nom . " " . $this->prenom;
        $premiereLettre = $completeName[0];


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
                    <form method="POST" action="?action=subscribe" class="ms-auto">
                        <input type="hidden" name="type" value="unsub">
                        <input type="hidden" name="id" value="{$this->user_id}">
                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill">Abonné</button>
                    </form>
                HTML;
            } else {
                $subBtn = <<<HTML
                    <form method="POST" action="?action=subscribe" class="ms-auto">
                        <input type="hidden" name="type" value="sub">
                        <input type="hidden" name="id" value="{$this->user_id}">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill">S'abonner</button>
                    </form>
                HTML;
            }
        }

        $class = $selector === TypeRender::LONG ? 'touite-card touite-card-long' : 'touite-card';
        $card = <<<HTML
                <article class="{$class}">
                    <div class="touite-row">
                        <div class="touite-avatar">{$premiereLettre}</div>
                        <div class="touite-body">
                            <div class="touite-header d-flex align-items-center">
                                <a class="text-decoration-none text-reset fw-bold" href="?action=displayProfile&id={$this->user_id}"><strong>{$completeName}</strong></a>
                                <span>@{$completeName} · <time datetime="{$date}">{$date}</time></span>
                                {$subBtn}
                            </div>
                            <p class="touite-content">{$content}</p>
                            {$image}
                            <div class="touite-actions" aria-label="Actions du touite">
                                <button type="button" aria-label="J'aime"><i class="bi bi-heart"></i></button>
                            </div>
                        </div>
                    </div>
                </article>
        HTML;

        return $selector === TypeRender::LONG
            ? $card
            : "<a class=\"touite-link\" href=\"?action=displayTouite&id={$touite_id}\">{$card}</a>";
    }
}