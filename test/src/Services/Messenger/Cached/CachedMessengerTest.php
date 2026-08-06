<?php

declare(strict_types=1);

namespace Test\Src\Services\Messenger\Cached;

use App\Repository\User\User;
use App\Services\Messenger\Cached\CachedMessenger;
use App\Services\Messenger\Messenger;
use App\Utils\Clock;
use Test\CustomTestCase;
use Test\Support\Services\Messenger\MessengerForTests;

class CachedMessengerTest extends CustomTestCase {

    public function testMessenger_ShouldCacheAndInvalidate(): void {
        $execution_count = 0;
        $delegate = new MessengerForTests();
        $delegate->setGetUpdatesBehavior(function() use (&$execution_count) {
            $execution_count++;
            return [];
        });

        $messenger = new CachedMessenger($delegate);

        $messenger->getUpdates();
        $this->assertSame(1, $execution_count);

        $messenger->getUpdates();
        $this->assertSame(1, $execution_count);

        // Should invalidate the cache
        $this->executePost($messenger);

        $messenger->getUpdates();
        $this->assertSame(2, $execution_count);

        $messenger->getUpdates();
        $this->assertSame(2, $execution_count);
    }

    private function executePost(Messenger $messenger): void {
        $user = new User('fake-uid', 'fake-name', Clock::now());
        $messenger->post($user, 'some-fake-message');
    }

}
