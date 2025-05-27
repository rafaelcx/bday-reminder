<?php

declare(strict_types=1);

namespace App\Repository\Birthday;

class Birthday {

    public function __construct(
        public readonly string $uid,
        public readonly string $user_uid,
        public readonly string $name,
        public readonly \DateTime $date,
        public readonly \DateTime $created_at,
    ) {}

}
