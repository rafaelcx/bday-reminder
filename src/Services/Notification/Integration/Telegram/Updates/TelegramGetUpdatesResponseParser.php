<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram\Updates;
 
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Utils\Clock;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class TelegramGetUpdatesResponseParser {

    /** 
     * @return TelegramUpdate[] 
     */
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

    private static function buildUpdate(stdClass $result): TelegramUpdate {
        $user_cfg_name = 'telegram-chat-id';
        $user_cfg = UserConfigRepositoryResolver::resolve()
            ->findByNameAndValue($user_cfg_name, $result->message->chat->id);

        [$bday_name, $bday_date] = explode('.', $result->message->text);

        return new TelegramUpdate($user_cfg->user_uid, $bday_name, Clock::at($bday_date));
    }

}
