<?php
require 'vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::create('/admin/destinations/api/autocomplete-location?q=Paris', 'GET');
$response = $kernel->handle($request);
echo $response->getContent();
