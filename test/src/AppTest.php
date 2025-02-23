<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Test\CustomTestCase;

class AppTest extends CustomTestCase {

    public function testAppRouting(): void {
        $request = new ServerRequest('GET', 'http://test.com/');
        $result = $this->getAppInstance()->handle($request);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('Hello World!', (string) $result->getBody());
    }

}
