<?php

declare(strict_types=1);

namespace App\Http;

use App\Repository\Birthday\BirthdayRepositoryResolver;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class UserController {

    public function show(Request $request, Response $response): Response {
        $user_uid = $request->getQueryParams()['uid'];

        $birthday_list = BirthdayRepositoryResolver::resolve()
            ->findByUserUid($user_uid);

        $view = Twig::fromRequest($request);
        return $view->render($response, 'user.html.twig', [
            'birthdays' => $birthday_list,
            'user_uid' => $user_uid,
        ]);
    }

}
