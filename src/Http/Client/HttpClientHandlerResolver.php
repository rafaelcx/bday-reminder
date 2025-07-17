<?php

declare(strict_types=1);

namespace App\Http\Client;

use App\Utils\StaticScope;

class HttpClientHandlerResolver {

    public static function resolve(): ?\Countable {
        return StaticScope::get(self::class, 'instance');
    }

}
