<?php

namespace Controllers;

use Database\DB;
use Models\UserModel;
use Exception;
use PDO;

class UserController
{
    public static function add(UserModel $model, string $pwd, ?PDO $conn = null): ?UserModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "INSERT INTO users (username, email, hash, gallery_id) 
            VALUES (?, ?, ?, ?)"
        );

        $hash = password_hash($pwd, PASSWORD_BCRYPT);
        
        try {
            $stmt->execute([
                $model->username,
                $model->email,
                $hash,
                $model->gallery->id
            ]);
        } catch (Exception $e) {
            return null;
        }

        $model->id = (int)$conn->lastInsertId();

        return $model;
    }

    public static function update(UserModel $model, ?PDO $conn = null): ?UserModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "UPDATE users SET
                email = ?
            WHERE id = ?"
        );

        try {
            $stmt->execute([
                $model->email,
                $model->id
            ]);
        } catch (Exception $e) {
            return null;
        }

        return $model;
    }

    public static function get(int $id, ?PDO $conn = null): ?UserModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, username, email, gallery_id
            FROM users 
            WHERE id = ?"
        );

        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $model = new UserModel();
            $model->id = (int)$data['id'];
            $model->username = $data['username'];
            $model->email = $data['email'];
            $model->gallery = GalleryController::get($data['gallery_id'], $conn);
            
            return $model;
        }

        return null;
    }

    public static function getAll(?PDO $conn = null): array {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, username, email, gallery_id
            FROM users"
        );

        $stmt->execute();
        $result = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $currentProfile = new UserModel();   
            $currentProfile->id = (int)$data['id'];
            $currentProfile->username = $data['username'];
            $currentProfile->email = $data['email'];
            $currentProfile->gallery = GalleryController::get($data['gallery_id'], $conn);
            
            $result[] = $currentProfile;
        }

        return $result;
    }

    public static function delete(?int $id, ?PDO $conn = null): ?int { 
        if ($id === null) return null;
        
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "DELETE FROM users WHERE id = ?"
        );

        try {
            $stmt->execute([$id]);
        } catch (Exception $e) {
            return null;
        }

        return $id;  
    }

    public static function find(string $username, ?PDO $conn = null): ?UserModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, username, email, gallery_id
            FROM users 
            WHERE username = ?"
        );

        $stmt->execute([$username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $model = new UserModel();
            $model->id = (int)$data['id'];
            $model->username = $data['username'];
            $model->email = $data['email'];
            $model->gallery = GalleryController::get($data['gallery_id'], $conn);

            return $model;
        }

        return null;
    }

    public static function createAuthToken(int $id, string $username, string $hash) {
        $random_number = mt_rand();
        $token_data = $id . $username . $hash . time() . $random_number;
        return hash('sha256', $token_data);
    }

    public static function storeAuthToken($id, $auth_token, ?PDO $conn = null): ?int {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "UPDATE users SET
                auth_token = ?
            WHERE id = ?"
        );

        try {
            $stmt->execute([$auth_token, $id]);
        } catch (Exception $e) {
            return null;
        }

        return $id;
    }

    public static function login(string $username, string $pwd, ?PDO $conn = null): ?string {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, hash 
            FROM users 
            WHERE username = ?"
        );

        $stmt->execute([$username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $id = $data['id'];
            $hash = $data['hash'];

            if (password_verify($pwd, $hash)) {
                $auth_token = self::createAuthToken($id, $username, $hash);
                if (self::storeAuthToken($id, $auth_token, $conn) === null) {
                    return null;
                }
                return $auth_token;
            }
        }

        return null;
    }

    public static function auth($auth_token, ?PDO $conn = null): ?UserModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, username, email, gallery_id
            FROM users 
            WHERE auth_token = ?"
        );

        $stmt->execute([$auth_token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $user = new UserModel();
            $user->id = (int)$data['id'];
            $user->username = $data['username'];
            $user->email = $data['email'];
            $user->gallery = GalleryController::get($data['gallery_id'], $conn);

            return $user;
        }

        return null;
    }

    public static function logout($username, ?PDO $conn = null): ?string {
        $conn = $conn == null ? DB::connect() : $conn;
        $user = self::find($username, $conn);
        if (!$user) {
            return null;
        }

        $stmt = $conn->prepare(
            "UPDATE users SET
                auth_token = ?
            WHERE id = ?"
        );

        try {
            $stmt->execute([null, $user->id]);
        } catch (Exception $e) {
            return null;
        }

        return $username;
    }
}