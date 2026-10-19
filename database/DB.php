<?php

namespace Database;

use Exception;
use PDO;

class DB {

    public static function connect()
    {
        $servername = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '5432';
        $username = $_ENV['DB_USERNAME'] ?? 'ar_app_user';
        $password = $_ENV['DB_PASSWORD'] ?? 'admin';
        $database = $_ENV['DB_NAME'] ?? 'ar_app';

        echo "Connecting to database at $servername:$port with user $username and database $database<br>";
        echo "Environment variables: " . json_encode($_ENV) . "<br>";
        echo "PHP version: " . phpversion() . "<br>";
        echo "PDO PostgreSQL loaded: " . (extension_loaded('pdo_pgsql') ? 'Yes' : 'No') . "<br>";

        try {
            $dsn = "pgsql:host=$servername;port=$port;dbname=$database";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return $pdo;
        } catch (\PDOException $e) {
            echo "Chyba připojení: " . $e->getMessage() . "<br>";
            return null;
        }
    }

    public static function initializeMarkers(?PDO $conn = null): void
    {
        $conn = $conn ?? self::connect();
        
        if (!$conn) {
            return;
        }

        $result = $conn->query("SELECT * FROM ar_markers");

        if ($result && $result->rowCount() > 0) {
            return;
        }

        $baseUrl = rtrim($_ENV['BASE_URL'], '/');

        $values = [];
        $params = [];

        for ($i = 0; $i < 50; $i++) {
            $values[] = "(?, ?)";
            $params[] = $i;
            $params[] = $baseUrl . '/public/markers/' . $i . '.png';
        }

        // Hromadný INSERT pro PostgreSQL s ON CONFLICT
        $sql = "
            INSERT INTO ar_markers (barcode_value, barcode_link)
            VALUES " . implode(', ', $values) . "
            ON CONFLICT (barcode_value) DO UPDATE SET
                barcode_link = EXCLUDED.barcode_link
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    }
}