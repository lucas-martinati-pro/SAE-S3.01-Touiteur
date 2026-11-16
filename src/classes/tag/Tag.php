<?php

namespace iutnc\touiteur\tag;

/**
 * Classe Tag de l’application.
 */
class Tag {
    /**
     * Valeur utilisée pour id.
     */
    private ?int $id;
    /**
     * Valeur utilisée pour libelle.
     */
    private string $libelle;
    /**
     * Valeur utilisée pour description.
     */
    private string $description;

    /**
     * Initialise les données nécessaires à cet objet.
     * @param string $libelle libellé du tag.
     * @param string $description description du tag.
     * @param ?int $id identifiant de la ressource.
     */
    public function __construct(string $libelle, string $description = '', ?int $id = null) {
        $this->id = $id;
        $this->libelle = $libelle;
        $this->description = $description;
    }

    /**
     * Retourne l’identifiant du tag.
     * @return ?int Description du tag.
     */
    public function getId(): ?int {
        return $this->id;
    }

    /**
     * Retourne le libellé du tag.
     * @return string Texte ou contenu HTML généré.
     */
    public function getLibelle(): string {
        return $this->libelle;
    }

    /**
     * Retourne la description du tag.
     * @return ?string Message d’erreur HTML, ou null si la vérification réussit.
     */
    public function getDescription(): ?string {
        return $this->description;
    }

    /**
     * Modifie la description du tag.
     * @param ?string $description description du tag.
     * @return void Aucune valeur.
     */
    public function setDescription(?string $description): void {
        $this->description = $description;
    }
}