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
        return <<<HTML
            <article class="{$class}">
                <div class="touite-header">
                    <strong>@touiteur</strong>
                    <time datetime="{$date}">{$date}</time>
                </div>
                <p class="touite-content">{$content}</p>
                {$image}
            </article>
        HTML;
    }
}