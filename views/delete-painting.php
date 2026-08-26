<?php

use Controllers\GalleryController;
use Controllers\PaintingController;
use Controllers\UserController;
use Utilities\Auth;

/**
 * Autentizace prihlaseneho uzivatele
 */
$user = Auth::auth();
if ($user == null) {
    header('Location: ?page=login-register');
}

$gallery_id = $user->gallery->id;

$gallery = GalleryController::get($gallery_id);

if ($gallery === null) {
    exit(); // Record with that id does not exists
}

if (!isset($_GET['id'])) {
    exit(); // Gallery id is not set
}

$painting_id = $_GET['id'];

if (!ctype_digit($painting_id)) {
    exit(); // Id is not integer
}

$painting_id = intval($painting_id);

$painting = $user->checkPainting($painting_id);

if ($painting === null) {
    exit(); // Record with that id does not exists
}

$painting_id = PaintingController::delete($painting->id);

if ($painting_id === null) {
  exit(); // Record with that id does not exists
}

$image_file = basename($painting->image_link);
unlink("public/images/" . $image_file);

header('Location: ?page=account');