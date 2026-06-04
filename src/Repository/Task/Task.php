<?php

declare(strict_types=1);

namespace App\Repository\Task;

use App\Utils\Clock;

class Task {

    public function __construct(
        public readonly string $id,
        public readonly string $user_uid,
        public readonly string $title,
        public readonly string $status,
        public readonly Clock $created_at,
        public readonly Clock $updated_at,
    ) {}

}
