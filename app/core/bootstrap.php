<?php

// Start session
session_start();

// Load config
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// Autoload core classes if not using Composer
spl_autoload_register(function ($className) {
    $paths = [
        __DIR__ . '/../controllers/' . $className . '.php',
        __DIR__ . '/../models/' . $className . '.php',
        __DIR__ . '/' . $className . '.php', // for core classes (like Router.php, etc)
        __DIR__ . '/../helpers/' . $className . '.php', // if you want helper classes auto-loaded
    ];

    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
