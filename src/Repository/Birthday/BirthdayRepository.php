<?php

declare(strict_types=1);

namespace App\Repository\Birthday;

interface BirthdayRepository {

    public function create(string $user_uid, string $name, \DateTime $date): void;
    public function findByUserUid(string $user_id): array;

}
