<?php

namespace Utilities;

use Controllers\UserController;
use Models\UserModel;

class Auth {
    public static function auth(): ?UserModel {
        $user = null;
        if (isset($_SESSION['rembrandt'])) {
            $user = UserController::auth($_SESSION['rembrandt']);
                if ($user === null) {
                    return null;
                }
            }
            else {
                return null;
            }
            return $user;
        }
}