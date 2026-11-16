<?php

namespace iutnc\touiteur\touite;

use Override;
use Iterator;

/**
 * Représente un fil de touites que l’on peut parcourir.
 */
class Feed implements Iterator {
    /**
     * Tableau des touites.
     * @var list<Touite>
     */
    private array $touites;

    /**
     * Index de la position courante pour l'itération.
     */
    private int $position = 0;

    /**
     * Crée un fil à partir d’une liste de touites.
     * @param list<Touite> $touites liste de touites à parcourir.
     */
    public function __construct(array $touites) {
        $this->touites = $touites;
    }

    /**
     * Retourne les touites du fil.
     * @return list<Touite> Touites présents dans le fil.
     */
    public function getTouites(): array {
        return $this->touites;
    }

    /**
     * Indique si le fil ne contient aucun touite.
     * @return bool Résultat de la vérification.
     */
    public function isEmpty(): bool {
        return empty($this->touites);
    }

    /**
     * Retourne le nombre de touites du fil.
     * @return int Valeur numérique calculée.
     */
    public function getLength(): int {
        return count($this->touites);
    }

    /**========================================================================
     * METHODES ITERATOR
     *========================================================================*/

    #[Override]
    public function current(): mixed {
        return $this->touites[$this->position];
    }

    #[Override]
    public function key(): int {
        return $this->position;
    }

    #[Override]
    public function next(): void {
        $this->position++;
    }

    #[Override]
    public function rewind(): void {
        $this->position = 0;
    }

    #[Override]
    public function valid(): bool {
        // Vérifier que la variable existe
        return isset($this->touites[$this->position]);
    }
}