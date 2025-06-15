<?php

declare(strict_types=1);

use App\Http\BirthdayController;
use App\Http\HomeController;
use App\Http\UserController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response, $args) {
        return (new HomeController())->handle($request, $response);
    });

    $app->get('/user', function (Request $request, Response $response, $args) {
        return (new UserController())->show($request, $response);
    });

    $app->post('/birthday', function (Request $request, Response $response, $args) {
        return (new BirthdayController())->create($request, $response);
    });

    $app->post('/birthday/edit', function (Request $request, Response $response, $args) {
        return (new BirthdayController())->update($request, $response);
    });
};
