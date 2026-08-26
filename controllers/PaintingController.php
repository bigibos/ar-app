<?php

namespace Controllers;

use Models\PaintingModel;
use Database\DB;
use Exception;
use mysqli;

class PaintingController
{
    public static function add(PaintingModel $model, ?mysqli $conn = null): ?PaintingModel {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "INSERT INTO paintings (name, author, image_link, gallery_id, marker_id) 
            VALUES (?, ?, ?, ?, ?)",
        );

        $stmt->bind_param(
            "sssii",
            $model->name,
            $model->author,
            $model->image_link,
            $model->gallery->id,
            $model->marker->id
        );

        try {
            $stmt->execute();
        }
        catch (Exception $e) {
            return null;
        }

        $model->id = mysqli_insert_id($conn);

        return $model;
    }

    public static function update(PaintingModel $model, ?mysqli $conn = null): ?PaintingModel {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "UPDATE paintings SET
                name = ?, 
                author = ?, 
                image_link = ?,
                gallery_id = ?,
                marker_id = ?
            WHERE id = ?",
        );


        $stmt->bind_param(
            "sssiii",
            $model->name,
            $model->author,
            $model->image_link,
            $model->gallery->id,
            $model->marker->id,
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

    public static function get(int $id, ?mysqli $conn = null): ?PaintingModel {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, name, author, image_link, gallery_id, marker_id
            FROM paintings 
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
            $model = new PaintingModel();

            $model->id = $id;
            $model->name = $data['name'];
            $model->author = $data['author'];
            $model->image_link = $data['image_link'];
            $model->gallery = GalleryController::get($data['gallery_id']);
            $model->marker = MarkerController::get($data['marker_id']);


            return $model;
        }


        // Create profile model from retrieved data from database

        return null;
    }



    
    public static function getAll(?int $gallery_id = null, ?mysqli $conn = null): array {

        /**
         * Nastaveni filtru vyberu maleb podle id galerie
         */
        $filter = "";
        if ($gallery_id !== null) {
            $filter = "WHERE gallery_id = ". $gallery_id;
        }

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, name, author, image_link, gallery_id, marker_id
            FROM paintings $filter"
        );

        $stmt->execute();

        $stmt->bind_result($id, $name, $author, $image_linkg, $gallery_id, $marker_id);

        $result = [];

        while ($stmt->fetch()) {

            $model = new PaintingModel();   

            $model->id = $id;
            $model->name = $name;
            $model->author = $author;
            $model->image_link = $image_linkg;
            $model->gallery = GalleryController::get($gallery_id);
            $model->marker = MarkerController::get($marker_id);
            
            $result[] = $model;
        }


        return $result;
    }

    public static function delete(?int $id, ?mysqli $conn = null): ?int {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "DELETE FROM paintings WHERE id = ?",
        );

        $stmt->bind_param(
            "i",
            $id
        );

        try {
            $stmt->execute();
        }
        catch (Exception $e) {
            return null;
        }


        return  $id;  
    } 
}
