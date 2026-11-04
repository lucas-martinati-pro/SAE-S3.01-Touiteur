<?php

namespace iutnc\touiteur\touite;

class Touite {
    private ?int $id;
    private string $content;
    private string $posted_at;
    private int $note;
    private ?string $media_path;
    private ?string $media_description;

    public function __construct(string $content, string $posted_at, int $note, ?string $media_path, ?string $media_description, ?int $id = null) {
        $this->id = $id;
        $this->content = $content;
        $this->posted_at = $posted_at;
        $this->note = $note;
        $this->media_path = $media_path;
        $this->media_description = $media_description;
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

    public function getPosted_at(): string {
        return $this->posted_at;
    }

    public function getNote(): int {
        return $this->note;
    }

    public function getmedia_path(): ?string {
        return $this->media_path;
    }

    public function getmedia_description(): ?string {
        return $this->media_description;
    }
}