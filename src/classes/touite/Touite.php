<?php

namespace iutnc\touiteur\touite;

class Touite{
    private int $id;
    private string $content;
    private string $posted_at;
    private string $image_path;
    private string $image_description;

    function __construct(int $id_para, string $content_para, string $posted_at_para, string $image_path_para, string $image_description_para){
        $this->id = $id_para;
        $this->content = $content_para;
        $this->posted_at = $posted_at_para;
        $this->image_path = $image_path_para;
        $this->image_description = $image_description_para;
    }

    public function getId(): int{
        return $this->id;
    }

    public function getContent(): string{
        return $this->content;
    }
    public function getPostedAt(): string{
        return $this->posted_at;
    }
    public function getImagePath(): string{
        return $this->image_path;
    }
    public function getImageDescription(): string{
        return $this->image_description;
    }

}