<?php

namespace Controllers;

use Models\GalleryModel;
use Controllers\PaintingController;
use Database\DB;
use Exception;
use Models\MarkerModel;
use mysqli;
use ZipArchive;

class GalleryController
{
    public static function add(GalleryModel $model, ?mysqli $conn = null): ?GalleryModel {


        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "INSERT INTO galleries (name, description) 
            VALUES (?, ?)",
        );

        $stmt->bind_param(
            "ss",
            $model->name,
            $model->description
        );

        try {
            $stmt->execute();
        }
        catch (Exception $e) {
            return null;
        }

        $model->id = mysqli_insert_id($conn);

        return  $model;
    }

    public static function update(GalleryModel $model, ?mysqli $conn = null): ?GalleryModel {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "UPDATE galleries SET
                name = ?,
                description = ?
            WHERE id = ?",
        );


        $stmt->bind_param(
            "ssi",
            $model->name,
            $model->description,
            $model->id
        );

        try {
            $stmt->execute();
        }
        catch (Exception $e) {
            return null;
        }


        return $model;

    }


    public static function get(?int $id, ?mysqli $conn = null): ?GalleryModel {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, name, description
            FROM galleries 
            WHERE id = ?",
        );

        $stmt->bind_param(
            "i",
            $id
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $data = $result->fetch_assoc();
            $model = new GalleryModel();

            $model->id = $id;
            $model->name = $data['name'];
            $model->description = $data['description'];
            
            return $model;
        }

        return null;
    }
    

    public static function getAll(?mysqli $conn = null): array {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, name, description
            FROM galleries"
        );

        $stmt->execute();

        $stmt->bind_result($id, $name, $description);

        $result = [];

        while ($stmt->fetch()) {

            $model = new GalleryModel();   

            $model->id = $id;
            $model->name = $name;
            $model->description = $description;
            
            $result[] = $model;
        }


        return $result;
    }


    /**
     * Kompletni vymazani galeire, vcetne svazaneho uctu a maleb
     * Ucet se maze taky, protoze je pevne svazan s galerii
     */
    public static function delete(?int $id, ?mysqli $conn = null): ?int {

        $gallery = GalleryController::get($id);

        if ($gallery === null) {
            return null;
        }

        $paintings = PaintingController::getAll($id);


        try {
            $conn = $conn == null ? DB::connect() : $conn;
            mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);
            
            $stmt = $conn->prepare(
                "DELETE FROM paintings WHERE gallery_id = ?",
            );
            
            $stmt->bind_param(
                "i",
                $id
            );
            
            $stmt->execute();

            $stmt = $conn->prepare(
                "DELETE FROM users WHERE gallery_id = ?",
            );
            
            $stmt->bind_param(
                "i",
                $id
            );
            
            $stmt->execute();
            
            $stmt = $conn->prepare(
                "DELETE FROM galleries WHERE id = ?",
            );
            
            $stmt->bind_param(
                "i",
                $id
            );
            
            $stmt->execute();
            mysqli_commit($conn);
        }
        catch(Exception $ex) {
            mysqli_rollback($conn); 
            return null;
        }

        foreach ($paintings as $painting) {
            $image_file = basename($painting->image_link);
            unlink("public/images/" . $image_file);
        }


        return  $id;  
    } 

    /**
     * Ziska dostupné AR znacky (ty ktere jeste nejsou prirazeny k zadne malbe)
     */
    public static function getAvailableMarkers(?int $id, ?mysqli $conn = null) {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT m.id, m.barcode_value, m.barcode_link
            FROM ar_markers as m
            LEFT JOIN paintings as p ON m.id = p.marker_id AND p.gallery_id = ?
            WHERE p.id IS NULL"
        );

        $stmt->bind_param(
            "i",
            $id
        );

        $stmt->execute();

        $stmt->bind_result($id, $barcode_value, $barcode_link);

        $result = [];

        while ($stmt->fetch()) {

            $model = new MarkerModel();   

            $model->id = $id;
            $model->barcode_value = $barcode_value;
            $model->barcode_link = $barcode_link;
            
            $result[] = $model;
        }

        return $result;
    }


    public static function downloadMarkersZip(?int $id, ?mysqli $conn = null) {


        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT p.id, p.name, p.author, m.barcode_link, g.name FROM paintings as p
            JOIN ar_markers as m ON m.id = p.marker_id
            JOIN galleries as g ON g.id = p.gallery_id
            WHERE gallery_id = ?"
        );

        $stmt->bind_param(
            "i",
            $id
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
    }

}
