<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram\Updates;

use App\Services\Notification\Integration\Telegram\TelegramCredentials;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class TelegramGetUpdatesRequestBuilder {

    public static function build(?string $offset = null): RequestInterface {
        $bot_token = TelegramCredentials::getBotToken();
        $uri = "https://api.telegram.org/bot{$bot_token}/getUpdates";

        if (isset($offset)) {
            $uri .= '?offset=' . $offset;
        }

        return new Request('GET', $uri);
    }

}
