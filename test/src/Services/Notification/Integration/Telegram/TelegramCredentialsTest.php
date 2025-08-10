<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification\Integration\Telegram;

use App\Repository\Credential\CredentialRepositoryResolver;
use App\Services\Notification\Integration\Telegram\TelegramCredentials;
use App\Services\Notification\NotificationException;
use Test\CustomTestCase;

class TelegramCredentialsTest extends CustomTestCase {

    public function testTelegramCredentials(): void {
        $credential_data = '{"bot_token": "some_token"}';
        CredentialRepositoryResolver::resolve()->create('telegram-credential', $credential_data);

        $bot_token = TelegramCredentials::getBotToken();
        $this->assertSame('some_token', $bot_token);
    }

    public function testTelegramCredentials_WhenMissingBotToken(): void {
        $credential_data = '{"not_bot_token": "some_token"}';
        CredentialRepositoryResolver::resolve()->create('telegram-credential', $credential_data);

        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Telegram credentials not found');
        TelegramCredentials::getBotToken();
    }

}
