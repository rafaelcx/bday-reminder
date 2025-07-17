<?php

declare(strict_types=1);

namespace Test\Support\Http\Client;

use App\Http\Client\HttpClient;
use App\Http\Client\HttpClientHandlerResolver;
use App\Utils\StaticScope;

class HttpClientForTests extends HttpClient {

    public static function overrideHandler(\Countable $mock_handler): void {
        StaticScope::set(HttpClientHandlerResolver::class, 'instance', $mock_handler);
    }

}
