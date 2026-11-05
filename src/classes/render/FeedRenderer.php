<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\touite\Feed;
use iutnc\touiteur\enum\TypeRender;
use iutnc\touiteur\pagination\Pagination;
use Override;

class FeedRenderer implements Renderer {
    protected Feed $feed;
    private Pagination $pagination;
    private String $action;

    public function __construct(Feed $feed, Pagination $pagination, String $action) {
        $this->feed = $feed;
        $this->pagination = $pagination;
        $this->action = $action;
    }

    #[Override]
    public function render(TypeRender $selector = TypeRender::COMPACT): string {
        $previous = $this->pagination->previous();
        $next = $this->pagination->next();
        $pageNumber = $this->pagination->getPage();
        $paginationHtml = HtmlHelper::pagination(
            'Pagination du feed',
            "?action={$this->action}&page={$previous}",
            "?action={$this->action}&page={$next}",
            $pageNumber
        );

        if ($this->feed->isEmpty()) {
            return HtmlHelper::alert('info', 'Aucun touite à afficher pour le moment.') . $paginationHtml;
        }

        $res = '<section class="feed" aria-label="Liste des touites">';
        foreach ($this->feed as $touite) {
            $res .= (new TouiteRenderer($touite))->render($selector);
        }

        return $res . $paginationHtml . '</section>';
    }
}