<?php

declare(strict_types=1);

namespace App\Services\Messenger\Telegram\Get;

use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Messenger\Message;
use Psr\Http\Message\ResponseInterface;

class TelegramGetResponseParser {

    /**
     * @return Message[]
     */
    public static function parse(ResponseInterface $response): array {
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

}
