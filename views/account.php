<?php

use Controllers\UserController;
use Controllers\GalleryController;
use Controllers\PaintingController;
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
    exit(); // Gallery with that id does not exists
}


$paintings = PaintingController::getAll($gallery_id);


?>


<div class="view flex-col">
    <div class="gallery-tab-wrapper mb-8 relative">
        <div class="gallery-tab">
            <a class="button-sml !absolute -top-2 -left-2 bg-red-200 hover:bg-red-300" href="?page=delete-account">
                <img src="public/assets/icons/trash.svg" alt="">
                Smazat účet
            </a>
            <div class="">
                <h3 class="text-3xl font-bold uppercase tracking-wider sm:m-0 mb-6 "><?php echo $gallery->name  ?></h3>
                <p>
                    <?php echo $gallery->description ?>
                </p>
            </div>
            <div class="w-fit flex flex-row flex-wrap sm:justify-end justify-center items-center gap-6">
                <a class="button-sc" href="?page=edit-gallery">Upravit galerii</a>
                <a class="button-pm" href="<?php echo '?page=gallery&id=' . $gallery->id ?>" target="_blank">Zobrazit galerii</a>
            </div>
        </div>
        <div class="absolute left-0 w-full h-full flex flex-row items-center z-20">
            <div class="absolute left-0 h-full w-full z-10 bg-gradient-to-l from-transparent to-back-100 to-100%"></div>
        </div>
    </div>

    <div>
        <div class="tool-bar">
            <a class="button-med text-main-700 bg-main-100 hover:bg-main-200" href="?page=new-painting">Přidat malbu</a>
        </div>
    </div>
    <div class="overflow-auto whitespace-nowrap block">
        <table class="table w-full min-w-96">
            <thead>
                <tr class="table-row">
                    <th class="table-col">Malba</th>
                    <th class="table-col">Název</th>
                    <th class="table-col">Autor</th>
                    <th class="table-col">AR Značka</th>
                    <th class="table-col"></th>
                </tr>
                <tbody>
                    <?php foreach($paintings as $painting) { ?>
                    <tr class="table-row">
                        <td class="table-col"><img class="max-w-32 max-h-32" src="<?php echo $painting->image_link  ?>" alt=""></td>
                        <td class="table-col">
                            <?php echo $painting->name ?>
                        </td>
                        <td class="table-col">
                            <?php echo $painting->author ?>
                        </td>
                        <td class="table-col"><img class="max-w-16 max-h-16" src="<?php echo $painting->marker->barcode_link  ?>" alt=""></td>
                        <td>
                            <a href="?page=delete-painting&id=<?php echo $painting->id ?>" class="button-med text-red-500 hover:text-red-800">
                                Smazat
                            </a>
                            <a href="?page=edit-painting&id=<?php echo $painting->id ?>" class="button-med text-main-700 bg-main-100 hover:bg-main-200">
                                Upravit
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </thead>
        </table>
    </div>
</div>



