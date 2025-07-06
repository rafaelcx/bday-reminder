<?php

declare(strict_types=1);

namespace App\Logger;

use App\Utils\StaticScope;
use Psr\Log\LoggerInterface;

class LoggerResolver {

    public static function resolve(): LoggerInterface {
        return StaticScope::getOrCreate(self::class, 'instance', self::createInstance(...));
    }

    private static function createInstance(): LoggerInterface {
        return new LoggerDefault('log-file.json');
    }

}
