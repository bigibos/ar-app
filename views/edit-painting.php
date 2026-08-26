<?php

use Controllers\GalleryController;
use Controllers\PaintingController;
use Utilities\Auth;
use Utilities\ImageSaver;


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
    exit(); // Zaznam neexistuje
}

if (!isset($_GET['id'])) {
    exit(); // Neni nastaveno id malby
}

$painting_id = $_GET['id'];

if (!ctype_digit($painting_id)) {
    exit(); // id neni cislo
}
$painting_id = intval($painting_id);

$painting = $user->checkPainting($painting_id);


if ($painting === null) {
    exit();
}

if (
    isset($_POST['edit-painting']) && 
    isset($_POST['name']) && 
    isset($_POST['author']) &&
    isset($_FILES['image-file'])
    ) {
        
    $painting->name = $_POST['name'];
    $painting->author = $_POST['author'];
    
    $image_file = $_FILES['image-file'];


    $file_is_set = !empty($image_file['tmp_name']);

    if ($file_is_set) {
        if (ImageSaver::makeImageUrl($image_file, '') == null) {
            $painting->image_link = null;
        }
    }

    $errors += $painting->validate();


    if (count($errors) === 0) {
    
        $file_name =  "malba_" . $painting->id . "_" . time();
    
        $old_link = $painting->image_link;
        $old_file = basename($painting->image_link);
    
        /**
         * Byl nahran novy soubor
         * Pokus o jeho ulozeni
         */
        if ($file_is_set) {
            $new_link = ImageSaver::compressAndSave($image_file, $file_name, 1024);
            $new_file = basename($new_link);
    
            if ($new_link === null) {
                exit(); // Nepodarilo se ulozit obrazek
            }
        }
        

        $painting->image_link = $file_is_set ? $new_link : $old_link;
        $painting = PaintingController::update($painting);
    
        /**
         * Kontrola uspechu zapisu do databaze
         */
        if ($painting === null){
            if ($file_is_set) {
                unlink("public/images/" . $new_file); // Pokud soubor byl ulozen, ale zapis do db selhal, smaz nove ulozeny soubor 
            }
            exit();
        }
        if ($file_is_set) {
            unlink("public/images/" . $old_file); // Pokud je vse ok, smaz stary soubor
        }
        header('Location: ?page=account');
    } 

}

?>

<script>
    
$(document).ready(function(){


    var paintingImage = $('#painting-image');
    var imageFileSelect = $('#image-file');
    var cancelSelect = $('#cancel-image-file');


    cancelSelect.hide();
    var ogImageLink = paintingImage.attr('src');

    imageFileSelect.change(function(){
        cancelSelect.show();
        var file = this.files[0];
        console.log(file);
        if(file){
            var reader = new FileReader();
            reader.onload = function(e){
                paintingImage.attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    cancelSelect.click(function() {
        imageFileSelect.val('');
        paintingImage.attr('src', ogImageLink);
        $(this).hide();
    });
});
</script>


<div class="view flex-col">
    <form class="form border-2 border-back-200" enctype="multipart/form-data" action="" method="POST">
        <h3 class="text-2xl font-bold uppercase tracking-wider absolute -top-4 left-4 "><span class="bg-back-100 ">Úprava malby</span></h3>
        <div class="flex flex-col items-center gap-8">
            <div class="flex sm:flex-row flex-col justify-center w-full gap-8">
                <label class="label" for="name">
                    Název
                    <input class="input" type="text" name="name" id="name" value="<?php echo $painting->name ?>">
                    <span class="input-error"><?php echo $errors['name'] ?? null ?></span>
                </label>
                <label class="label" for="author">
                    Autor
                    <input class="input" type="text" name="author" id="author" value="<?php echo $painting->author ?>">
                    <span class="input-error"><?php echo $errors['author'] ?? null ?></span>
                </label>
            </div>
            <div class="flex flex-col  gap-8">
                <div class="relative overflow-hidden">
                    <div class="w-full h-80">
                        <img id="painting-image" class="painting-image w-full h-full absolute object-contain object-center" src="<?php echo $painting->image_link ?>" alt="">
                    </div>
                </div>
                <label class="label" for="username">
                    Obrázek malby
                    <div class="flex flex-row">
                        <input class="input cursor-pointer" id="image-file" name="image-file" type="file">
                        <div class="button-sml text-red-600" id="cancel-image-file">Zrušit výběr</div>
                    </div>
                    <span class="input-error"><?php echo $errors['image_link'] ?? null ?></span>
                </label>
            </div>
        </div>
        <div class="flex flex-row gap-6">
            <a href="?page=account" class="button-tr" >Zrušit úpravy</a>
            <input class="button-pm" value="Uložit úpravy" type="submit" name="edit-painting" id="edit-painting">
        </div>
    </form>
</div>