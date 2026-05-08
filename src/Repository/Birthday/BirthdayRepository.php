<?php

declare(strict_types=1);

namespace App\Repository\Birthday;

use App\Utils\Clock;

interface BirthdayRepository {

    public function create(string $user_uid, string $name, Clock $date): void;

    /** @return Birthday[] */
    public function findByUserUid(string $user_id): array;

    /** @return Birthday[] */
    public function findByUserUidInTheNextDays(string $user_uid, int $days): array;

    public function update(string $birthday_uid, string $name, Clock $date): void;
    public function delete(string $birthday_uid): void;

}
