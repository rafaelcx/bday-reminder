<?php

declare(strict_types=1);

namespace Test\Support\Logger;

use App\Logger\ProcessLogContext;

class ProcessLogContextForTests extends ProcessLogContext {

    public static function reset(): void {
        self::$context = [];
    }

    public static function getField(string $field_name): mixed {
        return self::$context[$field_name];
    }

}
