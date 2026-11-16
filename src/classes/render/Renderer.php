<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\enum\TypeRender;

/**
 * Interface pour le rendu HTML des éléments de l'application.
 */
interface Renderer {
    /**
     * Génère et retourne le code HTML représentant l'élément selon le sélecteur fourni.
     * @param TypeRender $selector type de rendu demandé.
     * @return string Texte ou contenu HTML généré.
     */
    public function render(TypeRender $selector = TypeRender::COMPACT): string;
}