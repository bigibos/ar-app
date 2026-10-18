<?php

namespace Database;

use Exception;
use mysqli;

class DB {



    public static function connect()
    {
        $servername = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $username = $_ENV['DB_USERNAME'] ?? 'ar_app_user';
        $password = $_ENV['DB_PASSWORD'] ?? 'admin';
        $database = $_ENV['DB_NAME'] ?? 'ar_app';

        echo "Connecting to database at $servername:$port with user $username and database $database<br>";
        echo "Environment variables: " . json_encode($_ENV) . "<br>";
        echo "PHP version: " . phpversion() . "<br>";
        echo "MySQLi extension loaded: " . (extension_loaded('mysqli') ? 'Yes' : 'No') . "<br>";

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