<?php

use Controllers\UserController;
use Utilities\Auth;

/**
 * Autentizace prihlaseneho uzivatele
 */
$user = Auth::auth();
if ($user == null) {
    header('Location: ?page=account');
}

UserController::logout($user->username);

header('Location: ?page=main');
