<?php

declare(strict_types=1);

namespace App\Logger;

class ProcessLogContext {

    protected static $context = [];

    public static function set(string $key, string $value): void {
        if (!isset(self::$context[$key])) {
            self::$context[$key] = $value;
            return;
        }

        $i = 1;
        while (isset(self::$context[$key . '.' . $i])) {
            $i++;
        }

        // Append the new entry as key.N
        self::$context[$key . '.' . $i] = $value;
    }

    public static function getAll(): array {
        return self::$context;
    }

}
