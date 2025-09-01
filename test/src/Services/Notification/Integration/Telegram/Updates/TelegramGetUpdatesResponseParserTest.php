<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification\Integration\Telegram\Updates;

use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Notification\Integration\Telegram\Updates\TelegramGetUpdatesResponseParser;
use GuzzleHttp\Psr7\Response;
use Test\CustomTestCase;

class TelegramGetUpdatesResponseParserTest extends CustomTestCase {

    public function testParser_WhenResponseHaveResults(): void {
        $response_body = <<<JSON
        {
            "result": [
                {
                    "update_id": 4242,
                    "message": {
                        "text": "name1.01-01-1995",
                        "chat": {
                            "id": 42
                        },
                        "message_id": 4000
                    }
                },
                {
                    "update_id": 8484,
                    "message": {
                        "text": "name2.30-12-1990",
                        "chat": {
                            "id": 84
                        },
                        "message_id": 8000
                    }
                }
            ]
        }
        JSON;

        UserConfigRepositoryResolver::resolve()->create('user1Uid', 'telegram-chat-id', '42');
        UserConfigRepositoryResolver::resolve()->create('user2Uid', 'telegram-chat-id', '84');

        $response = new Response(200, [], $response_body);
        $results = TelegramGetUpdatesResponseParser::parse($response);

        $this->assertCount(2, $results);

        $this->assertSame('4242', $results[0]->id);
        $this->assertSame('4000', $results[0]->message_id);
        $this->assertSame('name1', $results[0]->birhday_name);
        $this->assertSame('1995-01-01', $results[0]->birthday_date->asDateString());
        $this->assertSame('user1Uid', $results[0]->user_uid);

        $this->assertSame('8484', $results[1]->id);
        $this->assertSame('8000', $results[1]->message_id);
        $this->assertSame('name2', $results[1]->birhday_name);
        $this->assertSame('1990-12-30', $results[1]->birthday_date->asDateString());
        $this->assertSame('user2Uid', $results[1]->user_uid);
    }

    public function testParser_WhenResponseHasNoResults(): void {
        $response_body = <<<JSON
        {
            "result": []
        }
        JSON;

        $response = new Response(200, [], $response_body);
        $results = TelegramGetUpdatesResponseParser::parse($response);

        $this->assertCount(0, $results);
        $this->assertSame([], $results);
    }

}
