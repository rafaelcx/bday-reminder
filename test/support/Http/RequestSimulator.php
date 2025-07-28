<?php

declare(strict_types=1);

namespace Test\Support\Http;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestSimulator {

    private string $method;
    private string $path;
    private array $headers = [];
    private string $body = '';
    private array $query_params = [];
    private array $post_params = [];
    private ?\Closure $routing_behavior = null;

    public function dispatch(): ResponseInterface {
        $app = require __DIR__ . '/../../../app/app.php';
        
        if ($this->routing_behavior) {
            $app->getCallableResolver()->resolve($this->routing_behavior);
        }

        return $app->handle($this->buildIncomingRequest());
    }

    public function withMethod(string $method): self {
        $this->method = $method;
        return $this;
    }

    public function withPath(string $path): self {
        $this->path = $path;
        return $this;
    }

    public function withBody(array $body): self {
        $body_as_json = empty($body) ? '' : json_encode($body);
        $this->body = $body_as_json;
        return $this;
    }

    public function withQueryParam(string $name, string $value): self {
        $this->query_params[$name] = $value;
        return $this;
    }

    public function withPostParams(array $params): self {
        $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
        $this->post_params = $params;
        return $this;
    }

    public function withRoutingBehavior(callable $behavior): self {
        $this->routing_behavior = $behavior;
        return $this;
    }

    private function buildIncomingRequest(): ServerRequestInterface {
        $uri = 'http://localhost' . $this->path;

        foreach ($this->query_params as $name => $value) {
            $uri = $uri . '?' . $name . '=' . $value;
        }

        $server_request = new ServerRequest($this->method, $uri, $this->headers, $this->body);

        return $server_request
            ->withQueryParams($this->query_params)
            ->withParsedBody($this->post_params);
    }

}
