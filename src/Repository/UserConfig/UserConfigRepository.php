<?php

declare(strict_types=1);

namespace App\Repository\UserConfig;

interface UserConfigRepository {

    public function create(string $user_uid, string $name, string $value): void;
    public function findByUserUidAndName(string $user_uid, string $name): UserConfig;
    public function findByNameAndValue(string $name, string $value): UserConfig;

}
