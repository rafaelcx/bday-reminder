<?php

declare(strict_types=1);

namespace App\Http;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TaskController {

    public function show(Request $request, Response $response): Response {
        $user_uid = $request->getQueryParams()['user_uid'];
        return new GuzzleResponse(200, [], 'Hello task! User_uid: ' . (string) $user_uid);
    }

}
