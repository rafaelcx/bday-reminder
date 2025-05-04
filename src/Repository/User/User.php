<?php

declare(strict_types=1);

namespace App\Repository\User;

class User {

    public function __construct(
        public readonly string $uid,
        public readonly string $name,
        public readonly \DateTime $created_at,
    ) {}

}
