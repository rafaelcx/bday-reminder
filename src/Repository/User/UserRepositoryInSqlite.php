<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Storage\Database\DatabaseResolver;
use App\Utils\Clock;

class UserRepositoryInSqlite implements UserRepository {

    public function create(string $name): void {
        $sql = <<<SQL
            INSERT INTO users (uid, name, created_at) 
            VALUES (:uid, :name, :created_at);
            SQL;

        $params = [
            'uid' => uniqid(),
            'name' => $name,
            'created_at' => Clock::now()->format('Y-m-d H:i:s'),
        ];

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute($params);
    }

    /** 
     * @return User[] 
     */
    public function findAll(): array {
        $sql = <<<SQL
            SELECT * FROM users
            SQL;

        $stmt = DatabaseResolver::resolve()->query($sql);

        if ($stmt === false) {
            return [];
        }

        $rows = $stmt->fetchAll();

        $fn = fn(array $r) => new User(
            $r['uid'],
            $r['name'], 
            Clock::at($r['created_at']
        ));
        return array_map($fn, $rows ?: []);
    }

}
