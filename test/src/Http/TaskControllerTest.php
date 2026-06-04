<?php

declare(strict_types=1);

namespace Test\Src\Http;

use Test\CustomTestCase;

class TaskControllerTest extends CustomTestCase {

    public function testController_WhenSuccessful(): void {
        $response = $this->request_simulator
            ->withPath('/task')
            ->withMethod('GET')
            ->withQueryParam('user_uid', '1')
            ->dispatch();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello task! User_uid: 1', (string) $response->getBody());
    }

}
