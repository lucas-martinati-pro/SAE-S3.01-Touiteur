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
     * @param object $element objet à rendre.
     * @param Pagination $pagination paramètres de pagination.
     * @param string $url adresse de destination.
     * @return Renderer Moteur de rendu adapté à l’objet.
     * @throws \InvalidArgumentException Si le traitement échoue.
     */
    public static function getRenderer(object $element, Pagination $pagination = new Pagination(), string $url = 'feed'): Renderer {
        return match (true) {
            $element instanceof Touite => new TouiteRenderer($element),
            $element instanceof Feed => new FeedRenderer($element, $pagination, $url),
            default => throw new \InvalidArgumentException('Unsupported renderer type.'),
        };
    }
}