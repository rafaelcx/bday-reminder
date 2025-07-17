<?php

declare(strict_types=1);

namespace App\Http\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClient {

    private ClientInterface $client;

    public function __construct() {
        $this->client = new Client($this->buildConfigs());
    }

    public function send(RequestInterface $request): ResponseInterface {
        try {
            return $this->client->send($request);
        } catch (\Throwable $e) {
            throw new HttpClientException("External HTTP request failed: {$e->getMessage()}");
        }
    }

    private function buildConfigs(): array {
        return [
            'timeout' => 10.0,
            'handler' => HandlerStack::create(HttpClientHandlerResolver::resolve()),
        ];
    }

}
