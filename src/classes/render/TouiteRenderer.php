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

        $imagePath = $this->touite->getImage_path();
        if ($imagePath !== null && $imagePath !== '') {
            $path = htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars(
                $this->touite->getImage_description() ?? 'Image du touite',
                ENT_QUOTES,
                'UTF-8'
            );

            $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

            if ($extension === 'mp4') {
                $image = <<<HTML
                    <video class="touite-image touite-video" controls width="100%" preload="metadata">
                        <source src="{$path}" type="video/mp4">
                        Votre navigateur ne supporte pas la lecture de vidéos.
                    </video>
                HTML;
            } else {
                $image = "<img class=\"touite-image\" src=\"{$path}\" alt=\"{$description}\">";
            }
        }

        $rawName = $this->nom . " " . $this->prenom;
        $rawNameId = $this->nom . "_" . $this->prenom;
        $completeName = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
        $completeNameId = htmlspecialchars($rawNameId, ENT_QUOTES, 'UTF-8');
        $premiereLettre = htmlspecialchars(mb_substr($rawName, 0, 1), ENT_QUOTES, 'UTF-8');
        $subBtn = "";
        $currentUser = AuthnProvider::getSignedInUser();
        $currentUserId = $currentUser['id'];
        $profileUser = [
            'id' => $this->user_id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
        ];
        $profileAvatar = HtmlHelper::profileAvatar($profileUser);

        if ($this->user_id !== $currentUserId && $selector === TypeRender::LONG) {
            $subBtn = HtmlHelper::subscriptionButton(
                $this->user_id,
                RepositoryFactory::getReader()->isSub($this->user_id, $currentUserId)
            );
        }

        $actionsClass = $selector === TypeRender::LONG ? 'touite-actions-long' : 'touite-actions-compact';
        $noteActions = HtmlHelper::touiteActions($touite_id, $note, $selector);

        $class = $selector === TypeRender::LONG ? 'touite-card touite-card-long' : 'touite-card';
        $card = <<<HTML
                <article class="{$class}">
                    {$this->touiteLink($touite_id, $completeName, $selector)}
                    <div class="touite-row">
                        <a class="touite-profile-link" href="?action=displayProfile&id={$this->user_id}"
                           aria-label="Voir le profil de {$completeName}">
                            {$profileAvatar}
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