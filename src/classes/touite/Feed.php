<?php

namespace iutnc\touiteur\touite;

class Feed {
    /**
     * Tableau des touites.
     * @var Touite[]
     */
    private array $touites;

    public function __construct(array $touites) {
        $this->touites = $touites;
    }

    public function getTouites() : array {
        return $this->touites;
    }
}