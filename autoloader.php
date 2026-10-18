```php
<?php

spl_autoload_register(function ($class) {
    $parts = explode('\\', $class);
    $path = __DIR__;

    foreach ($parts as $part) {
        if (!is_dir($path)) {
            $entries = scandir($path);
            $match = null;

            foreach ($entries as $entry) {
                if (strcasecmp($entry, $part) === 0) {
                    $match = $entry;
                    break;
                }
            }

            if ($match === null) {
                return;
            }

            $path .= '/' . $match;
        } else {
            $path .= '/' . $part;
        }
    }

    $file = $path . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});
```