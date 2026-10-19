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

    public static function initializeDatabase(?PDO $conn = null): void
    {
        $conn = $conn ?? self::connect();

        // Cesta k tvému SQL souboru
        $schemaFile = __DIR__ . '/init/create.sql'; // Pokud ho máš ve složce database/

        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $conn->exec($sql);
        } else {
            throw new Exception("Soubor se schématem databáze nebyl nalezen: " . $schemaFile);
        }

        // Inicializace ar_markers dat (pokud jsou prázdná)
        $result = $conn->query("SELECT * FROM ar_markers");
        if ($result && $result->rowCount() === 0) {
            $baseUrl = rtrim($_ENV['BASE_URL'] ?? '', '/');
            $values = [];
            $params = [];

            for ($i = 0; $i < 50; $i++) {
                $values[] = "(?, ?)";
                $params[] = $i;
                $params[] = $baseUrl . '/public/markers/' . $i . '.png';
            }

            $sql = "
                INSERT INTO ar_markers (barcode_value, barcode_link)
                VALUES " . implode(', ', $values) . "
                ON CONFLICT DO NOTHING
            ";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
    }
}