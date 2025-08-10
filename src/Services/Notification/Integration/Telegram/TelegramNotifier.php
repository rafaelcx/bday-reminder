<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram;

use App\Http\Client\HttpClient;
use App\Http\Client\HttpClientException;
use App\Repository\Birthday\Birthday;
use App\Repository\User\User;
use App\Services\Notification\Integration\Notifier;
use App\Services\Notification\Integration\Telegram\Delete\TelegramDeleteMessagesRequestBuilder;
use App\Services\Notification\Integration\Telegram\Updates\TelegramGetUpdatesRequestBuilder;
use App\Services\Notification\Integration\Telegram\Updates\TelegramGetUpdatesResponseParser;
use App\Services\Notification\NotificationException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TelegramNotifier implements Notifier {

    public function notify(User $user, Birthday ...$birthday_list): void {
        $request = TelegramNotifyRequestBuilder::build($user, ...$birthday_list);
        $response = $this->dispatchRequest($request);
        TelegramNotifyResponseValidator::validate($response);
    }

    public function getUpdates(): array {
        $request = TelegramGetUpdatesRequestBuilder::build();
        $response = $this->dispatchRequest($request);
        return TelegramGetUpdatesResponseParser::parse($response);
    }

    public function deleteMessages(array $updates): void {
        $unique_chats = [];
        foreach ($updates as $update) {
            $unique_chats[$update->chat_id][] = $update;
        }

        foreach ($unique_chats as $chat_id => $messages) {
            $request = TelegramDeleteMessagesRequestBuilder::build((string) $chat_id, $messages);
            $this->dispatchRequest($request);
        }
    }

    private function dispatchRequest(RequestInterface $request): ResponseInterface {
        try {
            return (new HttpClient())->send($request);
        } catch (HttpClientException $e) {
            throw new NotificationException('Notification error: ' . $e->getMessage());
        }
    }

}
