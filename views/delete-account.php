<?php

use Controllers\GalleryController;
use Utilities\Auth;

/**
 * Autentizace prihlaseneho uzivatele
 */
$user = Auth::auth();
if ($user == null) {
    header('Location: ?page=login-register');
}

GalleryController::delete($user->gallery->id);

header('Location: ?page=logout');
