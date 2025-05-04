<?php

declare(strict_types=1);

use App\Http\HomeController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Views\Twig;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response, $args) {
        return (new HomeController())->handle($request, $response);
    });
};
