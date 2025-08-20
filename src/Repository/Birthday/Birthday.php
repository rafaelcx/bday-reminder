<?php

declare(strict_types=1);

namespace App\Repository\Birthday;

use App\Utils\Clock;

class Birthday {

    public function __construct(
        public readonly string $uid,
        public readonly string $user_uid,
        public readonly string $name,
        public readonly Clock $date,
        public readonly Clock $created_at,
    ) {}

}
