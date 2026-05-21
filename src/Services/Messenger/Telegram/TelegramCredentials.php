<?php

declare(strict_types=1);

namespace App\Services\Messenger\Telegram;

use App\Repository\Credential\CredentialRepositoryResolver;
use App\Services\Birthday\BirthdayServiceException;

class TelegramCredentials {

    private const string CREDENTIAL_FILE_ID = 'telegram-credential';

    /**
     * @throws BirthdayServiceException
     */
    public static function getBotToken(): string {
        $credential = CredentialRepositoryResolver::resolve()->findById(self::CREDENTIAL_FILE_ID);
        $credential_data = json_decode($credential->data);

        if (empty($credential_data->bot_token)) {
            // TODO: Throw a generic exception from a communication service instead
            throw new BirthdayServiceException('Telegram credentials not found');
        }
        return $credential_data->bot_token;
    }

}
