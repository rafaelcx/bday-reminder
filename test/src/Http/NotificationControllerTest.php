<?php

declare(strict_types=1);

namespace Test\Src\Http;

use Test\CustomTestCase;

class NotificationControllerTest extends CustomTestCase {

    public function testController_WhenSuccessful(): void {
        $result = $this->request_simulator
            ->withMethod('POST')
            ->withPath('/notify')
            ->withPostParams(['user_uid' => '123'])
            ->dispatch();

        $this->assertSame(302, $result->getStatusCode());
        $this->assertSame('/user?uid=123', $result->getHeaderLine('Location'));
    }

}
