<?php

namespace Controllers;

use Models\PaintingModel;
use Database\DB;
use Exception;
use PDO;

class PaintingController
{
    public static function add(PaintingModel $model, ?PDO $conn = null): ?PaintingModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "INSERT INTO paintings (name, author, image_link, gallery_id, marker_id) 
            VALUES (?, ?, ?, ?, ?)"
        );

        try {
            $stmt->execute([
                $model->name,
                $model->author,
                $model->image_link,
                $model->gallery->id,
                $model->marker->id
            ]);
        } catch (Exception $e) {
            return null;
        }

        // V PostgreSQL je ideální zajistit, že lastInsertId() vrací správné ID (v závislosti na názvu sekvence)
        $model->id = (int)$conn->lastInsertId();

        return $model;
    }

    public static function update(PaintingModel $model, ?PDO $conn = null): ?PaintingModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "UPDATE paintings SET
                name = ?, 
                author = ?, 
                image_link = ?,
                gallery_id = ?,
                marker_id = ?
            WHERE id = ?"
        );

        try {
            $stmt->execute([
                $model->name,
                $model->author,
                $model->image_link,
                $model->gallery->id,
                $model->marker->id,
                $model->id
            ]);
        } catch (Exception $e) {
            return null;
        }

        return $model;
    }

    public static function get(int $id, ?PDO $conn = null): ?PaintingModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, name, author, image_link, gallery_id, marker_id
            FROM paintings 
            WHERE id = ?"
        );

        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $model = new PaintingModel();
            $model->id = (int)$data['id'];
            $model->name = $data['name'];
            $model->author = $data['author'];
            $model->image_link = $data['image_link'];
            $model->gallery = GalleryController::get($data['gallery_id'], $conn);
            $model->marker = MarkerController::get($data['marker_id'], $conn);

            return $model;
        }

        return null;
    }

    public static function getAll(?int $gallery_id = null, ?PDO $conn = null): array {
        $filter = "";
        $params = [];
        if ($gallery_id !== null) {
            $filter = "WHERE gallery_id = ?";
            $params[] = $gallery_id;
        }

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, name, author, image_link, gallery_id, marker_id
            FROM paintings $filter"
        );

        $stmt->execute($params);
        $result = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $model = new PaintingModel();   
            $model->id = (int)$data['id'];
            $model->name = $data['name'];
            $model->author = $data['author'];
            $model->image_link = $data['image_link'];
            $model->gallery = GalleryController::get($data['gallery_id'], $conn);
            $model->marker = MarkerController::get($data['marker_id'], $conn);
            
            $result[] = $model;
        }

        return $result;
    }

    public static function delete(?int $id, ?PDO $conn = null): ?int {
        if ($id === null) return null;

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "DELETE FROM paintings WHERE id = ?"
        );

        try {
            $stmt->execute([$id]);
        } catch (Exception $e) {
            return null;
        }

        return $id;  
    } 
}