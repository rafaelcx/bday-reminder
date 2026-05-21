<?php

declare(strict_types=1);

namespace Test\Src\Services\Messenger\Telegram\Delete;

use App\Repository\Credential\CredentialRepositoryResolver;
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Messenger\Telegram\Delete\TelegramDeleteRequestBuilder;
use App\Services\Messenger\Message;
use Test\CustomTestCase;

class TelegramDeleteRequestBuilderTest extends CustomTestCase {

    public function testBuild_ReturnsDeleteRequestWithEncodedMessageIds(): void {
        CredentialRepositoryResolver::resolve()->create('telegram-credential', '{"bot_token": "some_token"}');
        UserConfigRepositoryResolver::resolve()->create('user-1', 'telegram-chat-id', '42');

        $messages = [
            new Message('1', '101', 'user-1', 'hi'),
            new Message('2', '102', 'user-1', 'bye'),
        ];

        $request = TelegramDeleteRequestBuilder::build('user-1', $messages);

        $this->assertSame('GET', $request->getMethod());
        $this->assertStringStartsWith('https://api.telegram.org/botsome_token/deleteMessages?', (string) $request->getUri());

        parse_str($request->getUri()->getQuery(), $query);
        $this->assertSame('42', $query['chat_id']);
        $this->assertSame(json_encode(['101', '102']), $query['message_ids']);
    }

}
