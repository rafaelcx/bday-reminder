<?php

declare(strict_types=1);

namespace Test\Support\Services\Notification;

use App\Services\Notification\NotificationService;
use App\Services\Notification\NotificationServiceResolver;
use App\Utils\StaticScope;

class NotificationServiceResolverForTests extends NotificationServiceResolver {

    public static function override(NotificationService $service): void {
        StaticScope::set(parent::class, 'instance', $service);
    }

}
