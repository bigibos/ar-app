<?php

namespace Controllers;

use Models\MarkerModel;
use Database\DB;
use Exception;
use PDO;

class MarkerController
{
    public static function get(int $id, ?PDO $conn = null): ?MarkerModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, barcode_value, barcode_link
            FROM ar_markers 
            WHERE id = ?"
        );

        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $model = new MarkerModel();
            $model->id = (int)$data['id'];
            $model->barcode_value = $data['barcode_value'];
            $model->barcode_link = $data['barcode_link'];

            return $model;
        }

        return null;
    }

    public static function getAll(?PDO $conn = null): array {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, barcode_value, barcode_link
            FROM ar_markers"
        );

        $stmt->execute();
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

    public static function delete(?int $id, ?PDO $conn = null): ?int {
        if ($id === null) return null;
        
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "DELETE FROM ar_markers WHERE id = ?"
        );

        try {
            $stmt->execute([$id]);
        } catch (Exception $e) {
            return null;
        }

        return $id;  
    } 
}