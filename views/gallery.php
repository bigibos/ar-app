<?php

use Controllers\GalleryController;
use Controllers\PaintingController;
use Models\PaintingModel;

if (!isset($_GET['id'])) {
    exit(); // Neni nastaveno id
}

$gallery_id = $_GET['id'];

if (!ctype_digit($gallery_id)) {
    exit(); // Id neni cislo
}

$gallery_id = intval($gallery_id);

$gallery = GalleryController::get($gallery_id);

if ($gallery === null) {
    exit(); // Zaznam neexistuje
}

/*
if (isset($_POST['download-markers-zip'])) {
    GalleryController::downloadMarkersZip($gallery->id);
}
*/


$paintings = PaintingController::getAll($gallery_id);



?>


<div class="view flex-col">
    <div class="gallery-tab-wrapper mb-8 relative overflow-hidden bg-back-100">
        <div class="gallery-tab">
            <div>
                <h3 class="text-3xl font-bold uppercase tracking-wider sm:m-0 mb-6 "><?php echo $gallery->name  ?></h3>
                <p>
                    <?php echo $gallery->description ?>
                </p>
            </div>
            <div class="flex flex-row justify-start items-center">
                <a class="button-pm" href="<?php echo 'public/scene.php?gallery=' . $gallery->id ?>" target="_blank">
                    <img src="public/assets/icons/tour-virtual-white.svg" alt="">
                    Otevřít virtuální galerii
                </a>
            </div>
        </div>
        <div class="absolute left-0 w-full h-full flex flex-row items-center z-20">
            <!--<div class="absolute left-0 h-full w-full z-10 bg-gradient-to-l from-transparent to-main-700 to-75%"></div>-->
            <div class="absolute left-0 h-full w-full z-10  bg-gradient-to-l from-transparent to-back-100 to-75%""></div>
            <img class="absolute left-0 w-full opacity-25" src="public/images/gallery.jpg" alt="">
        </div>
    </div>

    <div>
        <form class="tool-bar" action="" method="POST">

            <a href="<?php echo 'public/download-markers.php?gallery=' . $gallery->id ?>" id="download-markers-zip" name="download-markers-zip" type="submit" class="button-med text-main-700 bg-main-100 hover:bg-main-200" >  
                Stáhnout značky 
            </a>

            <label class="inline-flex items-center cursor-pointer">
                <input id="show-markers-toggle" type="checkbox" value="" class="sr-only peer">
                <div class="toggle-button"></div>
                <span class="ms-3 font-semibold">Zobrazit značky</span>
            </label>
        </form>
        <div class="grid grid-cols-[repeat(auto-fill,minmax(360px,1fr))] gap-6">
            <?php foreach($paintings as $painting) { ?>
            <div class="painting-tab">
                <div class="h-56 relative overflow-hidden">
                    <div class="w-fit h-fit">
                        <img class="painting-image w-full h-full absolute object-contain object-center" src="<?php echo $painting->image_link ?>" alt="">
                        <img class="marker-image hidden w-full h-full absolute object-contain object-center" src="<?php echo $painting->marker->barcode_link ?>" alt="">
                    </div>
                </div>
                <div class="py-8 flex flex-col justify-center items-center gap-4">
                    <div class="flex flex-row items-center font-semibold">
                        <img class="h-4 pr-2" src="public/assets/icons/user.svg" alt="">
                        <?php echo $painting->author ?>
                    </div>
                    <div class="flex flex-row items-center font-semibold">
                        <img class="h-4 pr-2" src="public/assets/icons/picture.svg" alt="">
                        <?php echo $painting->name ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>


<script>
$(document).ready(function(){

    $("#show-markers-toggle").is(':checked', function(){
        $(".painting-image").toggleClass("hidden");
        $(".marker-image").toggleClass("hidden");
    });

    $("#show-markers-toggle").change(function(){
        var painting = $(".painting-image");
        var marker = $(".marker-image");
        if ($(this).is(':checked')) {
            painting.addClass("hidden");
            marker.removeClass("hidden");
        } else {
            painting.removeClass("hidden");
            marker.addClass("hidden");
        }
    });
});

</script>
