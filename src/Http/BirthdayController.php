<?php

declare(strict_types=1);

namespace App\Http;

use App\Repository\Birthday\BirthdayRepositoryResolver;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BirthdayController {

    public function create(Request $request, Response $response): Response {
        $parsed_body = $request->getParsedBody();

        $bday_name = $parsed_body['name'];
        $bday_date = $parsed_body['date'];
        $bday_user_id = $parsed_body['user_id'];

        BirthdayRepositoryResolver::resolve()
            ->create($bday_user_id, $bday_name, new \DateTime($bday_date));
    
        return $response
            ->withStatus(302)
            ->withHeader('Location', '/user?uid=' . urlencode($bday_user_id));
    }

    public function update(Request $request, Response $response): Response {
        $parsed_body = $request->getParsedBody();

        $bday_name = $parsed_body['name'];
        $bday_date = $parsed_body['date'];
        $bday_uid = $parsed_body['birthday_uid'];
        $bday_user_uid = $parsed_body['user_uid'];

        BirthdayRepositoryResolver::resolve()
            ->update($bday_uid, $bday_name, new \DateTime($bday_date));

        return $response
            ->withStatus(302)
            ->withHeader('Location', '/user?uid=' . urlencode($bday_user_uid));
    }

    public function delete(Request $request, Response $response): Response {
        $parsed_body = $request->getParsedBody();
        $bday_uid = $parsed_body['birthday_uid'];
        $bday_user_uid = $parsed_body['user_uid'];

        BirthdayRepositoryResolver::resolve()
            ->delete($bday_uid);

        return $response
            ->withStatus(302)
            ->withHeader('Location', '/user?uid=' . urlencode($bday_user_uid));
    }

}
