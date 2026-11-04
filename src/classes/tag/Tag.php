<?php

namespace iutnc\touiteur\tag;

class Tag {
    private ?int $id;
    private String $libelle;

    public function __construct(String $libelle, ?int $id = null) {
        $this->id = $id;
        $this->libelle = $libelle;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getLibelle(): String {
        return $this->libelle;
    }
}