<?php

declare(strict_types=1);

namespace App\Services\Messenger\Telegram\Post;

use Psr\Http\Message\ResponseInterface;

class TelegramPostResponseValidator {

    public static function validate(ResponseInterface $response): void {
        $body = (string) $response->getBody();
        $body_as_obj = json_decode($body, true);

        if (!is_array($body_as_obj)) {
            throw new \Exception('Notification response parsing error: could not parse');
        }

        if (!($body_as_obj['ok'] ?? false)) {
            $error = $body_as_obj['description'] ?? 'Unknown error';
            throw new \Exception('Notification response parsing error: ' . $error);
        }
    }

}
