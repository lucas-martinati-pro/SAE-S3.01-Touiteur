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
    public function render(TypeRender $selector = TypeRender::COMPACT) : string {
        $res = ($selector == TypeRender::LONG)
            ? <<<HTML
            <h2> LONG : {$this->touite->getContent()}</h2>
            HTML
            : <<<HTML
            <h2> COMPACT : {$this->touite->getImage_description()}</h2>
            HTML;

        return $res;
    }
}