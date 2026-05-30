<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ServiceController {

    public function show(Request $request, Response $response): Response {
        $user_uid = $request->getQueryParams()['uid'];

        $view = Twig::fromRequest($request);
        return $view->render($response, 'services.html.twig', [
            'user_uid' => $user_uid,
        ]);
    }

}
