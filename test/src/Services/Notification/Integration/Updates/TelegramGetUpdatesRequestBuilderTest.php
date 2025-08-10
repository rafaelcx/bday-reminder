<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification\Integration\Updates;

use App\Repository\Credential\CredentialRepositoryResolver;
use App\Services\Notification\Integration\Telegram\Updates\TelegramGetUpdatesRequestBuilder;
use Test\CustomTestCase;

class TelegramGetUpdatesRequestBuilderTest extends CustomTestCase {

    public function testRequestBuilder(): void {
        // Mocking valid credentials
        $credential_id = 'telegram-credential';
        $credential_data = '{"bot_token": "some_token"}';
        CredentialRepositoryResolver::resolve()->create($credential_id, $credential_data);

        $request = TelegramGetUpdatesRequestBuilder::build();

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://api.telegram.org/botsome_token/getUpdates', (string) $request->getUri());
    }

}
