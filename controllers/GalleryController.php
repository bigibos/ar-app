<?php

namespace Controllers;

use Models\GalleryModel;
use Controllers\PaintingController;
use Database\DB;
use Exception;
use Models\MarkerModel;
use PDO;
use ZipArchive;

class GalleryController
{
    public static function add(GalleryModel $model, ?PDO $conn = null): ?GalleryModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "INSERT INTO galleries (name, description) 
            VALUES (?, ?)"
        );

        try {
            $stmt->execute([$model->name, $model->description]);
        } catch (Exception $e) {
            return null;
        }

        $model->id = (int)$conn->lastInsertId();

        return $model;
    }

    public static function update(GalleryModel $model, ?PDO $conn = null): ?GalleryModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "UPDATE galleries SET
                name = ?,
                description = ?
            WHERE id = ?"
        );

        try {
            $stmt->execute([$model->name, $model->description, $model->id]);
        } catch (Exception $e) {
            return null;
        }

        return $model;
    }

    public static function get(?int $id, ?PDO $conn = null): ?GalleryModel {
        if ($id === null) return null;

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, name, description
            FROM galleries 
            WHERE id = ?"
        );

        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $model = new GalleryModel();
            $model->id = (int)$data['id'];
            $model->name = $data['name'];
            $model->description = $data['description'];
            
            return $model;
        }

        return null;
    }
    
    public static function getAll(?PDO $conn = null): array {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, name, description
            FROM galleries"
        );

        $stmt->execute();
        $result = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $model = new GalleryModel();   
            $model->id = (int)$data['id'];
            $model->name = $data['name'];
            $model->description = $data['description'];
            
            $result[] = $model;
        }

        return $result;
    }

    public static function delete(?int $id, ?PDO $conn = null): ?int {
        if ($id === null) return null;

        $conn = $conn == null ? DB::connect() : $conn;
        $gallery = GalleryController::get($id, $conn);

        if ($gallery === null) {
            return null;
        }

        $paintings = PaintingController::getAll($id, $conn);

        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("DELETE FROM paintings WHERE gallery_id = ?");
            $stmt->execute([$id]);

            $stmt = $conn->prepare("DELETE FROM users WHERE gallery_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $conn->prepare("DELETE FROM galleries WHERE id = ?");
            $stmt->execute([$id]);

            $conn->commit();
        } catch(Exception $ex) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return null;
        }

        foreach ($paintings as $painting) {
            $image_file = basename($painting->image_link);
            if (file_exists("public/images/" . $image_file)) {
                unlink("public/images/" . $image_file);
            }
        }

        return $id;  
    } 

    public static function getAvailableMarkers(?int $id, ?PDO $conn = null) {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT m.id, m.barcode_value, m.barcode_link
            FROM ar_markers as m
            LEFT JOIN paintings as p ON m.id = p.marker_id AND p.gallery_id = ?
            WHERE p.id IS NULL"
        );

        $stmt->execute([$id]);
        $result = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $model = new MarkerModel();   
            $model->id = (int)$data['id'];
            $model->barcode_value = $data['barcode_value'];
            $model->barcode_link = $data['barcode_link'];
            
            $result[] = $model;
        }

        return $result;
    }

    public static function downloadMarkersZip(?int $id, ?PDO $conn = null) {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT p.id, p.name, p.author, m.barcode_link, g.name AS gallery_name FROM paintings as p
            JOIN ar_markers as m ON m.id = p.marker_id
            JOIN galleries as g ON g.id = p.gallery_id
            WHERE p.gallery_id = ?"
        );

        $stmt->execute([$id]);

        $images = [];
        $zip_name = "Galerie";
        $cnt = 0;

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $image_file = basename($data['barcode_link']);
            $image_link = "markers/" . $image_file;

            $image_name = $data['author'] . "_" . $data['name'] . "_" . ++$cnt;
            $image_name = preg_replace('!\s+!', '-', $image_name);

            $zip_name = $data['gallery_name'];

            $images[] = array(
                "image_link" => $image_link,
                "image_name" => $image_name . "." . pathinfo($image_link, PATHINFO_EXTENSION)
            );
        }

        $zip_name = $zip_name . "_ZNACKY.zip";
        $zip_name = preg_replace('!\s+!', '-', $zip_name);
        $zip = new ZipArchive();
    
        $tmp_file = tempnam('tmp','');
        $zip->open($tmp_file, ZipArchive::CREATE);
    
        foreach($images as $image){
            if (file_exists($image["image_link"])) {
                $download_file = file_get_contents($image["image_link"]);
                $zip->addFromString($image["image_name"], $download_file);
            }
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