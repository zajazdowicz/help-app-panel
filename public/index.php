<?php

use App\Kernel;

// Jeśli aplikacja działa w podkatalogu, dostosuj REQUEST_URI i SCRIPT_NAME
$subdirectory = '/help-app-panel';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// Usuń podkatalog z REQUEST_URI, jeśli jest obecny
if (str_starts_with($requestUri, $subdirectory)) {
    $_SERVER['REQUEST_URI'] = substr($requestUri, strlen($subdirectory));
    if ($_SERVER['REQUEST_URI'] === '') {
        $_SERVER['REQUEST_URI'] = '/';
    }
    // Dostosuj SCRIPT_NAME, aby Symfony poprawnie generował URL-e
    $_SERVER['SCRIPT_NAME'] = $subdirectory . $scriptName;
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
