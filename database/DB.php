<?php

namespace Database;

use Exception;
use mysqli;

class DB {



    public static function connect()
    {
        $servername = "mysql";
        $port = "3306";
        $username = "ar_app_user";
        $password = "admin";
        $database = "ar_app";

        $mysqli = new \mysqli($servername, $username, $password, $database, $port);

        if ($mysqli->connect_errno) {
            return null;
        }
        
        return $mysqli;
    }



    public static function initializeMarkers(?mysqli $conn = null): void
    {
        $conn = $conn ?? DB::connect();

        $result = $conn->query("SELECT * FROM ar_markers");

        if ($result && $result->num_rows > 0) {
            return;
        }

        $baseUrl = rtrim($_ENV['BASE_URL'], '/');

        $sql = "
            INSERT INTO ar_markers (barcode_value, barcode_link)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE
                barcode_link = VALUES(barcode_link)
        ";

        $stmt = $conn->prepare($sql);

        for ($i = 0; $i < 50; $i++) {
            $barcodeValue = $i;
            $barcodeLink = $baseUrl . '/public/markers/' . $i . '.png';

            $stmt->bind_param('is', $barcodeValue, $barcodeLink);
            $stmt->execute();
        }

        $stmt->close();
    }



}