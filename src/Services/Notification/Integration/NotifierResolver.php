<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration;

use App\Services\Notification\Integration\Telegram\TelegramNotifier;
use App\Utils\StaticScope;

class NotifierResolver {

    public static function resolve(): Notifier {
        return StaticScope::getOrCreate(self::class, 'instance', self::createInstance(...));
    }

    private static function createInstance(): Notifier {
        return new TelegramNotifier();
    }

}
