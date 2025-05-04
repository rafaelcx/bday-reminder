<?php

declare(strict_types=1);

namespace App\Http;

use App\Repository\User\UserRepositoryInMemory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class HomeController {

    public function handle(Request $request, Response $response): Response {
        $user_repository = new UserRepositoryInMemory();

        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.html.twig', [
            'users' => $user_repository->findAll(),
        ]);
    }

}
