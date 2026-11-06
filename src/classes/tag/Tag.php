<?php

namespace iutnc\touiteur\tag;

class Tag {
    private ?int $id;
    private string $libelle;
    private string $description;

    public function __construct(string $libelle, string $description = '', ?int $id = null) {
        $this->id = $id;
        $this->libelle = $libelle;
        $this->description = $description;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getLibelle(): string {
        return $this->libelle;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }
}