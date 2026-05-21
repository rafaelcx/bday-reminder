<?php

declare(strict_types=1);

namespace App\Services\Messenger\Telegram\Get;

use App\Services\Messenger\Telegram\TelegramCredentials;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class TelegramGetRequestBuilder {

    public static function build(?string $offset = null): RequestInterface {
        $bot_token = TelegramCredentials::getBotToken();
        $uri = "https://api.telegram.org/bot{$bot_token}/getUpdates";

        if (isset($offset)) {
            $uri .= '?offset=' . $offset;
        }

        return new Request('GET', $uri);
    }

}
