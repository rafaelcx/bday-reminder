<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NotificationController {

    public function handle(Request $request, Response $response): Response {
        $parsed_body = $request->getParsedBody();

        $user_uid = $parsed_body['user_uid'];

        return $response
            ->withStatus(302)
            ->withHeader('Location', '/user?uid=' . urlencode($user_uid));
    }

}
