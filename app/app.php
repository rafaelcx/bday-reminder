<?php

declare(strict_types=1);

use App\Http\Middleware\LogMiddleware;
use App\Logger\ProcessLogContext;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

ProcessLogContext::append('process_type', 'http');
ProcessLogContext::append('process_id', uniqid());

$app = AppFactory::create();

// Registering routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

// Add Twig-View middleware
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

// Add Logger middleware
$logger = new LogMiddleware();
$app->add($logger);

return $app;
