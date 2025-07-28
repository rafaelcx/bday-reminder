<?php

declare(strict_types=1);

use App\Http\Handler\ErrorHandler;
use App\Http\Middleware\LogMiddleware;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

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

// Add Error handler
$error_handler = new ErrorHandler($app->getCallableResolver(), $app->getResponseFactory());
$error_middleware = $app->addErrorMiddleware(true, true, true);
$error_middleware->setDefaultErrorHandler($error_handler);

return $app;
