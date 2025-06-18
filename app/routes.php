<?php

declare(strict_types=1);

use App\Http\BirthdayController;
use App\Http\HomeController;
use App\Http\NotificationController;
use App\Http\UserController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {
        return (new HomeController())->handle($request, $response);
    });

    $app->get('/user', function (Request $request, Response $response) {
        return (new UserController())->show($request, $response);
    });

    $app->post('/birthday', function (Request $request, Response $response) {
        return (new BirthdayController())->create($request, $response);
    });

    $app->post('/birthday/edit', function (Request $request, Response $response) {
        return (new BirthdayController())->update($request, $response);
    });

    $app->post('/birthday/delete', function (Request $request, Response $response) {
        return (new BirthdayController())->delete($request, $response);
    });

    $app->post('/notify', function (Request $request, Response $response) {
        return (new NotificationController())->handle($request, $response);
    });
};
