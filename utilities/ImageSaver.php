<?php

namespace Utilities;

class ImageSaver {

   

    public static function makeFileName($source, $new_filename) {
        $filename = $source['name'];

        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $new_filename = $new_filename . "." . $extension;

        return $new_filename;
    }

    public static function makeImageUrl($source, $new_filename) {
        if (empty($source['name'])){
            return null;
        }

        var_dump($source);

        $info = getimagesize($source['tmp_name']);

        if (!$info) {
            return null;
        }

        if (
            $info['mime'] != 'image/jpeg' &&
            $info['mime'] != 'image/gif' &&
            $info['mime'] != 'image/png' &&
            $info['mime'] != 'image/bmp'
        ) {
            return null;
        }

        return $_ENV['BASE_URL'] . "/public/images/" . self::makeFileName($source, $new_filename);
    }

    public static function compressAndSave($source, $new_filename, $max_size_in_kB): ?string {

        if (empty($source['name'])){
            return null;
        }
        
        $full_filename = self::makeFileName($source, $new_filename);
        
        // $destinationDirectory = "./";
        // $destination = $destinationDirectory . $new_filename;
        $destination = "public/images/" . DIRECTORY_SEPARATOR . $full_filename;
        
        $kB_size = $source['size'] / 1024;
        $quality = 100;
        if ($kB_size > $max_size_in_kB) {
            $quality = intval(($max_size_in_kB / $kB_size) * 100);
        }
        
        $info = getimagesize($source['tmp_name']);

        if (!$info) {
            return null;
        }
        
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source['tmp_name']);
        } else if ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source['tmp_name']);
        } else if ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source['tmp_name']);
        } else if ($info['mime'] == 'image/bmp') {
            $image = imagecreatefrombmp($source['tmp_name']);
        } else {
            return null;
        }
        

        if (imagejpeg($image, $destination,  $quality)) {
            return self::makeImageUrl($source, $new_filename);
        }

        return null;
    }

    public static function checkImage($source): bool {
        // Kontrola, zda byl nahrán soubor
        if (!isset($source['tmp_name']) || empty($source['tmp_name'])) {
            return false;
        }

        // Podporované formáty obrázků
        $supportedFormats = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff'];
        
        // Získání informací o obrázku
        $info = getimagesize($source['tmp_name']);
        
        // Kontrola, zda je formát obrázku podporovaný
        if (!in_array($info['mime'], $supportedFormats)) {
            return false;
        }

        $targetWidth = 800; 
        $targetHeight = ceil($targetWidth * ($info[1] / $info[0]));

        if ($info[0] != $targetWidth || $info[1] != $targetHeight) {
            return false;
        }

        return true;
    }
}


?>
