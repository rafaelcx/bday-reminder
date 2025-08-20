<?php

declare(strict_types=1);

namespace App\Repository\Credential;

class Credential {

    // TODO: Change DateTime types to clock

    public function __construct(
        public readonly string $id,
        public readonly string $data,
        public readonly \DateTime $created_at,
    ) {}

}
