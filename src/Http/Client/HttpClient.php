<?php

declare(strict_types=1);

namespace App\Http\Client;

use App\Logger\ProcessLogContext;
use App\Utils\StopWatch;
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
        $response = null;

        $stop_watch = new StopWatch();
        $stop_watch->start();

        try {
            $response = $this->client->send($request);
            return $response;
        } catch (\Throwable $e) {
            $this->handleHttpError($e);
        } finally {
            $this->performHttpLogs($request, $response, $stop_watch);
        }
    }

    private function buildConfigs(): array {
        return [
            'timeout' => 10.0,
            'handler' => HandlerStack::create(HttpClientHandlerResolver::resolve()),
        ];
    }

    private function handleHttpError(\Throwable $e): never {
        ProcessLogContext::append('external_request.error', $e->getMessage());
        throw new HttpClientException("External HTTP request failed: {$e->getMessage()}");
    }

    private function performHttpLogs(RequestInterface $rq, ?ResponseInterface $rs, StopWatch $sw): void {
        $sw->stop();

        // TODO: When multiple external requests happen on the same process, log all of them

        ProcessLogContext::append('external_request.method', $rq->getMethod());
        ProcessLogContext::append('external_request.target_url', (string) $rq->getUri());
        ProcessLogContext::append('external_request.elapsed_time_in_msec', (string) $sw->getTime());

        if (!is_null($rs)) {
            ProcessLogContext::append('external_request.response.status_code', (string) $rs->getStatusCode());
        }
    }

}
