<?php

namespace Database;

use Exception;
use PDO;

class DB {

    public static function connect()
    {
        $dbHostEnv = $_ENV['DB_HOST'] ?? '';

        // Pokud je v DB_HOST celý connection string z Renderu (začíná postgresql://)
        if (str_starts_with($dbHostEnv, 'postgresql://') || str_starts_with($dbHostEnv, 'postgres://')) {
            $urlParts = parse_url($dbHostEnv);
            $servername = $urlParts['host'] ?? '';
            $port = $urlParts['port'] ?? '5432';
            $username = $urlParts['user'] ?? '';
            $password = $urlParts['pass'] ?? '';
            $database = ltrim($urlParts['path'] ?? '', '/');
        } else {
            // Fallback pro lokální proměnné (pokud máš rozepsáno v .env)
            $servername = $dbHostEnv ?: 'localhost';
            $port = $_ENV['DB_PORT'] ?? '5432';
            $username = $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? 'ar_app_user';
            $password = $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? 'admin';
            $database = $_ENV['DB_NAME'] ?? 'ar_app';
        }

        $dsn = "pgsql:host=$servername;port=$port;dbname=$database";
        
        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return $pdo;
        } catch (\PDOException $e) {
            throw new Exception("Nelze navázat spojení s databází: " . $e->getMessage());
        }
    }

    public static function initializeMarkers(?PDO $conn = null): void
    {
        $conn = $conn ?? self::connect();

        $result = $conn->query("SELECT * FROM ar_markers");

        if ($result && $result->rowCount() > 0) {
            return;
        }

        $baseUrl = rtrim($_ENV['BASE_URL'] ?? '', '/');

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