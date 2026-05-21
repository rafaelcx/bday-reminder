<?php

declare(strict_types=1);

namespace Test\Src\Services\Messenger\Telegram\Get;

use App\Repository\Credential\CredentialRepositoryResolver;
use App\Services\Messenger\Telegram\Get\TelegramGetRequestBuilder;
use Test\CustomTestCase;

class TelegramGetRequestBuilderTest extends CustomTestCase {

    public function testBuildWithoutOffset_ReturnsRequestToGetUpdates(): void {
        $this->mockValidCredentials();

        $request = TelegramGetRequestBuilder::build();

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://api.telegram.org/botsome_token/getUpdates', (string) $request->getUri());
    }

    public function testBuildWithOffset_AppendsOffsetToQuery(): void {
        $this->mockValidCredentials();
        $request = TelegramGetRequestBuilder::build('123');
        $this->assertSame('https://api.telegram.org/botsome_token/getUpdates?offset=123', (string) $request->getUri());
    }

    private function mockValidCredentials(): void {
        CredentialRepositoryResolver::resolve()->create('telegram-credential', '{"bot_token": "some_token"}');
    }

}
