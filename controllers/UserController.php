<?php

namespace Controllers;

use Database\DB;
use Models\UserModel;
use Exception;
use mysqli;

class UserController
{

    /**
     * Registrace uzivatele
     */
    public static function add(UserModel $model, string $pwd, ?mysqli $conn = null): ?UserModel {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "INSERT INTO users (username, email, hash, gallery_id) 
            VALUES (?, ?, ?, ?)"
        );

        $hash = password_hash($pwd, PASSWORD_BCRYPT);
        

        $stmt->bind_param(
            "sssi",
            $model->username,
            $model->email,
            $hash,
            $model->gallery->id
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

    public static function update(UserModel $model, ?mysqli $conn = null): ?UserModel {

        $role = 2; // user
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "UPDATE users SET
                email = ?
            WHERE id = ?",
        );


        $stmt->bind_param(
            "si",
            $model->email,
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

    public static function get(int $id, ?mysqli $conn = null): ?UserModel {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT username, email, gallery_id
            FROM users 
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
            $model = new UserModel();

            $model->id = $id;
            $model->username = $data['username'];
            $model->email = $data['email'];
            $model->gallery = GalleryController::get($data['gallery_id']);
            

            return $model;
        }


        // Create profile model from retrieved data from database

        return null;
    }



    
    public static function getAll(?mysqli $conn = null): array {

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, email, gallery_id
            FROM users"
        );

        $stmt->execute();

        $stmt->bind_result($userId, $username, $email, $galleryId);

        $result = [];

        while ($stmt->fetch()) {

            $currentProfile = new UserModel();   

            $currentProfile->id = $userId;
            $currentProfile->username = $username;
            $currentProfile->email = $email;
            $currentProfile->gallery = GalleryController::get($galleryId);
            
            $result[] = $currentProfile;
        }


        return $result;
    }

    public static function delete(?int $id, ?mysqli $conn = null): ?int { 
        $conn = $conn == null ? DB::connect() : $conn;

        $stmt = $conn->prepare(
            "DELETE FROM users WHERE id = ?",
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

    /**
     * Hledani uzivatele podle uzivatelskeho jmena
     */
    public static function find(string $username, ?mysqli $conn = null): ?UserModel {
        
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, username, email, gallery_id
            FROM users 
            WHERE username = ?",
        );

        $stmt->bind_param(
            "s",
            $username
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $data = $result->fetch_assoc();
            $model = new UserModel();

            $model->id = $data['id'];
            $model->username = $data['username'];
            $model->email = $data['email'];
            $model->gallery = GalleryController::get($data['gallery_id']);

            return $model;
        }


        // Create profile model from retrieved data from database

        return null;
    }

    /**
     * Vytvareni autentikacniho tokenu
     */
    public static function createAuthToken(int $id, string $username, string $hash) {
        $random_number = mt_rand();

        $token_data = $id . $username . $hash . time() . $random_number;

        $token = hash('sha256', $token_data);

        return $token;
    }

    /**
     * Ulozeni autentikacniho tokenu k zaznamu uzivatele v databazi
     */
    public static function storeAuthToken($id, $auth_token, ?mysqli $conn = null): ?int {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "UPDATE users SET
                auth_token = ?
            WHERE id = ?",
        );


        $stmt->bind_param(
            "si",
            $auth_token,
            $id
        );

        try {
            $stmt->execute();
        }
        catch (Exception $e) {
            return null;
        }


        return $id;

    }

    /**
     * Prihlaseni uzivatele
     */
    public static function login(string $username, string $pwd, ?mysqli $conn = null): ?string{
        
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, hash 
            FROM users 
            WHERE username = ?",
        );

        $stmt->bind_param(
            "s",
            $username
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $data = $result->fetch_assoc();

            $id = $data['id'];
            $hash = $data['hash'];

            $pwdValid = password_verify($pwd, $hash);

            if ($pwdValid) {
                $auth_token = self::createAuthToken($id, $username, $hash);
                if (self::storeAuthToken($id, $auth_token) === null) {
                    return null;
                }
                return $auth_token;
            }

        }


        // Create profile model from retrieved data from database

        return null;
    }

    /**
     * Autentizace uzivatele pomoci tokenu
     * Slouzi k udrzeni prihlaseneho stavu u uzivatel, aby se uzivatel nemusel pri kazde akci znovu prihlasovat
     */
    public static function auth($auth_token, ?mysqli $conn = null): ?UserModel {
        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "SELECT id, username, email, gallery_id
            FROM users 
            WHERE auth_token = ?",
        );

        $stmt->bind_param(
            "s",
            $auth_token
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $data = $result->fetch_assoc();

            $user = new UserModel();

            $user->id = $data['id'];
            $user->username = $data['username'];
            $user->email = $data['email'];
            $user->gallery = GalleryController::get($data['gallery_id']);

            return $user;
        }

        return null;
    }

    /**
     * Odhlaseni uzivatele a resetování autentizacniho tokenu
     */
    public static function logout($username, ?mysqli $conn = null): ?string {

        $user = self::find($username);

        $conn = $conn == null ? DB::connect() : $conn;
        $stmt = $conn->prepare(
            "UPDATE users SET
                auth_token = ?
            WHERE id = ?",
        );

        $no_auth_token = null;

        $stmt->bind_param(
            "si",
            $no_auth_token,
            $user->id
        );

        try {
            $stmt->execute();
        }
        catch (Exception $e) {
            return null;
        }


        return $username;
    }
}
