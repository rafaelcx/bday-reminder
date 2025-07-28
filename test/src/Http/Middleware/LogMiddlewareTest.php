<?php

declare(strict_types=1);

namespace Test\Src\Http\Middleware;

use App\Http\Middleware\LogMiddleware;
use App\Logger\ProcessLogContext;
use App\Storage\FileServiceResolver;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Test\CustomTestCase;

class LogMiddlewareTest extends CustomTestCase {

    public function testMiddleware_ShouldLogBaseInfo(): void {
        $request = $this->createFakeRequest();
        $request_handler = $this->createFakeRequestHandler();

        (new LogMiddleware())->process($request, $request_handler);
        
        $log_content = FileServiceResolver::resolve()->getFileContents('log-file.json');
        $log_content = json_decode($log_content)[0];

        $this->assertBaseInfoWasLogged($log_content);
    }

    public function testMiddleware_ShouldAppendProcessLogContext(): void {
        $request = $this->createFakeRequest();
        $request_handler = $this->createFakeRequestHandler();

        ProcessLogContext::append('key', 'value');

        (new LogMiddleware())->process($request, $request_handler);
        
        $log_content = FileServiceResolver::resolve()->getFileContents('log-file.json');
        $log_content = json_decode($log_content)[0];
        
        $this->assertBaseInfoWasLogged($log_content);
        $this->assertSame('value', $log_content->key);
    }

    private function createFakeRequestHandler(): RequestHandlerInterface {
        return new class() implements RequestHandlerInterface {
        
            public function handle(ServerRequestInterface $request): ResponseInterface {
                return new Response(200);
            }
        };
    }

    private function createFakeRequest(): ServerRequest {
        return new ServerRequest('GET', 'http://uri.com/path');
    }

    private function assertBaseInfoWasLogged(\stdClass $log_content): void {
        $this->assertSame('http', $log_content->process_type);
        $this->assertSame('/path', $log_content->http_request_path);
        $this->assertSame('GET', $log_content->http_request_method);
        $this->assertSame('[]', $log_content->http_request_query_params);
        $this->assertSame('null', $log_content->http_request_parsed_params);
        $this->assertNotNull($log_content->process_id);

        $this->assertSame('200', $log_content->http_response_status);
        $this->assertSame('Process Finished', $log_content->message);
    }

}
