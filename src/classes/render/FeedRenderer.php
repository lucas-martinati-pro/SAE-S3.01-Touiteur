<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\touite\Feed;
use iutnc\touiteur\enum\TypeRender;
use iutnc\touiteur\pagination\Pagination;
use Override;

/**
 * Classe FeedRenderer de l’application.
 */
class FeedRenderer implements Renderer {
    /**
     * Valeur utilisée pour feed.
     */
    protected Feed $feed;
    /**
     * Valeur utilisée pour pagination.
     */
    private Pagination $pagination;
    /**
     * Valeur utilisée pour action.
     */
    private string $action;

    /**
     * Initialise les données nécessaires à cet objet.
     * @param Feed $feed fil de touites à afficher.
     * @param Pagination $pagination paramètres de pagination.
     * @param string $action nom de l’action.
     */
    public function __construct(Feed $feed, Pagination $pagination, string $action) {
        $this->feed = $feed;
        $this->pagination = $pagination;
        $this->action = $action;
    }

    #[Override]
    public function render(TypeRender $selector = TypeRender::COMPACT): string {
        $this->pagination->setIsLastPage($this->feed->getLength());
        $paginationHtml = HtmlHelper::pagination(
            'Pagination du feed',
            $this->action,
            $this->pagination
        );

        if ($this->feed->isEmpty()) {
            return HtmlHelper::alert('info', 'Aucun touite à afficher pour le moment.') . $paginationHtml;
        }

        $html = '<section class="feed" aria-label="Liste des touites">';
        foreach ($this->feed as $touite) {
            $html .= (new TouiteRenderer($touite))->render($selector);
        }

        return $html . $paginationHtml . '</section>';
    }
}
