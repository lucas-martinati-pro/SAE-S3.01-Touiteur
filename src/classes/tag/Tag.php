<?php

namespace iutnc\touiteur\tag;

class Tag {
    private ?int $id;
    private String $libelle;
    private String $description;

    public function __construct(String $libelle, String $descrion, ?int $id = null) {
        $this->id = $id;
        $this->description = $descrion;
        $this->libelle = $libelle;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getLibelle(): String {
        return $this->libelle;
    }

    public function getDescription(): String {
        return $this->description;
    }
}