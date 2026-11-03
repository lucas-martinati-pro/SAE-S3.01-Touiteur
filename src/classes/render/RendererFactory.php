<?php

namespace iutnc\touiteur\render;

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
     * @return Renderer|null Instance de Renderer adaptée, ou null si l'objet n'est pas pris en charge.
     */
    public static function getRenderer(object $element): ?Renderer {
        if ($element instanceof Touite) return new TouiteRenderer($element);
        elseif ($element instanceof Feed) return new FeedRenderer($element);
        else return null;
    }
}