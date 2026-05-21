<?php

declare(strict_types=1);

namespace Test\Src\Services\Messenger\Telegram\Get;

use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Messenger\Telegram\Get\TelegramGetResponseParser;
use GuzzleHttp\Psr7\Response;
use Test\CustomTestCase;

class TelegramGetResponseParserTest extends CustomTestCase {

    public function testParse_ReturnsMessagesWhenResultsExist(): void {
        UserConfigRepositoryResolver::resolve()->create('user-1', 'telegram-chat-id', '42');

        $body = '{"result":[{"update_id":1,"message":{"text":"hello","chat":{"id":42},"message_id":99}}]}';
        $response = new Response(200, [], $body);

        $messages = TelegramGetResponseParser::parse($response);

        $this->assertCount(1, $messages);
        $this->assertSame('1', $messages[0]->id);
        $this->assertSame('99', $messages[0]->message_id);
        $this->assertSame('user-1', $messages[0]->user_uid);
        $this->assertSame('hello', $messages[0]->text);
    }

    public function testParse_ReturnsEmptyArrayWhenNoResults(): void {
        $response = new Response(200, [], '{"result":[]}');
        $messages = TelegramGetResponseParser::parse($response);
        $this->assertSame([], $messages);
    }

}
