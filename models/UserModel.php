<?php

namespace Models;

use Controllers\PaintingController;
use Controllers\UserController;

class UserModel
{
    public ?int $id = null;
    public ?string $username = null;
    public ?string $email = null;
    public GalleryModel $gallery;

    public function validate(): array {
        $errors = [];

        if (!preg_match('/^.{6,}$/', $this->username)) {
            $errors['username'] = "Uživatelské jméno musí mít alespoň 6 znaků";
        }

        if (strlen($this->username) > 40) {
            $errors['username'] = "Maximální délka jména je 30 znaků";
        }

        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $this->email)) {
            $errors['email'] = "Špatný formát pro email";
        }


        return $errors;
    }

    public function validatePassword(string $password): array {
        $errors = [];

        if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9]).{10,}$/', $password)) {
            $errors['password'] = "Heslo musí mít alespoň 10 znaků, z toho 1 velké písmeno a 1 číslici";
        }

        if (strlen($password) > 20) {
            $errors['password'] = "Maximální délka hesla je 20 znaků";
        }

        return $errors;
    }

    public function validateUserExists(string $username): array {
        $errors = [];

        $user = UserController::find($username);

        if ($user !== null) {
            $errors['user_exists'] = "Uživatel s jménem '" . $user->username . "' již exituje";
        }

        return $errors;
    }

    /**
     * Kontrola, jestli malba ma vazbu s uzivatele
     */
    public function checkPainting(int $painting_id): ?PaintingModel {
        $painting = null;
        $painting = PaintingController::get($painting_id);

        if ($painting === null) {
            return null;
        }

        if ($painting->gallery->id !== $this->gallery->id) {
            return null;
        }

        return $painting;
    }

    /**
     * Kontrola jestli galerie ma vazbu s uzivatelem
     */
    public function checkGallery(int $gallery_id): ?GalleryModel {
        if ($this->gallery->id !== $gallery_id) {
            return null;
        }
        return $this->gallery;
    }

    public function __construct() {
        $this->gallery = new GalleryModel();
    }
}