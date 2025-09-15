<?php

declare(strict_types=1);

namespace App\Http\Handler;

use App\Logger\LoggerResolver;
use App\Logger\ProcessLogContext;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\ErrorHandlerInterface;

class ErrorHandler implements ErrorHandlerInterface {

    public function __invoke(Request $r, \Throwable $t, bool $ded, bool $le, bool $led): ResponseInterface {
        return $this->handleApplicationError($t);
    }

    private function handleApplicationError(\Throwable $t): ResponseInterface {
        $response = new Response(500, [], 'Internal Server Error');
        
        $this->performShutdownLogs($response, $t);
        $this->flushLogs();

        return $response;
    }

    private function performShutdownLogs(ResponseInterface $response, \Throwable $t): void {
        ProcessLogContext::set('http_response_status', (string) $response->getStatusCode());
        ProcessLogContext::set('exception_message', $t->getMessage());
        ProcessLogContext::set('exception_file', $t->getFile());
        ProcessLogContext::set('exception_line', (string) $t->getLine());
        ProcessLogContext::set('exception_trace', $t->getTraceAsString());
    }

    private function flushLogs(): void {
        $message = 'Process Error';
        $process_context = ProcessLogContext::getAll();
        LoggerResolver::resolve()->error($message, $process_context);
    }

}
