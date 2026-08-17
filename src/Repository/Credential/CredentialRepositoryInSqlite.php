<?php

declare(strict_types=1);

namespace App\Repository\Credential;

use App\Storage\Database\DatabaseResolver;
use App\Utils\Clock;
use PDO;

class CredentialRepositoryInSqlite implements CredentialRepository {

    public function create(string $id, string $data): void {
        $sql = <<<SQL
            INSERT INTO credentials (id, data, created_at)
            VALUES (:id, :data, :created_at);
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'data' => $data,
            'created_at' => Clock::now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(string $id): Credential {
        $sql = <<<SQL
            SELECT * FROM credentials WHERE id = :id;
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new CredentialException('Credential not find for id: ' . $id);
        }

        return new Credential(
            id: $row['id'],
            data: $row['data'],
            created_at: Clock::at($row['created_at']),
        );
    }
}
