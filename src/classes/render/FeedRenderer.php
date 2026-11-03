<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\touite\Feed;
use iutnc\touiteur\enum\TypeRender;
use Override;

class FeedRenderer implements Renderer {
    protected Feed $feed;

    public function __construct(Feed $feed) {
        $this->feed = $feed;
    }

    #[Override]
    public function render(TypeRender $selector = TypeRender::COMPACT): string {
        $res = '<section class="feed" aria-label="Liste des touites">';
        foreach ($this->feed as $touite) {
            $res .= (new TouiteRenderer($touite))->render($selector);
        }

        return $res . '</section>';
    }
}