<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram\Delete;

use App\Services\Notification\Integration\Telegram\TelegramCredentials;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class TelegramDeleteMessagesRequestBuilder {

    public static function build(string $chat_id, array $messages): RequestInterface {
        $bot_token = TelegramCredentials::getBotToken();
        
        $message_ids = [];
        foreach ($messages as $message) {
            $message_ids[] = $message->id;
        }

        $query = http_build_query([
            'chat_id'     => $chat_id,
            'message_ids' => json_encode($message_ids),
        ]);

        $uri = "https://api.telegram.org/bot{$bot_token}/deleteMessages?{$query}";

        return new Request('GET', $uri);
    }

}
