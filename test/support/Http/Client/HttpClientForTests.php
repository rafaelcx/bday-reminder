<?php

declare(strict_types=1);

namespace Test\Support\Http\Client;

use App\Http\Client\HttpClient;
use App\Http\Client\HttpClientHandlerResolver;
use App\Utils\StaticScope;
use GuzzleHttp\Handler\MockHandler;

class HttpClientForTests extends HttpClient {

    public static function override(): void {
        StaticScope::set(HttpClientHandlerResolver::class, 'instance', new MockHandler());
    }

    public static function overrideHandler(\Countable $mock_handler): void {
        StaticScope::set(HttpClientHandlerResolver::class, 'instance', $mock_handler);
    }

}
