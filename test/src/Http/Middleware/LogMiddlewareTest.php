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
        $log_as_array = json_decode($log_content);
        
        $this->assertBaseInfoWasLogged($log_as_array);
    }

    public function testMiddleware_ShouldLogProcessLogContext(): void {
        $request = $this->createFakeRequest();
        $request_handler = $this->createFakeRequestHandler();

        ProcessLogContext::append('key', 'value');

        (new LogMiddleware())->process($request, $request_handler);
        
        $log_content = FileServiceResolver::resolve()->getFileContents('log-file.json');
        $log_as_array = json_decode($log_content);
        
        $this->assertBaseInfoWasLogged($log_as_array);
        $this->assertSame('value', $log_as_array[0]->key);
    }

    private function createFakeRequestHandler(): RequestHandlerInterface {
        return new class() implements RequestHandlerInterface {
        
            public function handle(ServerRequestInterface $request): ResponseInterface {
                return new Response(200);
            }
        };
    }

    private function createFakeRequest(): ServerRequest {
        return new ServerRequest('GET', 'uri.com');
    }

    private function assertBaseInfoWasLogged(array $log_as_array): void {
        $this->assertSame('info', $log_as_array[0]->level);
        $this->assertSame('app_request', $log_as_array[0]->message);
        $this->assertNotNull($log_as_array[0]->timestamp);
    }

}
