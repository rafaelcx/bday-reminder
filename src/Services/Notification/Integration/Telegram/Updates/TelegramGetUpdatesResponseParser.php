<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram\Updates;

use Psr\Http\Message\ResponseInterface;
use stdClass;

class TelegramGetUpdatesResponseParser {

    public static function parse(ResponseInterface $response): array {
        $response_as_obj = json_decode((string) $response->getBody());
        $results = $response_as_obj->result;

        if (empty($results)) {
            return [];
        }

        $updates = [];
        foreach ($results as $result) {
            $updates[] = self::buildUpdate($result);
        }
        return $updates;
    }

    private static function buildUpdate(stdClass $result): \stdClass {
        $update = new \stdClass;
        $update->text = $result->message->text;
        $update->chat_id = $result->message->chat->id;
        $update->id = $result->message->message_id;
        return $update;
    }

}
