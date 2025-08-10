<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification\Integration\Updates;

use App\Services\Notification\Integration\Telegram\Updates\TelegramGetUpdatesResponseParser;
use GuzzleHttp\Psr7\Response;
use Test\CustomTestCase;

class TelegramGetUpdatesResponseParserTest extends CustomTestCase {

    public function testParser_WhenResponseHaveResults(): void {
        $response_body = <<<JSON
        {
            "result": [
                {
                    "message": {
                        "text": "some_text_1",
                        "chat": {
                            "id": "42"
                        },
                        "message_id": "4242"
                    }
                },
                {
                    "message": {
                        "text": "some_text_2",
                        "chat": {
                            "id": "84"
                        },
                        "message_id": "8484"
                    }
                }
            ]
        }
        JSON;

        $response = new Response(200, [], $response_body);
        $results = TelegramGetUpdatesResponseParser::parse($response);

        $this->assertCount(2, $results);

        $this->assertSame('some_text_1', $results[0]->text);
        $this->assertSame('42', $results[0]->chat_id);
        $this->assertSame('4242', $results[0]->id);

        $this->assertSame('some_text_2', $results[1]->text);
        $this->assertSame('84', $results[1]->chat_id);
        $this->assertSame('8484', $results[1]->id);
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
