<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram\Delete;

use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Notification\Integration\Telegram\TelegramCredentials;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class TelegramDeleteMessagesRequestBuilder {

    public static function build(string $user_uid, array $messages): RequestInterface {
        $bot_token = TelegramCredentials::getBotToken();
        $message_ids = self::groupMessageIds($messages);
        
        $user_cfg = UserConfigRepositoryResolver::resolve()
            ->findByUserUidAndName($user_uid, 'telegram-chat-id');

        $query = self::buildHttpQuery($user_cfg->value, $message_ids);

        $uri = "https://api.telegram.org/bot{$bot_token}/deleteMessages?{$query}";

        return new Request('GET', $uri);
    }

    private static function groupMessageIds(array $messages): array {
        $message_ids = [];
        foreach ($messages as $message) {
            $message_ids[] = $message->id;
        }
        return $message_ids;
    }

    private static function buildHttpQuery(string $chat_id, array $message_ids): string {
         return http_build_query([
            'chat_id'     => $chat_id,
            'message_ids' => json_encode($message_ids),
        ]);
    }

}
