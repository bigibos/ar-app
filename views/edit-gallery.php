<?php

use Controllers\GalleryController;
use Controllers\UserController;
use Models\galleryModel;
use Utilities\Auth;

$errors = [];

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

if (
    isset($_POST['edit-gallery']) && 
    isset($_POST['name']) && 
    isset($_POST['description'])
    ) {

    $gallery->id = $gallery_id;
    $gallery->name = $_POST['name'];
    $gallery->description = $_POST['description'];
    
    $errors = $gallery->validate();
    
    if (count($errors) === 0) {
        $gallery = GalleryController::update($gallery);
    
        if ($gallery === null ){
            echo "Uprava byla neuspesna";
            exit();
        }
        header('Location: ?page=account');
    }

}

?>


<div class="view flex-col">
    <form class="form border-2 border-back-200"  action="" method="POST">
        <h3 class="text-2xl font-bold uppercase tracking-wider absolute -top-4 left-4 "><span class="bg-back-100 ">Úprava galerie</span></h3>
        <div class="flex flex-col items-center gap-8">
            <div class="flex sm:flex-row flex-col justify-center w-full gap-8">
                <label class="label" for="name">
                    Název
                    <input class="input" type="text" name="name" id="name" value="<?php echo $gallery->name ?>">
                    <span class="input-error"><?php echo $errors['name'] ?? null ?></span>
                </label>
                <label class="label" for="description">
                    Popis
                    <textarea class="input" name="description" id="description" rows="5" cols="50"><?php echo $gallery->description ?></textarea>
                    <span class="input-error"><?php echo $errors['description'] ?? null ?></span>
                </label>
            </div>
        </div>
        <div class="flex flex-row gap-6">
            <a href="?page=account" class="button-tr" >Zrušit úpravy</a>
            <input class="button-pm" value="Uložit úpravy" type="submit" name="edit-gallery" id="edit-gallery">
        </div>
    </form>
</div>