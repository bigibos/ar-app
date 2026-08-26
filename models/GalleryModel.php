<?php

namespace Models;
use Controllers\PaintingController;

class GalleryModel
{
    public ?int $id = null;
    public ?string $name = null;
    public ?string $description = null;

    public function validate(): array {
        $errors = [];

        if (empty($this->name)) {
            $errors['name'] = "Název galerie musí být vyplněn";
        }

        if (strlen($this->name) > 200) {
            $errors['name'] = "Maximální délka názvu je 200 znaků";
        }

        if (strlen($this->description) > 1000) {
            $errors['description'] = "Maximální délka popisu 1000 znaků";
        }

        return $errors;
    }
}