<?php

spl_autoload_register(function ($class) {
    // Convert class name to file path
    $names = explode('\\', $class);
    $class_name = array_pop($names);
    $path = strtolower(implode('/', $names));
    $class_path = $path . '/' . $class_name . '.php';
    require $class_path;
    
    /*
    // Define base directories for your modules
    $base_dirs = [
        __DIR__ . '/controllers/',
        __DIR__ . '/models/',
        __DIR__ . '/database/',
        __DIR__ . '/pages/',
        __DIR__ . '/views/'
    ];


    // Check each base directory for the class file
    foreach ($base_dirs as $dir) {
        $file = $dir . $class_path;
        if (file_exists($file)) {
            include $file;
            return;
        }
    }
    */
});