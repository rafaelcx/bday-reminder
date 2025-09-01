<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram;

use App\Http\Client\HttpClient;
use App\Http\Client\HttpClientException;
use App\Repository\Birthday\Birthday;
use App\Repository\User\User;
use App\Services\Notification\Integration\Notifier;
use App\Services\Notification\Integration\Telegram\Delete\TelegramDeleteMessagesRequestBuilder;
use App\Services\Notification\Integration\Telegram\Notify\TelegramNotifyRequestBuilder;
use App\Services\Notification\Integration\Telegram\Notify\TelegramNotifyResponseValidator;
use App\Services\Notification\Integration\Telegram\Updates\TelegramGetUpdatesRequestBuilder;
use App\Services\Notification\Integration\Telegram\Updates\TelegramGetUpdatesResponseParser;
use App\Services\Notification\Integration\Telegram\Updates\TelegramUpdate;
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
        $updates = TelegramGetUpdatesResponseParser::parse($response);

        /*
         * Fetching the higher update_id among all updates and performing another getUpdate call
         * passing it as an offset. This will mark the perviously fetched udpates as `checked`, so
         * they are not returning in a next query attempt.
         */
        if (!empty($updates)) {
            $higher_offset = max(array_map(fn($update) => $update->id, $updates)) + 1;
            $request = TelegramGetUpdatesRequestBuilder::build((string) $higher_offset);
            $this->dispatchRequest($request);
        }

        /*
         * Deleting messages on the chat to avoid clutter, that way we end up with only the
         * birthday notifications.
         */
        $this->deleteMessages(...$updates);
        return $updates;
    }

    
    private function deleteMessages(TelegramUpdate ...$updates): void {
        $unique_chats = [];
        foreach ($updates as $update) {
            $unique_chats[$update->user_uid][] = $update;
        }

        foreach ($unique_chats as $user_uid => $messages) {
            $request = TelegramDeleteMessagesRequestBuilder::build((string) $user_uid, $messages);
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
