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
    public function render(TypeRender $selector = TypeRender::COMPACT) : string {
        $res = "";
        foreach ($this->feed->getTouites() as $touite) {
            $res .= ($selector == TypeRender::LONG)
                ? <<<HTML
                <h2> LONG : {$touite->getContent()}</h2>
                HTML
                : <<<HTML
                <h2> COMPACT : {$touite->getImage_description()}</h2>
                HTML;
        }

        return $res;
    }
}