<?php

declare(strict_types=1);

namespace Test\Src\Http\Middleware;

use App\Http\Middleware\LogMiddleware;
use App\Logger\ProcessContext;
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
        $log_as_obj = json_decode($log_content);
        
        $this->assertBaseInfoWasLogged($log_as_obj);
    }

    public function testMiddleware_ShouldLogProcessContext(): void {
        $request = $this->createFakeRequest();
        $request_handler = $this->createFakeRequestHandler();

        ProcessContext::append('key', 'value');

        (new LogMiddleware())->process($request, $request_handler);
        
        $log_content = FileServiceResolver::resolve()->getFileContents('log-file.json');
        $log_as_obj = json_decode($log_content);
        
        $this->assertBaseInfoWasLogged($log_as_obj);
        $this->assertSame('value', $log_as_obj->key);
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

    private function assertBaseInfoWasLogged(\stdClass $log_as_obj): void {
        $this->assertSame('info', $log_as_obj->level);
        $this->assertSame('app_request', $log_as_obj->message);
        $this->assertNotNull($log_as_obj->timestamp);
    }

}
