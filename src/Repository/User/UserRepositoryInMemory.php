<?php

declare(strict_types=1);

namespace App\Repository\User;

class UserRepositoryInMemory {

    public static array $user_list = [];

    public function create(string $name): void {
        $uid = uniqid();
        $created_at = (new \DateTime())->format('Y-m-d H:i:s');

        self::$user_list[$uid] = [
            'uid' => $uid,
            'name' => $name,
            'created_at' => $created_at,
        ];
    }

    public function findAll(): array {
        $fn = fn($user) => new User($user['uid'], $user['name'], new \DateTime($user['created_at']));
        return array_map($fn, self::$user_list);
    }

}
