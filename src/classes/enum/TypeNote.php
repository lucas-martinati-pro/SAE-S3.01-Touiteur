<?php

namespace iutnc\touiteur\enum;

enum TypeNote {
    case like;
    case annuler;
    case dislike;

    public static function fromLabel(string $label): ?self {
        return match($label) {
            'like' => self::like,
            'dislike' => self::dislike,
            default => self::annuler,
        };
    }

    public function note(): string {
        return match($this) {
            self::like => 1,
            self::annuler => 0,
            self::dislike => -1,
        };
    }
}