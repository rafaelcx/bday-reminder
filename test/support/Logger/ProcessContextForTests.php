<?php

declare(strict_types=1);

namespace Test\Support\Logger;

use App\Logger\ProcessContext;

class ProcessContextForTests extends ProcessContext {

    public static function reset(): void {
        self::$context = [];
    }

}
