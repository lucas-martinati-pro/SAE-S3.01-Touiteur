<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\touite\Touite;
use iutnc\touiteur\enum\TypeRender;
use Override;

class TouiteRenderer implements Renderer {
    protected Touite $touite;

    public function __construct(Touite $touite) {
        $this->touite = $touite;
    }

    #[Override]
    public function render(TypeRender $selector = TypeRender::COMPACT): string {
        $touite_id = $this->touite->getId();
        $content = htmlspecialchars($this->touite->getContent(), ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars($this->touite->getPosted_at(), ENT_QUOTES, 'UTF-8');
        $note = $this->touite->getNote();
        $media = '';
        $tags = RepositoryFactory::getReader()->findTagsByTouiteId($this->touite->getId());
        $content_split = [];
        $mots = explode(' ', $content);

        foreach ($mots as $mot) {
            if ($tags && str_starts_with($mot, '#')) {
                $tagLibelle = rtrim(substr($mot, 1), '.,');
                foreach ($tags as $tag) {
                    $id = $tag->getId();
                    $libelle = $tag->getLibelle();
                    if ($tagLibelle == $libelle) {
                        $mot = str_replace("#{$libelle}", "<a href=\"?action=tag&id={$id}\">#{$libelle}</a>", $mot);
                        break;
                    }
                }
            }
            $content_split[] = $mot;
        }

        $content = implode(' ', $content_split);

        $mediaPath = $this->touite->getmedia_path();
        if ($mediaPath !== null && $mediaPath !== '') {
            $path = htmlspecialchars($mediaPath, ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars(
                $this->touite->getmedia_description() ?? 'Média du touite',
                ENT_QUOTES,
                'UTF-8'
            );

            $extension = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));

            if ($extension === 'mp4') {
                $media = <<<HTML
                    <video class="touite-media touite-video" controls width="100%" preload="metadata">
                        <source src="{$path}" type="video/mp4">
                        Votre navigateur ne supporte pas la lecture de vidéos.
                    </video>
                HTML;
            } else {
                $media = "<img class=\"touite-media\" src=\"{$path}\" alt=\"{$description}\">";
            }
        }

        $r = RepositoryFactory::getReader();
        $user = $r->findUserByTouiteId($this->touite->getId());
        $user_id = $user['id'];
        $rawName = $user['nom'] . " " . $user['prenom'];
        $rawNameId = $user['nom'] . "_" . $user['prenom'];
        $completeName = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
        $completeNameId = htmlspecialchars($rawNameId, ENT_QUOTES, 'UTF-8');
        $currentUserId = AuthnProvider::getSignedInUser()['id'];
        $profileAvatar = HtmlHelper::profileAvatar($user);

        $subBtn = "";
        if ($user_id !== $currentUserId && $selector === TypeRender::LONG) {
            $subBtn = HtmlHelper::subscriptionButton(
                $user_id,
                RepositoryFactory::getReader()->isSub($user_id, $currentUserId)
            );
        }

        $actionsClass = $selector === TypeRender::LONG ? 'touite-actions-long' : 'touite-actions-compact';
        $noteActions = HtmlHelper::touiteActions($touite_id, $note, $selector);

        $class = $selector === TypeRender::LONG ? 'touite-card touite-card-long' : 'touite-card';
        $card = <<<HTML
                <article class="{$class}">
                    {$this->touiteLink($touite_id, $completeName, $selector)}
                    <div class="touite-row">
                        <div class="touite-body">
                            <div class="touite-header">
                                <a class="touite-profile-link" href="?action=profile&id={$user_id}"
                                   aria-label="Voir le profil de {$completeName}">
                                    {$profileAvatar}
                                    <span class="touite-profile-identity">
                                        <strong class="touite-author">{$completeName}</strong>
                                        <span>@{$completeNameId}</span>
                                    </span>
                                </a>
                                <time datetime="{$date}">{$date}</time>
                                {$subBtn}
                            </div>
                            <p class="touite-content">{$content}</p>
                            {$media}
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
            <a class="touite-main-link" href="?action=touite&id={$touiteId}"
               aria-label="Voir le touite de {$author}">
            </a>
        HTML;
    }
}
