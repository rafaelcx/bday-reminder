<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Logger\LoggerResolver;
use App\Logger\ProcessContext;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class LogMiddleware implements MiddlewareInterface {

    public function process(Request $request, RequestHandler $handler): Response {
        $response = $handler->handle($request);

        $message = 'app_request';
        $process_context = ProcessContext::getAll();
        LoggerResolver::resolve()->info($message, $process_context);

        return $response;
    }

}
