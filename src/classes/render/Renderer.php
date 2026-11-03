<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\enum\TypeRender;

/**
 * Interface pour le rendu HTML des éléments de l'application.
 */
interface Renderer {
    /**
     * Génère et retourne le code HTML représentant l'élément selon le sélecteur fourni.
     *
     * @param TypeRender $selector Constante indiquant le mode de rendu désiré (TypeRender::COMPACT ou TypeRender::LONG).
     * @return string Balises HTML prêtes à être insérées dans la page.
     */
    public function render(TypeRender $selector = TypeRender::COMPACT) : string;
}