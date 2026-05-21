<?php

declare(strict_types=1);

namespace App\Services\Messenger\Telegram\Post;

use App\Repository\User\User;
use App\Repository\UserConfig\UserConfigException;
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Messenger\Telegram\TelegramCredentials;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class TelegramPostRequestBuilder {

    public static function build(User $user, string $message): RequestInterface {
        try {
            $bot_token = TelegramCredentials::getBotToken();
        } catch (\Exception $e) {
            throw new \Exception('Notification request build error: ' . $e->getMessage());
        }

        $uri = "https://api.telegram.org/bot{$bot_token}/sendMessage";
        $user_cfg_name = 'telegram-chat-id';

        try {
            $user_cfg = UserConfigRepositoryResolver::resolve()->findByUserUidAndName($user->uid, $user_cfg_name);
        } catch (UserConfigException $e) {
            throw new \Exception('Notification request build error: ' . $e->getMessage());
        }

        $chat_id = $user_cfg->value;
        if (empty($chat_id)) {
            throw new \Exception('No Telegram chat ID found for user ' . $user->uid);
        }

        $body = http_build_query([
            'chat_id'    => $chat_id,
            'text'       => $message,
            'parse_mode' => 'Markdown',
        ]);

        return new Request(
            method: 'POST',
            uri: $uri,
            headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
            body: $body
        );
    }

}
