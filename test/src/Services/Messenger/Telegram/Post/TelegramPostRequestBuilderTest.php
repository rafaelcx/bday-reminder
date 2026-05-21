<?php

declare(strict_types=1);

namespace Test\Src\Services\Messenger\Telegram\Post;

use App\Repository\Credential\CredentialRepositoryResolver;
use App\Repository\User\User;
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Messenger\Telegram\Post\TelegramPostRequestBuilder;
use App\Utils\Clock;
use Test\CustomTestCase;

class TelegramPostRequestBuilderTest extends CustomTestCase {

    public function testBuild_ReturnsPostRequestWithExpectedBodyAndHeaders(): void {
        $user = new User('uid-1', 'user1', Clock::now());
        $this->mockValidCredentials();
        UserConfigRepositoryResolver::resolve()->create($user->uid, 'telegram-chat-id', '42');

        $request = TelegramPostRequestBuilder::build($user, 'Hello world');

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://api.telegram.org/botsome_token/sendMessage', (string) $request->getUri());
        $this->assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
        $this->assertSame('chat_id=42&text=Hello+world&parse_mode=Markdown', (string) $request->getBody());
    }

    public function testBuild_ShouldThrowWhenTelegramConfigIsMissing(): void {
        $user = new User('uid-1', 'user1', Clock::now());
        $this->mockValidCredentials();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Notification request build error');

        TelegramPostRequestBuilder::build($user, 'Hello world');
    }

    private function mockValidCredentials(): void {
        CredentialRepositoryResolver::resolve()
            ->create('telegram-credential', '{"bot_token": "some_token"}');
    }

}
