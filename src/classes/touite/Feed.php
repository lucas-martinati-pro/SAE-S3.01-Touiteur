<?php

namespace iutnc\touiteur\touite;

use Override;
use Iterator;

class Feed implements Iterator {
    /**
     * Tableau des touites.
     * @var Touite[]
     */
    private array $touites;

    /**
     * Index de la position courante pour l'itération.
     */
    private int $position = 0;

    public function __construct(array $touites) {
        $this->touites = $touites;
    }

    public function getTouites(): array {
        return $this->touites;
    }

    public function isEmpty(): bool {
        return empty($this->touites);
    }

    public function getLength(): int {
        return count($this->touites);
    }

    /**========================================================================
     * METHODES ITERATOR
     *========================================================================**/

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
    // Vérifier que la variable existe
    public function valid(): bool {
        return isset($this->touites[$this->position]);
    }
}