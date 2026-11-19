<?php

namespace iutnc\touiteur\touite;

/**
 * Classe Touite de l’application.
 */
class Touite {
    /**
     * Valeur utilisée pour id.
     */
    private ?int $id;
    /**
     * Valeur utilisée pour content.
     */
    private string $content;
    /**
     * Valeur utilisée pour postedAt.
     */
    private string $postedAt;
    /**
     * Valeur utilisée pour note.
     */
    private int $note;
    /**
     * Valeur utilisée pour mediaPath.
     */
    private ?string $mediaPath;
    /**
     * Valeur utilisée pour mediaDescription.
     */
    private ?string $mediaDescription;

    /**
     * Initialise les données nécessaires à cet objet.
     * @param string $content contenu du touite.
     * @param string $postedAt date de publication.
     * @param int $note valeur numérique de la note.
     * @param ?string $mediaPath chemin du média.
     * @param ?string $mediaDescription description du média.
     * @param ?int $id identifiant de la ressource.
     */
    public function __construct(string $content, string $postedAt, int $note, ?string $mediaPath, ?string $mediaDescription, ?int $id = null) {
        $this->id = $id;
        $this->content = $content;
        $this->postedAt = $postedAt;
        $this->note = $note;
        $this->mediaPath = $mediaPath;
        $this->mediaDescription = $mediaDescription;
    }

    /**
     * Retourne l’identifiant du touite.
     * @return ?int Description du média, ou null si elle n’est pas définie.
     */
    public function getId(): ?int {
        return $this->id;
    }

    /**
     * Définit l’identifiant du touite.
     * @param int $id identifiant de la ressource.
     * @return void Aucune valeur.
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * Retourne le contenu du touite.
     * @return string Texte ou contenu HTML généré.
     */
    public function getContent(): string {
        return $this->content;
    }

    /**
     * Retourne la date de publication du touite.
     * @return string Texte ou contenu HTML généré.
     */
    public function getPostedAt(): string {
        return $this->postedAt;
    }

    /**
     * Retourne la note du touite.
     * @return int Valeur numérique calculée.
     */
    public function getNote(): int {
        return $this->note;
    }

    /**
     * Retourne le chemin du média associé au touite.
     * @return ?string Message d’erreur HTML, ou null si la vérification réussit.
     */
    public function getMediaPath(): ?string {
        return $this->mediaPath;
    }

    /**
     * Retourne la description du média associé au touite.
     * @return ?string Message d’erreur HTML, ou null si la vérification réussit.
     */
    public function getMediaDescription(): ?string {
        return $this->mediaDescription;
    }
}
