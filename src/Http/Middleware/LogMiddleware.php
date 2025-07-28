<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Logger\LoggerResolver;
use App\Logger\ProcessLogContext;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class LogMiddleware implements MiddlewareInterface {

    public function process(Request $request, RequestHandler $handler): Response {
        $this->logIncomingRequestDetails($request);
        $response = $handler->handle($request);
        $this->flushProcessLogs($response);

        return $response;
    }

    private function logIncomingRequestDetails(Request $request): void {
        ProcessLogContext::append('process_type', 'http');
        ProcessLogContext::append('process_id', uniqid());
        ProcessLogContext::append('http_request_path', $request->getUri()->getPath());
        ProcessLogContext::append('http_request_method', $request->getMethod());
        ProcessLogContext::append('http_request_query_params', json_encode($request->getQueryParams()));
        ProcessLogContext::append('http_request_parsed_params', json_encode($request->getParsedBody()));
    }

    private function flushProcessLogs(Response $response): void {
        $response_status = (string) $response->getStatusCode();
        ProcessLogContext::append('http_response_status', $response_status);

        if ($response_status == '302') {
            ProcessLogContext::append('http_response_redirect_location', $response->getHeaderLine('Location'));
        }

        $message = 'Process Finished';
        $process_context = ProcessLogContext::getAll();
        LoggerResolver::resolve()->info($message, $process_context);
    }

}
