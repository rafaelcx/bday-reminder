<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Utils\Clock;

class User {

    public function __construct(
        public readonly string $uid,
        public readonly string $name,
        public readonly Clock $created_at,
    ) {}

}
