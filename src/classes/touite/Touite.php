<?php

namespace iutnc\touiteur\touite;

class Touite {
    private int $id;
    private string $content;
    private string $posted_at;
    private ?string $image_path;
    private ?string $image_description;

    function __construct(int $id, string $content, string $posted_at, ?string $image_path, ?string $image_description) {
        $this->id = $id;
        $this->content = $content;
        $this->posted_at = $posted_at;
        $this->image_path = $image_path;
        $this->image_description = $image_description;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getContent(): string {
        return $this->content;
    }
    public function getPosted_at(): string {
        return $this->posted_at;
    }
    public function getImage_path(): ?string {
        return $this->image_path;
    }
    public function getImage_description(): ?string {
        return $this->image_description;
    }
}