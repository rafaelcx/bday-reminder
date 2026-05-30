<?php

declare(strict_types=1);

use App\Http\BirthdayController;
use App\Http\LoginController;
use App\Http\NotificationController;
use App\Http\ServiceController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {
        return (new LoginController())->handle($request, $response);
    });

    $app->get('/services', function (Request $request, Response $response) {
        return (new ServiceController())->show($request, $response);
    });

    $app->get('/birthday', function (Request $request, Response $response) {
        return (new BirthdayController())->show($request, $response);
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
