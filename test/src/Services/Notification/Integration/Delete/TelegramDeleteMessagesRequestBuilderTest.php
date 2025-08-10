<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification\Integration\Delete;

use App\Repository\Credential\CredentialRepositoryResolver;
use App\Services\Notification\Integration\Telegram\Delete\TelegramDeleteMessagesRequestBuilder;
use Test\CustomTestCase;

class TelegramDeleteMessagesRequestBuilderTest extends CustomTestCase {

    public function testRequestBuilder(): void {
        // Mocking valid credentials
        $credential_id = 'telegram-credential';
        $credential_data = '{"bot_token": "some_token"}';
        CredentialRepositoryResolver::resolve()->create($credential_id, $credential_data);

        $chat_id = '123';
        $messages = [
            $this->buildMessageObject('1'),
            $this->buildMessageObject('2'),
        ];

        $request = TelegramDeleteMessagesRequestBuilder::build($chat_id, $messages);

        $this->assertSame('GET', $request->getMethod());

        $this->assertSame('https', $request->getUri()->getScheme());
        $this->assertSame('api.telegram.org', $request->getUri()->getHost());
        $this->assertSame('/botsome_token/deleteMessages', $request->getUri()->getPath());

        parse_str($request->getUri()->getQuery(), $parsed_query);

        $this->assertSame('123', $parsed_query['chat_id']);
        $this->assertSame('["1","2"]', $parsed_query['message_ids']);
    }

    private function buildMessageObject(string $id): \stdClass {
        $msg = new \stdClass();
        $msg->id = $id;
        return $msg;
    }

}
