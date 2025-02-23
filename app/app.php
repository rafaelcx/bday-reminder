<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Registering routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

return $app;
