<?php

namespace iutnc\touiteur\render;

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
        $id = $this->touite->getId();
        $content = htmlspecialchars($this->touite->getContent(), ENT_QUOTES, 'UTF-8');
        $date = htmlspecialchars($this->touite->getPosted_at(), ENT_QUOTES, 'UTF-8');
        $image = '';

        if ($this->touite->getImage_path() !== null) {
            $path = htmlspecialchars($this->touite->getImage_path(), ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars(
                $this->touite->getImage_description() ?? 'Image du touite',
                ENT_QUOTES,
                'UTF-8'
            );
            $image = "<img class=\"touite-image\" src=\"{$path}\" alt=\"{$description}\">";
        }

        $class = $selector === TypeRender::LONG ? 'touite-card touite-card-long' : 'touite-card';
        $card = <<<HTML
                <article class="{$class}">
                    <div class="touite-row">
                        <div class="touite-avatar">T</div>
                        <div class="touite-body">
                            <div class="touite-header">
                                <strong>Nom User</strong>
                                <span>@Nom User · <time datetime="{$date}">{$date}</time></span>
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
            : "<a class=\"touite-link\" href=\"?action=displayTouite&id={$id}\">{$card}</a>";
    }
}