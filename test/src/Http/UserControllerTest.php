<?php

declare(strict_types=1);

namespace Test\Http;

use Test\CustomTestCase;

class UserControllerTest extends CustomTestCase {

    public function testController_WhenSuccessful(): void {
        $result = $this->request_simulator
            ->withMethod('GET')
            ->withBody([])
            ->withPath('/user')
            ->withQueryParam('uid', '1')
            ->dispatch();

        $this->assertSame(200, $result->getStatusCode());

        $this->assertStringContainsString('Your Birthday List', (string) $result->getBody());
    }

}
