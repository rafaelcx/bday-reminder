<?php

declare(strict_types=1);

namespace Test\Support\Services\Messenger;

use App\Services\Messenger\Messenger;
use App\Services\Messenger\MessengerResolver;
use App\Utils\StaticScope;

class MessengerResolverForTests extends MessengerResolver {

    public static function override(Messenger $mock_messenger): void {
        StaticScope::set(parent::class, 'instance', $mock_messenger);
    }

}
