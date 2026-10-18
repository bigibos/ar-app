<?php

spl_autoload_register(function ($class) {
    // Rozdělíme třídu podle namespace (např. "Database\DB" -> ["Database", "DB"])
    $parts = explode('\\', $class);

    if (count($parts) > 1) {
        // První část (např. "Database") převedeme na malá písmena pro název složky ("database")
        $folder = strtolower(array_shift($parts));
        
        // Zbytek cesty tvoří podadresáře a název třídy
        $file = __DIR__ . '/' . $folder . '/' . implode('/', $parts) . '.php';
    } else {
        // Pokud třída nemá namespace, hledá se přímo v kořenovém adresaři
        $file = __DIR__ . '/' . $class . '.php';
    }

    if (file_exists($file)) {
        require_once $file;
    }
});