<?php

declare(strict_types=1);

namespace App\Repository\UserConfig;

use App\Storage\Database\DatabaseResolver;
use App\Utils\Clock;
use PDO;

class UserConfigRepositoryInSqlite implements UserConfigRepository {

    public function create(string $user_uid, string $name, string $value): void {
        $sql = <<<SQL
            INSERT INTO user_configs (uid, user_uid, name, value, created_at, updated_at)
            VALUES (:uid, :user_uid, :name, :value, :created_at, :updated_at);
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute([
            'uid' => uniqid(),
            'user_uid' => $user_uid,
            'name' => $name,
            'value' => $value,
            'created_at' => Clock::now()->format('Y-m-d H:i:s'),
            'updated_at' => Clock::now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findByUserUidAndName(string $user_uid, string $name): UserConfig {
        $sql = <<<SQL
            SELECT * FROM user_configs WHERE user_uid = :user_uid AND name = :name;
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute([
            'user_uid' => $user_uid,
            'name' => $name,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new UserConfigException("Config not found for user with uid `{$user_uid}` and name `{$name}`");
        }

        return $this->buildConfig($row);
    }

    public function findByNameAndValue(string $name, string $value): UserConfig {
        $sql = <<<SQL
            SELECT * FROM user_configs WHERE name = :name AND value = :value;
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'value' => $value,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new UserConfigException("Config not found for user with name `{$name}` and value `{$value}`");
        }

        return $this->buildConfig($row);
    }

    /**
     * @param array<string, string> $config
     */
    private function buildConfig(array $config): UserConfig {
        return new UserConfig(
            uid: $config['uid'],
            user_uid: $config['user_uid'],
            name: $config['name'],
            value: $config['value'],
            created_at: Clock::at($config['created_at']),
            updated_at: Clock::at($config['updated_at']),
        );
    }
}
