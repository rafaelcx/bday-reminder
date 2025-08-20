<?php

declare(strict_types=1);

namespace App\Repository\Credential;

use App\Utils\Clock;

class Credential {

    public function __construct(
        public readonly string $id,
        public readonly string $data,
        public readonly Clock $created_at,
    ) {}

}
