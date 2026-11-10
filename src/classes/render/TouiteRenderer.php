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
        $touiteId = $this->touite->getId();
        $rawContent = $this->touite->getContent();
        $date = htmlspecialchars($this->touite->getPostedAt(), ENT_QUOTES, 'UTF-8');
        $note = $this->touite->getNote();
        $media = '';
        $tags = RepositoryFactory::getReader()->findTagsByTouiteId($this->touite->getId());
        $contentParts = [];
        $words = explode(' ', $rawContent);

        foreach ($words as $word) {
            $renderedWord = null;
            if ($tags && str_starts_with($word, '#')) {
                $tagLibelle = rtrim(substr($word, 1), '.,\'"');
                if (strlen($tagLibelle) > 0) {
                    foreach ($tags as $tag) {
                        $id = $tag->getId();
                        $tagLabel = $tag->getLibelle();
                        if ($tagLibelle == $tagLabel) {
                            $escapedTagLabel = htmlspecialchars($tagLabel, ENT_QUOTES, 'UTF-8');
                            $suffix = substr($word, strlen($tagLabel) + 1);
                            $escapedSuffix = htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8');
                            $renderedWord = "<a href=\"?action=tag&id={$id}\">#{$escapedTagLabel}</a>{$escapedSuffix}";
                            break;
                        }
                    }
                }
            }
            $contentParts[] = $renderedWord ?? htmlspecialchars($word, ENT_QUOTES, 'UTF-8');
        }

        $content = implode(' ', $contentParts);

        $mediaPath = $this->touite->getMediaPath();
        if ($mediaPath !== null && $mediaPath !== '') {
            $path = htmlspecialchars($mediaPath, ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars(
                $this->touite->getMediaDescription() ?? 'Média du touite',
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
        $userId = $user['id'];
        $rawName = $user['nom'] . ' ' . $user['prenom'];
        $rawNameId = $user['nom'] . '_' . $user['prenom'];
        $completeName = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
        $completeNameId = htmlspecialchars($rawNameId, ENT_QUOTES, 'UTF-8');
        $currentUserId = AuthnProvider::getSignedInUser()['id'];
        $profileAvatar = HtmlHelper::profileAvatar($user);

        $subBtn = '';
        if ($userId !== $currentUserId && $selector === TypeRender::LONG) {
            $subBtn = HtmlHelper::subscriptionButton(
                $userId,
                RepositoryFactory::getReader()->isFollowingUser($userId, $currentUserId)
            );
        }

        $actionsClass = $selector === TypeRender::LONG ? 'touite-actions-long' : 'touite-actions-compact';
        $noteActions = HtmlHelper::touiteActions($touiteId, $note, $selector);

        $class = $selector === TypeRender::LONG ? 'touite-card touite-card-long' : 'touite-card';
        $touiteRenderer = $this->touiteLink($touiteId, $completeName, $selector);

        return <<<HTML
            <article class="{$class}">
                {$touiteRenderer}
                <div class="touite-row">
                    <div class="touite-body">
                        <div class="touite-header">
                            <a class="touite-profile-link" href="?action=profile&id={$userId}"
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
