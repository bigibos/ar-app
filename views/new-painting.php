<?php

use Controllers\GalleryController;
use Controllers\PaintingController;
use Models\PaintingModel;
use Utilities\Auth;
use Utilities\ImageSaver;

$errors = [];

/**
 * Autentizace prihlaseneho uzivatele
 */
$user = Auth::auth();
if ($user == null) {
    header('Location: ?page=account');
}


$gallery_id = $user->gallery->id;
$gallery = GalleryController::get($gallery_id);

if ($gallery === null) {
    exit(); // Záznam neexistuje
}

$painting = new PaintingModel();
$image_file = "";

if (
    isset($_POST['new-painting']) && 
    isset($_POST['name']) && 
    isset($_POST['author']) &&
    isset($_FILES['image-file'])
    ) {

    $painting = new PaintingModel();
    $painting->name = $_POST['name'];
    $painting->author = $_POST['author'];
    $painting->gallery = $gallery;

    $image_file = $_FILES['image-file'];

    $file_is_set = !empty($image_file['tmp_name']); // Jestli byl nahrán soubor

    $file_name = "malba_" . $painting->id . time(); // Novy nazev souboru
    
    $new_link = ImageSaver::makeImageUrl($image_file, $file_name);
    $painting->image_link = $file_is_set ? $new_link : null;
    
    $errors += $painting->validate();
    
    if (count($errors) === 0) {

        $new_link = ImageSaver::compressAndSave($image_file, $file_name, 1024); // Ulozeni souboru
        $painting->image_link = $new_link;

        /**
         * Ziskani dostupnych markeru a prirazeni jednoho k malbe
         */
        $available_markers = GalleryController::getAvailableMarkers($gallery->id);
        $painting->marker = $available_markers[0];
    
        $painting = PaintingController::add($painting);
    
        if ($painting === null ){
            unlink("public/images/" . $new_file); // Neuspech, soubor smazan
            exit();
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

    // Nahrany obrazek se zobrazi
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

    // Zruseni vyberu narhaneho obrazku
    cancelSelect.click(function() {
        imageFileSelect.val('');
        paintingImage.attr('src', ogImageLink);
        $(this).hide();
    });
});
</script>


<div class="view flex-col">
    <form class="form border-2 border-back-200" enctype="multipart/form-data" action="" method="POST">
        <h3 class="text-2xl font-bold uppercase tracking-wider absolute -top-4 left-4 "><span class="bg-back-100 ">Nová malba</span></h3>
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
                        <img id="painting-image" class="painting-image w-full h-full absolute object-contain object-center" src="" alt="">
                    </div>
                </div>
                <label class="label" for="username">
                    Obrázek malby
                    <div class="flex flex-row">
                        <input class="input cursor-pointer" id="image-file" name="image-file" type="file" >
                        <div class="button-sml text-red-600" id="cancel-image-file">Zrušit výběr</div>
                    </div>
                    <span class="input-error"><?php echo $errors['image_link'] ?? null ?></span>
                </label>
            </div>
        </div>
        <div class="flex flex-row gap-6">
            <a href="?page=account" class="button-tr" >Zrušit úpravy</a>
            <input class="button-pm" value="Uložit úpravy" type="submit" name="new-painting" id="edit-painting">
        </div>
    </form>
</div>