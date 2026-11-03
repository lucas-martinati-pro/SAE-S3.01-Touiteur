<?php

namespace iutnc\touiteur\enum;

/**
 * Mode de rendu pour les renderers (vue compacte ou détaillée).
 */
enum TypeRender {
    /**
     * Mode de rendu compact (carte réduite, vue grille).
     */
    case COMPACT;
    /**
     * Mode de rendu long (carte horizontale avec lecteur complet).
     */
    case LONG;
}