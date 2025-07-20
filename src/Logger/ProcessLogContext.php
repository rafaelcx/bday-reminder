<?php

declare(strict_types=1);

namespace App\Logger;

class ProcessLogContext {

    protected static $context = [];

    public static function append(string $key, string $value): void {
        self::$context[$key] = $value;
    }

    public static function getAll(): array {
        return self::$context;
    }

}
