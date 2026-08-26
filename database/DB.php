<?php

namespace Database;

use Exception;

class DB {


    public static function connect()
    {
        $servername = "localhost";
        $port = "3306";
        $username = "root";
        $password = "";
        $database = "ar_app";

        $mysqli = new \mysqli($servername, $username, $password, $database, $port);

        if ($mysqli->connect_errno) {
            return null;
        }
        
        if (!$mysqli->ping()) {
            return null;
        }
        
        return $mysqli;
    }

}