<?php

declare(strict_types=1);

namespace App\Services\Messenger;

use App\Services\Messenger\Telegram\TelegramMessenger;
use App\Utils\StaticScope;

class MessengerResolver {

    public static function resolve(): Messenger {
        return StaticScope::getOrCreate(self::class, 'instance', self::createInstance(...));
    }

    private static function createInstance(): Messenger {
        return new TelegramMessenger();
    }

}
