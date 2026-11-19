<?php

namespace iutnc\touiteur\enum;

/**
 * Décrit les valeurs possibles de TypeNote.
 */
enum TypeNote {
    /**
     * Valeur like de l’énumération.
     */
    case like;
    /**
     * Valeur annuler de l’énumération.
     */
    case annuler;
    /**
     * Valeur dislike de l’énumération.
     */
    case dislike;

    /**
     * Convertit un libellé en type de note.
     * @param string $label libellé à afficher.
     * @return ?self Type de note correspondant au libellé, ou null si aucun type ne correspond.
     */
    public static function fromLabel(string $label): ?self {
        return match($label) {
            'like' => self::like,
            'dislike' => self::dislike,
            default => self::annuler,
        };
    }

    /**
     * Retourne la valeur numérique de la note.
     * @return string Texte ou contenu HTML généré.
     */
    public function getNote(): string {
        return match($this) {
            self::like => 1,
            self::annuler => 0,
            self::dislike => -1,
        };
    }
}