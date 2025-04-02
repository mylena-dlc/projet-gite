<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv; // <-- Ajoute ceci explicitement !

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

(new Dotenv())->loadEnv(dirname(__DIR__).'/.env'); // <-- Ajoute cette ligne explicitement ici !

// --- DETECTION HTTPS derrière un proxy (Cloudflare, Nginx, Apache proxy...)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};