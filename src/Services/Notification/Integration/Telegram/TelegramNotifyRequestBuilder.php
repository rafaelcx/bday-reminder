<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram;

use App\Repository\Birthday\Birthday;
use App\Repository\User\User;
use App\Repository\UserConfig\UserConfigException;
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Notification\NotificationException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class TelegramNotifyRequestBuilder {

    public static function build(User $user, Birthday ...$birthdays): RequestInterface {
        $uri = self::buildRequestUri();
        $chat_id = self::resolveChatIdFromUserUid($user->uid);
        $message = self::formatBirthdayMessage($user, ...$birthdays);

        if ($chat_id === null) {
            throw new NotificationException('No Telegram chat ID found for user ' . $user->uid);
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

    private static function buildRequestUri(): string {
        try {
            $bot_token = TelegramCredentials::getBotToken();
        } catch (\Exception $e) {
            throw new NotificationException('Notification request build error: ' . $e->getMessage());
        }
        return "https://api.telegram.org/bot{$bot_token}/sendMessage";
    }

    private static function resolveChatIdFromUserUid(string $user_uid): string {
        $user_cfg_name = 'telegram-chat-id';
        try {
            $user_cfg = UserConfigRepositoryResolver::resolve()->findByUserUidAndName($user_uid, $user_cfg_name);
        } catch (UserConfigException $e) {
            throw new NotificationException('Notification request build error: ' . $e->getMessage());
        }
        return $user_cfg->value;
    }

    private static function formatBirthdayMessage(User $user, Birthday ...$birthdays): string {
        $message = 'Hello ' . $user->name . ',' . "\n\n";
        $message .= '🎉 Here are your upcoming birthday reminders!' . "\n\n";
        
        foreach ($birthdays as $birthday) {
            $message .= sprintf(
                "🎂 *Name:* %s\n*Date:* %s\n\n",
                $birthday->name ?? 'Unknown',
                $birthday->date->format('Y-m-d') ?? 'Unknown'
            );
        }
        return $message;
    }

}
