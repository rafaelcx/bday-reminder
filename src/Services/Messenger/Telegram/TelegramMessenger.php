<?php

namespace App\Services\Messenger\Telegram;

use App\Http\Client\HttpClient;
use App\Http\Client\HttpClientException;
use App\Repository\User\User;
use App\Repository\UserConfig\UserConfigException;
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Messenger\Message;
use App\Services\Messenger\Messenger;
use App\Services\Notification\Integration\Telegram\TelegramCredentials;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TelegramMessenger implements Messenger {

    // TODO: Extract request builder, parsers and validators to their own classes

    public function post(User $user, string $message): void {
        $request = $this->buildPostRequest($user, $message);
        $response = $this->dispatchRequest($request);
        $this->validatePostResponse($response);
    }

    /**
     * @return Message[]
     */
    public function getUpdates(User $user): array {
        $request = $this->buildGetUpdatesRequest();
        $response = $this->dispatchRequest($request);
        $updates = $this->parseGetUpdateResponse($response);

        /*
         * Fetching the higher update_id among all updates and performing another getUpdate call
         * passing it as an offset. This will mark the perviously fetched udpates as `checked`, so
         * they are not returning in a next query attempt.
         */
        if (!empty($updates)) {
            $higher_offset = (int) max(array_map(fn($update) => $update->id, $updates)) + 1;
            $request = $this->buildGetUpdatesRequest((string) $higher_offset);
            $this->dispatchRequest($request);
        }

        /*
         * Deleting messages on the chat to avoid clutter, that way we end up with only the
         * birthday notifications.
         */
        $this->deleteMessages(...$updates);

        return $updates;
    }

    private function buildPostRequest(User $user, string $message): RequestInterface {
        try {
            $bot_token = TelegramCredentials::getBotToken();
        } catch (\Exception $e) {
            // TODO: Throw a generic exception from a communication service instead
            throw new \Exception('Notification request build error: ' . $e->getMessage());
        }
        $uri = "https://api.telegram.org/bot{$bot_token}/sendMessage";

        $user_cfg_name = 'telegram-chat-id';
        try {
            $user_cfg = UserConfigRepositoryResolver::resolve()->findByUserUidAndName($user->uid, $user_cfg_name);
        } catch (UserConfigException $e) {
            // TODO: Throw a generic exception from a communication service instead
            throw new \Exception('Notification request build error: ' . $e->getMessage());
        }
        $chat_id = $user_cfg->value;

        if (empty($chat_id)) {
            // TODO: Throw a generic exception from a communication service instead
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

    private function validatePostResponse(ResponseInterface $response): void {
        $body = (string) $response->getBody();
        $body_as_obj = json_decode($body, true);

        if (!is_array($body_as_obj)) {
            $error = $body_as_obj['description'] ?? 'Unknown error';
            throw new \Exception('Notification response parsing error: could not parse');
        }

        if (!($body_as_obj['ok'] ?? false)) {
            $error = $body_as_obj['description'] ?? 'Unknown error';
            throw new \Exception('Notification response parsing error: ' . $error);
        }
    }

    private function buildGetUpdatesRequest(?string $offset = null): RequestInterface {
        $bot_token = TelegramCredentials::getBotToken();
        $uri = "https://api.telegram.org/bot{$bot_token}/getUpdates";

        if (isset($offset)) {
            $uri .= '?offset=' . $offset;
        }

        return new Request('GET', $uri);
    }

    /**
     * @return Message[]
     */
    private function parseGetUpdateResponse(ResponseInterface $response): array {
        $response_as_obj = json_decode((string) $response->getBody());
        $results = $response_as_obj->result;

        if (empty($results)) {
            return [];
        }

        $updates = [];
        foreach ($results as $result) {
            $user_cfg_name = 'telegram-chat-id';
            $user_cfg = UserConfigRepositoryResolver::resolve()
                ->findByNameAndValue($user_cfg_name, (string) $result->message->chat->id);

            $updates[] = new Message(
                (string) $result->update_id,
                (string) $result->message->message_id,
                $user_cfg->user_uid,
                $result->message->text
            );
        }

        return $updates;
    }

    private function deleteMessages(Message ...$updates): void {
        $unique_chats = [];
        foreach ($updates as $update) {
            $unique_chats[$update->user_uid][] = $update;
        }

        foreach ($unique_chats as $user_uid => $messages) {
            $request = $this->buildDeleteRequest((string) $user_uid, $messages);
            $this->dispatchRequest($request);
        }
    }

    /**
     * @param string $user_uid
     * @param Message[] $messages
     */
    private function buildDeleteRequest(string $user_uid, array $messages): RequestInterface {
        $bot_token = TelegramCredentials::getBotToken();
        $message_ids = $this->groupMessageIds($messages);
        
        $user_cfg = UserConfigRepositoryResolver::resolve()
            ->findByUserUidAndName($user_uid, 'telegram-chat-id');

        $query = self::buildHttpQuery($user_cfg->value, $message_ids);

        $uri = "https://api.telegram.org/bot{$bot_token}/deleteMessages?{$query}";

        return new Request('GET', $uri);
    }

    /**
     * @param Message[] $messages
     * @return string[]
     */
    private function groupMessageIds(array $messages): array {
        $message_ids = [];
        foreach ($messages as $message) {
            $message_ids[] = $message->message_id;
        }
        return $message_ids;
    }

    /**
     * @param string $chat_id
     * @param string[] $message_ids
     */
    private function buildHttpQuery(string $chat_id, array $message_ids): string {
         return http_build_query([
            'chat_id'     => $chat_id,
            'message_ids' => json_encode($message_ids),
        ]);
    }

    private function dispatchRequest(RequestInterface $request): ResponseInterface {
        try {
            return (new HttpClient())->send($request);
        } catch (HttpClientException $e) {
            throw new \Exception('Messenger error: ' . $e->getMessage());
        }
    }

}
