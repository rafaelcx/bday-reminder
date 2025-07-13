<?php

declare(strict_types=1);

namespace App\Repository\UserConfig;

class UserConfig {

    public function __construct(
        public readonly string $uid,
        public readonly string $user_uid,
        public readonly string $name,
        public readonly string $value,
        public readonly \DateTime $created_at,
        public readonly \DateTime $updated_at,
    ) {}

}
