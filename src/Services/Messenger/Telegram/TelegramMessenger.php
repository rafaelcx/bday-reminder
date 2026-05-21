<?php

namespace App\Services\Messenger\Telegram;

use App\Http\Client\HttpClient;
use App\Http\Client\HttpClientException;
use App\Repository\User\User;
use App\Services\Messenger\Message;
use App\Services\Messenger\Messenger;
use App\Services\Messenger\Telegram\Delete\TelegramDeleteRequestBuilder;
use App\Services\Messenger\Telegram\Get\TelegramGetRequestBuilder;
use App\Services\Messenger\Telegram\Get\TelegramGetResponseParser;
use App\Services\Messenger\Telegram\Post\TelegramPostRequestBuilder;
use App\Services\Messenger\Telegram\Post\TelegramPostResponseValidator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TelegramMessenger implements Messenger {

    public function post(User $user, string $message): void {
        $request = TelegramPostRequestBuilder::build($user, $message);
        $response = $this->dispatchRequest($request);
        TelegramPostResponseValidator::validate($response);
    }

    /**
     * @return Message[]
     */
    public function getUpdates(): array {
        $request = TelegramGetRequestBuilder::build();
        $response = $this->dispatchRequest($request);
        $updates = TelegramGetResponseParser::parse($response);

        /*
         * Fetching the higher update_id among all updates and performing another getUpdate call
         * passing it as an offset. This will mark the perviously fetched udpates as `checked`, so
         * they are not returning in a next query attempt.
         */
        if (!empty($updates)) {
            $higher_offset = (int) max(array_map(fn($update) => $update->id, $updates)) + 1;
            $request = TelegramGetRequestBuilder::build((string) $higher_offset);
            $this->dispatchRequest($request);
        }

        /*
         * Deleting messages on the chat to avoid clutter, that way we end up with only the
         * birthday notifications.
         */
        $this->deleteMessages(...$updates);

        return $updates;
    }

    private function deleteMessages(Message ...$updates): void {
        $unique_chats = [];
        foreach ($updates as $update) {
            $unique_chats[$update->user_uid][] = $update;
        }

        foreach ($unique_chats as $user_uid => $messages) {
            $request = TelegramDeleteRequestBuilder::build((string) $user_uid, $messages);
            $this->dispatchRequest($request);
        }
    }

    private function dispatchRequest(RequestInterface $request): ResponseInterface {
        try {
            return (new HttpClient())->send($request);
        } catch (HttpClientException $e) {
            throw new \Exception('Messenger error: ' . $e->getMessage());
        }
    }

}
