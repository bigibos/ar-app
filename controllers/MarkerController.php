<?php

namespace Controllers;

use Models\MarkerModel;
use Database\DB;
use Exception;
use mysqli;

class MarkerController
{

    public static function get(int $id, ?mysqli $conn = null): ?MarkerModel {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, barcode_value, barcode_link
            FROM ar_markers 
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
            $model = new MarkerModel();

            $model->id = $id;
            $model->barcode_value = $data['barcode_value'];
            $model->barcode_link = $data['barcode_link'];

            return $model;
        }


        // Create profile model from retrieved data from database

        return null;
    }



    
    public static function getAll(?mysqli $conn = null): array {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, barcode_value, barcode_link
            FROM ar_markers"
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

    public static function delete(?int $id, ?mysqli $conn = null): ?int {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "DELETE FROM ar_markers WHERE id = ?",
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
