<?php

use Controllers\UserController;

$account_link = "?page=login-register";
$account_text = "Registrace / Přihlášení";
$account_logged = false;

if (isset($_SESSION['rembrandt']) && UserController::auth($_SESSION['rembrandt']) !== null) {
    $account_link = "?page=account";
    $account_text = "Moje galerie";
    $account_logged = true;
}

?>



<div class="header">
    <div class="flex justify-start items-center">
        <div class="h-12 w-12 sm:mr-12 flex justify-center items-center">
            <a href="?page=main"><img class="w-full" src="public/assets/logo.png" alt=""></a>
        </div>
        <div class="ml-24 flex flex-row gap-6 sm:gap-12 uppercase">
            <a href="?page=about">O projektu</a>
            <a href="?page=main">Galerie</a>
        </div>
    </div>
    <div class="flex flex-row items-center gap-6">

        <?php if($account_logged) { ?> 
        <a href="?page=logout" class="button-big border-2 border-back-200 hover:border-back-300">
            <img src="public/assets/icons/logout.svg" alt="">
            Odhlásit se
        </a>
        <?php } ; ?>
        <a href="<?php echo $account_link ?>" class="button-big border-2 border-back-200 hover:border-back-300">
            <img src="public/assets/icons/user.svg" alt="">
            <?php echo $account_text ?>
        </a>
    </div>
</div>