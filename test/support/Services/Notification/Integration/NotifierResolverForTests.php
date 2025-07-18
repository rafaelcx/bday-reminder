<?php

declare(strict_types=1);

namespace Test\Support\Services\Notification\Integration;

use App\Services\Notification\Integration\Notifier;
use App\Services\Notification\Integration\NotifierResolver;
use App\Utils\StaticScope;

class NotifierResolverForTests extends NotifierResolver {

    public static function override(Notifier $mock_notifier): void {
        StaticScope::set(parent::class, 'instance', $mock_notifier);
    }

}
