<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$dotenv = new Dotenv();
$envFile = dirname(__DIR__).'/.env';

// Vérifie si le fichier .env existe avant de le charger
if (file_exists($envFile)) {
    $dotenv->load($envFile);

    // Ajoute les variables d'environnement dans $_SERVER et $_ENV
    foreach ($_ENV as $key => $value) {
        $_SERVER[$key] = $_ENV[$key];
    }

    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'DATABASE_') === 0) {
            $_ENV[$key] = $value;
        }
    }
}