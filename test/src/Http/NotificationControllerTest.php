<?php

declare(strict_types=1);

namespace Test\Src\Http;

use Test\CustomTestCase;
use Test\Support\Services\Notification\Integration\NotifierForTests;
use Test\Support\Services\Notification\Integration\NotifierResolverForTests;

class NotificationControllerTest extends CustomTestCase {

    public function testController_WhenSuccessful(): void {
        $this->mockNoOpNotificationService();

        $result = $this->request_simulator
            ->withMethod('POST')
            ->withPath('/notify')
            ->withPostParams(['user_uid' => '123'])
            ->dispatch();

        $this->assertSame(302, $result->getStatusCode());
        $this->assertSame('/user?uid=123', $result->getHeaderLine('Location'));
    }

    private function mockNoOpNotificationService(): void {
        $notifier_for_tests = new NotifierForTests();
        NotifierResolverForTests::override($notifier_for_tests);
    }

}
