<?php

declare(strict_types=1);

namespace App\Repository\User;

interface UserRepository {

    public function create(string $name): void;
    public function findAll(): array;

}
