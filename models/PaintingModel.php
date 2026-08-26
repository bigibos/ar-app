<?php

namespace Models;


class PaintingModel
{
    public ?int $id = null;
    public ?string $name = null;
    public ?string $author = null;
    public ?string $image_link = null;
    public MarkerModel $marker;
    public GalleryModel $gallery;

    public function validate(): array {
        $errors = [];

        if (empty($this->name)) {
            $errors['name'] = "Název malby musí být vyplněn";
        }

        if (strlen($this->name) > 200) {
            $errors['name'] = "Maximální délka názvu je 200 znaků";
        }

        if (empty($this->author)) {
            $errors['author'] = "Jméno autora musí být vyplněno";
        }

        if (strlen($this->author) > 200) {
            $errors['author'] = "Maximální délka jména je 200 znaků";
        }

        if (empty($this->image_link)) {
            $errors['image_link'] = "Obrázek musí být vybrán";
        }


        return $errors;
    }

    public function __construct() {
        $this->marker = new MarkerModel();
        $this->gallery = new GalleryModel();
    }
}