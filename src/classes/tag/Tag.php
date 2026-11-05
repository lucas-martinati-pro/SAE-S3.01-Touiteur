<?php

namespace iutnc\touiteur\tag;

class Tag {
    private ?int $id;
    private String $libelle;
    private ?string $description;

    public function __construct(String $libelle, ?int $id = null, ?string $description = null) {
        $this->id = $id;
        $this->libelle = $libelle;
        $this->description = $description;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getLibelle(): String {
        return $this->libelle;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }
}