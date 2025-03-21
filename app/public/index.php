<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv; // <-- Ajoute ceci explicitement !

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

(new Dotenv())->loadEnv(dirname(__DIR__).'/.env'); // <-- Ajoute cette ligne explicitement ici !

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};