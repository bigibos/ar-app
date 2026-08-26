<?php

use Controllers\GalleryController;
use Controllers\UserController;
use Database\DB;
use Models\GalleryModel;
use Models\UserModel;
use Utilities\Auth;

$errors = [];

/**
 * Autentizace prihlaseneho uzivatele
 */
$user = Auth::auth();
if ($user != null) {
    header('Location: ?page=account');
}


/**
 * Přihlášení uživatele
 */
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $auth_token = UserController::login($username, $password);

    if ($auth_token === null) {
        $errors['wrong_login'] = "Nesprávné jméno, nebo heslo";
    }
    else {
        $_SESSION['rembrandt'] = $auth_token;
        header('Location: ?page=account');
    }
}

/**
 * Registrace uživatele
 */
if (isset($_POST['register'])) {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    
    $gallery = new GalleryModel();
    $gallery->name = "Nová galerie";
    
    $user = new UserModel();
    $user->email = $email;
    $user->username = $username;

    $errors += $user->validate();
    $errors += $user->validatePassword($password);
    $errors += $user->validateUserExists($user->username);

    if (count($errors) === 0) {
        try {
            $conn = DB::connect(); 
            mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);
            $gallery = GalleryController::add($gallery, $conn);
            $user->gallery = $gallery;
            $user = UserController::add($user, $password, $conn);

            if ($gallery == null || $user == null) {
                mysqli_rollback($conn); 
            }
            else {
                mysqli_commit($conn);
            }
        }
        catch(Exception $ex) {
            mysqli_rollback($conn); 
        }
    }
    
}



?>

<div class="view justify-center">
    <div class="w-full flex flex-col gap-16">
        <form class="form border-2 border-back-200"  action="" method="POST">
            <h3 class="text-2xl font-bold uppercase tracking-wider absolute -top-4 left-4 ">
                <span class="bg-back-100 ">Přihlášení</span>
            </h3>
            <div class="flex flex-row flex-wrap gap-8">
                <label class="label" for="username">
                    Uživatelské jméno
                    <input class="input" type="text" name="username" id="username">
                    <span class="input-error"><?php echo $errors['wrong_login'] ?? null ?></span>
                </label>
                <label class="label" for="password">
                    Heslo
                    <input class="input" type="password" name="password" id="password">
                    <span class="input-error"><?php echo $errors['wrong_login'] ?? null ?></span>
                </label>
            </div>
            <input class="button-pm" value="Přihlásit se" type="submit" name="login" id="login">
        </form>
        
        <form class="form border-2 border-back-200" method="POST">
            <h3 class="text-2xl font-bold uppercase tracking-wider absolute -top-4 left-4 ">
                <span class="bg-back-100 ">Registrace</span>
            </h3>
            <div class="flex flex-row flex-wrap gap-8">
                <label class="label" for="email">
                    Email
                    <input class="input" type="email" name="email" id="email">
                    <span class="input-error"><?php echo $errors['email'] ?? null ?></span>
                </label>
                <label class="label" for="username">
                    Uživatelské jméno
                    <input class="input" type="text" name="username" id="username">
                    <span class="input-error"><?php echo $errors['username'] ?? null ?></span>
                </label>
                <label class="label" for="password">
                    Heslo
                    <input class="input" type="password" name="password" id="password">
                    <span class="input-error"><?php echo $errors['password'] ?? null ?></span>
                </label>
            </div>
            <label class="label">
                <input class="button-sc" value="Registrovat" type="submit" name="register" id="register">
                <span class="input-error"><?php echo $errors['user_exists'] ?? null ?></span>
            </label>
        </form>
    </div>
</div>