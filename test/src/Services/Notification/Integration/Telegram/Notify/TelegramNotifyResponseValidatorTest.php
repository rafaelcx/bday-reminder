<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification\Integration\Telegram\Notify;

use App\Services\Notification\Integration\Telegram\Notify\TelegramNotifyResponseValidator;
use App\Services\Notification\NotificationException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Test\CustomTestCase;

class TelegramNotifyResponseValidatorTest extends CustomTestCase {

    public function testValidator_WhenSuccess(): void {
        $test_response = $this->buildHttpResponse('{"ok": true}');
        TelegramNotifyResponseValidator::validate($test_response);
        $this->assertTrue(true);
    }

    public function testValidator_WhenMalformedResponse(): void {
        $test_response = $this->buildHttpResponse('{malformed_json');
        $this->expectExceptionMessage('Notification response parsing error');
        $this->expectException(NotificationException::class);
        TelegramNotifyResponseValidator::validate($test_response);
    }

    public function testValidator_WhenResponseNotOk(): void {
        $test_response = $this->buildHttpResponse('{"ok": false, "description": "description"}');
        $this->expectExceptionMessage('Notification response parsing error');
        $this->expectException(NotificationException::class);
        TelegramNotifyResponseValidator::validate($test_response);
    }

    private function buildHttpResponse(string $body): ResponseInterface {
        return new Response(200, [], $body);
    }

}
