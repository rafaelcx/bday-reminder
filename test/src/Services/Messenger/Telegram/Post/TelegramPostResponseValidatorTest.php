<?php

declare(strict_types=1);

namespace Test\Src\Services\Messenger\Telegram\Post;

use App\Services\Messenger\Telegram\Post\TelegramPostResponseValidator;
use GuzzleHttp\Psr7\Response;
use Test\CustomTestCase;

class TelegramPostResponseValidatorTest extends CustomTestCase {

    public function testValidate_DoesNotThrowWhenResponseIsOk(): void {
        $response = new Response(200, [], '{"ok": true}');
        TelegramPostResponseValidator::validate($response);
        $this->expectNotToPerformAssertions();
    }

    public function testValidate_ThrowsWhenBodyIsMalformedJson(): void {
        $response = new Response(200, [], '{malformed_json}');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Notification response parsing error: could not parse');

        TelegramPostResponseValidator::validate($response);
    }

    public function testValidate_ThrowsWhenOkIsFalse(): void {
        $response = new Response(200, [], '{"ok": false, "description": "Bad token"}');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Notification response parsing error: Bad token');

        TelegramPostResponseValidator::validate($response);
    }

}
