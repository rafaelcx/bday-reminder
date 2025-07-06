<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Utils\StaticScope;

class NotificationServiceResolver {

    public static function resolve(): NotificationService {
        return StaticScope::getOrCreate(self::class, 'isntance', self::createInstance(...));
    }

    private static function createInstance(): NotificationService {
        return new NotificationServiceDefault();
    }

}
