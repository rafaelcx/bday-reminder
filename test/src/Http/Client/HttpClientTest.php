<?php

declare(strict_types=1);

namespace Test\Src\Http\Client;

use App\Http\Client\HttpClient;
use App\Http\Client\HttpClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Test\CustomTestCase;
use Test\Support\Http\Client\HttpClientForTests;

class HttpClientTest extends CustomTestCase {

    public function testHttpClient(): void {
        $mock_handler = new MockHandler();
        $mock_handler->append(new Response(200, [], 'body'));
        HttpClientForTests::overrideHandler($mock_handler);

        $client = new HttpClient();

        $request_method = 'POST';
        $request_uri = 'https://api.example.com/test';
        $request_headers = ['X-Test-Header' => 'value'];
        $request_body = 'payload';
        $request = new Request($request_method, $request_uri, $request_headers, $request_body);

        $response = $client->send($request);

        // Assertions against the response
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('body', (string)$response->getBody());

        // Assertions against the dispatched request
        $this->assertSame($request_method, $mock_handler->getLastRequest()->getMethod());
        $this->assertSame($request_uri, (string) $mock_handler->getLastRequest()->getUri());
        $this->assertSame('value', $mock_handler->getLastRequest()->getHeaderLine('X-Test-Header'));
        $this->assertSame($request_body, (string) $mock_handler->getLastRequest()->getBody());
        $this->assertSame(10.0, $mock_handler->getLastOptions()['timeout']);
    }

    public function testHttpClient_UponRequestFailure(): void {
        $mock_handler = new MockHandler();
        $mock_handler->append(new \Exception('Test failure'));
        HttpClientForTests::overrideHandler($mock_handler);

        $request_method = 'POST';
        $request_uri = 'https://api.example.com/test';
        $request_headers = ['X-Test-Header' => 'value'];
        $request_body = 'payload';
        $request = new Request($request_method, $request_uri, $request_headers, $request_body);

        $client = new HttpClient();

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('External HTTP request failed: Test failure');
        $client->send($request);
    }

}
