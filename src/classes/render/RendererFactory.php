<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\pagination\Pagination;
use iutnc\touiteur\touite\Touite;
use iutnc\touiteur\touite\Feed;

/**
 * Factory pour l'instanciation du renderer approprié.
 */
class RendererFactory {
    /**
     * Instancie et retourne le renderer adéquat pour l'objet donné en argument.
     *
     * @param object $element L'objet à restituer en HTML (Touite ou Feed).
     * @return Renderer Instance de Renderer adaptée.
     * @throws \InvalidArgumentException Si l'objet n'est pas pris en charge.
     */
    public static function getRenderer(object $element, Pagination $pagination = new Pagination(), string $url = 'feed'): Renderer {
        return match (true) {
            $element instanceof Touite => new TouiteRenderer($element),
            $element instanceof Feed => new FeedRenderer($element, $pagination, $url),
            default => throw new \InvalidArgumentException('Type de rendu non pris en charge.'),
        };
    }
}