<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram;

use App\Repository\Credential\CredentialRepositoryResolver;
use App\Services\Notification\NotificationException;

class TelegramCredentials {

    private const string CREDENTIAL_FILE_ID = 'telegram-credential';

    /**
     * @throws NotificationException
     */
    public static function getBotToken(): string {
        $credential = CredentialRepositoryResolver::resolve()->findById(self::CREDENTIAL_FILE_ID);
        $credential_data = json_decode($credential->data);

        if (empty($credential_data->bot_token)) {
            throw new NotificationException('Telegram credentials not found');
        }
        return $credential_data->bot_token;
    }

}
