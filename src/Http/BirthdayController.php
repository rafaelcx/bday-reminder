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
        $bday_user_uid = $parsed_body['user_uid'];

        BirthdayRepositoryResolver::resolve()
            ->create($bday_user_uid, $bday_name, new \DateTime($bday_date));
    
        return $this->buildRedirectResponse($response, $bday_user_uid);
    }

    public function update(Request $request, Response $response): Response {
        $parsed_body = $request->getParsedBody();

        $bday_name = $parsed_body['name'];
        $bday_date = $parsed_body['date'];
        $bday_uid = $parsed_body['birthday_uid'];
        $bday_user_uid = $parsed_body['user_uid'];

        BirthdayRepositoryResolver::resolve()
            ->update($bday_uid, $bday_name, new \DateTime($bday_date));

        return $this->buildRedirectResponse($response, $bday_user_uid);
    }

    public function delete(Request $request, Response $response): Response {
        $parsed_body = $request->getParsedBody();

        $bday_uid = $parsed_body['birthday_uid'];
        $bday_user_uid = $parsed_body['user_uid'];

        BirthdayRepositoryResolver::resolve()
            ->delete($bday_uid);

        return $this->buildRedirectResponse($response, $bday_user_uid);
    }

    private function buildRedirectResponse(Response $response, string $user_uid): Response {
        return $response
            ->withStatus(302)
            ->withHeader('Location', '/user?uid=' . urlencode($user_uid));
    }

}
