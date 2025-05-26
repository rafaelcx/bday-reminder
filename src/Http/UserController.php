<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class UserController {

    public function show(Request $request, Response $response): Response {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'user.html.twig', [
            'birthdays' => $this->generateRandomBirthday(),
        ]);
    }

    private function generateRandomBirthday(): array {
        $b_array = [];
        for ($i = 0; $i <= 20; $i++) {
            $b = new \stdClass;
            $b->id = rand(1, 10000);
            $b->name = 'Rafael Garcia de Carvalho e Outro Sobrenome e Outro';
            $b->date = '1995-11-30';

            $b_array[] = $b;
        }
        return $b_array;
    }

}
