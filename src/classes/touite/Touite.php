<?php

namespace iutnc\touiteur\touite;

class Touite {
    private ?int $id;
    private string $content;
    private string $postedAt;
    private int $note;
    private ?string $mediaPath;
    private ?string $mediaDescription;

    public function __construct(string $content, string $postedAt, int $note, ?string $mediaPath, ?string $mediaDescription, ?int $id = null) {
        $this->id = $id;
        $this->content = $content;
        $this->postedAt = $postedAt;
        $this->note = $note;
        $this->mediaPath = $mediaPath;
        $this->mediaDescription = $mediaDescription;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getPostedAt(): string {
        return $this->postedAt;
    }

    public function getNote(): int {
        return $this->note;
    }

    public function getMediaPath(): ?string {
        return $this->mediaPath;
    }

    public function getMediaDescription(): ?string {
        return $this->mediaDescription;
    }
}
