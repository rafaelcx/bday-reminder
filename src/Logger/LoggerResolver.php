<?php

declare(strict_types=1);

namespace App\Logger;

use Psr\Log\LoggerInterface;

class LoggerResolver {

    protected static ?LoggerInterface $instance = null;

    public static function resolve(): LoggerInterface {
        if (is_null(self::$instance)) {
            self::createInstance();
        }
        return self::$instance;
    }

    private static function createInstance(): void {
        self::$instance = new LoggerDefault('log-file.json');
    }

}
