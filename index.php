<?php

use Controllers\PaintingController;
use Database\DB;

session_start();

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles/output.css">
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.3.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.3.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.3.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <title>ARt - AR Gallery</title>
</head>

<body>

<?php

  // Composer and custom autoloading
  require_once __DIR__ . '/autoloader.php';
  require_once __DIR__ . '/vendor/autoload.php';

  // Environmental variabless
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();

  try {
    DB::connect();
    DB::initializeMarkers();
  }
  catch (Exception $e) {
    echo "Nelze navázat spojení s databází";
    echo "<hr>";
    echo "Error: " . $e->getMessage();
    die();
  }


  require('components/header.php');

  if (isset($_GET['page'])) {
    $page = rtrim($_GET['page'], '/');
    $page = isset($_GET['page']) ? $_GET['page'] : 'home';

    $page_file = "views/{$page}.php";
    // $page_file = "pages/{$page}";

    if (file_exists($page_file)) {
        require $page_file;
    } else {
        // page not found
        header("HTTP/1.0 404 Not Found");
        // include('pages/404.php');
    }
  }
  else {
    require('views/main.php');
  }

  
  ?>

  
</body>
</html>