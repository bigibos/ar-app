<?php 

error_reporting(0);
ini_set('display_errors', 0);

require('../autoloader.php');

use Controllers\GalleryController;
use Models\GalleryModel;
use Controllers\PaintingController;
use Database\DB;
use Models\MarkerModel;



if (!isset($_GET['gallery'])) {
    exit(); // Neni ovdeneo id galerie
}

$gallery_id = $_GET['gallery'];

if (!ctype_digit($gallery_id)) {
    exit(); // Id neni cislo
}

$gallery_id = intval($gallery_id);

$gallery = GalleryController::get($gallery_id);

if ($gallery === null) {
    exit(); // Zaznam neexistuje
}


$conn = DB::connect();
$stmt = $conn->prepare(
    "SELECT p.id, p.name, p.author, m.barcode_link, g.name FROM paintings as p
    JOIN ar_markers as m ON m.id = p.marker_id
    JOIN galleries as g ON g.id = p.gallery_id
    WHERE gallery_id = ?"
);

$stmt->bind_param(
    "i",
    $gallery->id
);

$stmt->execute();


/**
 * p - painting
 * m - marker
 */
$stmt->bind_result($p_id, $p_name, $p_author, $m_barcode_link, $g_name);

$images = [];
$zip_name = "Galerie";

$cnt = 0;
while ($stmt->fetch()) {
    $image_file = basename($m_barcode_link);
    $image_link = "markers/" . $image_file;

    $image_name = $p_author . "_" . $p_name . "_" . ++$cnt;
    $image_name = preg_replace('!\s+!', '-', $image_name);

    $zip_name = $g_name;

    $images[] = array(
        "image_link" => $image_link,
        "image_name" => $image_name . "." . pathinfo($image_link, PATHINFO_EXTENSION)
    );
}


/**
 * Vytvareni a stahovani zip souboru s obrazky AR znacek
 */
$zip_name = $zip_name . "_ZNACKY.zip";
$zip_name = preg_replace('!\s+!', '-', $zip_name);
$zip = new ZipArchive();

$tmp_file = tempnam('tmp','');
$zip->open($tmp_file, ZipArchive::CREATE);

foreach($images as $image){
    $download_file = file_get_contents($image["image_link"]);
    $zip->addFromString($image["image_name"], $download_file);
}

$zip->close();

header('Content-Disposition: attachment; filename=' . $zip_name);
header('Content-type: application/zip');
header("Content-Transfer-Encoding: Binary");
header("Pragma: no-cache");
header("Expires: 0");
readfile($tmp_file);
unlink($tmp_file);